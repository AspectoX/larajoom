<?php

namespace Aspectox\LaraJoom\Plugins\Authentication\Ldap\Extension;

use Illuminate\Support\Facades\Log;

class Ldap
{
    public function getName(): string
    {
        return 'authentication.ldap';
    }

    public function getParams(): array
    {
        return [
            'host'               => null,
            'port'               => 389,
            'use_ldapV3'         => 1,
            'encryption'         => 'none',   // none | ssl | tls
            'ignore_reqcert_tls' => 1,
            'cacert'             => null,
            'no_referrals'       => 0,
            'auth_method'        => 'bind',   // bind | search
            'base_dn'            => null,
            'search_string'      => null,
            'users_dn'           => null,
            'username'           => null,
            'password'           => null,
            'ldap_fullname'      => 'cn',
            'ldap_email'         => 'mail',
            'ldap_uid'          => 'uid',
            'ldap_debug'         => 0,
        ];
    }

    /**
     * Autenticación LDAP. Equivale a plg_authentication_ldap::onUserAuthenticate().
     *
     * Flujo: conecta, configura TLS/SSL, bind con una cuenta de servicio,
     * busca el usuario en base_dn y verifica su contraseña con un bind propio.
     */
    public function onUserAuthenticate(array $credentials): array
    {
        $response = ['status' => 'failure', 'type' => 'LDAP', 'error_message' => '', 'user' => null];
        $params   = $this->getParams();

        if (!function_exists('ldap_connect')) {
            $response['error_message'] = 'La extensión PHP LDAP no está disponible.';
            return $response;
        }

        $login = trim((string) ($credentials['username'] ?? ''));
        $pass  = (string) ($credentials['password'] ?? '');

        if ($login === '' || $pass === '') {
            $response['error_message'] = 'JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED';
            return $response;
        }

        $host = rtrim((string) $params['host'], '/');
        $port = (int) ($params['port'] ?: 389);

        if ($host === '') {
            $response['error_message'] = 'LDAP host no configurado.';
            return $response;
        }

        // LDAPS (ssl) requiere prefijo ldaps://
        if (($params['encryption'] ?? 'none') === 'ssl') {
            $host = 'ldaps://' . $host;
        }

        $conn = @ldap_connect($host, $port);
        if (!$conn) {
            $response['error_message'] = 'No se pudo conectar al servidor LDAP.';
            return $response;
        }

        // Opciones de conexión
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, (int) $params['use_ldapV3'] ? 3 : 2);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, (int) $params['no_referrals'] ? 0 : 1);
        ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, 5);

        $cacert = (string) ($params['cacert'] ?? '');
        if ($cacert !== '' && is_file($cacert)) {
            ldap_set_option($conn, LDAP_OPT_X_TLS_CACERTFILE, $cacert);
        }
        if (!empty($params['ignore_reqcert_tls'])) {
            ldap_set_option($conn, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);
        }

        // StartTLS antes del bind
        if (($params['encryption'] ?? 'none') === 'tls') {
            if (!@ldap_start_tls($conn)) {
                $response['error_message'] = 'No se pudo iniciar TLS.';
                return $response;
            }
        }

        // Bind con la cuenta de servicio (si se configuró)
        $bindUser = (string) ($params['username'] ?? '');
        $bindPass = (string) ($params['password'] ?? '');

        if ($bindUser !== '') {
            if (!@ldap_bind($conn, $bindUser, $bindPass)) {
                $response['error_message'] = 'Fallo el bind de la cuenta de servicio LDAP.';
                return $response;
            }
        }

        // Determinación del DN del usuario
        $dn = $this->resolveDn($conn, $login, $params);

        // Bind con las credenciales del usuario (método bind canónico)
        if ($dn !== null) {
            if (@ldap_bind($conn, $dn, $pass)) {
                $entry = $this->fetchEntry($conn, $dn, $params);
                $response['status'] = 'success';
                $response['user']   = $entry;
                $response['email']  = $entry['email'] ?? null;
                $response['fullname'] = $entry['fullname'] ?? $entry['uid'] ?? $login;
                return $response;
            }

            $response['error_message'] = 'JGLOBAL_AUTH_INVALID_PASS';
            return $response;
        }

        $response['error_message'] = 'JGLOBAL_AUTH_NO_USER';
        return $response;
    }

    /**
     * Resuelve el DN del usuario:
     *  - auth_method=bind  → construye el DN desde users_dn y el uid.
     *  - auth_method=search → busca en base_dn con search_string.
     */
    protected function resolveDn($conn, string $login, array $params): ?string
    {
        $uidAttr = (string) ($params['ldap_uid'] ?: 'uid');
        $usersDn = (string) ($params['users_dn'] ?? '');
        $baseDn  = (string) ($params['base_dn'] ?? '');
        $method   = (string) ($params['auth_method'] ?: 'bind');

        if ($method === 'bind' && $usersDn !== '') {
            $escaped = $this->escapeFilter($login);
            return $uidAttr . '=' . $escaped . ',' . $usersDn;
        }

        if ($method === 'search' && $baseDn !== '') {
            $searchString = (string) ($params['search_string'] ?? '');
            $filter = str_replace(
                ['[search]', '[username]'],
                [$this->escapeFilter($login), $this->escapeFilter($login)],
                $searchString
            );
            if ($filter === '') {
                $filter = '(' . $uidAttr . '=' . $this->escapeFilter($login) . ')';
            }

            $sr = @ldap_search($conn, $baseDn, $filter, [$uidAttr], 0, 1);
            if ($sr === false) {
                return null;
            }

            $entries = ldap_get_entries($conn, $sr);
            if (is_array($entries) && isset($entries['count']) && $entries['count'] > 0) {
                return $entries[0]['dn'] ?? null;
            }
        }

        return null;
    }

    /**
     * Obtiene los atributos mapeados (fullname/email/uid) de una entrada LDAP.
     */
    protected function fetchEntry($conn, string $dn, array $params): array
    {
        $fullAttr = strtolower((string) ($params['ldap_fullname'] ?: 'cn'));
        $mailAttr = strtolower((string) ($params['ldap_email'] ?: 'mail'));
        $uidAttr  = strtolower((string) ($params['ldap_uid'] ?: 'uid'));

        $sr = @ldap_read($conn, $dn, '(objectClass=*)', [$fullAttr, $mailAttr, $uidAttr]);
        if ($sr === false) {
            return ['uid' => $dn];
        }

        $entries = ldap_get_entries($conn, $sr);
        if (!is_array($entries) || empty($entries['count'])) {
            return ['uid' => $dn];
        }

        $e = $entries[0];

        return [
            'uid'      => $this->firstValue($e, $uidAttr) ?? $dn,
            'fullname' => $this->firstValue($e, $fullAttr),
            'email'    => $this->firstValue($e, $mailAttr),
        ];
    }

    protected function firstValue(array $entry, string $attr): ?string
    {
        if (isset($entry[$attr]) && is_array($entry[$attr]) && !empty($entry[$attr]['count'])) {
            return (string) $entry[$attr][0];
        }
        return null;
    }

    /**
     * Escapa caracteres especiales LDAP para filtros/DN (RFC 4515).
     */
    protected function escapeFilter(string $value): string
    {
        return str_replace(
            ['\\', '*', '(', ')', "\0", '/'],
            ['\\5c', '\\2a', '\\28', '\\29', '\\00', '\\2f'],
            $value
        );
    }
}
