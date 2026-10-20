<?php

namespace Aspectox\LaraJoom\Admin\Components\com_users\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Aspectox\LaraJoom\Admin\Components\com_users\Models\User;

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
            'email'    => 'required|email|max:255|unique:larajoom_users,email,' . $user->id,
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

    public function profile()
    {
        $user = Auth::guard('ax-larajoom')->user();
        $profile = $this->profileRow($user->getKey());

        return view('admin.components.com_users::users.profile', [
            'user'      => $user,
            'fieldsets' => $this->profileFieldsets($user, $profile),
        ]);
    }

    /**
     * Fila de perfil del usuario (larajoom_user_profile).
     */
    protected function profileRow(int $userId): ?object
    {
        if (!Schema::hasTable('larajoom_user_profile')) {
            return null;
        }

        return DB::table('larajoom_user_profile')->where('user_id', $userId)->first();
    }

    /**
     * Formatea una fecha de la base (string o Carbon) para mostrarla.
     */
    protected function formatDate($value, string $format = 'Y-m-d H:i:s'): string
    {
        if (empty($value)) {
            return '';
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format($format);
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    /**
     * Definición de las pestañas del perfil.
     *
     * Cada fieldset define sus campos y el componente con el que se renderiza,
     * igual que hace el formulario de configuración global.
     */
    protected function profileFieldsets($user, ?object $profile = null): array
    {
        $profile = $profile ?? (object) [];

        return [
            'account_details' => [
                'label'  => __('com_users::com_users.account_details'),
                'fields' => [
                    [
                        'name'    => 'avatar',
                        'type'    => 'file',
                        'label'   => __('com_users::com_users.avatar'),
                        'value'   => $profile->avatar ?? '',
                        'preview' => $this->avatarUrl($profile->avatar ?? null),
                    ],
                    [
                        'name'     => 'id',
                        'type'     => 'text',
                        'label'    => 'ID',
                        'value'    => $user->getKey(),
                        'readonly' => true,
                    ],
                    [
                        'name'     => 'name',
                        'type'     => 'text',
                        'label'    => __('com_users::com_users.user_name'),
                        'value'    => $profile->name ?? $user->name,
                        'required' => true,
                    ],
                    [
                        'name'     => 'username',
                        'type'     => 'text',
                        'label'    => __('com_users::com_users.user_name_username'),
                        'value'    => $profile->username ?? '',
                        'required' => true,
                    ],
                    [
                        'name'     => 'email',
                        'type'     => 'email',
                        'label'    => __('com_users::com_users.email'),
                        'value'    => $profile->email ?? $user->email,
                        'required' => true,
                    ],
                    [
                        'name'         => 'password',
                        'type'         => 'password',
                        'label'        => __('com_users::com_users.password'),
                        'autocomplete' => 'new-password',
                        'hint'         => __('com_users::com_users.password_requirements'),
                    ],
                    [
                        'name'         => 'password_confirmation',
                        'type'         => 'password',
                        'label'        => __('com_users::com_users.password_confirm'),
                        'autocomplete' => 'new-password',
                    ],
                    [
                        'name'     => 'registerDate',
                        'type'     => 'text',
                        'label'    => __('com_users::com_users.registration_date'),
                        'value'    => $this->formatDate($profile->registerDate ?? null)
                            ?: ($user->created_at?->format('Y-m-d H:i:s') ?? ''),
                        'readonly' => true,
                    ],
                    [
                        'name'     => 'lastvisitDate',
                        'type'     => 'text',
                        'label'    => __('com_users::com_users.last_visit_date'),
                        'value'    => $this->formatDate($profile->lastvisitDate ?? null),
                        'readonly' => true,
                    ],
                    [
                        'name'     => 'lastResetTime',
                        'type'     => 'text',
                        'label'    => __('com_users::com_users.last_reset_date'),
                        'value'    => $this->formatDate($profile->lastResetTime ?? null),
                        'readonly' => true,
                    ],
                    [
                        'name'     => 'resetCount',
                        'type'     => 'text',
                        'label'    => __('com_users::com_users.password_reset_count'),
                        'value'    => $profile->resetCount ?? 0,
                        'readonly' => true,
                    ],
                    [
                        'name'  => 'sendEmail',
                        'type'  => 'toggle',
                        'label' => __('com_users::com_users.receive_system_emails'),
                        'value' => (bool) ($profile->sendEmail ?? true),
                    ],
                    [
                        'name'  => 'block',
                        'type'  => 'toggle',
                        'label' => __('com_users::com_users.user_status'),
                        'value' => (bool) ($profile->block ?? false),
                        'hint'  => __('com_users::com_users.user_status_hint'),
                    ],
                ],
            ],
            'multifactor' => [
                'label'   => __('com_users::com_users.multifactor_auth'),
                'message' => __('com_users::com_users.multifactor_none'),
            ],
        ];
    }

    /**
     * URL pública del avatar del usuario.
     */
    protected function avatarUrl(?string $avatar): ?string
    {
        if (empty($avatar)) {
            return null;
        }

        if (preg_match('#^(https?:)?//#i', $avatar) || str_starts_with($avatar, '/')) {
            return $avatar;
        }

        if (is_file(public_path('storage/' . $avatar))) {
            return asset('storage/' . $avatar);
        }

        if (is_file(storage_path('app/public/' . $avatar))) {
            return Storage::disk('public')->url($avatar);
        }

        return null;
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('ax-larajoom')->user();

        $data = $request->validate([
            'avatar'   => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'name'     => 'required|string|max:100',
            'username' => 'required|string|max:150|alpha_dash',
            'email'    => 'required|email|max:155',
            'password' => 'nullable|string|min:12|confirmed',
            'sendEmail' => 'nullable|boolean',
            'block'    => 'nullable|boolean',
        ]);

        $profile = $this->profileRow($user->getKey()) ?? (object) [
            'registerDate'  => now(),
            'resetCount'    => 0,
            'lastvisitDate' => null,
            'lastResetTime' => null,
        ];

        $profile->name      = $data['name'];
        $profile->username  = $data['username'];
        $profile->email     = $data['email'];
        $profile->sendEmail = $request->boolean('sendEmail');
        $profile->block     = $request->boolean('block');

        // Avatar
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');

            // Reemplaza el avatar anterior
            $previous = $profile->avatar ?? null;

            if ($previous && !preg_match('#^(https?:)?//#i', $previous) && is_file(storage_path('app/public/' . $previous))) {
                @unlink(storage_path('app/public/' . $previous));
            }

            $profile->avatar = $path;
        }

        if (!empty($data['password'])) {
            $profile->password      = Hash::make($data['password']);
            $profile->lastResetTime = now();
            $profile->resetCount    = (int) ($profile->resetCount ?? 0) + 1;

            // La contraseña del perfil es la del usuario del panel
            $user->password = $profile->password;
        }

        if (!Schema::hasTable('larajoom_user_profile')) {
            return redirect()->route('larajoom.admin.profile')
                ->withErrors(['profile' => __('com_users::com_users.profile_table_missing')]);
        }

        DB::table('larajoom_user_profile')->updateOrInsert(
            ['user_id' => $user->getKey()],
            [
                'name'          => $profile->name,
                'username'      => $profile->username,
                'email'         => $profile->email,
                'password'      => $profile->password ?? null,
                'block'         => (int) $profile->block,
                'activated'     => 1,
                'sendEmail'     => (int) $profile->sendEmail,
                'params'        => $profile->params ?? null,
                'registerDate'  => $profile->registerDate ?? now(),
                'lastvisitDate' => $profile->lastvisitDate ?? null,
                'lastResetTime' => $profile->lastResetTime ?? null,
                'resetCount'    => (int) ($profile->resetCount ?? 0),
                'avatar'        => $profile->avatar ?? null,
                'created_at'    => $profile->created_at ?? now(),
                'updated_at'    => now(),
            ]
        );

        // El nombre y el email se reflejan también en el usuario del panel
        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->save();

        return redirect()->route('larajoom.admin.profile')->with('success', __('com_users::com_users.profile_saved'));
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
