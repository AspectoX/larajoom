<?php

namespace Aspectox\LaraJoom\Plugins\User\Logout\Extension;

use Illuminate\Support\Facades\Log;

class Logout
{
    public function getName(): string
    {
        return 'user.logout';
    }

    /**
     * Equivale a plg_user_logout::onUserLogout(). Invalida la sesión y
     * registra el evento (para auditoría).
     */
    public function onUserLogout(?int $userId, array $options = []): array
    {
        if ($userId && ($options['log'] ?? true)) {
            Log::info('LaraJoom logout', ['user_id' => $userId]);
        }

        return ['type' => 'logout', 'status' => 'success'];
    }
}
