<?php

use Illuminate\Support\Facades\Route;
use Aspectox\LaraJoom\Admin\Components\com_admin\Controllers\AdminController;

// El prefijo "administrator" y el middleware los añade el ExtensionLoader
Route::get('/', [AdminController::class, 'index'])->name('larajoom.admin.index');
// Route::get('/dashboard', [AdminController::class, 'index'])->name('larajoom.admin.dashboard');
