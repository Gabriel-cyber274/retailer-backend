<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Filament\Admin\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ])
                            ->nullable(),
                        Forms\Components\Textarea::make('address')
                            ->nullable()
                            ->maxLength(65535),
                        Forms\Components\TextInput::make('city')
                            ->nullable()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('state')
                            ->nullable()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Account Details')
                    ->schema([
                        Forms\Components\TextInput::make('acc_balance')
                            ->label('Account Balance')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\TextInput::make('verification_code')
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\Checkbox::make('admin')
                            ->label('Is Admin'),
                    ])->columns(2),

                Forms\Components\Section::make('Shop Information')
                    ->schema([
                        Forms\Components\TextInput::make('shop_name')
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\TextInput::make('shop_id')
                            ->maxLength(255)
                            ->nullable(),
                    ])->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('state')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('acc_balance')
                    ->label('Balance')
                    ->sortable(),
                Tables\Columns\IconColumn::make('admin')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shop_id')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shop_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('admin')
                    ->label('Admin Users'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RetailsRelationManager::class,
            RelationManagers\CustomersRelationManager::class,
            RelationManagers\DepositsRelationManager::class,
            RelationManagers\OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
