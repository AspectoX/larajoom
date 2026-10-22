<?php
namespace Aspectox\LaraJoom\Admin\Components\com_login\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        if (!Auth::guard('ax-larajoom')->attempt($credentials, request()->boolean('remember', false))) {
            return redirect()->back()->withErrors(['login' => 'Credenciales incorrectas']);
        }

        $user = Auth::guard('ax-larajoom')->user();

        // Usuario bloqueado (User Status del perfil)
        if ($user && $this->isBlocked($user->getKey())) {
            Auth::guard('ax-larajoom')->logout();

            return redirect()->back()->withErrors(['login' => 'La cuenta está bloqueada.']);
        }

        request()->session()->regenerate();

        // Registra la última visita en el perfil del usuario
        if ($user && Schema::hasTable('larajoom_user_profile')) {
            DB::table('larajoom_user_profile')
                ->where('user_id', $user->getKey())
                ->update(['lastvisitDate' => now(), 'updated_at' => now()]);
        }

        // Vuelve a la última página visitada ("intended" del middleware), o al dashboard si no hay
        return redirect()->intended('/administrator');
    }

    /**
     * ¿El usuario está bloqueado en su perfil?
     */
    protected function isBlocked(int $userId): bool
    {
        if (!Schema::hasTable('larajoom_user_profile')) {
            return false;
        }

        $profile = DB::table('larajoom_user_profile')->where('user_id', $userId)->first();

        return $profile ? (bool) $profile->block : false;
    }

    public function logout()
    {
        Auth::guard('ax-larajoom')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/administrator');
    }
}
