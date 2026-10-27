<?php

namespace Aspectox\LaraJoom\Admin\Components\com_installer\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PackageInstaller
{
    protected string $baseDir;

    protected array $coreElements = [
        'com_login', 'com_admin', 'com_config', 'com_menus', 'com_modules',
        'com_content', 'com_users', 'com_installer', 'com_actionlogs',
        'com_cpanel', 'com_media', 'com_templates',
        'mod_login', 'mod_menu', 'mod_title', 'mod_breadcrumbs',
    ];

    public function __construct()
    {
        $this->baseDir = dirname(__DIR__, 4);
    }

    /**
     * Escanea el disco y auto-registra en larajoom_extensions
     * todo lo instalado que ainda no esté Registrado (Discover automático).
     */
    public function syncFromDisk(): int
    {
        if (!DB::getSchemaBuilder()->hasTable('larajoom_extensions')) {
            return 0;
        }

        $registered = 0;
        $locations = [
            ['dir' => 'components',                  'type' => 'component', 'client' => 0],
            ['dir' => 'administrator/components',   'type' => 'component', 'client' => 1],
            ['dir' => 'modules',                    'type' => 'module',    'client' => 0],
            ['dir' => 'administrator/modules',      'type' => 'module',    'client' => 1],
            ['dir' => 'administrator/templates',    'type' => 'template',  'client' => 1],
        ];

        foreach ($locations as $location) {
            $dir = $this->baseDir . '/' . $location['dir'];
            if (!is_dir($dir)) continue;

            foreach (scandir($dir) as $item) {
                if ($item === '.' || $item === '..' || !is_dir($dir . '/' . $item)) continue;
                if ($location['dir'] === 'administrator/templates' && $item === 'system') continue;
                $manifestPath = $dir . '/' . $item . '/manifest.json';

                // Sin manifest: registrar igual (datos inferidos)
                if (!is_file($manifestPath)) {
                    $row = $this->buildRow($location['type'], $item, '', $location['client'], null);
                    $row->path = $location['dir'] . '/' . $item;

                    if ($this->upsertExtension($row, true)) {
                        $registered++;
                    }
                    continue;
                }

                $manifest = json_decode((string) file_get_contents($manifestPath), true);
                if (!is_array($manifest)) continue;

                $row = $this->buildRow($location['type'], $item, '', $location['client'], $manifest);
                $row->path = $location['dir'] . '/' . $item;

                if ($this->upsertExtension($row, true)) {
                    $registered++;
                }
            }
        }

        // Plugins: se registran solo bajo demanda (PackageInstaller::syncPluginsFromDisk()).

        return $registered;
    }

    /**
     * Escanea plugins/{group}/{name} y registra los que falten en la DB.
     * Solo registra plugins que tengan manifest.json.
     * Estructura Joomla: plugins/{grupo}/{nombre}/
     * Se ejecuta manualmente desde com_plugins ("Detectar plugins").
     */
    public function syncPluginsFromDisk(): int
    {
        if (!DB::getSchemaBuilder()->hasTable('larajoom_extensions')) {
            return 0;
        }

        $registered = 0;

        $pluginsDir = $this->baseDir . '/plugins';
        if (!is_dir($pluginsDir)) return 0;

        foreach (scandir($pluginsDir) as $group) {
            if ($group === '.' || $group === '..' || !is_dir($pluginsDir . '/' . $group)) continue;
            foreach (scandir($pluginsDir . '/' . $group) as $plugin) {
                if ($plugin === '.' || $plugin === '..' || !is_dir($pluginsDir . '/' . $group . '/' . $plugin)) continue;

                $manifestPath = $pluginsDir . '/' . $group . '/' . $plugin . '/manifest.json';
                if (!is_file($manifestPath)) continue;

                $manifest = json_decode((string) file_get_contents($manifestPath), true);
                if (!is_array($manifest)) continue;

                $row = $this->buildRow('plugin', $plugin, $group, 0, $manifest);
                $row->path = 'plugins/' . $group . '/' . $plugin;

                if ($this->upsertExtension($row, true)) {
                    $registered++;
                }
            }
        }

        return $registered;
    }

