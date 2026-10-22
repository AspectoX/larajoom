<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaraJoomMenusSeeder extends Seeder
{
    public function run(): void
    {
        // Tipo de menú
        if (Schema::hasTable('larajoom_menu_types')) {
            DB::table('larajoom_menu_types')->updateOrInsert(
                ['menutype' => 'mainmenu'],
                [
                    'title'       => 'mainmenu',
                    'description' => 'Menú principal del dashboard',
                    'client_id'   => 1, // 1 = administración
                    'ordering'    => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }

        // Items del menú
        if (Schema::hasTable('larajoom_menu')) {
            $items = [
                [
                    'alias'    => 'dashboard',
                    'title'    => 'Dashboard',
                    'link'     => '/administrator',
                    'icon'     => 'dashboard',
                    'home'     => true,
                    'ordering' => 1,
                ],
                [
                    'alias'    => 'menu',
                    'title'    => 'Menu',
                    'link'     => '/administrator/menus',
                    'icon'     => 'menus',
                    'home'     => false,
                    'ordering' => 2,
                ],
                [
                    'alias'    => 'modules',
                    'title'    => 'Modules',
                    'link'     => '/administrator/modules',
                    'icon'     => 'module',
                    'home'     => false,
                    'ordering' => 3,
                ],
            ];

            foreach ($items as $item) {
                DB::table('larajoom_menu')->updateOrInsert(
                    ['menutype' => 'mainmenu', 'alias' => $item['alias']],
                    [
                        'title'      => $item['title'],
                        'link'       => $item['link'],
                        'type'       => 'component',
                        'icon'       => $item['icon'],
                        'show_icon'  => true,
                        'published'  => true,
                        'home'       => $item['home'],
                        'parent_id'  => 0,
                        'level'      => 1,
                        'ordering'   => $item['ordering'],
                        'access'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // Módulos del panel: hooks de posición del template
        if (Schema::hasTable('larajoom_modules')) {
            $modules = [
                [
                    'title'     => 'Dashboard',
                    'module'    => 'mod_title',
                    'position'  => 'title',
                    'showtitle' => true,
                ],
                [
                    'title'     => 'Menú de administración',
                    'module'    => 'mod_menu',
                    'position'  => 'menu',
                    'showtitle' => false,
                ],
            ];

            foreach ($modules as $module) {
                DB::table('larajoom_modules')->updateOrInsert(
                    ['module' => $module['module'], 'position' => $module['position']],
                    [
                        'title'      => $module['title'],
                        'published'  => true,
                        'ordering'   => 1,
                        'showtitle'  => $module['showtitle'],
                        'params'     => null,
                        'access'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // Limpia los módulos del panel de control que se habían registrado
            $this->removeCpanelModules();
        }
    }

    /**
     * Elimina de la base los módulos del panel de control (posición cpanel)
     * que ya no forman parte del paquete.
     */
    protected function removeCpanelModules(): void
    {
        if (!Schema::hasTable('larajoom_modules')) {
            return;
        }

        DB::table('larajoom_modules')
            ->where('position', 'cpanel')
            ->whereIn('module', ['mod_stats_admin', 'mod_logged', 'mod_latestactions'])
            ->delete();

        if (Schema::hasTable('larajoom_extensions')) {
            DB::table('larajoom_extensions')
                ->where('type', 'module')
                ->whereIn('element', ['mod_stats_admin', 'mod_logged', 'mod_latestactions'])
                ->delete();
        }
    }
}
