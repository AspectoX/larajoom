<?php

use Illuminate\Support\Facades\Route;
use Aspectox\LaraJoom\Admin\Components\com_modules\Controllers\ModuleController;

Route::prefix('modules')->middleware(['larajoom.auth', 'larajoom.acl'])->group(function () {
    Route::get('/', [ModuleController::class, 'index'])->name('larajoom.admin.modules.index');
    Route::get('/create', [ModuleController::class, 'create'])->name('larajoom.admin.modules.create');
    Route::post('/', [ModuleController::class, 'store'])->name('larajoom.admin.modules.store');
    Route::post('/reorder', [ModuleController::class, 'reorder'])->name('larajoom.admin.modules.reorder');
    Route::get('/{id}/edit', [ModuleController::class, 'edit'])->name('larajoom.admin.modules.edit');
    Route::put('/{id}', [ModuleController::class, 'update'])->name('larajoom.admin.modules.update');
    Route::delete('/{id}', [ModuleController::class, 'destroy'])->name('larajoom.admin.modules.destroy');
});
