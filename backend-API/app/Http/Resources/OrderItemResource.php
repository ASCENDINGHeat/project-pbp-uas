<?php

// app/Http/Resources/OrderItemResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'item_id' => $this->id,
            'product_id' => $this->product_id,
            // Assuming you have a Product model and a ProductResource for deeper details
            // 'product' => new ProductResource($this->whenLoaded('product')),
            'quantity' => $this->quantity,
            'unit_price' => number_format($this->unit_price, 2),
            'subtotal' => number_format($this->quantity * $this->unit_price, 2),
            'created_at' => $this->created_at,
        ];
    }
}
