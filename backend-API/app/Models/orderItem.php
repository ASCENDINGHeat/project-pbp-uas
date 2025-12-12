<?php

// app/Models/OrderItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'vendor_order_id',
        'product_id',
        'quantity',
        'unit_price',
    ];

    /**
     * Get the vendor order this item belongs to.
     */
    public function vendorOrder(): BelongsTo
    {
        return $this->belongsTo(vendorOrder::class);
    }

    /**
     * Get the product associated with this item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
