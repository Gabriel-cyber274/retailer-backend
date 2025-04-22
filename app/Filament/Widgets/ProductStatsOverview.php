<?php

namespace App\Filament\Widgets;

use App\Models\Product; // Import the Product model
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $now = now();

        return [
            Stat::make('Total Products', Product::count())
                ->description('All time products')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('In Stock', Product::where('in_stock', true)->count())
                ->description('Currently in stock')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Out of Stock', Product::where('in_stock', false)->count())
                ->description('Currently out of stock')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),
            Stat::make('This Month', Product::whereMonth('created_at', $now->month)->count())
                ->description('Products created this month')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('warning'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
