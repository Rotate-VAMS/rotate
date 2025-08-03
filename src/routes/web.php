<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\CustomLogoutController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard.index');
    }
    return redirect()->route('login');
})->name('home');

Route::post('/logout', [CustomLogoutController::class, 'destroy'])->name('logout');

// CSRF token refresh route
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->middleware('web');