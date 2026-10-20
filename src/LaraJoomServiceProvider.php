<?php

namespace Aspectox\LaraJoom;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Aspectox\LaraJoom\Core\ExtensionLoader;
use Aspectox\LaraJoom\Core\Positions\PositionRenderer;
use Aspectox\LaraJoom\Core\ConfigLoader;
use Aspectox\LaraJoom\Core\Middleware\LarajoomAcl;
use Aspectox\LaraJoom\Core\Middleware\LarajoomAuth;
use Aspectox\LaraJoom\Core\Plugins\PluginManager;
use Aspectox\LaraJoom\Core\Overrides\OverrideResolver;

class LaraJoomServiceProvider extends ServiceProvider
{
    protected ExtensionLoader $loader;

    public function register(): void
    {
        $baseDir = dirname(__DIR__);
        $this->loader = new ExtensionLoader($baseDir);

        $this->app->singleton(ExtensionLoader::class, fn() => $this->loader);
        $this->app->singleton(OverrideResolver::class, fn() => new OverrideResolver($baseDir));
        $this->app->singleton(PluginManager::class);

        // Load helpers from src/Core/helpers.php
        $helpersPath = __DIR__ . '/Core/helpers.php';
        if (file_exists($helpersPath)) {
            require_once $helpersPath;
        }
        // Load icon helper (function larajoom_icon)
        $iconHelperPath = __DIR__ . '/Core/IconHelper.php';
        if (file_exists($iconHelperPath)) {
            require_once $iconHelperPath;
        }
        // Load plugin helper (function larajoom_plugins)
        $pluginHelperPath = __DIR__ . '/Core/Plugins/helpers.php';
        if (file_exists($pluginHelperPath)) {
            require_once $pluginHelperPath;
        }
    }

