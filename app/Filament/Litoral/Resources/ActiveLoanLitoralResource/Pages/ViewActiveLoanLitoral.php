<?php

namespace App\Filament\Litoral\Resources\ActiveLoanLitoralResource\Pages;

use App\Filament\Litoral\Resources\ActiveLoanLitoralResource;
use Filament\Resources\Pages\ViewRecord;

class ViewActiveLoanLitoral extends ViewRecord
{
    protected static string $resource = ActiveLoanLitoralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('back')
                ->label('Volver')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
