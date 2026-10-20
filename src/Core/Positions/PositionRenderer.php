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

            // Check if modules table exists
            if (!Schema::hasTable('larajoom_modules')) {
                return "<!-- Position '{$positionName}' (Table not migrated yet) -->";
            }

            $modules = DB::table('larajoom_modules')
                ->where('position', $positionName)
                ->where('published', 1)
                ->orderBy('ordering', 'asc')
                ->get();

            foreach ($modules as $module) {
                $moduleOutput = SafeRunner::execute("mod_{$module->module}", function () use ($module, $params) {
                    $viewKey = "modules.{$module->module}::default";
                    if (!View::exists($viewKey)) {
                        // Show available view keys for debugging
                        return "<!-- View missing: try modules.{$module->module}::default -->";
                    }
                        return View::make($viewKey, array_merge($params, ['module' => $module, 'icon' => $module->icon ?? null, 'title' => $module->title ?? null]))->render();
                }, "<!-- Error: " . ($module->module ?? 'unknown') . " -->");
                $output .= $moduleOutput . "\n";
            }

            return $output;
        }, "<!-- Position '{$positionName}' failed to render safely -->");
    }
}
