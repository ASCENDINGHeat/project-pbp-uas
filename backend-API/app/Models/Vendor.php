<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vendor extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'store_name',
        'store_description',
        'store_logo',
        'commission_rate',
        'balance'
    ];

    public function getStoreLogoUrlAttribute()
    {
        if ($this->store_logo) {
            return asset('storage/' . $this->store_logo);
        }
        return null; // or return a default placeholder URL
    }

    public function products(){
        return $this->hasMany(Product::class, 'vendor_id', 'id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
