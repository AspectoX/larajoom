<?php
namespace Aspectox\LaraJoom\Admin\Components\com_login\Controllers;

use Illuminate\Support\Facades\Auth;

class LoginController
{
    public function index()
    {
        return view('admin.components.com_login::login.login', ['displayData' => []]);
    }

    public function store()
    {
        $login = request('username') ?? request('email');
        $credentials = [
            (filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username') => $login,
            'password' => request('passwd'),
        ];

        if (Auth::guard('ax-larajoom')->attempt($credentials, request()->boolean('remember', false))) {
            request()->session()->regenerate();
        return redirect('/administrator');
        }

        return redirect()->back()->withErrors(['login' => 'Credenciales incorrectas']);
    }

    public function logout()
    {
        Auth::guard('ax-larajoom')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/administrator');
    }
}