    public function boot(): void
    {
        $baseDir = dirname(__DIR__);

        $this->loader->discoverAll();

        // Load package-level migrations (core + extensions discovered by loader)
        foreach ($this->loader->getMigrationPaths() as $migrationPath) {
            $this->loadMigrationsFrom($migrationPath);
        }
        $this->loadMigrationsFrom($baseDir . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations');
        $this->app->bind('seeders.larajoom', fn () => $baseDir . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_config' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seeders');

        $this->app['router']->aliasMiddleware('larajoom.auth', LarajoomAuth::class);
        $this->app['router']->aliasMiddleware('larajoom.acl', LarajoomAcl::class);

        // Language files registration\
        $adminLangDir = $baseDir . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'language';
        if (is_dir($adminLangDir)) {
            $this->loadTranslationsFrom($adminLangDir, 'larajoom_admin');
        }

        // Register language namespaces for templates (administrator/templates/<name>/language)
        $templatesDir = $baseDir . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'templates';
        if (is_dir($templatesDir)) {
            foreach (scandir($templatesDir) as $tplItem) {
                if ($tplItem === '.' || $tplItem === '..' || !is_dir($templatesDir . DIRECTORY_SEPARATOR . $tplItem)) continue;
                $tplManifestPath = $templatesDir . DIRECTORY_SEPARATOR . $tplItem . DIRECTORY_SEPARATOR . 'manifest.json';
                if (file_exists($tplManifestPath)) {
                    $tplManifest = json_decode(file_get_contents($tplManifestPath), true);
                    if (is_array($tplManifest) && ($tplManifest['type'] ?? null) === 'template') {
                        $tplLangPath = $templatesDir . DIRECTORY_SEPARATOR . $tplItem . DIRECTORY_SEPARATOR . 'language';
                        if (is_dir($tplLangPath)) {
                            $this->loadTranslationsFrom($tplLangPath, 'tmp');
                        }
                    }
                }
            }
        }

        // Auto-register from individual extension manifests
        $searchDirs = [
            $baseDir . DIRECTORY_SEPARATOR . 'components',
            $baseDir . DIRECTORY_SEPARATOR . 'modules',
            $baseDir . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components',
            $baseDir . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'modules',
        ];
        foreach ($searchDirs as $dir) {
            if (!is_dir($dir)) continue;
            foreach (scandir($dir) as $item) {
                if ($item === '.' || $item === '..') continue;
                $manifestPath = $dir . DIRECTORY_SEPARATOR . $item . DIRECTORY_SEPARATOR . 'manifest.json';
                if (file_exists($manifestPath)) {
                    $manifest = json_decode(file_get_contents($manifestPath), true);
                    if (is_array($manifest)) {
                        $type = $manifest['type'] ?? null;
                        // Los templates se registran en ExtensionLoader::discoverTemplates()
                        if ($type !== 'template' && !empty($manifest['view_namespace'])) {
                            // Priorizar view_path del manifest; si no existe, derivar de view_namespace
                            $path = !empty($manifest['view_path'])
                                ? $baseDir . DIRECTORY_SEPARATOR . trim($manifest['view_path'], DIRECTORY_SEPARATOR)
                                : $baseDir . DIRECTORY_SEPARATOR . strtolower(str_replace('.', DIRECTORY_SEPARATOR, $manifest['view_namespace']));
                            if (is_dir($path)) {
                                View::addNamespace($manifest['view_namespace'], $path);
                            }
                        }
                        $langNs = match($type) {
                            'template'  => 'tmp',
                            'module'    => 'mdl',
                            'component' => $manifest['lang_namespace'] ?? $item,
                            'plugin'    => 'pgi',
                            default     => null,
                        };
                        if ($langNs) {
                            $extLangPath = $dir . DIRECTORY_SEPARATOR . $item . DIRECTORY_SEPARATOR . 'language';
                            if (is_dir($extLangPath)) {
                                $this->loadTranslationsFrom($extLangPath, $langNs);
                            }
                        }
                    }
                }
            }
        }

        // Plugins (nested: plugins/<group>/<name>/)
        $pluginsDir = $baseDir . DIRECTORY_SEPARATOR . 'plugins';
        if (is_dir($pluginsDir)) {
            foreach (scandir($pluginsDir) as $group) {
                if ($group === '.' || $group === '..') continue;
                $groupDir = $pluginsDir . DIRECTORY_SEPARATOR . $group;
                if (!is_dir($groupDir)) continue;
                foreach (scandir($groupDir) as $pluginName) {
                    if ($pluginName === '.' || $pluginName === '..') continue;
                    $pluginDir = $groupDir . DIRECTORY_SEPARATOR . $pluginName;
                    $manifestPath = $pluginDir . DIRECTORY_SEPARATOR . 'manifest.json';
                    if (file_exists($manifestPath)) {
                        $manifest = json_decode(file_get_contents($manifestPath), true);
                        if (is_array($manifest) && !empty($manifest['view_namespace'])) {
                            $path = $baseDir . DIRECTORY_SEPARATOR . strtolower(str_replace('.', DIRECTORY_SEPARATOR, $manifest['view_namespace']));
                            if (is_dir($path)) {
                                View::addNamespace($manifest['view_namespace'], $path);
                            }
                        }
                        $langPath = $pluginDir . DIRECTORY_SEPARATOR . 'language';
                        if (is_dir($langPath)) {
                            $this->loadTranslationsFrom($langPath, 'pgi');
                        }
                    }
                }
            }
        }

        // Register admin auth guard
        // LaraJoom usa su propia tabla de usuarios (larajoom_users, estilo Joomla),
        // la misma que el sitio y que el mapa de roles.
        config()->set('auth.guards.ax-larajoom', ['driver' => 'session', 'provider' => 'ax-larajoom-users']);
        config()->set('auth.providers.ax-larajoom-users', ['driver' => 'eloquent', 'model' => 'Aspectox\LaraJoom\Admin\Components\com_users\Models\User']);

        // Blade directives (like AxAdminLara)
        Blade::directive('icon', function ($expression) {
            return "<?php echo function_exists('larajoom_icon') ? larajoom_icon({$expression}) : ''; ?>";
        });
        Blade::directive('position', function ($expression) {
            return "<?php echo \\Aspectox\\LaraJoom\\Core\\Positions\\PositionRenderer::render({$expression}); ?>";
        });

        // Shared components registration from media/system/components
        $systemComponents = $baseDir . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'components';
        if (is_dir($systemComponents)) {
            Blade::anonymousComponentPath($systemComponents, 'larajoom');
        }

        // Load configuration from DB into Laravel config
        $dbConfig = ConfigLoader::load();
        if (!empty($dbConfig)) {
            config()->set('larajoom', $dbConfig);
        }

        // Aplica al config de la app los overrides que son ajustes del framework
        // (sesión, cache, mail, debug, zona horaria, error_reporting).
        // Cada ajuste se valida y se aplica de forma independiente: nunca rompen la app.
        \Aspectox\LaraJoom\Core\Settings\FrameworkConfig::apply();

        // Auto-discover package seeders (site + admin + root)
        $seederDirs = [
            $baseDir . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seeders',
            $baseDir . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_config' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seeders',
            $baseDir . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_content' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seeders',
            $baseDir . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_users' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seeders',
            $baseDir . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_menus' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seeders',
        ];
        foreach ($seederDirs as $dir) {
            if (is_dir($dir)) {
                foreach (glob($dir . '/*.php') as $seederFile) {
                    $className = basename($seederFile, '.php');
                    $fullClass = 'Database\\Seeders\\' . $className;
                    if (!class_exists($fullClass)) {
                        require_once $seederFile;
                    }
                }
            }
        }

        // Site routes (the admin routes of each component/com_ are loaded by the ExtensionLoader)
        $siteRoutePath = $baseDir . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php';
        if (file_exists($siteRoutePath)) {
            $this->loadRoutesFrom($siteRoutePath);
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $baseDir . DIRECTORY_SEPARATOR . 'media' => public_path('media'),
            ], ['larajoom-all-assets', 'larajoom-assets']);
        }
    }
}






