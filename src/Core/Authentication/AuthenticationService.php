<?php

namespace Aspectox\LaraJoom\Core\Authentication;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Aspectox\LaraJoom\Admin\Components\com_users\Models\User;

class AuthenticationService
{
    protected function plugins()
    {
        return app(\Aspectox\LaraJoom\Core\Plugins\PluginManager::class);
    }

    /**
     * Autentica contra los plugins del grupo "authentication" (joomla, ldap, …).
     * Devuelve ['status' => 'success'|'failure', 'user' => ?User, 'error_message' => string].
     */
    public function authenticate(array $credentials): array
    {
        $results = $this->plugins()->dispatch('authentication', 'onUserAuthenticate', [$credentials]);

        foreach ($results as $result) {
            if (!is_array($result) || ($result['status'] ?? 'failure') !== 'success') {
                continue;
            }

            $user = $result['user'] ?? null;

            if (!$user instanceof User) {
                $email = $result['email'] ?? null;
                $user = $email ? User::where('email', $email)->first() : null;
            }

            if ($user) {
                return ['status' => 'success', 'user' => $user];
            }
        }

        return ['status' => 'failure', 'error_message' => 'Credenciales inválidas'];
    }

    /**
     * Indica si el usuario tiene un segundo factor configurado (TOTP / YubiKey).
     */
    public function requiresMfa(User $user): bool
    {
        if (!Schema::hasTable('larajoom_user_mfa')) {
            return false;
        }

        return DB::table('larajoom_user_mfa')
            ->where('user_id', $user->getKey())
            ->where('enabled', 1)
            ->whereIn('method', ['totp', 'yubikey'])
            ->exists();
    }

    /**
     * Valida el código del segundo factor contra los plugins de 2FA.
     */
    public function verifyMfa(User $user, string $code): bool
    {
        $totp = $this->plugins()->resolve('twofactorauth', 'tfa_totp');
        if ($totp && method_exists($totp, 'validate') && $totp->validate($user->getKey(), $code)) {
            return true;
        }

        $yubi = $this->plugins()->resolve('twofactorauth', 'tfa_yubikey');
        if ($yubi && method_exists($yubi, 'validate') && $yubi->validate($user->getKey(), $code)) {
            return true;
        }

        return false;
    }

    /**
     * Completa el login: arranca sesión, regenera, emite "Recordarme" y audita.
     */
    public function completeLogin(User $user, bool $remember = false): void
    {
        Auth::guard('ax-larajoom')->login($user, $remember);
        session()->regenerate();

        // plugin system/remember → cookie persistente
        $rememberPlugin = $this->plugins()->resolve('system', 'remember');
        if ($rememberPlugin && method_exists($rememberPlugin, 'createRememberCookie')) {
            $rememberPlugin->createRememberCookie($user->getKey(), $remember);
        }

        // plugin actionlog/joomla → auditoría de login
        $actionlog = $this->plugins()->resolve('actionlog', 'joomla');
        if ($actionlog && method_exists($actionlog, 'onUserLogin')) {
            $actionlog->onUserLogin($user->getKey());
        }
    }

    /**
     * Cierra la sesión invocando los plugins system/logout y user/logout.
     */
    public function logout(?int $userId): void
    {
        $system = $this->plugins()->resolve('system', 'logout');
        if ($system && method_exists($system, 'onUserLogout')) {
            $system->onUserLogout($userId);
        }

        $user = $this->plugins()->resolve('user', 'logout');
        if ($user && method_exists($user, 'onUserLogout')) {
            $user->onUserLogout($userId);
        }

        Auth::guard('ax-larajoom')->logout();
    }
}
