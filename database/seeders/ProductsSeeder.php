<?php

namespace Database\Seeders;

use App\Models\Products;
use App\Models\ProductVariants;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{

		$variantTypes = array_flip(config("common.productVariantTypes"));

		$insertDatas = [
			[
				"product_name" => "Laptop",
				"product_quantity" => 50,
				"product_price" => 799.99,
				"product_tax" => "18.00",
				"variants" => [
					[
						"variant_type" => $variantTypes["storage"],
						"variant_value" => "8GB"
					],
					[
						"variant_type" => $variantTypes["color"],
						"variant_value" => "Black"
					]
				]
			],
			[
				"product_name" => "Laptop",
				"product_quantity" => 50,
				"product_price" => 999.99,
				"product_tax" => "18.00",
				"variants" => [
					[
						"variant_type" => $variantTypes["storage"],
						"variant_value" => "16GB"
					],
					[
						"variant_type" => $variantTypes["color"],
						"variant_value" => "White"
					]
				]
			],
			[
				"product_name" => "Smart Watch",
				"product_quantity" => 120,
				"product_price" => 499.99,
				"product_tax" => "15.00",
				"variants" => [
					[
						"variant_type" => $variantTypes["color"],
						"variant_value" => "Blue"
					]
				]
			],
			[
				"product_name" => "Smart Watch",
				"product_quantity" => 120,
				"product_price" => 499.99,
				"product_tax" => "15.00",
				"variants" => [
					[
						"variant_type" => $variantTypes["color"],
						"variant_value" => "Red"
					]
				]
			],
			[
				"product_name" => "Smart Watch",
				"product_quantity" => 120,
				"product_price" => 499.99,
				"product_tax" => "15.00",
				"variants" => [
					[
						"variant_type" => $variantTypes["color"],
						"variant_value" => "Black"
					]
				]
			],
			[
				"product_name" => "Wireless Headset",
				"product_quantity" => 80,
				"product_price" => 129.99,
				"product_tax" => "10.00",
				"variants" => [
					[
						"variant_type" => $variantTypes["color"],
						"variant_value" => "Black"
					]
				]
			],
			[
				"product_name" => "T-Shirt",
				"product_quantity" => 50,
				"product_price" => 199.99,
				"product_tax" => "12.00",
				"variants" => [
					[
						"variant_type" => $variantTypes["size"],
						"variant_value" => "S"
					],
					[
						"variant_type" => $variantTypes["color"],
						"variant_value" => "Black"
					]
				]
			],
			[
				"product_name" => "T-Shirt",
				"product_quantity" => 50,
				"product_price" => 199.99,
				"product_tax" => "12.00",
				"variants" => [
					[
						"variant_type" => $variantTypes["size"],
						"variant_value" => "S"
					],
					[
						"variant_type" => $variantTypes["color"],
						"variant_value" => "White"
					]
				]
			],
			[
				"product_name" => "T-Shirt",
				"product_quantity" => 50,
				"product_price" => 199.99,
				"product_tax" => "12.00",
				"variants" => [
					[
						"variant_type" => $variantTypes["size"],
						"variant_value" => "M"
					],
					[
						"variant_type" => $variantTypes["color"],
						"variant_value" => "Black"
					]
				]
			],
			[
				"product_name" => "T-Shirt",
				"product_quantity" => 50,
				"product_price" => 199.99,
				"product_tax" => "12.00",
				"variants" => [
					[
						"variant_type" => $variantTypes["size"],
						"variant_value" => "M"
					],
					[
						"variant_type" => $variantTypes["color"],
						"variant_value" => "White"
					]
				]
			],
			[
				"product_name" => "T-Shirt",
				"product_quantity" => 50,
				"product_price" => 199.99,
				"product_tax" => "12.00",
				"variants" => [
					[
						"variant_type" => $variantTypes["size"],
						"variant_value" => "L"
					],
					[
						"variant_type" => $variantTypes["color"],
						"variant_value" => "Black"
					]
				]
			],
			[
				"product_name" => "T-Shirt",
				"product_quantity" => 50,
				"product_price" => 199.99,
				"product_tax" => "12.00",
				"variants" => [
					[
						"variant_type" => $variantTypes["size"],
						"variant_value" => "L"
					],
					[
						"variant_type" => $variantTypes["color"],
						"variant_value" => "White"
					]
				]
			],
			[
				"product_name" => "Keyboard",
				"product_quantity" => 90,
				"product_price" => 89.99,
				"product_tax" => "8.00",
				"variants" => [
					[
						"variant_type" => $variantTypes["color"],
						"variant_value" => "Black"
					]
				]
			],
			[
				"product_name" => "Keyboard",
				"product_quantity" => 90,
				"product_price" => 89.99,
				"product_tax" => "8.00",
				"variants" => [
					[
						"variant_type" => $variantTypes["color"],
						"variant_value" => "White"
					]
				]
			],
			[
				"product_name" => "Mouse",
				"product_quantity" => 150,
				"product_price" => 39.99,
				"product_tax" => "8.00",
				"variants" => [
					[
						"variant_type" => $variantTypes["color"],
						"variant_value" => "Black"
					]
				]
			],
			[
				"product_name" => "Mouse",
				"product_quantity" => 150,
				"product_price" => 39.99,
				"product_tax" => "8.00",
				"variants" => [
					[
						"variant_type" => $variantTypes["color"],
						"variant_value" => "White"
					]
				]
			]
		];

		$sku = config("common.SkuNo");
		foreach ($insertDatas as $insertData) {
			$product = Products::create([
				"product_name" => $insertData["product_name"],
				"product_sku" => $sku,
				"product_quantity" => $insertData["product_quantity"],
				"product_price" => $insertData["product_price"],
				"product_tax" => $insertData["product_tax"]
			]);

			$sku = ++$sku;

			$variants = array_map(fn($var) => array_merge($var, ["variant_product_id" => $product->product_id]), $insertData["variants"]);

			ProductVariants::insert($variants);
		}
	}
}
