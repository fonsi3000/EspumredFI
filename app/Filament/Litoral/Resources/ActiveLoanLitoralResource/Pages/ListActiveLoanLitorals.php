<?php

namespace App\Filament\Litoral\Resources\ActiveLoanLitoralResource\Pages;

use App\Filament\Litoral\Resources\ActiveLoanLitoralResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ActiveLoanResource;
use App\Imports\ActiveLoanLitoralImport;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class ListActiveLoanLitorals extends ListRecords
{
    protected static string $resource = ActiveLoanLitoralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->label('Importar Pagos Masivamente')
                ->processCollectionUsing(function (string $modelClass, Collection $collection) {
                    try {
                        $importer = new ActiveLoanLitoralImport();
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
                ->use(ActiveLoanLitoralImport::class)
                ->color('success')
                ->icon('heroicon-o-arrow-up-tray')
        ];
    }
}
