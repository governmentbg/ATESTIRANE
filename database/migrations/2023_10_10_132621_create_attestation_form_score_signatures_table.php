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
        Schema::create('attestation_form_score_signatures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attestation_form_score_id');
            $table->unsignedBigInteger('user_id');
            $table->text('signed_score')->nullable();
            $table->datetime('signed_at')->nullable();
            $table->string('status')->default('none');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attestation_form_score_signatures');
    }
};
