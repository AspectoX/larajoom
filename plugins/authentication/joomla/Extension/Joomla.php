<?php

namespace Aspectox\LaraJoom\Plugins\Authentication\Joomla\Extension;

use Illuminate\Support\Facades\Hash;
use Aspectox\LaraJoom\Admin\Components\com_users\Models\User;

class Joomla
{
    public function getName(): string
    {
        return 'authentication.joomla';
    }

    /**
     * Autenticación local contra la tabla de usuarios.
     * Equivale a plg_authentication_joomla::onUserAuthenticate()
     *
     * Respuesta (contrato del grupo "authentication"):
     *   ['status' => 'success'|'failure', 'type' => 'Joomla',
     *    'error_message' => string, 'user' => ?object,
     *    'email' => ?string, 'fullname' => ?string]
     */
    public function onUserAuthenticate(array $credentials): array
    {
        $response = ['status' => 'failure', 'type' => 'Joomla', 'error_message' => '', 'user' => null];

        if (empty($credentials['password'])) {
            $response['error_message'] = 'JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED';
            return $response;
        }

        $login = trim((string) ($credentials['username'] ?? ''));
        if ($login === '') {
            $response['error_message'] = 'JGLOBAL_AUTH_NO_USER';
            return $response;
        }

        $user = User::where('email', $login)
            ->orWhere('username', $login)
            ->first();

        // Anti-sondeo: cifra aunque el usuario no exista (mismo coste).
        if (!$user) {
            Hash::make($credentials['password']);
            $response['error_message'] = 'JGLOBAL_AUTH_NO_USER';
            return $response;
        }

        // Usuario desactivado / pendiente de activación.
        if (!$user->activated) {
            $response['error_message'] = 'JLIB_LOGIN_ERROR_NOT_ACTIVATED';
            return $response;
        }

        if (Hash::check($credentials['password'], $user->password)) {
            $response['status']       = 'success';
            $response['user']         = $user;
            $response['email']        = $user->email;
            $response['fullname']     = $user->name;
            return $response;
        }

        $response['error_message'] = 'JGLOBAL_AUTH_INVALID_PASS';
        return $response;
    }
}
