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
        Schema::create('order_details', function (Blueprint $table) {
            $table->id("order_details_id");
            $table->unsignedBigInteger("order_details_order_id");
            $table->unsignedBigInteger("order_details_product_id");
            $table->integer("order_details_product_qty");
            $table->float("order_details_price",20,2)->comment("per unit price");
            $table->float("order_details_tax",20,2)->comment("tax percentage");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
