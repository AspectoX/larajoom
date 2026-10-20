@extends('admin_tpl::index')

@section('title', __('com_users::com_users.usergroups'))
@section('icon', 'roles')

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
    <x-larajoom::form :action="route('larajoom.admin.groups.store')" method="POST" id="adminForm">
        <fieldset>
            <legend>{{ __('com_users::com_users.new_group') }}</legend>

            <x-larajoom::input name="title" :label="__('com_users::com_users.group_title')" required />

            <x-larajoom::select name="parent_id" :label="__('com_users::com_users.parent_group')">
                <option value="0">{{ __('com_users::com_users.no_parent_group') }}</option>
                @foreach ($groups as $group)
                    <option value="{{ $group->id }}" @selected((int) old('parent_id') === (int) $group->id)>{{ $group->title }}</option>
                @endforeach
            </x-larajoom::select>
        </fieldset>
    </x-larajoom::form>

    @php
        $rows = [];
        $actions = [];

        foreach ($groups as $i => $group) {
            $rows[] = [
                $group->id,
                $group->title,
                $group->parent_id ? ($groups->firstWhere('id', $group->parent_id)->title ?? $group->parent_id) : __('com_users::com_users.no_parent_group'),
            ];

            $actions[$i] = [
                'edit'   => route('larajoom.admin.groups.edit', $group->id),
                'delete' => route('larajoom.admin.groups.destroy', $group->id),
            ];
        }
    @endphp

    <x-larajoom::table
        :headers="[
            'ID',
            __('com_users::com_users.group_title'),
            __('com_users::com_users.parent_group'),
        ]"
        :rows="$rows"
        :actions="$actions"
        :asset="'com_users'"
        :draggable="false"
    />
@endsection
