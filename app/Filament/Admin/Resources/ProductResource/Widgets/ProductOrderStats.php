<?php

namespace App\Filament\Admin\Resources\ProductResource\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductOrderStats extends BaseWidget
{
    public ?int $recordId = null;

    protected function getStats(): array
    {
        $product = Product::withCount('orders')->findOrFail($this->recordId);

        $completedOrders = $product->orders()->where('status', 'completed')->count();
        $totalRevenue = $product->orders()->sum('amount');
        $avgOrderValue = $product->orders()->avg('amount') ?? 0;

        return [
            Stat::make('Total Orders', $product->orders_count),
            Stat::make('Completed Orders', $completedOrders),
            Stat::make('Total Revenue', '$' . number_format($totalRevenue, 2)),
            Stat::make('Avg Order Value', '$' . number_format($avgOrderValue, 2)),
        ];
    }
}
