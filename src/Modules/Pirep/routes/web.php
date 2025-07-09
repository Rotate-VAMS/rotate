<?php

use Illuminate\Support\Facades\Route;
use Modules\Pirep\Http\Controllers\PirepController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::group(['prefix' => 'pireps', 'controller' => PirepController::class], function () {
        Route::get('/', 'index')->name('pireps.index');
        Route::get('/jxFetchPireps', 'jxFetchPireps')->name('pireps.jxFetchPireps');
    });
});
