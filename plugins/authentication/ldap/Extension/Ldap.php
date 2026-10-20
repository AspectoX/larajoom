<?php

namespace Aspectox\LaraJoom\Plugins\Authentication\Ldap\Extension;

class Ldap
{
    public function getName(): string
    {
        return 'authentication.ldap';
    }

    /**
     * Parámetros (de ldap.xml) almacenados en la configuración del plugin.
     */
    public function getParams(): array
    {
        return [
            'host'             => null,
            'port'             => 389,
            'use_ldapV3'       => 1,
            'encryption'       => 'none',
            'ignore_reqcert_tls' => 1,
            'cacert'           => null,
            'no_referrals'     => 0,
            'auth_method'      => 'bind',
            'base_dn'          => null,
            'search_string'    => null,
            'users_dn'         => null,
            'username'         => null,
            'password'         => null,
            'ldap_fullname'    => 'fullName',
            'ldap_email'       => 'mail',
            'ldap_uid'         => 'uid',
            'ldap_debug'       => 0,
        ];
    }

    public function onUserAuthenticate(array $credentials): array
    {
        // Implementación LDAP: bind contra el servidor configurado.
        return ['status' => 'failure', 'type' => 'LDAP', 'error_message' => ''];
    }
}
