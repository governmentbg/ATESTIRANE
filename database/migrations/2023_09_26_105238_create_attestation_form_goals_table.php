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
        Schema::create('attestation_form_goals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attestation_form_id');
            $table->json('goals')->nullable();
            $table->string('goals_status')->nullable();
            $table->string('goals_status_by')->nullable();
            $table->text('signed_goals')->nullable();
            $table->datetime('signed_goals_director_at')->nullable();
            $table->datetime('signed_goals_employee_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attestation_form_goals');
    }
};
