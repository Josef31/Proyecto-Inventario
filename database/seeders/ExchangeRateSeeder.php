<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExchangeRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $exchangeRates = [
            [
                'date' => Carbon::today(),
                'base_currency' => 'USD',
                'target_currency' => 'VES', // Bolívar Venezolano
                'rate' => 36.50, // Tasa de ejemplo, ajustar según necesidad
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'date' => Carbon::yesterday(),
                'base_currency' => 'USD',
                'target_currency' => 'VES',
                'rate' => 36.45,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('exchange_rates')->insert($exchangeRates);
    }
}
