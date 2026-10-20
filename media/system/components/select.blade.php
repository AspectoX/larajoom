@props(['name', 'label' => null, 'options' => [], 'value' => null, 'id' => null])

<div class="form-group">
    @if ($label)
        <label for="{{ $id ?? $name }}" class="form-label">{{ $label }}</label>
    @endif
    <select name="{{ $name }}" id="{{ $id ?? $name }}" {{ $attributes }} class="form-select-new">
        <button>
            <selectedcontent></selectedcontent>
            <span class="icon-svg">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m6 9 6 6 6-6"></path>
                </svg>
            </span>
        </button>
        <optgroup>
            @forelse ($options as $key => $optLabel)
                <option value="{{ $key }}" @selected(old($name, $value) == $key)>{{ $optLabel }}</option>
            @empty
                {{ $slot }}
            @endforelse
        </optgroup>
    </select>
    @error($name)
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>
