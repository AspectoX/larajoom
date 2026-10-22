<?php

namespace Aspectox\LaraJoom\Core;

use Aspectox\LaraJoom\Core\Settings\Registry;
use Aspectox\LaraJoom\Core\Settings\Settings;
use Illuminate\Support\Facades\DB;

/**
 * Fachada de la configuración global.
 *
 * La carga real la hace Settings\Settings (tabla larajoom_configuration con
 * los tipos del registro); esta clase mantiene la API histórica y el merge con
 * los defaults del archivo de config.
 */
class ConfigLoader
{
    /**
     * Carga la configuración global.
     *
     * Prioridad: valores guardados en la base > defaults del archivo de config
     * > defaults del registro.
     */
    public static function load(): array
    {
        $base = config('larajoom', []);
        $stored = Settings::stored();

        $config = [];

        foreach ($stored as $key => $value) {
            // Un valor vacío en DB no pisa el default del archivo de config
            if ($value === '' && array_key_exists($key, $base)) {
                continue;
            }

            // El nombre del sitio vacío tampoco pisa el default: se mantiene
            // el nombre de la aplicación de Laravel (APP_NAME del .env).
            if ($key === 'sitename' && $value === '') {
                continue;
            }

            $config[$key] = $value;
        }

        return array_merge(Registry::defaults(), $base, $config);
    }

    /**
     * Valor tipado de una opción (usa el registro para el tipo).
     */
    public static function get(string $name, $default = null)
    {
        if (Registry::exists($name)) {
            return Settings::get($name, $default);
        }

        $row = DB::table(Settings::TABLE)->where('name', $name)->first();

        return $row ? $row->value : $default;
    }

    /**
     * Guarda una opción.
     */
    public static function set(string $name, $value): void
    {
        Settings::set($name, $value);
    }
}
