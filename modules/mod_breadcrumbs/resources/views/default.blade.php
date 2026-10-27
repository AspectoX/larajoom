@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Str;

    $crumbs = [];
    $path = trim(request()->path(), '/');

    // Raíz: siempre Inicio (sitio) o Panel (administración)
    $isAdmin = Str::startsWith($path, 'administrator');
    $crumbs[] = (object) [
        'title' => $isAdmin ? 'Panel' : 'Inicio',
        'link'  => $isAdmin ? url('/administrator') : route('larajoom.content.index'),
    ];

    if ($path !== '' && !$isAdmin) {
        // /article/{alias} → título del artículo
        if (preg_match('#^article/(.+)$#', $path, $m) && Schema::hasTable('larajoom_content')) {
            $article = DB::table('larajoom_content')
                ->where('alias', $m[1])
                ->where('published', 1)
                ->first(['title']);
            if ($article) {
                $crumbs[] = (object) ['title' => $article->title, 'link' => null];
            }
        }
        // /category/{alias} → título de la categoría
        elseif (preg_match('#^category/(.+)$#', $path, $m) && Schema::hasTable('larajoom_categories')) {
            $category = DB::table('larajoom_categories')
                ->where('alias', $m[1])
                ->where('published', 1)
                ->first(['title']);
            if ($category) {
                $crumbs[] = (object) ['title' => $category->title, 'link' => null];
            }
        }
        // Genérico: muestra los segmentos como texto
        else {
            foreach (explode('/', $path) as $segment) {
                $label = Str::title(str_replace(['-', '_'], ' ', $segment));
                if ($label !== '') {
                    $crumbs[] = (object) ['title' => $label, 'link' => null];
                }
            }
        }
    }
@endphp

<nav aria-label="Miga de Pan">
    @if (!empty($module->showtitle))
        <h4>{{ $module->title }}</h4>
    @endif
    <ol>
        @foreach ($crumbs as $i => $crumb)
            <li>
                @if ($crumb->link && $i < count($crumbs) - 1)
                    <a href="{{ $crumb->link }}">{{ $crumb->title }}</a>
                @else
                    <span>{{ $crumb->title }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
