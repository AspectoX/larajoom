@extends('tpl::index')

@section('title', __('com_templates::com_templates.manage_title'))
@section('icon', 'palette')

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('com_templates::com_templates.cancel'),
            'icon'  => 'cancel',
            'url'   => url('/administrator'),
            'color' => 'warning',
        ],
    ]">
        <form method="GET" action="{{ route('larajoom.admin.templates.index') }}" style="display:inline;">
            <x-larajoom::select
                name="client"
                :options="[
                    '' => __('com_templates::com_templates.all_templates'),
                    'site' => __('com_templates::com_templates.site'),
                    'administrator' => __('com_templates::com_templates.administrator'),
                ]"
                :value="$client"
                onchange="this.form.submit()"
            />
        </form>
    </x-larajoom::toolbar>
@endsection

@section('content')
    @php
        $rows = [];

        foreach ($templates as $tpl) {
            $firstStyle = $styles->where('template', $tpl['element'])->first();
            $detailsUrl = $firstStyle
                ? route('larajoom.admin.templates.edit', $firstStyle->id)
                : route('larajoom.admin.templates.create', $tpl['element']);

            $imageHtml = '<span class="btn-icon">' . larajoom_icon('palette') . '</span>';

            if (!empty($tpl['image'])) {
                $imageHtml = '<img src="' . e(asset($tpl['image'])) . '" alt="' . e($tpl['title']) . '" style="max-width:120px;max-height:80px;">';
            } else {
                $imageHtml = '<div class="small">' . e(__('com_templates::com_templates.no_preview')) . '</div>';
            }

            $templateCell = e($tpl['title'])
                . ' <a href="' . $detailsUrl . '">' . e(__('com_templates::com_templates.details_and_files')) . '</a>';

            $rows[] = [
                ['html' => $imageHtml],
                ['html' => $templateCell],
                e($tpl['version']),
                e($tpl['date']),
                e($tpl['author']),
                $tpl['override'],
            ];
        }
    @endphp

    @if (session('success'))
        <x-message type="success" :message="session('success')" />
    @endif

    <x-larajoom::table
        :headers="[
            __('com_templates::com_templates.image'),
            __('com_templates::com_templates.template'),
            __('com_templates::com_templates.version'),
            __('com_templates::com_templates.date'),
            __('com_templates::com_templates.author'),
            __('com_templates::com_templates.override_files'),
        ]"
        :rows="$rows"
        :actions="[]"
        :asset="'com_templates'"
        :draggable="false"
    />
@endsection