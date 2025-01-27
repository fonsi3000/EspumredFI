<?php

namespace App\Filament\Litoral\Resources\LoanRequestLitoralResource\Pages;

use App\Filament\Litoral\Resources\LoanRequestLitoralResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLoanRequestLitorals extends ListRecords
{
    protected static string $resource = LoanRequestLitoralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth('5xl'),
        ];
    }
}
