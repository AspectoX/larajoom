@extends('admin_tpl::index')

@section('title', __('com_users::com_users.new_user'))
@section('icon', 'add')

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
            'label' => __('larajoom_admin::admin.cancel'),
            'icon'  => 'cancel',
            'url'   => route('larajoom.admin.users.index'),
            'color' => 'warning',
        ],
    ]" />
@endsection

@section('content')
    <x-larajoom::form :action="route('larajoom.admin.users.store')" method="POST" id="adminForm">
        @csrf
        <fieldset>
            <legend>{{ __('com_users::com_users.new_user') }}</legend>

            <x-larajoom::input name="name" :label="__('com_users::com_users.user_name')" :value="old('name')" required />
            <x-larajoom::input name="username" :label="__('com_users::com_users.username')" :value="old('username')" required />
            <x-larajoom::input name="email" type="email" :label="__('com_users::com_users.email')" :value="old('email')" required />
            <x-larajoom::input name="password" type="password" :label="__('com_users::com_users.password')" required />

            <fieldset>
                <legend>{{ __('com_users::com_users.usergroups') }}</legend>

                @forelse ($groups as $group)
                    <x-larajoom::toggle
                        name="groups[]"
                        :id="'user_group_' . $group->id"
                        :value="$group->id"
                        :label="$group->title"
                        :checked="old('groups', []) && in_array((int) $group->id, old('groups', []))"
                    />
                @empty
                    <p>{{ __('com_users::com_users.no_records_groups') }}</p>
                @endforelse
            </fieldset>
        </fieldset>
    </x-larajoom::form>
@endsection
