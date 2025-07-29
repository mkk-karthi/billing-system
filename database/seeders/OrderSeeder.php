<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Products;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class OrderSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		Schema::disableForeignKeyConstraints();
		OrderDetails::truncate();
		Order::truncate();
		User::truncate();
		Schema::enableForeignKeyConstraints();

		User::insert([
			[
				"name" => "examble",
				"email" => "examble@gmail.com",
			],
			[
				"name" => "test",
				"email" => "test@gmail.com",
			]
		]);

		$userIds = User::pluck("id")->toArray();

		for ($i = 0; $i < 2; $i++) {
			$products = Products::limit(3)->get()->toArray();

			$itemDetails = [];
			$orderTotalAmount = 0;
			$orderTaxAmount = 0;

			foreach ($products as $product) {
				$productId = $product['product_id'];
				$productQty = rand(1, 3);

				// calculate total amount and tax
				$productPrice = $product['product_price'];
				$productTax = $product['product_tax'];

				$orderTotalAmount += $productPrice * $productQty;
				$orderTaxAmount += (($productPrice / 100) * $productTax) * $productQty;

				// collect order details
				$itemDetails[] = [
					"order_details_product_id" => $productId,
					"order_details_product_qty" => $productQty,
					"order_details_price" => $productPrice,
					"order_details_tax" => $productTax,
				];
			}

			// calculate paid amount
			$denominations = [];
			$totalAmount = $orderTotalAmount + $orderTaxAmount;
			$denominationAmts = config("common.denominations");
			$j = 0;

			while ($totalAmount > 0 && count($denominationAmts) > $j) {
				$amt = $denominationAmts[$j];
				$denomination = floor($totalAmount / $amt);
				$denominations[$amt] = $denomination;
				$totalAmount = $totalAmount - ($amt * $denomination);
				$j++;
			}
			$paidAmount = 0;
			foreach ($denominations as $denomination => $count) {
				$paidAmount += ($denomination * $count);
			}

			foreach ($userIds as $userId) {

				// generate reference number
				$orderRefNo = Order::orderBy("order_id", "desc")->value("order_ref_no");
				if ($orderRefNo) ++$orderRefNo;
				else $orderRefNo = config("common.orderRefNo");

				// create order
				$order = Order::create([
					"order_user_id" => $userId,
					"order_ref_no" => $orderRefNo,
					"order_total_price" => $orderTotalAmount,
					"order_tax_price" => $orderTaxAmount,
					"order_paid_amount" => $paidAmount,
					"order_denomination" => json_encode($denominations),
				]);

				if ($order) {
					$orderId = $order->order_id;

					// create order details
					foreach ($itemDetails as $itemDetail) {
						$itemDetail["order_details_order_id"] = $orderId;

						OrderDetails::create($itemDetail);

						// maintain stock
						Products::where("product_id", $itemDetail["order_details_product_id"])->decrement("product_quantity", $itemDetail["order_details_product_qty"]);
					}
				}
			}
		}
	}
}
