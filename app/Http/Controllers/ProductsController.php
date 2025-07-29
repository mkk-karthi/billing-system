<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\ProductVarients;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		$varientTypes = config('common.productVarientTypes');
		$products = Products::with('varients')->paginate(10);

		$productsData = [];
		foreach ($products as $product) {
			$productsData[] = [
				"product_id" => $product->product_id,
				"product_sku" => $product->product_sku,
				"product_name" => $product->product_name,
				"product_image" => Storage::disk("public")->url($product->product_image ?? "products/empty.svg"),
				"product_quantity" => $product->product_quantity,
				"product_price" => $product->product_price,
				"varients" => $product->varients->toArray(),
				"varient_name" => join(", ", array_map(fn($var) => $varientTypes[$var['varient_type']] . ': ' . $var['varient_value'], $product->varients->toArray())),
			];
		}
		return view('products.index', ["products" => $products, "productsData" => $productsData]);
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create()
	{
		return view('products.create');
	}
	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		$inputs = $request->all();
		$varientTypes = array_keys(config('common.productVarientTypes'));

		// validation
		$validator = Validator::make($inputs, [
			"sku" => "required|string|max:50|unique:products,product_sku",
			"name" => "required|string|max:120|regex:/^(?=.*[a-zA-Z])[a-zA-Z0-9]+$/",
			"image" => "nullable|file|mimes:png,jpeg,jpg|max:1024",
			"quantity" => "required|numeric",
			"price" => "required|numeric",
			"tax" => "required|numeric",
			"varient" => "required|array",
			"varient.*.type" => "required|in:" . join(',', $varientTypes),
			"varient.*.value" => "required|string",
		], [
			"varient.*.type.required" => "Type is required.",
			"varient.*.value.required" => "Value is required.",
			"varient.*.value.string" => "Value must be a string.",
		]);

		// validation errors
		if ($validator->fails()) {
			return response()->json(["code" => 1, "errors" => $validator->errors()->toArray(), "input" => $inputs]);
		} else {
			try {
				// start tansaction
				DB::beginTransaction();

				// file upload
				$uploadedPath = "";
				if ($file = $request->file("image")) {
					$uploadedPath = Storage::disk("public")->putFile("products", $file);
				}

				// insert products
				$productInput = [
					"product_sku" => $inputs["sku"],
					"product_name" => $inputs["name"],
					"product_quantity" => $inputs["quantity"],
					"product_price" => $inputs["price"],
					"product_tax" => $inputs["tax"],
					"product_image" => $uploadedPath
				];

				$product = Products::create($productInput);

				if ($product) {
					$productId = $product->product_id;

					// insert varient details
					foreach ($inputs["varient"] as $varient) {

						$varientInput = [
							"varient_type" => $varient["type"],
							"varient_value" => $varient["value"],
							"varient_product_id" => $productId
						];

						ProductVarients::create($varientInput);
					}
				}

				DB::commit();

				return response()->json(["code" => 0, "message" => "Product succesfully created"]);
			} catch (Exception $ex) {
				DB::rollBack();

				// delete uploaded file
				if ($uploadedPath)
					Storage::disk("public")->delete($uploadedPath);

				return response()->json(["code" => 2, "message" => $ex->getMessage()]);
			}
		}
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit($id)
	{
		// get product
		$product = Products::with(["varients"])->where("product_id", $id)->first();

		if (is_null($product)) {
			return abort(404);
		} else {

			$product = $product->toArray();

			return view('products.create', ["id" => $id, "product" => $product]);
		}
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request)
	{
		$inputs = $request->all();
		$varientTypes = array_keys(config('common.productVarientTypes'));
		$productId = $inputs['id'] ?? "";

		$validator = Validator::make($inputs, [
			"id" => "required|exists:products,product_id",
			"sku" => "required|string|max:50|unique:products,product_sku,$productId,product_id",
			"name" => "required|string|max:120|regex:/^(?=.*[a-zA-Z])[a-zA-Z0-9]+$/",
			"image" => "nullable|file|mimes:png,jpeg,jpg|max:1024",
			"quantity" => "required|numeric",
			"price" => "required|numeric",
			"tax" => "required|numeric",
			"varient" => "required|array",
			"varient.*.type" => "required|in:" . join(',', $varientTypes),
			"varient.*.value" => "required|string",
		], [
			"varient.*.type.required" => "Type is required.",
			"varient.*.value.required" => "Value is required.",
			"varient.*.value.string" => "Value must be a string.",
		]);

		if ($validator->fails()) {
			return response()->json(["code" => 1, "errors" => $validator->errors()->toArray(), "input" => $inputs]);
		} else {

			try {
				// start tansaction
				DB::beginTransaction();

				// file upload
				$uploadedPath = "";
				if ($file = $request->file("image")) {

					$uploadedPath = Storage::disk("public")->putFile("products", $file);
					if (!$uploadedPath) {
						DB::rollBack();
						return response()->json(["code" => 2, "message" => "Image Not uploaded"]);
					}
				}

				$product = Products::where("product_id", $productId)->first();
				$productImage = $product->product_image;

				// insert products
				$productInput = [
					"product_sku" => $inputs["sku"],
					"product_name" => $inputs["name"],
					"product_quantity" => $inputs["quantity"],
					"product_price" => $inputs["price"],
					"product_tax" => $inputs["tax"],
				];

				if ($uploadedPath) {
					$productInput["product_image"] = $uploadedPath;
				}

				$update_product = Products::where("product_id", $productId)->update($productInput);

				if ($update_product) {
					$productId = $inputs['id'];

					$oldVarientIds = ProductVarients::where("varient_product_id", $productId)->pluck('varient_id')->toArray();
					$newVarientIds = array_map(fn($v) => trim($v), array_column($inputs["varient"], "id"));

					$deleteVarientIds = array_diff($oldVarientIds, $newVarientIds);
					ProductVarients::whereIn("varient_id", $deleteVarientIds)->delete();

					// insert varient details
					foreach ($inputs["varient"] as $varient) {

						$varientInput = [
							"varient_type" => $varient["type"],
							"varient_value" => $varient["value"],
							"varient_product_id" => $productId
						];

						if (!empty($varient["id"])) {
							ProductVarients::where("varient_id", $varient["id"])->update($varientInput);
						} else {
							ProductVarients::create($varientInput);
						}
					}

					// delete old uploaded file
					if ($uploadedPath && $productImage)
						Storage::disk("public")->delete($productImage);
				}

				DB::commit();

				return response()->json(["code" => 0, "message" => "Product succesfully updated"]);
			} catch (Exception $ex) {
				DB::rollBack();

				// delete uploaded file
				if ($uploadedPath)
					Storage::disk("public")->delete($uploadedPath);

				return response()->json(["code" => 2, "message" => $ex->getMessage()]);
			}
		}
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Request $request)
	{
		try {
			$inputs = $request->all();
			$id = $inputs["id"];

			$product = Products::find($id);

			if (is_null($product)) {
				return response()->json(["code" => 2, "message" => "Product not found"]);
			} else {
				DB::beginTransaction();

				// delete Product Varients
				ProductVarients::where("varient_product_id", $id)->delete();

				// delete product
				Products::where("product_id", $id)->delete();

				// delete uploaded file
				if ($product->product_image)
					Storage::disk("public")->delete($product->product_image);

				DB::commit();
				return response()->json(["code" => 0, "message" => "Product succesfully deleted"]);
			}
		} catch (Exception $ex) {
			DB::rollBack();

			return response()->json(["code" => 2, "message" => $ex->getMessage()]);
		}
	}
}
