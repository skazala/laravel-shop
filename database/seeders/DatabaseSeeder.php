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
            'email' => env('ADMIN_EMAIL'),
            'name' => 'admin',
            'password' => Hash::make(env('ADMIN_PASSWORD')),
            'is_admin' => true,
        ]);

        $this->call(CategorySeeder::class);

        $this->call(ProductSeeder::class);
    }
}
