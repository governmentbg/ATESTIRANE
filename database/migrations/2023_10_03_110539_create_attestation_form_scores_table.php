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
        Schema::create('attestation_form_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attestation_form_id');
            $table->json('scores')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('status_by')->nullable();
            $table->timestamp('status_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attestation_form_scores');
    }
};
