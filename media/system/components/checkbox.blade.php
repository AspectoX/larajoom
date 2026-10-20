@props([
    'name',
    'label' => null,
    'checked' => false,
    'value' => 1,
    'id' => null,
    'disabled' => false,
])

@php
    $current = old($name, $checked);
    $isChecked = is_array($current) ? in_array($value, $current) : (bool) $current;
@endphp

<input type="checkbox"
    class="form-check"
    id="{{ $id ?? $name }}"
    name="{{ $name }}"
    value="{{ $value }}"
    @checked($isChecked)
    @disabled($disabled)>
@if ($label)
    <label for="{{ $id ?? $name }}">{{ $label }}</label>
@endif
