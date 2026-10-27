<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
</head>
<body>
    <main>
        <h1>Crear Cuenta</h1>

        @if ($errors->any())
            <div>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('larajoom.register.post') }}">
            @csrf
            <div>
                <label for="name">Nombre Completo:</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
            </div>

            <div>
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div>
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div>
                <label for="password_confirmation">Confirmar Contraseña:</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>

            @php
                $captchaType = config('larajoom.captcha', '0');
                $captcha = ($captchaType && $captchaType !== '0')
                    ? app(\Aspectox\LaraJoom\Core\Plugins\PluginManager::class)->resolve('captcha', $captchaType)
                    : null;
            @endphp
            @if ($captcha && method_exists($captcha, 'onDisplay'))
                <div>{!! $captcha->onDisplay() !!}</div>
            @endif

            <div>
                <button type="submit">Registrarse</button>
            </div>
        </form>

        <p>
            <a href="{{ route('larajoom.login') }}">¿Ya tienes cuenta? Inicia sesión</a>
        </p>
    </main>
</body>
</html>
