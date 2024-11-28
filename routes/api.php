<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifyAuthenticatedSessionController;
use App\Http\Controllers\AuthenticatedSessionController;


Route::middleware(['guest:web'])
    ->group(function () {
        Route::post('auth/login', [FortifyAuthenticatedSessionController::class, 'store'])->name('login.store');
    });

Route::middleware(['web', 'auth:web'])
    ->group(function () {
        Route::get('me', [UserController::class, 'me'])->name('me');

        Route::prefix('auth')->group(function () {
            Route::get('logout', [AuthenticatedSessionController::class, 'logout'])->name('logout.destroy');
        });
    });

