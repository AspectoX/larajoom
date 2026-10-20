@extends('tpl::index')

@section('title', __('com_plugins::com_plugins.plugins'))
@section('icon', 'plugins')

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('com_plugins::com_plugins.detect'),
            'icon'  => 'add',
            'url'   => route('larajoom.admin.plugins.register'),
            'color' => 'primary',
        ],
        [
            'label' => __('com_plugins::com_plugins.back'),
            'icon'  => 'cancel',
            'url'   => url('/administrator'),
            'color' => 'warning',
        ],
    ]" />
@endsection

@section('content')
    @php
        $rows = [];
        $actions = [];
        foreach ($plugins as $i => $plugin) {
            $toggleUrl = route('larajoom.admin.plugins.toggle', $plugin->extension_id);

            $toggleCell = ['html' => '<form method="POST" action="' . $toggleUrl . '" style="display:inline">'
                . csrf_field()
                . '<button type="submit" class="btn transparent circle" title="'
                . ($plugin->enabled ? e(__('com_plugins::com_plugins.enabled')) : e(__('com_plugins::com_plugins.disabled'))) . '">'
                . '<span class="btn-icon">' . larajoom_icon($plugin->enabled ? 'circle-check' : 'circle-times') . '</span>'
                . '</button>'
                . '</form>'];

            $nameCell = ['html' => '<a href="' . route('larajoom.admin.plugins.edit', $plugin->extension_id) . '">'
                . e($plugin->name) . '</a>'
                . ($plugin->note ? '<div class="small">' . e($plugin->note) . '</div>' : '')];

            $rows[] = [
                $toggleCell,
                $nameCell,
                e($plugin->folder),
                e($plugin->element),
                $plugin->access,
                $plugin->ordering,
                $plugin->extension_id,
            ];

            $actions[$i] = [
                'edit' => route('larajoom.admin.plugins.edit', $plugin->extension_id),
            ];
        }
    @endphp

    @if (session('success'))
        <x-message type="success" :message="session('success')" />
    @endif

    <x-larajoom::table
        :headers="[
            __('com_plugins::com_plugins.enabled'),
            __('com_plugins::com_plugins.name'),
            __('com_plugins::com_plugins.group'),
            __('com_plugins::com_plugins.element'),
            __('com_plugins::com_plugins.access'),
            __('com_plugins::com_plugins.ordering'),
            __('com_plugins::com_plugins.id'),
        ]"
        :rows="$rows"
        :actions="$actions"
        :asset="'com_plugins'"
        :ids="$plugins->pluck('extension_id')"
        :draggable="false"
    />
@endsection
