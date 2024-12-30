<?php

namespace App\Filament\Resources\GastoResource\Widgets;

use App\Models\Gasto;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GastosOverview extends BaseWidget
{
    protected static ?int $sort = -2;
    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 6; // Ocupa la mitad del ancho

    protected function getStats(): array
    {
        return [
            Stat::make('Total Gastos', '$ ' . number_format(Gasto::where('estado', 'activo')->sum('monto'), 2, ',', '.'))
                ->description(Gasto::where('estado', 'activo')->count() . ' registros activos')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3])
                ->color('danger'),
        ];
    }
}
