<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // All time revenue
        $totalAmountAllTime = Order::where('status', 'completed')->sum('amount');

        // This month's revenue
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        $totalAmountMonthly = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // This week's revenue
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();
        $totalAmountWeekly = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->sum('amount');

        // Today's revenue
        $startOfDay = now()->startOfDay();
        $endOfDay = now()->endOfDay();
        $totalAmountDaily = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('amount');

        return [
            Stat::make('All Time', $totalAmountAllTime)
                ->description('Total amount made all time')
                ->color('primary'),

            Stat::make('This Month', $totalAmountMonthly)
                ->description('Amount made this month')
                ->color('success'),

            Stat::make('This Week', $totalAmountWeekly)
                ->description('Amount made this week')
                ->color('warning'),

            Stat::make('Today', $totalAmountDaily)
                ->description('Amount made today')
                ->color('danger'),
        ];
    }
}
