<?php

namespace Aspectox\LaraJoom\Plugins\Content\EmailCloak\Extension;

class EmailCloak
{
    public function getName(): string
    {
        return 'content.emailcloak';
    }

    public function onContentPrepare(string $context, &$article, $params = null): void
    {
        // Oculta correos electrónicos en el contenido (anti-spam).
    }
}
