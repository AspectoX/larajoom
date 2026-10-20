<?php

namespace Aspectox\LaraJoom\Core\Settings;

/**
 * Registro canónico de la configuración global de LaraJoom.
 *
 * Es la fuente de verdad: de acá salen el tipo, el default, el ámbito
 * (scope), la pestaña del formulario, el consumidor y el destino de cada opción.
 *
 * scope:
 *   'site'  -> solo afecta al sitio público (front)
 *   'admin' -> solo afecta al panel de administración
 *   'both'  -> afecta a site y admin
 *
 * target:
 *   'cms'      -> ajuste del CMS (lo usa LaraJoom)
 *   'framework' -> ajuste de Laravel (se aplica al config() de la app)
 */
class Registry
{
    public const SCOPE_SITE  = 'site';
    public const SCOPE_ADMIN = 'admin';
    public const SCOPE_BOTH  = 'both';

    public const TARGET_CMS      = 'cms';
    public const TARGET_FRAMEWORK = 'framework';

    /**
     * tipo: bool | int | string | text | json
     *
     * @var array<string, array{scope:string,type:string,default:mixed,group:string,label:string,consumer:string,target:string}>
     */
    public const OPTIONS = [
        // ------------------------------------------------ Site
        'sitename' => [self::SCOPE_BOTH, 'string', 'LaraJoom', 'site', 'sitename', 'Título del sitio (front y meta author)', self::TARGET_CMS],
        'offline' => [self::SCOPE_SITE, 'bool', 0, 'site', 'offline', 'Pone el sitio fuera de línea', self::TARGET_CMS],
        'display_offline_message' => [self::SCOPE_SITE, 'int', 1, 'site', 'display_offline_message', 'Qué muestra la pantalla de sitio offline', self::TARGET_CMS],
        'offline_message' => [self::SCOPE_SITE, 'text', '', 'site', 'offline_message', 'Mensaje de sitio fuera de línea', self::TARGET_CMS],
        'offline_image' => [self::SCOPE_SITE, 'string', '', 'site', 'offline_image', 'Imagen de la pantalla de sitio offline', self::TARGET_CMS],
        'frontediting' => [self::SCOPE_SITE, 'int', 1, 'site', 'frontediting', 'Edición frontal de artículos', self::TARGET_CMS],
        'editor' => [self::SCOPE_ADMIN, 'string', 'tinymce', 'site', 'editor', 'Editor por defecto del panel', self::TARGET_CMS],
        'captcha' => [self::SCOPE_SITE, 'string', '0', 'site', 'captcha', 'Captcha del registro del sitio', self::TARGET_CMS],
        'access' => [self::SCOPE_SITE, 'int', 1, 'site', 'access', 'Nivel de acceso por defecto del front', self::TARGET_CMS],
        'list_limit' => [self::SCOPE_BOTH, 'int', 10, 'site', 'list_limit', 'Filas por página en los listados', self::TARGET_CMS],
        'feed_limit' => [self::SCOPE_SITE, 'int', 10, 'site', 'feed_limit', 'Ítems por feed', self::TARGET_CMS],
        'feed_email' => [self::SCOPE_SITE, 'string', 'none', 'site', 'feed_email', 'Autor/email en los feeds', self::TARGET_CMS],
        'cookie_domain' => [self::SCOPE_BOTH, 'string', '', 'site', 'cookie_domain', 'Dominio de las cookies (session.domain)', self::TARGET_FRAMEWORK],
        'cookie_path' => [self::SCOPE_BOTH, 'string', '/', 'site', 'cookie_path', 'Ruta de las cookies (session.path)', self::TARGET_FRAMEWORK],

        // ------------------------------------------------ Metadata
        'MetaDesc' => [self::SCOPE_SITE, 'text', '', 'metadata', 'MetaDesc', 'Meta description del sitio', self::TARGET_CMS],
        'robots' => [self::SCOPE_SITE, 'string', 'index, follow', 'metadata', 'robots', 'Meta robots del sitio', self::TARGET_CMS],
        'MetaRights' => [self::SCOPE_SITE, 'string', '', 'metadata', 'MetaRights', 'Meta copyright del sitio', self::TARGET_CMS],
        'MetaAuthor' => [self::SCOPE_SITE, 'bool', 1, 'metadata', 'MetaAuthor', 'Meta author del sitio', self::TARGET_CMS],

        // ------------------------------------------------ SEO
        'sef' => [self::SCOPE_SITE, 'bool', 1, 'seo', 'sef', 'URLs amigables (SEF)', self::TARGET_CMS],
        'sef_rewrite' => [self::SCOPE_SITE, 'bool', 0, 'seo', 'sef_rewrite', 'Reescritura de URLs', self::TARGET_CMS],
        'sef_suffix' => [self::SCOPE_SITE, 'bool', 0, 'seo', 'sef_suffix', 'Sufijo .html en las URLs', self::TARGET_CMS],
        'unicodeslugs' => [self::SCOPE_BOTH, 'bool', 0, 'seo', 'unicodeslugs', 'Slugs con caracteres unicode', self::TARGET_CMS],
        'sitename_pagetitles' => [self::SCOPE_BOTH, 'int', 0, 'seo', 'sitename_pagetitles', 'Nombre del sitio en los títulos (0=no, 1=detrás, 2=delante)', self::TARGET_CMS],

        // ------------------------------------------------ Cache (framework)
        'caching' => [self::SCOPE_BOTH, 'int', 2, 'cache', 'caching', 'Modo de cache (0=off, 1=conservador, 2=progresivo)', self::TARGET_FRAMEWORK],
        'cache_handler' => [self::SCOPE_BOTH, 'string', '', 'cache', 'cache_handler', 'Store de cache (vacío = el de la aplicación)', self::TARGET_FRAMEWORK],
        'cache_platformprefix' => [self::SCOPE_BOTH, 'bool', 0, 'cache', 'cache_platformprefix', 'Prefijo de la cache por plataforma', self::TARGET_FRAMEWORK],
        'cachetime' => [self::SCOPE_BOTH, 'int', 15, 'cache', 'cachetime', 'Tiempo de cache en minutos', self::TARGET_FRAMEWORK],
        'cache_path' => [self::SCOPE_BOTH, 'string', 'cache', 'cache', 'cache_path', 'Ruta del almacenamiento de la cache', self::TARGET_FRAMEWORK],
        'memcached_persist' => [self::SCOPE_BOTH, 'bool', 1, 'cache', 'memcached_persist', 'Persistencia de memcached', self::TARGET_FRAMEWORK],
        'memcached_compress' => [self::SCOPE_BOTH, 'bool', 0, 'cache', 'memcached_compress', 'Compresión de memcached', self::TARGET_FRAMEWORK],
        'memcached_server_host' => [self::SCOPE_BOTH, 'string', 'localhost', 'cache', 'memcached_server_host', 'Host de memcached', self::TARGET_FRAMEWORK],
        'memcached_server_port' => [self::SCOPE_BOTH, 'int', 11211, 'cache', 'memcached_server_port', 'Puerto de memcached', self::TARGET_FRAMEWORK],
        'redis_persist' => [self::SCOPE_BOTH, 'bool', 1, 'cache', 'redis_persist', 'Persistencia de redis', self::TARGET_FRAMEWORK],
        'redis_server_host' => [self::SCOPE_BOTH, 'string', 'localhost', 'cache', 'redis_server_host', 'Host de redis', self::TARGET_FRAMEWORK],
        'redis_server_port' => [self::SCOPE_BOTH, 'int', 6379, 'cache', 'redis_server_port', 'Puerto de redis', self::TARGET_FRAMEWORK],
        'redis_server_auth' => [self::SCOPE_BOTH, 'string', '', 'cache', 'redis_server_auth', 'Contraseña de redis', self::TARGET_FRAMEWORK],
        'redis_server_db' => [self::SCOPE_BOTH, 'int', 0, 'cache', 'redis_server_db', 'Base de datos de redis', self::TARGET_FRAMEWORK],

        // ------------------------------------------------ Debug
        'debug' => [self::SCOPE_BOTH, 'bool', 0, 'debug', 'debug', 'Modo debug de la aplicación', self::TARGET_FRAMEWORK],
        'debug_lang' => [self::SCOPE_BOTH, 'bool', 0, 'debug', 'debug_lang', 'Depuración de idioma', self::TARGET_FRAMEWORK],
        'debug_lang_const' => [self::SCOPE_BOTH, 'bool', 1, 'debug', 'debug_lang_const', 'Mostrar el valor de las constantes de idioma', self::TARGET_FRAMEWORK],

        // ------------------------------------------------ Webservices (framework)
        'cors' => [self::SCOPE_SITE, 'bool', 0, 'webservices', 'cors', 'Habilita CORS para la API', self::TARGET_FRAMEWORK],
        'cors_allow_origin' => [self::SCOPE_SITE, 'string', '*', 'webservices', 'cors_allow_origin', 'Orígenes permitidos', self::TARGET_FRAMEWORK],
        'cors_allow_headers' => [self::SCOPE_SITE, 'string', 'Content-Type,X-LaraJoom-Token,Authorization', 'webservices', 'cors_allow_headers', 'Headers permitidos', self::TARGET_FRAMEWORK],
        'cors_allow_methods' => [self::SCOPE_SITE, 'string', 'GET,POST,PUT,DELETE,PATCH', 'webservices', 'cors_allow_methods', 'Métodos permitidos', self::TARGET_FRAMEWORK],

        // ------------------------------------------------ Proxy (framework)
        'behind_loadbalancer' => [self::SCOPE_BOTH, 'bool', 0, 'proxy', 'behind_loadbalancer', 'La app está detrás de un balanceador', self::TARGET_FRAMEWORK],
        'proxy_enable' => [self::SCOPE_BOTH, 'bool', 0, 'proxy', 'proxy_enable', 'Usa el proxy configurado', self::TARGET_FRAMEWORK],
        'proxy_host' => [self::SCOPE_BOTH, 'string', '', 'proxy', 'proxy_host', 'Host del proxy', self::TARGET_FRAMEWORK],
        'proxy_port' => [self::SCOPE_BOTH, 'string', '8080', 'proxy', 'proxy_port', 'Puerto del proxy', self::TARGET_FRAMEWORK],
        'proxy_user' => [self::SCOPE_BOTH, 'string', '', 'proxy', 'proxy_user', 'Usuario del proxy', self::TARGET_FRAMEWORK],
        'proxy_pass' => [self::SCOPE_BOTH, 'string', '', 'proxy', 'proxy_pass', 'Contraseña del proxy', self::TARGET_FRAMEWORK],

        // ------------------------------------------------ Locale (framework)
        'offset' => [self::SCOPE_BOTH, 'string', 'UTC', 'locale', 'offset', 'Zona horaria (identificador IANA)', self::TARGET_FRAMEWORK],

        // ------------------------------------------------ Mail
        'mailonline' => [self::SCOPE_SITE, 'bool', 1, 'mail', 'mailonline', 'Permite el envío de correo', self::TARGET_CMS],
        'massmailoff' => [self::SCOPE_SITE, 'bool', 0, 'mail', 'massmailoff', 'Bloquea el envío de correo masivo', self::TARGET_CMS],
        'mailfrom' => [self::SCOPE_BOTH, 'string', '', 'mail', 'mailfrom', 'Dirección de origen del correo', self::TARGET_FRAMEWORK],
        'fromname' => [self::SCOPE_BOTH, 'string', '', 'mail', 'fromname', 'Nombre de origen del correo', self::TARGET_FRAMEWORK],
        'replyto' => [self::SCOPE_BOTH, 'string', '', 'mail', 'replyto', 'Dirección de respuesta', self::TARGET_FRAMEWORK],
        'replytoname' => [self::SCOPE_BOTH, 'string', '', 'mail', 'replytoname', 'Nombre de respuesta', self::TARGET_FRAMEWORK],
        'mailer' => [self::SCOPE_BOTH, 'string', '', 'mail', 'mailer', 'Método de envío (vacío = el de la aplicación)', self::TARGET_FRAMEWORK],
        'sendmail' => [self::SCOPE_BOTH, 'string', '/usr/sbin/sendmail', 'mail', 'sendmail', 'Ruta del binario sendmail', self::TARGET_FRAMEWORK],
        'smtphost' => [self::SCOPE_BOTH, 'string', 'localhost', 'mail', 'smtphost', 'Host del servidor SMTP', self::TARGET_FRAMEWORK],
        'smtpport' => [self::SCOPE_BOTH, 'int', 25, 'mail', 'smtpport', 'Puerto del servidor SMTP', self::TARGET_FRAMEWORK],
        'smtpsecure' => [self::SCOPE_BOTH, 'string', 'none', 'mail', 'smtpsecure', 'Cifrado SMTP (none, ssl, tls)', self::TARGET_FRAMEWORK],
        'smtpauth' => [self::SCOPE_BOTH, 'bool', 0, 'mail', 'smtpauth', 'Usa autenticación SMTP', self::TARGET_FRAMEWORK],
        'smtpuser' => [self::SCOPE_BOTH, 'string', '', 'mail', 'smtpuser', 'Usuario de SMTP', self::TARGET_FRAMEWORK],
        'smtppass' => [self::SCOPE_BOTH, 'string', '', 'mail', 'smtppass', 'Contraseña de SMTP', self::TARGET_FRAMEWORK],

        // ------------------------------------------------ Server (framework)
        'tmp_path' => [self::SCOPE_BOTH, 'string', '', 'server', 'tmp_path', 'Ruta de archivos temporales', self::TARGET_FRAMEWORK],
        'gzip' => [self::SCOPE_BOTH, 'bool', 0, 'server', 'gzip', 'Comprime la salida', self::TARGET_FRAMEWORK],
        'error_reporting' => [self::SCOPE_BOTH, 'string', 'default', 'server', 'error_reporting', 'Nivel de errores (default, none, simple, maximum)', self::TARGET_FRAMEWORK],
        'force_ssl' => [self::SCOPE_BOTH, 'int', -1, 'server', 'force_ssl', 'Fuerza HTTPS (-1=no, 0=admin, 1=site, 2=todo)', self::TARGET_FRAMEWORK],

        // ------------------------------------------------ Session (framework)
        'session_handler' => [self::SCOPE_BOTH, 'string', '', 'session', 'session_handler', 'Driver de sesión (vacío = el de la aplicación)', self::TARGET_FRAMEWORK],
        'session_filesystem_path' => [self::SCOPE_BOTH, 'string', '', 'session', 'session_filesystem_path', 'Ruta de las sesiones en disco', self::TARGET_FRAMEWORK],
        'session_memcached_server_host' => [self::SCOPE_BOTH, 'string', 'localhost', 'session', 'session_memcached_server_host', 'Host de memcached para sesiones', self::TARGET_FRAMEWORK],
        'session_memcached_server_port' => [self::SCOPE_BOTH, 'int', 11211, 'session', 'session_memcached_server_port', 'Puerto de memcached para sesiones', self::TARGET_FRAMEWORK],
        'session_redis_persist' => [self::SCOPE_BOTH, 'bool', 1, 'session', 'session_redis_persist', 'Persistencia de redis para sesiones', self::TARGET_FRAMEWORK],
        'session_redis_server_host' => [self::SCOPE_BOTH, 'string', 'localhost', 'session', 'session_redis_server_host', 'Host de redis para sesiones', self::TARGET_FRAMEWORK],
        'session_redis_server_port' => [self::SCOPE_BOTH, 'int', 6379, 'session', 'session_redis_server_port', 'Puerto de redis para sesiones', self::TARGET_FRAMEWORK],
        'session_redis_server_auth' => [self::SCOPE_BOTH, 'string', '', 'session', 'session_redis_server_auth', 'Contraseña de redis para sesiones', self::TARGET_FRAMEWORK],
        'session_redis_server_db' => [self::SCOPE_BOTH, 'int', 0, 'session', 'session_redis_server_db', 'Base de datos de redis para sesiones', self::TARGET_FRAMEWORK],
        'lifetime' => [self::SCOPE_BOTH, 'int', 15, 'session', 'lifetime', 'Duración de la sesión en minutos', self::TARGET_FRAMEWORK],
        'shared_session' => [self::SCOPE_BOTH, 'bool', 0, 'session', 'shared_session', 'Comparte la sesión entre site y panel', self::TARGET_CMS],
        'session_metadata' => [self::SCOPE_BOTH, 'bool', 1, 'session', 'session_metadata', 'Guarda metadatos en la sesión', self::TARGET_FRAMEWORK],
        'session_metadata_for_guest' => [self::SCOPE_BOTH, 'bool', 1, 'session', 'session_metadata_for_guest', 'Metadatos de sesión para invitados', self::TARGET_FRAMEWORK],

        // ------------------------------------------------ Logging (framework)
        'log_path' => [self::SCOPE_BOTH, 'string', '', 'logging', 'log_path', 'Ruta de los logs', self::TARGET_FRAMEWORK],
        'log_everything' => [self::SCOPE_BOTH, 'bool', 0, 'logging', 'log_everything', 'Registra todo en el log', self::TARGET_FRAMEWORK],
        'log_deprecated' => [self::SCOPE_BOTH, 'bool', 0, 'logging', 'log_deprecated', 'Registra los avisos de deprecación', self::TARGET_FRAMEWORK],
        'log_priorities' => [self::SCOPE_BOTH, 'string', 'all', 'logging', 'log_priorities', 'Niveles de log permitidos', self::TARGET_FRAMEWORK],
        'log_categories' => [self::SCOPE_BOTH, 'string', '', 'logging', 'log_categories', 'Categorías de log a filtrar', self::TARGET_FRAMEWORK],
        'log_category_mode' => [self::SCOPE_BOTH, 'int', 0, 'logging', 'log_category_mode', 'Modo del filtro de categorías (0=incluir, 1=excluir)', self::TARGET_FRAMEWORK],

        // ------------------------------------------------ Filtros de texto
        'filters' => [self::SCOPE_BOTH, 'json', '', 'filters', 'filters', 'Filtros de texto por grupo', self::TARGET_CMS],
    ];

