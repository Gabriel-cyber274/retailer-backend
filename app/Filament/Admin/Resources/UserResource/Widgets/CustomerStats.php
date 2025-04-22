<?php

namespace App\Filament\Admin\Resources\UserResource\Widgets;

use App\Models\User; // Import the User model
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerStats extends BaseWidget
{
    public ?int $recordId = null; // Public property to receive the record ID

    protected function getStats(): array
    {
        $user = User::findOrFail($this->recordId); // Fetch the User record
        $customers = $user->customers()->withCount('deposits')->get(); // Eager load the deposit counts

        $averageDeposits = $customers->avg('deposits_count') ?? 0;

        return [
            Stat::make('Total Customers', $user->customers()->count()),
            Stat::make('Customers With Deposits', $customers->where('deposits_count', '>', 0)->count()),
            Stat::make(
                'Avg Deposits per Customer',
                number_format($averageDeposits, 2)
            ),
        ];
    }
}
