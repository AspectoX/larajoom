<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Aspectox\LaraJoom\Admin\Components\com_login\Controllers\LoginController;
use Aspectox\LaraJoom\Admin\Components\com_admin\Controllers\AdminController;

Route::get('/', function () {
    if (!Auth::guard('ax-larajoom')->check()) {
        // Guarda la URL como "intended": tras loguearse se vuelve a la última página
        return redirect()->guest(route('login'));
    }
    return app(AdminController::class)->index(request());
});
Route::post('/', [LoginController::class, 'store'])->name('larajoom.admin.login.store');

Route::get('login', function () {
    if (Auth::guard('ax-larajoom')->check()) {
        return redirect('/administrator');
    }
    return app(LoginController::class)->index();
})->name('login');
Route::post('login', [LoginController::class, 'store']);
Route::post('logout', [LoginController::class, 'logout'])->name('administrator.logout');

