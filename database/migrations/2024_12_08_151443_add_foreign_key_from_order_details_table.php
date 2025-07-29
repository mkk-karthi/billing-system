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
        Schema::table('order_details', function (Blueprint $table) {
            $table->foreign(['order_details_order_id'], 'order_details_ibfk_1')->references('order_id')->on('orders')->onUpdate('CASCADE');
            $table->foreign(['order_details_product_id'], 'order_details_ibfk_2')->references('product_id')->on('products')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropForeign('order_details_ibfk_2');
            $table->dropForeign('order_details_ibfk_1');
        });
    }
};
