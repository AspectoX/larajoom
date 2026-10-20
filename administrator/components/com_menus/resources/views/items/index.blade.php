@extends('admin_tpl::index')

@section('title', __('com_menus::com_menus.menu_items'))

@section('icon', 'list')

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('com_menus::com_menus.new_menu_item'),
            'icon'  => 'add',
            'url'   => route('larajoom.admin.menuitems.create', $menutype ? ['menutype' => $menutype] : []),
        ],
    ]" />
@endsection

@section('content')
    @php
        $rows = [];
        $actions = [];
        $meta = [];
        foreach ($items as $i => $item) {
            $iconHtml = ($item->show_icon && $item->icon) ? larajoom_icon($item->icon) : '';

            $rows[] = [
                ['html' => $iconHtml],
                $item->published ? __('com_menus::com_menus.published') : __('com_menus::com_menus.unpublished'),
                str_repeat('— ', max(0, $item->level - 1)) . $item->title,
                $menuTypes->firstWhere('menutype', $item->menutype)->title ?? $item->menutype,
                ['html' => $item->home ? larajoom_icon('star') : ''],
                $levels[$item->access] ?? $item->access,
                $item->id,
            ];
            $actions[$i] = [
                'edit'   => route('larajoom.admin.menuitems.edit', $item->id),
                'delete' => route('larajoom.admin.menuitems.destroy', $item->id),
            ];
            $meta[] = ['id' => $item->id, 'parent' => $item->parent_id, 'level' => $item->level];
        }
    @endphp

    <x-larajoom::table
        :headers="[
            __('com_menus::com_menus.icon'),
            __('com_menus::com_menus.status'),
            __('com_menus::com_menus.title'),
            __('com_menus::com_menus.menutype'),
            __('com_menus::com_menus.home'),
            __('com_menus::com_menus.access'),
            __('com_menus::com_menus.id'),
        ]"
        :rows="$rows"
        :actions="$actions"
        :asset="'com_menus'"
        :ids="$items->pluck('id')"
        :meta="$meta"
        :tree="true"
        :reorder-url="route('larajoom.admin.menuitems.reorder')"
    />
@endsection
