@props([
    'name',
    'label' => null,
    'value' => null,
])

<div class="form-group">
    @if ($label)
        <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    @endif
    <select name="{{ $name }}" id="{{ $name }}" class="form-select-new">
        <button>
            <selectedcontent></selectedcontent>
            <span class="icon-svg">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m6 9 6 6 6-6"></path>
                </svg>
            </span>
        </button>
        <optgroup>
            @foreach (larajoom_icons() as $key => $svg)
                <option value="{{ $key }}" @selected(old($name, $value) === $key)>
                    <span class="select-icon">{!! $svg !!}</span>
                    {{ $key }}
                </option>
            @endforeach
        </optgroup>
    </select>
    @error($name)
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>
