@extends('admin_tpl::index')

@section('title', __('com_users::com_users.access_levels'))
@section('icon', 'permissions')

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('larajoom_admin::admin.save'),
            'icon'  => 'save',
            'type'  => 'submit',
            'form'  => 'adminForm',
            'name'  => 'task',
            'value' => 'save',
        ],
        [
            'label' => __('com_users::com_users.back_to_users'),
            'icon'  => 'back',
            'url'   => route('larajoom.admin.users.index'),
            'color' => 'warning',
        ],
    ]" />
@endsection

@section('content')
    <x-larajoom::form :action="route('larajoom.admin.levels.store')" method="POST" id="adminForm">
        <fieldset>
            <legend>{{ __('com_users::com_users.new_access_level') }}</legend>

            <x-larajoom::input name="title" :label="__('com_users::com_users.level_title')" required />

            <fieldset>
                <legend>{{ __('com_users::com_users.groups_with_access') }}</legend>

                @forelse ($groups as $group)
                    <x-larajoom::checkbox
                        name="groups[]"
                        :id="'access_group_' . $group->id"
                        :value="$group->id"
                        :label="$group->title"
                        :checked="in_array($group->id, old('groups', []))"
                    />
                @empty
                    <p>{{ __('com_users::com_users.no_records_groups') }}</p>
                @endforelse
            </fieldset>
        </fieldset>
    </x-larajoom::form>

    @php
        $rows = [];
        $actions = [];

        foreach ($levels as $i => $level) {
            $allowed = collect(json_decode($level->rules ?? '[]', true) ?: [])
                ->map(fn ($groupId) => $groups->firstWhere('id', $groupId)->title ?? $groupId)
                ->implode(', ');

            $rows[] = [
                $level->id,
                $level->title,
                $allowed !== '' ? $allowed : __('com_users::com_users.no_user_group'),
            ];

            $actions[$i] = [
                'edit'   => route('larajoom.admin.levels.edit', $level->id),
                'delete' => route('larajoom.admin.levels.destroy', $level->id),
            ];
        }
    @endphp

    <x-larajoom::table
        :headers="[
            'ID',
            __('com_users::com_users.level_title'),
            __('com_users::com_users.groups_with_access'),
        ]"
        :rows="$rows"
        :actions="$actions"
        :draggable="false"
    />
@endsection
