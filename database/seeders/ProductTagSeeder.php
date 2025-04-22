<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductTag;
use App\Models\Product;
use App\Models\ProductTags;

class ProductTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Make sure you have products in the database first
        $product = Product::first();

        if (!$product) {
            $this->command->info('No products found. Seeding aborted.');
            return;
        }

        ProductTags::create([
            'product_id' => $product->id,
            'name' => 'Eco-Friendly',
            'tag_code' => 'ECO123',
            'tag_image' => 'eco.png',
            'description' => 'Products that are sustainable and eco-friendly.',
        ]);

        ProductTags::create([
            'product_id' => $product->id,
            'name' => 'New Arrival',
            'tag_code' => 'NEW456',
            'tag_image' => 'new.png',
            'description' => 'Recently added products.',
        ]);
    }
}
