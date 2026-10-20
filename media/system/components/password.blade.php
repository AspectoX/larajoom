@props([
    'name',
    'label' => null,
    'value' => null,
    'id' => null,
    'required' => false,
    'autocomplete' => 'current-password',
])

<div class="input-button" x-data="{ show: false }">
    <div class="form-group">
        @if ($label)
            <label for="{{ $id ?? $name }}" class="form-label">{{ $label }}</label>
        @endif
        <input type="password"
            id="{{ $id ?? $name }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            class="form-input"
            {{ $required ? 'required' : '' }}
            autocomplete="{{ $autocomplete }}"
            x-bind:type="show ? 'text' : 'password'" />
    </div>
    <button type="button" class="btn color-primary input-password-toggle"
        @click="show = !show"
        :aria-label="show ? @js(__('larajoom_admin::admin.hide_password')) : @js(__('larajoom_admin::admin.show_password'))"
        :aria-pressed="show">
        <span class="icon" aria-hidden="true">
            @icon('view')
        </span>
    </button>
</div>
