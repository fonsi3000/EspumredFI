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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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
    }
}
