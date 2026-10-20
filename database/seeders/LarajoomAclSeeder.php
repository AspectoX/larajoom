<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ACL de LaraJoom.
 *
 * Jerarquía de roles (de más a menos importante):
 *
 *   9 SuperAdmin      -> todo (además bypassa el ACL)
 *   8 Administrator   -> leer, escribir y borrar
 *   7 Manager         -> leer y escribir
 *   6 Publisher       -> leer y escribir
 *   5 Editor          -> leer y escribir
 *   4 Author          -> leer
 *   3 Registered      -> sin acceso al panel
 *   2 Guest           -> sin acceso al panel
 *   1 Public          -> sin acceso al panel
 *
 * Los grupos se encadenan con parent_id: cada uno hereda de su antecesor.
 */
class LarajoomAclSeeder extends Seeder
{
    /** Roles con su id, título y padre (de más a menos importante) */
    protected const GROUPS = [
        ['id' => 1, 'title' => 'Public', 'parent_id' => 0],
        ['id' => 2, 'title' => 'Guest', 'parent_id' => 1],
        ['id' => 3, 'title' => 'Registered', 'parent_id' => 2],
        ['id' => 4, 'title' => 'Author', 'parent_id' => 3],
        ['id' => 5, 'title' => 'Editor', 'parent_id' => 4],
        ['id' => 6, 'title' => 'Publisher', 'parent_id' => 5],
        ['id' => 7, 'title' => 'Manager', 'parent_id' => 6],
        ['id' => 8, 'title' => 'Administrator', 'parent_id' => 7],
        ['id' => 9, 'title' => 'SuperAdmin', 'parent_id' => 8],
    ];

    /** Nivel de permisos por grupo (ver Core\Access) */
    protected const TIERS = [
        9 => 3, // SuperAdmin    -> todo
        8 => 3, // Administrator -> leer, escribir y borrar
        7 => 2, // Manager       -> leer y escribir
        6 => 2, // Publisher     -> leer y escribir
        5 => 2, // Editor        -> leer y escribir
        4 => 1, // Author        -> leer
        // 3 Registered, 2 Guest y 1 Public no acceden al panel
    ];

    /** Numeración anterior -> nueva (se aplica si la base ya está instalada) */
    protected const REMAP = [8 => 9, 7 => 8, 6 => 7, 5 => 6, 4 => 5, 3 => 4, 2 => 3];

    public function run(): void
    {
        $this->remapLegacyGroups();

        $this->seedGroups();
        $this->seedAssets();
        $this->seedUsers();
    }

    /**
     * Renumera los roles si la base tiene la numeración anterior
     * (1=Public, 2=Registered, …, 8=Super Users).
     */
    protected function remapLegacyGroups(): void
    {
        if (!Schema::hasTable('larajoom_usergroups')) {
            return;
        }

        $hasGuest = DB::table('larajoom_usergroups')->where('title', 'Guest')->exists();

        if ($hasGuest) {
            return;
        }

        $second = DB::table('larajoom_usergroups')->where('id', 2)->value('title');

        if ($second !== 'Registered') {
            return;
        }

        // De mayor a menor para no pisar ids
        foreach (self::REMAP as $old => $new) {
            DB::table('larajoom_usergroups')->where('id', $old)->update(['id' => $new]);
        }

        if (Schema::hasTable('larajoom_user_usergroup_map')) {
            foreach (self::REMAP as $old => $new) {
                DB::table('larajoom_user_usergroup_map')->where('group_id', $old)->update(['group_id' => $new]);
            }
        }

        if (Schema::hasTable('larajoom_users')) {
            foreach (self::REMAP as $old => $new) {
                DB::table('larajoom_users')->where('group_id', $old)->update(['group_id' => $new]);
            }
        }

        // Los niveles de acceso guardan los ids de grupo en su JSON
        if (Schema::hasTable('larajoom_viewlevels')) {
            foreach (DB::table('larajoom_viewlevels')->get() as $level) {
                $groups = json_decode($level->rules ?? '[]', true);

                if (!is_array($groups)) {
                    continue;
                }

                $remapped = array_map(function ($groupId) {
                    $old = (int) $groupId;

                    return self::REMAP[$old] ?? $old;
                }, $groups);

                DB::table('larajoom_viewlevels')
                    ->where('id', $level->id)
                    ->update(['rules' => json_encode($remapped), 'updated_at' => now()]);
            }
        }
    }

