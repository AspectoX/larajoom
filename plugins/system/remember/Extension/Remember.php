<?php

namespace Aspectox\LaraJoom\Plugins\System\Remember\Extension;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Remember
{
    public const COOKIE_NAME = 'larajoom_remember';

    public function getName(): string
    {
        return 'system.remember';
    }

    public function getParams(): array
    {
        return [
            'cookie_lifetime' => 60,   // días
            'key_length'      => 32,   // bytes de entropía del token
        ];
    }

    /**
     * Emite la cookie "Recordarme" tras un login exitoso con remember=true.
     * Usa el esquema series/token (solo el token viaja al navegador).
     */
    public function createRememberCookie(int $userId, bool $remember): void
    {
        if (!$remember) {
            return;
        }

        $params = $this->getParams();
        $keyLength = max(16, (int) $params['key_length']);

        $series = bin2hex(random_bytes(16));
        $token  = bin2hex(random_bytes($keyLength));
        $days   = max(1, (int) $params['cookie_lifetime']);

        DB::table('larajoom_rememberme')->insert([
            'user_id'    => $userId,
            'series'     => $series,
            'token'      => hash('sha256', $token),
            'user_agent' => Str::limit((string) request()->userAgent(), 250),
            'expires_at' => now()->addDays($days),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cookie::queue(Cookie::make(
            self::COOKIE_NAME,
            $series . ':' . $token,
            $days * 1440,
            '/',
            null,
            true,   // secure
            true,   // httpOnly
            false,
            'Lax'
        ));
    }

    /**
     * Valida la cookie de recuerdo y devuelve el user_id o null.
     * Si la serie existe pero el token no coincide, se revoca la serie
     * (posible robo de cookie).
     */
    public function getUserFromCookie(): ?int
    {
        $value = Cookie::get(self::COOKIE_NAME);
        if (!$value || !str_contains($value, ':')) {
            return null;
        }

        [$series, $token] = explode(':', $value, 2);

        $row = DB::table('larajoom_rememberme')
            ->where('series', $series)
            ->where('expires_at', '>', now())
            ->first();

        if (!$row) {
            return null;
        }

        if (!hash_equals($row->token, hash('sha256', $token))) {
            // Token inválido → posible robo: revocar la serie.
            DB::table('larajoom_rememberme')->where('id', $row->id)->delete();
            return null;
        }

        return (int) $row->user_id;
    }

    public function clearRememberCookie(): void
    {
        $value = Cookie::get(self::COOKIE_NAME);
        if ($value && str_contains($value, ':')) {
            $series = explode(':', $value, 2)[0];
            DB::table('larajoom_rememberme')->where('series', $series)->delete();
        }

        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }
}
