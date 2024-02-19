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
            $table->enum('type', ['user_logged_in', 'employee_created', 'employee_updated', 'employee_deleted', 'employee_set_role_1', 'employee_set_role_2', 'employee_set_role_3', 'employee_set_role_4', 'employee_set_role_5', 'employee_unset_role_1', 'employee_unset_role_2', 'employee_unset_role_3', 'employee_unset_role_4', 'employee_unset_role_5', 'organisation_created', 'organisation_updated', 'organisation_activated', 'organisation_deactivated', 'position_created', 'position_updated', 'position_deleted', 'commisions_created', 'commisions_updated'])->nullable()->change();
            $table->foreignId('affected_user_id')->unsigned()->nullable()->after('user_id');
            $table->text('message')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_events', function (Blueprint $table) {
            $table->enum('type', ['organisation_created', 'organisation_updated', 'organisation_activated', 'organisation_deactivated', 'employee_created', 'employee_updated', 'employee_deleted'])->nullable()->change();
            $table->dropColumn('affected_user_id');
            $table->text('message')->change();
        });
    }
};
