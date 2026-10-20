<?php

namespace Aspectox\LaraJoom\Admin\Components\com_actionlogs\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Aspectox\LaraJoom\Admin\Components\com_actionlogs\Models\ActionlogsModel;

class ActionlogsController extends Controller
{
    public function index()
    {
        $limit = config('larajoom.list_limit', 10);
        $items = (new ActionlogsModel())->getItems($limit);
        return view('admin.components.com_actionlogs::actionlogs.default', compact('items'));
    }

    public function exportCsv(Request $request, string $delimiter = ',')
    {
        $items = DB::table('larajoom_action_logs')->get();
        $filename = 'actionlogs_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($items, $delimiter) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['user_id', 'action', 'extension', 'item_id', 'ip', 'created_at'], $delimiter);
            foreach ($items as $item) {
                fputcsv($file, [$item->user_id, $item->action, $item->extension, $item->item_id, $item->ip, $item->created_at], $delimiter);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
