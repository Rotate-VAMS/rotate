<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\UsersController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::group(['prefix' => 'pilots', 'controller' => UsersController::class], function () {
        Route::get('/', 'index')->name('pilots.index');
        Route::post('/jxFetchPilots', 'jxFetchPilots')->name('pilots.jxFetchPilots');
    });
});
