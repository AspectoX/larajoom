<?php

use Illuminate\Support\Facades\Route;
use Aspectox\LaraJoom\Admin\Components\com_menus\Controllers\MenuController;
use Aspectox\LaraJoom\Admin\Components\com_menus\Controllers\MenuItemController;

Route::prefix('menus')->middleware(['larajoom.auth', 'larajoom.acl'])->group(function () {

    // Menu items (registradas primero para que "items"
    // no sea capturado por las rutas con {id})
    Route::get('/items', [MenuItemController::class, 'index'])->name('larajoom.admin.menuitems.index');
    Route::get('/items/create', [MenuItemController::class, 'create'])->name('larajoom.admin.menuitems.create');
    Route::post('/items', [MenuItemController::class, 'store'])->name('larajoom.admin.menuitems.store');
    Route::post('/items/reorder', [MenuItemController::class, 'reorder'])->name('larajoom.admin.menuitems.reorder');
    Route::get('/items/{id}/edit', [MenuItemController::class, 'edit'])->name('larajoom.admin.menuitems.edit');
    Route::put('/items/{id}', [MenuItemController::class, 'update'])->name('larajoom.admin.menuitems.update');
    Route::delete('/items/{id}', [MenuItemController::class, 'destroy'])->name('larajoom.admin.menuitems.destroy');

    // Menu types
    Route::get('/', [MenuController::class, 'index'])->name('larajoom.admin.menus.index');
    Route::get('/create', [MenuController::class, 'create'])->name('larajoom.admin.menus.create');
    Route::post('/', [MenuController::class, 'store'])->name('larajoom.admin.menus.store');
    Route::post('/reorder', [MenuController::class, 'reorder'])->name('larajoom.admin.menus.reorder');
    Route::get('/{id}/edit', [MenuController::class, 'edit'])->name('larajoom.admin.menus.edit');
    Route::put('/{id}', [MenuController::class, 'update'])->name('larajoom.admin.menus.update');
    Route::delete('/{id}', [MenuController::class, 'destroy'])->name('larajoom.admin.menus.destroy');
});
