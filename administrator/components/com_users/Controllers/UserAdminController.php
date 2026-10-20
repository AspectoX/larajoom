<?php

namespace Aspectox\LaraJoom\Admin\Components\com_users\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class UserAdminController extends Controller
{
    public function index()
    {
        $limit = config('larajoom.list_limit', 10);
        $users = User::latest()->paginate($limit);
        $groups = DB::table('larajoom_usergroups')->get()->keyBy('id');

        $userGroups = collect();

        if (Schema::hasTable('larajoom_user_usergroup_map')) {
            $userGroups = DB::table('larajoom_user_usergroup_map')
                ->select('user_id', 'group_id')
                ->get()
                ->groupBy('user_id');
        }

        return view('admin.components.com_users::users.index', compact('users', 'groups', 'userGroups'));
    }

    public function edit(int $id)
    {
        $user = User::findOrFail($id);

        $groups = DB::table('larajoom_usergroups')->orderBy('id')->get();
        $userGroupIds = $this->userGroupIds($user->id);

        return view('admin.components.com_users::users.edit', compact('user', 'groups', 'userGroupIds'));
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'groups'   => 'nullable|array',
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        $this->syncUserGroups($user->id, $data['groups'] ?? []);

        return redirect()
            ->route('larajoom.admin.users.index')
            ->with('success', __('com_users::com_users.user_saved'));
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);

        if (Schema::hasTable('larajoom_user_usergroup_map')) {
            DB::table('larajoom_user_usergroup_map')->where('user_id', $user->id)->delete();
        }

        $user->delete();

        return redirect()
            ->route('larajoom.admin.users.index')
            ->with('success', __('com_users::com_users.user_deleted'));
    }

    public function groups()
    {
        $groups = DB::table('larajoom_usergroups')->orderBy('id')->get();
        return view('admin.components.com_users::groups.index', compact('groups'));
    }

    public function editGroup(int $id)
    {
        $group = DB::table('larajoom_usergroups')->where('id', $id)->firstOrFail();
        $groups = DB::table('larajoom_usergroups')->orderBy('id')->get();

        return view('admin.components.com_users::groups.edit', compact('group', 'groups'));
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

    public function updateGroup(Request $request, int $id)
    {
        $data = $request->validate([
            'title'     => 'required|string|max:100',
            'parent_id' => 'required|integer',
        ]);

        DB::table('larajoom_usergroups')->where('id', $id)->update([
            'title'      => $data['title'],
            'parent_id'  => $data['parent_id'],
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('larajoom.admin.groups.index')
            ->with('success', __('com_users::com_users.group_saved'));
    }

    public function destroyGroup(int $id)
    {
        DB::table('larajoom_usergroups')->where('id', $id)->update([
            'parent_id'  => 0,
            'updated_at' => now(),
        ]);

        DB::table('larajoom_usergroups')->where('id', $id)->delete();

        return redirect()
            ->route('larajoom.admin.groups.index')
            ->with('success', __('com_users::com_users.group_deleted'));
    }

    public function levels()
    {
        $levels = DB::table('larajoom_viewlevels')->orderBy('id')->get();
        $groups = DB::table('larajoom_usergroups')->orderBy('id')->get();
        return view('admin.components.com_users::levels.index', compact('levels', 'groups'));
    }

    public function editLevel(int $id)
    {
        $level = DB::table('larajoom_viewlevels')->where('id', $id)->firstOrFail();
        $groups = DB::table('larajoom_usergroups')->orderBy('id')->get();
        $allowed = json_decode($level->rules ?? '[]', true) ?: [];

        return view('admin.components.com_users::levels.edit', compact('level', 'groups', 'allowed'));
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

    public function updateLevel(Request $request, int $id)
    {
        $data = $request->validate([
            'title'  => 'required|string|max:100',
            'groups' => 'nullable|array',
        ]);

        DB::table('larajoom_viewlevels')->where('id', $id)->update([
            'title'      => $data['title'],
            'rules'      => json_encode($data['groups'] ?? []),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('larajoom.admin.levels.index')
            ->with('success', __('com_users::com_users.level_saved'));
    }

    public function destroyLevel(int $id)
    {
        DB::table('larajoom_viewlevels')->where('id', $id)->delete();

        return redirect()
            ->route('larajoom.admin.levels.index')
            ->with('success', __('com_users::com_users.level_deleted'));
    }

    /**
     * IDs de los grupos asignados a un usuario.
     */
    protected function userGroupIds(int $userId): array
    {
        if (!Schema::hasTable('larajoom_user_usergroup_map')) {
            return [];
        }

        return DB::table('larajoom_user_usergroup_map')
            ->where('user_id', $userId)
            ->pluck('group_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Reemplaza los grupos asignados a un usuario.
     */
    protected function syncUserGroups(int $userId, array $groupIds): void
    {
        if (!Schema::hasTable('larajoom_user_usergroup_map')) {
            return;
        }

        DB::table('larajoom_user_usergroup_map')->where('user_id', $userId)->delete();

        foreach (array_unique(array_map('intval', $groupIds)) as $groupId) {
            if ($groupId > 0) {
                DB::table('larajoom_user_usergroup_map')->insert([
                    'user_id'  => $userId,
                    'group_id' => $groupId,
                ]);
            }
        }
    }
}
