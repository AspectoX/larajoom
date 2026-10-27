<?php

namespace Aspectox\LaraJoom\Plugins\Privacy\Actionlogs\Extension;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ActionLogs
{
    public function getName(): string
    {
        return 'privacy.actionlogs';
    }

    /**
     * Registra en el log de acciones un evento de privacidad (solicitud,
     * exportación o eliminación de datos).
     */
    public function logEvent(string $type, ?int $userId, array $metadata = []): void
    {
        if (!Schema::hasTable('larajoom_action_logs')) {
            return;
        }

        DB::table('larajoom_action_logs')->insert([
            'user_id'     => $userId,
            'extension'   => 'com_privacy',
            'type'        => $type,           // privacy.request | privacy.export | privacy.remove
            'ip_address'  => request()->ip(),
            'log_message' => json_encode($metadata),
            'created_at'  => now(),
        ]);
    }

    /**
     * Exporta los eventos de privacidad de un usuario.
     */
    public function exportUser(?int $userId): array
    {
        if (!$userId || !Schema::hasTable('larajoom_action_logs')) {
            return [];
        }

        return DB::table('larajoom_action_logs')
            ->where('user_id', $userId)
            ->where('extension', 'com_privacy')
            ->get()
            ->map(fn ($r) => [
                'type'       => $r->type,
                'log_message' => json_decode($r->log_message ?? '{}', true),
                'created_at' => $r->created_at,
            ])
            ->all();
    }
}
