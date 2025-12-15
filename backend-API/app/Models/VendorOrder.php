<?php

// app/Models/VendorOrder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorOrder extends Model
{
    protected $table = 'vendor_orders'; // Matches the migration filename

    protected $fillable = [
        'parent_order_id',
        'vendor_id',
        'order_total',
        'commission_fee',
        'net_amount',
        'shipping_status',
    ];

    /**
     * Get the parent order this vendor order belongs to.
     */
    public function parentOrder(): BelongsTo
    {
        return $this->belongsTo(parentOrder::class, 'parent_order_id');
    }

    /**
     * Get the vendor this order is for.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the items in this vendor order.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
