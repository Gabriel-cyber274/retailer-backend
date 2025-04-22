<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderProcessStats extends BaseWidget
{
    protected function getStats(): array
    {
        $pending = Order::where('status', 'pending')->count();
        $completed = Order::where('status', 'completed')->count();
        $cancelled = Order::where('status', 'cancelled')->count();
        $total = $pending + $completed + $cancelled;

        return [
            Stat::make('Total Orders', $total)
                ->description('All orders')
                ->descriptionIcon('heroicon-o-shopping-bag')
                ->chart($this->getOrderTrendData())
                ->color('gray'),

            Stat::make('Pending Orders', $pending)
                ->description('Awaiting processing')
                ->descriptionIcon('heroicon-o-clock')
                ->descriptionColor('warning')
                ->color('warning'),

            Stat::make('Completed Orders', $completed)
                ->description('Successfully fulfilled')
                ->descriptionIcon('heroicon-o-check-circle')
                ->descriptionColor('success')
                ->color('success'),

            Stat::make('Cancelled Orders', $cancelled)
                ->description('Cancelled or rejected')
                ->descriptionIcon('heroicon-o-x-circle')
                ->descriptionColor('danger')
                ->color('danger'),
        ];
    }

    protected function getOrderTrendData(): array
    {
        // Get order counts for last 7 days
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = Order::whereDate('created_at', $date)->count();
        }
        return $data;
    }

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
