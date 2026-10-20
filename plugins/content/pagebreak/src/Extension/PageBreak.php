<?php

namespace Aspectox\LaraJoom\Plugins\Content\Pagebreak\Extension;

class PageBreak
{
    public function getName(): string
    {
        return 'content.pagebreak';
    }

    public function onContentPrepare(string $context, &$article, $params = null): void
    {
        // Inserta salto de página (toc, navegación) en contenido.
    }
}
