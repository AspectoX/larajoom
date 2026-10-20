<?php
use Illuminate\Support\Facades\Route;
use Aspectox\LaraJoom\Components\com_login\Controllers\LoginController;

Route::get('/', [LoginController::class, 'index'])->name('larajoom.login.index');
Route::post('/', [LoginController::class, 'store'])->name('larajoom.login.store');
Route::get('login', function () { return redirect(route('larajoom.login.index')); })->name('login');
