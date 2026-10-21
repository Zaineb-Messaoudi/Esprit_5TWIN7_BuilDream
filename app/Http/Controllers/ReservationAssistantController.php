<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ConsentRecord;
use App\Models\ReservationAssistantRequest;
use App\Services\AiRequestParser;
use App\Services\EquipmentRecommendationService;
use App\Services\ReservationAssistantService;
use App\Services\ServiceProviderRecommendationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReservationAssistantController extends Controller
{
    public function index(Request $request, ReservationAssistantService $assistant): View
    {
        return view('pages.front.reservation-assistant', [
            'title' => __('Reservation assistant'),
            'aiEnabled' => app(AiRequestParser::class)->enabled(),
            'serviceTypes' => $assistant->serviceTypes(),
            'categories' => Category::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'requests' => ReservationAssistantRequest::query()
                ->where('user_id', $request->user()->id)
                ->latest()
                ->limit(5)
                ->get(),
            'recommendationRequest' => ReservationAssistantRequest::query()
                ->where('user_id', $request->user()->id)
                ->where('reference', $request->session()->get('requestReference'))
                ->first(),
        ]);
    }

    public function analyze(Request $request, AiRequestParser $parser): JsonResponse
    {
        $rules = [
            'description' => ['required', 'string', 'min:5', 'max:1500'],
        ];

        if ($parser->enabled()) {
            $rules['external_ai_consent'] = ['required', 'accepted'];
        }

        $validated = $request->validate($rules);

        if ($parser->enabled()) {
            ConsentRecord::create([
                'user_id' => $request->user()->id,
                'consent_type' => 'third_party_ai_processing',
                'granted' => true,
                'version' => 'groq-request-parser-v1',
                'consent_text' => __('I agree that my project description may be sent to Groq for analysis.'),
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
                'granted_at' => now(),
            ]);
        }

        return response()->json($parser->analyze($validated['description']));
    }

    public function recommend(Request $request, EquipmentRecommendationService $recommendations): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('status', 'active')],
            'location' => ['nullable', 'string', 'max:100'],
            'max_daily_budget' => ['nullable', 'numeric', 'gt:0', 'max:999999999'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $matches = $recommendations->recommend(
            (int) $validated['category_id'],
            $validated['location'] ?? null,
            isset($validated['max_daily_budget']) ? (float) $validated['max_daily_budget'] : null,
            $validated['start_date'],
            $validated['end_date']
        );

        return response()->json([
            'recommendations' => $matches,
            'message' => $matches === []
                ? __('No equipment matched all your criteria for those dates. Try another city, budget, category, or period.')
                : __('Recommendations are ranked by category, location, budget, availability, and review history. You make the final choice.'),
        ]);
    }

    public function recommendServiceProviders(
        Request $request,
        ReservationAssistantRequest $workRequest,
        ServiceProviderRecommendationService $recommendations
    ): JsonResponse {
        abort_unless($workRequest->user_id === $request->user()->id, 404);

        $matches = $recommendations->recommend($workRequest);

        return response()->json([
            'recommendations' => $matches,
            'message' => $matches === []
                ? __('No artisan profile matches this request’s service, city, budget, and declared availability. You can edit the request or ask for a custom quote.')
                : __('These artisans match your request based on their declared service area and availability. Ratings are marketplace-wide, not specific to renovation work. You choose whom to contact; no reservation is made.'),
        ]);
    }

    public function store(Request $request, ReservationAssistantService $assistant): RedirectResponse
    {
        $serviceTypes = array_keys($assistant->serviceTypes());
        $data = $request->validate([
            'service_type' => ['required', Rule::in([...$serviceTypes, 'custom_quote'])],
            'custom_description' => ['required_if:service_type,custom_quote', 'nullable', 'string', 'max:2000'],
            'surface_m2' => ['nullable', 'numeric', 'gt:0', 'max:100000'],
            'urgency' => ['required', Rule::in(['urgent', 'soon', 'flexible'])],
            'budget' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'confirmed' => ['required', 'accepted'],
        ]);
        unset($data['confirmed']);

        $workRequest = ReservationAssistantRequest::create([
            ...$data,
            'reference' => 'DEV-'.Str::upper(Str::random(8)),
            'user_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        return redirect()->route('front.reservation-assistant')
            ->with('requestReference', $workRequest->reference);
    }
}
