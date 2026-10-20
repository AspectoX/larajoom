<?php

use Illuminate\Support\Facades\Route;
use Aspectox\LaraJoom\Admin\Components\com_installer\Controllers\InstallerController;

Route::prefix('installer')->middleware(['larajoom.auth', 'larajoom.acl'])->group(function () {

    // Gestión (le auto-registra lo que ya está en disco)
    Route::get('/', [InstallerController::class, 'index'])->name('larajoom.admin.installer.index');

    // Instalación
    Route::get('/install', [InstallerController::class, 'create'])->name('larajoom.admin.installer.create');
    Route::post('/install', [InstallerController::class, 'store'])->name('larajoom.admin.installer.store');

    // Estado
    Route::post('/{id}/toggle', [InstallerController::class, 'toggle'])->name('larajoom.admin.installer.toggle');

    // Desinstalar
    Route::delete('/{id}', [InstallerController::class, 'destroy'])->name('larajoom.admin.installer.destroy');
});
