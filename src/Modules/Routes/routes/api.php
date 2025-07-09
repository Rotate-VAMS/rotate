<?php

use Illuminate\Support\Facades\Route;
use Modules\Routes\Http\Controllers\RoutesController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('routes', RoutesController::class)->names('routes');
});
