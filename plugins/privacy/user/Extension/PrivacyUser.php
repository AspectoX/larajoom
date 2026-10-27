<?php

namespace Aspectox\LaraJoom\Plugins\Privacy\User\Extension;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PrivacyUser
{
    public function getName(): string
    {
        return 'privacy.user';
    }

    /**
     * Exporta los datos personales del usuario (GDPR Art. 15).
     */
    public function onPrivacyExportUser(?int $userId): array
    {
        if (!$userId || !Schema::hasTable('larajoom_users')) {
            return [];
        }

        $user = DB::table('larajoom_users')->where('id', $userId)->first();
        if (!$user) {
            return [];
        }

        $data = [
            'name'     => $user->name,
            'username' => $user->username,
            'email'    => $user->email,
            'registerDate' => $user->registerDate,
        ];

        // Mapa de grupos
        if (Schema::hasTable('larajoom_user_usergroup_map')) {
            $data['groups'] = DB::table('larajoom_user_usergroup_map')
                ->where('user_id', $userId)
                ->pluck('group_id')
                ->all();
        }

        // Perfil extendido
        if (Schema::hasTable('larajoom_user_profile')) {
            $profile = DB::table('larajoom_user_profile')->where('user_id', $userId)->first();
            if ($profile) {
                $data['profile'] = (array) $profile;
            }
        }

        return $data;
    }

    /**
     * Indica si los datos del usuario pueden eliminarse.
     */
    public function onPrivacyCanRemoveUser(?int $userId, array $options = []): bool
    {
        return $userId !== null;
    }

    /**
     * Elimina los datos del usuario (GDPR Art. 17).
     */
    public function onPrivacyRemoveUser(?int $userId): void
    {
        if (!$userId) {
            return;
        }

        foreach ([
            'larajoom_user_usergroup_map',
            'larajoom_user_profile',
            'larajoom_privacy_consents',
            'larajoom_rememberme',
            'larajoom_user_mfa',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->where('user_id', $userId)->delete();
            }
        }

        if (Schema::hasTable('larajoom_users')) {
            DB::table('larajoom_users')->where('id', $userId)->delete();
        }
    }
}
