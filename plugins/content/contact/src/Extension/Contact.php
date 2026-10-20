<?php

namespace Aspectox\LaraJoom\Plugins\Content\Contact\Extension;

class Contact
{
    public function getName(): string
    {
        return 'content.contact';
    }

    public function onContentPrepare(string $context, &$article, $params = null): void
    {
        // Inserta formulario de contacto dentro del artículo.
    }
}
