<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\OrderProcessStats;
use App\Filament\Widgets\OrdersTable;
use App\Filament\Widgets\OrderStatsOverview;
use App\Filament\Widgets\ProductStatsOverview;
use App\Filament\Widgets\RevenueWidget;
use App\Filament\Widgets\TopResellersChart;
use App\Filament\Widgets\TopResellersTable;
use App\Filament\Widgets\UserStatsOverview;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.dashboard';
    protected static ?int $navigationSort = -2;
    protected static ?string $title = 'Dashboard';
    protected static ?string $slug = 'dashboard';

    public string $activeTab = 'users';

    public function mount(): void
    {
        $this->activeTab = request()->query('tab', 'users');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function getWidgets(): array
    {
        return [
            'users' => [
                UserStatsOverview::class,
                TopResellersTable::class,
                TopResellersChart::class,
            ],
            'orders' => [
                OrderStatsOverview::class,
                OrderProcessStats::class,
                OrdersTable::class,
            ],
            'products' => [
                ProductStatsOverview::class,
            ],
            'revenue' => [
                RevenueWidget::class, // Add your new widget here
            ],
        ];
    }

    public function getVisibleWidgets(): array
    {
        return $this->getWidgets()[$this->activeTab] ?? [];
    }
}
