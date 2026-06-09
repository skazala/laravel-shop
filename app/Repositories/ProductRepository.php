<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateByCategory(
        ?string $categorySlug,
        int $perPage = 10,
        int $page = 1
    ): LengthAwarePaginator {
        $cacheKey = sprintf(
            'products:list:%s:page:%d:per:%d',
            $categorySlug ?? 'all',
            $page,
            $perPage
        );

        $query = Product::query()
            ->withAvg('reviews', 'rating')
            ->when($categorySlug, fn ($q) => $q->whereHas(
                'category',
                fn ($q2) => $q2->where('slug', $categorySlug)
            ));

        return Cache::remember(
            $cacheKey,
            now()->addSeconds(30),
            fn () => $query->paginate($perPage, ['*'], 'page', $page)
        );
    }
}
