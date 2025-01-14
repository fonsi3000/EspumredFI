<?php

namespace App\Filament\Resources\ActiveLoanResource\Pages;

use App\Filament\Resources\ActiveLoanResource;
use Filament\Resources\Pages\ViewRecord;

class ViewActiveLoan extends ViewRecord
{
    protected static string $resource = ActiveLoanResource::class;

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
