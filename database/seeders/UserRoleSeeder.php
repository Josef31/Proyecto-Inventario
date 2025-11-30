<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users_role')->insert([
            [
                'id' => 1,
                'name' => 'Administrador',
            ],
            [
                'id' => 2,
                'name' => 'Gerente',
            ],
            [
                'id' => 3,
                'name' => 'Cajero',
            ]
        ]);
    }
}
