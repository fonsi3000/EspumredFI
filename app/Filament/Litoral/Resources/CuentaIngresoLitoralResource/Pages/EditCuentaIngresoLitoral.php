<?php

namespace App\Filament\Litoral\Resources\CuentaIngresoLitoralResource\Pages;

use App\Filament\Litoral\Resources\CuentaIngresoLitoralResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCuentaIngresoLitoral extends EditRecord
{
    protected static string $resource = CuentaIngresoLitoralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
