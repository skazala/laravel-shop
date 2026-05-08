<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'stock_quantity'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function averageRating(): ?float
    {
        $avg = $this->reviews()->avg('rating');
        return $avg ? round($avg, 1) : null;
    }

    public function wasPurchasedBy(int $userId): bool
    {
        return OrderItem::query()
            ->where('product_id', $this->id)
            ->whereHas(
                'order',
                fn ($q) => $q
                ->where('user_id', $userId)
                ->whereIn('status', ['paid', 'shipped', 'delivered'])
            )
            ->exists();
    }
}
