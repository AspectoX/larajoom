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

            foreach (self::modulesOf($positionName) as $module) {
                $title = trim((string) ($module->title ?? ''));

                $output .= '<div class="card">';

                if ($title !== '') {
                    $output .= '<div class="card-header"><div class="card-header-title">'
                        . e($title) . '</div></div>';
                }

                $output .= '<div class="card-body">' . self::renderModule($module, $params) . '</div>';
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
