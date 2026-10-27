<?php

use Illuminate\Support\Facades\Route;
use Aspectox\LaraJoom\Plugins\Content\Vote\Extension\Vote;

Route::post('/vote/{id}', [Vote::class, 'store'])->name('larajoom.vote');
