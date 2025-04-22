<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CategoryResource\Pages;
use App\Filament\Admin\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $modelLabel = 'Category';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Category Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),

                    ])->columns(1),

                Forms\Components\Section::make('Relationships')
                    ->schema([
                        Forms\Components\Select::make('products')
                            ->relationship('products', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('retailProducts')
                            ->relationship(
                                name: 'retailProducts',
                                titleAttribute: 'id',
                                modifyQueryUsing: fn(Builder $query) => $query->with('product')
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn(\App\Models\RetailProduct $record) =>
                                $record->product->name ?? 'Unknown Product'
                            )
                            ->searchable()
                            ->getSearchResultsUsing(
                                fn(string $search) =>
                                \App\Models\RetailProduct::whereHas(
                                    'product',
                                    fn($query) =>
                                    $query->where('name', 'like', "%{$search}%")
                                )
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn($retailProduct) => [
                                        $retailProduct->id => $retailProduct->product->name
                                    ])
                            )
                            ->multiple()
                            ->preload(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Products')
                    ->counts('products')
                    ->sortable(),

                Tables\Columns\TextColumn::make('retailProducts_count')
                    ->label('Retail Products')
                    ->getStateUsing(function ($record) {
                        return $record->retailProducts()->count();
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_products')
                    ->label('Has Products')
                    ->query(fn(Builder $query): Builder => $query->has('products')),

                Tables\Filters\Filter::make('has_retail_products')
                    ->label('Has Retail Products')
                    ->query(fn(Builder $query): Builder => $query->has('retailProducts')),
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
            // RelationManagers\ProductsRelationManager::class,
            // RelationManagers\RetailProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'view' => Pages\ViewCategory::route('/{record}'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
