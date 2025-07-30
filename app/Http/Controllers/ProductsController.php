<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\ProductVariants;
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
		$variantTypes = config('common.productVariantTypes');
		$products = Products::with('variants')->paginate(10);

		$productsData = [];
		foreach ($products as $product) {
			$productsData[] = [
				"product_id" => $product->product_id,
				"product_sku" => $product->product_sku,
				"product_name" => $product->product_name,
				"product_image" => Storage::disk("public")->url($product->product_image ?? "products/empty.svg"),
				"product_quantity" => $product->product_quantity,
				"product_price" => $product->product_price,
				"variants" => $product->variants->toArray(),
				"variant_name" => join(", ", array_map(fn($var) => $variantTypes[$var['variant_type']] . ': ' . $var['variant_value'], $product->variants->toArray())),
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
		$variantTypes = array_keys(config('common.productVariantTypes'));

		// validation
		$validator = Validator::make($inputs, [
			"sku" => "required|string|max:50|unique:products,product_sku",
			"name" => "required|string|max:120|regex:/^(?=.*[a-zA-Z])[a-zA-Z0-9]+$/",
			"image" => "nullable|file|mimes:png,jpeg,jpg|max:1024",
			"quantity" => "required|numeric",
			"price" => "required|numeric",
			"tax" => "required|numeric",
			"variant" => "required|array",
			"variant.*.type" => "required|in:" . join(',', $variantTypes),
			"variant.*.value" => "required|string",
		], [
			"variant.*.type.required" => "Type is required.",
			"variant.*.value.required" => "Value is required.",
			"variant.*.value.string" => "Value must be a string.",
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

					// insert variant details
					foreach ($inputs["variant"] as $variant) {

						$variantInput = [
							"variant_type" => $variant["type"],
							"variant_value" => $variant["value"],
							"variant_product_id" => $productId
						];

						ProductVariants::create($variantInput);
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
		$product = Products::with(["variants"])->where("product_id", $id)->first();

		if (!empty($product)) {

			$product = $product->toArray();
			return view('products.create', ["id" => $id, "product" => $product]);
		} else {
			return view('no_data');
		}
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request)
	{
		$inputs = $request->all();
		$variantTypes = array_keys(config('common.productVariantTypes'));
		$productId = $inputs['id'] ?? "";

		$validator = Validator::make($inputs, [
			"id" => "required|exists:products,product_id",
			"sku" => "required|string|max:50|unique:products,product_sku,$productId,product_id",
			"name" => "required|string|max:120|regex:/^(?=.*[a-zA-Z])[a-zA-Z0-9]+$/",
			"image" => "nullable|file|mimes:png,jpeg,jpg|max:1024",
			"quantity" => "required|numeric",
			"price" => "required|numeric",
			"tax" => "required|numeric",
			"variant" => "required|array",
			"variant.*.type" => "required|in:" . join(',', $variantTypes),
			"variant.*.value" => "required|string",
		], [
			"variant.*.type.required" => "Type is required.",
			"variant.*.value.required" => "Value is required.",
			"variant.*.value.string" => "Value must be a string.",
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

					$oldVariantIds = ProductVariants::where("variant_product_id", $productId)->pluck('variant_id')->toArray();
					$newVariantIds = array_map(fn($v) => trim($v), array_column($inputs["variant"], "id"));

					$deleteVariantIds = array_diff($oldVariantIds, $newVariantIds);
					ProductVariants::whereIn("variant_id", $deleteVariantIds)->delete();

					// insert variant details
					foreach ($inputs["variant"] as $variant) {

						$variantInput = [
							"variant_type" => $variant["type"],
							"variant_value" => $variant["value"],
							"variant_product_id" => $productId
						];

						if (!empty($variant["id"])) {
							ProductVariants::where("variant_id", $variant["id"])->update($variantInput);
						} else {
							ProductVariants::create($variantInput);
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

				// delete Product Variants
				ProductVariants::where("variant_product_id", $id)->delete();

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
