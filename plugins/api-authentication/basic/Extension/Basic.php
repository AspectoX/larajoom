<?php

namespace Aspectox\LaraJoom\Plugins\ApiAuthentication\Basic\Extension;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Basic
{
    public function getName(): string
    {
        return 'api-authentication.basic';
    }

    /**
     * Autenticación HTTP Basic para la API.
     */
    public function onUserAuthenticate(Request $request): array
    {
        $user = $request->getUser();
        $pass = $request->getPassword();

        if (!$user || !$pass) {
            return ['status' => 'failure', 'error_message' => 'Authentication required'];
        }

        if (Auth::attempt(['email' => $user, 'password' => $pass])) {
            return ['status' => 'success', 'user' => Auth::user()];
        }

        return ['status' => 'failure', 'error_message' => 'Invalid credentials'];
    }
}
