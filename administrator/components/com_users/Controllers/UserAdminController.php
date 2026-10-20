<?php

namespace Aspectox\LaraJoom\Admin\Components\com_users\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserAdminController extends Controller
{
    public function index()
    {
        $limit = config('larajoom.list_limit', 10);
        $users = User::latest()->paginate($limit);
        $groups = DB::table('larajoom_usergroups')->get()->keyBy('id');
        return view('admin.components.com_users::users.index', compact('users', 'groups'));
    }

    public function groups()
    {
        $groups = DB::table('larajoom_usergroups')->get();
        return view('admin.components.com_users::groups.index', compact('groups'));
    }

    public function storeGroup(Request $request)
    {
        $data = $request->validate([
            'title'     => 'required|string|max:100',
            'parent_id' => 'required|integer',
        ]);
        DB::table('larajoom_usergroups')->insert([
            'title'      => $data['title'],
            'parent_id'  => $data['parent_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->route('larajoom.admin.groups.index');
    }

    public function levels()
    {
        $levels = DB::table('larajoom_viewlevels')->get();
        $groups = DB::table('larajoom_usergroups')->get();
        return view('admin.components.com_users::levels.index', compact('levels', 'groups'));
    }

    public function storeLevel(Request $request)
    {
        $data = $request->validate([
            'title'  => 'required|string|max:100',
            'groups' => 'nullable|array',
        ]);
        DB::table('larajoom_viewlevels')->insert([
            'title'      => $data['title'],
            'rules'      => json_encode($data['groups'] ?? []),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->route('larajoom.admin.levels.index');
    }
}
