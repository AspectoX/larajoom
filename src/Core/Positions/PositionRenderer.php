<?php

namespace Aspectox\LaraJoom\Core\Positions;

use Aspectox\LaraJoom\Core\Resilience\SafeRunner;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PositionRenderer
{
    /**
     * Renders all active modules assigned to the given position name.
     * Execution is isolated through SafeRunner.
     */
    public static function render(string $positionName, array $params = []): string
    {
        return SafeRunner::execute("position.{$positionName}", function () use ($positionName, $params) {
            $output = '';

            foreach (self::modulesOf($positionName) as $module) {
                $output .= self::renderModule($module, $params) . "\n";
            }

            return $output;
        }, "<!-- Position '{$positionName}' failed to render safely -->");
    }

    /**
     * Renders the modules of a position as cards (dashboard / control panel).
     */
    public static function renderInCards(string $positionName, array $params = []): string
    {
        return SafeRunner::execute("position.{$positionName}", function () use ($positionName, $params) {
            $output = '';
            $canManage = \Aspectox\LaraJoom\Core\Access::canWrite('com_modules');

            foreach (self::modulesOf($positionName) as $module) {
                $title     = trim((string) ($module->title ?? ''));
                $showTitle = (bool) ($module->showtitle ?? true);

                // Ancho en columnas (bootstrap_size de los params del módulo): 0 = ancho completo
                $moduleParams = json_decode((string) ($module->params ?? ''), true) ?: [];
                $columns      = (int) ($moduleParams['bootstrap_size'] ?? 0);
                $wrapperClass = 'module-wrapper ' . ($columns > 0 && $columns < 12 ? 'col-md-' . min(11, $columns) : 'col-md-12');

                // Estructura tipo Joomla (chrome "well"): module-wrapper > card > card-header + card-body
                $output .= '<div class="' . $wrapperClass . '">';
                $output .= '<div class="card">';

                if ($showTitle || $canManage) {
                    $output .= '<div class="card-header">';

                    if ($canManage) {
                        $modId = (int) ($module->id ?? 0);

                        $output .= '<div class="module-actions dropdown">'
                            . '<button type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-haspopup="true" aria-expanded="false" class="btn circle transparet" id="dropdownMenuButton-' . $modId . '">'
                            . larajoom_icon('config')
                            . '</button>'
                            . '<div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton-' . $modId . '">'
                            . '<a class="dropdown-item" href="' . e(route('larajoom.admin.modules.edit', ['id' => $modId])) . '">' . e(__('tmp::atum.lj_action_edit')) . '</a>'
                            . '<button type="button" class="dropdown-item unpublish-module" data-module-id="' . $modId . '">' . e(__('tmp::atum.lj_action_unpublish')) . '</button>'
                            . '</div>'
                            . '</div>';
                    }

                    if ($showTitle) {
                        $output .= '<h2>' . e($title) . '</h2>';
                    }

                    $output .= '</div>';
                }

                $output .= '<div class="card-body">' . self::renderModule($module, $params) . '</div>';
                $output .= '</div>';
                $output .= '</div>' . "\n";
            }

            return $output;
        }, "<!-- Position '{$positionName}' failed to render safely -->");
    }

    /**
     * Published modules assigned to a position.
     */
    protected static function modulesOf(string $positionName)
    {
        if (!Schema::hasTable('larajoom_modules')) {
            return collect();
        }

        return DB::table('larajoom_modules')
            ->where('position', $positionName)
            ->where('published', 1)
            ->orderBy('ordering', 'asc')
            ->get();
    }

    /**
     * Renders a single module.
     */
    protected static function renderModule($module, array $params = []): string
    {
        return SafeRunner::execute("mod_{$module->module}", function () use ($module, $params) {
            // Los módulos del panel viven en administrator/modules (namespace admin.modules.X)
            // y los del sitio en modules (namespace modules.X)
            $viewKeys = [
                "admin.modules.{$module->module}::default",
                "modules.{$module->module}::default",
            ];

            $viewKey = null;

            foreach ($viewKeys as $key) {
                if (View::exists($key)) {
                    $viewKey = $key;
                    break;
                }
            }

            if ($viewKey === null) {
                // Show available view keys for debugging
                return "<!-- View missing: try admin.modules.{$module->module}::default -->";
            }

            return View::make($viewKey, array_merge($params, ['module' => $module, 'icon' => $module->icon ?? null, 'title' => $module->title ?? null]))->render();
        }, "<!-- Error: " . ($module->module ?? 'unknown') . " -->");
    }
}
