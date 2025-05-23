<?php
// routes/web.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\IncomeImportController;
use App\Http\Controllers\IncomeReportController;
use App\Http\Controllers\IncomeTypeController;
use Illuminate\Support\Facades\Route;

// Utilisateur non authentifié uniquement
Route::middleware(['guest'])->group(function () {
    // Connexion
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.request');

    //Inscription
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.request');
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
    Route::get('/income-types', [IncomeTypeController::class, 'index'])->name('income-types.index');
    Route::post('/income-types', [IncomeTypeController::class, 'store'])->name('income-types.store');
    Route::put('/income-types/{id}', [IncomeTypeController::class, 'update'])->name('income-types.update');
    Route::delete('/income-types/{id}', [IncomeTypeController::class, 'destroy'])->name('income-types.destroy');

    // Import des revenus
    Route::get('/incomes/import', [IncomeImportController::class, 'showForm'])->name('incomes.import');
    Route::post('/incomes/import', [IncomeImportController::class, 'processFile'])->name('incomes.import.parse');
    Route::post('/incomes/import/process', [IncomeImportController::class, 'import'])->name('incomes.import.process');
    //Route::match(['get', 'post'], '/incomes/import', [IncomeImportController::class, 'showForm'])->name('incomes.import');

    // Rapports et statistiques
    Route::get('/', [IncomeReportController::class, 'index'])->name('dashboard');
    Route::get('/incomes/report', [IncomeReportController::class, 'index'])->name('incomes.report');
});
