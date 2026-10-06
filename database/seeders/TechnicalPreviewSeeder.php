<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\User;
use App\Support\TechnicalPreviewEquipment;
use Illuminate\Database\Schema\Blueprint;
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

        if (! is_a(Equipment::class, TechnicalPreviewEquipment::class, true)) {
            throw new RuntimeException('The real Equipment model is installed. Use TechnicalSeeder with real equipment instead.');
        }

        if (! Schema::hasTable('maintenances') || ! Schema::hasTable('maintenance_reports') || ! Schema::hasTable('inspections')) {
            throw new RuntimeException('Run php artisan migrate before TechnicalPreviewSeeder.');
        }

        if (! Schema::hasTable('equipment')) {
            Schema::create('equipment', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
                $table->boolean('technical_preview')->default(true);
                $table->timestamps();
            });
        } elseif (! Schema::hasColumn('equipment', 'technical_preview')) {
            throw new RuntimeException('An equipment table already exists and is not a technical preview fixture. No sample equipment was inserted.');
        }

        $owner = User::query()->firstOrCreate(
            ['email' => 'technical-preview-owner@solarshare.test'],
            ['name' => 'Technical Preview Owner', 'password' => 'TechnicalPreview2026!', 'role' => UserRole::OWNER],
        );
        $owner->forceFill(['role_setup_completed' => true, 'email_verified_at' => now()])->save();

        User::query()->where('role', UserRole::OWNER->value)->get()->each(function (User $owner): void {
            foreach (['Portable battery 1000 Wh', 'Foldable solar panel 200 W', 'Solar kit 2 x 100 W'] as $name) {
                Equipment::query()->firstOrCreate(
                    ['name' => $name, 'owner_id' => $owner->getKey()],
                    ['technical_preview' => true],
                );
            }
        });

        $this->call(TechnicalSeeder::class);

        $this->command?->info('Technical preview ready. Sign in as technical-preview-owner@solarshare.test / TechnicalPreview2026!');
    }
}
