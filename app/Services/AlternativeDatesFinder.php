<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class AlternativeDatesFinder
{
    /**
     * Find the free periods closest to the requested one.
     *
     * Dates are whole days and both ends are inclusive. A busy period is
     * ['start' => date, 'end' => date]. Malformed periods are ignored.
     *
     * @param  list<array<string, mixed>>  $busyPeriods
     * @return array{status: string, duration_days: int, alternatives: list<array{start: string, end: string, shift_days: int, direction: string}>}
     */
    public function find(
        CarbonInterface|string $start,
        CarbonInterface|string $end,
        array $busyPeriods,
        CarbonInterface|string|null $today = null,
        int $maxShiftDays = 30,
        int $limit = 3,
    ): array {
        $start = CarbonImmutable::parse($start)->startOfDay();
        $end = CarbonImmutable::parse($end)->startOfDay();
        $today = CarbonImmutable::parse($today ?? 'today')->startOfDay();
        $maxShiftDays = max(1, min(120, $maxShiftDays));
        $limit = max(1, min(10, $limit));

        if ($end->lessThan($start)) {
            return ['status' => 'invalid', 'duration_days' => 0, 'alternatives' => []];
        }

        $duration = (int) round($start->diffInDays($end)) + 1;
        $busy = $this->normalize($busyPeriods);

        if ($this->isFree($start, $end, $busy)) {
            return ['status' => 'available', 'duration_days' => $duration, 'alternatives' => []];
        }

        $accepted = [];

        for ($shift = 1; $shift <= $maxShiftDays && count($accepted) < $limit; $shift++) {
            foreach (['later' => $shift, 'earlier' => -$shift] as $direction => $offset) {
                if (count($accepted) >= $limit) {
                    break;
                }

                $candidateStart = $start->addDays($offset);
                $candidateEnd = $candidateStart->addDays($duration - 1);

                if ($candidateStart->lessThan($today)) {
                    continue;
                }

                if (! $this->isFree($candidateStart, $candidateEnd, $busy)) {
                    continue;
                }

                // Keep the proposals distinct from each other.
                foreach ($accepted as $other) {
                    if ($candidateStart->lte($other['end']) && $candidateEnd->gte($other['start'])) {
                        continue 2;
                    }
                }

                $accepted[] = [
                    'start' => $candidateStart,
                    'end' => $candidateEnd,
                    'shift' => $shift,
                    'direction' => $direction,
                ];
            }
        }

        return [
            'status' => $accepted === [] ? 'none' : 'alternatives',
            'duration_days' => $duration,
            'alternatives' => array_map(fn (array $row): array => [
                'start' => $row['start']->toDateString(),
                'end' => $row['end']->toDateString(),
                'shift_days' => $row['shift'],
                'direction' => $row['direction'],
            ], $accepted),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $periods
     * @return list<array{0: CarbonImmutable, 1: CarbonImmutable}>
     */
    private function normalize(array $periods): array
    {
        $busy = [];

        foreach ($periods as $period) {
            if (! is_array($period) || ! isset($period['start'], $period['end'])) {
                continue;
            }

            try {
                $busyStart = CarbonImmutable::parse($period['start'])->startOfDay();
                $busyEnd = CarbonImmutable::parse($period['end'])->startOfDay();
            } catch (\Throwable) {
                continue;
            }

            if ($busyEnd->lessThan($busyStart)) {
                continue;
            }

            $busy[] = [$busyStart, $busyEnd];
        }

        return $busy;
    }

    /**
     * @param  list<array{0: CarbonImmutable, 1: CarbonImmutable}>  $busy
     */
    private function isFree(CarbonImmutable $start, CarbonImmutable $end, array $busy): bool
    {
        foreach ($busy as [$busyStart, $busyEnd]) {
            if ($start->lte($busyEnd) && $end->gte($busyStart)) {
                return false;
            }
        }

        return true;
    }
}
