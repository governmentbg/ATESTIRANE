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

class LogsTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic test example.
     */

    public function test_logs_screen(): void
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
        
        $response = $this->get('/logs/list');

        $response->assertStatus(200);
    }
}
