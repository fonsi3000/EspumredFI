<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CuentaIngresoResource\Pages;
use App\Filament\Resources\CuentaIngresoResource\RelationManagers;
use App\Models\CuentaIngreso;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CuentaIngresoResource extends Resource
{
   protected static ?string $model = CuentaIngreso::class;

   protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';
   protected static ?string $modelLabel = 'Cuenta de Ingreso';
   protected static ?string $pluralModelLabel = 'Cuentas de Ingresos';
   protected static ?int $navigationSort = 3;

   public static function form(Form $form): Form
   {
       return $form
           ->schema([
               Forms\Components\TextInput::make('codigo')
                   ->required()
                   ->unique(ignoreRecord: true)
                   ->maxLength(255),
               Forms\Components\TextInput::make('nombre')
                   ->required()
                   ->maxLength(255),
               Forms\Components\Textarea::make('descripcion')
                   ->maxLength(65535)
                   ->columnSpanFull(),
               Forms\Components\Toggle::make('is_active')
                   ->required()
                   ->default(true)
                   ->label('Activo'),
           ]);
   }

   public static function table(Table $table): Table
   {
       return $table
           ->columns([
               Tables\Columns\TextColumn::make('codigo')
                   ->searchable()
                   ->sortable(),
               Tables\Columns\TextColumn::make('nombre')
                   ->searchable()
                   ->sortable(),
               Tables\Columns\IconColumn::make('is_active')
                   ->boolean()
                   ->label('Estado'),
               Tables\Columns\TextColumn::make('created_at')
                   ->dateTime()
                   ->sortable()
                   ->toggleable(isToggledHiddenByDefault: true)
                   ->label('Creado'),
               Tables\Columns\TextColumn::make('updated_at')
                   ->dateTime()
                   ->sortable()
                   ->toggleable(isToggledHiddenByDefault: true)
                   ->label('Actualizado'),
           ])
           ->filters([
               Tables\Filters\TernaryFilter::make('is_active')
                   ->label('Activo')
                   ->boolean()
                   ->trueLabel('Cuentas Activas')
                   ->falseLabel('Cuentas Inactivas')
                   ->native(false),
           ])
           ->actions([
               Tables\Actions\EditAction::make()
                   ->modalWidth('md'),
               Tables\Actions\DeleteAction::make(),
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
           'index' => Pages\ListCuentaIngresos::route('/'),
       ];
   }
}