<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\User;
use App\Models\Role;
use App\Models\Organisation;
use App\Models\Attestation;
use App\Models\Position;
use App\Models\TotalScoreType;

use Database\Seeders\RoleSeeder;
use Database\Seeders\OrganisationsSeeder;

class ManageTotalScoreTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic test example.
     */

    public function test_view_total_score_types_screen(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->get('/total_score/types');

        $response->assertStatus(200);
    }

    public function test_view_specific_total_score_type_list(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);

        $types = ['management', 'experts', 'general', 'technical'];
        
        $response = $this->get('/total_score/types/'.$types[rand(0,3)]);

        $response->assertStatus(200);
    }

    public function test_view_total_score_edit_screen(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);

        $total_score = TotalScoreType::first();
        
        $response = $this->get('/total_score/edit/'.$total_score->id);

        $response->assertStatus(200);
    }

    public function test_add_total_score(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);

        $types = ['management', 'experts', 'general', 'technical'];

        $type = $types[rand(0,3)];
        
        $response = $this->post('/total_score/update', [
            'attestation_form_type' => $type,
            'type' => 'Оценка 1',
            'text_score' => 'Оценка 1',
            'from_points' => '10',
            'to_points' => '20',
        ]);

        $response->assertRedirect(route('total_score.list', $type));
    }

    public function test_update_total_score(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);

        $total_score = TotalScoreType::first();
        
        $response = $this->post('/total_score/update', [
            'id' => $total_score->id,
            'attestation_form_type' => $total_score->attestation_form_type,
            'type' => 'Оценка 1',
            'text_score' => 'Оценка 1',
            'from_points' => '10',
            'to_points' => '20',
        ]);

        $response->assertRedirect(route('total_score.list', $total_score->attestation_form_type));
    }

    public function test_add_update_total_score_validation(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);

        $types = ['management', 'experts', 'general', 'technical'];

        $type = $types[rand(0,3)];
        
        $response = $this->post('/total_score/update', [
            'attestation_form_type' => $type,
            'type' => 'Оценка 1',
            'text_score' => 'Оценка 1',
            'from_points' => '10',
        ]);

        $response->assertSessionHasErrors('to_points')->assertStatus(302);;
    }

    public function test_total_score_delete(): void
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

        $total_score = TotalScoreType::first();
        
        $response = $this->get('/total_score/delete/'.$total_score->id);

        $response->assertRedirect(route('total_score.list', $total_score->attestation_form_type));
    }
}
