<?php

namespace App\Http\Controllers;

use App\Services\ReservationAssistantService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceProviderProfileController extends Controller
{
    public function edit(Request $request, ReservationAssistantService $assistant): View
    {
        return view('pages.front.service-provider-profile', [
            'profile' => $request->user()->serviceProviderProfile,
            'serviceTypes' => $assistant->serviceTypes(),
        ]);
    }

    public function update(Request $request, ReservationAssistantService $assistant): RedirectResponse
    {
        $serviceTypeKeys = array_keys($assistant->serviceTypes());
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1500'],
            'specialties' => ['required', 'array', 'min:1'],
            'specialties.*' => ['required', 'string', 'distinct', Rule::in($serviceTypeKeys)],
            'service_areas' => ['required', 'string', 'max:1000'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'minimum_project_budget' => ['nullable', 'numeric', 'gt:0', 'max:999999999'],
            'available_from' => [
                'exclude_unless:accepting_requests,1',
                Rule::requiredIf($request->boolean('accepting_requests')),
                'nullable',
                'date',
                'after_or_equal:today',
            ],
            'accepting_requests' => ['nullable', 'boolean'],
            'contact_email' => ['nullable', 'email', 'max:255'],
        ]);

        $areas = array_values(array_unique(array_filter(array_map(
            'trim',
            preg_split('/[,;\r\n]+/', $data['service_areas']) ?: []
        ), fn (string $area): bool => $area !== ''), SORT_STRING));

        if ($areas === [] || count(array_filter($areas, fn (string $area): bool => mb_strlen($area) > 100)) > 0) {
            throw ValidationException::withMessages([
                'service_areas' => __('Enter at least one service area, with each location no longer than 100 characters.'),
            ]);
        }

        $areas = collect($areas)->unique(fn (string $area): string => Str::lower(Str::ascii($area)))->values()->all();
        $data['service_areas'] = $areas;
        $data['accepting_requests'] = $request->boolean('accepting_requests');
        $data['available_from'] = $data['accepting_requests'] ? $data['available_from'] : null;

        $request->user()->serviceProviderProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $data
        );

        return redirect()->route('front.owner-service-provider-profile')
            ->with('status', __('Your artisan profile has been saved.'));
    }
}
