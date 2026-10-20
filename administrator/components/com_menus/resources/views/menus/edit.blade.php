@extends('admin_tpl::index')

@section('title', __('com_menus::com_menus.edit_menu'))

@section('icon', 'menus')

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('com_menus::com_menus.cancel'),
            'icon'  => 'cancel',
            'url'   => route('larajoom.admin.menus.index'),
            'color' => 'warning',
        ],
        [
            'label' => __('com_menus::com_menus.save'),
            'icon'  => 'save',
            'type'  => 'submit',
            'form'  => 'adminForm',
            'name'  => 'task',
            'value' => 'save',
        ],
        [
            'label' => __('com_menus::com_menus.save_new'),
            'icon'  => 'add',
            'type'  => 'submit',
            'form'  => 'adminForm',
            'name'  => 'task',
            'value' => 'save2new',
            'color' => 'info',
        ],
        [
            'label' => __('com_menus::com_menus.save_close'),
            'icon'  => 'check',
            'type'  => 'submit',
            'form'  => 'adminForm',
            'name'  => 'task',
            'value' => 'save2close',
        ],
    ]" />
@endsection

@section('content')
    <x-larajoom::form :action="route('larajoom.admin.menus.update', $menu->id)" method="PUT" id="adminForm">
        <fieldset>
            <legend>{{ __('com_menus::com_menus.menu_type') }}</legend>

            <x-larajoom::input
                name="menutype"
                :label="__('com_menus::com_menus.menutype')"
                :value="$menu->menutype"
                required
                size="60"
            />

            <x-larajoom::input
                name="title"
                :label="__('com_menus::com_menus.title')"
                :value="$menu->title"
                required
                size="60"
            />

            <x-larajoom::input
                name="description"
                :label="__('com_menus::com_menus.description')"
                :value="$menu->description"
                size="60"
            />

            <x-larajoom::select name="client_id" :label="__('com_menus::com_menus.client')">
                <option value="0" @selected(old('client_id', $menu->client_id) == 0)>{{ __('com_menus::com_menus.site') }}</option>
                <option value="1" @selected(old('client_id', $menu->client_id) == 1)>{{ __('com_menus::com_menus.administrator') }}</option>
            </x-larajoom::select>
        </fieldset>
    </x-larajoom::form>
@endsection
