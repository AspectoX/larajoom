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
                'label'   => __('com_users::com_users.account_details'),
                'active'  => true,
                'view'    => 'admin.components.com_users::users.profile-tabs.details',
                'data'    => ['fieldset' => $fieldsets['account_details'] ?? null],
            ],
            [
                'name'    => 'usergroups',
                'label'   => 'Assigned User Groups',
                'view'    => 'admin.components.com_users::users.profile-tabs.usergroups',
                'data'    => ['groups' => $groups, 'userGroupIds' => $userGroupIds ?? []],
            ],
            [
                'name'    => 'apitoken',
                'label'   => 'Larajoom API Token',
                'view'    => 'admin.components.com_users::users.profile-tabs.apitoken',
                'data'    => ['apiToken' => null, 'apiEnabled' => true, 'apiReset' => false],
            ],
            [
                'name'    => 'multifactor',
                'label'   => 'Multi-factor Authentication',
                'view'    => 'admin.components.com_users::users.profile-tabs.multifactor-create',
                'data'    => [],
            ],
        ]" />
    </x-larajoom::form>
@endsection