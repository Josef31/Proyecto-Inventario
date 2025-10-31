<?php

namespace Database\Seeders;

use App\Models\CashRegister;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CashRegisterSeeder extends Seeder
{
    public function run()
    {
        $user = User::first();

        // Crear algunos cortes de caja del pasado
        $cashRegisters = [
            [
                'initial_amount' => 1000.00,
                'final_amount' => 3850.00,
                'cash_sales' => 2850.00,
                'expected_amount' => 3850.00,
                'difference' => 0.00,
                'status' => 'cerrada',
                'opened_at' => Carbon::now()->subDays(3)->setTime(8, 0, 0),
                'closed_at' => Carbon::now()->subDays(3)->setTime(20, 0, 0)
            ],
            [
                'initial_amount' => 1000.00,
                'final_amount' => 4200.00,
                'cash_sales' => 3200.00,
                'expected_amount' => 4200.00,
                'difference' => 0.00,
                'status' => 'cerrada',
                'opened_at' => Carbon::now()->subDays(2)->setTime(8, 0, 0),
                'closed_at' => Carbon::now()->subDays(2)->setTime(20, 0, 0)
            ],
            [
                'initial_amount' => 1000.00,
                'final_amount' => 3980.00,
                'cash_sales' => 2980.00,
                'expected_amount' => 3980.00,
                'difference' => 0.00,
                'status' => 'cerrada',
                'opened_at' => Carbon::now()->subDays(1)->setTime(8, 0, 0),
                'closed_at' => Carbon::now()->subDays(1)->setTime(20, 0, 0)
            ],
            [
                'initial_amount' => 1000.00,
                'final_amount' => 4150.00,
                'cash_sales' => 3150.00,
                'expected_amount' => 4150.00,
                'difference' => 0.00,
                'status' => 'cerrada',
                'opened_at' => Carbon::now()->subDays(1)->setTime(8, 0, 0),
                'closed_at' => Carbon::now()->subDays(1)->setTime(20, 0, 0)
            ],
            // Un corte con diferencia positiva
            [
                'initial_amount' => 1000.00,
                'final_amount' => 4300.00,
                'cash_sales' => 3200.00,
                'expected_amount' => 4200.00,
                'difference' => 100.00,
                'status' => 'cerrada',
                'opened_at' => Carbon::now()->subDays(4)->setTime(8, 0, 0),
                'closed_at' => Carbon::now()->subDays(4)->setTime(20, 0, 0)
            ],
            // Un corte con diferencia negativa
            [
                'initial_amount' => 1000.00,
                'final_amount' => 3950.00,
                'cash_sales' => 3000.00,
                'expected_amount' => 4000.00,
                'difference' => -50.00,
                'status' => 'cerrada',
                'opened_at' => Carbon::now()->subDays(5)->setTime(8, 0, 0),
                'closed_at' => Carbon::now()->subDays(5)->setTime(20, 0, 0)
            ]
        ];

        foreach ($cashRegisters as $cashData) {
            CashRegister::create(array_merge($cashData, ['user_id' => $user->id]));
        }

        $this->command->info('✅ 6 cortes de caja de ejemplo creados exitosamente.');
    }
}