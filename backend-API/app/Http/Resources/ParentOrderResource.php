<?php

// app/Http/Resources/ParentOrderResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParentOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'parent_order_id' => $this->id,
            'user_id' => $this->user_id,
            'total_amount' => number_format($this->total_amount, 2),
            'payment_status' => $this->payment_status,
            'created_at' => $this->created_at->toDateTimeString(),

            // Nested Vendor Orders (using the collection method)
            'vendor_shipments' => VendorOrderResource::collection($this->whenLoaded('vendorOrders')),
            'shipping_info' => [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'address' => $this->user->address,
                'phone' => $this->user->phone,
            ],
            // You could include shipping address information here
            // 'shipping_address' => '...',
        ];
    }
}
