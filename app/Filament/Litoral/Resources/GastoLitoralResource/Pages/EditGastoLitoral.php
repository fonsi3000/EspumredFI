<?php

namespace App\Filament\Litoral\Resources\GastoLitoralResource\Pages;

use App\Filament\Litoral\Resources\GastoLitoralResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGastoLitoral extends EditRecord
{
    protected static string $resource = GastoLitoralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
