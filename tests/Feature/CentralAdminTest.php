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

class CentralAdminTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic test example.
     */

    public function test_view_central_admins_list_screen(): void
    {
        $user = User::factory()->create();
        $roles = Role::all();
        $attestation = Attestation::factory()->create();
        $user->roles()->saveMany($roles);
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->get('/central_administrators/list');

        $response->assertStatus(200);
    }

    public function test_add_central_admins(): void
    {
        $user = User::factory()->create();
        $roles = Role::all();
        $attestation = Attestation::factory()->create();
        $user->roles()->saveMany($roles);
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->post('/central_administrators/store', [
            'user_id' => $user->id,
            'organisation_id' => $user->organisation_id
        ]);

        $response->assertRedirect(route('central_administrators.list'));
    }

    public function test_delete_central_admins(): void
    {
        $user = User::factory()->create();
        $roles = Role::all();
        $attestation = Attestation::factory()->create();
        $user->roles()->saveMany($roles);
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->get('/central_administrators/delete/'.$user->id);

        $response->assertRedirect(route('central_administrators.list'));
    }
}
