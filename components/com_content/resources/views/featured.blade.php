<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Artículos Destacados</title>
</head>
<body>
    <header>
        @position('top')
    </header>

    <main>
        <h1>Artículos Destacados</h1>

        @forelse ($articles as $article)
            <article>
                <header>
                    <h2>
                        <a href="{{ route('larajoom.article.show', $article->alias) }}">
                            {{ $article->title }}
                        </a>
                    </h2>
                    <p>Publicado: <time datetime="{{ $article->created_at }}">{{ $article->created_at->format('d/m/Y') }}</time></p>
                </header>

                <div>
                    {!! $article->introtext !!}
                </div>

                <footer>
                    <a href="{{ route('larajoom.article.show', $article->alias) }}">Leer más...</a>
                </footer>
            </article>
            <hr>
        @empty
            <p>No hay artículos publicados.</p>
        @endforelse

        <nav>
            {{ $articles->links() }}
        </nav>
    </main>

    <aside>
        @position('right')
    </aside>

    <footer>
        @position('footer')
    </footer>
</body>
</html>
