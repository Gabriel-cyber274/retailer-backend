<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopResellersChart extends ChartWidget
{
    protected static ?string $heading = 'Top 5 Resellers by Deposit Amount';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $users = User::withSum('deposits', 'amount')
            ->withCount('deposits')
            ->with(['deposits' => function ($query) {
                $query->select('user_id', DB::raw('MAX(amount) as highest_deposit'))
                    ->groupBy('user_id');
            }])
            ->has('deposits') // Only users with deposits
            ->orderBy('deposits_sum_amount', 'desc')
            ->take(5)
            ->get();

        $labels = $users->map(fn($user) => $user->name);
        $totalAmounts = $users->map(fn($user) => $user->deposits_sum_amount);
        $highestDeposits = $users->map(fn($user) => $user->deposits->first()->highest_deposit ?? 0);
        $depositCounts = $users->map(fn($user) => $user->deposits_count);

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Deposits ($)',
                    'data' => $totalAmounts,
                    'backgroundColor' => '#4f46e5', // Indigo
                    'borderColor' => '#4f46e5',
                    'type' => 'bar',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Highest Single Deposit ($)',
                    'data' => $highestDeposits,
                    'backgroundColor' => '#10b981', // Emerald
                    'borderColor' => '#10b981',
                    'type' => 'bar',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Number of Deposits',
                    'data' => $depositCounts,
                    'borderColor' => '#f59e0b', // Amber
                    'backgroundColor' => 'transparent',
                    'type' => 'line',
                    'yAxisID' => 'y1',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Mixed chart (bar + line)
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Amount ($)',
                    ],
                    'beginAtZero' => true,
                ],
                'y1' => [
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Number of Deposits',
                    ],
                    'beginAtZero' => true,
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => function ($context) {
                            $label = $context->dataset->label ?? '';
                            $value = $context->parsed->y;

                            if (str_contains($label, '$')) {
                                return $label . ': ' . '$' . number_format($value, 2);
                            }
                            return $label . ': ' . $value;
                        }
                    ]
                ],
                'legend' => [
                    'position' => 'top',
                ],
            ],
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
