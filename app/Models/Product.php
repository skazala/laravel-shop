<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @use HasFactory<\Database\Factories\ProductFactory>
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'stock_quantity'];

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
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

    /**
     * @return HasMany<Wishlist, $this>
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }
}
