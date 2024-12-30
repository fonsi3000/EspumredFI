<?php

namespace App\Filament\Resources\IngresoResource\Widgets;

use App\Models\Ingreso;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class IngresosOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Ingresos', '$ ' . number_format(Ingreso::where('estado', 'activo')->sum('monto'), 2, ',', '.'))
                ->description('Total de registros: ' . Ingreso::where('estado', 'activo')->count())
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),
        ];
    }
}