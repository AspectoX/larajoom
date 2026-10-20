@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Str;
    use Aspectox\LaraJoom\Core\Access;

    // El módulo de menú de administración conoce su menú
    $menutype = 'mainmenu';

    // Items publicados del menú
    $rows = collect();
    if (Schema::hasTable('larajoom_menu')) {
        $rows = DB::table('larajoom_menu')
            ->where('menutype', $menutype)
            ->where('published', 1)
            ->orderBy('ordering')
            ->get();
    }

    $currentPath = trim(request()->path(), '/');
    $grouped = $rows->groupBy('parent_id');

    // Arma el árbol recursivo por parent_id + ordering
    $buildTree = function (int $parentId) use (&$buildTree, $grouped, $currentPath) {
        $nodes = [];

        foreach (($grouped->get($parentId) ?? collect())->sortBy('ordering') as $row) {
            $path = trim((string) $row->link, '/');
            // Exacto, o por prefijo solo si el enlace tiene subruta
            // (evita que /administrator marque todo el menú como activo)
            $isExact = $path !== '' && $path === $currentPath;
            $isChild = $path !== '' && Str::contains($path, '/') && Str::startsWith($currentPath, $path . '/');
            // El item "home" (Dashboard) también se marca activo en la raíz del panel
            $isHome = (bool) $row->home && $currentPath === 'administrator';
            $active = $isExact || $isChild || $isHome;

            $link = $row->link;
            if ($link && $link !== '#' && !Str::startsWith($link, ['http://', 'https://', '//'])) {
                $link = url($link);
            }

            // Permisos: el usuario debe tener el nivel de acceso del item
            // y permiso de lectura (core.manage) sobre el componente destino
            if (!Access::canAccessLevel((int) $row->access)) {
                continue;
            }

            $asset = Access::assetForLink((string) $row->link);

            if ($asset !== null && !Access::canRead($asset)) {
                continue;
            }

            $nodes[] = (object) [
                'title'     => $row->title,
                'link'      => $link ?: '#',
                'type'      => $row->type,
                'icon'      => $row->icon,
                'show_icon' => (bool) $row->show_icon,
                'active'    => $active,
                'children'  => $buildTree((int) $row->id),
            ];
        }

        return $nodes;
    };

    $tree = $buildTree(0);
@endphp

@if (count($tree))
    <nav class="main-nav-container" aria-label="{{ $module->title ?? 'Main menu' }}">
        <ul class="main-nav">
            @foreach ($tree as $item)
                @include('modules.mod_menu::item', ['item' => $item, 'level' => 1])
            @endforeach
        </ul>
    </nav>
@endif
