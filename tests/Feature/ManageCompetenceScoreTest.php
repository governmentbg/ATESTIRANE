<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\User;
use App\Models\Role;
use App\Models\Organisation;
use App\Models\Attestation;
use App\Models\Position;
use App\Models\CompetenceScoreType;

use Database\Seeders\RoleSeeder;
use Database\Seeders\OrganisationsSeeder;

class ManageCompetenceScoreTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic test example.
     */

    public function test_view_competence_score_types_screen(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->get('/competence_score/types');

        $response->assertStatus(200);
    }

    public function test_view_specific_competence_score_type_list(): void
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
        
        $response = $this->get('/competence_score/types/'.$types[rand(0,3)]);

        $response->assertStatus(200);
    }

    public function test_view_competence_score_edit_screen(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);

        $competence_score = CompetenceScoreType::first();
        
        $response = $this->get('/competence_score/edit/'.$competence_score->id);

        $response->assertStatus(200);
    }

    public function test_add_competence_score(): void
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
        
        $response = $this->post('/competence_score/update', [
            'attestation_form_type' => $type,
            'competence_type' => 'Тест',
            'text_score' => 'Оценка 1',
            'points' => '10'
        ]);

        $response->assertRedirect(route('competence_score.list', $type));
    }

    public function test_update_competence_score(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 1, 
            'role' => 'Централен Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);

        $competence_score = CompetenceScoreType::first();
        
        $response = $this->post('/competence_score/update', [
            'id' => $competence_score->id,
            'attestation_form_type' => $competence_score->attestation_form_type,
            'competence_type' => 'Тест',
            'text_score' => 'Оценка 1',
            'points' => '10'
        ]);

        $response->assertRedirect(route('competence_score.list', $competence_score->attestation_form_type));
    }

    public function test_add_update_competence_score_validation(): void
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
        
        $response = $this->post('/competence_score/update', [
            'attestation_form_type' => $type,
            'competence_type' => 'Тест',
            'text_score' => 'Оценка 1',
        ]);

        $response->assertSessionHasErrors('points')->assertStatus(302);;
    }

    public function test_competence_score_delete(): void
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

        $competence_score = CompetenceScoreType::first();
        
        $response = $this->get('/competence_score/delete/'.$competence_score->id);

        $response->assertRedirect(route('competence_score.list', $competence_score->attestation_form_type));
    }
}
