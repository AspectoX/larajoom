@php
    $groups = $groups ?? [];
    $filters = $filters ?? [];

    $filterTypes = [
        'BL'   => __('com_config::com_config.filter_type_default_forbidden'),
        'CBL'  => __('com_config::com_config.filter_type_custom_forbidden'),
        'WL'   => __('com_config::com_config.filter_type_allowed'),
        'NH'   => __('com_config::com_config.filter_type_no_html'),
        'NONE' => __('com_config::com_config.filter_type_no_filter'),
    ];
@endphp

<fieldset class="options-form">
    <legend>{{ __('com_config::com_config.filters_settings') }}</legend>

    <p>{{ __('com_config::com_config.filters_desc') }}</p>

    <table class="ax-table">
        <thead>
            <tr>
                <th scope="col">{{ __('com_config::com_config.filter_groups') }}</th>
                <th scope="col">{{ __('com_config::com_config.filter_type') }}</th>
                <th scope="col">{{ __('com_config::com_config.filter_tags') }}</th>
                <th scope="col">{{ __('com_config::com_config.filter_attributes') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($groups as $group)
                @php
                    $groupFilter = $filters[$group->id] ?? [];
                    $filterType = $groupFilter['filter_type'] ?? 'BL';
                    $filterTags = $groupFilter['filter_tags'] ?? '';
                    $filterAttributes = $groupFilter['filter_attributes'] ?? '';
                @endphp
                <tr>
                    <th scope="row">{{ $group->title }}</th>
                    <td>
                        <x-larajoom::select name="filters[{{ $group->id }}][filter_type]" id="filters_{{ $group->id }}_filter_type" :label="null">
                            @foreach ($filterTypes as $value => $label)
                                <option value="{{ $value }}" @selected($filterType === $value)>{{ $label }}</option>
                            @endforeach
                        </x-larajoom::select>
                    </td>
                    <td>
                        <x-larajoom::input name="filters[{{ $group->id }}][filter_tags]" id="filters_{{ $group->id }}_filter_tags" :label="null" :value="$filterTags" size="40" />
                    </td>
                    <td>
                        <x-larajoom::input name="filters[{{ $group->id }}][filter_attributes]" id="filters_{{ $group->id }}_filter_attributes" :label="null" :value="$filterAttributes" size="40" />
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</fieldset>
