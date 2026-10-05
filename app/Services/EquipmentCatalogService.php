<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Application service for catalogue writes.
 *
 * Equipment and its optional energy profile are saved in one transaction so
 * the two records cannot become inconsistent when a form submission fails.
 */
class EquipmentCatalogService
{
    public function createCategory(array $attributes): Category
    {
        $attributes = $this->storeImage($attributes);
        return Category::create($attributes);
    }

    public function updateCategory(Category $category, array $attributes): Category
    {
        $attributes = $this->storeImage($attributes);
        $category->update($attributes);
        return $category->refresh();
    }

    public function deleteCategory(Category $category): void
    {
        if ($category->equipment()->exists()) {
            throw new \DomainException(__('A category with equipment cannot be deleted.'));
        }

        $category->delete();
    }

    public function createEquipment(array $attributes): Equipment
    {
        // The nested `energy` payload belongs to energy_profiles, not equipment.
        return DB::transaction(function () use ($attributes): Equipment {
            $energy = $attributes['energy'] ?? [];
            unset($attributes['energy']);
            $attributes = $this->storeImage($attributes);
            $attributes['approval_status'] ??= 'published';

            $equipment = Equipment::create($attributes);
            if (array_filter($energy, static fn ($value) => $value !== null && $value !== '')) {
                $equipment->energyProfile()->create($energy);
            }

            return $equipment->load(['category', 'owner', 'energyProfile']);
        });
    }

    public function updateEquipment(Equipment $equipment, array $attributes): Equipment
    {
        // An empty energy section intentionally removes the old profile.
        return DB::transaction(function () use ($equipment, $attributes): Equipment {
            $energy = $attributes['energy'] ?? [];
            unset($attributes['energy']);
            $attributes = $this->storeImage($attributes);

            $equipment->update($attributes);
            if (array_filter($energy, static fn ($value) => $value !== null && $value !== '')) {
                $equipment->energyProfile()->updateOrCreate([], $energy);
            } else {
                $equipment->energyProfile()->delete();
            }

            return $equipment->refresh()->load(['category', 'owner', 'energyProfile']);
        });
    }

    public function deleteEquipment(Equipment $equipment): void
    {
        $equipment->delete();
    }

    /** Store an uploaded catalogue image while keeping URL input compatible. */
    private function storeImage(array $attributes): array
    {
        if (($attributes['image'] ?? null) instanceof UploadedFile) {
            $attributes['image_url'] = Storage::disk('public')->url(
                $attributes['image']->store('catalogue', 'public')
            );
        }

        unset($attributes['image']);
        return $attributes;
    }
}
