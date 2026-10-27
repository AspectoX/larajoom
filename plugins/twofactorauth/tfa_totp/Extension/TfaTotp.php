<?php

namespace Aspectox\LaraJoom\Plugins\Twofactorauth\TfaTotp\Extension;

use Illuminate\Support\Facades\DB;

class TfaTotp
{
    public function getName(): string
    {
        return 'twofactorauth.tfa_totp';
    }

    /**
     * Registra un nuevo secreto TOTP para un usuario.
     * Devuelve ['secret' => base32, 'url' => otpauth://...] para configurar la app.
     */
    public function setup(int $userId, string $label = 'LaraJoom'): array
    {
        $secret = $this->generateSecret(20);

        DB::table('larajoom_user_mfa')->insert([
            'user_id'    => $userId,
            'method'     => 'totp',
            'secret'     => $secret,
            'label'      => $label,
            'enabled'    => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $issuer = config('app.name', 'LaraJoom');

        return [
            'secret' => $secret,
            'url'    => 'otpauth://totp/' . rawurlencode($issuer) . ':' . rawurlencode($label)
                . '?secret=' . $secret . '&issuer=' . rawurlencode($issuer) . '&algorithm=SHA1&digits=6&period=30',
        ];
    }

    /**
     * Valida un código TOTP contra el secreto almacenado del usuario.
     * Se acepta una ventana de ±1 paso (30s) para tolerar desincronización.
     */
    public function validate(int $userId, string $code): bool
    {
        $row = DB::table('larajoom_user_mfa')
            ->where('user_id', $userId)
            ->where('method', 'totp')
            ->where('enabled', 1)
            ->first();

        if (!$row || empty($row->secret)) {
            return false;
        }

        $secret = $this->base32Decode($row->secret);
        $code   = trim($code);

        if ($code === '' || !preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $period  = 30;
        $counter = (int) floor(time() / $period);

        for ($offset = -1; $offset <= 1; $offset++) {
            if ($this->hotp($secret, $counter + $offset) === $code) {
                DB::table('larajoom_user_mfa')
                    ->where('id', $row->id)
                    ->update(['last_used_at' => now()]);
                return true;
            }
        }

        return false;
    }

    public function remove(int $userId): void
    {
        DB::table('larajoom_user_mfa')
            ->where('user_id', $userId)
            ->where('method', 'totp')
            ->delete();
    }

    public function hasConfigured(int $userId): bool
    {
        return DB::table('larajoom_user_mfa')
            ->where('user_id', $userId)
            ->where('method', 'totp')
            ->where('enabled', 1)
            ->exists();
    }

    /* ------------------------------------------------------------------
     * RFC 6238 / RFC 4226 (HOTP) — sin dependencias externas
     * ---------------------------------------------------------------- */

    protected function generateSecret(int $bytes): string
    {
        return $this->base32Encode(random_bytes($bytes));
    }

    protected function hotp(string $key, int $counter): string
    {
        $binary = pack('N*', 0) . pack('N*', $counter & 0xFFFFFFFF);
        $hash   = hash_hmac('sha1', $binary, $key, true);
        $offset = ord($hash[19]) & 0x0F;

        $value = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
    }

    protected function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';
        foreach (str_split($data) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }

        $result = '';
        foreach (str_split($bits, 5) as $chunk) {
            $result .= $alphabet[bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
        }

        return $result;
    }

    protected function base32Decode(string $data): string
    {
        $data = strtoupper(rtrim($data, '='));
        $bits = '';
        foreach (str_split($data) as $char) {
            $ord = ord($char);
            if ($ord >= 65 && $ord <= 90) {
                $bits .= str_pad(decbin($ord - 65), 5, '0', STR_PAD_LEFT);
            } elseif ($ord >= 50 && $ord <= 55) {
                $bits .= str_pad(decbin($ord - 24), 5, '0', STR_PAD_LEFT);
            }
        }

        $bytes = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $bytes .= chr(bindec($chunk));
            }
        }

        return $bytes;
    }
}
