<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LarajoomAclSeeder extends Seeder
{
    public function run(): void
    {
        // Assets base + todos los componentes Joomla 6 (con rules por defecto)
        $components = [
            'com_content' => 3, 'com_users' => 4, 'com_menus' => 5, 'com_modules' => 6,
            'com_media' => 7, 'com_contact' => 8, 'com_finder' => 9, 'com_tags' => 10,
            'com_config' => 11, 'com_categories' => 12, 'com_fields' => 13,
            'com_banners' => 14, 'com_newsfeeds' => 15, 'com_weblinks' => 16,
            'com_wrapper' => 17, 'com_ajax' => 18, 'com_installer' => 19,
            'com_languages' => 20, 'com_scheduler' => 21, 'com_redirect' => 22,
            'com_privacy' => 23, 'com_workflow' => 24, 'com_guidedtours' => 25,
            'com_joomlaupdate' => 26,
            'com_cache' => 27,
            'com_checkin' => 28,
            'com_contenthistory' => 29,
            'com_cpanel' => 30,
            'com_search' => 31,
            'com_login' => 32,
            'com_messages' => 33,
        ];
        $assets = [
            ['id' => 1, 'parent_id' => 0, 'lft' => 1, 'rgt' => 2, 'level' => 1, 'name' => 'root.1', 'title' => 'Root Asset', 'rules' => '{"core.admin":{"8":1},"core.manage":{"8":1}}', 'created_at' => now(), 'updated_at' => now()],
        ];
        $lft = 3; $rgt = 4; $id = 2;
        foreach ($components as $comp => $grp) {
            $assets[] = [
                'id' => $id, 'parent_id' => 1, 'lft' => $lft, 'rgt' => $rgt, 'level' => 2,
                'name' => $comp, 'title' => ucfirst(str_replace('com_', '', $comp)),
                'rules' => '{"core.admin":{"7":1,"8":1},"core.manage":{"7":1,"8":1}}',
                'created_at' => now(), 'updated_at' => now(),
            ];
            $lft += 2; $rgt += 2; $id++;
        }
        DB::table('larajoom_assets')->insertOrIgnore($assets);
        DB::table('users')->insertOrIgnore([
            'id' => 1,
            'name' => 'Administrator',
            'email' => 'admin@site.local',
            'password' => bcrypt('admin'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Grupos tipo Joomla 6 (base de instalación)
        DB::table('larajoom_usergroups')->insertOrIgnore([
            ['id' => 1, 'parent_id' => 0, 'title' => 'Public', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'parent_id' => 0, 'title' => 'Registered', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'parent_id' => 0, 'title' => 'Author', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'parent_id' => 0, 'title' => 'Editor', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'parent_id' => 0, 'title' => 'Publisher', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'parent_id' => 0, 'title' => 'Manager', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'parent_id' => 0, 'title' => 'Administrator', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'parent_id' => 7, 'title' => 'Super Users', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
