@props(['name', 'label' => null, 'value' => null, 'type' => 'text', 'required' => false, 'id' => null])

<div class="form-group">
    @if ($label)
        <label for="{{ $id ?? $name }}" class="form-label">{{ $label }}</label>
    @endif
    <input type="{{ $type }}" id="{{ $id ?? $name }}" name="{{ $name }}" value="{{ old($name, $value) }}"
        class="form-input" {{ $required ? 'required' : '' }} {{ $attributes }}>
</div>
