<?php

namespace App\Filament\Resources\CuentaIngresoResource\Pages;

use App\Filament\Resources\CuentaIngresoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCuentaIngresos extends ListRecords
{
    protected static string $resource = CuentaIngresoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth('md'),
        ];
    }
}