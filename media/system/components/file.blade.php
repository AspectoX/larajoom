@props([
    'name',
    'label' => null,
    'preview' => null
])

<div class="form-group">
    @if ($label)
        <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    @endif
    <div class="input-file">
        @if ($preview)
            <img src="{{ $preview }}" alt="Preview" width="60">
        @endif
        <input 
            type="file" 
            id="{{ $name }}" 
            name="{{ $name }}" 
            class="form-input"
            {{ $attributes }}
        >
    </div>
</div>
