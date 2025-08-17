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
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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
                        Forms\Components\Placeholder::make('current_product_image')
                            ->label('Current Product Image')
                            ->content(function ($record) {
                                if (!$record || !$record->product_image) {
                                    return new \Illuminate\Support\HtmlString('
                                        <div class="flex flex-col items-center space-y-2 p-3 border border-dashed border-gray-300 rounded-lg bg-gray-50">
                                            <div class="w-20 h-20 rounded-lg bg-gray-200 flex items-center justify-center">
                                                <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <p class="text-sm text-gray-500">No product image</p>
                                        </div>
                                    ');
                                }

                                return new \Illuminate\Support\HtmlString('
                                    <div class="flex flex-col items-center space-y-2 p-3 border border-gray-200 rounded-lg bg-gray-50">
                                        <img 
                                            src="' . e($record->product_image) . '" 
                                            alt="Product Image" 
                                            
                                            class="w-24 h-24 rounded-lg object-cover border border-white shadow-sm"
                                            onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';"
                                        />
                                        <div class="w-24 h-24 rounded-lg bg-gray-300 flex items-center justify-center border border-white shadow-sm" style="display: none;">
                                            <svg class="w-8 h-8 text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <p class="text-xs text-gray-600">Current product image</p>
                                    </div>
                                ');
                            })
                            ->columnSpanFull()
                            ->hiddenOn('create'),

                        Forms\Components\FileUpload::make('product_image')
                            ->label('Upload New Product Image')
                            ->image()
                            ->required(fn($context): bool => $context === 'create') // Only required on create
                            ->columnSpanFull()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->disk('cloudinary')
                            ->visibility('public')
                            ->saveUploadedFileUsing(function ($file, $record = null) {
                                try {
                                    if ($record && $record->product_image && $file) {
                                        deleteCloud($record->product_image);
                                    }

                                    $uploaded = Cloudinary::uploadApi()->upload($file->getRealPath(), [
                                        'folder' => 'products',
                                        'transformation' => [
                                            'width' => 400,
                                            'height' => 400,
                                            'crop' => 'fill',
                                            'quality' => 'auto'
                                        ]
                                    ]);

                                    return $uploaded['secure_url'];
                                } catch (\Exception $e) {
                                    Log::error("Failed to upload product image: " . $e->getMessage());
                                    throw new \Exception("Failed to upload image: " . $e->getMessage());
                                }
                            })
                            ->getUploadedFileNameForStorageUsing(fn($file) => uniqid() . '.' . $file->getClientOriginalExtension())
                            ->dehydrated(fn($state) => filled($state)),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('product_code', 'PRD-' . strtoupper(Str::random(6)));
                            }),

                        Forms\Components\TextInput::make('product_code')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->readOnly(),

                        Forms\Components\Textarea::make('description')->columnSpanFull(),

                        Forms\Components\TextInput::make('brand_name')->maxLength(255),
                        Forms\Components\TextInput::make('video_url')->url()->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Pricing & Inventory')
                    ->schema([
                        Forms\Components\TextInput::make('cost_price')->numeric()->required()->prefix('₦'),
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->required()
                            ->prefix('₦')
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                if ($state && is_numeric($state)) {
                                    $set('suggested_profit', round($state * 0.4, 2));
                                }
                            }),
                        Forms\Components\TextInput::make('suggested_profit')
                            ->numeric()
                            ->prefix('₦')
                            ->readOnly()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('quantity')->numeric(),
                        Forms\Components\Toggle::make('in_stock')->required()->default(true),
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
                                    ->afterStateUpdated(fn(Forms\Set $set) => $set('tag_code', 'PRDTAG-' . strtoupper(Str::random(6)))),
                                Forms\Components\TextInput::make('tag_code')
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->readOnly(),
                                Forms\Components\Textarea::make('description')->columnSpanFull(),

                                Forms\Components\Placeholder::make('current_tag_image')
                                    ->label('Current Tag Image')
                                    ->content(function ($record) {
                                        $tagImage = $record?->tag_image;

                                        if (!$tagImage) {
                                            return new \Illuminate\Support\HtmlString('
                <div class="flex flex-col items-center space-y-1 p-2 border border-dashed border-gray-300 rounded-lg bg-gray-50">
                    <div class="w-12 h-12 rounded-lg bg-gray-200 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-xs text-gray-500">No tag image</p>
                </div>
            ');
                                        }

                                        return new \Illuminate\Support\HtmlString('
            <div class="flex flex-col items-center space-y-1 p-2 border border-gray-200 rounded-lg bg-gray-50">
                <img 
                    src="' . e($tagImage) . '" 
                    alt="Tag Image" 
                    class="w-14 h-14 rounded-lg object-cover border border-white shadow-sm"
                    onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';"
                />
                <div class="w-14 h-14 rounded-lg bg-gray-300 flex items-center justify-center border border-white shadow-sm" style="display: none;">
                    <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-xs text-gray-600">Current tag image</p>
            </div>
        ');
                                    })
                                    ->columnSpan(1),

                                Forms\Components\FileUpload::make('tag_image')
                                    ->label('Upload Tag Image')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(2048)
                                    ->disk('cloudinary')
                                    ->visibility('public')
                                    ->saveUploadedFileUsing(function ($file, $record = null) {
                                        try {
                                            if ($record && $record->tag_image && $file) {
                                                deleteCloud($record->tag_image);
                                            }

                                            $uploaded = Cloudinary::uploadApi()->upload($file->getRealPath(), [
                                                'folder' => 'product-tags',
                                                'transformation' => [
                                                    'width' => 200,
                                                    'height' => 200,
                                                    'crop' => 'fill',
                                                    'quality' => 'auto'
                                                ]
                                            ]);

                                            return $uploaded['secure_url'];
                                        } catch (\Exception $e) {
                                            Log::error("Failed to upload tag image: " . $e->getMessage());
                                            throw new \Exception("Failed to upload tag image: " . $e->getMessage());
                                        }
                                    })
                                    ->getUploadedFileNameForStorageUsing(fn($file) => uniqid() . '.' . $file->getClientOriginalExtension())
                                    ->dehydrated(fn($state) => filled($state))
                                    ->columnSpan(1)
                                    ->live(),
                            ])
                            ->columns(2)
                            ->createItemButtonLabel('Add Tag')
                            ->defaultItems(0)
                            ->dehydrated(fn($state): bool => collect($state)->filter()->isNotEmpty()),
                    ]),

                Forms\Components\Section::make('Featured Images')
                    ->schema([
                        Forms\Components\Repeater::make('featuredimages')
                            ->relationship()
                            ->schema([
                                Forms\Components\Placeholder::make('current_featured_image')
                                    ->label('Current Featured Image')
                                    ->content(function ($record, $state, Forms\Get $get) {
                                        // Get the current featured image from the state
                                        $featuredImage = $record?->image;

                                        if (!$featuredImage) {
                                            return new \Illuminate\Support\HtmlString('
                                                <div class="flex flex-col items-center space-y-1 p-2 border border-dashed border-gray-300 rounded-lg bg-gray-50">
                                                    <div class="w-16 h-16 rounded-lg bg-gray-200 flex items-center justify-center">
                                                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                    </div>
                                                    <p class="text-xs text-gray-500">No featured image</p>
                                                </div>
                                            ');
                                        }

                                        return new \Illuminate\Support\HtmlString('
                                            <div class="flex flex-col items-center space-y-1 p-2 border border-gray-200 rounded-lg bg-gray-50">
                                                <img 
                                                    src="' . e($featuredImage) . '" 
                                                    alt="Featured Image" 
                                                    class="w-18 h-18 rounded-lg object-cover border border-white shadow-sm"
                                                    onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';"
                                                />
                                                <div class="w-18 h-18 rounded-lg bg-gray-300 flex items-center justify-center border border-white shadow-sm" style="display: none;">
                                                    <svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                                <p class="text-xs text-gray-600">Current featured image</p>
                                            </div>
                                        ');
                                    })
                                    ->columnSpanFull()
                                    ->reactive(),

                                Forms\Components\FileUpload::make('image')
                                    ->label('Upload Featured Image')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(2048)
                                    ->disk('cloudinary')
                                    ->visibility('public')
                                    ->saveUploadedFileUsing(function ($file, $record = null) {
                                        try {
                                            if ($record && $record->image && $file) {
                                                deleteCloud($record->image);
                                            }
                                            $uploaded = Cloudinary::uploadApi()->upload($file->getRealPath(), [
                                                'folder' => 'product-featured-images',
                                                'transformation' => [
                                                    'width' => 400,
                                                    'height' => 400,
                                                    'crop' => 'fill',
                                                    'quality' => 'auto'
                                                ]
                                            ]);
                                            return $uploaded['secure_url'];
                                        } catch (\Exception $e) {
                                            Log::error("Failed to upload featured image: " . $e->getMessage());
                                            throw new \Exception("Failed to upload featured image: " . $e->getMessage());
                                        }
                                    })
                                    ->getUploadedFileNameForStorageUsing(fn($file) => uniqid() . '.' . $file->getClientOriginalExtension())
                                    ->dehydrated(fn($state) => filled($state))
                                    ->required()
                                    ->columnSpanFull()
                                    ->live(),
                            ])
                            ->createItemButtonLabel('Add Featured Image')
                            ->defaultItems(0)
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
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categories')->relationship('categories', 'name')->multiple()->searchable(),
                Tables\Filters\TernaryFilter::make('in_stock')->label('In Stock'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->product_image) {
                                    deleteCloud($record->product_image);
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [OrdersRelationManager::class];
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
