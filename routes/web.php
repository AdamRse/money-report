<?php

use App\Http\Controllers\SingletonController;
use Illuminate\Support\Facades\Route;

Route::controller(SingletonController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/page2', 'page2');
    Route::get('/page3', 'page3');
});
