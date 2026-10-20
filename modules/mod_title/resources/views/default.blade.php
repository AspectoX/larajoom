@props([
    'module' => null,
    'params' => null,
])

@php
    // Título e icono dinámicos: usan @section('title') / @section('icon') de la vista del componente
    $pageTitle = trim((string) view()->yieldContent('title')) ?: ($module->title ?? '');
    $pageIcon  = trim((string) view()->yieldContent('icon')) ?: ($module->icon ?? '');
@endphp

<div class="container-title flex">
    <h1 class="page-title">
        <span class="icon-title" aria-hidden="true">
            @icon($pageIcon)
        </span>
        {{ $pageTitle }}
    </h1>
</div>
