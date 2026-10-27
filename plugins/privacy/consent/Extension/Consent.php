<?php

namespace Aspectox\LaraJoom\Plugins\Privacy\Consent\Extension;

use Illuminate\Support\Facades\DB;

class Consent
{
    public function getName(): string
    {
        return 'privacy.consent';
    }

    /**
     * Registra un consentimiento explícito.
     */
    public function record(?int $userId, array $details = []): void
    {
        DB::table('larajoom_privacy_consents')->insert([
            'user_id'    => $userId,
            'ip'         => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 250),
            'details'    => json_encode($details),
            'created_at' => now(),
        ]);
    }

    /**
     * Indica si el usuario aceptó la política vigente.
     */
    public function hasConsented(?int $userId, ?string $policyVersion = null): bool
    {
        if (!$userId) {
            return false;
        }

        $query = DB::table('larajoom_privacy_consents')
            ->where('user_id', $userId);

        if ($policyVersion !== null) {
            $query->where('details', 'like', '%"version":"' . $policyVersion . '"%');
        }

        return $query->exists();
    }

    /**
     * Exporta los consentimientos de un usuario (GDPR Art. 15).
     */
    public function exportUser(?int $userId): array
    {
        if (!$userId) {
            return [];
        }

        return DB::table('larajoom_privacy_consents')
            ->where('user_id', $userId)
            ->get()
            ->map(fn ($r) => [
                'ip'         => $r->ip,
                'user_agent' => $r->user_agent,
                'details'    => json_decode($r->details ?? '{}', true),
                'created_at' => $r->created_at,
            ])
            ->all();
    }

    /**
     * Elimina todos los consentimientos de un usuario (GDPR Art. 17).
     */
    public function removeUser(?int $userId): void
    {
        if (!$userId) {
            return;
        }

        DB::table('larajoom_privacy_consents')->where('user_id', $userId)->delete();
    }
}
