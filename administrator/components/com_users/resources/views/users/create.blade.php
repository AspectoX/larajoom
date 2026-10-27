@extends('admin_tpl::index')

@section('title', __('com_users::com_users.new_user'))
@section('icon', 'add')

@section('content')
    @include('admin.components.com_users::users.edit', [
        'user' => (object) [
            'id' => null,
            'name' => old('name', ''),
            'username' => old('username', ''),
            'email' => old('email', ''),
            'password' => '',
        ],
        'groups' => $groups ?? [],
        'fieldsets' => $fieldsets ?? [],
    ])
@endsection