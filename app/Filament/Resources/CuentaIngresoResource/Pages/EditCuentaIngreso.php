<?php

namespace App\Filament\Resources\CuentaIngresoResource\Pages;

use App\Filament\Resources\CuentaIngresoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCuentaIngreso extends EditRecord
{
    protected static string $resource = CuentaIngresoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
