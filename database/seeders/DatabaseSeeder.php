<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            UserRoleSeeder::class,
            ProductClassificationSeeder::class,
            PaymentMethodSeeder::class,
            ExchangeRateSeeder::class,
            UserSeeder::class,
            ProductSeeder::class, // Si también quieres datos de productos
            ServiceSeeder::class,
            //SaleSeeder::class,
            //CashRegisterSeeder::class,
        ]);
    }
}
