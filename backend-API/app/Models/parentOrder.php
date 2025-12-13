<?php

// app/Models/ParentOrder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParentOrder extends Model
{
    protected $fillable = [
        'user_id',
        'total_amount',
        'payment_status',
    ];

    /**
     * Get the user that placed the parent order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vendor orders associated with the parent order.
     */
    public function vendorOrders(): HasMany
    {
        return $this->hasMany(VendorOrder::class, 'parent_order_id');
    }
}
