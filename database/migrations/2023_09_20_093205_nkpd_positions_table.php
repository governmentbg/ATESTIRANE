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
        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn('nkpd');
            $table->string('nkpd1')->after('name')->nullable();
            $table->string('nkpd2')->after('nkpd1')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn('nkpd1');
            $table->dropColumn('nkpd2');
            $table->string('nkpd')->after('name')->nullable();
        });
    }
};
