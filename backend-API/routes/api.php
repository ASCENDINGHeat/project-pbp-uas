<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishListController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/cart/{id}', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/wishlist',[WishListController::class,'toggle']);
    Route::get('/wishlist',[WishListController::class,'view']);

});

Route::get('/product', [ProductController::class, 'index']); // Product List
Route::get('/product/{id}', [ProductController::class, 'show']); // Product Page
Route::post('/product', [ProductController::class, 'store']); // Create Product
