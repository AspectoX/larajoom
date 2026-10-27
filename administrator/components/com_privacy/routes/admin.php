<?php

use Illuminate\Support\Facades\Route;
use Aspectox\LaraJoom\Admin\Components\com_privacy\Controllers\PrivacyController;

Route::prefix('privacy')->middleware(['larajoom.auth', 'larajoom.acl'])->group(function () {
    Route::get('/', [PrivacyController::class, 'index'])->name('larajoom.admin.privacy.index');
    Route::get('/requests', [PrivacyController::class, 'requests'])->name('larajoom.admin.privacy.requests');
    Route::get('/consents', [PrivacyController::class, 'consents'])->name('larajoom.admin.privacy.consents');

    Route::get('/requests/{id}/export', [PrivacyController::class, 'export'])->name('larajoom.admin.privacy.export');
    Route::post('/requests/{id}/complete', [PrivacyController::class, 'complete'])->name('larajoom.admin.privacy.complete');
    Route::delete('/requests/{id}', [PrivacyController::class, 'destroy'])->name('larajoom.admin.privacy.destroy');
});
