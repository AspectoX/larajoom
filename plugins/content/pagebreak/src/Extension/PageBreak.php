<?php

namespace Aspectox\LaraJoom\Plugins\Content\Pagebreak\Extension;

class PageBreak
{
    public function getName(): string
    {
        return 'content.pagebreak';
    }

    /**
     * Divide el contenido por la etiqueta {pagebreak} y genera pestañas con
     * navegación (toc) para recorrer las secciones.
     */
    public function onContentPrepare(string $context, &$article, $params = null): void
    {
        if (!is_object($article)) {
            return;
        }

        foreach (['introtext', 'fulltext'] as $field) {
            if (!isset($article->{$field}) || strpos($article->{$field}, '{pagebreak') === false) {
                continue;
            }

            // Divide por {pagebreak}[Titulo]
            $parts = preg_split('/\{pagebreak(?:\s+([^}]*))?\}/i', $article->{$field}, -1, PREG_SPLIT_DELIM_CAPTURE);

            if (count($parts) < 3) {
                continue;
            }

            $sections = [];
            $sections[] = ['title' => 'Inicio', 'body' => array_shift($parts)];
            $i = 0;
            while (count($parts) >= 2) {
                $title = trim($parts[$i]) !== '' ? trim($parts[$i]) : 'Página ' . ($i + 2);
                $sections[] = ['title' => $title, 'body' => $parts[$i + 1]];
                $i += 2;
            }

            $article->{$field} = $this->render($sections);
        }
    }

    protected function render(array $sections): string
    {
        $tabs = '';
        $bodies = '';

        foreach ($sections as $idx => $section) {
            $active = $idx === 0 ? ' active' : '';
            $hidden = $idx === 0 ? '' : ' style="display:none"';
            $tabs .= '<button type="button" class="lj-pagebreak-tab' . $active . '" data-target="lj-pb-' . $idx . '">'
                . e($section['title']) . '</button>';
            $bodies .= '<div class="lj-pagebreak-section" id="lj-pb-' . $idx . '"' . $hidden . '>'
                . $section['body'] . '</div>';
        }

        return '<div class="lj-pagebreak" data-module="pagebreak">'
            . '<div class="lj-pagebreak-tabs">' . $tabs . '</div>'
            . '<div class="lj-pagebreak-bodies">' . $bodies . '</div>'
            . '<script>'
            . '(function(){var t=document.querySelectorAll(".lj-pagebreak-tab");t.forEach(function(b){'
            . 'b.addEventListener("click",function(){t.forEach(function(x){x.classList.remove("active")});'
            . 'document.querySelectorAll(".lj-pagebreak-section").forEach(function(s){s.style.display="none"});'
            . 'b.classList.add("active");document.getElementById(b.getAttribute("data-target")).style.display="block";});});})();'
            . '</script></div>';
    }
}
