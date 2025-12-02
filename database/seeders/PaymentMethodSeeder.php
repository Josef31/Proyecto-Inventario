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
        $paymentMethods = [
            ['id' => 1, 'name' => 'Dólares'],
            ['id' => 2, 'name' => 'Efectivo'],
            ['id' => 3, 'name' => 'Tarjeta Débito'],
            ['id' => 4, 'name' => 'Tarjeta Crédito'],
            ['id' => 5, 'name' => 'Transferencia'],
        ];

        DB::table('payment_method')->insert($paymentMethods);
    }
}
