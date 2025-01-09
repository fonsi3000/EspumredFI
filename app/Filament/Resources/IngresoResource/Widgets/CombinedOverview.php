<?php

namespace App\Filament\Resources\IngresoResource\Widgets;

use App\Models\Ingreso;
use App\Models\Gasto;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class CombinedOverview extends Widget
{
    protected static string $view = 'filament.resources.ingreso-resource.widgets.combined-overview';
    protected int | string | array $columnSpan = 'full';

    public $data = [];
    public $startDate;
    public $endDate;

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->filterResults();
    }

    public function filterResults()
    {
        $this->data = $this->getData();
    }

    public function getData()
    {
        return [
            'totalIngresos' => $this->getTotalIngresos(),
            'countIngresos' => $this->getCountIngresos(),
            'totalGastos' => $this->getTotalGastos(),
            'countGastos' => $this->getCountGastos(),
            'balance' => $this->getBalance(),
        ];
    }

    public function getTotalIngresos(): string
    {
        return number_format(
            Ingreso::query()
                ->where('estado', 'activo')
                ->whereBetween('fecha', [$this->startDate, $this->endDate])
                ->sum('monto'),
            2,
            ',',
            '.'
        );
    }

    public function getCountIngresos(): int
    {
        return Ingreso::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$this->startDate, $this->endDate])
            ->count();
    }

    public function getTotalGastos(): string
    {
        return number_format(
            Gasto::query()
                ->where('estado', 'activo')
                ->whereBetween('fecha', [$this->startDate, $this->endDate])
                ->sum('monto'),
            2,
            ',',
            '.'
        );
    }

    public function getCountGastos(): int
    {
        return Gasto::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$this->startDate, $this->endDate])
            ->count();
    }

    public function getBalance(): string
    {
        $ingresos = Ingreso::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$this->startDate, $this->endDate])
            ->sum('monto');

        $gastos = Gasto::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$this->startDate, $this->endDate])
            ->sum('monto');

        return number_format($ingresos - $gastos, 2, ',', '.');
    }

    public function render(): View
    {
        return view(static::$view, [
            'data' => $this->getData(),
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }
}