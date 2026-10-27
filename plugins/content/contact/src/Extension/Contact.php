<?php

namespace Aspectox\LaraJoom\Plugins\Content\Contact\Extension;

class Contact
{
    public function getName(): string
    {
        return 'content.contact';
    }

    /**
     * Convierte los enlaces mailto: del contenido en links ofuscados
     * (anti-spam) y añade la clase de contacto.
     */
    public function onContentPrepare(string $context, &$article, $params = null): void
    {
        if (!is_object($article)) {
            return;
        }

        $pattern = '/(<a\b[^>]*href=["\']mailto:)([A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,})(["\'][^>]*>)(.*?)(<\/a>)/is';

        $replace = function (array $m): string {
            $email = $m[2];
            [$user, $domain] = explode('@', $email, 2);
            $text = $m[4] !== '' ? $m[4] : $email;

            return '<a class="contact-mailto" href="#contact" data-user="' . base64_encode($user)
                . '" data-domain="' . base64_encode($domain) . '">' . $text . '</a>';
        };

        foreach (['introtext', 'fulltext'] as $field) {
            if (isset($article->{$field})) {
                $article->{$field} = preg_replace_callback($pattern, $replace, $article->{$field});
            }
        }
    }
}
