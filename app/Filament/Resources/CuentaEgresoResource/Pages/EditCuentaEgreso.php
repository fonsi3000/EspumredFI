<?php

namespace App\Filament\Resources\CuentaEgresoResource\Pages;

use App\Filament\Resources\CuentaEgresoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCuentaEgreso extends EditRecord
{
    protected static string $resource = CuentaEgresoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
