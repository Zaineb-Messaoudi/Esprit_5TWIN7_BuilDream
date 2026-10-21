<?php

use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    if (! Schema::hasTable('equipment')) {
        Schema::create('equipment', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }
});

test('buyers are denied technical pages and owners only see their own records', function () {
    $owner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true]);
    $otherOwner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true]);
    $buyer = User::factory()->create(['role' => UserRole::BUYER, 'role_setup_completed' => true]);

    $ownedEquipment = Equipment::factory()->create(['name' => 'Owner equipment', 'owner_id' => $owner->id]);
    $otherEquipment = Equipment::factory()->create(['name' => 'Other equipment', 'owner_id' => $otherOwner->id]);
    $ownedMaintenance = Maintenance::factory()->create(['equipment_id' => $ownedEquipment->id]);
    $otherMaintenance = Maintenance::factory()->create(['equipment_id' => $otherEquipment->id]);

    $this->actingAs($buyer)->get(route('technical.maintenances.index'))->assertForbidden();

    $this->actingAs($owner)
        ->get(route('technical.maintenances.index'))
        ->assertOk()
        ->assertSee('Owner equipment')
        ->assertDontSee('Other equipment');

    $this->actingAs($owner)->get(route('technical.maintenances.show', $otherMaintenance))->assertNotFound();
    $this->actingAs($owner)->get(route('technical.maintenances.show', $ownedMaintenance))->assertOk();
});

