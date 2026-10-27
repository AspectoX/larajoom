<?php

use Illuminate\Support\Facades\Route;
use Aspectox\LaraJoom\Site\Components\com_users\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('larajoom.login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('larajoom.login.post');

Route::get('/register', [AuthController::class, 'showRegister'])->name('larajoom.register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1')->name('larajoom.register.post');

Route::get('/two-factor', [AuthController::class, 'showTwoFactor'])->name('larajoom.twofactor');
Route::post('/two-factor', [AuthController::class, 'twoFactor'])->middleware('throttle:5,1')->name('larajoom.twofactor.post');

Route::post('/logout', [AuthController::class, 'logout'])->name('larajoom.logout');
