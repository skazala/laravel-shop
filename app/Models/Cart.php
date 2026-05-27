<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id'];

    protected $with = ['items'];

    /**
     * @return HasMany<CartItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
