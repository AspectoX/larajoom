<?php

namespace Aspectox\LaraJoom\Core;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Control de acceso (ACL) de LaraJoom, al estilo Joomla 6 pero con tres niveles:
 *
 *   0 = sin acceso
 *   1 = Leer                 (core.manage)
 *   2 = Leer y escribir       (+ core.create, core.edit, core.edit.state, core.edit.own)
 *   3 = Leer, escribir y borrar (+ core.delete, core.admin)
 *
 * Las reglas viven en `larajoom_assets.rules` con el formato de Joomla:
 *   {"core.manage":{"7":1,"8":1},"core.edit":{"7":1,"8":1}, ...}
 */
class Access
{
    public const TIER_NONE   = 0;
    public const TIER_READ   = 1;
    public const TIER_WRITE  = 2;
    public const TIER_DELETE = 3;

    /** Acciones que otorgan "leer" */
    public const READ_ACTIONS = ['core.manage'];

    /** Acciones que otorgan "escribir" */
    public const WRITE_ACTIONS = ['core.create', 'core.edit', 'core.edit.state', 'core.edit.own'];

    /** Acciones que otorgan "borrar" */
    public const DELETE_ACTIONS = ['core.delete'];

    /** Grupo con todos los permisos (bypasea el ACL, como en Joomla) */
    public const SUPER_ADMIN_GROUP = 9;

    /** Grupos con los que se puede entrar al panel de administración */
    public const ADMIN_GROUPS = [4, 5, 6, 7, 8, 9];

    /** @var array<int>|null Ids de los grupos del usuario */
    protected static ?array $groupIds = null;

    /** @var array<string, array>|null Reglas por asset */
    protected static ?array $rules = null;

