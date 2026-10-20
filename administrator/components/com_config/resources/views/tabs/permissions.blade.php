@php
    use Aspectox\LaraJoom\Core\Access;

    $groups = $groups ?? [];
    $assets = $assets ?? [];
    $rules = $assetRules ?? [];

    // Opciones de nivel de permiso
    $tierOptions = [
        0 => __('com_config::com_config.tier_none'),
        1 => __('com_config::com_config.tier_read'),
        2 => __('com_config::com_config.tier_write'),
        3 => __('com_config::com_config.tier_delete'),
    ];

    // Nivel actual de cada grupo sobre cada asset
    $tierFor = function (string $asset, int $groupId) use ($rules) {
        $assetRules = $rules[$asset] ?? [];

        if (empty($assetRules)) {
            return Access::TIER_NONE;
        }

        $actions = [];

        foreach ($assetRules as $action => $groupRules) {
            if (!empty($groupRules[$groupId]) || !empty($groupRules[(string) $groupId])) {
                $actions[] = $action;
            }
        }

        return Access::tierForActions($actions);
    };
@endphp

<fieldset class="options-form">
    <legend>{{ __('com_config::com_config.permissions_settings') }}</legend>

    <p>{{ __('com_config::com_config.permissions_hint') }}</p>

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
            @foreach ($assets as $asset)
                <tr>
                    <th scope="row">{{ $asset->title ?: $asset->name }}</th>
                    @foreach ($groups as $group)
                        @php($currentTier = $tierFor($asset->name, (int) $group->id))
                        <td class="text-center">
                            <select name="permissions[{{ $asset->name }}][{{ $group->id }}]" id="permission_{{ $asset->name }}_{{ $group->id }}" class="form-select-new">
                                <button>
                                    <selectedcontent></selectedcontent>
                                    <span class="icon-svg">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="m6 9 6 6 6-6"></path>
                                        </svg>
                                    </span>
                                </button>
                                <optgroup>
                                    <option value="0" @selected($currentTier === 0)>{{ __('com_config::com_config.tier_none') }}</option>
                                    <option value="1" @selected($currentTier === 1)>{{ __('com_config::com_config.tier_read') }}</option>
                                    <option value="2" @selected($currentTier === 2)>{{ __('com_config::com_config.tier_write') }}</option>
                                    <option value="3" @selected($currentTier === 3)>{{ __('com_config::com_config.tier_delete') }}</option>
                                </optgroup>
                            </select>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</fieldset>
