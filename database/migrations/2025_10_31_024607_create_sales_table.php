<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_code')->unique(); // Código único de venta
            $table->string('invoice_number')->unique()->nullable(); // Número de factura único
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Vendedor
            $table->string('customer_name')->nullable(); // Nombre del cliente
            $table->string('customer_rfc')->nullable(); // RFC del cliente
            $table->decimal('subtotal', 10, 2); // Subtotal sin impuestos
            $table->decimal('taxes', 10, 2); // Impuestos (IVA)
            $table->decimal('total', 10, 2); // Total a pagar
            $table->enum('payment_method', ['efectivo', 'tarjeta']); // Método de pago
            $table->decimal('amount_received', 10, 2)->nullable(); // Monto recibido (para efectivo)
            $table->decimal('change', 10, 2)->nullable(); // Cambio (para efectivo)
            $table->enum('status', ['completada', 'cancelada', 'pendiente'])->default('completada');
            $table->boolean('invoice_printed')->default(false); // Si la factura fue impresa
            $table->text('notes')->nullable(); // Notas adicionales
            $table->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('product_name'); // Nombre del producto al momento de la venta
            $table->decimal('price', 10, 2); // Precio al momento de la venta
            $table->integer('quantity'); // Cantidad vendida
            $table->decimal('subtotal', 10, 2); // Subtotal del item
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};