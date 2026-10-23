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
                            aria-label="{{ __('com_media::com_media.list_view') }}">
                        @icon('menus')
                    </button>
                    <button type="button" class="media-toolbar-icon media-toolbar-info"
                            aria-label="{{ __('com_media::com_media.info') }}">
                        @icon('info-i')
                    </button>
                </div>
            </div>

            {{-- Browser --}}
            <div class="media-browser" style="height: 100%;">
                <div class="media-dragoutline">
                    <span class="icon upload-icon">@icon('upload')</span>
                    <p>{{ __('com_media::com_media.drop_here') }}</p>
                </div>

                <div class="media-browser-grid">
                    <div class="media-browser-items media-browser-items-md" style="width: 100%; height: 100%;">

                        @foreach ($folders as $folder)
                        <div class="media-browser-item" data-type="dir"
                             data-path="{{ $folder['relative_path'] }}" data-name="{{ $folder['name'] }}">
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
                             data-path="{{ $file['relative_path'] }}" data-name="{{ $file['name'] }}">
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
            init() {},

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
    </script>
@endpush
