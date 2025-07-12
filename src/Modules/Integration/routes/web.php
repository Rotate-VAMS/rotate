<?php

use Illuminate\Support\Facades\Route;
use Modules\Integration\Http\Controllers\IntegrationController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::group(['prefix' => 'settings', 'controller' => IntegrationController::class], function () {
        Route::get('/', 'index')->name('integrations.settings');
        Route::post('jxCreateEditCustomFields', 'jxCreateEditCustomFields')->name('integrations.settings.jxCreateEditCustomFields');
        Route::get('jxFetchCustomFields', 'jxFetchCustomFields')->name('integrations.settings.jxFetchCustomFields');
        Route::post('jxDeleteCustomField', 'jxDeleteCustomField')->name('integrations.settings.jxDeleteCustomField');
    });
});
