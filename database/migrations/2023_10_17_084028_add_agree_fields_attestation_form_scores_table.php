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
            $table->text('agree_score')->after('status_at')->nullable();
            $table->datetime('agree_at')->after('agree_score')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attestation_form_scores', function (Blueprint $table) {
            $table->dropColumn('agree_score');
            $table->dropColumn('agree_at');
        });
    }
};
