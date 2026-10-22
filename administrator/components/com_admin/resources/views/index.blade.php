@extends('admin_tpl::index')

@section('title', __('larajoom_admin::admin.dashboard'))
@section('icon', 'dashboard')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <main>
                <x-message />
                <div id="cpanel-modules">
                    <div class="cpanel-modules {{ $position ?? 'cpanel' }}">
                        <div class="col-md-12 module-wrapper">
                            <div class="card-columns">                    
                                {!! \Aspectox\LaraJoom\Core\Positions\PositionRenderer::renderInCards($position ?? 'cpanel') !!}
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection
