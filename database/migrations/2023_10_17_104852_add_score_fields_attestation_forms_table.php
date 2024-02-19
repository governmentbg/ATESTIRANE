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
            $table->string('status')->after('personal_data')->default('in_progress');
            $table->string('final_score')->after('status')->nullable();
            $table->text('final_score_signed')->after('final_score')->nullable();
            $table->datetime('final_score_signed_at')->after('final_score_signed')->nullable();
            $table->unsignedBigInteger('final_score_signed_by')->after('final_score_signed_at')->nullable();
            $table->text('final_score_comment')->after('final_score_signed_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attestation_forms', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('final_score');
            $table->dropColumn('final_score_signed');
            $table->dropColumn('final_score_signed_at');
            $table->dropColumn('final_score_signed_by');
            $table->dropColumn('final_score_comment');
        });
    }
};
