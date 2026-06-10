<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return Collection<int, Category>
     */
    public static function cached(): Collection
    {
        return Cache::remember('shop_categories', 3600, function () {
            return self::orderBy('name')->get();
        });
    }

    protected static function booted()
    {
        static::saved(fn () => Cache::forget('shop_categories'));
        static::deleted(fn () => Cache::forget('shop_categories'));
    }
}
