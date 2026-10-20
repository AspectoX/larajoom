<?php

namespace Aspectox\LaraJoom\Admin\Components\com_actionlogs\Models;

use Illuminate\Support\Facades\DB;

class ActionlogsModel
{
    public function getItems(?int $perPage = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        if ($perPage === null) {
            $perPage = config('larajoom.list_limit', 10);
        }
        return DB::table('larajoom_action_logs')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getConfig(): array
    {
        $rows = DB::table('larajoom_configuration')->where('group', 'actionlogs')->get();
        $config = [];
        foreach ($rows as $row) {
            $config[$row->name] = $row->value;
        }
        return array_merge([
            'ip_logging' => '0',
            'csv_delimiter' => ',',
            'loggable_extensions' => 'com_content,com_users',
            'loggable_api' => '0',
            'date_relative' => '1',
        ], $config);
    }

    public static function log(int $userId, string $action, string $extension, $itemId = null, ?string $ip = null): void
    {
        DB::table('larajoom_action_logs')->insert([
            'user_id' => $userId,
            'action' => $action,
            'extension' => $extension,
            'item_id' => $itemId,
            'ip' => $ip ?? request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
