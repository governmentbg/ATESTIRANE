<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['name' => 'Централен администратор', 'created_at' => DB::raw('NOW()'), 'updated_at' => DB::raw('NOW()')],
            ['name' => 'Локален администратор', 'created_at' => DB::raw('NOW()'), 'updated_at' => DB::raw('NOW()')],
            ['name' => 'Оценяващ ръководител', 'created_at' => DB::raw('NOW()'), 'updated_at' => DB::raw('NOW()')],
            ['name' => 'Оценяван', 'created_at' => DB::raw('NOW()'), 'updated_at' => DB::raw('NOW()')],
            ['name' => 'Член на атестационна комисия', 'created_at' => DB::raw('NOW()'), 'updated_at' => DB::raw('NOW()')]
        ]);
    }
}
