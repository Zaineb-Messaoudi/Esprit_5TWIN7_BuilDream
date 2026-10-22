<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(private readonly ReviewService $reviews) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $pending = Rental::query()
            ->where('status', 'completed')
            ->whereDoesntHave('reviews', fn ($query) => $query->where('reviewer_id', $user->id))
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('equipment', fn ($equipment) => $equipment->where('owner_id', $user->id));
            })
            ->with(['equipment.owner', 'user'])
            ->latest('end_date')
            ->get();

        $received = $this->reviews->getUserReviews($user, 10);

        return view('pages.front.reviews', [
            'title' => __('Reviews'),
            'pending' => $pending,
            'received' => $received,
        ]);
    }

    public function create(Request $request, Rental $rental): View
    {
        abort_unless($this->canReview($request, $rental), 403);
        abort_unless($rental->status->value === 'completed', 409);
        abort_if(Review::where('rental_id', $rental->id)->where('reviewer_id', $request->user()->id)->exists(), 409);

        return view('pages.front.review-create', [
            'title' => __('Leave a review'),
            'rental' => $rental->load(['equipment.owner', 'user']),
        ]);
    }

    public function store(Request $request, Rental $rental): RedirectResponse
    {
        abort_unless($this->canReview($request, $rental), 403);
        abort_unless($rental->status->value === 'completed', 409);
        abort_if(Review::where('rental_id', $rental->id)->where('reviewer_id', $request->user()->id)->exists(), 409);

        $data = $request->validate([
            'overall' => ['required', 'integer', 'between:1,5'],
            'communication' => ['nullable', 'integer', 'between:1,5'],
            'reliability' => ['nullable', 'integer', 'between:1,5'],
            'condition' => ['nullable', 'integer', 'between:1,5'],
            'value' => ['nullable', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $isRenter = (int) $rental->user_id === (int) $request->user()->id;
        $reviewee = $isRenter ? $rental->equipment->owner : $rental->user;

        $this->reviews->createReview(
            $rental,
            $request->user(),
            $reviewee,
            $isRenter ? 'renter_to_owner' : 'owner_to_renter',
            [
                'overall' => $data['overall'],
                'communication' => $data['communication'] ?? null,
                'reliability' => $data['reliability'] ?? null,
                'condition' => $data['condition'] ?? null,
                'value' => $data['value'] ?? null,
            ],
            $data['comment'] ?? null,
        );

        return redirect()->route('front.reviews.index')->with('status', __('Review submitted successfully.'));
    }

    private function canReview(Request $request, Rental $rental): bool
    {
        return (int) $rental->user_id === (int) $request->user()->id
            || (int) $rental->equipment?->owner_id === (int) $request->user()->id;
    }
}
