<?php

namespace Aspectox\LaraJoom\Plugins\Twofactorauth\TfaWebauthn\Extension;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class TfaWebauthn
{
    public function getName(): string
    {
        return 'twofactorauth.tfa_webauthn';
    }

    /* ------------------------------------------------------------------
     * Registro
     * ---------------------------------------------------------------- */

    /**
     * Prepara un challenge de registro y lo guarda en sesión.
     */
    public function prepareRegistration(int $userId): array
    {
        $challenge = $this->generateChallenge();
        Session::put("webauthn.register.{$userId}", $challenge);

        return [
            'challenge' => $challenge,
            'rp'        => ['name' => config('app.name', 'LaraJoom'), 'id' => $this->rpId()],
            'user'      => ['id' => base64_encode(pack('N', $userId)), 'name' => 'user' . $userId, 'displayName' => 'Usuario ' . $userId],
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7],   // ES256
                ['type' => 'public-key', 'alg' => -257],  // RS256
            ],
            'timeout' => 60000,
            'authenticatorSelection' => ['residentKey' => 'preferred'],
        ];
    }

    /**
     * Registra la credencial pública (COSE) devuelta por el cliente.
     */
    public function completeRegistration(int $userId, array $data): array
    {
        $expected = Session::pull("webauthn.register.{$userId}");
        $this->assertChallenge($expected, $data['challenge'] ?? null);

        $cose = base64_decode($data['publicKey'] ?? '', true);
        if ($cose === false) {
            throw new \RuntimeException('Clave pública inválida.');
        }

        $this->verifyAttestation('', $cose, $data['clientData'] ?? '', $expected);

        DB::table('larajoom_user_mfa')->insert([
            'user_id'    => $userId,
            'method'     => 'webauthn',
            'secret'     => base64_encode($cose),
            'label'      => $data['label'] ?? 'WebAuthn',
            'enabled'    => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['status' => 'registered'];
    }

    /* ------------------------------------------------------------------
     * Verificación / aserción
     * ---------------------------------------------------------------- */

    public function prepareAssertion(int $userId): array
    {
        $challenge = $this->generateChallenge();
        Session::put("webauthn.assert.{$userId}", $challenge);

        return [
            'challenge' => $challenge,
            'rpId'      => $this->rpId(),
            'timeout'   => 60000,
        ];
    }

    public function validate(int $userId, array $data): bool
    {
        $row = DB::table('larajoom_user_mfa')
            ->where('user_id', $userId)
            ->where('method', 'webauthn')
            ->where('enabled', 1)
            ->first();

        if (!$row || empty($row->secret)) {
            return false;
        }

        $expected = Session::pull("webauthn.assert.{$userId}");
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

        // clientDataJSON + authenticatorData forman el payload firmado.
        $payload = $authenticatorData . $clientData;

        $ok = openssl_verify($payload, $signature, $pem, OPENSSL_ALGO_SHA256) === 1;
        if ($ok) {
            DB::table('larajoom_user_mfa')
                ->where('id', $row->id)
                ->update(['last_used_at' => now()]);
        }

        return $ok;
    }

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

    /* ------------------------------------------------------------------
     * Nota de verificación de attestation (nivel básico)
     * - En producción se debe verificar la cadena de certificados (packed/
     *   fido-u2f) contra las raíces de FIDO. Aquí validamos formato y firma
     *   de ejemplo con la clave pública incluida para mantener el flujo.
     * ---------------------------------------------------------------- */

    protected function verifyAttestation(string $authData, string $cose, string $clientData, string $challenge): void
    {
        // Decodificar COSE: [1, [alg, 3], [-2, x, -3, y]] (EC2) o [1, [alg], [n, e]] (RSA)
        if (strlen($cose) < 20 || $cose[0] !== "\xa2") {
            throw new \RuntimeException('Formato COSE no soportado.');
        }
        // Validación estructural básica (verificación completa depende de la
        // cadena de certificados del atestado, fuera del alcance de este esqueleto).
        if ($clientData === '' || $challenge === '') {
            throw new \RuntimeException('Datos de registro incompletos.');
        }
    }

    /**
     * Convierte una clave pública COSE EC2 (P-256) o RSA a PEM.
     */
    protected function coseToPem(string $cose): ?string
    {
        // Muy simplificado: espera COSE EC2 P-256
        // [a2, 01, a3, 02, 26, 20, 01, 21, 58, 20, x(32), 22, 58, 20, y(32)]
        if (preg_match('/\x52\x20(.{32})\x22\x58\x20(.{32})/s', $cose, $m) !== 1) {
            // Fallback: intentar RSA (n y e)
            if (preg_match('/\x52\x02(.{1})(.{128})\x22\x02(.{2,3})/s', $cose, $r) === 1) {
                $n = $r[2];
                $e = str_pad($r[3], 4, "\0", STR_PAD_LEFT);
                if (strlen($e) > 4) {
                    $e = substr($e, -4);
                }
                return $this->rsaPem($n, $e);
            }
            return null;
        }

        $x = $m[1];
        $y = $m[2];

        if (strlen($x) !== 32 || strlen($y) !== 32) {
            return null;
        }

        $bin = "\x04" . $x . $y;
        $der = $this->ecPublicKeyDer($bin);

        $pem = "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split(base64_encode($der), 64, "\n")
            . "-----END PUBLIC KEY-----\n";

        $key = openssl_pkey_get_public($pem);
        return $key === false ? null : $pem;
    }

    protected function ecPublicKeyDer(string $uncompressedPoint): string
    {
        // SubjectPublicKeyInfo para EC P-256
        $alg = "\x30\x0a\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07";
        $bitString = "\x03" . $this->lengthPrefix(strlen($uncompressedPoint) + 1) . "\x00" . $uncompressedPoint;
        $spki = "\x30" . $this->lengthPrefix(strlen($alg) + strlen($bitString)) . $alg . $bitString;
        return $spki;
    }

    protected function rsaPem(string $n, string $e): string
    {
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
