<?php

use Illuminate\Support\Facades\Route;
use Aspectox\LaraJoom\Admin\Components\com_users\Controllers\UserAdminController;

Route::prefix('users')->middleware(['larajoom.auth', 'larajoom.acl'])->group(function () {
    // Usuarios
    Route::get('/', [UserAdminController::class, 'index'])->name('larajoom.admin.users.index');
    Route::get('/create', [UserAdminController::class, 'create'])->name('larajoom.admin.users.create');
    Route::post('/', [UserAdminController::class, 'store'])->name('larajoom.admin.users.store');

    // Perfil del usuario conectado
    Route::get('/profile', [UserAdminController::class, 'profile'])->name('larajoom.admin.profile');
    Route::put('/profile', [UserAdminController::class, 'updateProfile'])->name('larajoom.admin.profile.update');

    Route::get('/{id}/edit', [UserAdminController::class, 'edit'])->name('larajoom.admin.users.edit');
    Route::put('/{id}', [UserAdminController::class, 'update'])->name('larajoom.admin.users.update');
    Route::delete('/{id}', [UserAdminController::class, 'destroy'])->name('larajoom.admin.users.destroy');
    Route::post('/{id}/toggle', [UserAdminController::class, 'toggle'])->name('larajoom.admin.users.toggle');
    Route::post('/{id}/groups', [UserAdminController::class, 'updateGroups'])->name('larajoom.admin.users.updateGroups');

    // Grupos de usuarios
    Route::get('/groups', [UserAdminController::class, 'groups'])->name('larajoom.admin.groups.index');
    Route::get('/groups/{id}/edit', [UserAdminController::class, 'editGroup'])->name('larajoom.admin.groups.edit');
    Route::post('/groups', [UserAdminController::class, 'storeGroup'])->name('larajoom.admin.groups.store');
    Route::put('/groups/{id}', [UserAdminController::class, 'updateGroup'])->name('larajoom.admin.groups.update');
    Route::delete('/groups/{id}', [UserAdminController::class, 'destroyGroup'])->name('larajoom.admin.groups.destroy');

    // Niveles de acceso
    Route::get('/levels', [UserAdminController::class, 'levels'])->name('larajoom.admin.levels.index');
    Route::get('/levels/{id}/edit', [UserAdminController::class, 'editLevel'])->name('larajoom.admin.levels.edit');
    Route::post('/levels', [UserAdminController::class, 'storeLevel'])->name('larajoom.admin.levels.store');
    Route::put('/levels/{id}', [UserAdminController::class, 'updateLevel'])->name('larajoom.admin.levels.update');
    Route::delete('/levels/{id}', [UserAdminController::class, 'destroyLevel'])->name('larajoom.admin.levels.destroy');
});
