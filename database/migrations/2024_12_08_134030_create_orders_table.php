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
		Schema::create('orders', function (Blueprint $table) {
			$table->id("order_id");
			$table->unsignedBigInteger("order_user_id");
			$table->string("order_ref_no");
			$table->decimal("order_total_price", 10, 4);
			$table->decimal("order_tax_price", 10, 4);
			$table->decimal("order_paid_amount", 10, 4);
			$table->decimal("order_discount", 10, 4)->default(0);
			$table->json("order_denomination");
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('orders');
	}
};
