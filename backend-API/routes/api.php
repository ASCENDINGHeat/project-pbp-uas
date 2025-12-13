<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishListController;
use App\Http\Controllers\OrderController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    //auth routes
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    //vendor and product management routes
    Route::post('/vendor/register', [VendorController::class, 'register']);
    Route::get('/vendor/profile', [VendorController::class, 'vendorProfile']);
    Route::post('/product', [ProductController::class, 'store']);
    Route::match(['put', 'patch'], '/product/{id}', [ProductController::class, 'update']);
    Route::delete('/product/{id}', [ProductController::class, 'destroy']);
    //cart and order routes
    Route::post('/cart/{id}', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/wishlist',[WishListController::class,'toggle']);
    Route::get('/wishlist',[WishListController::class,'view']);
    Route::post('/checkout',[OrderController::class,'checkout']);
    Route::get('/orders',[OrderController::class,'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
});

Route::get('/product', [ProductController::class, 'index']); // Product List
Route::get('/product/{id}', [ProductController::class, 'show']); // Product Page

Route::get('/vendor/{id}/products', [VendorController::class, 'vendorProductsView']); // Vendor's Products
