<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\User;
use App\Services\Maintenance\InspectionQrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class InspectionQrCodeTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true, 'email_verified_at' => now()]);
        $this->equipment = Equipment::factory()->create(['name' => 'QR equipment', 'owner_id' => $this->owner->id]);
    }

    public function test_generated_url_is_signed_and_form_is_prefilled(): void
    {
        $url = app(InspectionQrCodeService::class)->signedUrl($this->equipment);
        $this->assertTrue(URL::hasValidSignature(Request::create($url)));

        $this->actingAs($this->owner)->get($url)
            ->assertOk()
            ->assertSee('QR equipment')
            ->assertSee('value="'.$this->equipment->id.'" selected', false);

        $this->actingAs($this->owner)->get(route('technical.equipment.inspection-qr', $this->equipment))
            ->assertOk()->assertHeader('content-type', 'image/svg+xml');
    }

    public function test_tampered_url_is_rejected_and_authentication_is_required(): void
    {
        $url = app(InspectionQrCodeService::class)->signedUrl($this->equipment);
        $tampered = preg_replace('/signature=./', 'signature=x', $url, 1);

        $this->actingAs($this->owner)->get($tampered)->assertForbidden();
        auth()->logout();
        $this->get($url)->assertRedirect(route('login'));
    }
}
