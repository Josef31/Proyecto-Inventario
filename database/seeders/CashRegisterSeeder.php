<?php

namespace Database\Seeders;

use App\Models\CashRegister;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CashRegisterSeeder extends Seeder
{
    public function run()
    {
        $user = User::first();
        
        // Verificar si existe al menos un exchange_rate
        $exchangeRate = DB::table('exchange_rates')->first();
        
        if (!$exchangeRate) {
            $this->command->warn('⚠️  No hay tasas de cambio disponibles. Saltando creación de cortes de caja.');
            return;
        }

        // Crear algunos cortes de caja de ejemplo
        $cashRegisters = [
            [
                'user_id' => $user->id,
                'exchange_rate_id' => $exchangeRate->id,
                'initial_amount_moneda1' => 1000.00,
                'cash_sales_moneda1' => 2850.00,
                'final_amount_moneda1' => 3850.00,
                'initial_amount_moneda2' => 0.00,
                'cash_sales_moneda2' => 0.00,
                'final_amount_moneda2' => 0.00,
                'status' => 'cerrada',
                'notes' => 'Corte de caja de ejemplo 1',
            ],
            [
                'user_id' => $user->id,
                'exchange_rate_id' => $exchangeRate->id,
                'initial_amount_moneda1' => 1000.00,
                'cash_sales_moneda1' => 3200.00,
                'final_amount_moneda1' => 4200.00,
                'initial_amount_moneda2' => 100.00,
                'cash_sales_moneda2' => 50.00,
                'final_amount_moneda2' => 150.00,
                'status' => 'cerrada',
                'notes' => 'Corte de caja de ejemplo 2',
            ],
            [
                'user_id' => $user->id,
                'exchange_rate_id' => $exchangeRate->id,
                'initial_amount_moneda1' => 1000.00,
                'cash_sales_moneda1' => 2980.00,
                'final_amount_moneda1' => 3980.00,
                'initial_amount_moneda2' => 0.00,
                'cash_sales_moneda2' => 0.00,
                'final_amount_moneda2' => 0.00,
                'status' => 'cerrada',
                'notes' => 'Corte de caja de ejemplo 3',
            ],
            [
                'user_id' => $user->id,
                'exchange_rate_id' => $exchangeRate->id,
                'initial_amount_moneda1' => 1500.00,
                'cash_sales_moneda1' => 0.00,
                'final_amount_moneda1' => null,
                'initial_amount_moneda2' => 200.00,
                'cash_sales_moneda2' => 0.00,
                'final_amount_moneda2' => null,
                'status' => 'abierta',
                'notes' => 'Caja actualmente abierta',
            ],
        ];

        DB::table('cash_registers')->insert($cashRegisters);

        $this->command->info('✅ ' . count($cashRegisters) . ' cortes de caja de ejemplo creados exitosamente.');
    }
}