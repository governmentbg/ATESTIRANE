<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Database\Seeders\RoleSeeder;
use Database\Seeders\OrganisationsSeeder;
use Database\Seeders\PositionsSeeder;
use Database\Seeders\ScoreTypesSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(OrganisationsSeeder::class);
        $this->call(PositionsSeeder::class);
        $this->call(ScoreTypesSeeder::class);
    }
}
