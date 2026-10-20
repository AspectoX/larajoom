<?php

namespace Aspectox\LaraJoom\Core;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class ExtensionLoader
{
    protected string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
    }

    /**
     * Ruta base del paquete LaraJoom (donde viven administrator/, components/, media/).
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function discoverAll(): void
    {
        $this->discoverTemplates();
        $this->discoverSiteComponents();
        $this->discoverSiteModules();
        $this->discoverAdminComponents();
        $this->discoverAdminModules();
        $this->discoverPlugins();
    }

    /**
     * Registra los templates (administrator/templates/*) como namespaces de vista.
     * - 'system'  → layouts base del sistema (administrator/templates/system)
     * - 'admin_tpl', 'site_tpl', 'tpl' → template activo, para cualquier template
     */
    protected function discoverTemplates(): void
    {
        $dir = $this->basePath . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'templates';
        if (!is_dir($dir)) return;

        $activeTemplate = null;

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..' || !is_dir($dir . DIRECTORY_SEPARATOR . $item)) continue;

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            $name = $item;

            $manifestPath = $path . DIRECTORY_SEPARATOR . 'manifest.json';
            if (file_exists($manifestPath)) {
                $manifest = json_decode(file_get_contents($manifestPath), true);
                if (is_array($manifest) && !empty($manifest['name'])) {
                    $name = $manifest['name'];
                }
            }

            // Cada template con su propio nombre: system::layout_login, atum::index, etc.
            View::addNamespace($name, $path);
            View::addNamespace('larajoom_' . $name, $path);

            // El template del sistema no es un template de interfaz
            if ($name === 'system') continue;

            if ($activeTemplate === null) {
                $activeTemplate = $path;
                View::addNamespace('admin_tpl', $path);
                View::addNamespace('site_tpl', $path);
                View::addNamespace('tpl', $path);
            }
        }
    }

    protected function discoverSiteComponents(): void
    {
        $dir = $this->basePath . DIRECTORY_SEPARATOR . 'components';
        if (!is_dir($dir)) return;

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..' || !str_starts_with($item, 'com_')) continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->registerExtensionResources($path, 'components.' . $item, 'web');
            }
        }
    }

    protected function discoverSiteModules(): void
    {
        $dir = $this->basePath . DIRECTORY_SEPARATOR . 'modules';
        if (!is_dir($dir)) return;

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..' || !str_starts_with($item, 'mod_')) continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->registerExtensionResources($path, 'modules.' . $item, 'web');
            }
        }
    }

    protected function discoverAdminComponents(): void
    {
        $dir = $this->basePath . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components';
        if (!is_dir($dir)) return;

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..' || !str_starts_with($item, 'com_')) continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->registerExtensionResources($path, 'admin.components.' . $item, 'web', 'administrator');

                // Componentes admin: las rutas viven en routes/admin.php
                $adminRoutesPath = $path . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'admin.php';
                if (file_exists($adminRoutesPath)) {
                    Route::middleware('web')->prefix('administrator')->group($adminRoutesPath);
                }
            }
        }
    }

    protected function discoverAdminModules(): void
    {
        $dir = $this->basePath . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'modules';
        if (!is_dir($dir)) return;

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..' || !str_starts_with($item, 'mod_')) continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->registerExtensionResources($path, 'admin.modules.' . $item, 'web', 'administrator');
            }
        }
    }

    protected function discoverPlugins(): void
    {
        $dir = $this->basePath . DIRECTORY_SEPARATOR . 'plugins';
        if (!is_dir($dir)) return;

        foreach (scandir($dir) as $group) {
            if ($group === '.' || $group === '..' || str_contains($group, '.')) continue;
            $groupPath = $dir . DIRECTORY_SEPARATOR . $group;
            if (!is_dir($groupPath)) continue;

            foreach (scandir($groupPath) as $plugin) {
                if ($plugin === '.' || $plugin === '..') continue;
                $pluginPath = $groupPath . DIRECTORY_SEPARATOR . $plugin;
                if (is_dir($pluginPath)) {
                    $this->registerExtensionResources($pluginPath, "plugins.{$group}.{$plugin}", 'web');
                }
            }
        }
    }

    protected function registerExtensionResources(string $path, string $viewNamespace, string $middleware = 'web', ?string $prefix = null): void
    {
        $routesPath = $path . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php';
        if (file_exists($routesPath)) {
            $routeGroup = Route::middleware($middleware);
            if ($prefix) {
                $routeGroup = $routeGroup->prefix($prefix);
            }
            $routeGroup->group($routesPath);
        }

        $viewsPath = $path . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
        if (is_dir($viewsPath)) {
            View::addNamespace($viewNamespace, $viewsPath);
        }

        $tmplPath = $path . DIRECTORY_SEPARATOR . 'tmpl';
        if (is_dir($tmplPath)) {
            View::addNamespace($viewNamespace, $tmplPath);
        }
    }

    public function getMigrationPaths(): array
    {
        $paths = [];
        $searchDirs = [
            $this->basePath . DIRECTORY_SEPARATOR . 'components',
            $this->basePath . DIRECTORY_SEPARATOR . 'modules',
            $this->basePath . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components',
            $this->basePath . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'modules',
        ];

        foreach ($searchDirs as $parent) {
            if (!is_dir($parent)) continue;
            foreach (scandir($parent) as $item) {
                if ($item === '.' || $item === '..') continue;
                $migrationDir = $parent . DIRECTORY_SEPARATOR . $item . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
                if (is_dir($migrationDir)) {
                    $paths[] = $migrationDir;
                }
            }
        }

        return $paths;
    }
}
