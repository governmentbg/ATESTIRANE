<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\User;
use App\Models\Role;
use App\Models\Organisation;
use App\Models\Attestation;
use App\Models\Position;

use Database\Seeders\RoleSeeder;
use Database\Seeders\OrganisationsSeeder;

class ManagePositionTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic test example.
     */

    public function test_view_position_types_screen(): void
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
        
        $response = $this->get('/positions/types');

        $response->assertStatus(200);
    }

    public function test_view_specific_position_type_list(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);

        $types = ['management', 'experts', 'general', 'technical', 'specific'];
        
        $response = $this->get('/positions/types/'.$types[rand(0,4)]);

        $response->assertStatus(200);
    }

    public function test_view_specific_position_edit_screen(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);

        $position = Position::first();
        
        $response = $this->get('/positions/edit/'.$position->id);

        $response->assertStatus(200);
    }

    public function test_add_position(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);

        $types = ['management', 'experts', 'general', 'technical', 'specific'];

        $type = $types[rand(0,4)];
        
        $response = $this->post('/positions/update', [
            'name' => 'Тестова позиция',
            'type' => $type,
            'nkpd1' => '0000',
            'nkpd2' => '0000'
        ]);

        $response->assertRedirect(route('positions.list', $type));
    }

    public function test_update_position(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $types = ['management', 'experts', 'general', 'technical', 'specific'];

        $position = Position::first();
        
        $response = $this->post('/positions/update', [
            'id' => $position->id,
            'name' => 'Тестова позиция',
            'type' => $position->type,
            'nkpd1' => '0000',
            'nkpd2' => '0000'
        ]);

        $response->assertRedirect(route('positions.list', $position->type));
    }

    public function test_add_update_position_validation(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);

        $types = ['management', 'experts', 'general', 'technical', 'specific'];

        $type = $types[rand(0,4)];
        
        $response = $this->post('/positions/update', [
            'name' => 'Тестова позиция',
            'type' => $type,
            'nkpd2' => '0000'
        ]);

        $response->assertSessionHasErrors('nkpd1')->assertStatus(302);;
    }

    public function test_position_delete(): void
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

        $position = Position::first();
        
        $response = $this->get('/positions/delete/'.$position->id);

        $response->assertRedirect(route('positions.list', $position->type));
    }
}
