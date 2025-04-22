<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Category;

class CategoryProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();
        $categories = Category::all();

        // If either table is empty, abort
        if ($products->isEmpty() || $categories->isEmpty()) {
            $this->command->info('Products or Categories not found. Skipping category_product seeding.');
            return;
        }

        // Assign each product to 1–3 random categories
        foreach ($products as $product) {
            $product->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->toArray()
            );
        }
    }
}
