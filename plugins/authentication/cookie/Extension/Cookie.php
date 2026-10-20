<?php

namespace Aspectox\LaraJoom\Plugins\Authentication\Cookie\Extension;

class Cookie
{
    public function getName(): string
    {
        return 'authentication.cookie';
    }

    /**
     * Parámetros (de cookie.xml).
     */
    public function getParams(): array
    {
        return [
            'cookie_lifetime' => 60,
            'key_length'      => 16,
        ];
    }

    public function onUserAuthenticate(array $credentials): array
    {
        // Autenticación por cookie de sesión ("Recordarme").
        return ['status' => 'failure', 'type' => 'Cookie', 'error_message' => ''];
    }
}
