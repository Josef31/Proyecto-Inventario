<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->string('invoice_number', 255)->primary();
            $table->string('sale_code', 255)->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('cash_register_id');
            $table->unsignedBigInteger('customer_id');
            
            // Moneda de la Venta
            $table->string('payment_currency', 3)->default('M1');
            $table->decimal('exchange_rate_used', 10, 4)->default(1.0000);
            
            $table->decimal('taxes', 10, 2)->default(0.00);
            $table->smallInteger('payment_method_id');
            $table->decimal('amount_received', 10, 2);
            $table->decimal('change', 10, 2);
            $table->string('status', 10)->default('completada');
            $table->boolean('invoice_printed')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('cash_register_id')->references('id')->on('cash_registers');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('payment_method_id')->references('id')->on('payment_method');
        });

        // Restricción CHECK para PostgreSQL
        DB::statement("ALTER TABLE sales ADD CONSTRAINT sales_status_check CHECK (status IN ('completada', 'cancelada', 'pendiente'))");

    }

    public function down()
    {
        Schema::dropIfExists('sales');
    }
};