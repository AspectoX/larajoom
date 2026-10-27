<?php

namespace Aspectox\LaraJoom\Admin\Components\com_privacy\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PrivacyController extends Controller
{
    public function index()
    {
        $stats = [
            'pending'   => $this->countRequests('pending'),
            'completed' => $this->countRequests('completed'),
            'consents'  => Schema::hasTable('larajoom_privacy_consents')
                ? DB::table('larajoom_privacy_consents')->count()
                : 0,
        ];

        return view('admin.components.com_privacy::index', compact('stats'));
    }

    public function requests()
    {
        $requests = collect();
        if (Schema::hasTable('larajoom_privacy_requests')) {
            $requests = DB::table('larajoom_privacy_requests')->orderByDesc('requested_at')->paginate(20);
        }

        return view('admin.components.com_privacy::requests', compact('requests'));
    }

    public function consents()
    {
        $consents = collect();
        if (Schema::hasTable('larajoom_privacy_consents')) {
            $consents = DB::table('larajoom_privacy_consents')->orderByDesc('created_at')->paginate(20);
        }

        return view('admin.components.com_privacy::consents', compact('consents'));
    }

    public function export(int $id)
    {
        $request = DB::table('larajoom_privacy_requests')->where('id', $id)->firstOrFail();

        // Recopila datos del usuario (GDPR Art. 15)
        $data = ['request' => (array) $request];

        if (Schema::hasTable('larajoom_users')) {
            $user = DB::table('larajoom_users')
                ->where('id', $request->user_id)
                ->orWhere('email', $request->email)
                ->first();

            if ($user && Schema::hasTable('larajoom_user_profile')) {
                $data['profile'] = DB::table('larajoom_user_profile')
                    ->where('user_id', $user->id)
                    ->first();
            }
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="privacy-request-' . (int) $id . '.json"',
        ]);
    }

    public function complete(int $id)
    {
        DB::table('larajoom_privacy_requests')
            ->where('id', $id)
            ->update(['status' => 'completed', 'completed_at' => now(), 'updated_at' => now()]);

        return redirect()->route('larajoom.admin.privacy.requests')->with('success', 'Solicitud completada.');
    }

    public function destroy(int $id)
    {
        DB::table('larajoom_privacy_requests')->where('id', $id)->delete();

        return redirect()->route('larajoom.admin.privacy.requests')->with('success', 'Solicitud eliminada.');
    }

    protected function countRequests(string $status): int
    {
        if (!Schema::hasTable('larajoom_privacy_requests')) {
            return 0;
        }

        return DB::table('larajoom_privacy_requests')->where('status', $status)->count();
    }
}
