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
        Schema::table('users', function (Blueprint $table) {
            $table->string('old_attestation_year_1')->after('photo_url')->nullable();
            $table->string('old_attestation_score_1')->after('old_attestation_year_1')->nullable();
            $table->string('old_attestation_year_2')->after('old_attestation_score_1')->nullable();
            $table->string('old_attestation_score_2')->after('old_attestation_year_2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('old_attestation_year_1');
            $table->dropColumn('old_attestation_score_1');
            $table->dropColumn('old_attestation_year_2');
            $table->dropColumn('old_attestation_score_2');
        });
    }
};
