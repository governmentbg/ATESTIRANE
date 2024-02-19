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
        Schema::table('users', function (Blueprint $table) {
            $table->string('rank')->nullable()->change();
            $table->string('rank_acquisition')->after('rank')->nullable();
            $table->tinyInteger('only_evaluate')->after('id')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rank')->change();
            $table->dropColumn('rank_acquisition');
            $table->dropColumn('only_evaluate');
        });
    }
};
