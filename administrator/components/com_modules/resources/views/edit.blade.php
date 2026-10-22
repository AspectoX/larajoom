@extends('admin_tpl::index')

@section('title', __('com_modules::com_modules.edit_module'))
@section('icon', 'module')

@php
    $savedParams   = is_string($module->params) ? (json_decode($module->params, true) ?: []) : (array) ($module->params ?? []);
    $bootstrapSize = (string) old('bootstrap_size', $savedParams['bootstrap_size'] ?? 0);
@endphp

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('com_modules::com_modules.cancel'),
            'icon'  => 'cancel',
            'url'   => route('larajoom.admin.modules.index'),
            'color' => 'warning',
        ],
        [
            'label' => __('com_modules::com_modules.save'),
            'icon'  => 'save',
            'type'  => 'submit',
            'form'  => 'adminForm',
            'name'  => 'task',
            'value' => 'save',
        ],
        [
            'label' => __('com_modules::com_modules.save_new'),
            'icon'  => 'add',
            'type'  => 'submit',
            'form'  => 'adminForm',
            'name'  => 'task',
            'value' => 'save2new',
            'color' => 'info',
        ],
        [
            'label' => __('com_modules::com_modules.save_close'),
            'icon'  => 'check',
            'type'  => 'submit',
            'form'  => 'adminForm',
            'name'  => 'task',
            'value' => 'save2close',
        ],
    ]" />
@endsection

@section('content')
    <x-larajoom::form :action="route('larajoom.admin.modules.update', $module->id)" method="PUT" id="adminForm">
        <div class="details">

            {{-- TAB: MÓDULO --}}
            <details class="details__item" open name="module-edit">
                <summary class="details__link">{{ __('com_modules::com_modules.module') }}</summary>
                <div class="details__body">
                    <fieldset>
                        <x-larajoom::input name="title" :label="__('com_modules::com_modules.title')" :value="$module->title" required size="60" />

                        <x-larajoom::select name="module" :label="__('com_modules::com_modules.module')" required>
                            @foreach ($moduleTypes as $type)
                                <option value="{{ $type }}" @selected(old('module', $module->module) === $type)>{{ $type }}</option>
                            @endforeach
                        </x-larajoom::select>

                        <x-larajoom::select name="position" :label="__('com_modules::com_modules.position')" required>
                            @foreach ($positions as $pos)
                                <option value="{{ $pos }}" @selected(old('position', $module->position) === $pos)>{{ $pos }}</option>
                            @endforeach
                        </x-larajoom::select>

                        <x-larajoom::select name="bootstrap_size" :label="__('com_modules::com_modules.bootstrap_size')">
                            @for ($n = 0; $n <= 12; $n++)
                                <option value="{{ $n }}" @selected($bootstrapSize === (string) $n)>
                                    {{ $n === 0 ? __('com_modules::com_modules.full_width') : __('com_modules::com_modules.n_columns', ['n' => $n]) }}
                                </option>
                            @endfor
                        </x-larajoom::select>

                        <x-larajoom::input name="ordering" type="number" :label="__('com_modules::com_modules.ordering')" :value="$module->ordering" min="0" />

                        <x-larajoom::toggle name="showtitle" :label="__('com_modules::com_modules.showtitle')" :checked="$module->showtitle" />

                        <x-larajoom::toggle name="published" :label="__('com_modules::com_modules.published')" :checked="$module->published" />

                        <x-larajoom::select name="access" :label="__('com_modules::com_modules.access')">
                            @foreach (\Aspectox\LaraJoom\Core\Access::allAccessLevels() as $levelId => $levelTitle)
                                <option value="{{ $levelId }}" @selected(old('access', $module->access) == $levelId)>{{ $levelTitle }}</option>
                            @endforeach
                        </x-larajoom::select>
                    </fieldset>
                </div>
            </details>

            {{-- TAB: CONFIG --}}
            @if (!empty($fields))
                <details class="details__item" name="module-edit">
                    <summary class="details__link">{{ __('com_modules::com_modules.params') }}</summary>
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