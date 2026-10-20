@php
    $groups = $groups ?? [];
    $actions = $actions ?? [];
    $rules = $rules ?? [];
@endphp

<fieldset class="options-form">
    <legend>{{ __('com_config::com_config.permissions_settings') }}</legend>

    <table class="ax-table">
        <thead>
            <tr>
                <th>{{ __('com_config::com_config.permissions') }}</th>
                @foreach ($groups as $group)
                    <th class="text-center">{{ $group->title }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($actions as $action => $label)
                <tr>
                    <th scope="row">{{ $label }}</th>
                    @foreach ($groups as $group)
                        <td class="text-center">
                            <input type="checkbox"
                                class="form-check"
                                id="rule_{{ $action }}_{{ $group->id }}"
                                name="rules[{{ $action }}][{{ $group->id }}]"
                                value="1"
                                @checked(!empty($rules[$action][$group->id]))>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</fieldset>
