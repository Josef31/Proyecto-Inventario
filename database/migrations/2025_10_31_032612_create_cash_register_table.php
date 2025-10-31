<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Cajero
            $table->decimal('initial_amount', 10, 2); // Fondo inicial
            $table->decimal('final_amount', 10, 2)->nullable(); // Monto final físico
            $table->decimal('expected_amount', 10, 2)->nullable(); // Monto esperado
            $table->decimal('cash_sales', 10, 2)->default(0); // Ventas en efectivo
            $table->decimal('difference', 10, 2)->nullable(); // Diferencia
            $table->enum('status', ['abierta', 'cerrada'])->default('abierta');
            $table->timestamp('opened_at')->useCurrent(); // Fecha de apertura
            $table->timestamp('closed_at')->nullable(); // Fecha de cierre
            $table->text('notes')->nullable(); // Observaciones
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cash_registers');
    }
};