<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $now = Carbon::now();

        // All time users
        $totalUsers = User::count();

        // This month's users
        $monthStart = $now->startOfMonth();
        $monthlyUsers = User::where('created_at', '>=', $monthStart)->count();
        $monthlyGrowth = $this->calculateGrowthRate(
            User::whereBetween('created_at', [$now->copy()->subMonths(2)->startOfMonth(), $monthStart])->count(),
            $monthlyUsers
        );

        // This week's users
        $weekStart = $now->startOfWeek();
        $weeklyUsers = User::where('created_at', '>=', $weekStart)->count();
        $weeklyGrowth = $this->calculateGrowthRate(
            User::whereBetween('created_at', [$now->copy()->subWeeks(2)->startOfWeek(), $weekStart])->count(),
            $weeklyUsers
        );

        // Today's users
        $todayStart = $now->startOfDay();
        $dailyUsers = User::where('created_at', '>=', $todayStart)->count();
        $dailyGrowth = $this->calculateGrowthRate(
            User::whereBetween('created_at', [$now->copy()->subDays(2)->startOfDay(), $todayStart])->count(),
            $dailyUsers
        );

        return [
            Stat::make('Total Users', $totalUsers)
                ->description('All registered users')
                ->descriptionIcon('heroicon-m-user-group')
                ->chart($this->getUserGrowthChartData(12)) // 12 months data
                ->color('primary'),

            Stat::make('This Month', $monthlyUsers)
                ->description($monthlyGrowth . '% vs last month')
                ->descriptionIcon($monthlyGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->descriptionColor($monthlyGrowth >= 0 ? 'success' : 'danger')
                ->chart($this->getUserGrowthChartData(1, true)) // 1 month data
                ->color('info'),

            Stat::make('This Week', $weeklyUsers)
                ->description($weeklyGrowth . '% vs last week')
                ->descriptionIcon($weeklyGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->descriptionColor($weeklyGrowth >= 0 ? 'success' : 'danger')
                ->color('warning'),

            Stat::make('Today', $dailyUsers)
                ->description($dailyGrowth . '% vs yesterday')
                ->descriptionIcon($dailyGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->descriptionColor($dailyGrowth >= 0 ? 'success' : 'danger')
                ->color('success'),
        ];
    }

    protected function calculateGrowthRate(int $previousPeriodCount, int $currentPeriodCount): float
    {
        if ($previousPeriodCount === 0) {
            return $currentPeriodCount > 0 ? 100.0 : 0.0;
        }

        return round(($currentPeriodCount - $previousPeriodCount) / $previousPeriodCount * 100, 1);
    }

    protected function getUserGrowthChartData(int $months = 12, bool $byDay = false): array
    {
        $data = [];
        $now = Carbon::now();

        if ($byDay) {
            // Get daily data for the current month
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();

            while ($start <= $end) {
                $count = User::whereDate('created_at', $start->format('Y-m-d'))->count();
                $data[] = $count;
                $start->addDay();
            }
        } else {
            // Get monthly data
            for ($i = $months - 1; $i >= 0; $i--) {
                $month = $now->copy()->subMonths($i);
                $count = User::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();
                $data[] = $count;
            }
        }

        return $data;
    }
}