    /**
     * Instala un paquete desde zip subido, URL o carpeta local.
     */
    public function installFromUpload($uploadedFile): string
    {
        $tmpPath = sys_get_temp_dir() . '/larajoom_' . Str::random(10);
        mkdir($tmpPath, 0777, true);

        try {
            $zipPath = $tmpPath . '/' . $uploadedFile->getClientOriginalName();
            $uploadedFile->move($tmpPath, basename($zipPath));

            $zip = new \ZipArchive();
            if ($zip->open($zipPath) !== true) {
                throw new \RuntimeException('No se pudo abrir el archivo zip.');
            }
            $extractDir = $tmpPath . '/extracted';
            $zip->extractTo($extractDir);
            $zip->close();

            return $this->installFromDirectory($this->findManifestDir($extractDir));
        } finally {
            File::deleteDirectory($tmpPath);
        }
    }

    public function installFromUrl(string $url): string
    {
        $tmpPath = sys_get_temp_dir() . '/larajoom_' . Str::random(10);
        mkdir($tmpPath, 0777, true);

        try {
            $zipPath = $tmpPath . '/package.zip';
            $stream = @fopen($url, 'rb');
            if (!$stream) {
                throw new \RuntimeException('No se pudo descargar desde la URL indicada.');
            }
            file_put_contents($zipPath, $stream);
            fclose($stream);

            $zip = new \ZipArchive();
            if ($zip->open($zipPath) !== true) {
                throw new \RuntimeException('La descarga no es un archivo zip válido.');
            }
            $extractDir = $tmpPath . '/extracted';
            $zip->extractTo($extractDir);
            $zip->close();

            return $this->installFromDirectory($this->findManifestDir($extractDir));
        } finally {
            File::deleteDirectory($tmpPath);
        }
    }

