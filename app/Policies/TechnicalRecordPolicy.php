<?php

namespace App\Policies;

use App\Models\Inspection;
use App\Models\Maintenance;
use App\Models\MaintenanceReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TechnicalRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    public function view(User $user, Model $record): bool
    {
        return $this->ownsRecord($user, $record);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Model $record): bool
    {
        return $this->ownsRecord($user, $record);
    }

    public function delete(User $user, Model $record): bool
    {
        return $this->ownsRecord($user, $record);
    }

    private function ownsRecord(User $user, Model $record): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isOwner()) {
            return false;
        }

        $equipment = match (true) {
            $record instanceof Maintenance, $record instanceof Inspection => $record->equipment,
            $record instanceof MaintenanceReport => $record->maintenance?->equipment,
            default => null,
        };

        return $equipment !== null && (int) $equipment->owner_id === (int) $user->getKey();
    }
}
