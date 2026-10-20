<?php

use Illuminate\Support\Facades\Route;
use Aspectox\LaraJoom\Admin\Components\com_actionlogs\Controllers\ActionlogsController;

Route::prefix('actionlogs')->middleware(['larajoom.auth', 'larajoom.acl'])->group(function () {
    Route::get('/', [ActionlogsController::class, 'index'])->name('larajoom.admin.actionlogs.index');
    Route::get('/export/{delimiter?}', [ActionlogsController::class, 'exportCsv'])->name('larajoom.admin.actionlogs.export');
});