    /** Opciones que ya no se usan (heredadas de Joomla o duplicadas) */
    public const OBSOLETE = ['dbtype', 'dbprefix', 'timezone', 'MetaVersion'];

    public static function all(): array
    {
        return self::OPTIONS;
    }

    public static function exists(string $key): bool
    {
        return isset(self::OPTIONS[$key]);
    }

    /**
     * @return array{scope:string,type:string,default:mixed,group:string,label:string,consumer:string,target:string}|null
     */
    public static function get(string $key): ?array
    {
        if (!isset(self::OPTIONS[$key])) {
            return null;
        }

        return [
            'scope'    => self::OPTIONS[$key][0],
            'type'     => self::OPTIONS[$key][1],
            'default'  => self::OPTIONS[$key][2],
            'group'    => self::OPTIONS[$key][3],
            'label'    => self::OPTIONS[$key][4],
            'consumer' => self::OPTIONS[$key][5],
            'target'   => self::OPTIONS[$key][6],
        ];
    }

    public static function type(string $key): string
    {
        return self::get($key)['type'] ?? 'string';
    }

    public static function default(string $key): mixed
    {
        return self::get($key)['default'] ?? null;
    }

    public static function scope(string $key): string
    {
        return self::get($key)['scope'] ?? self::SCOPE_BOTH;
    }

