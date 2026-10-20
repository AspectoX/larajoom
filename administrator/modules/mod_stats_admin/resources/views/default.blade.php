@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    $rows = [];

    // Versión de PHP
    $rows[] = ['title' => __('mdl::mod_stats_admin.php'), 'icon' => 'config', 'data' => PHP_VERSION];

    // Versión de Laravel
    $rows[] = ['title' => __('mdl::mod_stats_admin.laravel'), 'icon' => 'source', 'data' => app()->version()];

    // Base de datos
    if (Schema::hasTable('larajoom_users')) {
        try {
            $database = DB::connection()->getDatabaseName();
            $version  = DB::selectOne('select version() as v')->v ?? '';
        } catch (\Throwable $e) {
            $database = config('database.default');
            $version  = '';
        }

        $rows[] = ['title' => $database, 'icon' => 'database', 'data' => $version];
    }

    // Sistema operativo
    $rows[] = ['title' => __('mdl::mod_stats_admin.os'), 'icon' => 'memory', 'data' => PHP_OS_FAMILY];

    // Espacio en disco
    $free = @disk_free(base_path());

    if ($free !== false) {
        $rows[] = [
            'title' => __('mdl::mod_stats_admin.disk'),
            'icon'  => 'files',
            'data'  => number_format($free / 1073741824, 2) . ' GB',
        ];
    }

    // Contadores del sitio
    if (Schema::hasTable('larajoom_users')) {
        $rows[] = [
            'title' => __('mdl::mod_stats_admin.users'),
            'icon'  => 'users',
            'data'  => DB::table('larajoom_users')->count(),
            'link'  => route('larajoom.admin.users.index'),
        ];
    }

    if (Schema::hasTable('larajoom_content')) {
        $rows[] = [
            'title' => __('mdl::mod_stats_admin.articles'),
            'icon'  => 'article',
            'data'  => DB::table('larajoom_content')->count(),
            'link'  => route('larajoom.admin.articles.index'),
        ];
    }

    if (Schema::hasTable('larajoom_extensions')) {
        $rows[] = [
            'title' => __('mdl::mod_stats_admin.extensions'),
            'icon'  => 'component',
            'data'  => DB::table('larajoom_extensions')->count(),
            'link'  => route('larajoom.admin.installer.index'),
        ];
    }

    if (Schema::hasTable('larajoom_modules')) {
        $rows[] = [
            'title' => __('mdl::mod_stats_admin.modules'),
            'icon'  => 'module',
            'data'  => DB::table('larajoom_modules')->count(),
            'link'  => route('larajoom.admin.modules.index'),
        ];
    }
@endphp

<ul class="list-unstyled">
    @foreach ($rows as $row)
        <li>
            <span class="icon" aria-hidden="true">@icon($row['icon'])</span>
            {{ $row['title'] }}:
            @if (!empty($row['link']))
                <a href="{{ $row['link'] }}">{{ $row['data'] }}</a>
            @else
                {{ $row['data'] }}
            @endif
        </li>
    @endforeach
</ul>
