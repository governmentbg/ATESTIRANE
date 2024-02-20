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

class ReportsTest extends TestCase
{

    use RefreshDatabase;

    public function setUp() :void {
        parent::setUp();
        $user = User::factory()->create();
        $user->roles()->attach(2);
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 2, 
            'role' => 'Локален Администратор',
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

    public function test_view_choose_reports_screen(): void
    {   
        $response = $this->get('/checks');
        $response->assertStatus(200);
    }

    public function test_no_goals_report(): void
    {   
        $response = $this->call('GET', '/checks/show', [
            'type' => 'no_goals'
        ]);
        $response->assertStatus(200);
    }

    public function test_no_score_report(): void
    {   
        $response = $this->call('GET', '/checks/show', [
            'type' => 'no_score'
        ]);
        $response->assertStatus(200);
    }

    public function test_percent_by_organisations_report(): void
    {   
        $response = $this->call('GET', '/checks/show', [
            'type' => 'percent_by_organisations'
        ]);
        $response->assertStatus(200);
    }

    public function test_scores_by_organisation_report(): void
    {   
        $response = $this->call('GET', '/checks/show', [
            'type' => 'scores_by_organisation',
            'organisation_id' => '1',
            'year' => [2023]
        ]);
        $response->assertStatus(200);
    }
    
    public function test_available_attestation_form_report(): void
    {   
        $user = User::where('egn', '4444444441')->first();
        $attestation_form = AttestationForm::first();
        $response = $this->call('GET', '/checks/show', [
            'type' => 'attestation_form',
            'organisation_id' => '1',
            'year' => [2023],
            'user_id' => $user->id
        ]);
        $response->assertRedirect(route('attestationforms.preview', $attestation_form->id));
    }

    public function test_missing_attestation_form_report(): void
    {   
        // this user don't have attestation form
        $user = User::where('egn', '4444444442')->first();
        $attestation_form = AttestationForm::first();
        $response = $this->call('GET', '/checks/show', [
            'type' => 'attestation_form',
            'organisation_id' => '1',
            'year' => [2023],
            'user_id' => $user->id
        ]);
        $response->assertRedirect(route('checks.dashboard'));
    }

    public function test_rank_upgrade_report(): void
    {   
        $response = $this->call('GET', '/checks/show', [
            'type' => 'rank_upgrade'
        ]);
        $response->assertStatus(200);
    }
    
}