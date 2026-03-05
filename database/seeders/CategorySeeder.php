<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::insert([
            ['name' => 'Yarn', 'slug' => 'yarn'],
            ['name' => 'Needles & Hooks', 'slug' => 'needles-hooks'],
            ['name' => 'Patterns & Books', 'slug' => 'patterns-books'],
            ['name' => 'Kits', 'slug' => 'kits'],
            ['name' => 'Accessories', 'slug' => 'accessories'],
        ]);
    }
}
