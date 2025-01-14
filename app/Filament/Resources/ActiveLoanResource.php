<?php

namespace App\Filament\Resources;

use Filament\Notifications\Notification;
use App\Filament\Resources\ActiveLoanResource\Pages;
use App\Models\ActiveLoan;
use App\Models\LoanPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Colors\Color;
use Filament\Infolists\Components\TextEntry;

class ActiveLoanResource extends Resource
{
    protected static ?string $model = ActiveLoan::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $modelLabel = 'Préstamo Activo';
    protected static ?string $pluralModelLabel = 'Préstamos Activos';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Información del Préstamo')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('loan_number')
                                            ->label('Número de Préstamo')
                                            ->required()
                                            ->disabled(),

                                        Forms\Components\TextInput::make('amount')
                                            ->label('Monto del Préstamo')
                                            ->required()
                                            ->numeric()
                                            ->prefix('$')
                                            ->disabled(),

                                        Forms\Components\TextInput::make('term_months')
                                            ->label('Plazo (Meses)')
                                            ->required()
                                            ->numeric()
                                            ->disabled(),

                                        Forms\Components\TextInput::make('interest_rate')
                                            ->label('Tasa de Interés')
                                            ->required()
                                            ->numeric()
                                            ->suffix('%')
                                            ->disabled(),

                                        Forms\Components\DatePicker::make('start_date')
                                            ->label('Fecha Inicio')
                                            ->required()
                                            ->disabled(),

