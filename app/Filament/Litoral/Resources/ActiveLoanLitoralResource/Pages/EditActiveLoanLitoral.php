<?php

namespace App\Filament\Litoral\Resources\ActiveLoanLitoralResource\Pages;

use App\Filament\Litoral\Resources\ActiveLoanLitoralResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditActiveLoanLitoral extends EditRecord
{
    protected static string $resource = ActiveLoanLitoralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