    /**
     * Crea (o actualiza) los roles con su jerarquía.
     */
    protected function seedGroups(): void
    {
        if (!Schema::hasTable('larajoom_usergroups')) {
            return;
        }

        foreach (self::GROUPS as $group) {
            DB::table('larajoom_usergroups')->updateOrInsert(
                ['id' => $group['id']],
                [
                    'title'      => $group['title'],
                    'parent_id'  => $group['parent_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Assets (componentes) con las reglas de permisos por grupo.
     */
    protected function seedAssets(): void
    {
        if (!Schema::hasTable('larajoom_assets')) {
            return;
        }

        $components = [
            'com_content' => 4, 'com_users' => 5, 'com_menus' => 6, 'com_modules' => 7,
            'com_media' => 8, 'com_contact' => 9, 'com_finder' => 10, 'com_tags' => 11,
            'com_config' => 12, 'com_categories' => 13, 'com_fields' => 14,
            'com_banners' => 15, 'com_newsfeeds' => 16, 'com_weblinks' => 17,
            'com_wrapper' => 18, 'com_ajax' => 19, 'com_installer' => 20,
            'com_languages' => 21, 'com_scheduler' => 22, 'com_redirect' => 23,
            'com_privacy' => 24, 'com_workflow' => 25, 'com_guidedtours' => 26,
            'com_joomlaupdate' => 27,
            'com_cache' => 28,
            'com_checkin' => 29,
            'com_contenthistory' => 30,
            'com_cpanel' => 31,
            'com_search' => 32,
            'com_login' => 33,
            'com_messages' => 34,
        ];

        $rules = $this->buildRules();

        $assets = [
            ['id' => 1, 'parent_id' => 0, 'lft' => 1, 'rgt' => 2, 'level' => 1, 'name' => 'root.1', 'title' => 'Root Asset', 'rules' => $rules],
        ];

        $lft = 3;
        $rgt = 4;
        $id = 2;

        foreach ($components as $comp => $grp) {
            $assets[] = [
                'id' => $id, 'parent_id' => 1, 'lft' => $lft, 'rgt' => $rgt, 'level' => 2,
                'name' => $comp, 'title' => ucfirst(str_replace('com_', '', $comp)),
                'rules' => $rules,
            ];
            $lft += 2;
            $rgt += 2;
            $id++;
        }

        foreach ($assets as $asset) {
            DB::table('larajoom_assets')->updateOrInsert(
                ['name' => $asset['name']],
                [
                    'parent_id'  => $asset['parent_id'],
                    'lft'        => $asset['lft'],
                    'rgt'        => $asset['rgt'],
                    'level'      => $asset['level'],
                    'title'      => $asset['title'],
                    'rules'      => $asset['rules'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Usuario administrador del panel: siempre con el rol SuperAdmin.
     */
    protected function seedUsers(): void
    {
        if (Schema::hasTable('users')) {
            DB::table('users')->insertOrIgnore([
                'id' => 1,
                'name' => 'Administrator',
                'email' => 'admin@site.local',
                'password' => bcrypt('admin'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!Schema::hasTable('larajoom_user_usergroup_map') || !Schema::hasTable('users')) {
            return;
        }

        $admin = DB::table('users')->orderBy('id')->first();

        if (!$admin) {
            return;
        }

        $exists = DB::table('larajoom_user_usergroup_map')
            ->where('user_id', $admin->id)
            ->where('group_id', 9)
            ->exists();

        if (!$exists) {
            DB::table('larajoom_user_usergroup_map')->insert([
                'user_id'  => $admin->id,
                'group_id' => 9,
            ]);
        }
    }

    /**
     * Construye el JSON de reglas de un asset a partir de los niveles por grupo.
     */
    protected function buildRules(): string
    {
        $rules = [];

        foreach (self::TIERS as $groupId => $tier) {
            foreach ($this->actionsForTier((int) $tier) as $action) {
                $rules[$action][(string) $groupId] = 1;
            }
        }

        // El SuperAdmin administra todos los assets
        $rules['core.admin']['9'] = 1;

        return json_encode($rules);
    }

    /**
     * Acciones que componen cada nivel (ver Core\Access).
     *
     * @return array<int>
     */
    protected function actionsForTier(int $tier): array
    {
        return match (true) {
            $tier >= 3 => ['core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.edit.own', 'core.delete'],
            $tier >= 2 => ['core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.edit.own'],
            $tier >= 1 => ['core.manage'],
            default  => [],
        };
    }
}
