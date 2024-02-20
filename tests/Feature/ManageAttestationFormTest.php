<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\User;
use App\Models\Role;
use App\Models\Organisation;
use App\Models\Attestation;
use App\Models\Commission;
use App\Models\AttestationForm;
use App\Models\AttestationFormGoal;
use App\Models\AttestationFormMeeting;
use App\Models\AttestationFormScore;
use App\Models\AttestationFormScoreSignature;

use Database\Seeders\RoleSeeder;
use Database\Seeders\OrganisationsSeeder;

class ManageAttestationFormTest extends TestCase
{

    use RefreshDatabase;

    public function setUp() :void {
        parent::setUp();
        $user = User::factory()->create();
        $user->roles()->attach(3);
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 3, 
            'role' => 'Оценяващ ръководител',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);

        $member = User::factory()->create([
            'egn' => '3333333331'
        ]);
        $member2 = User::factory()->create([
            'egn' => '3333333332'
        ]);

        $evaluated_member = User::factory()->create([
            'egn' => '4444444441'
        ]);
        $evaluated_member2 = User::factory()->create([
            'egn' => '4444444442'
        ]);

        $members = [];
        array_push($members, $member->id);
        array_push($members, $member2->id);

        $evaluated_members = [];
        array_push($evaluated_members, $evaluated_member->id);
        array_push($evaluated_members, $evaluated_member2->id);

        $commission = Commission::factory()->create([
            'attestation_id' => $attestation->id,
            'director_id' => $user->id
        ]);
        $commission->members()->attach($member);
        $commission->members()->attach($member2);
        $commission->evaluated_members()->attach($evaluated_member);
        $commission->evaluated_members()->attach($evaluated_member2);

        $attestation_form = AttestationForm::factory()->create([
            'attestation_id' => $attestation->id,
            'director_id' => $user->id,
            'commission_id' => $commission->id,
            'user_id' => $evaluated_member->id
        ]);

        $attestation_form_goals = AttestationFormGoal::factory()->create([
            'attestation_form_id' => $attestation_form->id
        ]);

        $attestation_form_meeting = AttestationFormMeeting::factory()->create([
            'attestation_form_id' => $attestation_form->id,
            'requested_by' => $user->id
        ]);

        $attestation_form_scores = AttestationFormScore::factory()->create([
            'attestation_form_id' => $attestation_form->id
        ]);

