<?php

namespace App\Filament\Litoral\Resources\CuentaEgresoLitoralResource\Pages;

use App\Filament\Litoral\Resources\CuentaEgresoLitoralResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCuentaEgresoLitorals extends ListRecords
{
    protected static string $resource = CuentaEgresoLitoralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
