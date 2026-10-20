<?php

use Illuminate\Support\Facades\Route;
use Aspectox\LaraJoom\Site\Components\com_users\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('larajoom.login');
Route::post('/login', [AuthController::class, 'login'])->name('larajoom.login.post');

Route::get('/register', [AuthController::class, 'showRegister'])->name('larajoom.register');
Route::post('/register', [AuthController::class, 'register'])->name('larajoom.register.post');

Route::post('/logout', [AuthController::class, 'logout'])->name('larajoom.logout');
