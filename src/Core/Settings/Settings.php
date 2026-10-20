<?php

namespace Aspectox\LaraJoom\Core\Settings;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Servicio de configuración global de LaraJoom.
 *
 * - Carga una sola vez por request (1 query) y deja los valores tipados en memoria.
 * - Los tipos y defaults salen de Settings\Registry (fuente de verdad).
 * - Permite consultar por ámbito: site / admin / both.
 */
class Settings
{
    /** Tabla donde se guardan los valores */
    public const TABLE = 'larajoom_configuration';

    /** @var array<string, mixed>|null Valores tipados en memoria */
    protected static ?array $values = null;

    /**
     * Carga toda la configuración en memoria (idempotente).
     *
     * @return array<string, mixed>
     */
    public static function load(): array
    {
        if (self::$values !== null) {
            return self::$values;
        }

        return self::$values = array_merge(Registry::defaults(), self::stored());
    }

    /**
     * Valores que están guardados en la base (tipados), sin defaults.
     *
     * @return array<string, mixed>
     */
    public static function stored(): array
    {
        $values = [];

        if (!Schema::hasTable(self::TABLE)) {
            return $values;
        }

        $rows = DB::table(self::TABLE)->select('name', 'value')->get();

        foreach ($rows as $row) {
            if (!Registry::exists($row->name)) {
                continue;
            }

            $values[$row->name] = Registry::cast($row->name, $row->value);
        }

        return $values;
    }

    /**
     * Descarta la caché en memoria (tras guardar).
     */
    public static function flush(): void
    {
        self::$values = null;
    }

    /**
     * Todos los valores tipados.
     *
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return self::load();
    }

    /**
     * Valor tipado de una opción.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $values = self::load();

        if (array_key_exists($key, $values)) {
            return $values[$key];
        }

        return $default ?? Registry::default($key);
    }

    public static function bool(string $key): bool
    {
        return (bool) self::get($key, false);
    }

    public static function int(string $key): int
    {
        return (int) self::get($key, 0);
    }

    public static function string(string $key, string $default = ''): string
    {
        $value = self::get($key, $default);

        return is_scalar($value) ? (string) $value : $default;
    }

    /**
     * @return array<mixed>
     */
    public static function array(string $key): array
    {
        $value = self::get($key, []);

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    // -----------------------------------------------------------------
    // Consultas por ámbito
    // -----------------------------------------------------------------

    /**
     * Valor de una opción de ámbito site.
     */
    public static function site(string $key, mixed $default = null): mixed
    {
        return self::scoped(Registry::SCOPE_SITE, $key, $default);
    }

    /**
     * Valor de una opción de ámbito admin.
     */
    public static function admin(string $key, mixed $default = null): mixed
    {
        return self::scoped(Registry::SCOPE_ADMIN, $key, $default);
    }

    /**
     * Valor de una opción compartida por site y admin.
     */
    public static function core(string $key, mixed $default = null): mixed
    {
        return self::scoped(Registry::SCOPE_BOTH, $key, $default);
    }

    /**
     * Todos los valores de un ámbito.
     *
     * @return array<string, mixed>
     */
    public static function byScope(string $scope): array
    {
        $values = self::load();
        $result = [];

        foreach (Registry::keysOfScope($scope) as $key) {
            $result[$key] = $values[$key] ?? Registry::default($key);
        }

        return $result;
    }

    /**
     * Lee una opción validando que pertenezca al ámbito pedido.
     */
    protected static function scoped(string $scope, string $key, mixed $default = null): mixed
    {
        if (Registry::scope($key) !== $scope) {
            return $default;
        }

        return self::get($key, $default);
    }

    // -----------------------------------------------------------------
    // Escritura
    // -----------------------------------------------------------------

    /**
     * Guarda un conjunto de valores (['clave' => valor, ...]).
     * Devuelve la cantidad de opciones guardadas.
     */
    public static function save(array $values): int
    {
        if (!Schema::hasTable(self::TABLE)) {
            return 0;
        }

        $saved = 0;

        foreach ($values as $key => $value) {
            if (!Registry::exists($key)) {
                continue;
            }

            $cast = Registry::cast($key, $value);

            // Los booleanos se guardan como '0' / '1' (nunca como cadena vacía,
            // que el cargador interpretaría como "sin valor")
            $stored = match (true) {
                is_bool($cast)   => $cast ? '1' : '0',
                is_array($cast)  => json_encode($cast),
                default          => (string) $cast,
            };

            DB::table(self::TABLE)->updateOrInsert(
                ['name' => $key],
                [
                    'value'      => $stored,
                    'group'      => Registry::get($key)['group'] ?? 'general',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $saved++;
        }

        self::flush();

        return $saved;
    }

    /**
     * Guarda una sola opción.
     */
    public static function set(string $key, mixed $value): void
    {
        self::save([$key => $value]);
    }
}
