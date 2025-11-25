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
        // 1. Start the query builder (don't get() or paginate() yet)
        $query = Product::query();

        // 2. Apply Filters based on Request Parameters

        // A. Search by Title (Partial Match)
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where('title', 'ILIKE', "%{$searchTerm}%"); // Use ILIKE for case-insensitive (PostgreSQL)
        }

        // B. Filter by Vendor
        if ($request->has('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // C. Filter by Price Range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // D. Sort/Order (Optional)
        $sortDirection = $request->input('sort', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy('price', $sortDirection);


        // 3. Execute Pagination (Dynamic per_page)
        $perPage = $request->query('per_page', 10);
        $products = $query->paginate($perPage);

        // 4. Append query parameters to pagination links
        // This ensures next_page_url includes &search=... and &min_price=...
        $products->appends($request->all());

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
