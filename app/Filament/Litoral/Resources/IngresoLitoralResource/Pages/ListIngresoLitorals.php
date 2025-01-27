<?php

namespace App\Filament\Litoral\Resources\IngresoLitoralResource\Pages;

use App\Filament\Litoral\Resources\IngresoLitoralResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIngresoLitorals extends ListRecords
{
    protected static string $resource = IngresoLitoralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
