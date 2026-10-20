@extends('admin_tpl::index')

@section('title', __('com_modules::com_modules.edit_module'))

@section('icon', 'module')

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
    <h2>{{ __('com_modules::com_modules.edit_module') }} #{{ $module->id }}</h2>

    <x-larajoom::form :action="route('larajoom.admin.modules.update', $module->id)" method="PUT" id="adminForm">
        <fieldset>
            <legend>{{ __('com_modules::com_modules.module') }}</legend>

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

            <x-larajoom::input name="ordering" type="number" :label="__('com_modules::com_modules.ordering')" :value="$module->ordering" min="0" />

            <x-larajoom::checkbox name="showtitle" :label="__('com_modules::com_modules.showtitle')" :checked="$module->showtitle" />

            <x-larajoom::checkbox name="published" :label="__('com_modules::com_modules.published')" :checked="$module->published" />
        </fieldset>
    </x-larajoom::form>
@endsection
