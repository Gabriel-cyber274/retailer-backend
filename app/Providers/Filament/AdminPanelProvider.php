<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Widgets\DashboardSwitcherWidget;
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\OrderProcessStats;
use App\Filament\Widgets\OrdersTable;
use App\Filament\Widgets\OrderStatsOverview;
use App\Filament\Widgets\ProductStatsOverview;
use App\Filament\Widgets\RevenueWidget;
use App\Filament\Widgets\TopResellersChart;
use App\Filament\Widgets\TopResellersTable;
use App\Filament\Widgets\UserStatsOverview;
use App\Http\Middleware\FilamentAdminAuth;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                // Pages\Dashboard::class,
                Dashboard::class
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,

                UserStatsOverview::class,
                TopResellersChart::class,
                TopResellersTable::class,
                OrderStatsOverview::class,
                OrderProcessStats::class,
                OrdersTable::class,
                ProductStatsOverview::class,
                RevenueWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                // Authenticate::class,
                FilamentAdminAuth::class,
            ]);
    }
}
