@props([
    'name',
    'label' => null,
    'checked' => false,
    'value' => 1,
    'id' => null,
    'disabled' => false,
])

<input type="checkbox"
    class="form-check"
    id="{{ $id ?? $name }}"
    name="{{ $name }}"
    value="{{ $value }}"
    @checked(old($name, $checked))
    @disabled($disabled)>
@if ($label)
    <label for="{{ $id ?? $name }}">{{ $label }}</label>
@endif
