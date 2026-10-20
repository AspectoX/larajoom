<?php

namespace Aspectox\LaraJoom\Core\Modules;

use FilesystemIterator;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Descubrimiento de tipos de módulo (carpetas mod_*) en todo el paquete.
 *
 * Igual que en Joomla, los tipos de módulo se buscan en TODAS las carpetas
 * "modules" del paquete:
 *  - /modules
 *  - /administrator/modules
 *  - /components/com_x/modules
 *  - /administrator/components/com_x/modules
 *  - /plugins/.../modules
 */
class ModuleTypes
{
    /**
     * Nombres de carpeta excluidos al recorrer el paquete (no se entra en ellos).
     */
    protected const EXCLUDED_DIRS = [
        'vendor',
        'node_modules',
        '.git',
        'storage',
        'public',
    ];

    /**
     * Rutas (relativas a la raíz) excluidas al recorrer el paquete.
     */
    protected const EXCLUDED_PATHS = [
        'bootstrap/cache',
    ];

    /**
     * Raíz del paquete LaraJoom (donde viven administrator/, components/, media/).
     */
    public static function basePath(): string
    {
        return dirname(__DIR__, 3);
    }

    /**
     * Todas las carpetas "modules" encontradas dentro del paquete.
     *
     * @return array<string> Rutas absolutas ordenadas alfabéticamente.
     */
    public static function folders(): array
    {
        $base = self::basePath();

        if (!is_dir($base)) {
            return [];
        }

        $found = [];

        $directories = new RecursiveDirectoryIterator(
            $base,
            FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::UNIX_PATHS
        );

        // Se podan las carpetas excluidas durante el recorrido (no se entra en ellas).
        $filter = new RecursiveCallbackFilterIterator($directories, function ($current) {
            if (!$current->isDir()) {
                return true;
            }

            if (in_array($current->getFilename(), self::EXCLUDED_DIRS, true)) {
                return false;
            }

            return !self::isExcludedPath($current->getPathname());
        });

        $iterator = new RecursiveIteratorIterator($filter, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $item) {
            if (!$item->isDir() || $item->getFilename() !== 'modules') {
                continue;
            }

            $path = $item->getPathname();

            if (self::isExcludedPath($path)) {
                continue;
            }

            $found[$path] = $path;
        }

        $found = array_values($found);

        sort($found);

        return $found;
    }

    /**
     * Tipos de módulo instalados (carpetas mod_* con manifest.json).
     *
     * @return array<string,string> array clave => valor (nombre del tipo => nombre del tipo).
     */
    public static function types(): array
    {
        $types = [];

        foreach (self::folders() as $folder) {
            foreach (scandir($folder) ?: [] as $item) {
                if ($item === '.' || $item === '..' || !str_starts_with($item, 'mod_')) {
                    continue;
                }

                $path = $folder . DIRECTORY_SEPARATOR . $item;

                if (!is_dir($path) || !is_file($path . DIRECTORY_SEPARATOR . 'manifest.json')) {
                    continue;
                }

                $types[$item] = $item;
            }
        }

        ksort($types);

        return $types;
    }

    /**
     * Indica si una ruta absoluta cae dentro de las rutas excluidas.
     */
    protected static function isExcludedPath(string $path): bool
    {
        $base  = self::basePath();
        $rel   = ltrim(str_replace('\\', '/', substr($path, strlen($base))), '/');
        $parts = array_values(array_filter(explode('/', $rel), static fn ($p) => $p !== ''));

        if ($parts === []) {
            return false;
        }

        if (in_array($parts[0], self::EXCLUDED_DIRS, true)) {
            return true;
        }

        return in_array($rel, self::EXCLUDED_PATHS, true);
    }
}
