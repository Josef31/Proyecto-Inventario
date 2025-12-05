<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payment_method')->insert([
            ['id' => 1, 'name' => 'Dólares', 'currency' => 'USD'],
            ['id' => 2, 'name' => 'Bolívares', 'currency' => 'Bs'],
            ['id' => 3, 'name' => 'Transferencia Bs', 'currency' => 'Bs'],
            ['id' => 4, 'name' => 'Pago Móvil Bs', 'currency' => 'Bs'],
            ['id' => 5, 'name' => 'Tarjeta Débito', 'currency' => 'Bs'],
            ['id' => 6, 'name' => 'Tarjeta Crédito', 'currency' => 'Bs'],
        ]);
    }
}
