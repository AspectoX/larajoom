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
            'label' => __('larajoom_admin::admin.back'),
            'icon'  => 'back',
            'url'   => route('larajoom.admin.users.index'),
            'color' => 'warning',
        ],        
    ]" />
@endsection

@section('content')
    <x-larajoom::form :action="route('larajoom.admin.users.store')" method="POST" id="adminForm" enctype="multipart/form-data">
        <x-larajoom::tabs id="user-create-tabs" :tabs="[
            [
                'name'    => 'details',
                'label'   => __('com_users::com_users.new_user'),
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
                    'twoFactorStatus'  => [],
                    'user'             => null,
                ],
            ],
        ]" />
    </x-larajoom::form>
@endsection
