@extends('admin_tpl::index')

@section('title', __('com_users::com_users.edit_access_level'))
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
            'url'   => route('larajoom.admin.levels.index'),
            'color' => 'warning',
        ],
    ]" />
@endsection

@section('content')
    <x-larajoom::form :action="route('larajoom.admin.levels.update', $level->id)" method="PUT" id="adminForm">
        <fieldset>
            <legend>{{ __('com_users::com_users.edit_access_level') }}</legend>

            <x-larajoom::input name="title" :label="__('com_users::com_users.level_title')" :value="old('title', $level->title)" required />

            <fieldset>
                <legend>{{ __('com_users::com_users.groups_with_access') }}</legend>

                @forelse ($groups as $group)
                    <x-larajoom::checkbox
                        name="groups[]"
                        :id="'level_group_' . $group->id"
                        :value="$group->id"
                        :label="$group->title"
                        :checked="in_array((int) $group->id, array_map('intval', $allowed))"
                    />
                @empty
                    <p>{{ __('com_users::com_users.no_records_groups') }}</p>
                @endforelse
            </fieldset>
        </fieldset>
    </x-larajoom::form>
@endsection
