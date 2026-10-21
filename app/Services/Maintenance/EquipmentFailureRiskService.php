<?php

namespace App\Services\Maintenance;

use App\Contracts\FailureRiskPredictorInterface;
use App\Models\Equipment;
use App\Models\Inspection;
use App\Models\Maintenance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EquipmentFailureRiskService
{
    public function __construct(private readonly FailureRiskPredictorInterface $predictor) {}

    /** @return array{score:int,level:string,rentals:int,damages:int,days_since_maintenance:int|null} */
    public function forEquipment(Equipment $equipment): array
    {
        $equipmentId = $equipment->getKey();
        $rentals = Schema::hasTable('rentals')
            ? DB::table('rentals')->where('equipment_id', $equipmentId)->count()
            : 0;

        $damages = Schema::hasTable('damages')
            ? DB::table('damages')->where('equipment_id', $equipmentId)->count()
            : (Schema::hasTable('inspections')
                ? Inspection::query()->where('equipment_id', $equipmentId)->where('damage_detected', true)->count()
                : 0);

        $lastMaintenance = Schema::hasTable('maintenances')
            ? Maintenance::query()->where('equipment_id', $equipmentId)
                ->orderByDesc('start_date')->value(DB::raw('COALESCE(end_date, start_date)'))
            : null;
        $days = $lastMaintenance ? (int) max(0, floor(Carbon::parse($lastMaintenance)->diffInDays(now()))) : null;

        return $this->predictor->predict((int) $rentals, (int) $damages, $days) + [
            'rentals' => (int) $rentals,
            'damages' => (int) $damages,
            'days_since_maintenance' => $days,
        ];
    }
}
