<?php

namespace Tests\Unit\Repositories;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new ProductRepository();
    }

    public function test_returns_paginated_products(): void
    {
        Product::factory()->count(15)->create();

        $result = $this->repo->paginateByCategory(null, 10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(15, $result->total());
    }

    public function test_filters_by_category_slug(): void
    {
        $category = Category::factory()->create(['slug' => 'yarn']);
        $otherCategory = Category::factory()->create(['slug' => 'needles']);
        Product::factory()->count(3)->create(['category_id' => $category->id]);
        Product::factory()->count(5)->create(['category_id' => $otherCategory->id]);

        $result = $this->repo->paginateByCategory('yarn', 10);

        $this->assertCount(3, $result->items());
    }

    public function test_returns_all_products_when_category_is_null(): void
    {
        Product::factory()->count(5)->create();

        $result = $this->repo->paginateByCategory(null, 10);

        $this->assertEquals(5, $result->total());
    }
}
