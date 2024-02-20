<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Attestation;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attestation_forms', function (Blueprint $table) {
            $table->unsignedBigInteger('attestation_id')->after('id')->nullable();
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->unsignedBigInteger('attestation_id')->after('id')->nullable();
            $table->dropColumn('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attestation_forms', function (Blueprint $table) {
            $table->dropColumn('attestation_id');
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->year('year')->nullable();
            $table->dropColumn('attestation_id');
        });
    }
};
