<?php

use App\Http\Controllers\SocialLoginController;
use Illuminate\Support\Facades\Route;


Route::middleware('guest:web')
    ->group(function () {
        Route::prefix('auth')->group(function () {
            Route::prefix('{provider}')->group(function () {
                Route::get('redirect', [SocialLoginController::class, 'redirectToProvider'])->name('social.redirect');
                Route::get('callback', [SocialLoginController::class, 'callback'])->name('social.callback');
            });
        });
    });

Route::get('login', fn() => 'ciao')->name('login.show');
