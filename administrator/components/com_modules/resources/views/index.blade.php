@extends('admin_tpl::index')

@section('title', __('com_modules::com_modules.modules'))
@section('icon', 'module')

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('com_modules::com_modules.new_module'),
            'icon'  => 'add',
            'url'   => route('larajoom.admin.modules.create'),
        ],
    ]" />
@endsection

@section('content')
    @php
        $rows = [];
        $actions = [];
        foreach ($modules as $i => $module) {
            $rows[] = [
                $module->title,
                $module->module,
                $module->position,
                $module->ordering,
                $module->published ? __('com_modules::com_modules.published') : __('com_modules::com_modules.unpublished'),
                $module->id,
            ];
            $actions[$i] = [
                'edit'   => route('larajoom.admin.modules.edit', $module->id),
                'delete' => route('larajoom.admin.modules.destroy', $module->id),
            ];
        }
    @endphp

    <x-larajoom::table
        :headers="[
            __('com_modules::com_modules.title'),
            __('com_modules::com_modules.module'),
            __('com_modules::com_modules.position'),
            __('com_modules::com_modules.ordering'),
            __('com_modules::com_modules.status'),
            __('com_modules::com_modules.id'),
        ]"
        :rows="$rows"
        :actions="$actions"
        :asset="'com_modules'"
        :ids="$modules->pluck('id')"
        :reorder-url="route('larajoom.admin.modules.reorder')"
    />

    <x-ax-pagination :paginator="$modules" />
@endsection