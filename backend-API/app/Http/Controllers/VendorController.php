<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorController extends Controller
{
    public function register(Request $request){
        $user = Auth::user();
        if ($user->vendor){
            return response()->json([
                'message' => 'User is already a vendor.',
                'vendor' => $user->vendor
            ], 400);
        }

        $validate = $request->validate([
            'store_name' => 'required|string|max:255',
            'store_description' => 'nullable|string'
        ]);

        $vendor = $user->vendor()->create([
            'store_name' => $validate['store_name'],
            'store_description' => $validate['store_description'] ?? '',
            'commission_rate' => 5.00,
            'balance' => 0.0
        ]);

        return response()->json([
            'message' => 'Vendor registration successful.',
            'vendor' => $vendor
        ], 201);
    }
}
