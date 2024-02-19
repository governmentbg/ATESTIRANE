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
            $table->unsignedBigInteger('commission_id')->after('user_id')->nullable();
            $table->unsignedBigInteger('director_id')->after('commission_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attestation_forms', function (Blueprint $table) {
            $table->dropColumn('commission_id');
            $table->dropColumn('director_id');
        });
    }
};
