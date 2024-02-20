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
            $table->datetime('signed_goals_director_at')->after('signed_goals')->nullable();
            $table->datetime('signed_goals_employee_at')->after('signed_goals_director_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attestation_forms', function (Blueprint $table) {
            $table->dropColumn('signed_goals_director_at');
            $table->dropColumn('signed_goals_employee_at');
        });
    }
};
