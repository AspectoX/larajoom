@extends('admin_tpl::index')

@section('title', __('com_media::com_media.media_manager'))
@section('icon', 'image')

@push('styles')
    <link rel="stylesheet" href="{{ url('media/com_media/css/media-manager.css') }}">
@endpush

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('com_media::com_media.upload'),
            'icon'  => 'upload',
            'color' => 'success',
            'url'   => '#upload',
        ],
        [
            'label' => __('com_media::com_media.new_folder'),
            'icon'  => 'folder',
            'color' => 'primary',
            'url'   => '#new-folder',
        ],
        [
            'label' => __('com_media::com_media.delete'),
            'icon'  => 'times',
            'color' => 'danger',
            'url'   => '#media-delete',
        ],
    ]" />
@endsection

@section('content')
@php
    $rootName = $breadcrumbs[0]['name'] ?? __('com_media::com_media.local');
@endphp
<div id="com-media" x-data="mediaManager()" x-init="init()">
    <div class="media-container">

        {{-- ======= SIDEBAR ======= --}}
        <div class="media-sidebar">
            <div class="media-disk">
                <details open>
                    <summary>
                        <span class="media-disk-marker"><svg data-icon="caret-right" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 512"><path fill="currentColor" d="M0 384.662V127.338c0-17.818 21.543-26.741 34.142-14.142l128.662 128.662c7.81 7.81 7.81 20.474 0 28.284L34.142 398.804C21.543 411.404 0 402.48 0 384.662z"/></svg></span>
                        <h2 id="disk-01" class="media-disk-name">{{ __('com_media::com_media.local') }}</h2>
                    </summary>

                    <div class="media-drive">
                        <ul class="media-tree" role="tree" aria-labelledby="disk-01">
                            <li class="active media-tree-item media-drive-name" role="none">
                                <a role="treeitem" aria-level="1" aria-setsize="0" aria-posinset="1" tabindex="0"
                                   href="{{ route('larajoom.admin.media.index') }}">
                                    <span class="item-name">{{ $rootName }}</span>
                                </a>

                                @php
                                    // Si el árbol recursivo viene vacío, fallback a las carpetas
                                    // inmediatas (misma fuente que la grilla).
                                    $treeChildren = !empty($tree['children'])
                                        ? $tree['children']
                                        : collect($folders)->map(fn ($f) => [
                                            'name' => $f['name'],
                                            'relative_path' => $f['relative_path'],
                                            'children' => [],
                                            'files' => [],
                                        ])->values()->all();
                                @endphp

                                <ul class="media-tree" role="group">
                                    @include('admin.components.com_media::media._tree', ['nodes' => $treeChildren])
                                </ul>
                            </li>
                        </ul>
                    </div>
                </details>
            </div>
        </div>

        {{-- ======= MAIN ======= --}}
        <div class="media-main">

            {{-- Toolbar --}}
            <div class="media-toolbar" role="toolbar" aria-label="{{ __('com_media::com_media.media_manager') }}">
                <div class="media-view-icons">
                    <input type="checkbox" class="media-toolbar-icon media-toolbar-select-all"
                           @change="toggleSelectAll($event)" aria-label="{{ __('com_media::com_media.select_all') }}">
                </div>

                <nav class="media-breadcrumb" aria-label="{{ __('com_media::com_media.breadcrumb') }}">
                    <ol>
                        @foreach ($breadcrumbs as $i => $crumb)
                            <li class="media-breadcrumb-item">
                                @if ($i === array_key_last($breadcrumbs))
                                    <a href="#" aria-current="page">{{ $crumb['name'] }}</a>
                                @else
                                    <a href="{{ route('larajoom.admin.media.folder', $crumb['path']) }}">{{ $crumb['name'] }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>

                <div class="media-view-search-input" role="search">
                    <input id="media_search" class="form-input transparent" type="text" placeholder="{{ __('com_media::com_media.search') }}" value="" @input="search($event)">
                </div>

                <div class="media-view-icons">
                    <button type="button" class="media-toolbar-icon" aria-label="{{ __('com_media::com_media.sort') }}">
                        @icon('sort-amount')
                    </button>
                    <button type="button" class="media-toolbar-icon media-toolbar-decrease-grid-size"
                            aria-label="{{ __('com_media::com_media.decrease_grid') }}">
                        @icon('search-minus')
                    </button>
                    <button type="button" class="media-toolbar-icon media-toolbar-increase-grid-size"
                            aria-label="{{ __('com_media::com_media.increase_grid') }}">
                        @icon('search-plus')
                    </button>
                    <button type="button" class="media-toolbar-icon media-toolbar-list-view"
                            aria-label="{{ __('com_media::com_media.list_view') }}" @click="toggleView()">
                        {!! larajoom_icon_toggle('menus', 'list') !!}
                    </button>
                    <button type="button" class="media-toolbar-icon media-toolbar-info"
                            aria-label="{{ __('com_media::com_media.info') }}">
                        @icon('info-i')
                    </button>
                </div>
            </div>

            {{-- Browser --}}
            {{-- Panel de orden (Sort): Order By + Direction --}}
            <div id="media-sort-panel" class="row g-3 pt-2 pb-2 pe-3 justify-content-end media-sort-panel" style="display:none">
                <div class="col-3">
                    <select name="media-sort-by" id="media-sort-by" class="form-select-new">
                        <button>
                            <selectedcontent></selectedcontent>
                            <span class="icon-svg">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="m6 9 6 6 6-6"></path>
                                </svg>
                            </span>
                        </button>
                        <optgroup>
                            <option value="name" @selected(true && false)>{{ __('com_media::com_media.name') }}</option>
                            <option value="size" @selected(true && false)>{{ __('com_media::com_media.size') }}</option>
                            <option value="dimension" @selected(true)>{{ __('com_media::com_media.dimensions') }}</option>
                            <option value="date_created" @selected(true && false)>{{ __('com_media::com_media.created') }}</option>
                            <option value="date_modified" @selected(true && false)>{{ __('com_media::com_media.modified') }}</option>
                        </optgroup>
                    </select>
                </div>
                <div class="col-3">
                    <select name="media-sort-dir" id="media-sort-dir" class="form-select-new">
                        <button>
                            <selectedcontent></selectedcontent>
                            <span class="icon-svg">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="m6 9 6 6 6-6"></path>
                                </svg>
                            </span>
                        </button>
                        <optgroup>
                            <option value="asc" @selected(true && false)>{{ __('com_media::com_media.asc') }}</option>
                            <option value="desc" @selected(true)>{{ __('com_media::com_media.desc') }}</option>
                        </optgroup>
                    </select>
                </div>
            </div>

            <div class="media-browser" style="height: 100%;">
                <div class="media-dragoutline">
                    <span class="icon upload-icon">@icon('upload')</span>
                    <p>{{ __('com_media::com_media.drop_here') }}</p>
                </div>

                <div class="media-browser-grid">
                    <div class="media-browser-items media-browser-items-md" style="width: 100%; height: 100%;">

                        @foreach ($folders as $folder)
                        <div class="media-browser-item" data-type="dir"
                             data-path="{{ $folder['relative_path'] }}" data-name="{{ $folder['name'] }}"
                             data-size="" data-modified="" data-ext="dir"
                             data-mime="" data-width="" data-height=""
                             data-directory="{{ dirname($folder['relative_path']) === '.' ? '/' : dirname($folder['relative_path']) }}">
                            <div class="media-browser-item-directory" focused="false">
                                <div class="media-browser-item-preview" tabindex="0"
                                     @click="navigate('{{ $folder['relative_path'] }}')">
                                    <div class="file-background">
                                        <div class="folder-icon">
                                            <span class="icon-folder">
                                                @icon('folder')
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="media-browser-item-info">{{ $folder['name'] }}</div>
                                <span class="media-browser-select" aria-label="{{ __('com_media::com_media.select_item') }}"
                                      title="{{ __('com_media::com_media.select_item') }}" tabindex="0"
                                      @click.stop="toggleSelect($event)"></span>
                                <div class="media-browser-actions">
                                    <button type="button" class="action-toggle"
                                            aria-label="{{ __('com_media::com_media.manage_item') }}: {{ $folder['name'] }}"
                                            title="{{ __('com_media::com_media.manage_item') }}: {{ $folder['name'] }}"
                                            @click.stop="toggleActions($event)">
                                        <span class="image-browser-action icon">@icon('more')</span>
                                    </button>
                                    <div class="media-browser-actions-list">
                                        <a href="{{ route('larajoom.admin.media.folder', $folder['relative_path']) }}"
                                           class="media-browser-actions-item">
                                            <span class="icon">@icon('eye')</span>
                                            <span class="action-text">{{ __('com_media::com_media.view') }}</span>
                                        </a>
                                        <button type="button" class="media-browser-actions-item action-rename"
                                                @click.stop="doRename($event)">
                                            <span class="icon">@icon('edit')</span>
                                            <span class="action-text">{{ __('com_media::com_media.rename') }}</span>
                                        </button>
                                        <button type="button" class="media-browser-actions-item action-delete"
                                                @click.stop="doDelete($event)">
                                            <span class="icon">@icon('trash')</span>
                                            <span class="action-text">{{ __('com_media::com_media.delete') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        @foreach ($files as $file)
                        <div class="media-browser-item" data-type="file"
                             data-path="{{ $file['relative_path'] }}" data-name="{{ $file['name'] }}"
                             data-size="{{ $file['size'] }}" data-modified="{{ $file['modified'] }}"
                             data-ext="{{ $file['extension'] }}"
                             data-mime="{{ $file['mime'] ?? '' }}"
                             data-width="{{ $file['width'] ?? '' }}"
                             data-height="{{ $file['height'] ?? '' }}"
                             data-directory="{{ dirname($file['relative_path']) === '.' ? '/' : dirname($file['relative_path']) }}">
                            <div class="media-browser-image" tabindex="0">
                                <div class="media-browser-item-preview" title="{{ $file['name'] }}">
                                    <div class="image-background">
                                        @if (in_array($file['extension'], ['jpg','jpeg','png','gif','webp','bmp','svg']))
                                            <img class="image-cropped"
                                                 src="{{ route('larajoom.admin.media.preview', ['path' => $file['relative_path']]) }}"
                                                 alt="{{ $file['name'] }}" loading="lazy">
                                        @else
                                            <span class="file-icon generic">@icon('file')</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="media-browser-item-info" title="{{ $file['name'] }}">{{ $file['name'] }}</div>
                                <span class="media-browser-select" aria-label="{{ __('com_media::com_media.select_item') }}"
                                      title="{{ __('com_media::com_media.select_item') }}" tabindex="0"
                                      @click.stop="toggleSelect($event)"></span>
                                <div class="media-browser-actions">
                                    <button type="button" class="action-toggle"
                                            aria-label="{{ __('com_media::com_media.manage_item') }}: {{ $file['name'] }}"
                                            title="{{ __('com_media::com_media.manage_item') }}: {{ $file['name'] }}"
                                            @click.stop="toggleActions($event)">
                                        <span class="image-browser-action icon">@icon('more')</span>
                                    </button>
                                    <div class="media-browser-actions-list">
                                        <a href="{{ route('larajoom.admin.media.preview', ['path' => $file['relative_path']]) }}"
                                           target="_blank" class="media-browser-actions-item">
                                            <span class="icon">@icon('eye')</span>
                                            <span class="action-text">{{ __('com_media::com_media.view') }}</span>
                                        </a>
                                        <a href="{{ route('larajoom.admin.media.preview', ['path' => $file['relative_path']]) }}"
                                           download class="media-browser-actions-item">
                                            <span class="icon">@icon('download')</span>
                                            <span class="action-text">{{ __('com_media::com_media.download') }}</span>
                                        </a>
                                        <button type="button" class="media-browser-actions-item action-rename"
                                                @click.stop="doRename($event)">
                                            <span class="icon">@icon('edit')</span>
                                            <span class="action-text">{{ __('com_media::com_media.rename') }}</span>
                                        </button>
                                        <button type="button" class="media-browser-actions-item action-delete"
                                                @click.stop="doDelete($event)">
                                            <span class="icon">@icon('trash')</span>
                                            <span class="action-text">{{ __('com_media::com_media.delete') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        @if (empty($folders) && empty($files))
                        <div class="media-browser-item">
                            <div class="media-empty">{{ __('com_media::com_media.empty_folder') }}</div>
                        </div>
                        @endif

                    </div>

                    {{-- Vista lista (tabla) como Joomla --}}
                    <table id="media-browser-table" class="table media-browser-table" style="display:none; width:100%">
                        <thead class="media-browser-table-head">
                            <tr>
                                <th class="type" scope="col"></th>
                                <th class="name" scope="col"><button type="button" class="btn btn-link" data-sort="name">{{ __('com_media::com_media.name') }} <span class="ms-1 icon-sort">@icon('sort')</span></button></th>
                                <th class="size" scope="col"><button type="button" class="btn btn-link" data-sort="size">{{ __('com_media::com_media.size') }} <span class="ms-1 icon-sort">@icon('sort')</span></button></th>
                                <th class="dimension" scope="col"><button type="button" class="btn btn-link" data-sort="dimension">{{ __('com_media::com_media.dimensions') }} <span class="ms-1 icon-sort">@icon('sort')</span></button></th>
                                <th class="created" scope="col"><button type="button" class="btn btn-link" data-sort="created">{{ __('com_media::com_media.created') }} <span class="ms-1 icon-sort">@icon('sort')</span></button></th>
                                <th class="modified" scope="col"><button type="button" class="btn btn-link" data-sort="modified">{{ __('com_media::com_media.modified') }} <span class="ms-1 icon-sort">@icon('sort')</span></button></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($folders as $folder)
                            <tr class="media-browser-item" data-size="0" data-dimension="0" data-created="" data-modified="">
                                <td class="type" data-type=""><span class="icon">@icon('folder')</span></td>
                                <th scope="row" class="name">{{ $folder['name'] }}</th>
                                <td class="size"></td>
                                <td class="dimension"></td>
                                <td class="created"></td>
                                <td class="modified"></td>
                            </tr>
                            @endforeach

                            @foreach ($files as $file)
                            @php
                                $fileIcon = in_array($file['extension'], ['jpg','jpeg','png','gif','webp','bmp','svg'])
                                    ? 'image'
                                    : (in_array($file['extension'], ['pdf']) ? 'file-pdf'
                                        : (in_array($file['extension'], ['doc','docx']) ? 'file-word'
                                            : (in_array($file['extension'], ['xls','xlsx']) ? 'file-excel'
                                                : (in_array($file['extension'], ['zip','rar','tar','gz']) ? 'file-archive' : 'file'))));
                                $fileSize = number_format($file['size'] / 1024, 2) . ' KB';
                                $fileDate = \Carbon\Carbon::createFromTimestamp($file['modified'])->format('Y-m-d H:i');
                                $fileDim = ($file['width'] ?? null) && ($file['height'] ?? null)
                                    ? $file['width'] . 'px * ' . $file['height'] . 'px'
                                    : '';
                            @endphp
                            <tr class="media-browser-item" data-size="{{ $file['size'] }}" data-dimension="{{ ($file['width'] ?? 0) * ($file['height'] ?? 0) }}" data-created="{{ $file['modified'] }}" data-modified="{{ $file['modified'] }}">
                                <td class="type" data-type="{{ $file['extension'] }}"><span class="icon">@icon($fileIcon)</span></td>
                                <th scope="row" class="name">{{ $file['name'] }}</th>
                                <td class="size">{{ $fileSize }}</td>
                                <td class="dimension">{{ $fileDim }}</td>
                                <td class="created">{{ $fileDate }}</td>
                                <td class="modified">{{ $fileDate }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Formularios ocultos (subida, crear carpeta, renombrar, borrar) --}}
    <x-larajoom::form :action="route('larajoom.admin.media.upload')" method="POST" id="upload-form" enctype="multipart/form-data">
        <input type="hidden" name="path" value="{{ $path }}">
        <input type="file" class="hidden" name="files[]" multiple accept="" id="media-file-input">
    </x-larajoom::form>

    <x-larajoom::form :action="route('larajoom.admin.media.folder.create')" method="POST" id="new-folder-form">
        <input type="hidden" name="path" value="{{ $path }}">
        <input type="hidden" name="name" id="new-folder-name">
    </x-larajoom::form>

    <x-larajoom::form :action="route('larajoom.admin.media.rename')" method="POST" id="rename-form">
        <input type="hidden" name="path" value="{{ $path }}">
        <input type="hidden" name="old_name" id="rename-old-path">
        <input type="hidden" name="new_name" id="rename-new-name">
    </x-larajoom::form>

    <x-larajoom::form :action="route('larajoom.admin.media.delete')" method="POST" id="delete-form">
        @method('DELETE')
        <input type="hidden" name="path" id="delete-path">
    </x-larajoom::form>

    <x-larajoom::form :action="route('larajoom.admin.media.delete.bulk')" method="POST" id="delete-bulk-form">
        <div id="delete-bulk-paths"></div>
    </x-larajoom::form>

    {{-- Dialog: Nueva Carpeta --}}
    <div class="media-dialog-overlay" id="new-folder-dialog" style="display:none">
        <div class="media-dialog" role="dialog" aria-modal="true" aria-labelledby="new-folder-dialog-title">
            <h3 id="new-folder-dialog-title">{{ __('com_media::com_media.new_folder') }}</h3>
            <input type="text" id="new-folder-input" class="form-control"
                   placeholder="{{ __('com_media::com_media.folder_name') }}">
            <div class="media-dialog-actions">
                <button type="button" class="btn" id="new-folder-cancel">
                    {{ __('com_media::com_media.cancel') }}
                </button>
                <button type="button" class="btn btn-success" id="new-folder-confirm">
                    {{ __('com_media::com_media.create_folder') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Dialog: Vista previa (doble clic) --}}
    <div class="media-preview-overlay" id="media-preview-modal" style="display:none">
        <div class="media-preview-box" role="dialog" aria-modal="true">
            <button type="button" class="media-preview-close" id="media-preview-close"
                    aria-label="{{ __('com_media::com_media.close') }}">&times;</button>
            <div id="media-preview-body"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
    var ljUploadUrl = '{{ route("larajoom.admin.media.upload") }}';
    var ljDeleteUrl = '{{ route("larajoom.admin.media.delete") }}';
    var ljNewFolderUrl = '{{ route("larajoom.admin.media.folder.create") }}';

    window.ljMediaUpload = function () {
        document.getElementById('media-file-input').click();
    };

    function openNewFolderDialog() {
        var dlg = document.getElementById('new-folder-dialog');
        var input = document.getElementById('new-folder-input');
        dlg.style.display = 'flex';
        input.value = '';
        input.focus();
    }

    function closeNewFolderDialog() {
        document.getElementById('new-folder-dialog').style.display = 'none';
    }

    function submitNewFolder() {
        var name = document.getElementById('new-folder-input').value.trim();
        if (!name) return;
        document.getElementById('new-folder-name').value = name;
        document.getElementById('new-folder-form').submit();
    }

    window.ljMediaNewFolder = openNewFolderDialog;

    var ljPreviewBase = '{{ route("larajoom.admin.media.preview") }}';

    function previewItem(event) {
        var item = event.target.closest('.media-browser-item');
        if (!item || item.dataset.type === 'dir') return;

        var path = item.dataset.path;
        var name = item.dataset.name || '';
        var ext = (name.split('.').pop() || '').toLowerCase();
        var imgExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
        var html;

        if (imgExt.indexOf(ext) !== -1) {
            html = '<img src="' + ljPreviewBase + '?path=' + encodeURIComponent(path) + '" alt="' + name + '">';
        } else {
            html = '<div class="media-empty" style="padding:2rem">' + name + '</div>';
        }

        document.getElementById('media-preview-body').innerHTML = html;
        document.getElementById('media-preview-modal').style.display = '';
    }

    function closePreview() {
        document.getElementById('media-preview-modal').style.display = 'none';
        document.getElementById('media-preview-body').innerHTML = '';
    }

    document.addEventListener('dblclick', function (e) {
        var item = e.target.closest('.media-browser-item');
        if (!item) return;
        previewItem(e);
    });

    window.ljMediaDelete = function () {
        var items = Array.prototype.slice.call(document.querySelectorAll('.media-browser-item.active'));
        if (!items.length) {
            alert('{{ __('com_media::com_media.select_item') }}');
            return;
        }
        if (!confirm('{{ __('com_media::com_media.delete_confirm') }} (' + items.length + ')')) return;

        var holder = document.getElementById('delete-bulk-paths');
        holder.innerHTML = '';
        items.forEach(function (i) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'paths[]';
            input.value = i.dataset.path;
            holder.appendChild(input);
        });
        document.getElementById('delete-bulk-form').submit();
    };

    // Enlazar la toolbar externa
    document.addEventListener('click', function (e) {
        if (e.target && e.target.id === 'new-folder-confirm') {
            e.preventDefault();
            submitNewFolder();
            return;
        }
        if (e.target && e.target.id === 'new-folder-cancel') {
            e.preventDefault();
            closeNewFolderDialog();
            return;
        }
        if (e.target && e.target.id === 'new-folder-dialog' && e.target === e.currentTarget) {
            closeNewFolderDialog();
            return;
        }
        if (e.target && e.target.id === 'media-preview-close') {
            closePreview();
            return;
        }
        if (e.target && e.target.id === 'media-preview-modal' && e.target === e.currentTarget) {
            closePreview();
            return;
        }

        var a = e.target.closest('.panelbar a[href]');
        if (!a) return;
        var href = a.getAttribute('href');
        if (href === '#upload') {
            e.preventDefault();
            window.ljMediaUpload();
        } else if (href === '#new-folder') {
            e.preventDefault();
            window.ljMediaNewFolder();
        } else if (href === '#media-delete') {
            e.preventDefault();
            window.ljMediaDelete();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (document.getElementById('media-preview-modal').style.display !== 'none') {
            closePreview();
        } else if (document.getElementById('new-folder-dialog').style.display !== 'none') {
            closeNewFolderDialog();
        }
    });

    // Subida automática al elegir archivos
    document.addEventListener('change', function (e) {
        if (e.target && e.target.id === 'media-file-input' && e.target.files.length) {
            document.getElementById('upload-form').submit();
        }
    });

    function mediaManager() {
        return {
            listView: false,

            init() {},

            toggleView() {
                this.listView = !this.listView;
                var grid = document.querySelector('.media-browser-items');
                var tbl = document.getElementById('media-browser-table');
                if (this.listView) {
                    if (grid) grid.style.display = 'none';
                    if (tbl) tbl.style.display = '';
                } else {
                    if (grid) grid.style.display = '';
                    if (tbl) tbl.style.display = 'none';
                }
            },

            navigate(path) {
                window.location.href = '{{ route("larajoom.admin.media.folder", ":path") }}'.replace(':path', path);
            },

            toggleSelectAll(event) {
                const checked = event.target.checked;
                document.querySelectorAll('.media-browser-item').forEach(item => {
                    item.classList.toggle('active', checked);
                });
            },

            toggleSelect(event) {
                const item = event.currentTarget.closest('.media-browser-item');
                if (!item) return;
                item.classList.toggle('active');
                this.syncSelectAll();
            },

            toggleActions(event) {
                const wrapper = event.currentTarget.closest('.media-browser-actions');
                if (!wrapper) return;
                document.querySelectorAll('.media-browser-actions.active').forEach(w => {
                    if (w !== wrapper) w.classList.remove('active');
                });
                wrapper.classList.toggle('active');
            },

            syncSelectAll() {
                const items = document.querySelectorAll('.media-browser-item');
                const checkedBoxes = document.querySelectorAll('.media-browser-item.active');
                const box = document.querySelector('.media-toolbar-select-all');
                if (box) {
                    box.checked = items.length > 0 && checkedBoxes.length === items.length;
                }
            },

            doRename(event) {
                const item = event.currentTarget.closest('.media-browser-item');
                if (!item) return;
                const name = item.dataset.name || '';
                const newName = prompt('{{ __('com_media::com_media.new_name') }}', name);
                if (!newName || newName === name) return;
                document.getElementById('rename-old-path').value = item.dataset.path;
                document.getElementById('rename-new-name').value = newName;
                document.getElementById('rename-form').submit();
            },

            doDelete(event) {
                const item = event.currentTarget.closest('.media-browser-item');
                if (!item) return;
                if (!confirm('{{ __('com_media::com_media.delete_confirm') }}: ' + (item.dataset.name || ''))) return;
                document.getElementById('delete-path').value = item.dataset.path;
                document.getElementById('delete-form').submit();
            },

            search(event) {
                const q = event.target.value.toLowerCase().trim();
                document.querySelectorAll('.media-browser-item').forEach(item => {
                    const info = item.querySelector('.media-browser-item-info');
                    if (!info) return;
                    item.style.display = (!q || info.textContent.toLowerCase().includes(q)) ? '' : 'none';
                });
            }
        };
    }

    // ===== Botones de la toolbar (sort / grid / lista / info) =====
    function initMediaToolbar() {
        var sizes = ['sm', 'md', 'lg', 'xl'];
        var idx = 1;

        function grid() {
            return document.querySelector('.media-browser-items');
        }

        function applyGrid() {
            var items = grid();
            if (!items) return;
            items.className = items.className.replace(/\bmedia-browser-items-(sm|md|lg|xl)\b/g, '').trim();
            items.classList.add('media-browser-items-' + sizes[idx]);
        }

        // Sort — muestra/oculta el panel de orden (Order By + Direction)
        var allToolbarIcons = document.querySelectorAll('.media-view-icons .media-toolbar-icon');
        var btnSort = Array.prototype.find.call(allToolbarIcons, function (el) {
            return el.tagName === 'BUTTON'
                && !el.classList.contains('media-toolbar-select-all')
                && !el.classList.contains('media-toolbar-decrease-grid-size')
                && !el.classList.contains('media-toolbar-increase-grid-size')
                && !el.classList.contains('media-toolbar-list-view')
                && !el.classList.contains('media-toolbar-info');
        });

        function sortKey(el, key) {
            switch (key) {
                case 'name': {
                    var n = el.dataset.name || (el.querySelector('.name') || {}).textContent || '';
                    return n.toLowerCase();
                }
                case 'size': return parseFloat(el.dataset.size) || 0;
                case 'dimension': return (parseFloat(el.dataset.width) || 0) * (parseFloat(el.dataset.height) || 0);
                case 'date_created':
                case 'date_modified': return parseInt(el.dataset.modified, 10) || 0;
            }
            return 0;
        }

        function applyMediaSort() {
            var bySel = document.getElementById('media-sort-by');
            var dirSel = document.getElementById('media-sort-dir');
            if (!bySel || !dirSel) return;
            var by = bySel.value, dir = dirSel.value;

            var grid = document.querySelector('.media-browser-items');
            var tbl = document.getElementById('media-browser-table');

            if (grid && grid.style.display !== 'none') {
                var els = Array.prototype.slice.call(grid.children);
                els.sort(function (a, b) {
                    var va = sortKey(a, by), vb = sortKey(b, by);
                    return typeof va === 'string' ? va.localeCompare(vb) : va - vb;
                });
                if (dir === 'desc') els.reverse();
                els.forEach(function (el) { grid.appendChild(el); });
            } else if (tbl && tbl.style.display !== 'none') {
                var rows = Array.prototype.slice.call(tbl.querySelectorAll('tbody tr'));
                rows.sort(function (a, b) {
                    var va = sortKey(a, by), vb = sortKey(b, by);
                    return typeof va === 'string' ? va.localeCompare(vb) : va - vb;
                });
                if (dir === 'desc') rows.reverse();
                rows.forEach(function (r) { tbl.querySelector('tbody').appendChild(r); });
            }
        }

        if (btnSort) {
            btnSort.addEventListener('click', function () {
                var panel = document.getElementById('media-sort-panel');
                if (!panel) return;
                panel.style.display = (panel.style.display === 'none') ? '' : 'none';
            });
        }

        var sortBySel = document.getElementById('media-sort-by');
        var sortDirSel = document.getElementById('media-sort-dir');
        if (sortBySel) sortBySel.addEventListener('change', applyMediaSort);
        if (sortDirSel) sortDirSel.addEventListener('change', applyMediaSort);

        var btnDec = document.querySelector('.media-toolbar-decrease-grid-size');
        if (btnDec) {
            btnDec.addEventListener('click', function () {
                if (idx > 0) { idx--; applyGrid(); }
            });
        }

        var btnInc = document.querySelector('.media-toolbar-increase-grid-size');
        if (btnInc) {
            btnInc.addEventListener('click', function () {
                if (idx < sizes.length - 1) { idx++; applyGrid(); }
            });
        }



        // Info
        function hideInfo() {
            var bar = document.getElementById('media-infobar');
            if (bar) bar.style.display = 'none';
        }

        function populateInfo() {
            var bar = document.getElementById('media-infobar');
            if (!bar) return;
            var sel = Array.prototype.slice.call(document.querySelectorAll('.media-browser-item.active'));
            var inner = bar.querySelector('.media-infobar-inner');
            if (!inner) return;

            if (!sel.length) {
                inner.innerHTML =
                    '<h2>{{ __('com_media::com_media.info') }}</h2>' +
                    '<div class="text-center">{{ __('com_media::com_media.no_files') }}</div>';
                return;
            }

            var item = sel[0];
            var name = item.dataset.name || '';
            var isDir = item.dataset.type === 'dir';
            var dir = item.dataset.directory || '/';
            var size = item.dataset.size;
            var sizeKb = size ? (parseInt(size, 10) / 1024).toFixed(2) + ' KB' : '-';
            var modified = item.dataset.modified;
            var modStr = modified ? new Date(parseInt(modified, 10) * 1000).toLocaleString() : '-';
            var createdStr = modStr;
            var w = item.dataset.width, h = item.dataset.height;
            var dimension = (w || h) ? w + 'px * ' + h + 'px' : '-';
            var type = isDir ? '{{ __('com_media::com_media.folder') }}' : '{{ __('com_media::com_media.file') }}';
            var mime = item.dataset.mime || '-';
            var ext = item.dataset.ext || '-';

            inner.innerHTML =
                '<h2>' + name + '</h2>' +
                '<dl>' +
                    '<dt>{{ __('com_media::com_media.folder') }}</dt><dd>' + dir + '</dd>' +
                    '<dt>{{ __('com_media::com_media.type') }}</dt><dd>' + type + '</dd>' +
                    '<dt>{{ __('com_media::com_media.created') }}</dt><dd>' + createdStr + '</dd>' +
                    '<dt>{{ __('com_media::com_media.modified') }}</dt><dd>' + modStr + '</dd>' +
                    '<dt>{{ __('com_media::com_media.dimensions') }}</dt><dd>' + dimension + '</dd>' +
                    '<dt>{{ __('com_media::com_media.size') }}</dt><dd>' + sizeKb + '</dd>' +
                    '<dt>{{ __('com_media::com_media.mime_type') }}</dt><dd>' + mime + '</dd>' +
                    '<dt>{{ __('com_media::com_media.extension') }}</dt><dd>' + ext + '</dd>' +
                '</dl>';
        }

        function toggleInfo() {
            var bar = document.getElementById('media-infobar');
            if (!bar) {
                bar = document.createElement('div');
                bar.className = 'media-infobar';
                bar.id = 'media-infobar';
                bar.innerHTML = '<button type="button" class="infobar-close" aria-label="{{ __('com_media::com_media.close') }}">&times;</button><div class="media-infobar-inner"></div>';
                var browser = document.querySelector('.media-browser');
                if (browser) browser.appendChild(bar);
                bar.querySelector('.infobar-close').addEventListener('click', hideInfo);
            }
            if (bar.style.display === 'none') {
                populateInfo();
                bar.style.display = '';
            } else {
                bar.style.display = 'none';
            }
        }

        var btnInfo = document.querySelector('.media-toolbar-info');
        if (btnInfo) {
            btnInfo.addEventListener('click', toggleInfo);
        }

        // Ordenar columnas de la tabla
        var tblHead = document.querySelector('.media-browser-table-head');
        if (tblHead) {
            tblHead.addEventListener('click', function (e) {
                var btn = e.target.closest('button[data-sort]');
                if (!btn) return;
                var tbody = document.querySelector('#media-browser-table tbody');
                if (!tbody) return;

                var field = btn.dataset.sort;
                var dir = btn.dataset.dir === 'asc' ? 'desc' : 'asc';
                btn.dataset.dir = dir;

                var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
                rows.sort(function (a, b) {
                    var va = a.dataset[field], vb = b.dataset[field];
                    if (field === 'name') {
                        return (va || '').localeCompare(vb || '');
                    }
                    return (parseFloat(va) || 0) - (parseFloat(vb) || 0);
                });
                if (dir === 'desc') rows.reverse();
                rows.forEach(function (r) { tbody.appendChild(r); });

                tblHead.querySelectorAll('button[data-sort]').forEach(function (b) {
                    b.classList.toggle('active', b === btn);
                });
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMediaToolbar);
    } else {
        initMediaToolbar();
    }
    </script>
@endpush
