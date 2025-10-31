<?php

namespace Database\Seeders;

use App\Models\BusinessService;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        $services = [
            [
                'name' => 'Formateo e Instalación de Windows',
                'description' => 'Formateo completo e instalación de Windows 10/11 con drivers',
                'base_cost' => 150.00,
                'customer_rate' => 400.00,
                'estimated_duration' => 120,
                'is_active' => true
            ],
            [
                'name' => 'Limpieza Interna de Computadora',
                'description' => 'Limpieza profunda de componentes internos y cambio de pasta térmica',
                'base_cost' => 50.00,
                'customer_rate' => 200.00,
                'estimated_duration' => 60,
                'is_active' => true
            ],
            [
                'name' => 'Instalación de Antivirus',
                'description' => 'Instalación y configuración de antivirus profesional',
                'base_cost' => 80.00,
                'customer_rate' => 250.00,
                'estimated_duration' => 45,
                'is_active' => true
            ],
            [
                'name' => 'Recuperación de Datos',
                'description' => 'Recuperación de datos de discos duros dañados',
                'base_cost' => 200.00,
                'customer_rate' => 800.00,
                'estimated_duration' => 180,
                'is_active' => true
            ],
            [
                'name' => 'Reparación de Pantalla Laptop',
                'description' => 'Cambio de pantalla LCD para laptops',
                'base_cost' => 300.00,
                'customer_rate' => 1200.00,
                'estimated_duration' => 90,
                'is_active' => true
            ],
            [
                'name' => 'Configuración de Red WiFi',
                'description' => 'Instalación y configuración de red WiFi empresarial',
                'base_cost' => 100.00,
                'customer_rate' => 350.00,
                'estimated_duration' => 75,
                'is_active' => true
            ],
            [
                'name' => 'Backup de Información',
                'description' => 'Copia de seguridad completa de datos importantes',
                'base_cost' => 60.00,
                'customer_rate' => 180.00,
                'estimated_duration' => 30,
                'is_active' => true
            ],
            [
                'name' => 'Actualización de Hardware',
                'description' => 'Instalación de memoria RAM o disco SSD',
                'base_cost' => 40.00,
                'customer_rate' => 150.00,
                'estimated_duration' => 45,
                'is_active' => true
            ],
            [
                'name' => 'Instalación de Software Especializado',
                'description' => 'Instalación de software como AutoCAD, Adobe Suite, etc.',
                'base_cost' => 120.00,
                'customer_rate' => 300.00,
                'estimated_duration' => 60,
                'is_active' => true
            ],
            [
                'name' => 'Consultoría Tecnológica',
                'description' => 'Asesoría personalizada en soluciones tecnológicas',
                'base_cost' => 100.00,
                'customer_rate' => 500.00,
                'estimated_duration' => 120,
                'is_active' => true
            ]
        ];

        foreach ($services as $service) {
            BusinessService::create($service);
        }

        $this->command->info('✅ 10 servicios de negocio creados exitosamente.');
    }
}