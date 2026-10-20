<?php

namespace Aspectox\LaraJoom\Core\Settings;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Ajustes de configuración global que corresponden a opciones del framework (Laravel)
 * y que se aplican al config() de la aplicación en el boot.
 *
 * Reglas:
 * - Lo guardado es lo que se aplica (misma nomenclatura que Laravel).
 * - Cada valor se valida antes de aplicarse: si no es válido o no está disponible,
 *   se usa un fallback que nunca rompe la aplicación.
 * - Cada ajuste se aplica de forma independiente: un fallo no impide aplicar los demás.
 */
class FrameworkConfig
{
    /** Drivers de sesión soportados por Laravel */
    public const SESSION_DRIVERS = ['file', 'database', 'redis', 'memcached', 'cookie', 'array'];

    /** Mailers soportados por Laravel */
    public const MAILERS = ['smtp', 'sendmail', 'log', 'array', 'failover'];

    /** Stores de cache soportados por Laravel */
    public const CACHE_STORES = ['file', 'database', 'redis', 'memcached', 'array', 'null'];

    public const SESSION_DRIVER_FALLBACK = 'file';
    public const MAILER_FALLBACK = 'log';
    public const CACHE_STORE_FALLBACK = 'file';
    public const TIMEZONE_FALLBACK = 'UTC';

    /**
     * Aplica todos los overrides. Nunca debe romper la aplicación.
     */
    public static function apply(): void
    {
        self::safe('debug', fn () => self::applyDebug());
        self::safe('timezone', fn () => self::applyTimezone());
        self::safe('error_reporting', fn () => self::applyErrorReporting());
        self::safe('session', fn () => self::applySession());
        self::safe('cache', fn () => self::applyCache());
        self::safe('mail', fn () => self::applyMail());
    }

    // -----------------------------------------------------------------
    // Valores normalizados
    // -----------------------------------------------------------------

    /**
     * Driver de sesión configurado, normalizado a un valor válido y disponible.
     */
    public static function sessionDriver(?string $value = null): string
    {
        $value = self::normalizeSessionDriver($value);

        if (!self::driverAvailable($value, 'session')) {
            return self::SESSION_DRIVER_FALLBACK;
        }

        return $value;
    }

    /**
     * Normaliza el nombre del driver de sesión (sin comprobar disponibilidad).
     * Traduce los valores históricos de Joomla a los de Laravel.
     */
    public static function normalizeSessionDriver(?string $value = null): string
    {
        $value = $value ?? (string) config('larajoom.session_handler', self::SESSION_DRIVER_FALLBACK);

        $value = match ($value) {
            'filesystem' => 'file',
            default      => $value,
        };

        return in_array($value, self::SESSION_DRIVERS, true) ? $value : self::SESSION_DRIVER_FALLBACK;
    }

    /**
     * Mailer configurado, normalizado a un valor válido de Laravel.
     */
    public static function mailer(?string $value = null): string
    {
        $value = self::normalizeMailer($value);

        return $value;
    }

    /**
     * Normaliza el nombre del mailer (sin comprobar disponibilidad).
     * Traduce los valores históricos de Joomla a los de Laravel.
     */
    public static function normalizeMailer(?string $value = null): string
    {
        $value = $value ?? (string) config('larajoom.mailer', self::MAILER_FALLBACK);

        $value = match ($value) {
            'mail'  => 'smtp',
            default => $value,
        };

        return in_array($value, self::MAILERS, true) ? $value : self::MAILER_FALLBACK;
    }

    /**
     * Store de cache configurado, normalizado a un valor válido y disponible.
     */
    public static function cacheStore(?string $value = null): string
    {
        $value = self::normalizeCacheStore($value);

        if (!self::driverAvailable($value, 'cache')) {
            return self::CACHE_STORE_FALLBACK;
        }

        return $value;
    }

    /**
     * Normaliza el nombre del store de cache (sin comprobar disponibilidad).
     */
    public static function normalizeCacheStore(?string $value = null): string
    {
        $value = $value ?? (string) config('larajoom.cache_handler', self::CACHE_STORE_FALLBACK);

        $value = match ($value) {
            'filesystem' => 'file',
            default      => $value,
        };

        return in_array($value, self::CACHE_STORES, true) ? $value : self::CACHE_STORE_FALLBACK;
    }

