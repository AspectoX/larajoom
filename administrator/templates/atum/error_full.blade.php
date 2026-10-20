@php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$wa = null;
$params = config('larajoom', []);
$language = app()->getLocale();

$option = request()->get('option', '');
$view = request()->get('view', '');
$layout = request()->get('layout', 'default');
$task = request()->get('task', 'display');
$cpanel = $option === 'com_cpanel';
$hiddenMenu = request()->get('hidemainmenu');
$logoBrandLarge = url('media/templates/administrator/atum/images/logos/LaraJoom-white.png');
$logoBrandSmall = url('media/templates/administrator/atum/images/logos/iso-LaraJoom.png');

$statusModules = '';
if (Schema::hasTable('larajoom_modules')) {
    $modules = DB::table('larajoom_modules')
        ->where('position', 'status')
        ->where('published', 1)
        ->orderBy('ordering', 'asc')
        ->get();
    foreach ($modules as $mod) {
        $statusModules .= view('modules.' . $mod->module . '::default', ['module' => $mod])->render();
    }
}
@endphp

<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="utf-8">
    @stack('styles')
    @stack('scripts')
</head>

<body class="admin {{ $option }} view-{{ $view }} layout-{{ $layout }}{{ $task ? ' task-' . $task : '' }}">

    <noscript>
        <div class="alert alert-danger" role="alert">
            {{ __('tmp::atum.lj_warn_javascript') }}
        </div>
    </noscript>

    <header id="header" class="header d-flex">
        <div class="header-title d-flex">
            <div class="d-flex align-items-center">
                <div class="logo">
                    <img src="{{ $logoBrandLarge }}" alt="{{ __('tmp::atum.lj_back_to_cp') }}" loading="eager" decoding="async">
                    <img src="{{ $logoBrandSmall }}" class="logo-collapsed" alt="{{ __('tmp::atum.lj_back_to_cp') }}" loading="eager" decoding="async">
                </div>
            </div>
            @position('title')
        </div>
        {!! $statusModules !!}
    </header>

    <div id="wrapper" class="d-flex wrapper{{ $hiddenMenu ? '0' : '' }}">
        <div class="container-fluid container-main">
            @if (!$cpanel)
                <a class="btn btn-subhead d-md-none d-lg-none d-xl-none" data-bs-toggle="collapse" data-bs-target=".subhead-collapse">
                    {{ __('tmp::atum.lj_toolbar') }}
                    <span class="icon-wrench"></span>
                </a>
                <div id="subhead" class="subhead mb-3">
                    <div id="container-collapse" class="container-collapse"></div>
                    <div class="row">
                        <div class="col-md-12">
                            @position('toolbar')
                        </div>
                    </div>
                </div>
            @endif
            <section id="content" class="content">
                <x-message />
                @position('top')
                <div class="row">
                    <div class="col-md-12">
                        <h1>{{ __('tmp::atum.lj_error_occurred') }}</h1>
                        <blockquote class="blockquote">
                            <span class="badge bg-secondary">{{ $error->getCode() ?? 500 }}</span>
                            {{ $error->getMessage() ?? __('tmp::atum.lj_default_error') }}
                        </blockquote>
                        @if ($error->debug ?? false)
                            <div>{{ $error->renderBacktrace() ?? '' }}</div>
                        @endif
                        <p>
                            <a href="{{ url('/) }}" class="btn btn-secondary">
                                <span class="icon-dashboard" aria-hidden="true"></span>
                                {{ __('tmp::atum.lj_cpanel_link') }}
                            </a>
                        </p>
                    </div>
                    @if (Schema::hasTable('larajoom_modules'))
                        @position('bottom')
                    @endif
                </div>
            </section>
        </div>

        @if (!Schema::hasTable('larajoom_modules') || !$hiddenMenu)
            <div id="sidebar-wrapper" class="sidebar-wrapper sidebar-menu" {{ $hiddenMenu ? 'data-hidden="' . $hiddenMenu . '"' : '' }}>
                <div id="sidebarmenu">
                    <div class="sidebar-toggle item item-level-1">
                        <a id="menu-collapse" href="#" aria-label="{{ __('tmp::atum.lj_sidebar_heading') }}">
                            <span id="menu-collapse-icon" aria-hidden="true">
                                <span class="icon-toggle-on">@icon('toggle-on')</span>
                                <span class="icon-toggle-off">@icon('toggle-off')</span>
                            </span>
                            <span class="sidebar-item-title">{{ __('tmp::atum.lj_sidebar_heading') }}</span>
                        </a>
                    </div>
                    @position('menu')
                </div>
            </div>
        @endif
    </div>
    @position('debug')
</body>
</html>
