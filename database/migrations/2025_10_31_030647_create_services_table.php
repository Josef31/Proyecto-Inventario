<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('services')) {
            Schema::create('services', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Nombre del servicio
                $table->text('description')->nullable(); // Descripción del servicio
                $table->decimal('base_cost', 10, 2); // Costo base (materiales/hora)
                $table->decimal('customer_rate', 10, 2); // Tarifa al cliente
                $table->integer('estimated_duration'); // Duración estimada en minutos
                $table->boolean('is_active')->default(true); // Si el servicio está activo
                $table->timestamps();
            });
        } else {
            // Si la tabla ya existe, solo agregar la columna faltante
            if (!Schema::hasColumn('services', 'is_active')) {
                Schema::table('services', function (Blueprint $table) {
                    $table->boolean('is_active')->default(true)->after('estimated_duration');
                });
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('services');
    }
};