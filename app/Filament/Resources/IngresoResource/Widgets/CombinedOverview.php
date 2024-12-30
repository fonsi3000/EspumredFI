<?php

namespace App\Filament\Resources\IngresoResource\Widgets;

use App\Models\Ingreso;
use App\Models\Gasto;
use Filament\Widgets\Widget;

class CombinedOverview extends Widget
{
   protected static string $view = 'filament.resources.ingreso-resource.widgets.combined-overview';
   protected int | string | array $columnSpan = 'full';
   
   public function getTotalIngresos(): string 
   {
       return number_format(Ingreso::where('estado', 'activo')->sum('monto'), 2, ',', '.');
   }

   public function getCountIngresos(): int
   {
       return Ingreso::where('estado', 'activo')->count();
   }

   public function getTotalGastos(): string
   {
       return number_format(Gasto::where('estado', 'activo')->sum('monto'), 2, ',', '.');
   }

   public function getCountGastos(): int 
   {
       return Gasto::where('estado', 'activo')->count();
   }

   public function getBalance(): string
   {
       $ingresos = Ingreso::where('estado', 'activo')->sum('monto');
       $gastos = Gasto::where('estado', 'activo')->sum('monto');
       return number_format($ingresos - $gastos, 2, ',', '.');
   }
}