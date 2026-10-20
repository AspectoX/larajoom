<?php

namespace Aspectox\LaraJoom\Core;

use Illuminate\Support\Facades\DB;

class ConfigLoader
{
    /**
     * Carga la configuración desde larajoom_configuration.
     * Todos los grupos se aplanan por nombre (la columna name es única),
     * de modo que config('larajoom.campo') funcione para cualquier campo.
     */
    public static function load(): array
    {
        try {
            $rows = DB::table('larajoom_configuration')
                ->select('name', 'value', 'group')
                ->get();

            $base = config('larajoom', []);
            $config = [];

            foreach ($rows as $row) {
                // Un valor vacío en DB no pisa el default del archivo de config
                if ($row->value === '' && array_key_exists($row->name, $base)) {
                    continue;
                }

                $config[$row->name] = $row->value;
            }

            return array_merge($base, $config);
        } catch (\Exception $e) {
            return config('larajoom', []);
        }
    }

    public static function get(string $name, $default = null)
    {
        $row = DB::table('larajoom_configuration')->where('name', $name)->first();
        return $row ? $row->value : $default;
    }
}
