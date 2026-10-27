<?php

namespace Aspectox\LaraJoom\Plugins\Captcha\InvisibleRecaptcha\Extension;

use Illuminate\Support\Facades\Log;

class InvisibleRecaptcha
{
    public function getName(): string
    {
        return 'captcha.invisible-recaptcha';
    }

    public function getParams(): array
    {
        return [
            'site_key'   => '',
            'secret_key' => '',
            'badge'      => 'bottomright',   // bottomright | bottomleft | inline
        ];
    }

    /**
     * Widget reCAPTCHA invisible: se envía al enviar el formulario.
     */
    public function onDisplay(string $name = 'g-recaptcha-response', string $id = 'g-recaptcha'): string
    {
        $params = $this->getParams();
        $siteKey = trim((string) $params['site_key']);

        if ($siteKey === '') {
            return '';
        }

        return '<div class="g-recaptcha" data-sitekey="' . htmlspecialchars($siteKey)
            . '" data-callback="onRecaptchaSubmit" data-size="invisible" data-badge="'
            . htmlspecialchars((string) $params['badge']) . '"></div>'
            . '<script src="https://www.google.com/recaptcha/api.js" async defer></script>'
            . '<script>function onRecaptchaSubmit(token){ var f=document.getElementById("' . $id . '"); if(f){ f.submit(); } }</script>';
    }

    public function onCheckAnswer(?string $code, ?string $remoteIp = null): bool
    {
        $params = $this->getParams();
        $secret = trim((string) $params['secret_key']);

        if ($secret === '' || empty($code)) {
            return false;
        }

        $payload = http_build_query([
            'secret'   => $secret,
            'response' => $code,
            'remoteip' => $remoteIp ?? request()->ip(),
        ]);

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $payload,
                'timeout' => 5,
            ],
        ]);

        $body = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $ctx);
        if ($body === false) {
            Log::warning('reCAPTCHA invisible: no se pudo contactar el servicio de verificación.');
            return false;
        }

        $result = json_decode($body, true);

        return is_array($result) && !empty($result['success']);
    }
}
