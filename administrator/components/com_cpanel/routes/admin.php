<?php

use Illuminate\Support\Facades\Route;
use Aspectox\LaraJoom\Admin\Components\com_cpanel\Controllers\CpanelController;

Route::prefix('cpanel')->middleware(['larajoom.auth', 'larajoom.acl'])->group(function () {
    Route::get('/', [CpanelController::class, 'index'])->name('larajoom.admin.cpanel.index');
});
