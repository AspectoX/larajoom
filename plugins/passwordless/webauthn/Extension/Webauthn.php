<?php

namespace Aspectox\LaraJoom\Plugins\Passwordless\Webauthn\Extension;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class Webauthn
{
    public function getName(): string
    {
        return 'passwordless.webauthn';
    }

    /**
     * Inicia el registro de una passkey para login sin contraseña.
     */
    public function prepareRegistration(int $userId): array
    {
        $challenge = $this->generateChallenge();
        Session::put("passwordless.register.{$userId}", $challenge);

        return [
            'challenge' => $challenge,
            'rp'        => ['name' => config('app.name', 'LaraJoom'), 'id' => $this->rpId()],
            'user'      => ['id' => base64_encode(pack('N', $userId)), 'name' => 'user' . $userId, 'displayName' => 'Usuario ' . $userId],
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7],
                ['type' => 'public-key', 'alg' => -257],
            ],
            'timeout' => 60000,
        ];
    }

    public function completeRegistration(int $userId, array $data): array
    {
        $expected = Session::pull("passwordless.register.{$userId}");
        $this->assertChallenge($expected, $data['challenge'] ?? null);

        $cose = base64_decode($data['publicKey'] ?? '', true);
        if ($cose === false) {
            throw new \RuntimeException('Clave pública inválida.');
        }

        DB::table('larajoom_user_mfa')->insert([
            'user_id'    => $userId,
            'method'     => 'passwordless',
            'secret'     => base64_encode($cose),
            'label'      => $data['label'] ?? 'Passkey',
            'enabled'    => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['status' => 'registered'];
    }

    public function prepareAssertion(int $userId): array
    {
        $challenge = $this->generateChallenge();
        Session::put("passwordless.assert.{$userId}", $challenge);

        return ['challenge' => $challenge, 'rpId' => $this->rpId(), 'timeout' => 60000];
    }

    /**
     * Valida la aserción y devuelve el user_id si es correcta.
     */
    public function validate(int $userId, array $data): bool
    {
        $row = DB::table('larajoom_user_mfa')
            ->where('user_id', $userId)
            ->where('method', 'passwordless')
            ->where('enabled', 1)
            ->first();

        if (!$row || empty($row->secret)) {
            return false;
        }

        $expected = Session::pull("passwordless.assert.{$userId}");
        $this->assertChallenge($expected, $data['challenge'] ?? null);

        $signature = base64_decode($data['signature'] ?? '', true);
        $clientData = (string) ($data['clientData'] ?? '');
        $authenticatorData = (string) ($data['authenticatorData'] ?? '');

        if ($signature === false) {
            return false;
        }

        $publicKey = base64_decode($row->secret, true);
        if ($publicKey === false) {
            return false;
        }

        $pem = $this->coseToPem($publicKey);
        if ($pem === null) {
            return false;
        }

        $ok = openssl_verify($authenticatorData . $clientData, $signature, $pem, OPENSSL_ALGO_SHA256) === 1;
        if ($ok) {
            DB::table('larajoom_user_mfa')
                ->where('id', $row->id)
                ->update(['last_used_at' => now()]);
        }

        return $ok;
    }

    /* ------------------------------------------------------------------ */

    protected function assertChallenge(?string $expected, ?string $provided): void
    {
        if ($expected === null) {
            throw new \RuntimeException('Sin challenge activo.');
        }
        if (!hash_equals($expected, (string) $provided)) {
            throw new \RuntimeException('Challenge no coincide.');
        }
    }

    protected function generateChallenge(): string
    {
        return base64_encode(random_bytes(32));
    }

    protected function rpId(): string
    {
        return (string) config('app.url', 'localhost');
    }

    protected function coseToPem(string $cose): ?string
    {
        if (preg_match('/\x52\x20(.{32})\x22\x58\x20(.{32})/s', $cose, $m) === 1) {
            $x = $m[1];
            $y = $m[2];
            if (strlen($x) !== 32 || strlen($y) !== 32) {
                return null;
            }
            $uncompressed = "\x04" . $x . $y;
            $alg = "\x30\x0a\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07";
            $bitString = "\x03" . $this->lengthPrefix(strlen($uncompressed) + 1) . "\x00" . $uncompressed;
            $spki = "\x30" . $this->lengthPrefix(strlen($alg) + strlen($bitString)) . $alg . $bitString;

            return "-----BEGIN PUBLIC KEY-----\n"
                . chunk_split(base64_encode($spki), 64, "\n")
                . "-----END PUBLIC KEY-----\n";
        }

        if (preg_match('/\x52\x02(.{1})(.{128})\x22\x02(.{2,3})/s', $cose, $r) === 1) {
            $n = $r[2];
            $e = str_pad($r[3], 4, "\0", STR_PAD_LEFT);
            if (strlen($e) > 4) {
                $e = substr($e, -4);
            }
            $rsa = "\x30" . $this->lengthPrefix(strlen($n) + strlen($e) + 6)
                . "\x02" . $this->lengthPrefix(strlen($n)) . $n
                . "\x02" . $this->lengthPrefix(strlen($e)) . $e;
            $alg = "\x30\x0d\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01\x05\x00";
            $bitString = "\x03" . $this->lengthPrefix(strlen($rsa) + 1) . "\x00" . $rsa;
            $spki = "\x30" . $this->lengthPrefix(strlen($alg) + strlen($bitString)) . $alg . $bitString;

            return "-----BEGIN PUBLIC KEY-----\n"
                . chunk_split(base64_encode($spki), 64, "\n")
                . "-----END PUBLIC KEY-----\n";
        }

        return null;
    }

    protected function lengthPrefix(int $len): string
    {
        if ($len < 128) {
            return chr($len);
        }
        $bytes = '';
        while ($len > 0) {
            $bytes = chr($len & 0xFF) . $bytes;
            $len >>= 8;
        }
        return chr(0x80 | strlen($bytes)) . $bytes;
    }
}
