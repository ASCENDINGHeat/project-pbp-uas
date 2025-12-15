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
        $quantity = (int)$request->input('quantity', 1);
        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        // Validasi Stok Habis Total
        if ($product->stock_quantity <= 0) {
            return response()->json([
                'message' => 'Maaf, stok barang ini sedang habis.'
            ], 400); // 400 Bad Request
        }
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
    public function update(Request $request, $id)
    {
        $userId = Auth::id();

        // Validasi: Kita izinkan 0 sekarang (min:0)
        $request->validate([
            'quantity' => 'required|integer|min:0'
        ]);

        // Cari item milik user
        $cartItem = Cart::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Item tidak ditemukan'], 404);
        }

        // --- INI LOGIKA UTAMANYA ---
        // Jika user mengirim quantity 0, maka hapus barangnya
        if ($request->quantity <= 0) {
            $cartItem->delete();

            return response()->json([
                'status' => 'deleted',
                'message' => 'Item dihapus dari keranjang karena jumlah 0'
            ], 200);
        }

        // Jika quantity > 0, Lanjut Update seperti biasa
        // Cek Stok dulu
        if ($cartItem->product->stock_quantity < $request->quantity) {
            return response()->json([
                'message' => 'Stok tidak mencukupi. Sisa: ' . $cartItem->product->stock
            ], 400);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json([
            'status' => 'updated',
            'message' => 'Jumlah barang berhasil diubah',
            'data' => $cartItem
        ], 200);
    }
    public function destroy($id)
    {
        $userId = Auth::id();

        // Cari cart berdasarkan ID dan User ID (Security)
        $cartItem = Cart::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Item tidak ditemukan'], 404);
        }

        $cartItem->delete();

        return response()->json([
            'message' => 'Item berhasil dihapus dari keranjang'
        ], 200);
    }
}
