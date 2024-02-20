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
            $table->string('goals_status')->after('goals')->nullable();
            $table->string('goals_status_by')->after('goals_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attestation_forms', function (Blueprint $table) {
            $table->dropColumn('goals_status');
            $table->dropColumn('goals_status_by');
        });
    }
};
