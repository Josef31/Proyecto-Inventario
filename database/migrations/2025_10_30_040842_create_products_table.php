<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->smallInteger('id_classification');
            $table->decimal('price_buy', 10, 2);
            $table->decimal('price_sell', 10, 2);
            $table->integer('stock_initial')->default(0);
            $table->integer('stock_minimum')->default(0);
            $table->date('expiration_date')->nullable();
            $table->timestamps();

            $table->foreign('id_classification')->references('id')->on('products_classification');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};