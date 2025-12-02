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
            // Use a portable case-insensitive search that works on MySQL and PostgreSQL
            $query->whereRaw('LOWER(title) LIKE ?', [strtolower("%{$searchTerm}%")]);
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

        // D. Sort/Order (Complex Logic)
        $sortBy = $request->input('sort_by', 'price'); // price, title, created_at, stock_quantity
        $sortDirection = $request->input('sort', 'asc') === 'desc' ? 'desc' : 'asc';

        // Validate sort_by to prevent SQL injection
        $allowedSortFields = ['price', 'title', 'created_at', 'stock_quantity', 'vendor_id'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'price';
        }

        $query->orderBy($sortBy, $sortDirection);

        // Secondary sort (if primary sort is not price, also sort by price)
        if ($sortBy !== 'price') {
            $query->orderBy('price', $sortDirection);
        }


        // 3. Execute Pagination (Dynamic per_page)
        $perPage = $request->query('per_page', 10);
        $products = $query->paginate($perPage);

        // 4. Append query parameters to pagination links
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
        }

        // 3. Create the Product
        // Note: ensure your products table has 'image_path' (string) and/or 'image_paths' (json/text) columns
        $product = Product::create([
            'vendor_id' => $request->vendor_id,
            'title' => $request->title,
            'price' => $request->price,
            'stock_quantity' => $request->stock_quantity ?? 0,
            'details' => $request->details ?? [],
        ]);

        // 4. Build public URLs for response
        $imageUrls = array_map(function ($p) {
            return asset('storage/' . $p);
        }, $imagePaths);

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }
}
