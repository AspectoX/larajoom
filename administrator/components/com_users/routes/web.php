<?php

use Illuminate\Support\Facades\Route;
use Aspectox\LaraJoom\Admin\Components\com_users\Controllers\UserAdminController;

Route::prefix('users')->middleware(['larajoom.auth', 'larajoom.acl'])->group(function () {
    Route::get('/', [UserAdminController::class, 'index'])->name('larajoom.admin.users.index');
    Route::get('/groups', [UserAdminController::class, 'groups'])->name('larajoom.admin.groups.index');
    Route::post('/groups', [UserAdminController::class, 'storeGroup'])->name('larajoom.admin.groups.store');
    Route::get('/levels', [UserAdminController::class, 'levels'])->name('larajoom.admin.levels.index');
    Route::post('/levels', [UserAdminController::class, 'storeLevel'])->name('larajoom.admin.levels.store');
});
