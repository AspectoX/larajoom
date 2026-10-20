@extends('site_tpl::index')

@section('content')
<form method="post" action="{{ route('larajoom.login.store') }}">
    @csrf
    <label for="username">Username</label>
    <input id="username" type="text" name="username" required />
    <label for="password">Password</label>
    <input id="password" type="password" name="password" required />
    <button type="submit">Login</button>
</form>
@endsection
