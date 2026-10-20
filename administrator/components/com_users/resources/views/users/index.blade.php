@extends('admin_tpl::index')

@section('title', __('com_users::com_users.users'))
@section('icon', 'users')

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('com_users::com_users.usergroups'),
            'icon'  => 'roles',
            'url'   => route('larajoom.admin.groups.index'),
        ],
        [
            'label' => __('com_users::com_users.access_levels'),
            'icon'  => 'permissions',
            'url'   => route('larajoom.admin.levels.index'),
        ],
    ]" />
@endsection

@section('content')
    @php
        $rows = [];
        $actions = [];

        foreach ($users as $i => $user) {
            $groupNames = collect($userGroups[$user->id] ?? [])
                ->map(fn ($map) => $groups[$map->group_id]->title ?? null)
                ->filter()
                ->implode(', ');

            $rows[] = [
                $user->id,
                $user->name,
                $user->email,
                $groupNames !== '' ? $groupNames : __('com_users::com_users.no_user_group'),
                $user->created_at?->format('Y-m-d'),
            ];

            $actions[$i] = [
                'edit'   => route('larajoom.admin.users.edit', $user->id),
                'delete' => route('larajoom.admin.users.destroy', $user->id),
            ];
        }
    @endphp

    <x-larajoom::table
        :headers="[
            'ID',
            __('com_users::com_users.user_name'),
            __('com_users::com_users.email'),
            __('com_users::com_users.usergroups'),
            __('com_users::com_users.registered'),
        ]"
        :rows="$rows"
        :actions="$actions"
        :draggable="false"
    />

    <x-larajoom::ax-pagination :paginator="$users" />
@endsection
