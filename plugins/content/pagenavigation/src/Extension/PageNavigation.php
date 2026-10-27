<?php

namespace Aspectox\LaraJoom\Plugins\Content\Pagenavigation\Extension;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PageNavigation
{
    public function getName(): string
    {
        return 'content.pagenavigation';
    }

    public function getParams(): array
    {
        return [
            'position' => 1,     // 1 = al final, 0 = al inicio
            'relative' => 1,     // 1 = según categoría, 0 = según texto
            'display'  => 0,     // 0 = anterior/siguiente, 1 = títulos
        ];
    }

    /**
     * Agrega navegación Anterior / Siguiente al artículo (por categoría).
     */
    public function onContentPrepare(string $context, &$article, $params = null): void
    {
        if (!is_object($article) || empty($article->id) || !Schema::hasTable('larajoom_content')) {
            return;
        }

        $mode = (int) ($params['position'] ?? $this->getParams()['position']);
        $prev = DB::table('larajoom_content')
            ->where('catid', $article->catid)
            ->where('published', 1)
            ->where('id', '<', $article->id)
            ->orderByDesc('id')
            ->first(['id', 'title', 'alias']);

        $next = DB::table('larajoom_content')
            ->where('catid', $article->catid)
            ->where('published', 1)
            ->where('id', '>', $article->id)
            ->orderBy('id')
            ->first(['id', 'title', 'alias']);

        if (!$prev && !$next) {
            return;
        }

        $nav = '<nav class="lj-pagenavigation"><div style="display:flex;justify-content:space-between;gap:1rem;">'
            . ($prev ? '<a href="' . e(route('larajoom.article.show', $prev->alias)) . '">← ' . e($prev->title) . '</a>' : '<span></span>')
            . ($next ? '<a href="' . e(route('larajoom.article.show', $next->alias)) . '">' . e($next->title) . ' →</a>' : '')
            . '</div></nav>';

        $field = ($mode === 0) ? 'introtext' : 'fulltext';

        if (isset($article->{$field})) {
            $article->{$field} .= $nav;
        } elseif (isset($article->fulltext)) {
            $article->fulltext .= $nav;
        }
    }
}
