<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Products;
use App\Models\User;
use App\Notifications\OrderReceipt;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{

	public function index()
	{
		$products = Products::get()->toArray();
		$emails = User::get()->pluck("email");
		return view('billing', [
			"products" => json_encode($products),
			"emails" => json_encode($emails),
			"denominations" => config("common.denominationFlag") ? config("common.denominations") : []
		])->with("error_messages", []);
	}

	public function create(Request $request)
	{
		$errors = [];
		$orderId = "";
		$inputs = $request->all();

		// validation
		$validator = Validator::make($inputs, [
			'email' => 'required|required',
			'items' => 'required|array|max:' . config('common.maxBillingProducts'),
			'items.*.product_id' => 'required|required_with:items.*.quantity|exists:products,product_id',
			'items.*.quantity' => 'required|required_with:items.*.product_id|numeric|min:1|max:10',
			'denominations' => 'nullable|array'
		], [
			"items.*.product_id.exists" => "The selected product is invalid.",
			"items.*.product_id.required_with" => "The product field is required when quantity is present.",
			"items.*.quantity.required_with" => "The quantity field is required when product is present."
		]);
		if ($validator->fails()) {
			return response()->json(["code" => 1, "errors" => $validator->errors()->toArray(), "input" => $inputs]);
		}

		// collect the product details
		$selectedProducts = [];
		foreach ($inputs["items"] as $item) {

			if ($item["product_id"]) {
				if (isset($selectedProducts[$item["product_id"]]))
					$selectedProducts[$item["product_id"]] += $item["quantity"];
				else
					$selectedProducts[$item["product_id"]] = $item["quantity"];
			}
		}

		$itemDetails = [];
		$orderTotalAmount = 0;
		$orderTaxAmount = 0;

		if (count($selectedProducts) > 0) {
			$products = Products::whereIn("product_id", array_keys($selectedProducts))->get();

			foreach ($products as $selectedProduct) {

				$productId = $selectedProduct->product_id;
				$productQty = $selectedProducts[$productId];
				$productQuantity = $selectedProduct->product_quantity;

				// check product stock
				if ($productQty > $productQuantity) {
					$productName = $selectedProduct->product_name;
					$productSku = $selectedProduct->product_sku;
					$errors[] = "$productSku - $productName: The product may not have more than $productQuantity quantity.";
				} else {

					// calculate total amount and tax
					$productPrice = $selectedProduct->product_price;
					$productTax = $selectedProduct->product_tax;

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
			}
		}

		$totalAmount = floor($orderTotalAmount + $orderTaxAmount);
		$discount = $inputs["discount"] ?? 0;
		$totalDiscountedAmount = $totalAmount - $discount;

		// calculate paid amount
		$denominations = array_map(fn($v) => empty($v) ? 0 : $v, $inputs["denominations"]);
		if (!config("common.denominationFlag")) {

			// calculate denominations
			$denominations = [];
			$denominationAmount = $totalDiscountedAmount;
			$denominationAmts = config("common.denominations");
			$j = 0;

			while ($denominationAmount > 0 && count($denominationAmts) > $j) {
				$amt = $denominationAmts[$j];
				$denomination = floor($denominationAmount / $amt);
				$denominations[$amt] = $denomination;
				$denominationAmount = $denominationAmount - ($amt * $denomination);
				$j++;
			}
		}
		$paidAmount = 0;
		foreach ($denominations as $denomination => $count) {
			$paidAmount += ($denomination * $count);
		}

		// check paid amount
		if (count($errors) == 0 && $paidAmount > ($totalDiscountedAmount)) {
			$errors[] = "Paid amount must be less than or equal to $totalDiscountedAmount $paidAmount";
		}

		// check errors
		if (count($errors)) {
			return response()->json(["code" => 2, "message" => join(" ", $errors)]);
		}

		// start transaction
		DB::beginTransaction();

		try {
			// check and create user
			$email = $inputs["email"];
			$name = explode("@", $email)[0];
			$user = User::updateorcreate(["email" => $email], ["name" => $name]);

			// generate reference number
			$orderRefNo = Order::orderBy("order_id", "desc")->value("order_ref_no");
			if ($orderRefNo) ++$orderRefNo;
			else $orderRefNo = config("common.orderRefNo");

			// create order
			$order = Order::create([
				"order_user_id" => $user->id,
				"order_ref_no" => $orderRefNo,
				"order_total_price" => $orderTotalAmount,
				"order_tax_price" => $orderTaxAmount,
				"order_paid_amount" => $paidAmount,
				"order_discount" => $discount,
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

			DB::commit();

			// send notification
			if (config("common.sendNotification")) {
				$user->notify(new OrderReceipt($orderId));
			}

			return response()->json([
				"code" => 0,
				"message" => "Bill generated",
				"data" => [
					"id" => $orderId,
					"redirct" => route('order.view', ["id" => $orderId])
				]
			]);
		} catch (Exception $ex) {
			DB::rollBack();
			return response()->json(["code" => 2, "message" => $ex->getMessage()]);
		}
	}

	public function view($id)
	{
		$data = $this->getOrderDetails($id);

		if (!empty($data)) {
			return view('order_receipt', [
				"order" => $data,
				"mailUrl" => route("order.sendMail", ["id" => $id]),
				"printData" => view('mail.receipt', ["order" => $data])
			]);
		} else {
			return view('no_data');
		}
	}

	public function getOrderDetails($id)
	{
		$data = [];
		$variantTypes = config("common.productVariantTypes");

		// get order
		$order = Order::with(["user", "order_details.product.variants"])->where("order_id", $id)->first();

		if (!empty($order)) {
			$order = $order->toArray();
			// get user details
			$user = $order['user'];
			$name = $user['name'];
			$email = $user['email'];
			$userId = $user['id'];

			$invoiceNo = $order['order_ref_no'];
			$paidAmount = $order['order_paid_amount'];
			$discount = $order['order_discount'];
			$orderDenomination = config("common.denominationFlag") ? json_decode($order['order_denomination'], true) : [];

			$orderDetails = [];
			$orderTotalAmount = 0;
			$orderTaxAmount = 0;

			// fetch order details
			foreach ($order['order_details'] as $orderDetail) {
				$product = $orderDetail['product'];
				$productName = $product['product_name'];
				$productSku = $product['product_sku'];

				$variants = $product['variants'];
				$variantsName = join(", ", array_map(fn($variant) => $variantTypes[$variant["variant_type"]] . ": " . $variant["variant_value"], $variants));

				// calculate order details
				$productPrice = $orderDetail['order_details_price'];
				$productQty = $orderDetail['order_details_product_qty'];
				$orderTax = $orderDetail['order_details_tax'];

				$orderPrice = $productPrice * $productQty;
				$taxPrice = ($orderPrice / 100) * $orderTax;
				$totalPrice = $orderPrice + $taxPrice;

				$orderTotalAmount += $orderPrice;
				$orderTaxAmount += $taxPrice;

				$orderDetails[] = [
					"product_name" => $productName,
					"variant_name" => $variantsName,
					"product_sku" => $productSku,
					"product_price" => $productPrice,
					"product_qty" => $productQty,
					"order_tax" => $orderTax,
					"tax_price" => self::roundNumber($taxPrice, 2),
					"total_price" => self::roundNumber($totalPrice, 2),
				];
			}

			// calculate net price
			$netPrice = ($orderTotalAmount + $orderTaxAmount - $discount);
			$roundNetPrice = floor($netPrice);
			$balanceAmount = $roundNetPrice - $paidAmount;

			$data = [
				"user_id" => $userId,
				"user" => [
					"name" => $name,
					"email" => $email
				],
				"date" => date("Y-m-d", strtotime($order["created_at"])),
				"time" => date("H:i:s", strtotime($order["created_at"])),
				"order_details" => $orderDetails,
				"invoice_no" => $invoiceNo,
				"order_total_price" => self::roundNumber($orderTotalAmount, 2),
				"order_tax_price" => self::roundNumber($orderTaxAmount, 2),
				"net_price" => self::roundNumber($netPrice, 2),
				"round_net_price" => self::roundNumber($roundNetPrice, 2),
				"order_paid_amount" => self::roundNumber($paidAmount, 2),
				"order_discount" => self::roundNumber($discount, 2),
				"order_balance_amount" => self::roundNumber($balanceAmount, 2),
				"order_denominations" => $orderDenomination,
			];
		}

		return $data;
	}

	public static function roundNumber($number, $precision = 2)
	{
		$string_number = (string)$number;
		$dot_position = strpos($string_number, '.');

		if ($dot_position !== false) {
			$truncated_number = substr($string_number, 0, $dot_position + ($precision + 1));
		} else {
			$truncated_number = "$string_number.00"; // No decimal part
		}
		return $truncated_number;
	}

	public function ordersList()
	{
		$emails = User::get()->pluck("email", "id");

		return view('orders', ["emails" => $emails]);
	}

	public function checkUser(Request $request)
	{
		// validate user
		$request->validate([
			'email' => 'required|required|exists:users,id',
		]);
		$inputs = $request->all();

		return redirect()->route("user.orders", ["id" => $inputs["email"]]);
	}

	public function userOrders($id)
	{
		// get user orders
		$orders = Order::with("order_details")->where('order_user_id', $id)->orderBy("order_id", "desc")->get();

		// get user
		$user = User::find($id);

		$data = [];
		if (!empty($orders)) {
			foreach ($orders as $order) {

				// fetch order details
				$orderId = $order->order_id;
				$refNo = $order->order_ref_no;
				$totalPrice = $order->order_total_price;
				$taxPrice = $order->order_tax_price;
				$paidAmount = $order->order_paid_amount;
				$discount = $order->order_discount;
				$order_details = $order->order_details;

				$data[] = [
					"order_id" => $orderId,
					"ref_no" => $refNo,
					"total_price" => number_format($totalPrice, 2, '.', ''),
					"tax_price" => number_format($taxPrice, 2, '.', ''),
					"paid_amount" => number_format($paidAmount, 2, '.', ''),
					"discount" => number_format($discount, 2, '.', ''),
					"total_items" => count($order_details)
				];
			}
		}

		if (!empty($data)) {
			return view('user_orders', ["orders" => $data, "user" => $user]);
		} else {
			return view('no_data');
		}
	}

	public function sendMail($orderId)
	{
		try {
			$order = Order::find($orderId);
			if (empty($order)) {
				return response()->json(["code" => 2, "message" => "Order not found"]);
			}

			$userId = $order->order_user_id;

			$user = User::find($userId);
			if (empty($user)) {
				return response()->json(["code" => 2, "message" => "Invalid user"]);
			}

			// send notification
			if (config("common.sendNotification")) {
				$user->notify(new OrderReceipt($orderId));
			}

			return response()->json([
				"code" => 0,
				"message" => "Mail sened"
			]);
		} catch (Exception $ex) {
			return response()->json(["code" => 2, "message" => $ex->getMessage()]);
		}
	}
}
