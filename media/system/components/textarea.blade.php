@props(['name', 'label' => null, 'value' => null, 'rows' => 5, 'id' => null, 'required' => false])

<div class="form-group">
    @if ($label)
        <label for="{{ $id ?? $name }}" class="form-label">{{ $label }}</label>
    @endif
    <textarea name="{{ $name }}" id="{{ $id ?? $name }}" rows="{{ $rows }}" {{ $required ? 'required' : '' }}
        {{ $attributes }} class="form-input textarea">{{ old($name, $value) }}</textarea>
    @error($name)
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>
