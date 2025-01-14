<?php

namespace App\Filament\Resources\ActiveLoanResource\Pages;

use App\Filament\Resources\ActiveLoanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActiveLoans extends ListRecords
{
    protected static string $resource = ActiveLoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
