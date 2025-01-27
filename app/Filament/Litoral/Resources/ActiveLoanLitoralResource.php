<?php

namespace App\Filament\Litoral\Resources;

use App\Filament\Litoral\Resources\ActiveLoanLitoralResource\Pages;
use App\Models\ActiveLoanLitoral;
use App\Models\LoanPaymentLitoral;
use Filament\Notifications\Notification;
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

class ActiveLoanLitoralResource extends Resource
{
    protected static ?string $model = ActiveLoanLitoral::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $modelLabel = 'Préstamo Litoral';
    protected static ?string $pluralModelLabel = 'Préstamos Litorales';
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
                Infolists\Components\Section::make('Informacion del Deudor')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('loanRequest.name')
                                    ->label('Nombre')
                                    ->size(TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('loanRequest.document_number')
                                    ->label('Numero de documento')
                                    ->size(TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('loanRequest.email')
                                    ->label('Correo')
                                    ->size(TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('loanRequest.phone')
                                    ->label('Numero de celular')
                                    ->size(TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('loanRequest.area')
                                    ->label('Area')
                                    ->size(TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('loanRequest.position')
                                    ->label('Cargo')
                                    ->size(TextEntry\TextEntrySize::Large),
                            ]),
                    ]),

                Infolists\Components\Section::make('Resumen del Préstamo')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('amount')
                                    ->label('Monto Préstamo')
                                    ->formatStateUsing(fn(string $state): string =>
                                    '$ ' . number_format(floatval($state), 2, ',', '.'))
                                    ->color('primary')
                                    ->size(TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('current_balance')
                                    ->label('Saldo Actual')
                                    ->formatStateUsing(fn(string $state): string =>
                                    '$ ' . number_format(floatval($state), 2, ',', '.'))
                                    ->color('warning')
                                    ->size(TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('total_paid')
                                    ->label('Total Pagado')
                                    ->formatStateUsing(fn(string $state): string =>
                                    '$ ' . number_format(floatval($state), 2, ',', '.'))
                                    ->color('success')
                                    ->size(TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('status')
                                    ->label('Estado')
                                    ->badge()
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'pending_disbursement' => 'Sin Desembolsar',
                                        'active' => 'Activo',
                                        'completed' => 'Completado',
                                        default => 'Desconocido',
                                    })
                                    ->color(fn(string $state): string => match ($state) {
                                        'active' => 'success',
                                        'pending_disbursement' => 'warning',
                                        'completed' => 'primary',
                                        default => 'gray',
                                    }),
                            ]),

                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_principal_paid')
                                    ->label('Capital Pagado')
                                    ->formatStateUsing(fn(string $state): string =>
                                    '$ ' . number_format(floatval($state), 2, ',', '.'))
                                    ->color('info')
                                    ->size(TextEntry\TextEntrySize::Large),

                                Infolists\Components\TextEntry::make('total_interest_paid')
                                    ->label('Intereses Pagados')
                                    ->formatStateUsing(fn(string $state): string =>
                                    '$ ' . number_format(floatval($state), 2, ',', '.'))
                                    ->color('info')
                                    ->size(TextEntry\TextEntrySize::Large),

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
                    ]),

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

                                Infolists\Components\Actions::make([
                                    Infolists\Components\Actions\Action::make('pay')
                                        ->label('Pagar Cuota')
                                        ->icon('heroicon-o-banknotes')
                                        ->color('success')
                                        ->visible(function ($record) {
                                            $nextPayment = $record->activeLoanLitoral->getNextPayment();
                                            return $record->status === 'pending' &&
                                                $nextPayment &&
                                                $nextPayment->id === $record->id;
                                        })
                                        ->action(function ($record) {
                                            $totalAmount = floatval($record->principal_amount) + floatval($record->interest_amount);
                                            $record->activeLoanLitoral->applyPayment(
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

                Tables\Columns\TextColumn::make('loanRequest.document_number')
                    ->label('Documento cliente')
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
                        'pending_disbursement' => 'warning',
                        'active' => 'success',
                        'completed' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending_disbursement' => 'Sin Desembolsar',
                        'active' => 'Activo',
                        'completed' => 'Completado',
                        default => 'Desconocido',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending_disbursement' => 'Sin Desembolsar',
                        'active' => 'Activo',
                        'completed' => 'Completado',
                    ]),
            ])
            ->actions([
                ViewAction::make()
                    ->modalWidth('7xl'),

                Action::make('disburse')
                    ->label('Desembolsar Préstamo')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('¿Desea desembolsar este préstamo?')
                    ->modalDescription('Esta acción iniciará el cronograma de pagos del préstamo.')
                    ->modalSubmitActionLabel('Sí, desembolsar')
                    ->visible(
                        fn(ActiveLoanLitoral $record): bool =>
                        $record->status === ActiveLoanLitoral::STATUS_PENDING_DISBURSEMENT
                    )
                    ->action(function (ActiveLoanLitoral $record) {
                        try {
                            $record->disburse();
                            Notification::make()
                                ->success()
                                ->title('Préstamo Desembolsado')
                                ->body('El préstamo ha sido desembolsado exitosamente.')
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
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
            'index' => Pages\ListActiveLoanLitorals::route('/'),
            'create' => Pages\CreateActiveLoanLitoral::route('/create'),
            'view' => Pages\ViewActiveLoanLitoral::route('/{record}'),
            'edit' => Pages\EditActiveLoanLitoral::route('/{record}/edit'),
        ];
    }
}
