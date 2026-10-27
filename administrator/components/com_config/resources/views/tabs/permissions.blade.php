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

    <div class="content-grid columns-1fr-5fr">
        <nav class="nav-permissions">
            <ul class="nav-permissions--list">
                <li class="nav-permissions--item active">
                    <a href="#" class="nav-permissions--link">Public</a>
                </li>
                <li class="nav-permissions--item nivel-5">
                    <a href="#" class="nav-permissions--link">Guest</a>
                </li>
                <li class="nav-permissions--item nivel-5">
                    <a href="#" class="nav-permissions--link">Registered</a>
                </li>
                <li class="nav-permissions--item nivel-4">
                    <a href="#" class="nav-permissions--link">Author</a>
                </li>
                <li class="nav-permissions--item nivel-4">
                    <a href="#" class="nav-permissions--link">Editor</a>
                </li>
                <li class="nav-permissions--item nivel-4">
                    <a href="#" class="nav-permissions--link">Publisher</a>
                </li>
                <li class="nav-permissions--item nivel-3">
                    <a href="#" class="nav-permissions--link">Manager</a>
                </li>
                <li class="nav-permissions--item nivel-2">
                    <a href="#" class="nav-permissions--link">Administrator</a>
                </li>
                <li class="nav-permissions--item nivel-1">
                    <a href="#" class="nav-permissions--link">SuperAdmin</a>
                </li>
            </ul>
        </nav>
        <div class="body-permissions">
            <ul class="body-list">
                <div class="content-body-list-head">
                    <li class="body-list-item">
                        <span class="body-list-text">Action</span>
                    </li>
                    <li class="body-list-item">
                        <span class="body-list-text">Select New Setting</span>
                    </li>
                    <li class="body-list-item">
                        <span class="body-list-text">Calculated Setting</span>
                    </li>
                </div>
                <div class="content-body-list-body">
                    <div class="row">
                        <li class="body-list-item">
                            <span class="body-list-text">Site Login</span>
                        </li>
                        <li class="body-list-item">
                            <select name="" id="" class="form-select-new">
                                <button>
                                    <selectedcontent></selectedcontent>
                                    <span class="icon-svg">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="m6 9 6 6 6-6"></path>
                                        </svg>
                                    </span>
                                </button>
                                <optgroup>
                                    <option value="0">{{ __('com_config::com_config.tier_none') }}</option>
                                    <option value="1">{{ __('com_config::com_config.tier_read') }}</option>
                                    <option value="2">{{ __('com_config::com_config.tier_write') }}</option>
                                    <option value="3">{{ __('com_config::com_config.tier_delete') }}</option>
                                </optgroup>
                            </select>
                        </li>
                        <li class="body-list-item">
                            <span class="badge color-danger">Not Allowed (Default)</span>
                        </li>
                    </div>
                    <div class="row">
                        <li class="body-list-item">
                            <span class="body-list-text">Administrator Login</span>
                        </li>
                        <li class="body-list-item">
                            <select name="" id="" class="form-select-new">
                                <button>
                                    <selectedcontent></selectedcontent>
                                    <span class="icon-svg">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="m6 9 6 6 6-6"></path>
                                        </svg>
                                    </span>
                                </button>
                                <optgroup>
                                    <option value="0">{{ __('com_config::com_config.tier_none') }}</option>
                                    <option value="1">{{ __('com_config::com_config.tier_read') }}</option>
                                    <option value="2">{{ __('com_config::com_config.tier_write') }}</option>
                                    <option value="3">{{ __('com_config::com_config.tier_delete') }}</option>
                                </optgroup>
                            </select>
                        </li>
                        <li class="body-list-item">
                            <span class="badge color-danger">Not Allowed (Default)</span>
                        </li>
                    </div>
                </div>
            </ul>
        </div>
    </div>
</fieldset>