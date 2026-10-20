<fieldset class="options-form">
    <legend>{{ __('com_config::com_config.logging_settings') }}</legend>

    <x-larajoom::input name="log_path" :label="__('com_config::com_config.log_path')" :value="config('larajoom.log_path', '')" size="60" />

    <x-larajoom::toggle name="log_everything" :label="__('com_config::com_config.log_everything')" :checked="(int) config('larajoom.log_everything', 0) === 1" />

    <x-larajoom::toggle name="log_deprecated" :label="__('com_config::com_config.log_deprecated')" :checked="(int) config('larajoom.log_deprecated', 0) === 1" />

    <x-larajoom::select name="log_priorities" :label="__('com_config::com_config.log_priorities')">
        @php
            $priorities = ['all' => __('com_config::com_config.log_priority_all')];
            foreach (['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'] as $level) {
                $priorities[$level] = $level;
            }
            $current = config('larajoom.log_priorities', 'all');
        @endphp
        @foreach ($priorities as $value => $label)
            <option value="{{ $value }}" @selected($current === $value)>{{ $label }}</option>
        @endforeach
    </x-larajoom::select>

    <x-larajoom::input name="log_categories" :label="__('com_config::com_config.log_categories')" :value="config('larajoom.log_categories', '')" size="60" />

    <x-larajoom::select name="log_category_mode" :label="__('com_config::com_config.log_category_mode')">
        <option value="0" @selected(config('larajoom.log_category_mode', '0') == '0')>{{ __('com_config::com_config.log_mode_include') }}</option>
        <option value="1" @selected(config('larajoom.log_category_mode', '0') == '1')>{{ __('com_config::com_config.log_mode_exclude') }}</option>
    </x-larajoom::select>
</fieldset>
