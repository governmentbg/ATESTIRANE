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
        Schema::table('attestation_form_scores', function (Blueprint $table) {
            $table->dropColumn('scores');
            $table->dropColumn('add_info');
            $table->decimal('total_score', 10, 2)->default(0)->after('attestation_form_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attestation_form_scores', function (Blueprint $table) {
            $table->json('scores')->after('attestation_form_id')->nullable();
            $table->tinyInteger('total_score')->unsigned()->default(0)->after('scores')->nullable()->change();
            $table->json('add_info')->after('total_score')->nullable();
        });
    }
};
