<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberOneLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_one_public_pages_use_the_french_catalogue_copy(): void
    {
        foreach ([
            ['front.catalog', 'Catalogue'],
            ['front.energy-calculator', 'Calculateur énergétique'],
            ['front.equipment.compare', 'Comparer les équipements'],
            ['front.equipment.map', 'Carte des équipements'],
        ] as [$route, $expected]) {
            $this->withSession(['locale' => 'fr'])
                ->get(route($route))
                ->assertOk()
                ->assertSee('<html lang="fr"', false)
                ->assertSee($expected);
        }
    }

    public function test_member_one_owner_and_admin_forms_keep_localized_system_labels(): void
    {
        $owner = User::factory()->create(['role' => UserRole::OWNER, 'role_setup_completed' => true, 'email_verified_at' => now()]);
        $this->withSession(['locale' => 'fr'])
            ->actingAs($owner)
            ->get(route('front.my-publish'))
            ->assertOk()
            ->assertSee('Ajouter des images à la galerie')
            ->assertSee('Profil énergétique');

        $admin = User::factory()->create(['role' => UserRole::ADMIN, 'role_setup_completed' => true, 'email_verified_at' => now()]);
        $this->withSession(['locale' => 'fr'])
            ->actingAs($admin)
            ->get(route('admin.equipment.create'))
            ->assertOk()
            ->assertSee('Ajouter des images à la galerie');
    }

    public function test_english_and_french_translation_files_have_the_same_keys(): void
    {
        $english = json_decode(file_get_contents(base_path('lang/en.json')), true, 512, JSON_THROW_ON_ERROR);
        $french = json_decode(file_get_contents(base_path('lang/fr.json')), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame([], array_diff_key($english, $french));
    }
}
