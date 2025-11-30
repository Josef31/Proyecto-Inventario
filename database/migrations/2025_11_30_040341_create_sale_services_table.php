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
        Schema::create('sale_services', function (Blueprint $table) {
            $table->string('sale_id', 255);
            $table->unsignedBigInteger('business_service_id');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->primary(['sale_id', 'business_service_id']);
            $table->foreign('sale_id')->references('invoice_number')->on('sales');
            $table->foreign('business_service_id')->references('id')->on('business_services');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_services');
    }
};
