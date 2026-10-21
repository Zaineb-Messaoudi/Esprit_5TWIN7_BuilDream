<?php

namespace App\Services;

use App\Models\ReservationAssistantRequest;
use App\Models\ServiceProviderProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ServiceProviderRecommendationService
{
    /**
     * @return list<array{
     *     id: int,
     *     business_name: string,
     *     name: string,
     *     specialty: string,
     *     service_areas: list<string>,
     *     experience_years: int,
     *     minimum_project_budget: ?float,
     *     available_from: string,
     *     rating: ?float,
     *     reviews_count: int,
     *     score: int,
     *     reasons: list<string>,
     *     contact_email: ?string
     * }>
     */
    public function recommend(ReservationAssistantRequest $workRequest): array
    {
        $deadline = $this->availabilityDeadline($workRequest);
        $city = $this->normalize((string) $workRequest->city);
        $serviceLabel = app(ReservationAssistantService::class)
            ->serviceTypes()[$workRequest->service_type]['label'] ?? null;

        if ($serviceLabel === null) {
            return [];
        }

        return ServiceProviderProfile::query()
            ->where('accepting_requests', true)
            ->whereJsonContains('specialties', $workRequest->service_type)
            ->whereDate('available_from', '<=', $deadline->toDateString())
            ->whereHas('user', fn ($query) => $query->where('role', 'owner'))
            ->with('user:id,name,aggregate_rating,reviews_count')
            ->get()
            ->filter(fn (ServiceProviderProfile $profile): bool => $this->servesCity($profile, $city))
            ->filter(fn (ServiceProviderProfile $profile): bool => $this->fitsBudget($profile, (float) $workRequest->budget))
            ->map(fn (ServiceProviderProfile $profile): array => $this->toRecommendation($profile, $workRequest, $serviceLabel))
            ->sortBy([
                ['score', 'desc'],
                ['rating', 'desc'],
                ['reviews_count', 'desc'],
                ['experience_years', 'desc'],
            ])
            ->take(5)
            ->values()
            ->all();
    }

    private function availabilityDeadline(ReservationAssistantRequest $workRequest): Carbon
    {
        $days = match ($workRequest->urgency) {
            'urgent' => 0,
            'soon' => 14,
            default => 60,
        };

        return Carbon::parse($workRequest->created_at)->startOfDay()->addDays($days);
    }

    private function normalize(string $value): string
    {
        return Str::lower(trim(Str::ascii($value)));
    }

    private function servesCity(ServiceProviderProfile $profile, string $city): bool
    {
        return collect($profile->service_areas)->contains(
            fn (string $area): bool => $this->normalize($area) === $city
        );
    }

    private function fitsBudget(ServiceProviderProfile $profile, float $budget): bool
    {
        return $profile->minimum_project_budget === null
            || (float) $profile->minimum_project_budget <= $budget;
    }

    /**
     * @return array{
     *     id: int,
     *     business_name: string,
     *     name: string,
     *     specialty: string,
     *     service_areas: list<string>,
     *     experience_years: int,
     *     minimum_project_budget: ?float,
     *     available_from: string,
     *     rating: ?float,
     *     reviews_count: int,
     *     score: int,
     *     reasons: list<string>,
     *     contact_email: ?string
     * }
     */
    private function toRecommendation(
        ServiceProviderProfile $profile,
        ReservationAssistantRequest $workRequest,
        string $serviceLabel
    ): array {
        $user = $profile->user;
        $rating = $user->aggregate_rating === null ? null : (float) $user->aggregate_rating;
        $reviewsCount = (int) $user->reviews_count;
        $experienceScore = min(5, $profile->experience_years);
        $ratingScore = (int) round(($this->confidenceAdjustedRating($rating, $reviewsCount) / 5) * 10);
        $budgetScore = $profile->minimum_project_budget === null
            ? 5
            : (int) round(max(0, 1 - ((float) $profile->minimum_project_budget / max((float) $workRequest->budget, 1))) * 15);
        $score = 30 + 25 + 15 + $experienceScore + $ratingScore + $budgetScore;
        $reasons = [
            __('Qualified for :service', ['service' => $serviceLabel]),
            __('Covers :city', ['city' => $workRequest->city]),
            __('Available from :date', ['date' => $profile->available_from->format('d/m/Y')]),
        ];

        if ($profile->minimum_project_budget === null) {
            $reasons[] = __('Starting price not provided; confirm the quote with the provider');
        } else {
            $reasons[] = __('Starting from :amount TND; within your budget', [
                'amount' => number_format((float) $profile->minimum_project_budget, 2),
            ]);
        }

        $reasons[] = __(':years year(s) of experience', ['years' => $profile->experience_years]);

        if ($rating !== null && $reviewsCount > 0) {
            $reasons[] = __('Platform customer rating: :rating/5 (:count review(s))', [
                'rating' => number_format($rating, 1),
                'count' => $reviewsCount,
            ]);
        } else {
            $reasons[] = __('No public customer reviews yet');
        }

        return [
            'id' => $profile->id,
            'business_name' => $profile->business_name,
            'name' => $user->name,
            'specialty' => $serviceLabel,
            'service_areas' => $profile->service_areas,
            'experience_years' => $profile->experience_years,
            'minimum_project_budget' => $profile->minimum_project_budget === null
                ? null : (float) $profile->minimum_project_budget,
            'available_from' => $profile->available_from->toDateString(),
            'rating' => $rating,
            'reviews_count' => $reviewsCount,
            'score' => $score,
            'reasons' => $reasons,
            'contact_email' => $profile->contact_email,
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
