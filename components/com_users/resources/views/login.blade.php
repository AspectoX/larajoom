<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
</head>
<body>
    <main>
        <h1>Iniciar Sesión</h1>

        @if ($errors->any())
            <div>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('larajoom.login.post') }}">
            @csrf
            <div>
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>

            <div>
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div>
                <x-larajoom::checkbox name="remember" label="Recordarme" />
            </div>

            <div>
                <button type="submit">Entrar</button>
            </div>
        </form>

        <p>
            <a href="{{ route('larajoom.register') }}">¿No tienes cuenta? Regístrate aquí</a>
        </p>
    </main>
</body>
</html>
