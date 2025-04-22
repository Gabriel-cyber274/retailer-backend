<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\RetailProduct;
use Illuminate\Support\Facades\DB;

class CategoryRetailProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $retailProducts = RetailProduct::all();
        $categories = Category::all();

        if ($retailProducts->isEmpty() || $categories->isEmpty()) {
            $this->command->info('RetailProducts or Categories not found. Skipping CategoryRetailProduct seeding.');
            return;
        }

        foreach ($retailProducts as $retailProduct) {
            // Assign 1–3 random categories to each retail product
            $randomCategories = $categories->random(rand(1, 3));
            foreach ($randomCategories as $category) {
                DB::table('category_retail_product')->insert([
                    'retail_product_id' => $retailProduct->id,
                    'category_id' => $category->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
