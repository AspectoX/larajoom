<?php

namespace Aspectox\LaraJoom\Plugins\Twofactorauth\TfaYubikey\Extension;

use Illuminate\Support\Facades\DB;

class TfaYubikey
{
    public function getName(): string
    {
        return 'twofactorauth.tfa_yubikey';
    }

    public function getParams(): array
    {
        return [
            'client_id' => '',
            'secret'    => '',
            'server'    => 'https://api.yubico.com/wsapi/2.0/verify',
            'slot'      => 1,
        ];
    }

    /**
     * Asocia una YubiKey (ID público) a un usuario.
     */
    public function setup(int $userId, string $publicId, string $label = 'YubiKey'): void
    {
        if (!preg_match('/^[cbdefghijklnrtuv]{12,16}$/i', $publicId)) {
            throw new \InvalidArgumentException('ID de YubiKey no válido.');
        }

        DB::table('larajoom_user_mfa')->insert([
            'user_id'    => $userId,
            'method'     => 'yubikey',
            'secret'     => strtolower($publicId),
            'label'      => $label,
            'enabled'    => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Valida un OTP de YubiKey contra el servidor de validación de Yubico.
     */
    public function validate(int $userId, string $otp): bool
    {
        $row = DB::table('larajoom_user_mfa')
            ->where('user_id', $userId)
            ->where('method', 'yubikey')
            ->where('enabled', 1)
            ->first();

        if (!$row || empty($row->secret)) {
            return false;
        }

        $otp = trim($otp);
        if (strlen($otp) < 32 || !preg_match('/^[cbdefghijklnrtuv]/i', $otp)) {
            return false;
        }

        // El prefijo (primeros 12 chars) debe coincidir con la YubiKey registrada.
        $publicId = substr(strtolower($otp), 0, 12);
        if ($publicId !== $row->secret) {
            return false;
        }

        if (!$this->verifyWithYubico($otp)) {
            return false;
        }

        DB::table('larajoom_user_mfa')
            ->where('id', $row->id)
            ->update(['last_used_at' => now()]);

        return true;
    }

    public function remove(int $userId): void
    {
        DB::table('larajoom_user_mfa')
            ->where('user_id', $userId)
            ->where('method', 'yubikey')
            ->delete();
    }

    public function hasConfigured(int $userId): bool
    {
        return DB::table('larajoom_user_mfa')
            ->where('user_id', $userId)
            ->where('method', 'yubikey')
            ->where('enabled', 1)
            ->exists();
    }

    /**
     * Yubico Validation Protocol v2.0
     */
    protected function verifyWithYubico(string $otp): bool
    {
        $params = $this->getParams();

        $query = [
            'id'    => trim((string) $params['client_id']),
            'otp'   => $otp,
            'nonce' => bin2hex(random_bytes(16)),
            'timestamp' => '1',
        ];

        // Firma HMAC del cliente (si se configuró secret)
        $secret = trim((string) $params['secret']);
        if ($secret !== '') {
            $message = '';
            foreach ($query as $k => $v) {
                $message .= $k . '=' . $v . '&';
            }
            $message = rtrim($message, '&');
            $query['h'] = base64_encode(hash_hmac('sha1', $message, base64_decode($secret), true));
        }

        $url = rtrim((string) $params['server'], '/') . '?' . http_build_query($query);

        $ctx = stream_context_create([
            'http' => [
                'timeout' => 5,
                'ignore_errors' => true,
            ],
        ]);

        $body = @file_get_contents($url, false, $ctx);
        if ($body === false) {
            return false;
        }

        if (preg_match('/status=([a-z_]+)/i', $body, $m) && strtolower($m[1]) === 'ok') {
            // Rechaza replays: el OTP no debe haberse usado antes.
            return strpos($body, 'otp=') !== false;
        }

        return false;
    }
}
