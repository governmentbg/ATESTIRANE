<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('log_events', function (Blueprint $table) {
            $table->enum('type', [
                'user_logged_in',
                'employee_created',
                'employee_updated',
                'employee_deleted',
                'employee_set_role_1',
                'employee_set_role_2',
                'employee_set_role_3',
                'employee_set_role_4',
                'employee_set_role_5',
                'employee_unset_role_1',
                'employee_unset_role_2',
                'employee_unset_role_3',
                'employee_unset_role_4',
                'employee_unset_role_5',
                'organisation_created',
                'organisation_updated',
                'organisation_activated',
                'organisation_deactivated',
                'position_created',
                'position_updated',
                'position_deleted',
                'commisions_created',
                'commisions_updated',
                'attestation_created',
                'attestation_form_step_1_preview',
                'attestation_form_step_2_preview',
                'attestation_form_step_2_new',
                'attestation_form_step_2_edit',
                'attestation_form_step_2_updated',
                'attestation_form_step_2_deleted',
                'attestation_form_step_2_unlock',
                'attestation_form_step_2_completed',
                'attestation_form_step_2_sign',
                'attestation_form_step_3_preview',
                'attestation_form_step_3_request',
                'attestation_form_step_3_updated',
                'attestation_form_step_3_director_comment',
                'attestation_form_step_3_employee_comment',
                'attestation_form_step_3_sign',
                'attestation_form_step_4_preview',
                'attestation_form_step_4_edit',
                'attestation_form_step_4_updated'
            ])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_events', function (Blueprint $table) {
            $table->enum('type', [
                'user_logged_in',
                'employee_created',
                'employee_updated',
                'employee_deleted',
                'employee_set_role_1',
                'employee_set_role_2',
                'employee_set_role_3',
                'employee_set_role_4',
                'employee_set_role_5',
                'employee_unset_role_1',
                'employee_unset_role_2',
                'employee_unset_role_3',
                'employee_unset_role_4',
                'employee_unset_role_5',
                'organisation_created',
                'organisation_updated',
                'organisation_activated',
                'organisation_deactivated',
                'position_created',
                'position_updated',
                'position_deleted',
                'commisions_created',
                'commisions_updated',
                'attestation_form_step_1_preview',
                'attestation_form_step_2_preview',
                'attestation_form_step_2_new',
                'attestation_form_step_2_edit',
                'attestation_form_step_2_updated',
                'attestation_form_step_2_deleted',
                'attestation_form_step_2_unlock',
                'attestation_form_step_2_completed',
                'attestation_form_step_2_sign',
                'attestation_form_step_3_preview',
                'attestation_form_step_3_request',
                'attestation_form_step_3_updated',
                'attestation_form_step_3_director_comment',
                'attestation_form_step_3_employee_comment',
                'attestation_form_step_3_sign',
                'attestation_form_step_4_preview',
                'attestation_form_step_4_edit',
                'attestation_form_step_4_updated'
            ])->nullable()->change();
        });
    }
};
