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
        Schema::create('order_product_topping', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_product_id')->constrained('order_product');
            $table->foreignId('topping_id')->constrained();
            $table->string('name');
            $table->unsignedInteger('quantity');
            $table->double('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_product_topping');
    }
};
