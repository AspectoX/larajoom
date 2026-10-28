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
    <x-larajoom::form :action="route('larajoom.admin.profile.update')" method="PUT" id="adminForm" enctype="multipart/form-data">
        <x-larajoom::tabs id="user-profile-tabs" :tabs="[
            [
                'name'    => 'details',
                'label'   => __('com_users::com_users.account_details'),
                'active'  => true,
                'view'    => 'admin.components.com_users::users.profile-tabs.details',
                'data'    => ['fieldset' => $fieldsets['account_details'] ?? null],
            ],
            [
                'name'    => 'multifactor',
                'label'   => __('com_users::com_users.multifactor_auth'),
                'active'  => false,
                'view'    => 'admin.components.com_users::users.profile-tabs.multifactor',
                'data'    => [
                    'label'            => $fieldsets['multifactor']['label'] ?? __('com_users::com_users.multifactor_auth'),
                    'twoFactorStatus'  => $twoFactorStatus ?? [],
                    'user'             => $user,
                ],
            ],
        ]" />
    </x-larajoom::form>
@endsection
