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
        @php
            $tabs = [
                [
                    'name'    => 'details',
                    'label'   => __('com_users::com_users.account_details'),
                    'active'  => true,
                    'view'    => 'admin.components.com_users::users.profile-tabs.details',
                    'data'    => ['fieldset' => $fieldsets['account_details'] ?? null],
                ],
            ];

            if (\Aspectox\LaraJoom\Core\Access::can('com_users', 'core.admin')) {
                $tabs[] = [
                    'name'    => 'usergroups',
                    'label'   => 'Assigned User Groups',
                    'active'  => false,
                    'view'    => 'admin.components.com_users::users.profile-tabs.usergroups',
                    'data'    => ['groups' => $groups ?? collect(), 'userGroupIds' => $userGroupIds ?? []],
                ];

                $tabs[] = [
                    'name'    => 'apitoken',
                    'label'   => 'Larajoom API Token',
                    'active'  => false,
                    'view'    => 'admin.components.com_users::users.profile-tabs.apitoken',
                    'data'    => [
                        'apiToken'  => $apiToken ?? null,
                        'apiEnabled' => $apiEnabled ?? true,
                        'apiReset'  => $apiReset ?? false,
                    ],
                ];
            }

            $tabs[] = [
                'name'    => 'multifactor',
                'label'   => __('com_users::com_users.multifactor_auth'),
                'active'  => false,
                'view'    => 'admin.components.com_users::users.profile-tabs.multifactor',
                'data'    => [
                    'label'            => $fieldsets['multifactor']['label'] ?? __('com_users::com_users.multifactor_auth'),
                    'twoFactorStatus'  => $twoFactorStatus ?? [],
                    'user'             => $user,
                ],
            ];
        @endphp

        <x-larajoom::tabs id="user-profile-tabs" :tabs="$tabs" />
    </x-larajoom::form>
@endsection