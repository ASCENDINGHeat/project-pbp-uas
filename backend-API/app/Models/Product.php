<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Specify the table name if it doesn't follow convention (plural 'products' is standard, but good to be explicit)
    protected $table = 'products';

    // Specify the primary key since it is 'product_id', not 'id'
    protected $primaryKey = 'id';

    // Disable timestamps since the table definition in pbpsql.sql doesn't include them
    public $timestamps = true;

    protected $fillable = [
        'vendor_id',
        'title',
        'price',
        'stock_quantity',
        'details',
        'gallery',
    ];

    // Cast JSONB column 'details' to array and price to decimal for API responses
    protected $casts = [
        'details' => 'array',
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
    ];

    public function getImageUrlAttribute()
    {
        // Check if 'details' has an 'image_path' key
        if (isset($this->details['images_path'])) {
            return asset('storage/' . $this->details['image_path']);
        }
        return null;
    }
}
