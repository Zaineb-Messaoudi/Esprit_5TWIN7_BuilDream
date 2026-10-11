<?php

namespace App\Services;

use App\Models\Rental;
use App\Models\Review;
use App\Models\ReviewDispute;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewService
{
    /**
     * Create a review after rental completion.
     */
    public function createReview(
        Rental $rental,
        User $reviewer,
        User $reviewee,
        string $type,
        array $ratings,
        ?string $comment = null,
        ?array $photos = null
    ): Review {
        return DB::transaction(function () use ($rental, $reviewer, $reviewee, $type, $ratings, $comment, $photos) {
            $review = Review::create([
                'rental_id' => $rental->id,
                'reviewer_id' => $reviewer->id,
                'reviewee_id' => $reviewee->id,
                'type' => $type,
                'overall_rating' => $ratings['overall'],
                'communication_rating' => $ratings['communication'] ?? null,
                'reliability_rating' => $ratings['reliability'] ?? null,
                'condition_rating' => $ratings['condition'] ?? null,
                'value_rating' => $ratings['value'] ?? null,
                'comment' => $comment,
                'photos' => $photos,
                'is_public' => true,
                'published_at' => now(),
            ]);

            // Update reviewee's aggregate rating
            $this->updateUserAggregateRating($reviewee);

            Log::info("Review created: {$type} for rental {$rental->reference}");

            return $review;
        });
    }

    /**
     * Update user's aggregate rating statistics.
     */
    public function updateUserAggregateRating(User $user): void
    {
        $reviews = Review::public()
            ->where('reviewee_id', $user->id)
            ->get();

        if ($reviews->isEmpty()) {
            $user->update([
                'aggregate_rating' => null,
                'reviews_count' => 0,
            ]);

            return;
        }

        $avg = $reviews->avg('overall_rating');
        $detailed = [
            'communication' => $reviews->where('communication_rating', '!=', null)->avg('communication_rating'),
            'reliability' => $reviews->where('reliability_rating', '!=', null)->avg('reliability_rating'),
            'condition' => $reviews->where('condition_rating', '!=', null)->avg('condition_rating'),
            'value' => $reviews->where('value_rating', '!=', null)->avg('value_rating'),
        ];

        $user->update([
            'aggregate_rating' => round($avg, 1),
            'reviews_count' => $reviews->count(),
            'detailed_ratings' => $detailed,
        ]);
    }

    /**
     * Get reviews for a user with pagination.
     */
    public function getUserReviews(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Review::public()
            ->where('reviewee_id', $user->id)
            ->with(['reviewer:id,name,profile_photo_path', 'rental.equipment'])
            ->latest('published_at')
            ->paginate($perPage);
    }

    /**
     * Create a dispute for a review.
     */
    public function createDispute(
        Review $review,
        User $initiator,
        string $reason,
        string $description
    ): ReviewDispute {
        // Check if user can dispute (must be involved in the rental)
        $rental = $review->rental;
        if (! in_array($initiator->id, [$rental->user_id, $rental->equipment->owner_id])) {
            throw new \Exception('Unauthorized to dispute this review');
        }

        // Check if already disputed by this user
        if (ReviewDispute::where('review_id', $review->id)
            ->where('initiator_id', $initiator->id)
            ->exists()) {
            throw new \Exception('You have already disputed this review');
        }

        return ReviewDispute::create([
            'review_id' => $review->id,
            'initiator_id' => $initiator->id,
            'reason' => $reason,
            'description' => $description,
            'status' => 'open',
        ]);
    }

    /**
     * Resolve a dispute (admin only).
     */
    public function resolveDispute(
        ReviewDispute $dispute,
        User $admin,
        string $status,
        ?string $resolution = null
    ): ReviewDispute {
        $dispute->update([
            'status' => $status,
            'resolved_by' => $admin->id,
            'resolution' => $resolution,
            'resolved_at' => in_array($status, ['resolved', 'dismissed']) ? now() : null,
        ]);

        // If resolved in favor of complainant, optionally hide the review
        if ($status === 'resolved' && $dispute->review) {
            $dispute->review->update(['is_public' => false]);
        }

        return $dispute;
    }

    /**
     * Get pending reviews for a user (rentals completed but not reviewed).
     */
    public function getPendingReviews(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Rental::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereDoesntHave('reviews', function ($q) use ($user) {
                $q->where('reviewer_id', $user->id);
            })
            ->whereHas('equipment', function ($q) {
                $q->where('status', '!=', 'deleted');
            })
            ->with(['equipment', 'equipment.owner'])
            ->get();
    }

    /**
     * Get average rating breakdown for a user.
     */
    public function getUserRatingBreakdown(User $user): array
    {
        $reviews = Review::public()->where('reviewee_id', $user->id);

        return [
            'overall' => round($reviews->avg('overall_rating') ?? 0, 1),
            'total_reviews' => $reviews->count(),
            'distribution' => [
                5 => $reviews->where('overall_rating', 5)->count(),
                4 => $reviews->where('overall_rating', 4)->count(),
                3 => $reviews->where('overall_rating', 3)->count(),
                2 => $reviews->where('overall_rating', 2)->count(),
                1 => $reviews->where('overall_rating', 1)->count(),
            ],
            'detailed' => [
                'communication' => round($reviews->where('communication_rating', '!=', null)->avg('communication_rating') ?? 0, 1),
                'reliability' => round($reviews->where('reliability_rating', '!=', null)->avg('reliability_rating') ?? 0, 1),
                'condition' => round($reviews->where('condition_rating', '!=', null)->avg('condition_rating') ?? 0, 1),
                'value' => round($reviews->where('value_rating', '!=', null)->avg('value_rating') ?? 0, 1),
            ],
        ];
    }
}
