<?php

namespace App\Http\Controllers;

use App\Support\FrontDemo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/** Renders any page declared in config/front.php (section-based pages). */
class PageController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $slug = $request->route('slug');
        $page = config("front.pages.$slug");
        abort_unless($page, 404);

        if (in_array($slug, ['reserve', 'booking-summary', 'payment', 'confirmed', 'invoice'], true)) {
            $equipmentId = $request->integer('equipment', 1);
            $equipment = FrontDemo::equipment()->firstWhere('id', $equipmentId);
            abort_unless($equipment, 404);

            return view('pages.front.booking-flow', [
                'title' => __($page['title']),
                'slug' => $slug,
                'item' => $equipment,
                'startDate' => $request->query('start', ''),
                'endDate' => $request->query('end', ''),
            ]);
        }

        if (($page['role'] ?? null) === 'owner') {
            abort_unless($request->user()?->isOwner(), 403);
        } elseif (($page['role'] ?? null) === 'buyer') {
            abort_unless($request->user()?->isBuyer(), 403);
        }

        if (in_array($slug, ['my-maintenance', 'my-inspections'], true)) {
            $ready = class_exists(\App\Models\Equipment::class)
                && Schema::hasTable('equipment')
                && Schema::hasColumn('equipment', 'owner_id')
                && Schema::hasTable('maintenances')
                && Schema::hasTable('maintenance_reports')
                && Schema::hasTable('inspections');

            if (! $ready) {
                return view('pages.front.technical-pending', [
                    'title' => __($page['title']),
                ]);
            }

            return redirect()->route($slug === 'my-maintenance'
                ? 'technical.maintenances.index'
                : 'technical.inspections.index');
        }

        if (in_array($slug, ['my-dashboard', 'owner-dashboard'], true)) {
            return view('pages.front.workspace-dashboard', [
                'title' => __($page['title']),
                'owner' => $request->user()->isOwner(),
                'user' => $request->user(),
            ]);
        }

        if ($request->user()?->isBuyer() && in_array($slug, [
            'my-reservations',
            'buyer-reservation-detail',
            'my-rentals',
            'buyer-rental-detail',
            'my-contract',
            'my-extensions',
            'my-payments',
            'my-notifications',
        ], true)) {
            return view('pages.front.buyer-workspace', [
                'title' => __($page['title']),
                'user' => $request->user(),
                'slug' => $slug,
            ]);
        }

        if ($request->user()?->isOwner() && in_array($slug, [
            'my-equipment',
            'my-publish',
            'my-equipment-detail',
            'my-equipment-edit',
            'my-reservations',
            'my-reservation-detail',
            'my-rentals',
            'my-rental-detail',
            'my-contract',
            'my-extensions',
            'my-inspections',
            'my-calendar',
            'my-earnings',
            'my-maintenance',
            'my-notifications',
        ], true)) {
            return view('pages.front.owner-workspace', [
                'title' => __($page['title']),
                'user' => $request->user(),
                'slug' => $slug,
            ]);
        }

        return view('pages.front.page', [
            'title' => $page['title'],
            'page' => $page,
            'slug' => $slug,
        ]);
    }
}
