@extends('tpl::index')

@section('title', __('com_plugins::com_plugins.edit_plugin'))
@section('icon', 'plugins')

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('com_plugins::com_plugins.cancel'),
            'icon'  => 'cancel',
            'url'   => route('larajoom.admin.plugins.index'),
            'color' => 'warning',
        ],
        [
            'label' => __('com_plugins::com_plugins.save'),
            'icon'  => 'save',
            'type'  => 'submit',
            'form'  => 'adminForm',
            'name'  => 'task',
            'value' => 'save',
        ],
        [
            'label' => __('com_plugins::com_plugins.save_close'),
            'icon'  => 'check',
            'type'  => 'submit',
            'form'  => 'adminForm',
            'name'  => 'task',
            'value' => 'save2close',
        ],
    ]" />
@endsection

@section('content')
    @if (session('success'))
        <x-message type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-message type="danger" :message="session('error')" />
    @endif

    <h2>{{ __('com_plugins::com_plugins.edit_plugin') }}: {{ $plugin->name }}</h2>

    <div>
        <span class="badge">{{ $plugin->folder }}</span>
        <span class="badge">{{ $plugin->element }}</span>
    </div>

    @if (!empty($plugin->manifest_cache['description']))
        <p>{{ $plugin->manifest_cache['description'] }}</p>
    @endif

    <x-larajoom::form :action="route('larajoom.admin.plugins.update', $plugin->extension_id)" method="PUT" id="adminForm">
        <fieldset>
            <legend>{{ __('com_plugins::com_plugins.plugin') }}</legend>

            <x-larajoom::checkbox name="enabled" :label="__('com_plugins::com_plugins.enabled')" :checked="$plugin->enabled" />

            <x-larajoom::input name="ordering" type="number" :label="__('com_plugins::com_plugins.ordering')" :value="old('ordering', $plugin->ordering)" min="0" />
            <x-larajoom::input name="access" type="number" :label="__('com_plugins::com_plugins.access')" :value="old('access', $plugin->access)" min="1" />
            <x-larajoom::input name="note" type="text" :label="__('com_plugins::com_plugins.note')" :value="old('note', $plugin->note)" size="60" />
        </fieldset>

        <fieldset>
            <legend>{{ __('com_plugins::com_plugins.params') }}</legend>

            @if ($rawJson)
                <p>
                    <label for="params_json">{{ __('com_plugins::com_plugins.params_json') }}</label>
                    <textarea id="params_json" name="params_json" rows="10" cols="60">{{ json_encode($values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
                </p>
            @else
                @foreach ($fields as $field)
                    @php($value = $values[$field['name']] ?? null)
                    @php($fieldType = $field['type'] ?? 'text')

                    @if ($fieldType === 'checkbox')
                        <x-larajoom::checkbox
                            name="params[{{ $field['name'] }}]"
                            :label="$field['label'] ?? $field['name']"
                            :checked="$value" />
                    @elseif ($fieldType === 'select')
                        <x-larajoom::select name="params[{{ $field['name'] }}]" :label="$field['label'] ?? $field['name']">
                            @foreach (($field['options'] ?? []) as $optionValue => $optionLabel)
                                <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                            @endforeach
                        </x-larajoom::select>
                    @elseif ($fieldType === 'textarea')
                        <p>
                            <label for="params_{{ $field['name'] }}">{{ $field['label'] ?? $field['name'] }}</label>
                            <textarea id="params_{{ $field['name'] }}" name="params[{{ $field['name'] }}]" rows="5" cols="60">{{ $value }}</textarea>
                        </p>
                    @elseif ($fieldType === 'number')
                        <x-larajoom::input name="params[{{ $field['name'] }}]" type="number" :label="$field['label'] ?? $field['name']" :value="$value" />
                    @else
                        <x-larajoom::input name="params[{{ $field['name'] }}]" type="text" :label="$field['label'] ?? $field['name']" :value="$value" />
                    @endif
                @endforeach
            @endif
        </fieldset>
    </x-larajoom::form>
@endsection
