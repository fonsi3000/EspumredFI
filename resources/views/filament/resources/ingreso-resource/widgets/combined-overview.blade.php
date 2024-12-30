<x-filament-widgets::widget>
   <x-filament::section>
       <div class="flex gap-4">
           {{-- Ingresos --}}
           <div class="flex-1 rounded-xl bg-indigo-50 shadow">
               <div class="p-6">
                   <h3 class="text-base font-medium text-gray-500">Total Ingresos</h3>
                   <div class="mt-2">
                       <div class="text-3xl font-bold text-gray-900">
                           $ {{ $this->getTotalIngresos() }}
                       </div>
                   </div>
                   <div class="mt-4 flex items-center text-success-600">
                       <span class="text-sm font-medium">
                           {{ $this->getCountIngresos() }} registros activos
                       </span>
                       <x-heroicon-m-arrow-trending-up class="ml-1 h-4 w-4"/>
                   </div>
                   <div class="mt-3">
                       <div class="h-1 w-full bg-success-500 rounded"></div>
                   </div>
               </div>
           </div>

           {{-- Balance/Saldo en Caja --}}
           <div class="flex-1 rounded-xl bg-emerald-50 shadow">
               <div class="p-6">
                   <h3 class="text-base font-medium text-gray-500">Saldo en Caja</h3>
                   <div class="mt-2">
                       <div class="text-3xl font-bold text-gray-900">
                           $ {{ $this->getBalance() }}
                       </div>
                   </div>
                   <div class="mt-4 flex items-center text-primary-600">
                       <span class="text-sm font-medium">
                           Balance Actual
                       </span>
                       <x-heroicon-m-currency-dollar class="ml-1 h-4 w-4"/>
                   </div>
                   <div class="mt-3">
                       <div class="h-1 w-full bg-primary-500 rounded"></div>
                   </div>
               </div>
           </div>

           {{-- Gastos --}}
           <div class="flex-1 rounded-xl bg-rose-50 shadow">
               <div class="p-6">
                   <h3 class="text-base font-medium text-gray-500">Total Gastos</h3>
                   <div class="mt-2">
                       <div class="text-3xl font-bold text-gray-900">
                           $ {{ $this->getTotalGastos() }}
                       </div>
                   </div>
                   <div class="mt-4 flex items-center text-danger-600">
                       <span class="text-sm font-medium">
                           {{ $this->getCountGastos() }} registros activos
                       </span>
                       <x-heroicon-m-arrow-trending-down class="ml-1 h-4 w-4"/>
                   </div>
                   <div class="mt-3">
                       <div class="h-1 w-full bg-danger-500 rounded"></div>
                   </div>
               </div>
           </div>
       </div>
   </x-filament::section>
</x-filament-widgets::widget>