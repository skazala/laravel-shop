<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public static function cached()
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
