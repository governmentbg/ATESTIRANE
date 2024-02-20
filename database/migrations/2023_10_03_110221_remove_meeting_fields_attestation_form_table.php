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
            $table->dropColumn('meeting_requested_by');
            $table->dropColumn('meeting_director_comment');
            $table->dropColumn('meeting_employee_comment');
            $table->dropColumn('meeting_date');
            $table->dropColumn('meeting_signed');
            $table->dropColumn('scores');
            $table->dropColumn('total_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attestation_forms', function (Blueprint $table) {
            $table->unsignedBigInteger('meeting_requested_by')->nullable();
            $table->text('meeting_director_comment')->nullable();
            $table->text('meeting_employee_comment')->nullable();
            $table->dateTime('meeting_date')->nullable();
            $table->text('meeting_signed')->nullable();
            $table->json('scores')->nullable();
            $table->tinyInteger('total_score')->nullable();
        });
    }
};
