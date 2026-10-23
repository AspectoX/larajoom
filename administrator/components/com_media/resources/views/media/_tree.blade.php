@foreach ($nodes as $node)
    <li class="media-tree-item" role="none">
        <a role="treeitem" aria-level="2" tabindex="-1"
           href="{{ route('larajoom.admin.media.folder', $node['relative_path']) }}">
            <span class="item-icon"><span class="icon-folder">@icon('folder')</span></span>
            <span class="item-name">{{ $node['name'] }}</span>
        </a>

        @if (!empty($node['children']))
            <ul class="media-tree" role="group">
                @include('admin.components.com_media::media._tree', ['nodes' => $node['children']])
            </ul>
        @endif
    </li>
@endforeach
