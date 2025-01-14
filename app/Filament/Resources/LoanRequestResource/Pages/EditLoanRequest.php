<?php

namespace App\Filament\Resources\LoanRequestResource\Pages;

use App\Filament\Resources\LoanRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoanRequest extends EditRecord
{
    protected static string $resource = LoanRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
