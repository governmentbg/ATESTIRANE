<?php

use App\Models\User;
use App\Models\Commission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commission_members', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Commission::class)->unsigned()->nullable();
            $table->foreignIdFor(User::class)->unsigned()->nullable();
            $table->timestamps();
            $table->index('commission_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_members');
    }
};
