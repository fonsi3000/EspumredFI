<?php

namespace App\Filament\Litoral\Resources\IngresoLitoralResource\Pages;

use App\Filament\Litoral\Resources\IngresoLitoralResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIngresoLitoral extends EditRecord
{
    protected static string $resource = IngresoLitoralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
