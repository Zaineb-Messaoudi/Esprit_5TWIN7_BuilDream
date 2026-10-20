<?php

namespace App\Services;

use App\Enums\ExtensionStatus;
use App\Enums\RentalStatus;
use App\Models\Rental;

/**
 * Delay risk score of a rental (AI feature of the rental module, rule-based).
 *
 * The score goes from 0 (very safe) to 100 (very likely to be late). It is computed from
 * the history that the database really has: current lateness, other late rentals of the
 * same renter, extensions already granted, and rentals completed or cancelled before.
 * Every rule that changed the score is returned as a "reason", so the result is explainable.
 *
 * Levels: below 30 = low, 30 to 59 = medium, 60 and above = high.
 */
class LateRiskService
{
    public const BASE_SCORE = 10;      // every rental starts here
    public const MEDIUM_FROM = 30;
    public const HIGH_FROM = 60;

    /**
     * @return array{score: int, level: string, label: string, color: string, reasons: array<int, array{direction: string, text: string}>}
     */
    public function assess(Rental $rental): array
    {
        $score = self::BASE_SCORE;
        $reasons = [];

        // 1) This rental is already late: the strongest signal (50 points + 5 per late day, 70 at most)
        if ($rental->status === RentalStatus::ACTIVE && $rental->end_date->lt(today())) {
            $lateDays = (int) $rental->end_date->diffInDays(today());
            $points = min(70, 50 + 5 * $lateDays);
            $score += $points;
            $reasons[] = ['direction' => 'up', 'text' => "This rental is already {$lateDays} day(s) late (+{$points})."];
        }

        // The other rentals of the same renter
        $others = Rental::query()->where('user_id', $rental->user_id)->whereKeyNot($rental->id);

        // 2) Other rentals of this renter that are late right now (25 points each, 30 at most)
        $otherLate = (clone $others)
            ->where('status', RentalStatus::ACTIVE->value)
            ->where('end_date', '<', today()->toDateString())
            ->count();
        if ($otherLate > 0) {
            $points = min(30, 25 * $otherLate);
            $score += $points;
            $reasons[] = ['direction' => 'up', 'text' => "{$otherLate} other rental(s) of this renter are late (+{$points})."];
        }

        // 3) Extensions already granted on THIS rental (8 points each, 24 at most):
        //    a rental that keeps being extended often ends late
        $approved = $rental->extensions()->where('status', ExtensionStatus::APPROVED->value)->count();
        if ($approved > 0) {
            $points = min(24, 8 * $approved);
            $score += $points;
            $reasons[] = ['direction' => 'up', 'text' => "Extended {$approved} time(s) already (+{$points})."];
        }

        // 4) Cancelled rentals of this renter (5 points each, 15 at most)
        $cancelled = (clone $others)->where('status', RentalStatus::CANCELLED->value)->count();
        if ($cancelled > 0) {
            $points = min(15, 5 * $cancelled);
            $score += $points;
            $reasons[] = ['direction' => 'up', 'text' => "{$cancelled} earlier rental(s) were cancelled (+{$points})."];
        }

        // 5) Rentals completed before: a good sign (5 points less each, 20 at most)
        $completed = (clone $others)->where('status', RentalStatus::COMPLETED->value)->count();
        if ($completed > 0) {
            $points = min(20, 5 * $completed);
            $score -= $points;
            $reasons[] = ['direction' => 'down', 'text' => "{$completed} rental(s) completed before (-{$points})."];
        }

        if ($reasons === []) {
            $reasons[] = ['direction' => 'neutral', 'text' => 'No history to judge from: the base score is used.'];
        }

        $score = max(0, min(100, $score));

        [$level, $label, $color] = match (true) {
            $score >= self::HIGH_FROM   => ['high', 'High', 'error'],
            $score >= self::MEDIUM_FROM => ['medium', 'Medium', 'warning'],
            default                     => ['low', 'Low', 'success'],
        };

        return [
            'score'   => $score,
            'level'   => $level,
            'label'   => $label,
            'color'   => $color,
            'reasons' => $reasons,
        ];
    }
}