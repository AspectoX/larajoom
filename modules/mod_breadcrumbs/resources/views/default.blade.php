<nav aria-label="Miga de Pan">
    @if (!empty($module->showtitle))
        <h4>{{ $module->title }}</h4>
    @endif
    <ol>
        <li><a href="{{ route('larajoom.content.index') }}">Inicio</a></li>
        <li><span>{{ $currentPage ?? 'Contenido' }}</span></li>
    </ol>
</nav>
