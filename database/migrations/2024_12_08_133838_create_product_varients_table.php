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
		Schema::create('product_varients', function (Blueprint $table) {
			$table->id('varient_id');
			$table->tinyInteger("varient_type")->comment("0-Other; 1-Size; 2-Color; 3-Storage; 4-Unit; 5-Material;");
			$table->string("varient_value");
			$table->unsignedBigInteger("varient_product_id");
			$table->timestamps();
		});

		Schema::table('product_varients', function (Blueprint $table) {
			$table->foreign(["varient_product_id"], "fk_product_id")->references("product_id")->on("products")->cascadeOnUpdate();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('product_varients', function (Blueprint $table) {
			$table->dropForeign("fk_product_id");
		});

		Schema::dropIfExists('product_varients');
	}
};
