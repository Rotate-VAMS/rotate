<?php

use Illuminate\Support\Facades\Route;
use Modules\Pirep\Http\Controllers\PirepController;

Route::middleware(['auth', 'verified', 'pilot.active'])->group(function () {
    Route::group(['prefix' => 'pireps', 'controller' => PirepController::class], function () {
        Route::get('/', 'index')->name('pireps.index');
        Route::get('/jxFetchPireps', 'jxFetchPireps')->name('pireps.jxFetchPireps');
        Route::post('/jxCreateEditPirep', 'jxCreateEditPirep')->name('pireps.jxCreateEditPirep');
        Route::post('/jxDeletePireps', 'jxDeletePireps')->name('pireps.jxDeletePireps');
        Route::get('/jxGetPirepCustomFields', 'jxGetPirepCustomFields')->name('pireps.jxGetPirepCustomFields');
    });
});
