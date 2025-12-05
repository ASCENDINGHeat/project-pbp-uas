<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        // 1. Get Authenticated User & Check Vendor Status
        $user = auth()->user();

        // Load the vendor relationship if not already loaded
        if (!$user->vendor) {
            return response()->json(['message' => 'You must be a registered vendor to create products.'], 403);
        }

        // 2. Validate Request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'integer|min:0',

            // Validate the Flexible "Bucket"
            'details' => 'nullable|array',

            // Validate explicit fields that will go into the bucket
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',

            // Validate Images (Changed 'gallery' to 'images' to match your loop)
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // 3. Initialize Details Array (THE MERGE STRATEGY)
        // First, grab any flexible data the user sent (like color, size)
        $details = $request->input('details', []);

        // Next, merge the explicit fields into the array
        if (isset($validated['description'])) {
            $details['description'] = $validated['description'];
        }
        if (isset($validated['category'])) {
            $details['category'] = $validated['category'];
        }

        // Initialize the images array if it doesn't exist
        if (!isset($details['images_paths'])) {
            $details['images_paths'] = [];
        }

        // 5. Handle Gallery Uploads
        // Fixed: Matching the validation key 'images'
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                // Fixed: specific path structure
                $path = $file->store('products/images', 'public');
                // Fixed: appending to the correct plural key 'images_paths'
                $details['images_paths'][] = $path;
            }
        }

        // 6. Create Product
        $product = Product::create([
            'vendor_id' => $user->vendor->id,
            'title' => $validated['title'],
            'price' => $validated['price'],
            'stock_quantity' => $validated['stock_quantity'] ?? 0,
            'details' => $details, // Now contains: flexible data + description + images
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!Auth::user()->vendor || $product->vendor_id !== Auth::user()->vendor->id){
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title'          => 'sometimes|string|max:255',
            'price'          => 'sometimes|numeric|min:0',
            'stock_quantity' => 'sometimes|integer|min:0',

            // Flexible Data
            'details'        => 'sometimes|array',
            'description'    => 'sometimes|string',
            'category'       => 'sometimes|string',

            // Images (Optional updates)
            'image'          => 'nullable|image|max:2048',     // Replace main image
            'images'         => 'nullable|array',             // Append to gallery
            'images.*'       => 'image|max:2048',

            // precise logic to remove specific gallery images
            'remove_gallery_images' => 'nullable|array',
            'remove_gallery_images.*' => 'string'
        ]);

        // 4. Update Standard Columns
        // Only update fields that are actually present in the request
        $product->fill($request->only(['title', 'price', 'stock_quantity']));

        // 5. Handle "Details" Merge Logic
        // Start with existing details from DB
        $currentDetails = $product->details ?? [];

        // Get new flexible details from request
        $newDetails = $request->input('details', []);

        // Merge: New input overwrites old keys, but keeps keys not mentioned
        $updatedDetails = array_merge($currentDetails, $newDetails);

        // Handle specific explicit fields merging into details
        if ($request->has('description')) {
            $updatedDetails['description'] = $request->input('description');
        }
        if ($request->has('category')) {
            $updatedDetails['category'] = $request->input('category');
        }

        // 6. Handle Main Image Replacement
        if ($request->hasFile('image')) {
            // Optional: Delete old image to save space
            if (!empty($currentDetails['images_path'])) {
                Storage::disk('public')->delete($currentDetails['images_path']);
            }
            // Store new image
            $updatedDetails['images_path'] = $request->file('image')->store('products/main', 'public');
        }

        // 7. Handle Gallery Updates
        // A. Remove selected images
        if ($request->has('remove_gallery_images')) {
            $pathsToRemove = $request->input('remove_gallery_images');
            $currentGallery = $updatedDetails['images_paths'] ?? [];

            // Filter out the paths user wants to remove
            $updatedDetails['images_paths'] = array_values(array_diff($currentGallery, $pathsToRemove));

            // Physically delete files
            Storage::disk('public')->delete($pathsToRemove);
        }

        // B. Append new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $updatedDetails['images_paths'][] = $file->store('products/gallery', 'public');
            }
        }

        // 8. Save Changes
        $product->details = $updatedDetails;
        $product->save();

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product
        ], 200);
    }
}
