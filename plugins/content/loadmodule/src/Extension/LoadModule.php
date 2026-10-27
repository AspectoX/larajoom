<?php

namespace Aspectox\LaraJoom\Plugins\Content\LoadModule\Extension;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Aspectox\LaraJoom\Core\Positions\PositionRenderer;

class LoadModule
{
    public function getName(): string
    {
        return 'content.loadmodule';
    }

    /**
     * Reemplaza {loadposition Nombre} y {loadmodule Modulo,Titulo} por el
     * contenido real de las posiciones / módulos.
     */
    public function onContentPrepare(string $context, &$article, $params = null): void
    {
        if (!is_object($article)) {
            return;
        }

        $replace = function (string $text): string {
            // {loadposition Posicion}
            $text = preg_replace_callback(
                '/\{loadposition\s+([a-z0-9_-]+)\}/i',
                fn ($m) => PositionRenderer::render($m[1]),
                $text
            );

            // {loadmodule Modulo,Titulo}
            $text = preg_replace_callback(
                '/\{loadmodule\s+(?:([a-z0-9_-]+),)?([^}]+)\}/i',
                function ($m) {
                    $module = $m[1] ?? '';
                    $title  = trim($m[2]);
                    return $this->renderModuleByName($module, $title);
                },
                $text
            );

            return $text;
        };

        if (isset($article->introtext)) {
            $article->introtext = $replace($article->introtext);
        }
        if (isset($article->fulltext)) {
            $article->fulltext = $replace($article->fulltext);
        }
    }

    protected function renderModuleByName(string $type, string $title): string
    {
        if (!Schema::hasTable('larajoom_modules')) {
            return '';
        }

        $query = DB::table('larajoom_modules')->where('published', 1);

        if ($type !== '') {
            $query->where('module', $type);
        } elseif ($title !== '') {
            $query->where('title', $title);
        }

        $rows = $query->orderBy('ordering')->get();

        $output = '';
        foreach ($rows as $module) {
            $viewKeys = [
                "admin.modules.{$module->module}::default",
                "modules.{$module->module}::default",
            ];

            foreach ($viewKeys as $key) {
                if (View::exists($key)) {
                    $output .= View::make($key, [
                        'module' => $module,
                        'params' => json_decode((string) ($module->params ?? ''), true) ?: [],
                    ])->render();
                    break;
                }
            }
        }

        return $output;
    }
}
