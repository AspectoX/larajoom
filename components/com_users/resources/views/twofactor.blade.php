<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación en Dos Pasos</title>
</head>
<body>
    <main>
        <h1>Verificación en Dos Pasos</h1>

        @if ($errors->any())
            <div>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('larajoom.twofactor.post') }}">
            @csrf
            <div>
                <label for="code">Código de verificación:</label>
                <input type="text" id="code" name="code" maxlength="64" required autofocus autocomplete="one-time-code">
            </div>
            <div>
                <button type="submit">Verificar</button>
            </div>
        </form>

        <p>
            <a href="{{ route('larajoom.login') }}">← Volver al inicio de sesión</a>
        </p>
    </main>
</body>
</html>
