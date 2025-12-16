<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\VendorOrder;
// use App\Http\Resources\VendorOrderResource; // Disable Resource to ensure raw data access

class VendorOrderController extends Controller
{
    /**
     * Display a listing of the vendor's orders.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ensure user is a vendor
        if (!$user->vendor) {
            return response()->json(['message' => 'User is not a registered vendor.'], 403);
        }

        // Fetch orders belonging to this vendor
        // We return the Paginator directly to ensure 'parentOrder' relationship is included in the JSON
        $orders = VendorOrder::where('vendor_id', $user->vendor->id)
            ->with(['orderItems.product', 'parentOrder.user']) // Eager load necessary data
            ->latest()
            ->paginate(15);

        return response()->json($orders);
    }

    /**
     * Display the specified order.
     */
    public function show($id)
    {
        $user = Auth::user();

        if (!$user->vendor) {
            return response()->json(['message' => 'User is not a registered vendor.'], 403);
        }

        // Find the order and ensure it belongs to the authenticated vendor
        $order = VendorOrder::where('vendor_id', $user->vendor->id)
            ->with(['orderItems.product', 'parentOrder.user'])
            ->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found or unauthorized.'], 404);
        }

        return response()->json(['data' => $order]);
    }

    /**
     * Update the shipping status of the order.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'shipping_status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $user = Auth::user();

        if (!$user->vendor) {
            return response()->json(['message' => 'User is not a registered vendor.'], 403);
        }

        $order = VendorOrder::where('vendor_id', $user->vendor->id)->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found or unauthorized.'], 404);
        }

        $order->update([
            'shipping_status' => $request->shipping_status,
        ]);

        // Return the updated order directly
        return response()->json([
            'message' => 'Order status updated successfully.',
            'data' => $order,
        ]);
    }
}