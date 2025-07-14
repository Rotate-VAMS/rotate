<?php

use Illuminate\Support\Facades\Route;
use Modules\Events\Http\Controllers\EventsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::group(['prefix' => 'events', 'controller' => EventsController::class], function () {
        Route::get('/', 'index')->name('events.index');
        Route::post('/jxFetchEvents', 'jxFetchEvents')->name('events.jxFetchEvents');
        Route::post('/jxCreateEditEvent', 'jxCreateEditEvent')->name('events.jxCreateEditEvent');
        Route::post('/jxDeleteEvent', 'jxDeleteEvent')->name('events.jxDeleteEvent');
    });
});