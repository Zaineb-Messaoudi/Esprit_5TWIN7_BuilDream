<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\Maintenance;
use App\Models\MaintenancePhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MaintenancePhotoTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Maintenance $maintenance;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->owner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true, 'email_verified_at' => now()]);
        $equipment = Equipment::query()->create(['name' => 'Repairable battery', 'owner_id' => $this->owner->id]);
        $this->maintenance = Maintenance::factory()->create(['equipment_id' => $equipment->id]);
    }

    public function test_valid_photo_is_stored_and_associated_with_maintenance(): void
    {
        $response = $this->actingAs($this->owner)->post(route('technical.maintenances.photos.store', $this->maintenance), [
            'type' => 'before',
            'photo' => $this->fakePng('before.png'),
        ]);

        $response->assertSessionHasNoErrors();
        $photo = MaintenancePhoto::query()->firstOrFail();
        $this->assertSame($this->maintenance->id, $photo->maintenance_id);
        $this->assertSame('before', $photo->type);
        Storage::disk('public')->assertExists($photo->path);
    }

    public function test_non_image_and_oversized_files_are_rejected(): void
    {
        $this->actingAs($this->owner)->post(route('technical.maintenances.photos.store', $this->maintenance), [
            'type' => 'before',
            'photo' => UploadedFile::fake()->create('payload.txt', 10, 'text/plain'),
        ])->assertSessionHasErrors('photo');

        $this->actingAs($this->owner)->post(route('technical.maintenances.photos.store', $this->maintenance), [
            'type' => 'after',
            'photo' => $this->fakePng('after.png', 5121),
        ])->assertSessionHasErrors('photo');

        $this->assertSame(0, MaintenancePhoto::query()->count());
    }

    public function test_deleting_photo_removes_database_row_and_file(): void
    {
        $path = $this->fakePng('before.png')->store("maintenance/{$this->maintenance->id}", 'public');
        $photo = MaintenancePhoto::factory()->create(['maintenance_id' => $this->maintenance->id, 'path' => $path]);

        $this->actingAs($this->owner)->delete(route('technical.maintenances.photos.destroy', [$this->maintenance, $photo]))->assertSessionHasNoErrors();

        $this->assertModelMissing($photo);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_another_owner_cannot_upload_or_delete_photos(): void
    {
        $otherOwner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true, 'email_verified_at' => now()]);

        $this->actingAs($otherOwner)->post(route('technical.maintenances.photos.store', $this->maintenance), [
            'type' => 'before',
            'photo' => $this->fakePng('before.png'),
        ])->assertForbidden();
    }

    private function fakePng(string $name, int $kilobytes = 1): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9Zl1sAAAAASUVORK5CYII=', true);
        $content = $png.str_repeat("\0", max(0, ($kilobytes * 1024) - strlen($png)));

        return UploadedFile::fake()->createWithContent($name, $content);
    }
}

