<?php

use Illuminate\Support\Facades\Route;
use Modules\Events\Http\Controllers\EventsController;

Route::middleware(['auth', 'verified', 'pilot.active'])->group(function () {
    Route::group(['prefix' => 'events', 'controller' => EventsController::class], function () {
        Route::get('/', 'index')->name('events.index');
        Route::get('/jxFetchEvents', 'jxFetchEvents')->name('events.jxFetchEvents');
        Route::post('/jxCreateEditEvent', 'jxCreateEditEvent')->name('events.jxCreateEditEvent');
        Route::post('/jxDeleteEvent', 'jxDeleteEvent')->name('events.jxDeleteEvent');
        Route::post('/jxRegisterForEvent', 'jxRegisterForEvent')->name('events.jxRegisterForEvent');
        Route::post('/jxDeRegisterForEvent', 'jxDeRegisterForEvent')->name('events.jxDeRegisterForEvent');
        Route::get('/jxFetchCustomFields', 'jxFetchCustomFields')->name('events.jxFetchCustomFields');
    });
});