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

class EmployeeTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic test example.
     */

    public function test_view_employees_list_screen(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->get('/employees/list');

        $response->assertStatus(200);
    }

    public function test_search_employee_by_egn_and_edit_as_central_admin(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->post('/employees/search', [
            'search_egn' => $user->egn
        ]);

        $response->assertRedirect(route('employees.edit', $user->id));
    }

    public function test_search_employee_by_egn_and_edit_as_local_admin(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 2, 
            'role' => 'Локален Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->post('/employees/search', [
            'search_egn' => $user->egn
        ]);

        $response->assertRedirect(route('employees.edit', $user->id));
    }

    public function test_search_employee_by_egn_and_add(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->post('/employees/search', [
            'search_egn' => '0000000000'
        ]);

        $response->assertRedirect(route('employees.edit'));
    }

    public function test_view_employees_edit_screen_as_central_admin(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->get('/employees/edit/'.$user->id);

        $response->assertStatus(200);
    }

    public function test_view_employees_edit_screen_as_local_admin(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 2, 
            'role' => 'Локален Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->get('/employees/edit/'.$user->id);

        $response->assertStatus(200);
    }

    public function test_view_employees_view_screen_as_local_admin(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 2, 
            'role' => 'Локален Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->get('/employees/view/'.$user->id);

        $response->assertStatus(200);
    }

    public function test_update_employee(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 2, 
            'role' => 'Локален Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->post('/employees/update/', [
            'id' => $user->id,
            'name' => 'Тест',
            'egn' => $user->egn,
            'email' => 'test@justice.bg',
            'rank' => '1',
            'organisation_id' => '1',
            'position_id' => '1',
            'digital_attestation' => '1',
            'appointment_date' => date('Y-m-d')
        ]);

        $response->assertRedirect(route('employees.list'));
    }

    public function test_insert_employee(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->post('/employees/update/', [
            'name' => 'Тест',
            'egn' => '0000000000',
            'email' => 'test@justice.bg',
            'rank' => '1',
            'organisation_id' => '1',
            'position_id' => '1',
            'digital_attestation' => '1',
            'appointment_date' => date('Y-m-d')
        ]);

        $response->assertRedirect(route('employees.list'));
    }

    public function test_update_insert_employee_validation(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 2, 
            'role' => 'Локален Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->post('/employees/update/', [
            'egn' => '0000000000',
            'email' => 'test@justice.bg',
            'rank' => '1',
            'organisation_id' => '1',
            'position_id' => '1',
            'digital_attestation' => '1',
            'appointment_date' => date('Y-m-d')
        ]);

        $response->assertSessionHasErrors('name')->assertStatus(302);;
    }

    public function test_delete_employee_myself(): void
    {
        $user = User::factory()->create();
        $roles = Role::all();
        $attestation = Attestation::factory()->create();
        $user->roles()->saveMany($roles);
        $this->actingAs($user)->withSession([
            'role_id' => 2, 
            'role' => 'Локален Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->get('/employees/delete/'.$user->id);

        $response->assertRedirect(route('employees.list'));
    }

    public function test_delete_employee_as_local_admin(): void
    {
        $user = User::factory()->create();
        $roles = Role::all();
        $attestation = Attestation::factory()->create();
        $user->roles()->saveMany($roles);
        $this->actingAs($user)->withSession([
            'role_id' => 2, 
            'role' => 'Локален Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);

        $another_user = User::factory()->create([
            'egn' => '5555555555',
        ]);
        
        $response = $this->get('/employees/delete/'.$another_user->id);

        $response->assertRedirect(route('employees.list'));
    }
}