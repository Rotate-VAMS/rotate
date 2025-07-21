<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\UsersController;

Route::middleware(['auth', 'verified', 'pilot.active'])->group(function () {
    Route::group(['prefix' => 'pilots', 'controller' => UsersController::class], function () {
        Route::get('/', 'index')->name('pilots.index');
        Route::post('/jxFetchPilots', 'jxFetchPilots')->name('pilots.jxFetchPilots');
        Route::post('/jxCreateEditPilot', 'jxCreateEditPilot')->name('pilots.jxCreateEditPilot');
        Route::post('/jxDeletePilot', 'jxDeletePilot')->name('pilots.jxDeletePilot');
        Route::get('/jxGetUserCustomFields', 'jxGetUserCustomFields')->name('pilots.jxGetUserCustomFields');
        Route::post('/jxTogglePilotStatus', 'jxTogglePilotStatus')->name('pilots.jxTogglePilotStatus');
    });
});
