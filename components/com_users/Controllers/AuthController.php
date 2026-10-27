<?php

namespace Aspectox\LaraJoom\Site\Components\com_users\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Aspectox\LaraJoom\Admin\Components\com_users\Models\User;
use Aspectox\LaraJoom\Core\Authentication\AuthenticationService;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('components.com_users::login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!$this->verifyCaptcha($request)) {
            return back()->withErrors(['captcha' => 'Verificación de captcha fallida'])->onlyInput('email');
        }

        $auth = app(AuthenticationService::class);
        $result = $auth->authenticate($credentials);

        // Fallback: autenticación directa contra el guard (sin plugins habilitados).
        if ($result['status'] !== 'success') {
            if (Auth::guard('ax-larajoom')->attempt($credentials, $request->boolean('remember'))) {
                $result = ['status' => 'success', 'user' => Auth::guard('ax-larajoom')->user()];
            }
        }

        if ($result['status'] === 'success' && $result['user'] instanceof User) {
            $user = $result['user'];

            // Segundo factor pendiente: requerir verificación antes de entrar.
            if ($auth->requiresMfa($user)) {
                session([
                    'mfa.pending.user_id'  => $user->getKey(),
                    'mfa.pending.remember' => $request->boolean('remember'),
                ]);
                return redirect()->route('larajoom.twofactor');
            }

            $auth->completeLogin($user, $request->boolean('remember'));
            return redirect()->intended('/');
        }

        return back()->withErrors(['email' => 'Credenciales inválidas'])->onlyInput('email');
    }

    public function showTwoFactor()
    {
        if (!session('mfa.pending.user_id')) {
            return redirect()->route('larajoom.login');
        }

        return view('components.com_users::twofactor');
    }

    public function twoFactor(Request $request)
    {
        $userId = session('mfa.pending.user_id');
        if (!$userId) {
            return redirect()->route('larajoom.login');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('larajoom.login');
        }

        $code = $request->validate([
            'code' => 'required|string|max:64',
        ])['code'];

        $auth = app(AuthenticationService::class);

        if ($auth->verifyMfa($user, $code)) {
            $remember = (bool) session('mfa.pending.remember', false);
            session()->forget(['mfa.pending.user_id', 'mfa.pending.remember']);

            $auth->completeLogin($user, $remember);
            return redirect()->intended('/');
        }

        return back()->withErrors(['code' => 'Código de verificación inválido']);
    }

    public function showRegister()
    {
        return view('components.com_users::register');
    }

    public function register(Request $request)
    {
        if (!$this->verifyCaptcha($request)) {
            return back()->withErrors(['captcha' => 'Verificación de captcha fallida'])->withInput();
        }

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:150|alpha_dash|unique:larajoom_users,username',
            'email'    => 'required|string|email|max:255|unique:larajoom_users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'         => $data['name'],
            'username'     => $data['username'],
            'email'        => $data['email'],
            'password'     => Hash::make($data['password']),
            'group_id'     => 3, // Registered
            'activated'    => 1,
            'params'       => json_encode([]),
            'registerDate' => now(),
        ]);

        DB::table('larajoom_user_usergroup_map')->insert([
            'user_id'  => $user->id,
            'group_id' => 3, // Registered
        ]);

        // Consentimiento de privacidad por defecto (plugin privacy/consent).
        $consent = app(\Aspectox\LaraJoom\Core\Plugins\PluginManager::class)
            ->resolve('privacy', 'consent');
        if ($consent && method_exists($consent, 'record')) {
            $consent->record($user->id, ['source' => 'register', 'version' => '1.0']);
        }

        Auth::guard('ax-larajoom')->login($user);
        session()->regenerate();

        return redirect('/');
    }

    public function logout(Request $request)
    {
        $user = Auth::guard('ax-larajoom')->user();
        $userId = $user?->getKey();

        app(AuthenticationService::class)->logout($userId);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Valida el captcha configurado (plugins del grupo captcha).
     * Si no hay captcha configurado o plugin activo, se permite el paso.
     */
    protected function verifyCaptcha(Request $request): bool
    {
        $type = config('larajoom.captcha', '0');

        if ($type === '0' || $type === '' || $type === null) {
            return true;
        }

        $plugin = app(\Aspectox\LaraJoom\Core\Plugins\PluginManager::class)
            ->resolve('captcha', $type);

        if (!$plugin || !method_exists($plugin, 'onCheckAnswer')) {
            return true;
        }

        return $plugin->onCheckAnswer($request->input('g-recaptcha-response'));
    }
}