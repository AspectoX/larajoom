<?php

use Illuminate\Support\Facades\Route;
use Aspectox\LaraJoom\Admin\Components\com_config\Controllers\ConfigController;

Route::prefix('config')->middleware(['larajoom.auth', 'larajoom.acl'])->group(function () {
    Route::get('/', [ConfigController::class, 'index'])->name('larajoom.admin.config.index');
    Route::post('/save', [ConfigController::class, 'save'])->name('larajoom.admin.config.save');
    Route::get('/edit/{name}/{group}', [ConfigController::class, 'edit'])->name('larajoom.admin.config.edit');
    Route::put('/update/{name}/{group}', [ConfigController::class, 'update'])->name('larajoom.admin.config.update');
});