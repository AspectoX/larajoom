@extends('admin_tpl::index')

@section('title', __('com_config::com_config.global_configuration'))
@section('icon', 'config')

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('com_config::com_config.save'),
            'icon'  => 'save',
            'type'  => 'submit',
            'form'  => 'configForm',
            'name'  => 'task',
            'value' => 'save',
        ],
        [
            'label' => __('com_config::com_config.save_close'),
            'icon'  => 'check',
            'type'  => 'submit',
            'form'  => 'configForm',
            'name'  => 'task',
            'value' => 'save2close',
        ],
        [
            'label' => __('com_config::com_config.cancel'),
            'icon'  => 'cancel',
            'url'   => url('/administrator'),
            'color' => 'warning',
        ],
    ]" />
@endsection

@section('content')
    <form action="{{ route('larajoom.admin.config.save') }}" method="POST" id="configForm" class="main-card form-validate">
        @csrf

        <div class="details">
            {{-- TAB: SITE --}}
            <details class="details__item" open name="config">
                <summary class="details__link">{{ __('com_config::com_config.tab_site') }}</summary>
                <div class="details__body">
                    @include('admin.components.com_config::tabs.site', ['config' => $config['site'] ?? []])
                </div>
            </details>

            {{-- TAB: SYSTEM --}}
            <details class="details__item" name="config">
                <summary class="details__link">{{ __('com_config::com_config.tab_system') }}</summary>
                <div class="details__body">
                    @include('admin.components.com_config::tabs.system', ['config' => $config['system'] ?? []])
                </div>
            </details>

            {{-- TAB: SERVER --}}
            <details class="details__item" name="config">
                <summary class="details__link">{{ __('com_config::com_config.tab_server') }}</summary>
                <div class="details__body">
                    @include('admin.components.com_config::tabs.server', ['config' => $config['server'] ?? []])
                </div>
            </details>

            {{-- TAB: LOGGING --}}
            <details class="details__item" name="config">
                <summary class="details__link">{{ __('com_config::com_config.tab_logging') }}</summary>
                <div class="details__body">
                    @include('admin.components.com_config::tabs.logging', ['config' => $config['logging'] ?? []])
                </div>
            </details>

            {{-- TAB: FILTERS --}}
            <details class="details__item" name="config">
                <summary class="details__link">{{ __('com_config::com_config.tab_filters') }}</summary>
                <div class="details__body">
                    @include('admin.components.com_config::tabs.filters', ['config' => $config['filters'] ?? []])
                </div>
            </details>

            {{-- TAB: PERMISSIONS --}}
            <details class="details__item" name="config">
                <summary class="details__link">{{ __('com_config::com_config.tab_permissions') }}</summary>
                <div class="details__body">
                    @include('admin.components.com_config::tabs.permissions', ['config' => $config['permissions'] ?? []])
                </div>
            </details>
        </div>

    <input type="hidden" name="task" value="">
</form>

<style>
    .details__body {
        display: block;
    }
    .options-form{
        legend{
            padding: 0 var(--sz-8);
        }
        p{
            margin-bottom: var(--sz-8);
        }
        td .form-group{
            display: flex;
            margin-block: var(--sz-4);
        }
    }
</style>
@endsection

