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

    <x-larajoom::form :action="route('larajoom.admin.plugins.update', $plugin->extension_id)" method="PUT" id="adminForm">
        <div class="details">

            {{-- TAB: PLUGIN --}}
            <details class="details__item" open name="plugin-edit">
                <summary class="details__link">{{ __('com_plugins::com_plugins.plugin') }}</summary>
                <div class="details__body">
                    <fieldset>
                        <x-larajoom::toggle name="enabled" :label="__('com_plugins::com_plugins.enabled')" :checked="$plugin->enabled" />

                        <x-larajoom::input name="ordering" type="number" :label="__('com_plugins::com_plugins.ordering')" :value="old('ordering', $plugin->ordering)" min="0" />

                        <x-larajoom::select name="access" :label="__('com_plugins::com_plugins.access')">
                            @foreach (\Aspectox\LaraJoom\Core\Access::allAccessLevels() as $levelId => $levelTitle)
                                <option value="{{ $levelId }}" @selected(old('access', $plugin->access) == $levelId)>{{ $levelTitle }}</option>
                            @endforeach
                        </x-larajoom::select>

                        <x-larajoom::textarea name="note" :label="__('com_plugins::com_plugins.note')" :value="old('note', $plugin->note)" rows="3" cols="60" />
                    </fieldset>
                </div>
            </details>

            {{-- TAB: PARAMETERS --}}
            @if (!empty($fields))
                <details class="details__item" name="plugin-edit">
                    <summary class="details__link">{{ __('com_plugins::com_plugins.params') }}</summary>
                    <div class="details__body">
                        <fieldset>
                            @foreach ($fields as $field)
                                @php($value = $values[$field['name']] ?? null)
                                @php($fieldType = $field['type'] ?? 'text')

                                @if ($fieldType === 'checkbox')
                                    <x-larajoom::toggle
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
                                    <x-larajoom::textarea name="params[{{ $field['name'] }}]" :label="$field['label'] ?? $field['name']" :value="$value" rows="5" cols="60" />
                                @elseif ($fieldType === 'number')
                                    <x-larajoom::input name="params[{{ $field['name'] }}]" type="number" :label="$field['label'] ?? $field['name']" :value="$value" />
                                @else
                                    <x-larajoom::input name="params[{{ $field['name'] }}]" type="text" :label="$field['label'] ?? $field['name']" :value="$value" />
                                @endif
                            @endforeach
                        </fieldset>
                    </div>
                </details>
            @endif
        </div>
    </x-larajoom::form>
@endsection