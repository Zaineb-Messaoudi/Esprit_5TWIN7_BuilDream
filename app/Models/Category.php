<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A catalogue category used to group renewable-energy equipment.
 *
 * Categories are managed by administrators and only active categories can
 * be selected when an owner publishes equipment.
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'status'];

    /** Equipment listings assigned to this category. */
    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }
}
