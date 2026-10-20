<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administración - Usuarios</title>
</head>
<body>
    <header>
        <h1>Panel de Administración: Usuarios</h1>
        <nav>
            <a href="{{ route('larajoom.admin.groups.index') }}">[ Grupos de Usuarios ]</a>
            <a href="{{ route('larajoom.admin.levels.index') }}">[ Niveles de Acceso ]</a>
            <a href="/administrator">[ Volver al Panel ]</a>
        </nav>
    </header>

    <main>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Registrado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No hay usuarios registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <nav>
            {{ $users->links() }}
        </nav>
    </main>
</body>
</html>
