<?php

namespace App\Filament\Resources\ActiveLoanResource\Pages;

use App\Filament\Resources\ActiveLoanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditActiveLoan extends EditRecord
{
    protected static string $resource = ActiveLoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
