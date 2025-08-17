<?php

namespace App\Filament\Admin\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ResellsRelationManager extends RelationManager
{
    protected static string $relationship = 'resells'; // matches Order::resells()

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('retail_id')
                    ->relationship('resells', 'id')
                    ->required()
                    ->label('Retail Product')
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Reseller')
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.price')
                    ->label('Base Price')
                    ->money('ngn')
                    ->sortable(),

                Tables\Columns\TextColumn::make('gain')
                    ->label('Gain')
                    ->money('ngn')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pivot.quantity')
                    ->label('Qty')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->state(
                        fn($record) => $record->product?->price && $record->pivot?->quantity
                            ? ($record->product->price + $record->gain) * $record->pivot->quantity
                            : null
                    )
                    ->money('ngn'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Attach Resell Product')
                    ->preloadRecordSelect()
                    ->form(fn(Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->form([
                    Forms\Components\TextInput::make('quantity')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                ]),
                Tables\Actions\DetachAction::make(),
            ])
            ->defaultSort('product.name');
    }
}
