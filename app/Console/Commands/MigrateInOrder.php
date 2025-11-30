<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MigrateInOrder extends Command
{
    protected $signature = 'migrate:inorder';

    protected $description = 'Ejecutar migraciones específicas en orden definido';

    public function handle()
    {
        $migrations = [
            'users_role',
            'users',
            'customers',
            'business_services',
            'exchange_rates',
            'cash_register',
            'payment_method',
            'suppliers',
            'products_classification',
            'products',
            'purchases',
            'purchase_items',
            'sales',
            'sale_items',
            'sale_services',
        ];

        $basePath = database_path('migrations') . DIRECTORY_SEPARATOR;

        foreach ($migrations as $table) {
            // Busca el archivo de migración que contenga el nombre de la tabla
            $files = glob($basePath . "*{$table}*.php");
            if (!$files) {
                $this->error("No se encontró migración para la tabla: {$table}");
                continue;
            }
            foreach ($files as $file) {
                $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);
                $this->info("Ejecutando migración: {$relativePath}");
                Artisan::call('migrate', ['--path' => $relativePath]);
            }
        }

        $this->info('Migraciones ejecutadas en el orden correcto.');
    }
}