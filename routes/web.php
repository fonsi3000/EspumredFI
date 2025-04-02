<?php

use App\Http\Controllers\LoanRequestController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Ruta principal: Muestra el login de inicio
Route::get('/', function () {
    return redirect()->route('filament.inicio.auth.login'); // Redirige al login de inicio
});

// Rutas públicas para solicitudes de préstamo (unificadas)
Route::get('/solicitar-prestamo', [LoanRequestController::class, 'showForm'])
    ->name('public.loan-request.form');

Route::post('/solicitar-prestamo', [LoanRequestController::class, 'store'])
    ->name('public.loan-request.store');

Route::get('/solicitud-prestamo-exitosa', [LoanRequestController::class, 'success'])
    ->name('public.loan-request.success');

// Ruta común para redirección después del login
Route::get('/redirect-after-login', function () {
    // Verifica si el usuario está autenticado
    if (Auth::check()) {
        $empresa = Auth::user()->empresa;

        // Redirige según el valor de 'empresa'
        if ($empresa === 'Espumas Medellin') {
            return redirect()->route('filament.inicio.pages.dashboard'); // Redirige al panel de inicio
        } elseif ($empresa === 'Espumados del Litoral') {
            return redirect()->route('filament.litoral.pages.dashboard'); // Redirige al panel de litoral
        }
    }

    // Si no está autenticado o no tiene una empresa definida, redirige al login de inicio
    return redirect()->route('filament.inicio.auth.login');
})->name('redirect.after.login');
