<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 50; $i++) {
            Product::create([
                'name' => $faker->unique()->sentence(3),
                'product_image' => $faker->imageUrl(640, 480, 'product', true),
                'description' => $faker->paragraph(3),
                'price' => $faker->randomFloat(2, 10, 500),
                'default_tag' => $faker->optional()->numberBetween(1, 10),
                'product_code' => $faker->unique()->ean13(),
                'suggested_profit' => $faker->optional()->randomFloat(2, 5, 50),
                'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                'updated_at' => $faker->dateTimeBetween('-1 year', 'now'),
            ]);
        }

        // You can also create specific products if needed
        Product::create([
            'name' => 'Awesome Gadget',
            'product_image' => 'https://via.placeholder.com/640x480?text=Awesome+Gadget',
            'description' => 'A truly awesome gadget that will change your life.',
            'price' => 199.99,
            'default_tag' => 1,
            'product_code' => 'AWESOME-001',
            'suggested_profit' => 50.00,
        ]);

        Product::create([
            'name' => 'Basic Utility Item',
            'product_image' => 'https://via.placeholder.com/640x480?text=Basic+Utility',
            'description' => 'A basic item that serves its purpose well.',
            'price' => 29.50,
            'product_code' => 'BASIC-123',
        ]);
    }
}
