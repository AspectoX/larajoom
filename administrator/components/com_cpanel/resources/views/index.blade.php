@extends('admin_tpl::index')

@section('title', __('larajoom_admin::admin.dashboard'))
@section('icon', 'dashboard')

@section('content')
    <div class="col-md-12 module-wrapper">
        {!! \Aspectox\LaraJoom\Core\Positions\PositionRenderer::renderInCards($position ?? 'cpanel') !!}
    </div>
@endsection
