<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoanRequestResource\Pages;
use App\Models\LoanRequest;
use App\Models\ActiveLoan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;

class LoanRequestResource extends Resource
{
    protected static ?string $model = LoanRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $modelLabel = 'Solicitud de Préstamo';
    protected static ?string $pluralModelLabel = 'Solicitudes de Préstamos';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Solicitante')
                    ->description('Datos personales del solicitante')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre Completo del solicitante')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('document_number')
                                    ->label('Número de Documento')
                                    ->required()
                                    ->maxLength(20),

                                Forms\Components\TextInput::make('phone')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->required()
                                    ->maxLength(20),

                                Forms\Components\TextInput::make('email')
                                    ->label('Correo Electrónico')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('area')
                                    ->label('Proceso o Area')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('position')
                                    ->label('Cargo')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Select::make('company')
                                    ->label('Empresa')
                                    ->options([
                                        'espumas_medellin' => 'Espumas medellin',
                                        'espumados_litoral' => 'Espumados del litoral',
                                        'ctn_carga' => 'STN Carga y logistica',
                                    ])
                                    ->required(),
                            ]),
                    ]),

                Forms\Components\Section::make('Detalles del Préstamo')
                    ->description('Información sobre el préstamo solicitado')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('amount')
                                    ->label('Monto Solicitado')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required(),

                                Forms\Components\TextInput::make('term_months')
                                    ->label('Plazo (Meses)')
                                    ->numeric()
                                    ->required(),

                                Forms\Components\TextInput::make('interest_rate')
                                    ->label('Tasa Mensual (%)')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),

                                Forms\Components\Select::make('payment_frequency')
                                    ->label('Frecuencia de Pago')
                                    ->options(LoanRequest::PAYMENT_FREQUENCIES)
                                    ->required(),

                                Forms\Components\Select::make('loan_reason')
                                    ->label('Motivo del Préstamo')
                                    ->options(LoanRequest::LOAN_REASONS)
                                    ->required(),

                                Forms\Components\Textarea::make('description')
                                    ->label('Descripción')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Forms\Components\Section::make('Documentación')
                    ->description('Documentos requeridos para el préstamo')
                    ->schema([
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\FileUpload::make('guarantee_document')
                                    ->label('Documento')
                                    ->helperText('Subir documento en formato PDF. Máximo 5MB.')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->directory('loan-documents')
                                    ->preserveFilenames()
                                    ->maxSize(5120)
                                    ->downloadable(),

                                Forms\Components\Textarea::make('observations')
                                    ->label('Motivo de Rechazo')
                                    ->rows(3)
                                    ->hidden(fn($record) => !$record || $record->status !== 'rejected'),

                                Forms\Components\Hidden::make('loan_number')
                                    ->default(fn() => LoanRequest::generateLoanNumber()),

                                Forms\Components\Hidden::make('status')
                                    ->default('pending_approval'),

                                Forms\Components\Hidden::make('created_by_user_id')
                                    ->default(Auth::id()),

                                Forms\Components\Hidden::make('responsible_user_id')
                                    ->default(Auth::id()),
                            ]),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\Group::make([
                                    Infolists\Components\TextEntry::make('loan_number')
                                        ->label('Número de Préstamo')
                                        ->weight('bold'),
                                    Infolists\Components\TextEntry::make('name')
                                        ->label('Nombre Completo'),
                                    Infolists\Components\TextEntry::make('document_number')
                                        ->label('Documento'),
                                    Infolists\Components\TextEntry::make('phone')
                                        ->label('Teléfono')
                                        ->icon('heroicon-m-phone'),
                                    Infolists\Components\TextEntry::make('email')
                                        ->label('Correo')
                                        ->icon('heroicon-m-envelope'),
                                    Infolists\Components\TextEntry::make('area')
                                        ->label('Área'),
                                    Infolists\Components\TextEntry::make('position')
                                        ->label('Cargo'),
                                    Infolists\Components\TextEntry::make('company')
                                        ->label('Empresa')
                                        ->formatStateUsing(fn(string $state): string => [
                                            'espumas_medellin' => 'Espumas medellin',
                                            'espumados_litoral' => 'Espumados del litoral',
                                            'ctn_carga' => 'STN Carga y logistica',
                                        ][$state] ?? $state),
                                ]),

                                Infolists\Components\Group::make([
                                    Infolists\Components\TextEntry::make('amount')
                                        ->label('Monto')
                                        ->money('COP')
                                        ->weight('bold'),
                                    Infolists\Components\TextEntry::make('term_months')
                                        ->label('Plazo')
                                        ->suffix(' meses'),
                                    Infolists\Components\TextEntry::make('interest_rate')
                                        ->label('Tasa de Interés')
                                        ->suffix('%'),
                                    Infolists\Components\TextEntry::make('payment_frequency')
                                        ->label('Frecuencia de Pago')
                                        ->formatStateUsing(fn(string $state): string => LoanRequest::PAYMENT_FREQUENCIES[$state] ?? $state),
                                    Infolists\Components\TextEntry::make('loan_reason')
                                        ->label('Motivo del Préstamo')
                                        ->formatStateUsing(fn(string $state): string => LoanRequest::LOAN_REASONS[$state] ?? $state),
                                    Infolists\Components\TextEntry::make('created_at')
                                        ->label('Fecha de Solicitud')
                                        ->dateTime('d/m/Y H:i'),
                                    Infolists\Components\TextEntry::make('status')
                                        ->label('Estado')
                                        ->badge()
                                        ->formatStateUsing(fn(string $state): string => LoanRequest::STATUSES[$state] ?? $state)
                                        ->color(fn(string $state): string => match ($state) {
                                            'pending_approval' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            default => 'gray',
                                        }),
                                ]),
                            ]),

                        Infolists\Components\TextEntry::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('observations')
                            ->label('Observaciones')
                            ->visible(fn($record) => $record->status === 'rejected')
                            ->columnSpanFull(),
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

                Tables\Columns\TextColumn::make('name')
                    ->label('Solicitante')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('position')
                    ->label('Cargo')
                    ->sortable(),

                // Tables\Columns\TextColumn::make('term_months')
                //     ->label('Plazo')
                //     ->suffix(' meses'),

                // Tables\Columns\TextColumn::make('payment_frequency')
                //     ->label('Frecuencia de Pago')
                //     ->formatStateUsing(fn(string $state): string => LoanRequest::PAYMENT_FREQUENCIES[$state] ?? $state),

                // Tables\Columns\TextColumn::make('company')
                //     ->label('Empresa')
                //     ->formatStateUsing(fn(string $state): string => [
                //         'espumas_medellin' => 'Espumas medellin',
                //         'espumados_litoral' => 'Espumados del litoral',
                //         'ctn_carga' => 'STN Carga y logistica',
                //     ][$state] ?? $state),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => LoanRequest::STATUSES[$state] ?? $state)
                    ->color(fn(string $state): string => match ($state) {
                        'pending_approval' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Solicitud')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(LoanRequest::STATUSES),
            ])
            ->actions([
                ViewAction::make()
                    ->modalWidth('4xl'),

                Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('¿Está seguro de aprobar esta solicitud?')
                    ->modalDescription('Al aprobar la solicitud se creará un préstamo activo.')
                    ->modalSubmitActionLabel('Sí, aprobar')
                    ->visible(fn($record) => $record->status === 'pending_approval')
                    ->action(function (LoanRequest $record) {
                        try {
                            $activeLoan = DB::transaction(function () use ($record) {
                                $record->update(['status' => 'approved']);
                                return ActiveLoan::createFromRequest($record);
                            });

                            Notification::make()
                                ->title('Préstamo aprobado exitosamente')
                                ->success()
                                ->send();

                            // Corregimos la ruta de redirección
                            return redirect()->to('inicio/loan-requests');
                            // O alternativamente:
                            // return redirect('/admin/active-loans');

                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al aprobar el préstamo')
                                ->body('Hubo un problema al crear el préstamo activo.')
                                ->danger()
                                ->send();

                            throw $e;
                        }
                    }),

                Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('observations')
                            ->label('Motivo del Rechazo')
                            ->required()
                    ])
                    ->visible(fn($record) => $record->status === 'pending_approval')
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'observations' => $data['observations']
                        ]);

                        Notification::make()
                            ->title('Solicitud rechazada')
                            ->success()
                            ->send();
                    }),

                Action::make('ver_documento')
                    ->label('Ver Documento')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => "/storage/{$record->guarantee_document}")
                    ->openUrlInNewTab()
                    ->visible(fn($record) => $record->guarantee_document !== null),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //  
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoanRequests::route('/'),
        ];
    }
}