                                        Forms\Components\DatePicker::make('next_payment_date')
                                            ->label('Próximo Pago')
                                            ->required(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Sección de Resumen 
                Infolists\Components\Section::make('Resumen del Préstamo')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                // Monto del Préstamo
                                Infolists\Components\TextEntry::make('amount')
                                    ->label('Monto Préstamo')
                                    ->formatStateUsing(fn(string $state): string =>
                                    '$ ' . number_format(floatval($state), 2, ',', '.'))
                                    ->color('primary')
                                    ->size(TextEntry\TextEntrySize::Large),

                                // Saldo Actual
                                Infolists\Components\TextEntry::make('current_balance')
                                    ->label('Saldo Actual')
                                    ->formatStateUsing(fn(string $state): string =>
                                    '$ ' . number_format(floatval($state), 2, ',', '.'))
                                    ->color('warning')
                                    ->size(TextEntry\TextEntrySize::Large),

                                // Total Pagado
                                Infolists\Components\TextEntry::make('total_paid')
                                    ->label('Total Pagado')
                                    ->formatStateUsing(fn(string $state): string =>
                                    '$ ' . number_format(floatval($state), 2, ',', '.'))
                                    ->color('success')
                                    ->size(TextEntry\TextEntrySize::Large),

                                // Estado del Préstamo
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Estado')
                                    ->badge()
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'active' => 'Activo',
                                        'delayed' => 'Atrasado',
                                        'defaulted' => 'En Mora',
                                        'completed' => 'Completado',
                                        default => 'Desconocido',
                                    })
                                    ->color(fn(string $state): string => match ($state) {
                                        'active' => 'success',
                                        'delayed' => 'warning',
                                        'defaulted' => 'danger',
                                        'completed' => 'primary',
                                        default => 'gray',
                                    }),
                            ]),

                        // Detalles Adicionales
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                // Capital Pagado
                                Infolists\Components\TextEntry::make('total_principal_paid')
                                    ->label('Capital Pagado')
                                    ->formatStateUsing(fn(string $state): string =>
                                    '$ ' . number_format(floatval($state), 2, ',', '.'))
                                    ->color('info')
                                    ->size(TextEntry\TextEntrySize::Large),

                                // Intereses Pagados
                                Infolists\Components\TextEntry::make('total_interest_paid')
                                    ->label('Intereses Pagados')
                                    ->formatStateUsing(fn(string $state): string =>
                                    '$ ' . number_format(floatval($state), 2, ',', '.'))
                                    ->color('info')
                                    ->size(TextEntry\TextEntrySize::Large),

                                // Progreso
                                Infolists\Components\TextEntry::make('progress')
                                    ->label('Progreso del Préstamo')
                                    ->state(fn($record) => $record->getProgressPercentage())
                                    ->formatStateUsing(fn($state) => number_format($state, 2) . '%')
                                    ->color(fn($state) => floatval($state) >= 50 ? 'success' : 'warning')
                                    ->size(TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('payments_remaining')
                                    ->label('Cuotas Pendientes')
                                    ->size(TextEntry\TextEntrySize::Large)
                            ]),

                        // // Información de Pagos quiero que me hagas la misma dinamica pero si las cuotas fueran quincenal
                        // Infolists\Components\Grid::make(3)
                        //     ->schema([
                        //         Infolists\Components\TextEntry::make('payments_made')
                        //             ->label('Cuotas Pagadas'),

                        //         Infolists\Components\TextEntry::make('payments_remaining')
                        //             ->label('Cuotas Pendientes'),

                        //         Infolists\Components\TextEntry::make('next_payment_date')
                        //             ->label('Próximo Pago')
                        //             ->date('d/m/Y'),
                        //     ]),
                    ]),
                // Tabla de Amortización
                Infolists\Components\Section::make('Tabla de Amortización')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('payments')
                            ->schema([
                                Infolists\Components\TextEntry::make('payment_number')
                                    ->label('Cuota'),

                                Infolists\Components\TextEntry::make('scheduled_date')
                                    ->label('Fecha')
                                    ->date('d/m/Y'),

                                Infolists\Components\TextEntry::make('principal_amount')
                                    ->label('Capital')
                                    ->formatStateUsing(fn(string $state): string =>
                                    '$ ' . number_format(floatval($state), 2, ',', '.')),

                                Infolists\Components\TextEntry::make('interest_amount')
                                    ->label('Interés')
                                    ->formatStateUsing(fn(string $state): string =>
                                    '$ ' . number_format(floatval($state), 2, ',', '.')),

                                // Cuota Total (Capital + Interés)
                                Infolists\Components\TextEntry::make('total_payment')
                                    ->label('Cuota Total')
                                    ->state(function ($record): float {
                                        return floatval($record->principal_amount) + floatval($record->interest_amount);
                                    })
                                    ->formatStateUsing(fn(float $state): string =>
                                    '$ ' . number_format($state, 2, ',', '.')),

                                Infolists\Components\TextEntry::make('remaining_balance')
                                    ->label('Saldo')
                                    ->formatStateUsing(fn(string $state): string =>
                                    '$ ' . number_format(floatval($state), 2, ',', '.')),

                                Infolists\Components\TextEntry::make('status')
                                    ->label('Estado')
                                    ->badge()
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'paid' => 'Pagado',
                                        'pending' => 'Pendiente',
                                        'partial' => 'Parcial',
                                        'late' => 'Atrasado',
                                        default => 'Desconocido',
                                    })
                                    ->color(fn(string $state): string => match ($state) {
                                        'paid' => 'success',
                                        'pending' => 'warning',
                                        'partial' => 'info',
                                        'late' => 'danger',
                                        default => 'gray',
                                    }),

                                // Botón de Acción para Pagar (modificado para ser secuencial)
                                Infolists\Components\Actions::make([
                                    Infolists\Components\Actions\Action::make('pay')
                                        ->label('Pagar Cuota')
                                        ->icon('heroicon-o-banknotes')
                                        ->color('success')
                                        ->visible(function ($record) {
                                            // Verificar si es el siguiente pago pendiente
                                            $nextPayment = $record->activeLoan->getNextPayment();
                                            return $record->status === 'pending' &&
                                                $nextPayment &&
                                                $nextPayment->id === $record->id;
                                        })
                                        ->action(function ($record) {
                                            $totalAmount = floatval($record->principal_amount) + floatval($record->interest_amount);
                                            $record->activeLoan->applyPayment(
                                                amount: $totalAmount,
                                                paymentDate: now(),
                                                notes: 'Pago automático de cuota'
                                            );
                                        })
                                        ->successNotification(
                                            Notification::make()
                                                ->success()
                                                ->title('Pago registrado')
                                                ->body('El pago se ha registrado correctamente')
                                        )
                                ]),
                            ])
                            ->columns(8),
                    ]),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('loan_number')
                    ->label('Número de Préstamo')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('loanRequest.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto Total')
                    ->money('COP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_balance')
                    ->label('Saldo Actual')
                    ->money('COP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('next_payment_date')
                    ->label('Próximo Pago')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'delayed' => 'warning',
                        'defaulted' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activo',
                        'delayed' => 'Atrasado',
                        'defaulted' => 'En Mora',
                        'completed' => 'Completado',
                    ]),
            ])
            ->actions([
                ViewAction::make()
                    ->modalWidth('7xl'),

                Action::make('register_payment')
                    ->label('Registrar Pago')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->form([
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Fecha de Pago')
                            ->required()
                            ->default(now()),

                        Forms\Components\TextInput::make('amount')
                            ->label('Monto')
                            ->required()
                            ->numeric()
                            ->prefix('$'),

                        Forms\Components\FileUpload::make('receipt')
                            ->label('Comprobante')
                            ->directory('payment-receipts')
                            ->acceptedFileTypes(['application/pdf']),

                        Forms\Components\Textarea::make('notes')
                            ->label('Observaciones'),
                    ])
                    ->action(function (ActiveLoan $record, array $data) {
                        $record->applyPayment(
                            amount: $data['amount'],
                            paymentDate: $data['payment_date'],
                            receiptNumber: $data['receipt'] ?? null,
                            notes: $data['notes'] ?? null
                        );
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActiveLoans::route('/'),
            'create' => Pages\CreateActiveLoan::route('/create'),
            'view' => Pages\ViewActiveLoan::route('/{record}'),
            'edit' => Pages\EditActiveLoan::route('/{record}/edit'),
        ];
    }
}
