<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'email' => config('admin.email'),
            'name' => 'admin',
            'password' => Hash::make(config('admin.password')),
            'is_admin' => true,
        ]);

        $this->call(CategorySeeder::class);

        $this->call(ProductSeeder::class);
    }
}
