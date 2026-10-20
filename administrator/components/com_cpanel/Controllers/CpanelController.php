<?php

namespace Aspectox\LaraJoom\Admin\Components\com_cpanel\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CpanelController extends Controller
{
    /**
     * Panel de control (dashboard).
     *
     * Renderiza los módulos asignados a la posición del panel (por defecto "cpanel")
     * y el botón para agregar módulos.
     */
    public function index(Request $request)
    {
        return view('admin.components.com_cpanel::index', self::panelData());
    }

    /**
     * Datos del panel de control (posición de los módulos y tipos disponibles).
     *
     * @return array
     */
    public static function panelData(): array
    {
        $options = self::options();

        return [
            'position'      => (string) ($options['position'] ?? 'cpanel'),
            'showAddModule' => (bool) ($options['show_add_module'] ?? true),
            'moduleTypes'   => self::moduleTypes(),
        ];
    }

    /**
     * Opciones del componente (larajoom_extensions.params).
     *
     * @return array
     */
    protected static function options(): array
    {
        if (!Schema::hasTable('larajoom_extensions')) {
            return [];
        }

        $extension = DB::table('larajoom_extensions')
            ->where('type', 'component')
            ->where('element', 'com_cpanel')
            ->first();

        if (!$extension || !$extension->params) {
            return [];
        }

        $params = json_decode($extension->params, true);

        return is_array($params) ? $params : [];
    }

    /**
     * Tipos de módulo instalados (carpetas mod_*).
     */
    protected static function moduleTypes(): array
    {
        $base = dirname(__DIR__, 4);

        $types = [];

        foreach (['administrator' . DIRECTORY_SEPARATOR . 'modules', 'modules'] as $folder) {
            $dir = $base . DIRECTORY_SEPARATOR . $folder;

            if (!is_dir($dir)) {
                continue;
            }

            foreach (scandir($dir) ?: [] as $item) {
                if (str_starts_with($item, 'mod_') && is_dir($dir . DIRECTORY_SEPARATOR . $item)) {
                    $types[$item] = $item;
                }
            }
        }

        return $types;
    }
}
