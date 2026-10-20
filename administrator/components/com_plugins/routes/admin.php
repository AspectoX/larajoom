<?php

use Illuminate\Support\Facades\Route;
use Aspectox\LaraJoom\Admin\Components\com_plugins\Controllers\PluginController;

Route::prefix('plugins')->middleware(['larajoom.auth', 'larajoom.acl'])->group(function () {

    Route::get('/', [PluginController::class, 'index'])->name('larajoom.admin.plugins.index');

    Route::get('/register', [PluginController::class, 'register'])->name('larajoom.admin.plugins.register');

    Route::get('/{id}/edit', [PluginController::class, 'edit'])->name('larajoom.admin.plugins.edit');
    Route::put('/{id}', [PluginController::class, 'update'])->name('larajoom.admin.plugins.update');

    Route::post('/{id}/toggle', [PluginController::class, 'toggle'])->name('larajoom.admin.plugins.toggle');
});
