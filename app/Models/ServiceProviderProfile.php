<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceProviderProfile extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'description',
        'specialties',
        'service_areas',
        'experience_years',
        'minimum_project_budget',
        'available_from',
        'accepting_requests',
        'contact_email',
    ];

    protected function casts(): array
    {
        return [
            'specialties' => 'array',
            'service_areas' => 'array',
            'experience_years' => 'integer',
            'minimum_project_budget' => 'decimal:2',
            'available_from' => 'date',
            'accepting_requests' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
