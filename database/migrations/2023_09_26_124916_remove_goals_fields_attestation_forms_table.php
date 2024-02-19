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
        Schema::table('attestation_forms', function (Blueprint $table) {
            $table->dropColumn('goals');
            $table->dropColumn('goals_status');
            $table->dropColumn('goals_status_by');
            $table->dropColumn('signed_goals');
            $table->dropColumn('signed_goals_director_at');
            $table->dropColumn('signed_goals_employee_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attestation_forms', function (Blueprint $table) {
            $table->json('goals')->after('user_id')->nullable();
            $table->string('goals_status')->nullable();
            $table->string('goals_status_by')->nullable();
            $table->text('signed_goals')->nullable();
            $table->datetime('signed_goals_director_at')->nullable();
            $table->datetime('signed_goals_employee_at')->nullable();
        });
    }
};
