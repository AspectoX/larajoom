<?php

namespace Aspectox\LaraJoom\Core\Settings;

use Illuminate\Support\Facades\DB;

/**
 * Nombre del sitio.
 *
 * Prioridad de resolución:
 *   1. Base de datos: tabla larajoom_configuration, fila name = 'sitename'.
 *   2. Fallback: nombre de la aplicación de Laravel (config/app.php → 'name' => env('APP_NAME', 'Laravel')).
 */
class SiteName
{
    /**
     * Devuelve el nombre del sitio: el valor guardado en la base de datos o,
     * si no existe o está vacío, el nombre de la aplicación (APP_NAME).
     */
    public static function get(): string
    {
        $stored = '';

        try {
            $stored = trim((string) DB::table('larajoom_configuration')
                ->where('name', 'sitename')
                ->value('value'));
        } catch (\Throwable $e) {
            $stored = '';
        }

        if ($stored !== '') {
            return $stored;
        }

        return trim((string) config('app.name'));
    }
}
