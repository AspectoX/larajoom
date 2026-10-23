<?php

namespace Aspectox\LaraJoom\Core\Plugins;

use Aspectox\LaraJoom\Core\Resilience\SafeRunner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PluginManager
{
    protected string $baseDir;

    protected array $resolved = [];

    public function __construct()
    {
        $this->baseDir = dirname(__DIR__, 2);
    }

    /**
     * Despacha un evento de plugin a todos los plugins habilitados del grupo.
     *
     * Ej.: dispatch('content', 'onContentPrepare', ['com_content.article', &$article, $params])
     *
     * Los argumentos por referencia se respetan (los plugins pueden modificarlos).
     */
    public function dispatch(string $group, string $method, array $args = []): array
    {
        $results = [];

        if (!Schema::hasTable('larajoom_extensions')) {
            return $results;
        }

        $plugins = DB::table('larajoom_extensions')
            ->where('type', 'plugin')
            ->where('folder', $group)
            ->where('enabled', 1)
            ->orderBy('ordering')
            ->orderBy('element')
            ->get(['element', 'folder', 'params']);

        foreach ($plugins as $row) {
            $plugin = $this->resolve($row->folder, $row->element);

            if ($plugin === null || !method_exists($plugin, $method)) {
                continue;
            }

            $result = SafeRunner::execute(
                "plg_{$row->folder}_{$row->element}",
                function () use ($plugin, $method, &$args) {
                    return $plugin->{$method}(...$args);
                },
                null
            );

            if ($result !== null) {
                $results[$row->folder . '.' . $row->element] = $result;
            }
        }

        return $results;
    }

    /**
     * Instancia el plugin {group}/{element} parseando su archivo de clase
     * (los namespaces de los plugins no siguen una convención uniforme).
     */
    public function resolve(string $group, string $element): ?object
    {
        $key = $group . '.' . $element;

        if (array_key_exists($key, $this->resolved)) {
            return $this->resolved[$key];
        }

        $base = $this->baseDir
            . DIRECTORY_SEPARATOR . 'plugins'
            . DIRECTORY_SEPARATOR . $group
            . DIRECTORY_SEPARATOR . $element;

        $this->resolved[$key] = null;

        foreach (['Extension', 'src' . DIRECTORY_SEPARATOR . 'Extension'] as $sub) {
            $dir = $base . DIRECTORY_SEPARATOR . $sub;
            if (!is_dir($dir)) continue;

            foreach (scandir($dir) ?: [] as $file) {
                if ($file === '.' || $file === '..' || !str_ends_with($file, '.php')) continue;

                $path = $dir . DIRECTORY_SEPARATOR . $file;
                $class = $this->classFromFile($path);

                if ($class === null) continue;

                if (!class_exists($class, false)) {
                    require_once $path;
                }

                if (class_exists($class)) {
                    $this->resolved[$key] = new $class();
                    return $this->resolved[$key];
                }
            }
        }

        return null;
    }

    /**
     * Extrae namespace + nombre de clase de un archivo PHP.
     */
    protected function classFromFile(string $path): ?string
    {
        $source = file_get_contents($path);
        if ($source === false) return null;

        if (!preg_match('/namespace\s+([\w\\\\]+)\s*;/', $source, $ns)) {
            return null;
        }

        if (!preg_match('/\bclass\s+(\w+)/', $source, $cls)) {
            return null;
        }

        return $ns[1] . '\\' . $cls[1];
    }

    /**
     * Obtiene todos los plugins de un grupo (instalados, no necesariamente habilitados).
     */
    public function getPluginsByGroup(string $group): array
    {
        if (!Schema::hasTable('larajoom_extensions')) {
            return [];
        }

        $plugins = DB::table('larajoom_extensions')
            ->where('type', 'plugin')
            ->where('folder', $group)
            ->orderBy('ordering')
            ->orderBy('element')
            ->get(['element', 'folder', 'params', 'enabled']);

        $result = [];
        foreach ($plugins as $row) {
            $plugin = $this->resolve($row->folder, $row->element);
            if ($plugin !== null) {
                $result[] = $plugin;
            }
        }

        return $result;
    }

    /**
     * Obtiene solo los plugins habilitados de un grupo.
     */
    public function getEnabledPluginsByGroup(string $group): array
    {
        return array_filter($this->getPluginsByGroup($group), fn ($p) => $p->isEnabled());
    }
}
