<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    public function paginateByCategory(?string $categorySlug, int $perPage = 10): LengthAwarePaginator
    {
        return Product::query()
            ->when(
                $categorySlug,
                fn ($q) => $q->whereHas(
                    'category',
                    fn ($q2) => $q2->where('slug', $categorySlug)
                )
            )
            ->paginate($perPage);
    }
}
