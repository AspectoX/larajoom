@extends('admin_tpl::index')

@section('title', 'Editar Configuración')
@section('icon', 'config')

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('com_config::com_config.cancel'),
            'icon'  => 'cancel',
            'url'   => route('larajoom.admin.config.index'),
            'color' => 'warning',
        ],
        [
            'label' => __('com_config::com_config.save'),
            'icon'  => 'save',
            'type'  => 'submit',
            'form'  => 'adminForm',
            'name'  => 'task',
            'value' => 'save',
        ],
        [
            'label' => __('com_config::com_config.save_close'),
            'icon'  => 'check',
            'type'  => 'submit',
            'form'  => 'adminForm',
            'name'  => 'task',
            'value' => 'save2close',
        ],
    ]" />
@endsection

@section('content')
    <h2>{{ __('com_config::com_config.edit_title', ['name' => $item->name, 'group' => $item->group]) }}</h2>

    <x-larajoom::form :action="route('larajoom.admin.config.update', ['name' => $item->name, 'group' => $item->group])" method="PUT" id="adminForm">
        <fieldset>
            <legend>{{ __('com_config::com_config.field_value') }}</legend>

            <x-larajoom::input name="value" :label="__('com_config::com_config.value')" :value="old('value', $item->value)" size="60" />
        </fieldset>
    </x-larajoom::form>
@endsection