<?php

use Illuminate\Support\Facades\Route;
use Modules\Integration\Http\Controllers\IntegrationController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::group(['prefix' => 'settings', 'controller' => IntegrationController::class], function () {
        Route::get('/', 'index')->name('integrations.settings');
    });
});
