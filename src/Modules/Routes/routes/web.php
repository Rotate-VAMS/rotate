<?php

use Illuminate\Support\Facades\Route;
use Modules\Routes\Http\Controllers\RoutesController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::group(['prefix' => 'routes', 'controller' => RoutesController::class], function () {
        Route::get('/', 'index')->name('routes.index');
        Route::post('/jxFetchRoutes', 'jxFetchRoutes')->name('routes.jxFetchRoutes');
    });
});
