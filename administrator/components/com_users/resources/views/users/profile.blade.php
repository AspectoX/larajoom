@extends('admin_tpl::index')

@section('title', __('com_users::com_users.my_profile'))
@section('icon', 'profile')

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
    ]" />
@endsection

@section('content')
    <x-larajoom::form :action="route('larajoom.admin.profile.update')" method="PUT" id="adminForm">
        <fieldset>
            <legend>{{ __('com_users::com_users.my_profile') }}</legend>

            <x-larajoom::input name="name" :label="__('com_users::com_users.user_name')" :value="old('name', $user->name)" required />

            <x-larajoom::input name="email" type="email" :label="__('com_users::com_users.email')" :value="old('email', $user->email)" required />

            <x-larajoom::input name="password" type="password" :label="__('com_users::com_users.password')" autocomplete="new-password" />

            <x-larajoom::input name="password_confirmation" type="password" :label="__('com_users::com_users.password_confirm')" autocomplete="new-password" />
        </fieldset>
    </x-larajoom::form>
@endsection
