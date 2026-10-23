<?php

use Illuminate\Support\Facades\Route;
use Aspectox\LaraJoom\Admin\Components\com_media\Controller\MediaController;

Route::prefix('media')->middleware(['larajoom.auth', 'larajoom.acl'])->group(function () {
    Route::get('/', [MediaController::class, 'index'])->name('larajoom.admin.media.index');
    Route::get('/preview', [MediaController::class, 'preview'])->name('larajoom.admin.media.preview');
    Route::get('/{path?}', [MediaController::class, 'index'])->where('path', '.*')->name('larajoom.admin.media.folder');

    Route::post('/folder', [MediaController::class, 'createFolder'])->name('larajoom.admin.media.folder.create');
    Route::post('/upload', [MediaController::class, 'upload'])->name('larajoom.admin.media.upload');
    Route::delete('/delete', [MediaController::class, 'delete'])->name('larajoom.admin.media.delete');
    Route::post('/delete-bulk', [MediaController::class, 'deleteBulk'])->name('larajoom.admin.media.delete.bulk');
    Route::post('/rename', [MediaController::class, 'rename'])->name('larajoom.admin.media.rename');
});