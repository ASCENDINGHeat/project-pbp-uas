<?php

namespace App\Http\Controllers;

use App\Models\WishList;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishListController extends Controller
{
    public function toggle(Request $request, $productId)
    {
        $userId = Auth::id();

        // Cek apakah sudah ada di wishlist
        $wishlist = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($wishlist) {
            // Jika sudah ada, HAPUS (Unlike)
            $wishlist->delete();
            return response()->json(['status' => 'removed', 'message' => 'Dihapus dari wishlist']);
        } else {
            // Jika belum ada, SIMPAN (Like)
            Wishlist::create([
                'user_id' => $userId,
                'product_id' => $productId
            ]);
            return response()->json(['status' => 'added', 'message' => 'Disimpan ke wishlist']);
        }
    }
    public function view()
    {
        $userId = Auth::id();
        $wishItems = WishList::with('product')
            ->where('user_id', $userId)
            ->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $wishItems,
            
        ], 200);
    }
    public function moveToCart($productId)
    {
        $userId = Auth::id();

        // 1. Cari data di wishlist
        $wishlist = Wishlist::where('user_id', $userId)->where('product_id', $productId)->first();

        if (!$wishlist) {
            return back()->with('error', 'Barang tidak ditemukan di wishlist');
        }

        // 2. Logika Masuk Keranjang (Re-use logic AddToCart yang lama atau manual)
        // Cek apakah barang sudah ada di cart user?
        $cartItem = Cart::where('user_id', $userId)->where('product_id', $productId)->first();

        if ($cartItem) {
            $cartItem->increment('quantity');
        } else {
            Cart::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => 1
            ]);
        }

        // 3. Hapus dari wishlist
        $wishlist->delete();

        return back()->with('success', 'Barang dipindahkan ke keranjang!');
    }
    public function destroy($productId)
    {
        $userId = Auth::id();

        // Cari item di wishlist user
        $wishlist = WishList::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return response()->json([
                'status' => 'removed',
                'message' => 'Produk berhasil dihapus dari wishlist'
            ], 200);
        }

        return response()->json([
            'message' => 'Produk tidak ditemukan di wishlist'
        ], 404);
    }
}
