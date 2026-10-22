@extends('admin_tpl::index')

@section('title', __('com_menus::com_menus.edit_menu_item'))
@section('icon', 'list')

@php
    $itemTypes = ['component', 'url', 'alias', 'heading', 'separator'];
@endphp

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('com_menus::com_menus.cancel'),
            'icon'  => 'cancel',
            'url'   => route('larajoom.admin.menuitems.index', ['menutype' => $item->menutype]),
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
    <x-larajoom::form :action="route('larajoom.admin.menuitems.update', $item->id)" method="PUT" id="adminForm">
        <input type="hidden" name="menutype" value="{{ $item->menutype }}">

        <x-larajoom::input name="title" :label="__('com_menus::com_menus.title')" :value="$item->title" required size="60" />

        <x-larajoom::alias :label="__('com_menus::com_menus.alias')" :value="$item->alias" />

        <x-larajoom::select name="type" :label="__('com_menus::com_menus.type')">
            @foreach ($itemTypes as $type)
                <option value="{{ $type }}" @selected(old('type', $item->type) === $type)>{{ __('com_menus::com_menus.type_' . $type) }}</option>
            @endforeach
        </x-larajoom::select>

        <x-larajoom::input name="link" :label="__('com_menus::com_menus.link')" :value="$item->link" size="60" />

        <dialog id="component-dialog">
            <header>
                <h3>{{ __('com_menus::com_menus.choose_component') }}</h3>
            </header>
            <nav aria-label="{{ __('com_menus::com_menus.choose_component') }}">
                <ul>
                    @foreach ($components as $link => $element)
                        <li>
                            <button type="button" class="btn" data-component-link="{{ $link }}">{{ $element }}</button>
                        </li>
                    @endforeach
                </ul>
            </nav>
            <footer>
                <button type="button" class="btn warning" id="component-dialog-cancel" value="cancel">
                    {{ __('com_menus::com_menus.cancel') }}
                </button>
            </footer>
        </dialog>

        @once
        <script>
        (function () {
            function initComponentDialog() {
                var dialog = document.getElementById('component-dialog');
                var link = document.querySelector('[name="link"]');
                var type = document.querySelector('[name="type"]');

                if (!dialog || !link || !type) return;

                var previousType = type.value;

                type.addEventListener('change', function () {
                    if (type.value === 'component') {
                        dialog.showModal();
                    } else {
                        previousType = type.value;
                    }
                });

                dialog.querySelectorAll('[data-component-link]').forEach(function (button) {
                    if (button.dataset.componentLink === link.value) {
                        button.setAttribute('aria-current', 'true');
                    }

                    button.addEventListener('click', function () {
                        link.value = button.dataset.componentLink;
                        if ('close' in dialog) dialog.close();
                    });
                });

                var cancel = document.getElementById('component-dialog-cancel');
                if (cancel) {
                    cancel.addEventListener('click', function () {
                        if (!link.value || !Array.prototype.some.call(
                            dialog.querySelectorAll('[data-component-link]'),
                            function (b) { return b.dataset.componentLink === link.value; }
                        )) {
                            link.value = '';
                            type.value = previousType;
                        }
                        if ('close' in dialog) dialog.close();
                    });
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initComponentDialog);
            } else {
                initComponentDialog();
            }
        })();
        </script>
        @endonce

        <x-larajoom::iconselect name="icon" :label="__('com_menus::com_menus.icon')" :value="$item->icon" />

        <x-larajoom::select name="parent_id" :label="__('com_menus::com_menus.parent')">
            <option value="0">{{ __('com_menus::com_menus.no_parent') }}</option>
            @foreach ($parents as $parent)
                @if ($parent->id !== $item->id)
                    <option value="{{ $parent->id }}" @selected(old('parent_id', $item->parent_id) == $parent->id)>{{ $parent->label }}</option>
                @endif
            @endforeach
        </x-larajoom::select>

        <x-larajoom::select name="access" :label="__('com_menus::com_menus.access')">
                @foreach ($levels as $levelId => $levelTitle)
                    <option value="{{ $levelId }}" @selected(old('access', $item->access) == $levelId)>{{ $levelTitle }}</option>
                @endforeach
            </x-larajoom::select>

        <x-larajoom::input name="ordering" type="number" :label="__('com_menus::com_menus.ordering')" :value="$item->ordering" min="0" />

        <x-larajoom::toggle name="published" :label="__('com_menus::com_menus.published')" :checked="$item->published" />

        <x-larajoom::toggle name="home" :label="__('com_menus::com_menus.home')" :checked="$item->home" />

        <x-larajoom::toggle name="show_icon" :label="__('com_menus::com_menus.show_icon')" :checked="$item->show_icon" />
    </x-larajoom::form>
@endsection