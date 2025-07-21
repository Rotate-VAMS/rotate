<?php

use Illuminate\Support\Facades\Route;
use Modules\Routes\Http\Controllers\RoutesController;

Route::middleware(['auth', 'verified', 'pilot.active'])->group(function () {
    Route::group(['prefix' => 'routes', 'controller' => RoutesController::class], function () {
        Route::get('/', 'index')->name('routes.index');
        Route::post('/jxFetchRoutes', 'jxFetchRoutes')->name('routes.jxFetchRoutes');
        Route::post('/jxCreateEditRoutes', 'jxCreateEditRoutes')->name('routes.jxCreateEditRoutes');
        Route::post('/jxDeleteRoutes', 'jxDeleteRoutes')->name('routes.jxDeleteRoutes');
        Route::get('/jxGetRouteCustomFields', 'jxGetRouteCustomFields')->name('routes.jxGetRouteCustomFields');
    });
});
