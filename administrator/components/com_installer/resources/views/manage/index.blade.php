@extends('tpl::index')

@section('title', __('com_installer::com_installer.manage_title'))
@section('icon', 'component-alt')

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('com_installer::com_installer.install_new'),
            'icon'  => 'add',
            'url'   => route('larajoom.admin.installer.create'),
        ],
    ]" />
@endsection

@section('content')
    @php
        $rows = [];
        $actions = [];
        foreach ($extensions as $i => $extension) {
            $meta = $extension->manifest_cache ?? [];
            $version = $meta['version'] ?? '-';
            $author = $meta['author'] ?? '-';

            $toggleUrl = route('larajoom.admin.installer.toggle', $extension->extension_id);

            if ($extension->protected) {
                $toggleCell = ['html' => '<span class="btn-icon">' . larajoom_icon('circle-lock') . '</span>'];
            } else {
                $toggleCell = ['html' => '<form method="POST" action="' . $toggleUrl . '" style="display:inline">'
                    . csrf_field()
                    . '<button type="submit" class="btn transparent circle title="'
                    . ($extension->enabled ? e(__('com_installer::com_installer.enabled')) : e(__('com_installer::com_installer.disabled'))) . '">'
                    . '<span class="btn-icon">' . larajoom_icon($extension->enabled ? 'circle-check' : 'circle-times') . '</span>'
                    . '</button>'
                    . '</form>'];
            }

            $rows[] = [
                e($extension->name),
                $extension->type,
                $extension->client_id ? __('com_installer::com_installer.administrator') : __('com_installer::com_installer.site'),
                e($version),
                e($author),
                $toggleCell,
            ];

            if ($extension->protected) {
                $actions[$i] = [];
            } else {
                $actions[$i] = [
                    'delete' => route('larajoom.admin.installer.destroy', $extension->extension_id),
                ];
            }
        }
    @endphp

    @if (session('success'))
        <x-message type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-message type="danger" :message="session('error')" />
    @endif
    @if ($registered)
        <x-message type="info" :message="__('com_installer::com_installer.auto_registered', ['count' => $registered])" />
    @endif

    <x-larajoom::table
        :headers="[
            __('com_installer::com_installer.name'),
            __('com_installer::com_installer.type'),
            __('com_installer::com_installer.client'),
            __('com_installer::com_installer.version'),
            __('com_installer::com_installer.author'),
            __('com_installer::com_installer.enabled'),
        ]"
        :rows="$rows"
        :actions="$actions"
        :ids="$extensions->pluck('extension_id')"
    />

    <x-ax-pagination :paginator="$extensions" />
@endsection