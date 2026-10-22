@props([
    'name',
    'label' => null,
    'checked' => false,
    'value' => 1,
    'id' => null,
    'disabled' => false,
    'classes' => '',
    'unchecked' => 0,
])

@php
    $current = old($name, $checked);
    $isChecked = is_string($current) ? ($current === (string) $value) : (bool) $current;
@endphp

<div class="form-group">
    @if ($label)
        <label for="{{ $id ?? $name }}" class="form-label">{{ $label }}</label>
    @endif
    <label class="toggle-switch ts-square ts-text ts-blue">
        <input type="hidden" name="{{ $name }}" value="{{ $unchecked }}">
        <input type="checkbox" id="{{ $id ?? $name }}" name="{{ $name }}" value="{{ $value }}"
            @checked($isChecked) {{ $attributes }}>
        <span class="slider"></span>
    </label>
</div>
