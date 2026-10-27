<?php

namespace Aspectox\LaraJoom\Plugins\System\Logout\Extension;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class Logout
{
    public function getName(): string
    {
        return 'system.logout';
    }

    /**
     * Revoca todas las series de "Recordarme" del usuario y borra la cookie.
     * Se invoca en el flujo de logout (onUserLogout).
     */
    public function onUserLogout(?int $userId): void
    {
        if ($userId) {
            DB::table('larajoom_rememberme')->where('user_id', $userId)->delete();
        }

        Cookie::queue(Cookie::forget('larajoom_remember'));
    }
}
