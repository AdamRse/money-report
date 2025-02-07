<?php

use App\Http\Controllers\SingletonController;
use Illuminate\Support\Facades\Route;

Route::controller(SingletonController::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'login')->name('login.request');
    Route::get('/register', 'register')->name('register');
    Route::post('/register', 'register')->name('register.request');
});
Route::middleware(['auth'])->group(function () {
    Route::controller(SingletonController::class)->group(function () {
        Route::get('/', 'index')->name('accueil');
        Route::post('/revenu', 'store')->name('revenu.store');
        Route::get('/revenus', 'list')->name('revenus.list');
        Route::post('/logout', 'logout')->name('logout');
    });
});
