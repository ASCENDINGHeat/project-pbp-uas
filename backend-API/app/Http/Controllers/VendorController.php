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
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->vendor)
        {
            return response()->json([
                'message' => 'User is already a vendor.',
                'vendor' => $user->vendor
            ], 400);
        }

        // Validate Store Data AND User Data (Phone/Address)
        $validate = $request->validate([
            'store_name' => 'required|string|max:255|unique:vendors',
            'store_description' => 'nullable|string',
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string',
            'owner_name' => 'nullable|string|max:255' // To update user name if needed
        ]);

        // 1. Update User Profile with Contact Info
        $user->update([
            'phone_number' => $validate['phone_number'],
            'address' => $validate['address'],
            'name' => $validate['owner_name'] ?? $user->name,
        ]);

        // 2. Create Vendor Profile
        $vendor = $user->vendor()->create([
            'store_name' => $validate['store_name'],
            'store_description' => $validate['store_description'] ?? '',
            'commission_rate' => 5.00,
            'balance' => 0.0
        ]);

        return response()->json([
            'message' => 'Vendor registration successful.',
            'vendor' => $vendor,
            'user' => $user
        ], 201);
    }

    public function vendorProductsView($id)
    {
        $vendor = Vendor::with('products')->find($id);

        if (!$vendor)
        {
            return response()->json([
                'message' => 'Vendor not found.'
            ], 404);
        }

        return response()->json([
            'message' => 'Vendor retrieved successfully',
            'data' => $vendor
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