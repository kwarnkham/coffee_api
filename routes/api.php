<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ToppingController;
use App\Http\Controllers\UserController;
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
        Route::post('change-password', 'changePassword')->name('auth.changePassword');
    });
    Route::post('login', 'login')->name('auth.login');
});


Route::controller(ItemController::class)->prefix('items')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::middleware(['role:admin'])->group(function () {
            Route::post('purchase', 'purchase')->name('items.purchase');
            Route::put('{item}', 'update')->name('items.update');
        });

        Route::middleware(['role:sale'])->group(function () {
            Route::get('', 'index')->name('items.index');
            Route::get('search', 'search')->name('items.search');
            Route::post('{item}/reduce-stock', 'reduceStock')->name('items.reduceStock');
        });
    });
});

Route::controller(ProductController::class)->prefix('products')->group(function () {
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('', 'store')->name('products.store');
        Route::put('{product}', 'update')->name('products.update');
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

Route::controller(ExpenseController::class)->prefix('expenses')->group(function () {
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::put('{expense}', 'update')->name('expenses.update');
        Route::post('purchase', 'purchase')->name('expenses.purchase');
        Route::get('', 'index')->name('expenses.index');
    });
});

Route::controller(RoleController::class)->prefix('roles')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('', 'index')->name('roles.index');
    });
});

Route::controller(UserController::class)->prefix('users')->group(function () {
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('', 'store')->name('users.store');
    });
    Route::middleware(['auth:sanctum', 'role:sale'])->group(function () {
        Route::get('', 'index')->name('users.index');
        Route::post('customer', 'storeCustomer')->name('users.storeCustomer');
        Route::post('{user}/cups', 'addCup')->name('users.addCup');
        Route::post('{user}/redeem', 'redeem')->name('users.redeem');
    });
});


Route::controller(PurchaseController::class)->prefix('purchases')->group(function () {
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('', 'index')->name('purchases.index');
    });
});
