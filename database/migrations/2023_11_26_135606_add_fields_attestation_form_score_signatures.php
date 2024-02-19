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
        Schema::table('attestation_form_score_signatures', function (Blueprint $table) {
            $table->unsignedBigInteger('attestation_form_id')->after('user_id');
            $table->json('scores')->after('attestation_form_id')->nullable();
            $table->tinyInteger('total_score')->unsigned()->default(0)->after('scores')->nullable();
            $table->json('add_info')->after('total_score')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attestation_form_score_signatures', function (Blueprint $table) {
            $table->dropColumn('attestation_form_id');
            $table->dropColumn('scores');
            $table->dropColumn('total_score');
            $table->dropColumn('add_info');
        });
    }
};
