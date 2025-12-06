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
        'vendors_id',
        'title',
        'price',
        'stock_quantity',
        'details',
    ];

    // Cast JSONB column 'details' to array and price to decimal for API responses
    protected $casts = [
        'details' => 'array',
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
    ];

    protected $appends = ['image_url']; // Append these to the JSON output automatically

    // Accessor for Main Image URL
    public function getImageUrlAttribute()
    {
        // Check if details is an array and has the key path under 'images.main'
        if (is_array($this->details) && isset($this->details['images']['main']) && $this->details['images']['main']) {
            return asset('storage/' . $this->details['images']['main']);
        }
        return null; // Return default placeholder URL here if needed
    }

    // Accessor for Gallery URLs
    public function getGalleryUrlsAttribute()
    {
        $urls = [];
        if (is_array($this->details) && isset($this->details['gallery_paths'])) {
            foreach ($this->details['gallery_paths'] as $path) {
                $urls[] = asset('storage/' . $path);
            }
        }
        return $urls;
    }
}
