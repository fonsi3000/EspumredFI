<?php

namespace App\Filament\Resources\ActiveLoanResource\Pages;

use App\Filament\Resources\ActiveLoanResource;
use App\Imports\ActiveLoanImport;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;

class ListActiveLoans extends ListRecords
{
    protected static string $resource = ActiveLoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->label('Importar Pagos Masivamente')
                ->processCollectionUsing(function (string $modelClass, Collection $collection) {
                    try {
                        $importer = new ActiveLoanImport();
                        $importer->collection($collection);

                        Notification::make()
                            ->success()
                            ->title('Importación exitosa')
                            ->body('Los pagos han sido procesados correctamente.')
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Error en la importación')
                            ->body($e->getMessage())
                            ->send();
                    }

                    return $collection;
                })
                ->use(ActiveLoanImport::class)
                ->color('success')
                ->icon('heroicon-o-arrow-up-tray')
        ];
    }
}
