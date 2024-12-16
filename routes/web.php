<?php

use App\Http\Controllers\SingletonController;
use Illuminate\Support\Facades\Route;

Route::controller(SingletonController::class)->group(function () {
    Route::get('/', 'index')->name('accueil');
    Route::post('/revenu', 'store')->name('revenu.store');
});
