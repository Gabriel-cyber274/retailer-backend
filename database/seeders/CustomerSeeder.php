<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\User;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('No users found. Skipping Customer seeding.');
            return;
        }

        foreach ($users as $user) {
            Customer::create([
                'user_id' => $user->id,
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone_no' => fake()->phoneNumber(),
                'address' => fake()->address(),
            ]);
        }
    }
}
