<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class OrdersTable extends BaseWidget
{
    protected static ?string $heading = 'Recent Orders';
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::with(['user', 'products', 'resells'])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order ID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('products_summary')
                    ->label('Products')
                    ->state(
                        fn(Order $record) =>
                        $record->products->pluck('name')->take(3)->join(', ')
                            . ($record->products->count() > 3 ? '…' : '')
                    )
                    ->tooltip(
                        fn(Order $record) =>
                        $record->products->pluck('name')->join(', ')
                    ),

                Tables\Columns\TextColumn::make('resells_summary')
                    ->label('Resells')
                    ->state(
                        fn(Order $record) =>
                        $record->resells->map(
                            fn($resell) =>
                            $resell->product?->name . ' (x' . $resell->pivot->quantity . ')'
                        )->take(3)->join(', ')
                            . ($record->resells->count() > 3 ? '…' : '')
                    )
                    ->tooltip(
                        fn(Order $record) =>
                        $record->resells->map(
                            fn($resell) =>
                            $resell->product?->name . ' (x' . $resell->pivot->quantity . ')'
                        )->join(', ')
                    ),

                Tables\Columns\TextColumn::make('amount')
                    ->money('ngn')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn(Order $record): string => url("/admin/orders/{$record->id}"))
                    ->openUrlInNewTab(false),
            ])
            ->paginated(10);
    }

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
