@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    $params = config('larajoom', []);
    $language = app()->getLocale();

    $option = request()->get('option', 'com_login');
    $view = request()->get('view', 'login');
    $layout = request()->get('layout', 'default');
    $task = request()->get('task', 'display');
    $cpanel = $option === 'com_cpanel' || ($option === 'com_admin' && $view === 'help');
    $hiddenMenu = request()->get('hidemainmenu');
    $sidebarState = request()->cookie('atumSidebarState', '');

    $logoBrandLarge = url('media/templates/administrator/atum/images/logos/LaraJoom-white.png');
    $logoBrandSmall = url('media/templates/administrator/atum/images/logos/iso-LaraJoom.png');

    $panelName = 'LaraJoom';
    $cpanelManifest = app(\Aspectox\LaraJoom\Core\ExtensionLoader::class)->getBasePath()
        . DIRECTORY_SEPARATOR . 'administrator'
        . DIRECTORY_SEPARATOR . 'components'
        . DIRECTORY_SEPARATOR . 'com_login'
        . DIRECTORY_SEPARATOR . 'manifest.json';

    if (is_file($cpanelManifest)) {
        $manifest = json_decode((string) file_get_contents($cpanelManifest), true);
        $pageTitle = trim((string) ($manifest['title'] ?? ''));
    }


    $statusModules = \Aspectox\LaraJoom\Core\LayoutHelper::render('status', ['modules' => 'status']);


    $siteName = '';
    try {
        $siteName = trim((string) DB::table('larajoom_configuration')
            ->where('name', 'sitename')
            ->value('value'));
    } catch (\Throwable $e) {
        $siteName = '';
    }
    if ($siteName === '') {
        $siteName = env('APP_NAME');
    }


    $documentTitle = match ((int) ($params['sitename_pagetitles'] ?? 0)) {
        1 => $pageTitle . ' - ' . $panelName,
        2 => $panelName . ' - ' . $pageTitle,
        default => $pageTitle,
    };
@endphp
<!DOCTYPE html>
<html lang="{{ $language }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="generator" content="LaraJoom - Open Source Content Management">
    <title>{{ $documentTitle }}</title>
    <link rel="stylesheet" href="{{ url('media/system/css/reset.css') }}">
    <link rel="stylesheet" href="{{ url('media/system/css/root.css') }}">
    <link rel="stylesheet" href="{{ url('media/system/css/grid.css') }}">
    <link rel="stylesheet" href="{{ url('media/system/css/components.css') }}">
    <link rel="stylesheet" href="{{ url('media/templates/administrator/atum/css/template.css') }}">
    @stack('styles')
    @stack('scripts')

    {{-- Alpine.js: interactividad del panel (ej. mostrar/ocultar contraseña).
         Se carga sin bloquear el renderizado de la página. --}}
    <script>
        if (!window.Alpine) {
            var alpine = document.createElement('script');
            alpine.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js';
            alpine.defer = true;
            document.head.appendChild(alpine);
        }
    </script>
</head>

<body class="admin {{ $option }} view-{{ $view }} layout-{{ $layout }}">

    <noscript>
        <x-message type="danger" :message="__('tmp::atum.lj_warn_javascript')" />
    </noscript>

    <header id="header" class="header">
        <div class="header-title">
            <div class="content-logo">
                <div class="logo">
                    <img src="{{ $logoBrandLarge }}" alt="{{ $panelName }}" loading="eager" decoding="async">
                    <img src="{{ $logoBrandSmall }}" class="logo-collapsed" alt="{{ $panelName }}" loading="eager" decoding="async">
                </div>
            </div>
        </div>
        <div class="header-items">
            <div class="header-item">
                <a href="{{ url('/') }}" class="header-item-content" title="{{ __('com_login::com_login.open_frontend') }}" target="_blank">
                    <span class="icon" aria-hidden="true">
                        @icon('go-to')
                    </span>
                    <div class="header-item-text">{{ $siteName }}</div>
                </a>
            </div>
        </div>
    </header>

    <div id="wrapper" class="wrapper flex-grow-1">
        <div class="container-fluid container-main">
            <section id="content" class="content">
                <main class="content-login">
                    @yield('content')
                </main>
            </section>
        </div>

        <aside id="sidebar-wrapper" class="sidebar-wrapper" aria-labelledby="sidebar-section-heading">
            <div id="main-brand" class="main-brand">
                <p class="h1">{{ $siteName }}</p>
                <p class="h2">{{ __('com_login::com_login.login_form') }}</p>
            </div>
            <div id="sidebar">
                <div class="module-wrapper">
                    <div class="card">
                        <h3 class="card-header">{{ __('com_login::com_login.need_support') }}</h3>
                        <div class="module-body">
                            <section class="loginsupport">
                                <p>{{ __('com_login::com_login.help_here') }}</p>
                                <ul class="list-unstyled">
                                    <li>
                                        <a href="https://extensions.joomla.org/" target="_blank"
                                            rel="nofollow noopener">
                                            <span class="icon">@icon('go-to')</span>
                                            Joomla! Extensions Directory
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" target="_blank" rel="nofollow noopener">
                                            <span class="icon">@icon('go-to')</span>
                                            LaraJoom! Documentation
                                        </a>
                                    </li>
                                </ul>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

</body>

</html>
