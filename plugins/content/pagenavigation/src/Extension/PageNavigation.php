<?php

namespace Aspectox\LaraJoom\Plugins\Content\Pagenavigation\Extension;

class PageNavigation
{
    public function getName(): string
    {
        return 'content.pagenavigation';
    }

    public function getParams(): array
    {
        return [
            'position' => 1,     // 1 = below, 0 = above
            'relative' => 1,     // 1 = article, 0 = text
            'display' => 0,     // 0 = nextprev, 1 = title
        ];
    }

    public function onContentPrepare(string $context, &$article, $params = null): void
    {
        // Inserta paginación según posición y modo
    }
}
