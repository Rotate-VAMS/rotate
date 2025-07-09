<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\CustomLogoutController;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('/login', function () {
    return Inertia::render('Users/Pages/Login');
})->middleware('guest')->name('login');

Route::get('/register', function () {
    return Inertia::render('Users/Pages/Register');
})->middleware('guest')->name('register');

Route::post('/logout', [CustomLogoutController::class, 'destroy'])->name('logout');
