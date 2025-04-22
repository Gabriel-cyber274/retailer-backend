<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RetailProduct;
use App\Models\Product;
use App\Models\User;

class RetailProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();
        $users = User::all();

        // Check if there are users and products
        if ($products->isEmpty() || $users->isEmpty()) {
            $this->command->info('Products or Users not found. Skipping RetailProduct seeding.');
            return;
        }

        // Assign random users to products with random gain values
        foreach ($products as $product) {
            RetailProduct::create([
                'product_id' => $product->id,
                'user_id' => $users->random()->id,
                'gain' => rand(5, 50) . '%',
            ]);
        }
    }
}
