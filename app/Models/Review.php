<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Review model for post-rental ratings and feedback.
 */
class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reviews';

    protected $fillable = [
        'rental_id',
        'reviewer_id',
        'reviewee_id',
        'type',
        'overall_rating',
        'communication_rating',
        'reliability_rating',
        'condition_rating',
        'value_rating',
        'comment',
        'photos',
        'is_public',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'overall_rating' => 'integer',
            'communication_rating' => 'integer',
            'reliability_rating' => 'integer',
            'condition_rating' => 'integer',
            'value_rating' => 'integer',
            'photos' => 'array',
            'is_public' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /** The rental this review is for. */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    /** The user who wrote the review. */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /** The user being reviewed. */
    public function reviewee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    /** Disputes filed against this review. */
    public function disputes(): HasMany
    {
        return $this->hasMany(ReviewDispute::class);
    }

    /** Responses to this review. */
    public function responses(): HasMany
    {
        return $this->hasMany(ReviewResponse::class);
    }

    /** Get the average detailed rating. */
    public function averageDetailedRating(): ?float
    {
        $ratings = collect([
            $this->communication_rating,
            $this->reliability_rating,
            $this->condition_rating,
            $this->value_rating,
        ])->filter();

        return $ratings->isEmpty() ? null : round($ratings->average(), 1);
    }

    /** Check if review is from owner to renter. */
    public function isOwnerToRenter(): bool
    {
        return $this->type === 'owner_to_renter';
    }

    /** Check if review is from renter to owner. */
    public function isRenterToOwner(): bool
    {
        return $this->type === 'renter_to_owner';
    }

    /** Scope for public reviews only. */
    public function scopePublic($query)
    {
        return $query->where('is_public', true)->whereNotNull('published_at');
    }

    /** Scope for reviews by type. */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
