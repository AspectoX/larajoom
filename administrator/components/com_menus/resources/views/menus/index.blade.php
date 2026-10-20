@extends('admin_tpl::index')

@section('title', __('com_menus::com_menus.menus'))

@section('icon', 'menus')

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('com_menus::com_menus.new_menu'),
            'icon'  => 'add',
            'url'   => route('larajoom.admin.menus.create'),
        ],
    ]" />
@endsection

@section('content')

    @php
        $rows = [];
        $actions = [];
        foreach ($menus as $i => $menu) {
            $rows[] = [
                ['html' => '<a href="' . route('larajoom.admin.menuitems.index', ['menutype' => $menu->menutype]) . '" class="btn transparent circle" title="' . e(__('com_menus::com_menus.menu_items')) . '">' . larajoom_icon('menus') . '</a>'],
                $menu->menutype,
                $menu->title,
                $menu->client_id ? __('com_menus::com_menus.administrator') : __('com_menus::com_menus.site'),
                $menu->items_count,
                $menu->id,
            ];
            $actions[$i] = [
                'edit'   => route('larajoom.admin.menus.edit', $menu->id),
                'delete' => route('larajoom.admin.menus.destroy', $menu->id),
            ];
        }
    @endphp

    <x-larajoom::table
        :headers="[
            __('com_menus::com_menus.menu_items'),
            __('com_menus::com_menus.menutype'),
            __('com_menus::com_menus.title'),
            __('com_menus::com_menus.client'),
            __('com_menus::com_menus.items'),
            __('com_menus::com_menus.id'),
        ]"
        :rows="$rows"
        :actions="$actions"
        :ids="$menus->pluck('id')"
        :reorder-url="route('larajoom.admin.menus.reorder')"
    />

    <x-ax-pagination :paginator="$menus" />

@endsection
