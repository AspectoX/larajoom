<?php

use Illuminate\Support\Facades\Route;
use Aspectox\LaraJoom\Admin\Components\com_templates\Controllers\TemplateController;

Route::prefix('templates')->middleware(['larajoom.auth', 'larajoom.acl'])->group(function () {
    Route::get('/', [TemplateController::class, 'index'])->name('larajoom.admin.templates.index');

    Route::get('/create/{template}', [TemplateController::class, 'create'])->name('larajoom.admin.templates.create');
    Route::post('/', [TemplateController::class, 'store'])->name('larajoom.admin.templates.store');

    Route::get('/{id}/edit', [TemplateController::class, 'edit'])->name('larajoom.admin.templates.edit');
    Route::put('/{id}', [TemplateController::class, 'update'])->name('larajoom.admin.templates.update');

    Route::post('/{id}/home', [TemplateController::class, 'setHome'])->name('larajoom.admin.templates.home');
    Route::delete('/{id}', [TemplateController::class, 'destroy'])->name('larajoom.admin.templates.destroy');

    Route::get('/{id}/files', [TemplateController::class, 'files'])->name('larajoom.admin.templates.files');
    Route::get('/{id}/file', [TemplateController::class, 'file'])->name('larajoom.admin.templates.file');

    Route::get('/{template}/newfile', [TemplateController::class, 'newfile'])->name('larajoom.admin.templates.newfile');
    Route::post('/{id}/savefile', [TemplateController::class, 'savefile'])->name('larajoom.admin.templates.savefile');
    Route::get('/{template}/preview', [TemplateController::class, 'preview'])->name('larajoom.admin.templates.preview');
});
