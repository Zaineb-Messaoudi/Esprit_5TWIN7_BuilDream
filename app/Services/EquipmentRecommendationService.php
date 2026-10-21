<?php

namespace App\Services;

use App\Enums\RentalStatus;
use App\Models\Equipment;

class EquipmentRecommendationService
{
    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     owner: string,
     *     category: string,
     *     location: string,
     *     price_per_day: float,
     *     rating: ?float,
     *     reviews_count: int,
     *     score: int,
     *     reasons: list<string>,
     *     url: string,
     *     reservation_url: string
     * }>
     */
    public function recommend(
        int $categoryId,
        ?string $location,
        ?float $maxDailyBudget,
        string $startDate,
        string $endDate
    ): array {
        $location = filled($location) ? trim($location) : null;

        $equipment = Equipment::query()
            ->published()
            ->where('category_id', $categoryId)
            ->whereHas('owner', fn ($query) => $query->where('role', 'owner'))
            ->with(['category', 'owner'])
            ->whereDoesntHave('reservations', fn ($query) => $query
                ->where('status', '!=', 'cancelled')
                ->where('start_date', '<=', $endDate)
                ->where('end_date', '>=', $startDate))
            ->whereDoesntHave('rentals', fn ($query) => $query
                ->whereNotIn('status', [RentalStatus::CANCELLED->value, RentalStatus::COMPLETED->value])
                ->where('start_date', '<=', $endDate)
                ->where('end_date', '>=', $startDate))
            ->when($location, fn ($query, string $city) => $query->where('location', 'like', '%'.$city.'%'))
            ->when($maxDailyBudget !== null, fn ($query) => $query->where('price_per_day', '<=', $maxDailyBudget))
            ->get();

        return $equipment
            ->map(fn (Equipment $item): array => $this->toRecommendation(
                $item,
                $location,
                $maxDailyBudget,
                $startDate,
                $endDate
            ))
            ->sortBy([
                ['score', 'desc'],
                ['rating', 'desc'],
                ['reviews_count', 'desc'],
                ['price_per_day', 'asc'],
            ])
            ->take(5)
            ->values()
            ->all();
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     owner: string,
     *     category: string,
     *     location: string,
     *     price_per_day: float,
     *     rating: ?float,
     *     reviews_count: int,
     *     score: int,
     *     reasons: list<string>,
     *     url: string,
     *     reservation_url: string
     * }
     */
    private function toRecommendation(
        Equipment $item,
        ?string $location,
        ?float $maxDailyBudget,
        string $startDate,
        string $endDate
    ): array {
        $owner = $item->owner;
        $rating = $owner->aggregate_rating === null ? null : (float) $owner->aggregate_rating;
        $reviewsCount = (int) ($owner->reviews_count ?? 0);
        $score = 60;
        $maximumScore = 60;
        $reasons = [__('Matches the selected equipment category')];

        if ($location !== null) {
            $score += 20;
            $maximumScore += 20;
            $reasons[] = __('Available near :city', ['city' => $location]);
        }

        if ($maxDailyBudget !== null) {
            $affordability = max(0, 1 - ((float) $item->price_per_day / $maxDailyBudget));
            $score += (int) round($affordability * 10);
            $maximumScore += 10;
            $reasons[] = __('Within your daily budget');
        }

        $maximumScore += 10;
        $score += (int) round(($this->confidenceAdjustedRating($rating, $reviewsCount) / 5) * 10);
        if ($rating !== null && $reviewsCount > 0) {
            $reasons[] = __('Rated :rating/5 from :count review(s)', [
                'rating' => number_format($rating, 1),
                'count' => $reviewsCount,
            ]);
        } else {
            $reasons[] = __('New to the platform: not enough public reviews yet');
        }

        $reasons[] = __('Available for your selected dates');

        return [
            'id' => $item->id,
            'name' => $item->name,
            'owner' => $owner->name,
            'category' => __($item->category->name),
            'location' => $item->location,
            'price_per_day' => (float) $item->price_per_day,
            'rating' => $rating,
            'reviews_count' => $reviewsCount,
            'score' => (int) round(($score / $maximumScore) * 100),
            'reasons' => $reasons,
            'url' => route('front.equipment.show', $item->id),
            'reservation_url' => route('front.reserve', [
                'equipment' => $item->id,
                'start' => $startDate,
                'end' => $endDate,
            ]),
        ];
    }

    private function confidenceAdjustedRating(?float $rating, int $reviewsCount): float
    {
        $priorReviews = 3;
        $priorRating = 3.5;

        return (($reviewsCount * ($rating ?? 0)) + ($priorReviews * $priorRating))
            / ($reviewsCount + $priorReviews);
    }
}
