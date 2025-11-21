<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Deliver Product List
     * GET /api/products
     */
    public function index(Request $request)
    {
        // Fetch products with pagination (e.g., 10 per page)
        // You can add filtering here later (e.g., where('stock_quantity', '>', 0))
        $products = Product::select('product_id', 'title', 'price', 'stock_quantity', 'vendor_id')
            ->paginate(10);

        return response()->json($products, 200);
    }

    /**
     * Deliver Single Product Page
     * GET /api/products/{id}
     */
    public function show($id)
    {
        // Find product by product_id
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json($product, 200);
    }

    public function store(Request $request)
    {
        // 1. Validate the input
        $request->validate([
            'vendor_id' => 'required|integer', // Assuming you handle auth separately or pass this
            'title' => 'required|string|max:255',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);

        // 2. Handle File Upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            // Stores file in 'storage/app/public/products'
            // Returns the path like 'products/filename.jpg'
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // 3. Create the Product
        $product = Product::create([
            'vendor_id' => $request->vendor_id,
            'title' => $request->title,
            'price' => $request->price,
            'stock_quantity' => $request->stock_quantity ?? 0,
            'details' => $request->details ?? [], // logic for jsonb
            'image_path' => $imagePath, // Save the path
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product,
            'image_url' => $imagePath ? asset('storage/' . $imagePath) : null
        ], 201);
    }
}
