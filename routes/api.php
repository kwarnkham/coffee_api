<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ToppingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(AuthController::class)->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('user', 'user')->name('auth.user');
        Route::post('logout', 'logout')->name('auth.logout');
    });
    Route::post('login', 'login')->name('auth.login');
});


Route::controller(ItemController::class)->prefix('items')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('purchase', 'purchase')->name('items.purchase');
        Route::get('', 'index')->name('items.index');
        Route::get('search', 'search')->name('items.search');
        Route::put('{item}', 'update')->name('items.update');
        Route::post('{item}/reduce-stock', 'reduceStock')->name('items.reduceStock');
    });
});

Route::controller(ProductController::class)->prefix('products')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('{product}/stock', 'stock')->name('products.stock');
    });
    Route::get('', 'index')->name('products.index');
});

Route::controller(ToppingController::class)->prefix('toppings')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('', 'index')->name('toppings.index');
    });
});

Route::controller(OrderController::class)->prefix('orders')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('', 'store')->name('orders.store');
        Route::get('{order}', 'show')->name('orders.show');
        Route::put('{order}', 'update')->name('orders.update');
        Route::get('', 'index')->name('orders.index');
    });
});
