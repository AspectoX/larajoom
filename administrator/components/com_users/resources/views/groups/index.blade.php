<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administración - Grupos de Usuarios</title>
</head>
<body>
    <header>
        <h1>Grupos de Usuarios (ACL)</h1>
        <nav>
            <a href="{{ route('larajoom.admin.users.index') }}">← Volver a Usuarios</a>
        </nav>
    </header>

    <main>
        <section>
            <h2>Crear Nuevo Grupo</h2>
            <form action="{{ route('larajoom.admin.groups.store') }}" method="POST">
                @csrf
                <p>
                    <label for="title">Título del Grupo:</label><br>
                    <input type="text" id="title" name="title" required>
                </p>
                <p>
                    <label for="parent_id">Grupo Padre:</label><br>
                    <select id="parent_id" name="parent_id">
                        <option value="0">- Ninguno (Raíz) -</option>
                        @foreach ($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->title }}</option>
                        @endforeach
                    </select>
                </p>
                <p>
                    <button type="submit">Crear Grupo</button>
                </p>
            </form>
        </section>

        <section>
            <h2>Grupos Existentes</h2>
            <table border="1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>ID Padre</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($groups as $group)
                        <tr>
                            <td>{{ $group->id }}</td>
                            <td>{{ $group->title }}</td>
                            <td>{{ $group->parent_id }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">No hay grupos definidos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
