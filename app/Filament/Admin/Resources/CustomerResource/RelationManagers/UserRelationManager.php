<?php

namespace App\Filament\Admin\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserRelationManager extends RelationManager
{
    protected static string $relationship = 'User';

    public function form(Form $form): Form
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user')
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
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
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