    public function installFromDirectory(string $dir): string
    {
        $manifestPath = $dir . '/manifest.json';
        if (!is_file($manifestPath)) {
            throw new \RuntimeException('No se encontró manifest.json en el paquete.');
        }
        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        if (!is_array($manifest) || empty($manifest['name'])) {
            throw new \RuntimeException('El manifest.json está incompleto (falta "name").');
        }

        $this->verifyPackageSignature($dir, $manifest);

        $element = $manifest['name'];
        $type    = $this->resolveType($manifest, $element);
        $client  = $this->resolveClient($manifest, $element, $type);
        $folder  = '';

        if ($type === 'plugin') {
            $folder = $manifest['group'] ?? $manifest['folder'] ?? '';
            if ($folder === '') {
                throw new \RuntimeException('Los plugins requieren "group" en manifest.json.');
            }
        }

        // Destino dentro de la raíz donde corre el paquete
        $sourceInsidePackage = str_starts_with(
            realpath($dir) ? : (is_string($dir) ? $dir : ''),
            realpath($this->baseDir) ? : ''
        );

        if (!$sourceInsidePackage) {
            $destPath = $this->destinationPath($type, $element, $folder, $client);
            if (File::exists($destPath)) {
                if (empty($manifest['upgrade'])) {
                    // Actualización implícita: se sobreescribe igual que Joomla (method="upgrade")
                }
                File::copyDirectory($dir, $destPath);
            } else {
                File::makeDirectory($destPath, 0755, true);
                File::copyDirectory($dir, $destPath);
            }
            $relativePath = $this->relativeDestination($type, $element, $folder, $client);
        } else {
            $relativePath = str_replace('\\', '/', Str::after($dir, $this->baseDir . DIRECTORY_SEPARATOR));
        }

        // Migraciones del paquete
        $this->runMigrations($this->baseDir . '/' . $relativePath);

        // Registro en larajoom_extensions
        $row = $this->buildRow($type, $element, $folder, $client, $manifest);
        $row->path = $relativePath;
        $row->source = $dir;
        $row->dir = $dir;
        $row->dest = ($this->baseDir . '/' . $relativePath);
        $row->client = $client;
        $this->upsertExtension($row, false);

        // Instancia de módulo (como #__modules en Joomla)
        if ($type === 'module') {
            $table = 'larajoom_modules';
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $title = $manifest['title'] ?? $element;
                $exists = DB::table($table)->where('module', $element)->where('title', $title)->count();
                if (!$exists) {
                    DB::table($table)->insert([
                        'title'    => $title,
                        'module'   => $element,
                        'position' => $client ? 'menu' : 'top',
                        'published' => 0,
                        'ordering' => 0,
                        'showtitle' => 1,
                        'params'   => json_encode($manifest['params'] ?? []),
                        'access'   => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        return ucfirst($type) . ' "' . $element . '" instalado correctamente.';
    }

    public function uninstall($row): string
    {
        if (!empty($row->protected) || !empty($row->locked)) {
            throw new \RuntimeException('La extensión del núcleo no se puede desinstalar.');
        }

        if ($row->type === 'module') {
            DB::table('larajoom_modules')->where('module', $row->element)->delete();
        }

        DB::table('larajoom_extensions')->where('extension_id', $row->extension_id)->delete();
        if (isset($row->extension_id)) {
            unset($row->extension_id);
        }

        return 'Extensión "' . $row->element . '" desinstalada. (los archivos se conservan en disco)';
    }

    /**
     * Encuentra el directorio con manifest.json (root o un subdirectorio).
     */
    protected function findManifestDir(string $dir): string
    {
        if (is_file($dir . '/manifest.json')) {
            return $dir;
        }
        foreach (scandir($dir) ?: [] as $item) {
            if ($item === '.' || $item === '..') continue;
            $sub = $dir . '/' . $item;
            if (is_dir($sub) && is_file($sub . '/manifest.json')) {
                return $sub;
            }
        }
        return $dir;
    }

    protected function resolveType(array $manifest, string $element): string
    {
        $type = strtolower(trim((string) ($manifest['type'] ?? '')));

        return $type !== '' ? $type : $this->inferType($element);
    }

    protected function inferType(string $element): string
    {
        return match (true) {
            str_starts_with($element, 'com_') => 'component',
            str_starts_with($element, 'mod_') => 'module',
            str_starts_with($element, 'plg_') => 'plugin',
            default => 'component',
        };
    }

    protected function resolveClient(array $manifest, string $element, string $type): int
    {
        if (isset($manifest['client_id'])) {
            return (int) $manifest['client_id'];
        }
        if (isset($manifest['client'])) {
            return in_array(strtolower((string) $manifest['client']), ['admin', 'administrator']) ? 1 : 0;
        }
        if ($type === 'template') {
            return 1;
        }
        return 0;
    }

    protected function destinationPath(string $type, string $element, string $folder, int $client): string
    {
        return $this->baseDir . '/' . $this->relativeDestination($type, $element, $folder, $client);
    }

    protected function relativeDestination(string $type, string $element, string $folder, int $client): string
    {
        return match ($type) {
            'component' => $client ? 'administrator/components/' . $element : 'components/' . $element,
            'module'    => $client ? 'administrator/modules/' . $element : 'modules/' . $element,
            'plugin'    => 'plugins/' . $folder . '/' . $element,
            'template'  => 'administrator/templates/' . $element,
            default     => 'components/' . $element,
        };
    }

    protected function runMigrations(string $extensionPath): void
    {
        $migrationsPath = $extensionPath . '/database/migrations';
        if (is_dir($migrationsPath)) {
            Artisan::call('migrate', ['--path' => $migrationsPath, '--force' => true]);
        }
    }

    protected function buildRow(string $type, string $element, string $folder, int $client, ?array $manifest = null): object
    {
        $manifestCache = is_array($manifest) ? array_filter([
            'name'         => $manifest['name'] ?? $element,
            'type'         => $type,
            'creationDate' => $manifest['creationDate'] ?? ($manifest['creationdate'] ?? null),
            'author'       => $manifest['author'] ?? null,
            'authorEmail'  => $manifest['authorEmail'] ?? null,
            'authorUrl'    => $manifest['authorUrl'] ?? null,
            'version'      => $manifest['version'] ?? null,
            'description'  => $manifest['description'] ?? null,
            'group'        => $folder,
            'changelogurl' => $manifest['changelogurl'] ?? null,
            'namespace'    => $manifest['namespace'] ?? null,
        ], fn ($v) => $v !== null) : [];

        $row = new \stdClass();
        $row->package_id = 0;
        $row->name = ($manifest['title'] ?? ($manifest['lang_namespace'] ?? $element));
        $row->type = $type;
        $row->element = $element;
        $row->folder = $folder;
        $row->client_id = $client;
        $row->enabled = 1;
        $row->access = 1;
        $row->ordering = 0;
        $row->params = $manifest['params'] ?? null;
        $row->protected = (int) in_array($element, $this->coreElements);
        $row->locked = 0;
        $row->manifest_cache = $manifestCache;
        $row->state = 0;

        return $row;
    }

    protected function upsertExtension(object $row, bool $onlyMissing): bool
    {
        $existing = DB::table('larajoom_extensions')
            ->where('type', $row->type)
            ->where('element', $row->element)
            ->where('folder', $row->folder)
            ->where('client_id', $row->client_id)
            ->first(['extension_id', 'manifest_cache']);

        if ($existing) {
            if (!$onlyMissing) {
                DB::table('larajoom_extensions')
                    ->where('extension_id', $existing->extension_id)
                    ->update([
                        'manifest_cache' => json_encode($row->manifest_cache),
                        'params'         => $row->params ? json_encode($row->params) : null,
                        'updated_at'     => now(),
                    ]);
            }
            return false;
        }

        DB::table('larajoom_extensions')->insert([
            'package_id'     => $row->package_id,
            'name'           => substr($row->name, 0, 100),
            'type'           => $row->type,
            'element'        => substr($row->element, 0, 100),
            'folder'         => substr($row->folder, 0, 50),
            'client_id'      => $row->client_id,
            'enabled'        => $row->enabled,
            'access'         => $row->access,
            'ordering'       => $row->ordering,
            'params'         => $row->params ? json_encode($row->params) : null,
            'protected'      => $row->protected,
            'locked'         => $row->locked,
            'manifest_cache' => json_encode($row->manifest_cache),
            'state'          => $row->state,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // Menú lateral automático para componentes del admin
        if ($row->type === 'component' && $row->client_id === 1) {
            $this->registerAdminMenuItem($row->element);
        }

        return true;
    }

    protected function registerAdminMenuItem(string $element): void
    {
        $table = 'larajoom_menu';
        if (!DB::getSchemaBuilder()->hasTable($table)) {
            return;
        }

        $this->selfMenuItem($element);
    }

    protected function selfMenuItem(string $element): void
    {
        if ($element !== 'com_installer') {
            return;
        }

        $alias = 'installer';
        $exists = DB::table('larajoom_menu')
            ->where('menutype', 'mainmenu')
            ->where('alias', $alias)
            ->exists();

        if (!$exists) {
            DB::table('larajoom_menu')->insert([
                'menutype'  => 'mainmenu',
                'title'     => 'Instalador',
                'alias'     => $alias,
                'link'      => '/administrator/installer',
                'type'      => 'url',
                'published' => 1,
                'parent_id' => 0,
                'level'     => 1,
                'ordering'  => 4,
                'home'      => 0,
                'show_icon' => 1,
                'icon'      => 'plug',
                'access'    => 1,
            ]);
        }
    }
}
