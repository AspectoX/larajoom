@php
    $treeIconMap = [
        'php'  => 'file-php',
        'js'   => 'file-js',
        'mjs'  => 'file-js',
        'css'  => 'file-css',
        'scss' => 'file-css',
        'less' => 'file-css',
        'json' => 'file-json',
        'md'   => 'file-md',
        'xml'  => 'file-xml',
        'ico'  => 'file-ico',
        'png'  => 'file-image',
        'jpg'  => 'file-image',
        'jpeg' => 'file-image',
        'gif'  => 'file-image',
        'svg'  => 'file-image',
        'webp' => 'file-image',
        'bmp'  => 'file-image',
    ];
@endphp
<ul class="directory-tree treeselect{{ ($open ?? false) ? '' : ' hidden' }}">
    @foreach ($nodes as $node)
        @php
            $treeLower = strtolower($node['path'] ?? '');
            $treeIcon  = str_ends_with($treeLower, '.blade.php')
                ? 'file-blade'
                : ($treeIconMap[strtolower(pathinfo($treeLower, PATHINFO_EXTENSION))] ?? 'file-generic');
        @endphp
        @if (($node['type'] ?? 'file') === 'folder')
            <li class="folder">
                <a class="folder-url" href=""><span class="icon-folder">{!! larajoom_icon('folder') !!}</span>&nbsp;{{ $node['name'] }}</a>
                @include('admin.components.com_templates::edit.tree', ['nodes' => $node['children'] ?? [], 'open' => false])
            </li>
        @else
            <li>
                @if (!empty($editUrl))
                    <a class="file" href="{{ $editUrl }}?file={{ rawurlencode($node['path']) }}"><span class="icon-file">{!! larajoom_icon($treeIcon) !!}</span>&nbsp;{{ $node['name'] }}</a>
                @else
                    <a class="file" href=""><span class="icon-file">{!! larajoom_icon($treeIcon) !!}</span>&nbsp;{{ $node['name'] }}</a>
                @endif
            </li>
        @endif
    @endforeach
</ul>
