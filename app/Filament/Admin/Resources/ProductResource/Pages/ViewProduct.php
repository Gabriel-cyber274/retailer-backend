<?php

namespace App\Filament\Admin\Resources\ProductResource\Pages;

use App\Filament\Admin\Resources\ProductResource;
use App\Filament\Admin\Resources\ProductResource\Widgets\ProductOrderStats;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;


    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            ProductOrderStats::make([
                'recordId' => $this->record->id, // Pass the record ID
            ]),
        ];
    }
}
