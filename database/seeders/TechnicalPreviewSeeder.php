<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/** Local fixture for manually testing the technical frontend before Equipment is merged. */
class TechnicalPreviewSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('TechnicalPreviewSeeder cannot run in production.');
        }

        // Check if we're using the real Equipment model or the preview alias
        $usingRealModel = is_a(Equipment::class, \App\Models\Equipment::class, true);

        if (! Schema::hasTable('maintenances') || ! Schema::hasTable('maintenance_reports') || ! Schema::hasTable('inspections')) {
            throw new RuntimeException('Run php artisan migrate before TechnicalPreviewSeeder.');
        }

        // Ensure a category exists
        $category = Category::query()->first();
        if (! $category) {
            $category = Category::create([
                'name' => 'Technical Preview',
                'description' => 'Category for technical preview equipment.',
                'status' => 'active',
            ]);
        }

        // Create technical preview owner
        $owner = User::query()->firstOrCreate(
            ['email' => 'technical-preview-owner@solarshare.test'],
            ['name' => 'Technical Preview Owner', 'password' => 'TechnicalPreview2026!', 'role' => UserRole::OWNER],
        );
        $owner->forceFill(['role_setup_completed' => true, 'email_verified_at' => now()])->save();

        // Create technical preview equipment for each owner
        User::query()->where('role', UserRole::OWNER->value)->get()->each(function (User $owner) use ($category): void {
            foreach (['Portable battery 1000 Wh', 'Foldable solar panel 200 W', 'Solar kit 2 x 100 W'] as $name) {
                Equipment::query()->firstOrCreate(
                    ['name' => $name, 'owner_id' => $owner->getKey()],
                    [
                        'category_id' => $category->id,
                        'description' => 'Technical preview equipment for testing.',
                        'brand' => 'Preview',
                        'model' => 'Preview',
                        'price_per_day' => 10,
                        'condition' => 'good',
                        'location' => 'Test',
                        'status' => 'available',
                        'approval_status' => 'published',
                    ],
                );
            }
        });

        $this->call(TechnicalSeeder::class);

        $this->command?->info('Technical preview ready. Sign in as technical-preview-owner@solarshare.test / TechnicalPreview2026!');
    }
}
