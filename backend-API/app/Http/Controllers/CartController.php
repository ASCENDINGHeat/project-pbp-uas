<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addToCart(Request $request, $productId)
    {
        $userId = Auth::id();
        $condition = [
            'product_id' => $productId,
            'user_id' => $userId,
        ];

        $cartItem = Cart::where($condition)->first();
        if ($cartItem) {
            $cartItem->increment('quantity', $request->quantity);
        } else {
            Cart::create(array_merge($condition, [
                'quantity' => $request->quantity,
            ]));
        }
        return response()->json([
            'message' => 'ditambahkan',
            'data' => $cartItem
        ], 200);
    }
    public function index()
    {
        $userId = Auth::id();
        $cartItems = Cart::with('product')
            ->where('user_id', $userId)
            ->get();
        $grandTotal = $cartItems->sum(function ($item) {
            return $item->quantity * ($item->product->price ?? 0);
        });

        return response()->json([
            'status' => 'success',
            'data' => $cartItems,
            'grand_total' => $grandTotal
        ], 200);
    }
}
