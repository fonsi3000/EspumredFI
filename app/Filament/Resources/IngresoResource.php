<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngresoResource\Pages;
use App\Filament\Resources\IngresoResource\RelationManagers;
use App\Models\Ingreso;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Filament\Support\Facades\FilamentAsset;

class IngresoResource extends Resource
{
   protected static ?string $model = Ingreso::class;

   protected static ?string $navigationIcon = 'heroicon-o-arrow-up-circle';
   protected static ?string $modelLabel = 'Ingreso';
   protected static ?string $pluralModelLabel = 'Ingresos';
   protected static ?int $navigationSort = 1;

   public static function form(Form $form): Form
   {
       return $form
           ->schema([
               Forms\Components\Grid::make(2)
                   ->schema([
                       Forms\Components\TextInput::make('numero_comprobante')
                           ->label('N° Comprobante')
                           ->default(fn () => Ingreso::generarNumeroComprobante())
                           ->disabled()
                           ->dehydrated(),

                       Forms\Components\DatePicker::make('fecha')
                           ->label('Fecha')
                           ->default(now())
                           ->required(),

                       Forms\Components\Select::make('cuenta_ingreso_id')
                           ->label('Cuenta de Ingreso')
                           ->relationship('cuentaIngreso', 'nombre')
                           ->searchable()
                           ->preload()
                           ->required(),

                       Forms\Components\TextInput::make('monto')
                           ->label('Monto')
                           ->required()
                           ->numeric()
                           ->mask('999999999999')
                           ->prefix('$'),

                       Forms\Components\Select::make('forma_pago')
                           ->label('Forma de Pago')
                           ->options(Ingreso::FORMAS_PAGO)
                           ->required(),

                        Forms\Components\Select::make('user_id')
                           ->label('Registrado por')
                           ->relationship('user', 'name')
                           ->default(Auth::user()?->id)
                           ->disabled()
                           ->dehydrated()
                           ->required(),

                        Forms\Components\FileUpload::make('comprobante_path')
                           ->label('Comprobante')
                           ->image()
                           ->acceptedFileTypes(['image/*', 'application/pdf'])
                           ->maxSize(5120)
                           ->directory('comprobantes/ingresos')
                           ->columnSpanFull()
                           ->required(), 
                       
                       Forms\Components\Textarea::make('descripcion')
                           ->label('Descripción')
                           ->rows(3)
                           ->columnSpanFull()
                           ->required(), 

                       Forms\Components\Select::make('estado')
                           ->label('Estado')
                           ->options(Ingreso::ESTADOS)
                           ->default('activo')
                           ->disabled()
                           ->dehydrated()
                           ->required(),
                   ]),
           ]);
   }

   public static function table(Table $table): Table
   {
       return $table
           ->columns([
               Tables\Columns\TextColumn::make('numero_comprobante')
                   ->label('N° Comprobante')
                   ->searchable()
                   ->sortable(),

               Tables\Columns\TextColumn::make('fecha')
                   ->label('Fecha')
                   ->date('d/m/Y')
                   ->sortable(),

               Tables\Columns\TextColumn::make('cuentaIngreso.nombre')
                   ->label('Cuenta')
                   ->searchable()
                   ->sortable(),

               Tables\Columns\TextColumn::make('monto')
                   ->label('Monto')
                   ->money('COP')
                   ->sortable(),

               Tables\Columns\TextColumn::make('forma_pago')
                   ->label('Forma de Pago')
                   ->badge(),

               Tables\Columns\TextColumn::make('user.name')
                   ->label('Registrado por')
                   ->sortable(),

               Tables\Columns\TextColumn::make('estado')
                   ->badge()
                   ->color(fn (string $state): string => match ($state) {
                       'activo' => 'success',
                       'anulado' => 'danger',
                   }),

               Tables\Columns\ImageColumn::make('comprobante_path')
                   ->label('Comprobante')
                   ->circular()
                   ->defaultImageUrl(url('/images/placeholder.png')),
           ])
           ->defaultSort('created_at', 'desc')
           ->filters([
               Tables\Filters\SelectFilter::make('cuenta_ingreso_id')
                   ->relationship('cuentaIngreso', 'nombre')
                   ->label('Cuenta')
                   ->preload()
                   ->multiple(),

               Tables\Filters\SelectFilter::make('estado')
                   ->options(Ingreso::ESTADOS)
                   ->label('Estado'),

               Tables\Filters\Filter::make('fecha')
                   ->form([
                       Forms\Components\DatePicker::make('desde'),
                       Forms\Components\DatePicker::make('hasta'),
                   ])
                   ->query(function (Builder $query, array $data): Builder {
                       return $query
                           ->when(
                               $data['desde'],
                               fn (Builder $query, $date): Builder => $query->whereDate('fecha', '>=', $date),
                           )
                           ->when(
                               $data['hasta'],
                               fn (Builder $query, $date): Builder => $query->whereDate('fecha', '<=', $date),
                           );
                   })
           ])
           ->actions([
               Tables\Actions\EditAction::make()
                   ->modalWidth('md'),
               Action::make('anular')
                   ->requiresConfirmation()
                   ->color('danger')
                   ->icon('heroicon-o-x-circle')
                   ->visible(fn ($record) => $record->estado === 'activo')
                   ->action(fn ($record) => $record->update(['estado' => 'anulado'])),
                Action::make('ver_comprobante')
                   ->label('Ver Comprobante')
                   ->icon('heroicon-o-eye')
                   ->url(fn ($record) => "/storage/{$record->comprobante_path}")
                   ->openUrlInNewTab()
                   ->visible(fn ($record) => $record->comprobante_path !== null),
           ])
           ->bulkActions([
               Tables\Actions\BulkActionGroup::make([
                   Tables\Actions\DeleteBulkAction::make(),
               ]),
           ]);
   }

   public static function getPages(): array
   {
       return [
           'index' => Pages\ListIngresos::route('/'),
       ];
   }
}