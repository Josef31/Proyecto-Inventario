<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('exchange_rate_id');
            
            // Moneda 1 (Principal)
            $table->decimal('initial_amount_moneda1', 10, 2);
            $table->decimal('cash_sales_moneda1', 10, 2)->default(0.00);
            $table->decimal('final_amount_moneda1', 10, 2)->nullable();
            
            // Moneda 2 (Secundaria)
            $table->decimal('initial_amount_moneda2', 10, 2)->default(0.00);
            $table->decimal('cash_sales_moneda2', 10, 2)->default(0.00);
            $table->decimal('final_amount_moneda2', 10, 2)->nullable();
            
            $table->string('status', 10)->default('abierta');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('exchange_rate_id')->references('id')->on('exchange_rates');
        });

        // Restricción CHECK para PostgreSQL
        DB::statement("ALTER TABLE cash_registers ADD CONSTRAINT cash_registers_status_check CHECK (status IN ('abierta', 'cerrada'))");
    }

    public function down()
    {
        Schema::dropIfExists('cash_registers');
    }
};