<?php

use Illuminate\Support\Facades\Route;
use Aspectox\LaraJoom\Admin\Components\com_content\Controllers\ArticleAdminController;

Route::prefix('content')->middleware(['larajoom.auth', 'larajoom.acl'])->group(function () {
    Route::get('/articles', [ArticleAdminController::class, 'index'])->name('larajoom.admin.articles.index');
    Route::get('/articles/create', [ArticleAdminController::class, 'create'])->name('larajoom.admin.articles.create');
    Route::post('/articles', [ArticleAdminController::class, 'store'])->name('larajoom.admin.articles.store');
    Route::get('/articles/{id}/edit', [ArticleAdminController::class, 'edit'])->name('larajoom.admin.articles.edit');
    Route::put('/articles/{id}', [ArticleAdminController::class, 'update'])->name('larajoom.admin.articles.update');
    Route::delete('/articles/{id}', [ArticleAdminController::class, 'destroy'])->name('larajoom.admin.articles.destroy');
});
