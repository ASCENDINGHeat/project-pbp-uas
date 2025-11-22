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
        // 1. Validate the input (support single "image" or multiple "images")
        $request->validate([
            'vendor_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // 2. Handle File Uploads (multiple)
        $imagePaths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file && $file->isValid()) {
                    $imagePaths[] = $file->store('products', 'public');
                }
            }
        } elseif ($request->hasFile('image')) {
            $single = $request->file('image');
            if ($single->isValid()) {
                $imagePaths[] = $single->store('products', 'public');
            }
        }

        // 3. Create the Product
        // Note: ensure your products table has 'image_path' (string) and/or 'image_paths' (json/text) columns
        $product = Product::create([
            'vendor_id' => $request->vendor_id,
            'title' => $request->title,
            'price' => $request->price,
            'stock_quantity' => $request->stock_quantity ?? 0,
            'details' => $request->details ?? [],
            'image_path' => count($imagePaths) ? $imagePaths[0] : null,             // backward compatibility
            'image_paths' => count($imagePaths) ? json_encode($imagePaths) : null, // store all paths as JSON
        ]);

        // 4. Build public URLs for response
        $imageUrls = array_map(function ($p) {
            return asset('storage/' . $p);
        }, $imagePaths);

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product,
            'image_paths' => $imagePaths,
            'image_urls' => $imageUrls,
        ], 201);
    }
}
