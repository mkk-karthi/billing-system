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
		Schema::create('product_variants', function (Blueprint $table) {
			$table->id('variant_id');
			$table->tinyInteger("variant_type")->comment("0-Other; 1-Size; 2-Color; 3-Storage; 4-Unit; 5-Material;");
			$table->string("variant_value");
			$table->unsignedBigInteger("variant_product_id");
			$table->timestamps();
		});

		Schema::table('product_variants', function (Blueprint $table) {
			$table->foreign(["variant_product_id"], "fk_product_id")->references("product_id")->on("products")->cascadeOnUpdate();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('product_variants', function (Blueprint $table) {
			$table->dropForeign("fk_product_id");
		});

		Schema::dropIfExists('product_variants');
	}
};
