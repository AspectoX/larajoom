@props([
    'component' => null,
    'view' => 'default',
])

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    @stack('styles')
    @stack('scripts')
</head>

<body class="contentpane component">
    <x-message />
    <main>
        {{ $slot ?? view('admin.components.' . $component . '::' . $view, $attributes ?? [])->render() }}
    </main>
</body>
</html>
