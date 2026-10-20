@props([
    'type' => 'info',
    'title' => '',
    'message' => '',
])

@php
$displayMessage = $message ?: session('message', session('status', ''));
@endphp

<div class="alert alert-{{ $type }}">
    <button class="btn circle transparent close" onclick="this.parentElement.remove()">@icon('close')</button>
    <div class="icon">
        @icon($type)
    </div>
    <div class="alert-text">
        @if($title)
            <h2>ALERT {{ strtoupper($type) }}</h2>
        @endif
        @if($displayMessage)
            <p>{{ $displayMessage }}</p>
        @endif
        {{ $slot }}
    </div>
</div>
