@extends('admin_tpl::index')

@section('title', __('com_users::com_users.edit_group'))
@section('icon', 'roles')

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('larajoom_admin::admin.save'),
            'icon'  => 'save',
            'type'  => 'submit',
            'form'  => 'adminForm',
            'name'  => 'task',
            'value' => 'save',
        ],
        [
            'label' => __('com_users::com_users.back_to_users'),
            'icon'  => 'back',
            'url'   => route('larajoom.admin.groups.index'),
            'color' => 'warning',
        ],
    ]" />
@endsection

@section('content')
    <x-larajoom::form :action="route('larajoom.admin.groups.update', $group->id)" method="PUT" id="adminForm">
        <fieldset>
            <legend>{{ __('com_users::com_users.edit_group') }}</legend>

            <x-larajoom::input name="title" :label="__('com_users::com_users.group_title')" :value="old('title', $group->title)" required />

            <x-larajoom::select name="parent_id" :label="__('com_users::com_users.parent_group')">
                <option value="0">{{ __('com_users::com_users.no_parent_group') }}</option>
                @foreach ($groups as $parent)
                    @continue((int) $parent->id === (int) $group->id)
                    <option value="{{ $parent->id }}" @selected((int) old('parent_id', $group->parent_id) === (int) $parent->id)>{{ $parent->title }}</option>
                @endforeach
            </x-larajoom::select>
        </fieldset>
    </x-larajoom::form>
@endsection
