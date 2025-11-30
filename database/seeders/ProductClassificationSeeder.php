<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classifications = [
            ['id' => 1, 'name' => 'Calzado'],
            ['id' => 2, 'name' => 'Vestido'],
            ['id' => 3, 'name' => 'S'],
            ['id' => 4, 'name' => 'Papelería'],
            ['id' => 5, 'name' => 'Herramientas'],
        ];

        DB::table('products_classification')->insert($classifications);
    }
}
