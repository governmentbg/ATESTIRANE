<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\User;
use App\Models\Role;
use App\Models\Attestation;

class AuthenticationTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_via_kep_data()
    {
        $user = User::factory()->create();
 
        $response = $this->post('/login', [
            'SSL_CLIENT_S_DN' => 'C=BG,CN=TEST TESTOV,serialNumber=PNOBG-1111111111,emailAddress=test@test.com'
        ]);
 
        $this->assertAuthenticated();
    }

    public function test_users_can_view_choose_role_screen(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/choose_role');

        $response->assertStatus(200);
    }

    public function test_users_choose_role_1(): void
    {
        $user = User::factory()->create();
        $roles = Role::all();
        $attestation = Attestation::factory()->create();

        $user->roles()->saveMany($roles);
        $this->actingAs($user);
        
        $response = $this->post('/choose_role', [
            'role' => 1
        ]);

        $response->assertRedirect(route('dashboard'));
    }
    public function test_users_choose_role_2(): void
    {
        $user = User::factory()->create();
        $roles = Role::all();
        $attestation = Attestation::factory()->create();

        $user->roles()->saveMany($roles);
        $this->actingAs($user);
        
        $response = $this->post('/choose_role', [
            'role' => 2
        ]);

        $response->assertRedirect(route('dashboard'));
    }
    public function test_users_choose_role_3(): void
    {
        $user = User::factory()->create();
        $roles = Role::all();
        $attestation = Attestation::factory()->create();

        $user->roles()->saveMany($roles);
        $this->actingAs($user);
        
        $response = $this->post('/choose_role', [
            'role' => 3
        ]);

        $response->assertRedirect(route('attestationforms.list'));
    }
    public function test_users_choose_role_4(): void
    {
        $user = User::factory()->create();
        $roles = Role::all();
        $attestation = Attestation::factory()->create();

        $user->roles()->saveMany($roles);
        $this->actingAs($user);
        
        $response = $this->post('/choose_role', [
            'role' => 4
        ]);

        $response->assertRedirect(route('attestationforms.start'));
    }
    public function test_users_choose_role_5(): void
    {
        $user = User::factory()->create();
        $roles = Role::all();
        $attestation = Attestation::factory()->create();

        $user->roles()->saveMany($roles);
        $this->actingAs($user);
        
        $response = $this->post('/choose_role', [
            'role' => 5
        ]);

        $response->assertRedirect(route('attestationforms.list'));
    }

    public function test_users_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->post('/logout');

        $response->assertRedirect(route('login'));
    }
}
