<?php

namespace Aspectox\LaraJoom\Plugins\Content\LoadModule\Extension;

class LoadModule
{
    public function getName(): string
    {
        return 'content.loadmodule';
    }

    public function onContentPrepare(string $context, &$article, $params = null): void
    {
        // Inserta un módulo dentro del artículo mediante tag {loadmodule ...}
    }
}
