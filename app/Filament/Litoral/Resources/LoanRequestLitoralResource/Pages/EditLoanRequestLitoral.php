<?php

namespace App\Filament\Litoral\Resources\LoanRequestLitoralResource\Pages;

use App\Filament\Litoral\Resources\LoanRequestLitoralResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoanRequestLitoral extends EditRecord
{
    protected static string $resource = LoanRequestLitoralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
