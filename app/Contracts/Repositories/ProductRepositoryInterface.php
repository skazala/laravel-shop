<?php

namespace App\Contracts\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function paginateByCategory(?string $categorySlug, int $perPage = 10): LengthAwarePaginator;
}
