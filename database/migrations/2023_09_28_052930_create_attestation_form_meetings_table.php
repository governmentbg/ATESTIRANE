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
        Schema::create('attestation_form_meetings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attestation_form_id');
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->text('director_comment')->nullable();
            $table->text('employee_comment')->nullable();
            $table->dateTime('date')->nullable();
            $table->text('signed_data')->nullable();
            $table->datetime('signed_director_at')->nullable();
            $table->datetime('signed_employee_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attestation_form_meetings');
    }
};
