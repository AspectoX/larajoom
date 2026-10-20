<?php

namespace Aspectox\LaraJoom\Plugins\Content\Vote\Extension;

class Vote
{
    public function getName(): string
    {
        return 'content.vote';
    }

    /**
     * Configuración del plugin (equivalente a vote.xml params).
     */
    public function getParams(): array
    {
        return [
            'position' => 'top',      // top o bottom
            'show_total_votes' => 0,  // 0 = ocultar, 1 = mostrar
        ];
    }

    /**
     * Procesa el contenido para insertar votación.
     */
    public function onContentPrepare(string $context, &$article, $params = null): void
    {
        // Inserta HTML de votación según posición (top/bottom) y total.
    }
}
