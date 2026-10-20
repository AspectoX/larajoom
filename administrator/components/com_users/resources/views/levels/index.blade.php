<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administración - Niveles de Acceso</title>
</head>
<body>
    <header>
        <h1>Niveles de Acceso de Visualización</h1>
        <nav>
            <a href="{{ route('larajoom.admin.users.index') }}">← Volver a Usuarios</a>
        </nav>
    </header>

    <main>
        <section>
            <h2>Crear Nuevo Nivel de Acceso</h2>
            <form action="{{ route('larajoom.admin.levels.store') }}" method="POST">
                @csrf
                <p>
                    <label for="title">Título del Nivel:</label><br>
                    <input type="text" id="title" name="title" required>
                </p>

                <fieldset>
                    <legend>Grupos con Acceso</legend>
                    @foreach ($groups as $group)
                        <label>
                            <input type="checkbox" name="groups[]" value="{{ $group->id }}">
                            {{ $group->title }}
                        </label><br>
                    @endforeach
                </fieldset>

                <p>
                    <button type="submit">Crear Nivel</button>
                </p>
            </form>
        </section>

        <section>
            <h2>Niveles de Acceso Registrados</h2>
            <table border="1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Grupos con Permiso</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($levels as $level)
                        <tr>
                            <td>{{ $level->id }}</td>
                            <td>{{ $level->title }}</td>
                            <td>{{ $level->rules }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">No hay niveles de acceso definidos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
