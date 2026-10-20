@props([
    'action' => '',
    'method' => 'POST'
])

@php
    $methodUpper = strtoupper($method);
    $spoofMethod = in_array($methodUpper, ['PUT', 'PATCH', 'DELETE']);
@endphp

<form action="{{ $action }}" method="{{ $spoofMethod ? 'POST' : $method }}" {{ $attributes }}>
    @csrf
    @if ($spoofMethod)
        @method($methodUpper)
    @endif

    {{ $slot }}
</form>
