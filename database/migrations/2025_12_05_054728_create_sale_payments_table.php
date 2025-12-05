<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->string('sale_id'); // FK to sales.invoice_number
            $table->smallInteger('payment_method_id');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3); // 'Bs' or 'USD'
            $table->timestamps();

            $table->foreign('sale_id')->references('invoice_number')->on('sales')->onDelete('cascade');
            $table->foreign('payment_method_id')->references('id')->on('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};
