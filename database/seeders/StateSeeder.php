<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = [
            ['name' => 'Abia', 'country_name' => 'Nigeria', 'dispatch_percentage' => 15.50],
            ['name' => 'Adamawa', 'country_name' => 'Nigeria', 'dispatch_percentage' => 14.75],
            ['name' => 'Akwa Ibom', 'country_name' => 'Nigeria', 'dispatch_percentage' => 13.25],
            ['name' => 'Anambra', 'country_name' => 'Nigeria', 'dispatch_percentage' => 16.00],
            ['name' => 'Bauchi', 'country_name' => 'Nigeria', 'dispatch_percentage' => 14.00],
            ['name' => 'Bayelsa', 'country_name' => 'Nigeria', 'dispatch_percentage' => 12.50],
            ['name' => 'Benue', 'country_name' => 'Nigeria', 'dispatch_percentage' => 15.25],
            ['name' => 'Borno', 'country_name' => 'Nigeria', 'dispatch_percentage' => 14.50],
            ['name' => 'Cross River', 'country_name' => 'Nigeria', 'dispatch_percentage' => 13.75],
            ['name' => 'Delta', 'country_name' => 'Nigeria', 'dispatch_percentage' => 17.00],
            ['name' => 'Ebonyi', 'country_name' => 'Nigeria', 'dispatch_percentage' => 12.25],
            ['name' => 'Edo', 'country_name' => 'Nigeria', 'dispatch_percentage' => 15.75],
            ['name' => 'Ekiti', 'country_name' => 'Nigeria', 'dispatch_percentage' => 13.50],
            ['name' => 'Enugu', 'country_name' => 'Nigeria', 'dispatch_percentage' => 14.25],
            ['name' => 'Gombe', 'country_name' => 'Nigeria', 'dispatch_percentage' => 12.75],
            ['name' => 'Imo', 'country_name' => 'Nigeria', 'dispatch_percentage' => 15.00],
            ['name' => 'Jigawa', 'country_name' => 'Nigeria', 'dispatch_percentage' => 13.00],
            ['name' => 'Kaduna', 'country_name' => 'Nigeria', 'dispatch_percentage' => 16.25],
            ['name' => 'Kano', 'country_name' => 'Nigeria', 'dispatch_percentage' => 17.50],
            ['name' => 'Katsina', 'country_name' => 'Nigeria', 'dispatch_percentage' => 14.50],
            ['name' => 'Kebbi', 'country_name' => 'Nigeria', 'dispatch_percentage' => 13.25],
            ['name' => 'Kogi', 'country_name' => 'Nigeria', 'dispatch_percentage' => 15.75],
            ['name' => 'Kwara', 'country_name' => 'Nigeria', 'dispatch_percentage' => 14.00],
            ['name' => 'Lagos', 'country_name' => 'Nigeria', 'dispatch_percentage' => 19.00],
            ['name' => 'Nasarawa', 'country_name' => 'Nigeria', 'dispatch_percentage' => 12.50],
            ['name' => 'Niger', 'country_name' => 'Nigeria', 'dispatch_percentage' => 14.75],
            ['name' => 'Ogun', 'country_name' => 'Nigeria', 'dispatch_percentage' => 16.50],
            ['name' => 'Ondo', 'country_name' => 'Nigeria', 'dispatch_percentage' => 15.00],
            ['name' => 'Osun', 'country_name' => 'Nigeria', 'dispatch_percentage' => 13.50],
            ['name' => 'Oyo', 'country_name' => 'Nigeria', 'dispatch_percentage' => 16.75],
            ['name' => 'Plateau', 'country_name' => 'Nigeria', 'dispatch_percentage' => 14.25],
            ['name' => 'Rivers', 'country_name' => 'Nigeria', 'dispatch_percentage' => 18.50],
            ['name' => 'Sokoto', 'country_name' => 'Nigeria', 'dispatch_percentage' => 13.75],
            ['name' => 'Taraba', 'country_name' => 'Nigeria', 'dispatch_percentage' => 12.25],
            ['name' => 'Yobe', 'country_name' => 'Nigeria', 'dispatch_percentage' => 13.00],
            ['name' => 'Zamfara', 'country_name' => 'Nigeria', 'dispatch_percentage' => 12.75],
            ['name' => 'Federal Capital Territory (FCT)', 'country_name' => 'Nigeria', 'dispatch_percentage' => 17.25],
        ];


        foreach ($states as $state) {
            State::create($state);
        }
    }
}
