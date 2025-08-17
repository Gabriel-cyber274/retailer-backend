<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;

class TopResellersTable extends TableWidget
{
    protected static ?string $heading = 'Top Resellers Details';
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::withSum('deposits', 'amount')
                    ->withCount('deposits')
                    ->with(['deposits' => function ($query) {
                        $query->select('user_id', DB::raw('MAX(amount) as highest_deposit'))
                            ->groupBy('user_id');
                    }])
                    ->has('deposits')
                    ->orderBy('deposits_sum_amount', 'desc')
                    ->take(5)
            )
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('deposits_sum_amount')
                    ->label('Total Deposits')
                    ->money('ngn')
                    ->sortable(),

                TextColumn::make('deposits_count')
                    ->label('Deposit Count')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('deposits.highest_deposit')
                    ->label('Highest Deposit')
                    ->money('ngn')
                    ->formatStateUsing(fn($state) => $state ?? 0),
                TextColumn::make('email')
                    ->searchable(),
            ])
            ->defaultSort('deposits_sum_amount', 'desc')
            ->paginated(false)
            ->deferLoading();
    }

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
