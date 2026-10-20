@extends('admin_tpl::index')

@section('content')
<x-larajoom::form :action="route('larajoom.admin.login.store')" method="POST" id="form-login">
    <input type="hidden" name="task" value="login">

    <x-larajoom::input name="username" type="text" :label="__('com_login::com_login.username')" required autofocus autocomplete="username" />
    <x-larajoom::input name="passwd" type="password" :label="__('com_login::com_login.password')" required autocomplete="current-password" />

    <button type="submit" class="ax-btn color-primary full">
        {{ __('com_login::com_login.login_button') }}
    </button>
</x-larajoom::form>
@endsection
