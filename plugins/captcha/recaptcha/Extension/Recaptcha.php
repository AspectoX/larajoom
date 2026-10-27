<?php

namespace Aspectox\LaraJoom\Plugins\Captcha\Recaptcha\Extension;

use Illuminate\Support\Facades\Log;

class Recaptcha
{
    public function getName(): string
    {
        return 'captcha.recaptcha';
    }

    public function getParams(): array
    {
        return [
            'site_key'   => '',
            'secret_key' => '',
            'theme'      => 'light',
            'size'       => 'normal',
        ];
    }

    /**
     * HTML del widget reCAPTCHA v2.
     */
    public function onDisplay(string $name = 'g-recaptcha-response', string $id = 'g-recaptcha'): string
    {
        $params = $this->getParams();
        $siteKey = trim((string) $params['site_key']);

        if ($siteKey === '') {
            return '';
        }

        return '<div class="g-recaptcha" data-sitekey="' . htmlspecialchars($siteKey)
            . '" data-theme="' . htmlspecialchars((string) $params['theme'])
            . '" data-size="' . htmlspecialchars((string) $params['size']) . '"></div>'
            . '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
    }

    /**
     * Valida la respuesta del reCAPTCHA contra Google.
     */
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
            Log::warning('reCAPTCHA: no se pudo contactar el servicio de verificación.');
            return false;
        }

        $result = json_decode($body, true);

        return is_array($result) && !empty($result['success']);
    }
}
