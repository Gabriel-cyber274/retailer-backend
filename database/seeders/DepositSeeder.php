<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use App\Models\Deposit;
use App\Models\User;
use App\Models\RetailProduct;

class DepositSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $retailProducts = RetailProduct::all();
        $customers = Customer::all();

        if ($users->isEmpty() || $retailProducts->isEmpty()) {
            $this->command->info('Users or RetailProducts not found. Skipping Deposit seeding.');
            return;
        }

        // Create 20 deposits
        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $retail = $retailProducts->random();
            $quantity = rand(1, 10);
            $amount = $quantity * rand(50, 150); // random unit price for calculation

            Deposit::create([
                'user_id' => $user->id,
                'customer_id' => $customers->random()->id, // Add this line
                'retail_id' => $retail->id,
                'quantity' => $quantity,
                'amount' => $amount,
            ]);
        }
    }
}
