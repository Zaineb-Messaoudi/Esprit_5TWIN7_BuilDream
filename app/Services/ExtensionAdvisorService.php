<?php

namespace App\Services;

use App\Enums\ExtensionStatus;
use App\Enums\RentalStatus;
use App\Models\Rental;
use App\Models\RentalExtension;

/**
 * Decision aid for an extension request (AI feature of the rental module, rule-based).
 *
 * It looks at the request and gives a RECOMMENDATION (approve / review / refuse)
 * with the REASONS behind it. It never decides: the final decision stays human.
 *
 * Each reason has a level:
 *   blocker  : a reason to refuse (e.g. the equipment is booked by someone else)
 *   warning  : a reason to look more closely
 *   positive : a reason in favour of the request
 *   info     : neutral information
 * Recommendation: any blocker -> refuse; else any warning -> review; else approve.
 */
class ExtensionAdvisorService
{
    /** A request longer than this number of extra days gets a warning. */
    public const LONG_EXTENSION_DAYS = 7;

    /** A rental already extended this many times (or more) gets a warning. */
    public const MAX_COMFORTABLE_EXTENSIONS = 2;

    public function __construct(protected ExtensionAvailabilityService $availability) {}

    /**
     * @return array{recommendation: string, label: string, color: string, reasons: array<int, array{level: string, text: string}>}
     */
    public function advise(RentalExtension $extension): array
    {
        $extension->loadMissing('rental.user');
        $rental = $extension->rental;
        $reasons = [];

        // 1) Availability: is another booking planned during the extra period?
        $conflicts = $this->availability->conflicts($rental, $extension->new_end_date);
        if ($conflicts->isNotEmpty()) {
            $reasons[] = ['level' => 'blocker', 'text' => $this->availability->refusalMessage($rental, $conflicts)];
        } else {
            $reasons[] = ['level' => 'positive', 'text' => 'No other booking during the extra period.'];
        }

        // 2) The rental itself must still be running or about to start
        if (! in_array($rental->status, [RentalStatus::PENDING, RentalStatus::ACTIVE], true)) {
            $reasons[] = ['level' => 'blocker', 'text' => 'The rental is ' . strtolower($rental->status->label()) . ': it can no longer be extended.'];
        }

        // 3) Is the rental already late?
        if ($rental->status === RentalStatus::ACTIVE && $rental->end_date->lt(today())) {
            $lateDays = (int) $rental->end_date->diffInDays(today());
            $reasons[] = ['level' => 'warning', 'text' => "The equipment is already {$lateDays} day(s) late."];
        }

        // 4) Length of the extension
        $extraDays = max(1, (int) $rental->end_date->diffInDays($extension->new_end_date));
        if ($extraDays > self::LONG_EXTENSION_DAYS) {
            $reasons[] = ['level' => 'warning', 'text' => "Long extension: {$extraDays} extra days requested."];
        }

        // 5) Number of times this rental was already extended
        $alreadyExtended = $rental->extensions()
            ->where('status', ExtensionStatus::APPROVED->value)
            ->count();
        if ($alreadyExtended >= self::MAX_COMFORTABLE_EXTENSIONS) {
            $reasons[] = ['level' => 'warning', 'text' => "This rental was already extended {$alreadyExtended} time(s)."];
        }

        // 6) History of the renter on other rentals
        $reasons = array_merge($reasons, $this->renterHistory($rental, $extension));

        // Final recommendation
        $levels = array_column($reasons, 'level');
        $recommendation = match (true) {
            in_array('blocker', $levels, true) => 'refuse',
            in_array('warning', $levels, true) => 'review',
            default                            => 'approve',
        };

        return [
            'recommendation' => $recommendation,
            'label'          => ['approve' => 'Approve', 'review' => 'Review carefully', 'refuse' => 'Refuse'][$recommendation],
            'color'          => ['approve' => 'success', 'review' => 'warning', 'refuse' => 'error'][$recommendation],
            'reasons'        => $reasons,
        ];
    }

    /** Reasons coming from what the renter did on his OTHER rentals. */
    private function renterHistory(Rental $rental, RentalExtension $extension): array
    {
        $reasons = [];
        $others = Rental::query()->where('user_id', $rental->user_id)->whereKeyNot($rental->id);

        $completed = (clone $others)->where('status', RentalStatus::COMPLETED->value)->count();
        $overdue = (clone $others)
            ->where('status', RentalStatus::ACTIVE->value)
            ->where('end_date', '<', today()->toDateString())
            ->count();
        $rejected = RentalExtension::query()
            ->where('status', ExtensionStatus::REJECTED->value)
            ->whereKeyNot($extension->id)
            ->whereHas('rental', fn ($query) => $query->where('user_id', $rental->user_id))
            ->count();

        if ($completed > 0) {
            $reasons[] = ['level' => 'positive', 'text' => "The renter completed {$completed} earlier rental(s)."];
        }
        if ($overdue > 0) {
            $reasons[] = ['level' => 'warning', 'text' => "Another rental of this renter is overdue ({$overdue})."];
        }
        if ($rejected > 0) {
            $reasons[] = ['level' => 'warning', 'text' => "{$rejected} earlier extension request(s) of this renter were rejected."];
        }
        if ($completed === 0 && $overdue === 0 && $rejected === 0) {
            $reasons[] = ['level' => 'info', 'text' => 'No earlier history for this renter: nothing to judge from.'];
        }

        return $reasons;
    }
}