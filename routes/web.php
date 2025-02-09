<?php
// routes/web.php

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
        Route::get('/', 'list')->name('accueil');
        Route::get('/revenu', 'index')->name('revenu');
        Route::post('/revenu', 'store')->name('revenu.store');
        Route::get('/revenus', 'list')->name('revenus.list');
        Route::post('/logout', 'logout')->name('logout');
        Route::get('/parse', 'parse')->name('parse');
        Route::post('/parse', 'parse')->name('parse.request');
        Route::post('/multipleRevenus', 'multipleRevenus')->name('multipleRevenus');
        Route::get('/type-revenu', 'typeRevenu')->name('typeRevenu');
        Route::post('/type-revenu', 'typeRevenu')->name('typeRevenu.store');
        // Ajout des nouvelles routes pour la modification et suppression
        Route::put('/type-revenu/{id}', 'updateTypeRevenu')->name('typeRevenu.update');
        Route::delete('/type-revenu/{id}', 'deleteTypeRevenu')->name('typeRevenu.destroy');
        // Routes pour les revenus
        Route::get('/revenu', [SingletonController::class, 'index'])->name('revenu');
        Route::post('/revenu', [SingletonController::class, 'store'])->name('revenu.store');
        Route::put('/revenu/{id}', [SingletonController::class, 'update'])->name('revenu.update');
        Route::delete('/revenu/{id}', [SingletonController::class, 'destroy'])->name('revenu.destroy');
    });
});
