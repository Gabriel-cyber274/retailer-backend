<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Filament\Admin\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('viewFrontend')
                ->label('Open in Website')
                ->url(fn($record) => url("/order/{$record->id}"))
                ->openUrlInNewTab()
                ->button()
                ->color('success')
                ->icon('heroicon-o-link'),
        ];
    }
}
