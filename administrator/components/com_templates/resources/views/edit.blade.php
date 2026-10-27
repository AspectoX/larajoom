@extends('admin_tpl::index')

@php
    $template  = $style->template ?? $template;
    $client    = $client ?? 'site';
    $styleId   = $style->id ?? 0;
    $hasStyle  = $style !== null && $styleId !== 0;
    $editUrl   = $hasStyle
        ? route('larajoom.admin.templates.edit', ['id' => $styleId])
        : (!empty($template) ? route('larajoom.admin.templates.create', ['template' => $template]) : null);

    $toolbarButtons = [
        [
            'label' => __('com_templates::com_templates.cancel'),
            'icon'  => 'cancel',
            'url'   => route('larajoom.admin.templates.index'),
            'color' => 'warning',
        ],
        [
            'label' => __('com_templates::com_templates.preview'),
            'icon'  => 'eye',
            'url'   => route('larajoom.admin.templates.preview', $style?->template ?? $template),
            'color' => 'info',
        ],
    ];

    $cmExtension = strtolower(pathinfo((string) ($filename ?? ''), PATHINFO_EXTENSION));
    $cmMode = match ($cmExtension) {
        'php', 'blade'          => 'application/x-httpd-php',
        'css', 'scss', 'less'   => 'text/css',
        'js', 'mjs', 'cjs'      => 'text/javascript',
        'json', 'geojson'       => ['name' => 'javascript', 'json' => true],
        'html', 'htm', 'svg'    => 'text/html',
        'md', 'markdown'        => 'text/x-markdown',
        'xml', 'rss'            => 'application/xml',
        default                 => null,
    };
    $cmModeAttr = $cmMode === null ? '' : e((string) json_encode($cmMode));
    $cmEnabled  = $activeFile !== '' && $source !== null && $cmMode !== null;

    if ($hasStyle) {
        $toolbarButtons = array_merge(
            [
                [
                    'label' => __('com_templates::com_templates.save'),
                    'icon'  => 'save',
                    'type'  => 'submit',
                    'form'  => 'adminForm',
                ],
                [
                    'label' => __('com_templates::com_templates.new_file'),
                    'icon'  => 'plus',
                    'url'   => route('larajoom.admin.templates.newfile', $styleId),
                    'color' => 'success',
                ],
            ],
            $toolbarButtons
        );
    }
@endphp

@section('title', $style ? __('com_templates::com_templates.edit_style') : __('com_templates::com_templates.new_style'))
@section('icon', 'code')

@section('toolbar')
    <x-larajoom::toolbar :buttons="$toolbarButtons" />
@endsection

