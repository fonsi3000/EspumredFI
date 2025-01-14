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

            \Filament\Actions\Action::make('register_payment')
                ->label('Registrar Pago')
                ->color('success')
                ->icon('heroicon-o-banknotes')
                ->action(function (array $data) {
                    $this->record->applyPayment(
                        amount: $data['amount'],
                        paymentDate: $data['payment_date'],
                        receiptNumber: $data['receipt'] ?? null,
                        notes: $data['notes'] ?? null
                    );
                })
                ->form([
                    \Filament\Forms\Components\DatePicker::make('payment_date')
                        ->label('Fecha de Pago')
                        ->required()
                        ->default(now()),

                    \Filament\Forms\Components\TextInput::make('amount')
                        ->label('Monto')
                        ->required()
                        ->numeric()
                        ->prefix('$'),

                    \Filament\Forms\Components\FileUpload::make('receipt')
                        ->label('Comprobante')
                        ->directory('payment-receipts'),

                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Observaciones'),
                ]),
        ];
    }
}