    /**
     * Zona horaria configurada (identificador IANA), validada.
     */
    public static function timezone(?string $value = null): string
    {
        $value = $value ?? (string) config('larajoom.offset', self::TIMEZONE_FALLBACK);

        return in_array($value, timezone_identifiers_list(), true) ? $value : self::TIMEZONE_FALLBACK;
    }

    // -----------------------------------------------------------------
    // Aplicación
    // -----------------------------------------------------------------

    protected static function applyDebug(): void
    {
        config(['app.debug' => (bool) config('larajoom.debug', false)]);
    }

    protected static function applyTimezone(): void
    {
        $timezone = self::timezone();

        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);
    }

    protected static function applyErrorReporting(): void
    {
        $levels = [
            'default' => E_ALL,
            'none'    => 0,
            'simple'  => E_ERROR | E_WARNING | E_PARSE,
            'maximum' => E_ALL,
        ];

        $level = (string) config('larajoom.error_reporting', 'default');

        if (isset($levels[$level])) {
            error_reporting($levels[$level]);
        }
    }

    protected static function applySession(): void
    {
        config(['session.driver' => self::sessionDriver()]);

        $lifetime = (int) config('larajoom.lifetime', 15);
        if ($lifetime > 0) {
            config(['session.lifetime' => $lifetime]);
        }

        $domain = (string) config('larajoom.cookie_domain', '');
        if ($domain !== '') {
            config(['session.domain' => $domain]);
        }

        $path = (string) config('larajoom.cookie_path', '');
        if ($path !== '') {
            config(['session.path' => $path]);
        }
    }

    protected static function applyCache(): void
    {
        $store = self::cacheStore();

        config(['cache.default' => $store]);

        $ttl = (int) config('larajoom.cachetime', 15);
        if ($ttl > 0 && config('cache.stores.' . $store) !== null) {
            // Joomla guarda el tiempo de cache en minutos, Laravel en segundos
            config(['cache.stores.' . $store . '.ttl' => $ttl * 60]);
        }
    }

    protected static function applyMail(): void
    {
        config(['mail.default' => self::mailer()]);

        $map = [
            'mailfrom'    => 'mail.from.address',
            'fromname'    => 'mail.from.name',
            'replyto'     => 'mail.reply_to.address',
            'replytoname' => 'mail.reply_to.name',
        ];

        foreach ($map as $key => $configKey) {
            $value = (string) config('larajoom.' . $key, '');
            if ($value !== '') {
                config([$configKey => $value]);
            }
        }

        $host = (string) config('larajoom.smtphost', '');
        if ($host !== '') {
            config(['mail.mailers.smtp.host' => $host]);
        }

        $port = (int) config('larajoom.smtpport', 25);
        if ($port > 0) {
            config(['mail.mailers.smtp.port' => $port]);
        }

        $secure = (string) config('larajoom.smtpsecure', 'none');
        if ($secure !== '') {
            config([
                'mail.mailers.smtp.scheme' => $secure === 'none' ? null : $secure,
                'mail.mailers.smtp.secure' => $secure === 'none' ? null : $secure,
            ]);
        }

        if (config('larajoom.smtpauth')) {
            $user = (string) config('larajoom.smtpuser', '');
            $pass = (string) config('larajoom.smtppass', '');

            if ($user !== '') {
                config(['mail.mailers.smtp.username' => $user]);
            }
            if ($pass !== '') {
                config(['mail.mailers.smtp.password' => $pass]);
            }
        }
    }

    // -----------------------------------------------------------------
    // Soporte
    // -----------------------------------------------------------------

    /**
     * Un driver está disponible si sus dependencias existen.
     * (Laravel: database necesita la tabla, redis/memcached la extensión o el cliente)
     */
    protected static function driverAvailable(string $driver, string $context): bool
    {
        return match ($driver) {
            'database' => $context === 'session'
                ? Schema::hasTable((string) config('session.table', 'sessions'))
                : Schema::hasTable((string) config('cache.stores.database.table', 'cache')),
            'redis' => extension_loaded('redis') || class_exists(\Predis\Client::class),
            'memcached' => extension_loaded('memcached'),
            default => true,
        };
    }

    /**
     * Ejecuta un ajuste sin dejar que una excepción rompa la aplicación.
     */
    protected static function safe(string $key, callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::warning('LaraJoom: no se pudo aplicar el override de configuración "' . $key . '": ' . $e->getMessage());
        }
    }
}
