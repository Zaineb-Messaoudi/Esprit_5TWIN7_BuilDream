<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreReviewRequest;
use App\Models\Rental;
use App\Models\Review;
use App\Models\ReviewDispute;
use App\Models\User;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Reviews API controller for buyers and owners.
 */
class ReviewController
{
    public function __construct(private readonly ReviewService $service) {}

    /**
     * Get all reviews for the authenticated user (received).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $reviews = $this->service->getUserReviews($user, $request->integer('per_page', 10));

        return response()->json($reviews);
    }

    /**
     * Store a new review for a completed rental.
     */
    public function store(StoreReviewRequest $request, Rental $rental): JsonResponse
    {
        $user = $request->user();

        // Verify rental belongs to user or equipment owner
        $isRenter = $rental->user_id === $user->id;
        $isOwner = $rental->equipment->owner_id === $user->id;

        abort_unless($isRenter || $isOwner, 403, 'You can only review rentals you participated in.');
        abort_unless($rental->status === 'completed', 409, 'Only completed rentals can be reviewed.');

        // Check if already reviewed by this user
        $existing = Review::where('rental_id', $rental->id)
            ->where('reviewer_id', $user->id)
            ->first();

        abort_if($existing, 409, 'You have already reviewed this rental.');

        $type = $isRenter ? 'renter_to_owner' : 'owner_to_renter';
        $reviewee = $isRenter ? $rental->equipment->owner : $rental->user;

        $review = $this->service->createReview(
            $rental,
            $user,
            $reviewee,
            $type,
            $request->validated('ratings'),
            $request->validated('comment'),
            $request->validated('photos')
        );

        return response()->json([
            'message' => 'Review submitted successfully.',
            'review' => $review->load('rental.equipment'),
        ], 201);
    }

    /**
     * Get pending reviews for the authenticated user.
     */
    public function pending(Request $request): JsonResponse
    {
        $user = $request->user();
        $pending = $this->service->getPendingReviews($request->user())
            ->map(function ($rental) {
                return [
                    'rental_id' => $rental->id,
                    'rental_reference' => $rental->reference,
                    'equipment' => [
                        'id' => $rental->equipment->id,
                        'name' => $rental->equipment->name,
                        'image_url' => $rental->equipment->image_url,
                    ],
                    'owner' => [
                        'id' => $rental->equipment->owner->id,
                        'name' => $rental->equipment->owner->name,
                    ],
                    'start_date' => $rental->start_date->toDateString(),
                    'end_date' => $rental->end_date->toDateString(),
                ];
            });

        return response()->json($pending);
    }

    /**
     * Get rating breakdown for a user.
     */
    public function breakdown(Request $request, User $user): JsonResponse
    {
        $breakdown = $this->service->getUserRatingBreakdown($user);

        return response()->json($breakdown);
    }

    /**
     * Get single review details.
     */
    public function show(Review $review): JsonResponse
    {
        abort_unless(
            $review->is_public &&
            ($review->reviewee_id === request()->user()->id || $review->reviewer_id === request()->user()->id),
            403
        );

        return response()->json($review->load(['reviewer:id,name,profile_photo_path', 'reviewee:id,name', 'rental.equipment', 'responses.user']));
    }

    /**
     * Dispute a review.
     */
    public function dispute(Request $request, Review $review): JsonResponse
    {
        $user = $request->user();

        // Check if user is involved in the rental
        $rental = $review->rental;
        abort_unless(
            in_array($user->id, [$rental->user_id, $rental->equipment->owner_id]),
            403,
            'You can only dispute reviews for rentals you participated in.'
        );

        $validated = $request->validate([
            'reason' => ['required', 'in:inaccurate,harassment,fake,irrelevant,other'],
            'description' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $dispute = $this->service->createDispute($review, $user, $validated['reason'], $validated['description']);

            return response()->json([
                'message' => 'Dispute filed successfully.',
                'dispute' => $dispute,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    /**
     * Respond to a review (reviewee only).
     */
    public function respond(Request $request, Review $review): JsonResponse
    {
        $user = $request->user();

        // Only the reviewee can respond
        abort_unless($review->reviewee_id === $user->id, 403);

        $validated = $request->validate([
            'response' => ['required', 'string', 'max:2000'],
        ]);

        $response = \App\Models\ReviewResponse::create([
            'review_id' => $review->id,
            'user_id' => $user->id,
            'response' => $validated['response'],
        ]);

        return response()->json([
            'message' => 'Response posted successfully.',
            'response' => $response->load('user:id,name,profile_photo_path'),
        ], 201);
    }

    /**
     * Admin: List all disputes.
     */
    public function disputes(Request $request): JsonResponse
    {
        $disputes = ReviewDispute::query()
            ->with(['review.rental.equipment', 'initiator', 'resolvedBy'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return response()->json($disputes);
    }

    /**
     * Admin: Resolve a dispute.
     */
    public function resolveDispute(Request $request, ReviewDispute $dispute): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:resolved,dismissed'],
            'resolution' => ['nullable', 'string', 'max:2000'],
        ]);

        $dispute = $this->service->resolveDispute($dispute, request()->user(), $validated['status'], $validated['resolution']);

        return response()->json([
            'message' => 'Dispute resolved.',
            'dispute' => $dispute->load('review'),
        ]);
    }
}
