<?php

namespace App\Contracts;

interface FailureRiskPredictorInterface
{
    public function predict(int $rentals, int $damages, ?int $daysSinceMaintenance): array;
}