@section('content')

    <div class="details">
        {{-- TAB: EDITOR --}}
        <details class="details__item" open name="template">
            <summary class="details__link">{{ __('com_templates::com_templates.tab_editor') }}</summary>
            <div class="details__body">
                @if ($activeFile !== '' && ($source !== null || $imageUrl !== null))
                    <div class="row mt-2">
                        <div class="col-md-8" id="conditional-section">
                            <p class="lead">{{ sprintf(__('com_templates::com_templates.template_filename'), e($activeFile), e($template)) }}</p>
                            <p class="lead path hidden">{{ e($filePath ?? '') }}</p>
                        </div>
                    </div>
                @endif
                <div class="content-grid columns-2fr-4fr">
                    @php $mediaTree = $mediaTree ?? []; $rootMediaLabel = $rootMediaLabel ?? ''; @endphp
                    @if (!empty($rootLabel))
                        <div id="treeholder" class="tree-holder">
                            <ul class="directory-tree treeselect">
                                <li class="folder-select">
                                    <a class="folder-url" href=""><span class="icon-folder">{!! larajoom_icon('folder') !!}</span>&nbsp;{{ $rootLabel }}</a>
                                    @include('admin.components.com_templates::edit.tree', ['nodes' => $tree, 'open' => true])
                                </li>
                            </ul>
                            @if (!empty($mediaTree))
                                <ul class="directory-tree treeselect">
                                    <li class="folder-select">
                                        <a class="folder-url" href=""><span class="icon-folder">{!! larajoom_icon('folder') !!}</span>&nbsp;{{ $rootMediaLabel }}</a>
                                        @include('admin.components.com_templates::edit.tree', ['nodes' => $mediaTree, 'open' => true])
                                    </li>
                                </ul>
                            @endif
                        </div>
                    @endif

                    <div class="content-brower">
                        @php $imageUrl = $imageUrl ?? null; @endphp
                        @if ($activeFile !== '' && $source !== null && $hasStyle)
                            <form id="adminForm" method="POST" action="{{ route('larajoom.admin.templates.savefile', $styleId) }}">
                                @csrf
                                <input type="hidden" name="template" value="{{ $template }}">
                                <input type="hidden" name="client" value="{{ $client }}">
                                <input type="hidden" name="style_id" value="{{ $styleId }}">
                                <input type="hidden" name="file" value="{{ $activeFile }}">

                                <fieldset class="options-form">
                                    <legend></legend>
                                    <div class="row mt-2">
                                        <div class="col-md-8" id="conditional-section">
                                            <p class="lead">{{ sprintf(__('com_templates::com_templates.template_filename'), e($activeFile), e($template)) }}</p>
                                            <p class="lead path hidden">{{ e($filePath ?? '') }}</p>
                                        </div>
                                    </div>
                                    <textarea name="content" id="file-editor" rows="25" data-cm-mode='{{ $cmModeAttr }}'>{{ $source }}</textarea>
                                </fieldset>
                            </form>
                        @elseif ($activeFile !== '' && $source !== null)
                            <fieldset class="options-form">
                                <legend></legend>
                                <textarea readonly id="file-editor" rows="25" data-cm-mode='{{ $cmModeAttr }}'>{{ $source }}</textarea>
                            </fieldset>
                        @elseif ($imageUrl !== null && $activeFile !== '')
                            <fieldset class="options-form">
                                <legend>{{ $filename }}</legend>
                                <img src="{{ asset($imageUrl) }}" alt="{{ $filename }}" style="max-width:100%;">
                            </fieldset>
                        @else
                            <fieldset class="options-form">
                                <legend>{{ __('com_templates::com_templates.home_heading') }}</legend>
                                @error('file')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                                <p>{{ __('com_templates::com_templates.home_text') }}</p>
                                <p>
                                    <a href="https://guide.joomla.org/user-manual/templates/templates-template-overrides" target="_blank" rel="noopener" class="btn btn-primary btn-lg">
                                        {{ __('com_templates::com_templates.home_button') }}
                                    </a>
                                </p>
                            </fieldset>
                        @endif
                    </div>
                </div>
            </div>
        </details>

        {{-- TAB: DETAILS --}}
        <details class="details__item" name="template">
            <summary class="details__link">{{ __('com_templates::com_templates.template_details') }}</summary>
            <div class="details__body">
                <fieldset class="options-form">
                    <legend>{{ __('com_templates::com_templates.template_details') }}</legend>
                    <div class="style-info-grid">
                        <div><strong>{{ __('com_templates::com_templates.template') }}:</strong> {{ $template }}</div>
                        <div><strong>{{ __('com_templates::com_templates.style') }}:</strong> {{ __('com_templates::com_templates.style_title') }}</div>
                        <div><strong>{{ __('com_templates::com_templates.client') }}:</strong> {{ $client }}</div>
                        <div><strong>{{ __('com_templates::com_templates.version') }}:</strong> {{ '--' }}</div>
                        <div><strong>{{ __('com_templates::com_templates.author') }}:</strong> {{ '--' }}</div>
                        <div><strong>{{ __('com_templates::com_templates.date') }}:</strong> {{ '--' }}</div>
                    </div>
                </fieldset>
            </div>
        </details>
    </div>

    @once
    <script>
        document.querySelectorAll('#treeholder a.folder-url').forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                var li = a.closest('li');
                if (!li) {
                    return;
                }
                var ul = li.querySelector(':scope > ul');
                if (ul) {
                    ul.classList.toggle('hidden');
                }
            });
        });
    </script>
    @endonce

    @if ($cmEnabled)
        @once
        @push('styles')
            <link rel="stylesheet" href="{{ asset('media/vendor/codemirror/lib/codemirror.css') }}">
            <link rel="stylesheet" href="{{ asset('media/vendor/codemirror/theme/material-palenight.css') }}">
            <style>
                .CodeMirror { height: auto; min-height: 300px; border: 1px solid #c0c4cc; border-radius: 4px; font-family: monospace; }
            </style>
        @endpush
        @endonce

        @once
        @push('scripts')
            <script src="{{ asset('media/vendor/codemirror/lib/codemirror.js') }}"></script>
            <script src="{{ asset('media/vendor/codemirror/mode/xml/xml.js') }}"></script>
            <script src="{{ asset('media/vendor/codemirror/mode/css/css.js') }}"></script>
            <script src="{{ asset('media/vendor/codemirror/mode/javascript/javascript.js') }}"></script>
            <script src="{{ asset('media/vendor/codemirror/mode/htmlmixed/htmlmixed.js') }}"></script>
            <script src="{{ asset('media/vendor/codemirror/mode/php/php.js') }}"></script>
            <script src="{{ asset('media/vendor/codemirror/mode/markdown/markdown.js') }}"></script>
            <script>
                (function () {
                    var el = document.getElementById('file-editor');
                    if (!el || typeof window.CodeMirror === 'undefined') {
                        return;
                    }
                    var mode = 'text/plain';
                    try {
                        mode = JSON.parse(el.getAttribute('data-cm-mode')) || mode;
                    } catch (e) {}

                    CodeMirror.fromTextArea(el, {
                        lineNumbers: true,
                        lineWrapping: true,
                        theme: 'material-palenight',
                        mode: mode,
                        readOnly: el.readOnly
                    });
                })();
            </script>
        @endpush
        @endonce
    @endif
@endsection
