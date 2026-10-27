<?php

namespace Aspectox\LaraJoom\Plugins\Actionlog\Joomla\Extension;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Joomla
{
    public function getName(): string
    {
        return 'actionlog.joomla';
    }

    /**
     * Registra una acción administrativa. Equivale a
     * plg_actionlog_joomla::onAfter... / JLog::add() en el flujo de Joomla.
     */
    public function log(string $extension, string $message, array $context = []): void
    {
        if (!Schema::hasTable('larajoom_action_logs')) {
            return;
        }

        $user = Auth::guard('ax-larajoom')->user();

        DB::table('larajoom_action_logs')->insert([
            'user_id'     => $user?->getKey(),
            'extension'   => $extension,
            'type'        => $context['type'] ?? 'admin',
            'ip_address'  => request()->ip(),
            'log_message' => json_encode([
                'message' => $message,
                'context' => $context,
            ]),
            'created_at'  => now(),
        ]);
    }

    /**
     * Atajos para eventos comunes de auditoría.
     */
    public function onUserSaved(?int $targetUserId, array $changes = []): void
    {
        $this->log('com_users', 'user_saved', ['target_user_id' => $targetUserId, 'changes' => $changes]);
    }

    public function onUserDeleted(?int $targetUserId): void
    {
        $this->log('com_users', 'user_deleted', ['target_user_id' => $targetUserId]);
    }

    public function onUserLogin(?int $userId): void
    {
        $this->log('com_users', 'user_login', ['target_user_id' => $userId]);
    }

    public function onArticleSaved(?int $articleId, string $action): void
    {
        $this->log('com_content', 'article_' . $action, ['article_id' => $articleId]);
    }

    public function onConfigChanged(array $changes = []): void
    {
        $this->log('com_config', 'configuration_changed', ['changes' => $changes]);
    }
}
