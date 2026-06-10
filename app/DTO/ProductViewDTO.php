<?php

namespace App\DTO;

use App\Models\Product;

final class ProductViewDTO
{
    public function __construct(
        public Product $product,
        public int $inCart,
        public int $availableQuantity,
    ) {
    }

    public function __get(string $key): mixed
    {
        return $this->product->{$key};
    }
}
