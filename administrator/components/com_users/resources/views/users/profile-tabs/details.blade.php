<fieldset class="options-form">
    <legend>{{ $fieldset['label'] ?? __('com_users::com_users.account_details') }}</legend>

    @foreach ($fieldset['fields'] ?? [] as $field)
        @if ($field['type'] === 'password')
            <x-larajoom::password
                :name="$field['name']"
                :label="$field['label']"
                :required="$field['required'] ?? false"
                :autocomplete="$field['autocomplete'] ?? 'new-password'" />
        @elseif ($field['type'] === 'file')
            <x-larajoom::file
                :name="$field['name']"
                :label="$field['label']"
                :preview="$field['preview'] ?? null"
                accept="image/*" />
        @elseif ($field['type'] === 'toggle')
            <x-larajoom::toggle
                :name="$field['name']"
                :label="$field['label']"
                :checked="$field['value'] ?? false" />
        @elseif ($field['readonly'] ?? false)
            <div class="form-group">
                <label class="form-label">{{ $field['label'] }}</label>
                <div class="form-input">{{ $field['value'] }}</div>
            </div>
        @else
            <x-larajoom::input
                :name="$field['name']"
                :type="$field['type'] ?? 'text'"
                :label="$field['label']"
                :value="old($field['name'], $field['value'] ?? null)"
                :required="$field['required'] ?? false" />
        @endif

        @if (!empty($field['hint']))
            <p><small>{{ $field['hint'] }}</small></p>
        @endif
    @endforeach

    @if (!empty($fieldset['message']))
        <p>{{ $fieldset['message'] }}</p>
    @endif
</fieldset>
