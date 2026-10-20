<?php

namespace App\Services\Maintenance;

use App\Contracts\FailureRiskPredictorInterface;

class RuleBasedFailureRiskService implements FailureRiskPredictorInterface
{
    public function predict(int $rentals, int $damages, ?int $daysSinceMaintenance): array
    {
        $points = min(40, max(0, $rentals) * 2) + min(40, max(0, $damages) * 10);
        $points += $daysSinceMaintenance === null ? 20 : min(20, max(0, $daysSinceMaintenance) / 18.25);
        $score = (int) round(min(100, max(0, $points)));

        return ['score' => $score, 'level' => $score >= 70 ? 'high' : ($score >= 35 ? 'medium' : 'low')];
    }
}
