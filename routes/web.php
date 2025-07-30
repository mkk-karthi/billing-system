<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/products', [ProductsController::class, "index"])->name('products');
Route::get('/product/create', [ProductsController::class, "create"])->name('product.create');
Route::post('/product/store', [ProductsController::class, "store"])->name('product.store');
Route::get('/product/edit/{id}', [ProductsController::class, "edit"])->name('product.create');
Route::post('/product/update', [ProductsController::class, "update"])->name('product.update');
Route::post('/product/delete', [ProductsController::class, "destroy"])->name('product.delete');

Route::get('/', [OrderController::class, "index"])->name('bill');
Route::post('order/create', [OrderController::class, "create"])->name("order.create");
Route::get('order/{id}', [OrderController::class, "view"])->name("order.view");
Route::get('/orders', [OrderController::class, "ordersList"])->name('orders');
Route::post('/orders', [OrderController::class, "checkUser"])->name("user.check");
Route::get('/order/user/{id}', [OrderController::class, "userOrders"])->name("user.orders");
Route::get('/order/sendMail/{id}', [OrderController::class, "sendMail"])->name("order.sendMail");
