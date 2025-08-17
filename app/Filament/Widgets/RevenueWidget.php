<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Helper: calculate profit for a given order collection
        $calculateProfit = function ($orders) {
            $profit = 0;

            foreach ($orders as $order) {
                // Resell products
                foreach ($order->resells as $resell) {
                    $profit += (($resell->gain + $resell->product->cost_price) - $resell->product->price) * $resell->pivot->quantity;
                }

                // Direct products
                foreach ($order->products as $product) {
                    $profit += ($product->price - $product->cost_price) * $product->pivot->quantity;
                }
            }

            return $profit;
        };

        // Query scopes
        $allTimeOrders = Order::with(['products', 'resells'])->where('status', 'completed')->get();
        $monthlyOrders = Order::with(['products', 'resells'])
            ->where('status', 'completed')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->get();
        $weeklyOrders = Order::with(['products', 'resells'])
            ->where('status', 'completed')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->get();
        $dailyOrders = Order::with(['products', 'resells'])
            ->where('status', 'completed')
            ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->get();

        // Revenue (sum of order->amount)
        $totalRevenueAllTime = $allTimeOrders->sum('amount');
        $totalRevenueMonthly = $monthlyOrders->sum('amount');
        $totalRevenueWeekly = $weeklyOrders->sum('amount');
        $totalRevenueDaily = $dailyOrders->sum('amount');

        // Profit (calculated per product/resell)
        $totalProfitAllTime = $calculateProfit($allTimeOrders);
        $totalProfitMonthly = $calculateProfit($monthlyOrders);
        $totalProfitWeekly = $calculateProfit($weeklyOrders);
        $totalProfitDaily = $calculateProfit($dailyOrders);

        return [
            // Revenue
            Stat::make('Revenue (All Time)', number_format($totalRevenueAllTime, 2))
                ->description('Total revenue all time')
                ->color('primary'),

            Stat::make('Revenue (This Month)', number_format($totalRevenueMonthly, 2))
                ->description('Revenue this month')
                ->color('success'),

            Stat::make('Revenue (This Week)', number_format($totalRevenueWeekly, 2))
                ->description('Revenue this week')
                ->color('warning'),

            Stat::make('Revenue (Today)', number_format($totalRevenueDaily, 2))
                ->description('Revenue today')
                ->color('danger'),

            // Profit
            Stat::make('Profit (All Time)', number_format($totalProfitAllTime, 2))
                ->description('Total profit all time')
                ->color('primary'),

            Stat::make('Profit (This Month)', number_format($totalProfitMonthly, 2))
                ->description('Profit this month')
                ->color('success'),

            Stat::make('Profit (This Week)', number_format($totalProfitWeekly, 2))
                ->description('Profit this week')
                ->color('warning'),

            Stat::make('Profit (Today)', number_format($totalProfitDaily, 2))
                ->description('Profit today')
                ->color('danger'),
        ];
    }
}
