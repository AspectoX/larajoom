@extends('admin_tpl::index')

@section('title', __('com_modules::com_modules.new_module'))

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
    <x-larajoom::form :action="route('larajoom.admin.modules.store')" method="POST" id="adminForm">
        <fieldset>
            <legend>{{ __('com_modules::com_modules.module') }}</legend>

            <x-larajoom::input name="title" :label="__('com_modules::com_modules.title')" required size="60" />

            <x-larajoom::select name="module" :label="__('com_modules::com_modules.module')" required>
                @foreach ($moduleTypes as $type)
                    <option value="{{ $type }}" @selected(old('module') === $type)>{{ $type }}</option>
                @endforeach
            </x-larajoom::select>

            <x-larajoom::select name="position" :label="__('com_modules::com_modules.position')" required>
                <option value="">{{ __('com_modules::com_modules.select_position') }}</option>
                @foreach ($positions as $pos)
                    <option value="{{ $pos }}" @selected(old('position', request('position')) === $pos)>{{ $pos }}</option>
                @endforeach
            </x-larajoom::select>

            <x-larajoom::input name="ordering" type="number" :label="__('com_modules::com_modules.ordering')" :value="0" min="0" />

            <x-larajoom::checkbox name="showtitle" :label="__('com_modules::com_modules.showtitle')" :checked="true" />

            <x-larajoom::checkbox name="published" :label="__('com_modules::com_modules.published')" :checked="true" />
        </fieldset>
    </x-larajoom::form>
@endsection
