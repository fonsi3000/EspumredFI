<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use BezhanSalleh\PanelSwitch\PanelSwitch;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $configPath = __DIR__ . '/../config/loans.php';

        if (file_exists($configPath)) {
            $this->mergeConfigFrom(
                $configPath,
                'loans'
            );
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configuración de PanelSwitch
        PanelSwitch::configureUsing(function (PanelSwitch $panelSwitch) {
            $panelSwitch
                ->simple()
                ->labels([
                    'inicio' => 'Espumas medellin',
                    'litoral' => 'Espumasdos del Litoral'
                ])
                ->visible(fn(): bool => auth()->user()?->hasAnyRole([
                    'gerencia',
                    'super_admin',
                ]));
        });

        // Publicar configuración de préstamos
        $this->publishes([
            __DIR__ . '/../config/loans.php' => config_path('loans.php'),
        ], 'loans-config');
    }
}
