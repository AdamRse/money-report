<?php
// routes/web.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\IncomeImportController;
use App\Http\Controllers\IncomeReportController;
use App\Http\Controllers\incomeTypesController;
use Illuminate\Support\Facades\Route;

// Utilisateur non authentifié uniquement
Route::middleware(['guest'])->group(function () {
    Route::controller(AuthController::class)->group(function () {
        // Connexion
        Route::get('/login', 'showLoginForm')->name('login');
        Route::post('/login', 'login')->name('login.request');

        //Inscription
        Route::get('/register', 'showRegistrationForm')->name('register');
        Route::post('/register', 'register')->name('register.request');
    });
});

// Utilisateur authentifié uniquement
Route::middleware(['auth'])->group(function () {
    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Gestion des revenus (income)
    Route::get('/incomes', [IncomeController::class, 'index'])->name('incomes.index');
    Route::post('/incomes', [IncomeController::class, 'store'])->name('incomes.store');
    Route::put('/incomes/{id}', [IncomeController::class, 'update'])->name('incomes.update');
    Route::delete('/incomes/{id}', [IncomeController::class, 'destroy'])->name('incomes.destroy');

    // Gestion des types de revenus
    Route::get('/income-types', [incomeTypesController::class, 'index'])->name('income-types.index');
    Route::post('/income-types', [incomeTypesController::class, 'store'])->name('income-types.store');
    Route::put('/income-types/{id}', [incomeTypesController::class, 'update'])->name('income-types.update');
    Route::delete('/income-types/{id}', [incomeTypesController::class, 'destroy'])->name('income-types.destroy');

    // Import des revenus
    Route::match(['get', 'post'], '/incomes/import', [IncomeImportController::class, 'showForm'])->name('incomes.import');
    Route::post('/incomes/import/process', [IncomeImportController::class, 'import'])->name('incomes.import.process');

    // Rapports et statistiques
    Route::get('/', [IncomeReportController::class, 'index'])->name('dashboard');
    Route::get('/incomes/report', [IncomeReportController::class, 'index'])->name('incomes.report');
});
