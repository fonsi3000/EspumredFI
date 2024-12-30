<?php

namespace App\Filament\Resources\IngresoResource\Widgets;

use App\Models\Ingreso;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class IngresosOverview extends BaseWidget
{
    protected static ?int $sort = -3;
    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 6; // Ocupa la otra mitad del ancho

    protected function getStats(): array
    {
        return [
            Stat::make('Total Ingresos', '$ ' . number_format(Ingreso::where('estado', 'activo')->sum('monto'), 2, ',', '.'))
                ->description(Ingreso::where('estado', 'activo')->count() . ' registros activos')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3])
                ->color('success'),
        ];
    }
}
