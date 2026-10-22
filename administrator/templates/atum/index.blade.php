@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    $params = config('larajoom', []);
    $language = app()->getLocale();

    $option = request()->get('option', '');
    $view = request()->get('view', '');
    $layout = request()->get('layout', 'default');
    $task = request()->get('task', 'display');
    // Panel de control por ruta real (pretty URL) o por option legado (query ?option=)
    $cpanel = $option === 'com_cpanel' || ($option === 'com_admin' && $view === 'help')
        || in_array(request()->path(), ['administrator', 'administrator/index.php', 'administrator/cpanel', 'administrator/cpanel/index.php'], true);
    $hiddenMenu = request()->get('hidemainmenu');
    $sidebarState = request()->cookie('atumSidebarState', '');

    $logoBrandLarge = url('media/templates/administrator/atum/images/logos/LaraJoom-white.png');
    $logoBrandSmall = url('media/templates/administrator/atum/images/logos/iso-LaraJoom.png');

    $logoBrandLargeAlt = '';
    $logoBrandSmallAlt = '';

    $statusModules = \Aspectox\LaraJoom\Core\LayoutHelper::render('status', ['modules' => 'status']);

    // Nombre del panel administrativo: se toma del manifest de com_cpanel
    $panelName = 'LaraJoom';
    $cpanelManifest = app(\Aspectox\LaraJoom\Core\ExtensionLoader::class)->getBasePath()
        . DIRECTORY_SEPARATOR . 'administrator'
        . DIRECTORY_SEPARATOR . 'components'
        . DIRECTORY_SEPARATOR . 'com_cpanel'
        . DIRECTORY_SEPARATOR . 'manifest.json';

    if (is_file($cpanelManifest)) {
        $manifest = json_decode((string) file_get_contents($cpanelManifest), true);
        $panelName = trim((string) ($manifest['title'] ?? '')) ?: $panelName;
    }

    // Título del documento según sitename_pagetitles: 0 = solo título, 1 = título + sitio, 2 = sitio + título
    $pageTitle = trim((string) view()->yieldContent('title')) ?: $panelName;
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

<body
    class="admin {{ $option }} view-{{ $view }} layout-{{ $layout }}{{ $task ? ' task-' . $task : '' }}">

    <noscript>
        <x-message type="danger" :message="__('tmp::atum.lj_warn_javascript')" />
    </noscript>

    @position('customtop')

    <header id="header" class="header">
        <div class="header-inside">
            <div class="header-title flex">
                <div class="content-logo">
                    @if ($hiddenMenu || $cpanel)
                        <div class="logo {{ $sidebarState === 'closed' ? 'small' : '' }}">
                            <img src="{{ $logoBrandLarge }}" alt="{{ $logoBrandLargeAlt }}" loading="eager"
                                decoding="async">
                            <img src="{{ $logoBrandSmall }}" class="logo-collapsed" alt="{{ $logoBrandSmallAlt }}"
                                loading="eager" decoding="async">
                        </div>
                    @else
                        <div class="logo {{ $sidebarState === 'closed' ? 'small' : '' }}">
                            <img src="{{ $logoBrandLarge }}" alt="{{ __('tmp::atum.lj_back_to_cp') }}" loading="eager"
                                decoding="async">
                            <img src="{{ $logoBrandSmall }}" class="logo-collapsed"
                                alt="{{ __('tmp::atum.lj_back_to_cp') }}" loading="eager" decoding="async">
                        </div>
                    @endif
                </div>
                @position('title')
            </div>
            {!! $statusModules !!}
        </div>
    </header>

    <div id="wrapper" class="flex wrapper{{ $hiddenMenu ? '0' : '' }} {{ $sidebarState }}">
        @if (!$hiddenMenu)
            <div id="sidebar-wrapper" class="sidebar-wrapper sidebar-menu"
                {{ $hiddenMenu ? 'data-hidden="' . $hiddenMenu . '"' : '' }}>
                <div id="sidebarmenu" class="sidebar-sticky">
                    <div class="sidebar-toggle">
                        <a id="menu-collapse" href="#" aria-label="{{ __('tmp::atum.lj_toggle_sidebar') }}">
                            <span id="menu-collapse-icon" aria-hidden="true">
                                <span class="icon-toggle-on">@icon('toggle-on')</span>
                                <span class="icon-toggle-off">@icon('toggle-off')</span>
                            </span>
                            <span class="sidebar-item-title">{{ __('tmp::atum.lj_toggle_sidebar') }}</span>
                        </a>
                    </div>
                    @position('menu')
                </div>
            </div>
        @endif

        <div class="container-fluid container-main">
            @if (!$cpanel)
                <div id="subhead-container" class="subhead mb-3">
                    @yield('toolbar')
                </div>
            @endif
            <section id="content" class="content">
                @position('top')

                @yield('content')

                @if (Schema::hasTable('larajoom_modules'))
                    @position('bottom')
                @endif
            </section>
        </div>
    </div>
    @position('debug')
    <script src="{{ url('media/templates/administrator/atum/js/template.js') }}"></script>
</body>

</html>



