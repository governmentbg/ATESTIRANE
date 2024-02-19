<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\User;
use App\Models\Role;
use App\Models\Organisation;
use App\Models\Attestation;
use App\Models\Commission;

use Database\Seeders\RoleSeeder;
use Database\Seeders\OrganisationsSeeder;

class ManageCommissionTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic test example.
     */

    public function test_view_commissions_list_screen(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 2, 
            'role' => 'Локален Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->get('/commissions/list');

        $response->assertStatus(200);
    }

    public function test_view_commissions_list_with_filters_screen(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 2, 
            'role' => 'Локален Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->call('GET', '/commissions/list', [
            'approval_order' => '123',
            'approval_date' => date('Y-m-d')
        ]);

        $response->assertStatus(200);
    }

    public function test_view_commission_edit_screen(): void
    {
        $user = User::factory()->create();
        $attestation = Attestation::factory()->create();
        $this->actingAs($user)->withSession([
            'role_id' => 2, 
            'role' => 'Локален Администратор',
            'attestation_id' => $attestation->id,
            'attestation' => $attestation
        ]);
        
        $response = $this->get('/commissions/edit');

        $response->assertStatus(200);
    }

    public function test_new_commission(): void
    {
        $user = User::factory()->create();
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
        
        $response = $this->post('/commissions/update', [
            'members' => $members,
            'director_id' => $member->id,
            'valid_until' => date('Y-m-d'),
            'approval_order' => '1111',
            'approval_date' => date('Y-m-d'),
            'evaluated_members' => $evaluated_members
        ]);

        $response->assertRedirect(route('commissions.list'));
    }
}