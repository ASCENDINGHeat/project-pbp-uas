<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ParentOrder;
use App\Models\OrderItem;
use App\Models\VendorOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Midtrans\Snap;
use Midtrans\Config;

class OrderController extends Controller
{
    // app/Http/Controllers/Api/OrderController.php

    public function checkout(Request $request)
    {
        // Ensure the user is authenticated and has payment info/address handled by a Request object.
        $user = $request->user();

        // Start a transaction to prevent partial order creation if an error occurs.
        DB::beginTransaction();

        try {
            // 1. Fetch Cart and Pre-Checks
            $cartItems = $user->cart()->with('product.vendor')->get();

            if ($cartItems->isEmpty()) {
                throw new ValidationException(['cart' => 'Your cart is empty.']);
            }

            // ** (Inventory/Stock Check & Price Validation should occur here) **
            // Check if all products are in stock and if the current cart price matches the product's database price.

            $calculatedTotal = 0;
            foreach ($cartItems as $item) {
                $calculatedTotal += $item->quantity * $item->product->price;
            }
            $vendorGroups = $cartItems->groupBy('product.vendor_id');

            $parentOrder = ParentOrder::create([
                'user_id' => $user->id,
                'total_amount' => $calculatedTotal,
                'payment_status' => '1', // Atau '1' sesuai enum migration Anda
                'snap_token' => null, // Nanti diupdate
            ]);



            $finalTotal = 0;

            // 4. Create Vendor Orders and Order Items
            foreach ($vendorGroups as $vendorId => $items) {
                $vendorOrderTotal = 0;

                // Calculate subtotal for the current vendor
                foreach ($items as $item) {
                    $vendorOrderTotal += $item->quantity * $item->product->price;
                }

                $commissionRate = 0.10; // Example: 10% commission rate
                $commissionFee = round($vendorOrderTotal * $commissionRate, 2);
                $netAmount = round($vendorOrderTotal - $commissionFee, 2);
                $finalTotal += $vendorOrderTotal;

                // Create Vendor Order (Shipment Group)
                $vendorOrder = VendorOrder::create([
                    'parent_order_id' => $parentOrder->id,
                    'vendor_id' => $vendorId,
                    'order_total' => $vendorOrderTotal,
                    'commission_fee' => $commissionFee,
                    'net_amount' => $netAmount,
                    'shipping_status' => 'pending',
                ]);

                // Create Order Items
                foreach ($items as $item) {
                    OrderItem::create([
                        'vendor_order_id' => $vendorOrder->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->product->price,
                    ]);
                    $item->product->decrement('stock_quantity', $item->quantity);
                }
            }
            $params = [
                'transaction_details' => [
                    'order_id' => $parentOrder->id, // Gunakan ID Parent Order
                    'gross_amount' => (int) $calculatedTotal, // Pastikan Integer
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    // 'phone' => $user->phone, // Tambahkan jika ada kolom phone
                ],
                // Opsional: Tambahkan item_details jika ingin rincian di email Midtrans
            ];
            $snapToken = Snap::getSnapToken($params);

            // 5. Simpan Token ke Database
            $parentOrder->update(['snap_token' => $snapToken]);
            // 5. Update Parent Order Total
            // $parentOrder->update(['total_amount' => $finalTotal]);

            // 6. Clear the Cart
            $user->cart()->delete();

            // Commit all changes to the database
            DB::commit();



            return response()->json([
                'status' => 'success',
                'message' => 'Order created, waiting for payment',
                'snap_token' => $snapToken, // INI YANG PENTING
                'order_id' => $parentOrder->id,
                'redirect_url' => "https://app.sandbox.midtrans.com/snap/v2/vtweb/" . $snapToken
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Checkout failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error message
            return response()->json(['message' => 'An unexpected error occurred during checkout.', 'error' => $e->getMessage()], 500);
        }
    }
    // app/Http/Controllers/Api/OrderController.php

    public function index(Request $request)
    {
        // Retrieve the authenticated user's parent orders, ordered by newest first.
        // Eager load vendorOrders for a complete overview.
        $orders = $request->user()->parentOrders()
            ->with('vendorOrders')
            ->latest()
            ->paginate(15);

        return response()->json($orders);
    }
    // app/Http/Controllers/Api/OrderController.php

    public function show(ParentOrder $parentOrder)
    {
        // Policy/Authorization Check: Ensure the order belongs to the authenticated user
        if ($parentOrder->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Load all relationships: vendor orders, and the items within each vendor order.
        $parentOrder->load('vendorOrders.orderItems.product');

        return response()->json($parentOrder);
    }
}
