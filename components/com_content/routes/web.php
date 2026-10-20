<?php

use Illuminate\Support\Facades\Route;
use Aspectox\LaraJoom\Site\Components\com_content\Controllers\ArticleController;

Route::get('/', [ArticleController::class, 'index'])->name('larajoom.content.index');
Route::get('/article/{alias}', [ArticleController::class, 'show'])->name('larajoom.article.show');
Route::get('/category/{alias}', [ArticleController::class, 'category'])->name('larajoom.category.show');
