@php
    use Aspectox\LaraJoom\Core\Access;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    $moduleParams = match (true) {
        is_array($module->params ?? null)       => $module->params,
        is_string($module->params ?? null) && $module->params !== '' => json_decode($module->params, true) ?: [],
        default => [],
    };

    $boxes = [];

    // Usuarios
    if ((int) ($moduleParams['show_users'] ?? 1) !== 0
        && Route::has('larajoom.admin.users.index')
        && Access::canWrite('com_users')
        && Schema::hasTable('larajoom_users')) {
        $boxes[] = [
            'color' => 'info',
            'icon'  => 'users',
            'title' => __('mod_quickicon::mod_quickicon.user_manager'),
            'number' => DB::table('larajoom_users')->count(),
            'url'   => route('larajoom.admin.users.index'),
        ];
    }

    // Autores (usuarios con artículos publicados)
    if ((int) ($moduleParams['show_authors'] ?? 1) !== 0
        && Route::has('larajoom.admin.users.index')
        && Access::canWrite('com_users')
        && Schema::hasTable('larajoom_users')
        && Schema::hasTable('larajoom_content')) {
        $boxes[] = [
            'color' => 'acent',
            'icon'  => 'author',
            'title' => __('mod_quickicon::mod_quickicon.authors'),
            'number' => DB::table('larajoom_content')->distinct()->count('created_by'),
            'url'   => route('larajoom.admin.users.index'),
        ];
    }

    // Artículos
    if ((int) ($moduleParams['show_articles'] ?? 1) !== 0
        && Route::has('larajoom.admin.articles.index')
        && Access::canWrite('com_content')
        && Schema::hasTable('larajoom_content')) {
        $boxes[] = [
            'color' => 'success',
            'icon'  => 'article',
            'title' => __('mod_quickicon::mod_quickicon.article_manager'),
            'number' => DB::table('larajoom_content')->count(),
            'url'   => route('larajoom.admin.articles.index'),
        ];
    }

    // Categorías
    if ((int) ($moduleParams['show_categories'] ?? 1) !== 0
        && Route::has('larajoom.admin.categories.index')
        && Access::canWrite('com_categories')
        && Schema::hasTable('larajoom_categories')) {
        $boxes[] = [
            'color' => 'warning',
            'icon'  => 'category',
            'title' => __('mod_quickicon::mod_quickicon.category_manager'),
            'number' => DB::table('larajoom_categories')->count(),
            'url'   => route('larajoom.admin.categories.index'),
        ];
    }

    // Tags
    if ((int) ($moduleParams['show_tags'] ?? 1) !== 0
        && Route::has('larajoom.admin.tags.index')
        && Access::canWrite('com_tags')
        && Schema::hasTable('larajoom_tags')) {
        $boxes[] = [
            'color' => 'success',
            'icon'  => 'tag',
            'title' => __('mod_quickicon::mod_quickicon.tags_manager'),
            'number' => DB::table('larajoom_tags')->count(),
            'url'   => route('larajoom.admin.tags.index'),
        ];
    }

    // Media
    if ((int) ($moduleParams['show_media'] ?? 1) !== 0
        && Route::has('larajoom.admin.media.index')
        && Access::canWrite('com_media')) {
        $boxes[] = [
            'color' => 'info',
            'icon'  => 'media',
            'title' => __('mod_quickicon::mod_quickicon.media_manager'),
            'number' => '',
            'url'   => route('larajoom.admin.media.index'),
        ];
    }

    // Elementos de menú
    if ((int) ($moduleParams['show_menuitems'] ?? 1) !== 0
        && Route::has('larajoom.admin.menuitems.index')
        && Access::canWrite('com_menus')
        && Schema::hasTable('larajoom_menu')) {
        $boxes[] = [
            'color' => 'acent',
            'icon'  => 'list',
            'title' => __('mod_quickicon::mod_quickicon.menuitems_manager'),
            'number' => DB::table('larajoom_menu')->count(),
            'url'   => route('larajoom.admin.menuitems.index'),
        ];
    }

    // Módulos
    if ((int) ($moduleParams['show_modules'] ?? 1) !== 0
        && Route::has('larajoom.admin.modules.index')
        && Access::canWrite('com_modules')
        && Schema::hasTable('larajoom_modules')) {
        $boxes[] = [
            'color' => 'warning',
            'icon'  => 'module',
            'title' => __('mod_quickicon::mod_quickicon.module_manager'),
            'number' => DB::table('larajoom_modules')->count(),
            'url'   => route('larajoom.admin.modules.index'),
        ];
    }

    // Plugins
    if ((int) ($moduleParams['show_plugins'] ?? 1) !== 0
        && Route::has('larajoom.admin.plugins.index')
        && Access::canWrite('com_plugins')
        && Schema::hasTable('larajoom_extensions')) {
        $boxes[] = [
            'color' => 'danger',
            'icon'  => 'plugins',
            'title' => __('mod_quickicon::mod_quickicon.plugins'),
            'number' => DB::table('larajoom_extensions')->where('type', 'plugin')->count(),
            'url'   => route('larajoom.admin.plugins.index'),
        ];
    }

    // Configuración global
    if ((int) ($moduleParams['show_config'] ?? 1) !== 0
        && Route::has('larajoom.admin.config.index')
        && Access::canWrite('com_config')
        && Schema::hasTable('larajoom_configuration')) {
        $boxes[] = [
            'color' => 'success',
            'icon'  => 'config',
            'title' => __('mod_quickicon::mod_quickicon.global_configuration'),
            'number' => DB::table('larajoom_configuration')->count(),
            'url'   => route('larajoom.admin.config.index'),
        ];
    }

    // Barra de progreso relativa al mayor contador entre las cajas visibles
    $maxCount = max(array_map(fn ($b) => is_numeric($b['number']) ? (int) $b['number'] : 0, $boxes));
    foreach ($boxes as &$box) {
        $box['percent'] = $maxCount > 0 && is_numeric($box['number'])
            ? (int) round(((int) $box['number'] / $maxCount) * 100)
            : 0;
    }
    unset($box);
@endphp

<section class="body">
    @foreach ($boxes as $box)
        <x-larajoom::info-box :color="$box['color']" :icon="$box['icon']" :title="$box['title']" :number="$box['number']" :percent="$box['percent']" />
    @endforeach
</section>
