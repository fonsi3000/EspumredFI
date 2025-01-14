<?php

namespace App\Filament\Resources\LoanRequestResource\Pages;

use App\Filament\Resources\LoanRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLoanRequests extends ListRecords
{
    protected static string $resource = LoanRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth('5xl'),
        ];
    }
}