        foreach ($commission->members as $member){
            $attestation_form_score_signature = AttestationFormScoreSignature::factory()->create([
                'attestation_form_score_id' => $attestation_form_scores->id,
                'user_id' => $member->id
            ]);
        }
    }

    public function test_view_attestation_forms_list_screen(): void
    {   
        $response = $this->get('/attestation-form/list');
        $response->assertStatus(200);
    }

    public function test_view_attestation_form_step2_screen(): void
    {
        $attestation_form = AttestationForm::first();
        $response = $this->get('/attestation-form/step-2/'.$attestation_form->id);
        $response->assertStatus(200);
    }

    public function test_attestation_form_step2_edit_mode(): void
    {
        $attestation_form = AttestationForm::first();
        $response = $this->post('/attestation-form/step-2/edit-mode/'.$attestation_form->id);
        $response->assertStatus(200);
    }

    public function test_attestation_form_step2_save(): void
    {
        $attestation_form = AttestationForm::first();
        $response = $this->post('/attestation-form/step-2/save/'.$attestation_form->id, [
            'goals[0][goal]' => 'Тест',
            'goals[0][result]' => 'Тест',
            'goals[0][date_from]' => date('Y-m-d'),
            'goals[0][date_to]' => date('Y-m-d')
        ]);
        $response->assertStatus(200);
    }

    public function test_attestation_form_step2_delete(): void
    {
        $attestation_form = AttestationForm::first();
        $response = $this->post('/attestation-form/step-2/delete/'.$attestation_form->id, [
            'goal_number' => '0'
        ]);
        $response->assertRedirect(route('attestationforms.step_2.view', $attestation_form->id));
    }

    public function test_attestation_form_step2_unlock(): void
    {
        $attestation_form = AttestationForm::first();
        $response = $this->post('/attestation-form/step-2/operation/'.$attestation_form->id, [
            'operation' => 'unlock'
        ]);
        $response->assertRedirect(route('attestationforms.step_2.view', $attestation_form->id));
    }
    public function test_attestation_form_step2_complete(): void
    {
        $attestation_form = AttestationForm::first();
        $response = $this->post('/attestation-form/step-2/operation/'.$attestation_form->id, [
            'operation' => 'complete'
        ]);
        $response->assertRedirect(route('attestationforms.step_2.view', $attestation_form->id));
    }
    public function test_attestation_form_step2_new(): void
    {
        $attestation_form = AttestationForm::first();
        $response = $this->post('/attestation-form/step-2/operation/'.$attestation_form->id, [
            'operation' => 'new'
        ]);
        $response->assertRedirect(route('attestationforms.step_2.view', $attestation_form->id));
    }

    public function test_attestation_form_step2_presign(): void
    {
        $attestation_form = AttestationForm::first();
        $response = $this->post('/attestation-form/step-2/presign/'.$attestation_form->id);
        $response->assertStatus(200);
    }

    public function test_attestation_form_step2_sign(): void
    {
        $attestation_form = AttestationForm::first();
        $response = $this->post('/attestation-form/step-2/sign/'.$attestation_form->id, [
            'signed_goals' => 'hash'
        ]);
        $response->assertStatus(200);
    }

    public function test_attestation_form_step3_request(): void
    {
        $attestation_form = AttestationForm::first();
        $response = $this->post('/attestation-form/step-3/request/'.$attestation_form->id);
        $response->assertRedirect(route('attestationforms.step_3.view', $attestation_form->id));
    }

    public function test_attestation_form_step3_save(): void
    {
        $attestation_form = AttestationForm::first();
        $response = $this->post('/attestation-form/step-3/save/'.$attestation_form->id, [
            'director_comment' => 'Тест'
        ]);
        $response->assertRedirect(route('attestationforms.step_3.view', $attestation_form->id));
    }

    public function test_attestation_form_step3_presign(): void
    {
        $attestation_form = AttestationForm::first();
        $response = $this->post('/attestation-form/step-3/presign/'.$attestation_form->id);
        $response->assertStatus(200);
    }

    public function test_attestation_form_step3_sign(): void
    {
        $attestation_form = AttestationForm::first();
        $response = $this->post('/attestation-form/step-3/sign/'.$attestation_form->id, [
            'signed_data' => 'hash'
        ]);
        $response->assertStatus(200);
    }

    public function test_view_attestation_form_step4_screen(): void
    {
        $user = User::where('egn', '3333333331')->first();
        $user->roles()->attach(5);
        $attestation = Attestation::first();
        $this->actingAs($user)->withSession([
            'role_id' => 5, 
            'role' => 'Член на атестационна комисия',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        $attestation_form = AttestationForm::first();
        $attestation_form_goals = AttestationFormGoal::where('attestation_form_id', $attestation_form->id)->first();
        $attestation_form_goals->goals_status = 'signed';
        $attestation_form_goals->save();
        $response = $this->get('/attestation-form/step-4/'.$attestation_form->id);
        $response->assertStatus(200);
    }

    public function test_attestation_form_step4_edit_mode(): void
    {
        $user = User::where('egn', '3333333331')->first();
        $user->roles()->attach(5);
        $attestation = Attestation::first();
        $this->actingAs($user)->withSession([
            'role_id' => 5, 
            'role' => 'Член на атестационна комисия',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        $attestation_form = AttestationForm::first();
        $attestation_form_goals = AttestationFormGoal::where('attestation_form_id', $attestation_form->id)->first();
        $attestation_form_goals->goals_status = 'signed';
        $attestation_form_goals->save();
        $response = $this->post('/attestation-form/step-4/edit-mode/'.$attestation_form->id);
        $response->assertStatus(200);
    }

    public function test_attestation_form_step4_save(): void
    {
        $user = User::where('egn', '3333333331')->first();
        $user->roles()->attach(5);
        $attestation = Attestation::first();
        $this->actingAs($user)->withSession([
            'role_id' => 5, 
            'role' => 'Член на атестационна комисия',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        $attestation_form = AttestationForm::first();
        $attestation_form_goals = AttestationFormGoal::where('attestation_form_id', $attestation_form->id)->first();
        $attestation_form_goals->goals_status = 'signed';
        $attestation_form_goals->save();

        $response = $this->post('/attestation-form/step-4/save/'.$attestation_form->id, [
            'goals_score' => 1,
            'competence_score' => [1,2,3],
            'add_info' => [
                'arguments' => 'Тест',
                'sources' => 'Тест',
                'needs' => 'Тест' 
            ]
        ]);
        $response->assertStatus(200);
    }

    public function test_attestation_form_step4_complete(): void
    {
        $user = User::where('egn', '3333333331')->first();
        $user->roles()->attach(5);
        $attestation = Attestation::first();
        $this->actingAs($user)->withSession([
            'role_id' => 5, 
            'role' => 'Член на атестационна комисия',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        $attestation_form = AttestationForm::first();
        $attestation_form_goals = AttestationFormGoal::where('attestation_form_id', $attestation_form->id)->first();
        $attestation_form_goals->goals_status = 'signed';
        $attestation_form_goals->save();
        $response = $this->post('/attestation-form/step-4/complete/'.$attestation_form->id);
        $response->assertRedirect(route('attestationforms.step_4.view', $attestation_form->id));
    }

    public function test_attestation_form_step4_presign(): void
    {
        $user = User::where('egn', '3333333331')->first();
        $user->roles()->attach(5);
        $attestation = Attestation::first();
        $this->actingAs($user)->withSession([
            'role_id' => 5, 
            'role' => 'Член на атестационна комисия',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        $attestation_form = AttestationForm::first();
        $attestation_form_goals = AttestationFormGoal::where('attestation_form_id', $attestation_form->id)->first();
        $attestation_form_goals->goals_status = 'signed';
        $attestation_form_goals->save();
        $response = $this->post('/attestation-form/step-4/presign/'.$attestation_form->id);
        $response->assertStatus(200);
    }

    public function test_attestation_form_step4_sign(): void
    {
        $user = User::where('egn', '3333333331')->first();
        $user->roles()->attach(5);
        $attestation = Attestation::first();
        $this->actingAs($user)->withSession([
            'role_id' => 5, 
            'role' => 'Член на атестационна комисия',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        $attestation_form = AttestationForm::first();
        $attestation_form_goals = AttestationFormGoal::where('attestation_form_id', $attestation_form->id)->first();
        $attestation_form_goals->goals_status = 'signed';
        $attestation_form_goals->save();
        $response = $this->post('/attestation-form/step-4/sign/'.$attestation_form->id, [
            'signed_score' => 'hash'
        ]);
        $response->assertStatus(200);
    }

    public function test_attestation_form_step4_agree(): void
    {
        $user = User::where('egn', '3333333331')->first();
        $user->roles()->attach(5);
        $attestation = Attestation::first();
        $this->actingAs($user)->withSession([
            'role_id' => 5, 
            'role' => 'Член на атестационна комисия',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        $attestation_form = AttestationForm::first();
        $attestation_form_goals = AttestationFormGoal::where('attestation_form_id', $attestation_form->id)->first();
        $attestation_form_goals->goals_status = 'signed';
        $attestation_form_goals->save();
        $response = $this->post('/attestation-form/step-4/agree/'.$attestation_form->id, [
            'signed_score' => 'hash'
        ]);
        $response->assertStatus(200);
    }

    public function test_attestation_form_step5_presign(): void
    {
        $user = User::where('egn', '3333333331')->first();
        $user->roles()->attach(2);
        $attestation = Attestation::first();
        $this->actingAs($user)->withSession([
            'role_id' => 2, 
            'role' => 'Локален Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        $attestation_form = AttestationForm::first();
        $attestation_form->status = 'wait_final_score';
        $attestation_form->save();
        $attestation_form_goals = AttestationFormGoal::where('attestation_form_id', $attestation_form->id)->first();
        $attestation_form_goals->goals_status = 'signed';
        $attestation_form_goals->save();
        $response = $this->post('/attestation-form/step-5/presign/'.$attestation_form->id, [
            'final_score' => 'Оценка 1',
            'final_score_comment' => 'Тест'
        ]);
        $response->assertStatus(200);
    }

    public function test_attestation_form_step5_finalize(): void
    {
        $user = User::where('egn', '3333333331')->first();
        $user->roles()->attach(2);
        $attestation = Attestation::first();
        $this->actingAs($user)->withSession([
            'role_id' => 2, 
            'role' => 'Локален Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        $attestation_form = AttestationForm::first();
        $attestation_form->status = 'wait_final_score';
        $attestation_form->save();
        $attestation_form_goals = AttestationFormGoal::where('attestation_form_id', $attestation_form->id)->first();
        $attestation_form_goals->goals_status = 'signed';
        $attestation_form_goals->save();
        $response = $this->post('/attestation-form/step-5/finalize/'.$attestation_form->id, [
            'final_score' => 'Оценка 1',
            'signed_score' => 'hash',
            'final_score_comment' => 'Тест'
        ]);
        $response->assertStatus(200);
    }
    
}