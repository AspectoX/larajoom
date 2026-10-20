<?php

namespace Aspectox\LaraJoom\Plugins\Authentication\Joomla\Extension;

use Illuminate\Support\Facades\Auth;
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
     */
    public function onUserAuthenticate(array $credentials): array
    {
        $response = ['status' => 'failure', 'type' => 'Joomla', 'error_message' => ''];

        if (empty($credentials['password'])) {
            $response['error_message'] = 'JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED';
            return $response;
        }

        $user = User::where('email', $credentials['username'])
            ->orWhere('name', $credentials['username'])
            ->first();

        if (!$user) {
            Hash::make($credentials['password']);
            $response['error_message'] = 'JGLOBAL_AUTH_NO_USER';
            return $response;
        }

        if (Hash::check($credentials['password'], $user->password)) {
            $response['status']       = 'success';
            $response['email']        = $user->email;
            $response['fullname']     = $user->name;
            return $response;
        }

        $response['error_message'] = 'JGLOBAL_AUTH_INVALID_PASS';
        return $response;
    }
}
