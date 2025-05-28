<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Filament\Admin\Resources\ProductResource\RelationManagers\OrdersRelationManager;
use App\Models\Product;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $modelLabel = 'Product';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\FileUpload::make('product_image')
                            ->image()
                            ->required()
                            ->directory('products')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('product_code', 'PRD-' . strtoupper(Str::random(6)));
                            }),

                        Forms\Components\TextInput::make('product_code')
                            // ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->readOnly(),

                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('brand_name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('video_url')
                            ->url()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Pricing & Inventory')
                    ->schema([
                        Forms\Components\TextInput::make('cost_price')
                            ->numeric()
                            ->required()
                            ->prefix('₦'),

                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->required()
                            ->prefix('₦')
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                if ($state && is_numeric($state)) {
                                    $suggested = $state * 1.10;
                                    $set('suggested_profit', round($suggested, 2));
                                }
                            }),

                        Forms\Components\TextInput::make('suggested_profit')
                            ->numeric()
                            ->prefix('₦')
                            ->readOnly()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('quantity')
                            ->numeric(),

                        Forms\Components\Toggle::make('in_stock')
                            ->required()
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Tags')
                    ->schema([
                        Forms\Components\Repeater::make('tags')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Forms\Set $set) {
                                        $set('tag_code', 'PRDTAG-' . strtoupper(Str::random(6)));
                                    }),
                                Forms\Components\TextInput::make('tag_code')
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->readOnly(),
                                Forms\Components\Textarea::make('description'),
                                Forms\Components\FileUpload::make('tag_image')
                                    ->image()
                                    ->directory('product-tags'),
                            ])
                            ->columns(2)
                            ->createItemButtonLabel('Add Tag')
                            ->defaultItems(0) // prevent auto-adding an empty tag
                            ->dehydrated(fn($state): bool => collect($state)->filter()->isNotEmpty()),
                    ]),

                Forms\Components\Section::make('Featured Images')
                    ->schema([
                        Forms\Components\Repeater::make('featuredimages')
                            ->relationship()
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->image()
                                    ->directory('product-featured-images')
                                    ->required(),
                            ])
                            ->createItemButtonLabel('Add Featured Image')
                            ->defaultItems(0) // prevent auto-adding an empty tag
                            ->dehydrated(fn($state): bool => collect($state)->filter()->isNotEmpty()),
                    ]),

                Forms\Components\Section::make('Categories')
                    ->schema([
                        Forms\Components\Select::make('categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->options(Category::all()->pluck('name', 'id')->toArray())
                            ->label('Categories'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('product_image')->size(50),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('product_code')->searchable(),
                Tables\Columns\TextColumn::make('price')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('quantity')->sortable(),
                Tables\Columns\IconColumn::make('in_stock')->boolean()->sortable(),
                Tables\Columns\TextColumn::make('categories.name')->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categories')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->searchable(),

                Tables\Filters\TernaryFilter::make('in_stock')->label('In Stock'),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
                            );
                    })
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
            OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
