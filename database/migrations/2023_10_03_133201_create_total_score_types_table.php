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
        Schema::create('total_score_types', function (Blueprint $table) {
            $table->id();
            $table->enum('attestation_form_type', ['management', 'experts', 'general', 'technical'])->nullable();
            $table->text('text_score')->nullable();
            $table->tinyInteger('from_points')->unsigned()->default(0)->nullable();
            $table->tinyInteger('to_points')->unsigned()->default(0)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('total_score_types');
    }
};
