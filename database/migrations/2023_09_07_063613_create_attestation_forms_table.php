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
        Schema::create('attestation_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->json('goals')->nullable();
            $table->text('signed_goals')->nullable();
            $table->unsignedBigInteger('meeting_requested_by')->nullable();
            $table->text('meeting_director_comment')->nullable();
            $table->text('meeting_employee_comment')->nullable();
            $table->dateTime('meeting_date')->nullable();
            $table->text('meeting_signed')->nullable();
            $table->json('scores')->nullable();
            $table->tinyInteger('total_score')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attestation_forms');
    }
};
