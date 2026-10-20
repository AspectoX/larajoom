<?php

namespace Aspectox\LaraJoom\Plugins\ApiAuthentication\Token\Extension;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Token
{
    public function getName(): string
    {
        return 'api-authentication.token';
    }

    /**
     * Autenticación mediante Bearer Token.
     */
    public function onUserAuthenticate(Request $request): array
    {
        $header = $request->header('Authorization', '');
        if (!preg_match('/Bearer\s+(\S+)/', $header, $m)) {
            return ['status' => 'failure', 'error_message' => 'No token'];
        }

        $token = $m[1];
        $row = DB::table('larajoom_users')->where('api_token', hash('sha256', $token))->first();

        if (!$row) {
            return ['status' => 'failure', 'error_message' => 'Invalid token'];
        }

        return ['status' => 'success', 'user' => $row];
    }
}