    /**
     * Ids de los grupos a los que pertenece el usuario actual.
     *
     * @return array<int>
     */
    public static function userGroupIds(): array
    {
        if (self::$groupIds !== null) {
            return self::$groupIds;
        }

        $groupIds = [];

        $user = Auth::guard('ax-larajoom')->user() ?? Auth::user();

        if ($user && Schema::hasTable('larajoom_user_usergroup_map')) {
            $groupIds = DB::table('larajoom_user_usergroup_map')
                ->where('user_id', $user->getKey())
                ->pluck('group_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return self::$groupIds = $groupIds;
    }

    /**
     * Nivel de permisos del usuario sobre un asset (componente).
     */
    public static function tier(string $asset): int
    {
        if (self::isSuperAdmin()) {
            return self::TIER_DELETE;
        }

        $rules = self::rulesFor($asset);

        if (empty($rules)) {
            // Sin asset configurado: se deniega por defecto (deny-by-default)
            return self::TIER_NONE;
        }

        $tier = self::TIER_NONE;

        foreach (self::userGroupIds() as $groupId) {
            $groupId = (string) $groupId;

            if (!empty($rules['core.admin'][$groupId])) {
                return self::TIER_DELETE;
            }

            if (self::hasAny($rules, self::READ_ACTIONS, $groupId)) {
                $tier = max($tier, self::TIER_READ);
            }

            if (self::hasAll($rules, self::WRITE_ACTIONS, $groupId)) {
                $tier = max($tier, self::TIER_WRITE);
            }

            if (!empty($rules['core.delete'][$groupId])) {
                $tier = max($tier, self::TIER_DELETE);
            }
        }

        return $tier;
    }

    public static function canRead(string $asset): bool
    {
        return self::tier($asset) >= self::TIER_READ;
    }

    public static function canWrite(string $asset): bool
    {
        return self::tier($asset) >= self::TIER_WRITE;
    }

    public static function canDelete(string $asset): bool
    {
        return self::tier($asset) >= self::TIER_DELETE;
    }

    /**
     * Comprueba una acción concreta sobre un asset.
     */
    public static function can(string $asset, string $action): bool
    {
        if (self::isSuperAdmin()) {
            return true;
        }

        $rules = self::rulesFor($asset);

        if (empty($rules)) {
            return false;
        }

        foreach (self::userGroupIds() as $groupId) {
            $groupId = (string) $groupId;

            if (!empty($rules['core.admin'][$groupId])) {
                return true;
            }

            if (!empty($rules[$action][$groupId])) {
                return true;
            }
        }

        return false;
    }

    /**
     * ¿El usuario tiene acceso a un nivel de vista (los items de menú)?
     *
     * @param array<int>|null $groupIds Grupos a evaluar; por defecto los del usuario.
     *                                    En el sitio se debe pasar siteGroupIds().
     */
    public static function canAccessLevel(?int $levelId, ?array $groupIds = null): bool
    {
        if (empty($levelId) || $levelId <= 1) {
            return true;
        }

        if (!Schema::hasTable('larajoom_viewlevels')) {
            return true;
        }

        $level = DB::table('larajoom_viewlevels')->where('id', $levelId)->first();

        if (!$level) {
            // Nivel inexistente: no se oculta el item
            return true;
        }

        $groups = json_decode($level->rules ?? '[]', true) ?: [];
        $groupIds = $groupIds ?? self::userGroupIds();

        foreach ($groupIds as $groupId) {
            if (in_array((string) $groupId, array_map('strval', $groups), true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Acceso a un nivel de vista en el SITIO (usuarios que cambian roles
     * entran como invitados y no ven contenido restringido).
     */
    public static function siteCanAccessLevel(?int $levelId): bool
    {
        return self::canAccessLevel($levelId, self::siteGroupIds());
    }

    /**
     * Todos los niveles de vista disponibles con sus títulos (para selects).
     *
     * @return array<int, string>
     */
    public static function allAccessLevels(): array
    {
        // Todos los roles del sistema
        if (Schema::hasTable('larajoom_usergroups')) {
            $roles = DB::table('larajoom_usergroups')->orderBy('id')->pluck('title', 'id')->all();
            if ($roles) {
                return $roles;
            }
        }

        // Respaldo: niveles de acceso
        if (Schema::hasTable('larajoom_viewlevels')) {
            $levels = DB::table('larajoom_viewlevels')->orderBy('id')->pluck('title', 'id')->all();
            if ($levels) {
                return $levels;
            }
        }

        return self::defaultAccessLevels();
    }

    /**
     * Niveles de acceso por defecto (estilo Joomla).
     *
     * @return array<int, string>
     */
    public static function defaultAccessLevels(): array
    {
        return [
            1 => 'Public',
            2 => 'Registered',
            3 => 'Special',
            4 => 'Guest',
            5 => 'Super Users',
        ];
    }

    /**
     * Asset (componente) al que apunta un enlace del menú.
     * Ej: '/administrator/menus' → 'com_menus'
     */
    public static function assetForLink(string $link): ?string
    {
        $path = trim(parse_url($link, PHP_URL_PATH) ?: $link, '/');

        $segments = explode('/', $path);
        $index = array_search('administrator', $segments, true);

        if ($index === false || !isset($segments[$index + 1])) {
            return null;
        }

        $segment = $segments[$index + 1];

        if ($segment === '' || $segment === 'index') {
            return 'com_cpanel';
        }

        if (Schema::hasTable('larajoom_extensions')) {
            $element = DB::table('larajoom_extensions')
                ->where('type', 'component')
                ->where('client_id', 1)
                ->where(function ($query) use ($segment) {
                    $query->where('element', 'com_' . $segment)
                        ->orWhere('name', $segment);
                })
                ->value('element');

            if ($element) {
                return $element;
            }
        }

        // Fallback: el propio nombre del segmento
        return str_starts_with($segment, 'com_') ? $segment : 'com_' . $segment;
    }

    /**
     * Acción del ACL que corresponde a una ruta (core.manage, core.create, core.edit, core.delete).
     */
    public static function actionForRouteName(string $routeName): string
    {
        $name = strtolower($routeName);

        return match (true) {
            str_contains($name, 'destroy') || str_contains($name, 'delete') => 'core.delete',
            str_contains($name, 'create')  || str_contains($name, 'store')  => 'core.create',
            str_contains($name, 'edit')    || str_contains($name, 'update') => 'core.edit',
            default => 'core.manage',
        };
    }

    /**
     * Asset de una ruta: se deduce del controlador (…\Components\com_menus\Controllers\…).
     */
    public static function assetForController(string $controllerClass): ?string
    {
        if (!preg_match('/Components\\\\(com_[a-z0-9_]+)\\\\Controllers/i', $controllerClass, $matches)) {
            return null;
        }

        return $matches[1];
    }

    /**
     * ¿El usuario es SuperAdmin?
     *
     * Los SuperAdmin tienen todos los permisos y no están sujetos a las reglas
     * de los assets (igual que en Joomla).
     */
    public static function isSuperAdmin(): bool
    {
        return in_array(self::SUPER_ADMIN_GROUP, self::userGroupIds(), true);
    }

    /**
     * ¿El usuario puede entrar al panel de administración?
     *
     * Solo los grupos con perfil administrativo: Author, Editor, Publisher,
     * Manager, Administrator y SuperAdmin. Public y Registered no entran.
     */
    public static function canAccessAdmin(): bool
    {
        return (bool) array_intersect(self::userGroupIds(), self::ADMIN_GROUPS);
    }

    /**
     * Grupos con los que el usuario se presenta en el SITIO.
     *
     * Los usuarios que pueden cambiar los roles entran como invitados
     * (invitado) en el sitio: en el front no tienen ningún privilegio extra,
     * aunque en la administración sí.
     *
     * @return array<int>
     */
    public static function siteGroupIds(): array
    {
        if (self::canManageRoles()) {
            return [];
        }

        return self::userGroupIds();
    }

    /**
     * ¿El usuario tiene permiso para cambiar roles (usuarios y grupos)?
     */
    public static function canManageRoles(): bool
    {
        return self::canWrite('com_users');
    }

    /**
     * Reglas de un asset (core.manage, core.edit, …) con los grupos que las tienen.
     */
    public static function rulesFor(string $asset): array
    {
        if (self::$rules === null) {
            self::$rules = [];

            if (Schema::hasTable('larajoom_assets')) {
                foreach (DB::table('larajoom_assets')->get() as $row) {
                    $decoded = json_decode($row->rules ?? '{}', true);
                    self::$rules[$row->name] = is_array($decoded) ? $decoded : [];
                }
            }
        }

        return self::$rules[$asset] ?? [];
    }

    /**
     * Todas las acciones que administra el ACL (sirve para limpiar las de un grupo).
     *
     * @return array<string>
     */
    public static function allActions(): array
    {
        return array_merge(
            self::READ_ACTIONS,
            self::WRITE_ACTIONS,
            self::DELETE_ACTIONS,
            ['core.admin']
        );
    }

    /**
     * Convierte un nivel (0-3) en el conjunto de acciones que lo componen.
     *
     * @return array<string>
     */
    public static function actionsForTier(int $tier): array
    {
        return match (true) {
            $tier >= self::TIER_DELETE => array_merge(
                self::READ_ACTIONS,
                self::WRITE_ACTIONS,
                self::DELETE_ACTIONS,
                ['core.admin']
            ),
            $tier >= self::TIER_WRITE  => array_merge(self::READ_ACTIONS, self::WRITE_ACTIONS),
            $tier >= self::TIER_READ   => self::READ_ACTIONS,
            default                     => [],
        };
    }

    /**
     * Nivel que representa un conjunto de acciones.
     */
    public static function tierForActions(array $actions): int
    {
        $actions = array_map('strval', $actions);

        if (in_array('core.admin', $actions, true) || in_array('core.delete', $actions, true)) {
            return self::TIER_DELETE;
        }

        if (array_intersect(self::WRITE_ACTIONS, $actions) !== []) {
            return self::TIER_WRITE;
        }

        if (array_intersect(self::READ_ACTIONS, $actions) !== []) {
            return self::TIER_READ;
        }

        return self::TIER_NONE;
    }

    protected static function hasAny(array $rules, array $actions, string $groupId): bool
    {
        foreach ($actions as $action) {
            if (!empty($rules[$action][$groupId])) {
                return true;
            }
        }

        return false;
    }

    protected static function hasAll(array $rules, array $actions, string $groupId): bool
    {
        foreach ($actions as $action) {
            if (empty($rules[$action][$groupId])) {
                return false;
            }
        }

        return true;
    }
}
