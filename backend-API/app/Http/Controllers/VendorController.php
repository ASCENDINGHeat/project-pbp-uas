<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Vendor;
use App\Models\Product;

class VendorController extends Controller
{
    public function register(Request $request)
    {
        $user = Auth::user();
        if ($user->vendor)
        {
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

    public function vendorProductsView($id)
    {
        // Logic: Find vendor by ID and load their products simultaneously
        $vendor = Vendor::with('products')->find($id);

        if (!$vendor)
        {
            return response()->json([
                'message' => 'Vendor not found.'
            ], 404);
        }

        // Return the combined data
        return response()->json([
            'message' => 'Vendor retrieved successfully',
            'data' => $vendor // 'products' will be nested inside this object
        ], 200);
    }

    public function vendorProfile()
    {
        $user = Auth::user();
        $vendor = $user->vendor;

        if (!$vendor){
            return response()->json([
                'message' => 'User is not a vendor.'
            ], 404);
        }

        return response()->json([
            'message' => 'Vendor profile retrieved successfully.',
            'vendor' => $vendor
        ], 200);
    }
}
