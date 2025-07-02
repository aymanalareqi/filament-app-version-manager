<?php

use Illuminate\Support\Facades\Route;
use Alareqi\FilamentAppVersionManager\Http\Controllers\Api\VersionController;
use Alareqi\FilamentAppVersionManager\Facades\FilamentAppVersionManager;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for the Filament App Version
| Manager plugin. These routes are loaded by the service provider within
| a group which is assigned the "api" middleware group.
|
*/

// Only register routes if API is enabled
if (FilamentAppVersionManager::isApiEnabled()) {
    $prefix = config('filament-app-version-manager.api.prefix', 'api/version');
    $middleware = config('filament-app-version-manager.api.middleware', ['throttle:60,1']);

    Route::prefix($prefix)
        ->middleware($middleware)
        ->group(function () {
            // Version check endpoint
            Route::post('/check', [VersionController::class, 'check'])
                ->name('filament-app-version-manager.api.check');

            // Optional stats endpoint (can be disabled via configuration)
            if (config('filament-app-version-manager.api.enable_stats', false)) {
                Route::get('/stats', [VersionController::class, 'stats'])
                    ->name('filament-app-version-manager.api.stats');
            }
        });
}
