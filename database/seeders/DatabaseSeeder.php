<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->count(20)->create();
        Product::factory()->count(20)->create();
        $this->call(PermissionSeeder::class);
    }
}
