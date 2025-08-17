<?php

namespace App\Filament\Admin\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'Orders';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->label('Customer')
                    ->nullable(),

                Forms\Components\Select::make('products')
                    ->multiple()
                    ->relationship('products', 'name')
                    ->label('Products'),

                Forms\Components\Select::make('resells')
                    ->multiple()
                    ->relationship('resells', 'id')
                    ->label('Resell Products'),

                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->label('Amount'),

                Forms\Components\TextInput::make('address')
                    ->required()
                    ->label('Address'),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'canceled' => 'Canceled',
                    ])
                    ->label('Status')
                    ->required(),

                Forms\Components\Select::make('type')
                    ->options([
                        'sale' => 'Sale',
                        'refund' => 'Refund',
                    ])
                    ->label('Order Type')
                    ->required(),

                Forms\Components\TextInput::make('reference')
                    ->label('Reference')
                    ->nullable(),

                Forms\Components\TextInput::make('dispatch_number')
                    ->label('Dispatch Number')
                    ->nullable(),

                Forms\Components\TextInput::make('dispatch_fee')
                    ->numeric()
                    ->label('Dispatch Fee')
                    ->nullable(),

                Forms\Components\TextInput::make('original_price')
                    ->numeric()
                    ->label('Original Price')
                    ->nullable(),

                Forms\Components\Textarea::make('note')
                    ->label('Note')
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Order')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('User'),
                Tables\Columns\TextColumn::make('customer.name')->label('Customer')->sortable(),
                Tables\Columns\TextColumn::make('amount')->label('Amount')->money('ngn')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('type')->label('Order Type'),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime()->sortable(),
            ])
            ->filters([
                // Filter by status
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'canceled' => 'Canceled',
                    ]),

                // Filter by type
                Tables\Filters\SelectFilter::make('type')
                    ->label('Order Type')
                    ->options([
                        'customer_purchase' => 'customer purchase',
                        'direct_purchase' => 'direct purchase',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