    public static function target(string $key): string
    {
        return self::get($key)['target'] ?? self::TARGET_CMS;
    }

    /**
     * Claves de un ámbito (site, admin o both).
     */
    public static function keysOfScope(string $scope): array
    {
        $keys = [];

        foreach (self::OPTIONS as $key => $option) {
            if ($option[0] === $scope) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * Claves de una pestaña del formulario (site, metadata, seo, cache…).
     */
    public static function keysOfGroup(string $group): array
    {
        $keys = [];

        foreach (self::OPTIONS as $key => $option) {
            if ($option[3] === $group) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * Defaults tipados de todas las opciones.
     */
    public static function defaults(): array
    {
        $defaults = [];

        foreach (self::OPTIONS as $key => $option) {
            $defaults[$key] = $option[2];
        }

        return $defaults;
    }

    /**
     * Convierte un valor de formulario (string) al tipo declarado.
     */
    public static function cast(string $key, mixed $value): mixed
    {
        $type = self::type($key);

        return match ($type) {
            'bool' => is_bool($value)
                ? $value
                : (bool) filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) || $value === 1 || $value === '1',
            'int'  => (int) $value,
            'json' => is_array($value)
                ? $value
                : (is_string($value) && $value !== '' ? (json_decode($value, true) ?? $value) : []),
            'text', 'string' => (string) $value,
            default => $value,
        };
    }
}
