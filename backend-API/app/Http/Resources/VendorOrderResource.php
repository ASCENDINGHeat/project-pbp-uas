<?php

// app/Http/Resources/VendorOrderResource.php

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
            'vendor_order_id' => $this->id,
            'vendor_id' => $this->vendor_id,
            // 'vendor_name' => $this->whenLoaded('vendor', $this->vendor->name), // Include vendor details
            'totals' => [
                'order_total' => number_format($this->order_total, 2),
                'commission_fee' => number_format($this->commission_fee, 2),
                'net_amount' => number_format($this->net_amount, 2),
            ],
            'shipping_status' => $this->shipping_status,
            'items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
