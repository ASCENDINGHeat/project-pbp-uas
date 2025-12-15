<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture',
        'address',
        'phone_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_vendor', // <--- ADD THIS
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    //accessor for vendor logo URL
    public function getProfilePictureUrlAttribute()
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }
        return null; // or return a default placeholder URL
    }

    /**
     * Determine if the user is a vendor.
     *
     * @return bool
     */
    public function getIsVendorAttribute() // <--- ADD THIS FUNCTION
    {
        // Checks if the vendor relationship returns a model or null
        return $this->vendor !== null;
    }

    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }

    public function parentOrders(){
        return $this->hasMany(ParentOrder::class, 'user_id', 'id');
    }

    public function cartItems(){
        return $this->hasMany(CartItem::class, 'user_id', 'id');
    }

    public function Cart()
    {
        return $this->hasMany(Cart::class);
    }
}
