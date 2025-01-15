<x-filament-widgets::widget>
    <x-filament::section>
        <!-- Filtro de fechas -->
        <x-filament::card class="bg-white p-4 sm:p-6 border border-gray-200 rounded-lg shadow-md max-w-4xl mx-auto">
            <form wire:submit.prevent="filterResults" class="flex flex-col sm:flex-row items-start sm:items-end justify-end gap-4 sm:gap-6">
                <div class="flex flex-col sm:flex-row items-center justify-end gap-4 sm:gap-6">
                    <!-- Rango de fechas -->
                    <div class="sm:w-auto flex flex-col items-center gap-2">
                        <span class="text-sm font-bold" style="color: #fe890b;">Rango de fechas</span>
                        <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 w-full sm:w-auto">
                            <x-filament::input
                                wire:model="startDate"
                                id="startDate"
                                type="date"
                                class="sm:w-36 p-2 rounded-md font-bold"
                                style="background-color: #fe890b87; border: none; outline: none;"
                            />
                            <x-filament::input
                                wire:model="endDate"
                                id="endDate"
                                type="date"
                                class="sm:w-36 p-2 rounded-md font-bold"
                                style="background-color: #fe890b87; border: none; outline: none;"
                            />
                        </div>
                    </div>
                    
                    <!-- Botón de filtrar -->
                    <x-filament::button
                        type="submit"
                        class="px-6 py-2 text-white rounded-lg cursor-pointer text-base font-bold whitespace-nowrap"
                        style="background-color: #fe890b; margin-top: 30px; padding:8px; margin-left: 15px;"
                    >
                        Filtrar
                    </x-filament::button>
                </div>
            </form>
        </x-filament::card>

        <!-- Primera fila de cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-4 mt-4">
            {{-- Ingresos --}}
            <div class="rounded-xl bg-indigo-50 shadow">
                <div class="p-4">
                    <h3 class="text-sm font-medium text-gray-500">Total Ingresos</h3>
                    <div class="mt-1">
                        <div class="text-2xl font-bold text-gray-900">
                            $ {{ $data['totalIngresos'] }}
                        </div>
                    </div>
                    <div class="mt-2 flex items-center text-success-600">
                        <span class="text-xs font-medium">
                            {{ $data['countIngresos'] }} registros activos
                        </span>
                        <x-heroicon-m-arrow-trending-up class="ml-1 h-3 w-3"/>
                    </div>
                    <div class="mt-2">
                        <div class="h-1 w-full bg-success-500 rounded"></div>
                    </div>
                </div>
            </div>

            {{-- Balance/Saldo en Caja --}}
            <div class="rounded-xl bg-emerald-50 shadow">
                <div class="p-4">
                    <h3 class="text-sm font-medium text-gray-500">Saldo en Caja</h3>
                    <div class="mt-1">
                        <div class="text-2xl font-bold text-gray-900">
                            $ {{ $data['balance'] }}
                        </div>
                    </div>
                    <div class="mt-2 flex items-center text-primary-600">
                        <span class="text-xs font-medium">
                            Balance del Período
                        </span>
                        <x-heroicon-m-currency-dollar class="ml-1 h-3 w-3"/>
                    </div>
                    <div class="mt-2">
                        <div class="h-1 w-full bg-primary-500 rounded"></div>
                    </div>
                </div>
            </div>

            {{-- Gastos --}}
            <div class="rounded-xl bg-rose-50 shadow">
                <div class="p-4">
                    <h3 class="text-sm font-medium text-gray-500">Total Gastos</h3>
                    <div class="mt-1">
                        <div class="text-2xl font-bold text-gray-900">
                            $ {{ $data['totalGastos'] }}
                        </div>
                    </div>
                    <div class="mt-2 flex items-center text-danger-600">
                        <span class="text-xs font-medium">
                            {{ $data['countGastos'] }} registros activos
                        </span>
                        <x-heroicon-m-arrow-trending-down class="ml-1 h-3 w-3"/>
                    </div>
                    <div class="mt-2">
                        <div class="h-1 w-full bg-danger-500 rounded"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Segunda fila de cards (Préstamos) -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-4 mt-4">
            {{-- Préstamos Activos --}}
            <div class="rounded-xl bg-purple-50 shadow">
                <div class="p-4">
                    <h3 class="text-sm font-medium text-gray-500">Préstamos Activos</h3>
                    <div class="mt-1">
                        <div class="text-2xl font-bold text-gray-900">
                            $ {{ $data['activeLoansTotal'] }}
                        </div>
                    </div>
                    <div class="mt-2 flex items-center text-purple-600">
                        <span class="text-xs font-medium">
                            {{ $data['activeLoansCount'] }} préstamos vigentes
                        </span>
                        <x-heroicon-m-banknotes class="ml-1 h-3 w-3"/>
                    </div>
                    <div class="mt-2">
                        <div class="h-1 w-full bg-purple-500 rounded"></div>
                    </div>
                </div>
            </div>

            {{-- Saldo en Mora --}}
            <div class="rounded-xl bg-red-50 shadow">
                <div class="p-4">
                    <h3 class="text-sm font-medium text-gray-500">Saldo en Mora</h3>
                    <div class="mt-1">
                        <div class="text-2xl font-bold text-red-600">
                            $ {{ $data['latePaymentsBalance'] }}
                        </div>
                    </div>
                    <div class="mt-2 flex items-center text-red-600">
                        <span class="text-xs font-medium">
                            Pagos atrasados
                        </span>
                        <x-heroicon-m-clock class="ml-1 h-3 w-3"/>
                    </div>
                    <div class="mt-2">
                        <div class="h-1 w-full bg-red-500 rounded"></div>
                    </div>
                </div>
            </div>

            {{-- Ingresos por Préstamos --}}
            <div class="rounded-xl bg-green-50 shadow">
                <div class="p-4">
                    <h3 class="text-sm font-medium text-gray-500">Ingresos por Préstamos</h3>
                    <div class="mt-1">
                        <div class="text-2xl font-bold text-gray-900">
                            $ {{ $data['loanPaymentsIncome'] }}
                        </div>
                    </div>
                    <div class="mt-2 flex items-center text-green-600">
                        <span class="text-xs font-medium">
                            Pagos recibidos
                        </span>
                        <x-heroicon-m-arrow-trending-up class="ml-1 h-3 w-3"/>
                    </div>
                    <div class="mt-2">
                        <div class="h-1 w-full bg-green-500 rounded"></div>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>