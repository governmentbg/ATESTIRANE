<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\User;
use App\Models\Role;
use App\Models\Organisation;
use App\Models\Attestation;

use Database\Seeders\RoleSeeder;
use Database\Seeders\OrganisationsSeeder;

class AttestationTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic test example.
     */

    public function test_view_attestation_add_screen(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->get('/attestation/add');

        $response->assertStatus(200);
    }

    public function test_new_attestation_with_period(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->post('/attestation/update', [
            'period_from' => '2023-12-01',
            'period_to' => '2024-11-30',
            'management_form_version' => 1
        ]);

        $response->assertRedirect(route('dashboard'));
    }

    public function test_new_attestation_validation(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->post('/attestation/update', [
            'period_from' => '2023-12-01',
            'management_form_version' => 1
        ]);

        $response->assertStatus(302);
    }
}