@extends('admin_tpl::index')

@section('title', __('com_cpanel::com_cpanel.dashboard'))
@section('icon', 'dashboard')

@section('content')
    <section id="content" class="content">
        @include('admin.components.com_cpanel::dashboard')
    </section>
@endsection
