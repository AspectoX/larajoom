<?php

namespace Aspectox\LaraJoom\Admin\Components\com_config\Controllers;

use Aspectox\LaraJoom\Core\Access;
use Aspectox\LaraJoom\Core\Settings\Settings;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfigController extends Controller
{
    /**
     * Mapa campo => grupo de configuración (larajoom_configuration.group)
     * Nombres y grupos alineados con el com_config de Joomla 6.
     */
    protected const FIELDS = [
        'site' => [
            'sitename', 'offline', 'display_offline_message', 'offline_message', 'offline_image',
            'frontediting', 'editor', 'captcha', 'access', 'list_limit', 'feed_limit', 'feed_email',
            'cookie_domain', 'cookie_path',
        ],
        'metadata' => [
            'MetaDesc', 'robots', 'MetaRights', 'MetaAuthor',
        ],
        'seo' => [
            'sef', 'sef_rewrite', 'sef_suffix', 'unicodeslugs', 'sitename_pagetitles',
        ],
        'cache' => [
            'caching', 'cache_handler', 'cache_platformprefix', 'cachetime', 'cache_path',
            'memcached_persist', 'memcached_compress', 'memcached_server_host', 'memcached_server_port',
            'redis_persist', 'redis_server_host', 'redis_server_port', 'redis_server_auth', 'redis_server_db',
        ],
        'debug' => [
            'debug', 'debug_lang', 'debug_lang_const',
        ],
        'webservices' => [
            'cors', 'cors_allow_origin', 'cors_allow_headers', 'cors_allow_methods',
        ],
        'proxy' => [
            'behind_loadbalancer', 'proxy_enable', 'proxy_host', 'proxy_port', 'proxy_user', 'proxy_pass',
        ],
        'locale' => [
            'offset',
        ],
        'mail' => [
            'mailonline', 'massmailoff', 'mailfrom', 'fromname', 'replyto', 'replytoname',
            'mailer', 'sendmail', 'smtphost', 'smtpport', 'smtpsecure', 'smtpauth', 'smtpuser', 'smtppass',
        ],
        'server' => [
            'tmp_path', 'gzip', 'error_reporting', 'force_ssl',
        ],
        'session' => [
            'session_handler', 'session_filesystem_path',
            'session_memcached_server_host', 'session_memcached_server_port',
            'session_redis_persist', 'session_redis_server_host', 'session_redis_server_port',
            'session_redis_server_auth', 'session_redis_server_db',
            'lifetime', 'shared_session', 'session_metadata', 'session_metadata_for_guest',
        ],
        'logging' => [
            'log_path', 'log_everything', 'log_deprecated', 'log_priorities', 'log_categories', 'log_category_mode',
        ],
        'filters' => [
            'filters',
        ],
    ];

    /**
     * Acciones de permisos (Joomla 6) => etiqueta traducida
     */
    protected const ACTIONS = [
        'core.login.site'    => 'action_login_site',
        'core.login.admin'   => 'action_login_admin',
        'core.login.api'     => 'action_login_api',
        'core.login.offline' => 'action_login_offline',
        'core.admin'         => 'action_admin',
        'core.options'       => 'action_options',
        'core.manage'        => 'action_manage',
        'core.create'        => 'action_create',
        'core.delete'        => 'action_delete',
        'core.edit'          => 'action_edit',
        'core.edit.state'    => 'action_edit_state',
        'core.edit.own'      => 'action_edit_own',
        'core.edit.value'    => 'action_edit_value',
    ];

    /**
     * Configuración global - vista principal con pestañas
     */
    public function index()
    {
        return view('admin.components.com_config::index', [
            'config'    => $this->getConfigGrouped(),
            'activeTab' => request('tab', 'site'),
            'groups'    => $this->getGroups(),
            'actions'   => $this->getActions(),
            'rules'     => $this->getRules(),
            'assets'    => $this->getAssets(),
            'assetRules' => $this->getAssetRules(),
            'filters'   => $this->getFilters(),
        ]);
    }

    /**
     * Guarda todos los campos enviados por el formulario de configuración
     */
    public function save(Request $request)
    {
        $values = [];

        foreach (self::FIELDS as $group => $fields) {
            foreach ($fields as $field) {
                if (!$request->has($field)) {
                    continue;
                }

                // El tipo y el ámbito salen del registro (Settings\Registry)
                $values[$field] = $request->input($field);
            }
        }

        $saved = Settings::save($values);

        $this->savePermissions($request->input('permissions', []));

        return redirect()
            ->route('larajoom.admin.config.index')
            ->with('success', __('com_config::com_config.saved', ['count' => $saved]));
    }

    /**
     * Guarda la matriz de permisos por componente y grupo.
     *
     * Cada celda es un nivel:
     *   0 = sin acceso | 1 = leer | 2 = leer y escribir | 3 = leer, escribir y borrar
     */
    protected function savePermissions(array $permissions): void
    {
        if (!DB::getSchemaBuilder()->hasTable('larajoom_assets') || empty($permissions)) {
            return;
        }

        foreach ($permissions as $assetName => $groups) {
            if (!is_array($groups)) {
                continue;
            }

            $asset = DB::table('larajoom_assets')->where('name', $assetName)->first();

            if (!$asset) {
                continue;
            }

            $decoded = json_decode($asset->rules ?? '{}', true);
            $rules = is_array($decoded) ? $decoded : [];

            foreach ($groups as $groupId => $tier) {
                $groupId = (string) (int) $groupId;
                $tier = (int) $tier;

                // Se recalculan todas las acciones del grupo según el nivel elegido
                foreach (Access::allActions() as $action) {
                    unset($rules[$action][$groupId]);
                }

                foreach (Access::actionsForTier($tier) as $action) {
                    $rules[$action][$groupId] = 1;
                }
            }

            // Limpia las entradas vacías
            foreach ($rules as $action => $groupRules) {
                if (empty($groupRules)) {
                    unset($rules[$action]);
                }
            }

            DB::table('larajoom_assets')
                ->where('id', $asset->id)
                ->update(['rules' => json_encode($rules), 'updated_at' => now()]);
        }
    }

    /**
     * Text filters configurados por grupo
     */
    protected function getFilters(): array
    {
        $raw = config('larajoom.filters', '');

        if (is_array($raw)) {
            return $raw;
        }

        if (!is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Configuración agrupada por pestañas
     */
    protected function getConfigGrouped(): array
    {
        $config = DB::table('larajoom_configuration')
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy('group')
            ->toArray();

        return [
            'site'        => $config['site'] ?? [],
            'metadata'    => $config['metadata'] ?? [],
            'seo'         => $config['seo'] ?? [],
            'system'      => $config['cache'] ?? [],
            'server'      => $config['server'] ?? [],
            'logging'     => $config['logging'] ?? [],
            'filters'     => $config['filters'] ?? [],
            'permissions' => $config['permissions'] ?? [],
        ];
    }

    /**
     * Grupos de usuarios para la matriz de permisos
     */
    protected function getGroups(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('larajoom_usergroups')) {
            return [];
        }

        return DB::table('larajoom_usergroups')->orderBy('id')->get()->all();
    }

    /**
     * Acciones de permisos traducidas
     */
    protected function getActions(): array
    {
        $actions = [];

        foreach (self::ACTIONS as $action => $labelKey) {
            $actions[$action] = __('com_config::com_config.' . $labelKey);
        }

        return $actions;
    }

    /**
     * Permisos actuales del asset com_config
     */
    protected function getRules(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('larajoom_assets')) {
            return [];
        }

        $asset = DB::table('larajoom_assets')->where('name', 'com_config')->first();

        if (!$asset || !$asset->rules) {
            return [];
        }

        $rules = json_decode($asset->rules, true);

        return is_array($rules) ? $rules : [];
    }

    /**
     * Assets (componentes) del sistema
     */
    protected function getAssets(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('larajoom_assets')) {
            return [];
        }

        return DB::table('larajoom_assets')->orderBy('id')->get()->all();
    }

    /**
     * Reglas de permisos de todos los assets (nombre => reglas)
     */
    protected function getAssetRules(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('larajoom_assets')) {
            return [];
        }

        $rules = [];

        foreach (DB::table('larajoom_assets')->get() as $asset) {
            $decoded = json_decode($asset->rules ?? '{}', true);
            $rules[$asset->name] = is_array($decoded) ? $decoded : [];
        }

        return $rules;
    }

    public function edit(string $name, string $group = 'general')
    {
        $item = DB::table('larajoom_configuration')
            ->where('name', $name)
            ->where('group', $group)
            ->first();

        return view('admin.components.com_config::edit', compact('item'));
    }

    public function update(Request $request, string $name, string $group = 'general')
    {
        $data = $request->validate([
            'value' => 'nullable|string',
        ]);

        // El tipo sale del registro (Settings\Registry)
        Settings::set($name, $data['value']);

        return redirect()->route('larajoom.admin.config.index')->with('success', 'Actualizado');
    }
}
