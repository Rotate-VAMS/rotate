<?php

use Illuminate\Support\Facades\Route;
use Modules\Integration\Http\Controllers\IntegrationController;
use Modules\Integration\Http\Controllers\RanksController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Custom Fields
    Route::group(['prefix' => 'settings', 'controller' => IntegrationController::class], function () {
        Route::get('/', 'index')->name('integrations.settings');
        Route::post('jxCreateEditCustomFields', 'jxCreateEditCustomFields')->name('integrations.settings.jxCreateEditCustomFields');
        Route::get('jxFetchCustomFields', 'jxFetchCustomFields')->name('integrations.settings.jxFetchCustomFields');
        Route::post('jxDeleteCustomField', 'jxDeleteCustomField')->name('integrations.settings.jxDeleteCustomField');
    });


    // Ranks
    Route::group(['prefix' => 'settings', 'controller' => RanksController::class], function () {
        Route::post('jxCreateEditRank', 'jxCreateEditRank')->name('integrations.settings.jxCreateEditRank');
        Route::get('jxFetchRanks', 'jxFetchRanks')->name('integrations.settings.jxFetchRanks');
        Route::post('jxDeleteRank', 'jxDeleteRank')->name('integrations.settings.jxDeleteRank');
    });
});        