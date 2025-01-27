<?php

namespace App\Filament\Litoral\Resources\GastoLitoralResource\Pages;

use App\Filament\Litoral\Resources\GastoLitoralResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGastoLitorals extends ListRecords
{
    protected static string $resource = GastoLitoralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
