<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $article->title }}</title>
</head>
<body>
    <header>
        @position('top')
    </header>

    <main>
        <article>
            <header>
                <h1>{{ $article->title }}</h1>
                <p>Visitas: {{ $article->hits }} | Fecha: {{ $article->created_at->format('d/m/Y') }}</p>
            </header>

            <div>
                {{ $article->introtext }}
                {{ $article->fulltext }}
            </div>

            <footer>
                <p><a href="{{ route('larajoom.content.index') }}">← Volver a destacados</a></p>
            </footer>
        </article>
    </main>

    <aside>
        @position('right')
    </aside>

    <footer>
        @position('footer')
    </footer>
</body>
</html>
