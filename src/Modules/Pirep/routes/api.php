<?php

use Illuminate\Support\Facades\Route;
use Modules\Pirep\Http\Controllers\PirepController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('pireps', PirepController::class)->names('pirep');
});
