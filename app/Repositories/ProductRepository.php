<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ProductRepository implements ProductRepositoryInterface
{
    public function paginateByCategory(?string $categorySlug, int $perPage = 10): LengthAwarePaginator
    {
        $page     = request()->get('page', 1);
        $cacheKey = "products.list.{$categorySlug}.page.{$page}";

        return Cache::remember(
            $cacheKey,
            now()->addSeconds(30),
            fn () =>
            Product::query()
                ->withAvg('reviews', 'rating')
                ->when($categorySlug, fn ($q) => $q->whereHas(
                    'category',
                    fn ($q2) => $q2->where('slug', $categorySlug)
                ))
                ->paginate($perPage)
        );
    }
}
