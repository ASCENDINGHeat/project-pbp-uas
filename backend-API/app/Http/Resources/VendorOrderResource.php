<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // 1. Map 'id' so the frontend can read order.id
            'id' => $this->id,
            
            'vendor_order_id' => $this->id,
            'vendor_id' => $this->vendor_id,

            // 2. Map 'total_price' so the frontend can read order.total_price
            // We use the raw value (float/int) so the frontend's formatCurrency function works
            'total_price' => $this->order_total,

            // 3. Map 'status' so the frontend can read order.status and calculate stats
            'status' => $this->shipping_status,
            'shipping_status' => $this->shipping_status,

            // 4. Include User data so the frontend can read order.user.name
            // We access the parentOrder's user relationship loaded in the controller
            'user' => [
                'name' => $this->parentOrder->user->name ?? 'Customer',
                'email' => $this->parentOrder->user->email ?? '',
            ],

            // Existing structure (optional to keep)
            'totals' => [
                'order_total' => number_format($this->order_total, 2),
                'commission_fee' => number_format($this->commission_fee, 2),
                'net_amount' => number_format($this->net_amount, 2),
            ],
            'items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}