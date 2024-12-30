<?php

namespace App\Filament\Resources\CuentaEgresoResource\Pages;

use App\Filament\Resources\CuentaEgresoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCuentaEgresos extends ListRecords
{
    protected static string $resource = CuentaEgresoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth('md'),
        ];
    }
}