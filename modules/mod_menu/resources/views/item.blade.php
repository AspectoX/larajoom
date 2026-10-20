@php
    $hasChildren = count($item->children) > 0;

    $classes = 'item item-level-' . $level;
    if ($hasChildren) {
        $classes .= ' parent';
    }
    if ($item->active) {
        $classes .= ' mm-active';
    }
@endphp

@if ($item->type === 'separator')
    <li class="divider" role="presentation">
        @if ($item->title !== '')
            <span class="sidebar-item-title">{{ $item->title }}</span>
        @endif
    </li>
@else
    <li class="{{ $classes }}">
        <a href="{{ $item->link }}"
           class="{{ $hasChildren ? 'has-arrow' : 'no-dropdown' }}{{ $item->active ? ' mm-active' : '' }}"
           @if ($item->active && !$hasChildren) aria-current="page" @endif>
            @if ($item->show_icon && $item->icon)
                <span class="menu-icon" aria-hidden="true">@icon($item->icon)</span>
            @endif
            <span class="sidebar-item-title">{{ $item->title }}</span>
            @if ($hasChildren)
                <span class="menu-arrow" aria-hidden="true">@icon('angle-right')</span>
            @endif
        </a>

        @if ($hasChildren)
            <ul class="collapse-level-{{ $level + 1 }}">
                @foreach ($item->children as $child)
                    @include('modules.mod_menu::item', ['item' => $child, 'level' => $level + 1])
                @endforeach
            </ul>
        @endif
    </li>
@endif
