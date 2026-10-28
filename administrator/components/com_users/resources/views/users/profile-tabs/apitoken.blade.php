<div class="alert alert-info">
    <span class="icon-info-circle" aria-hidden="true"></span>
    <span class="visually-hidden">Info</span>
    {{ __('com_users::com_users.api_token_info') }}
</div>
<div class="form-grid">
    <div class="control-group">
        <div class="control-label">
            <label for="jform_joomlatoken_token">{{ __('com_users::com_users.api_token_token') }}</label>
        </div>
        <div class="controls">
            <div class="input-group">
                <x-larajoom::input
                    name="jform[joomlatoken][token]"
                    id="jform_joomlatoken_token"
                    :value="$apiToken ?? ''"
                    readonly
                />
                <button class="btn btn-primary" type="button" id="token-copy" title="Copy the token to your clipboard">
                    @icon('copy') {{ __('larajoom_admin::admin.copy') }}
                </button>
            </div>
            <div id="jform_joomlatoken_token-desc">
                <small class="form-text">{{ __('com_users::com_users.api_token_token_desc') }}</small>
            </div>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <label for="jform_joomlatoken_enabled">{{ __('com_users::com_users.api_token_active') }}</label>
        </div>
        <div class="controls">
            <fieldset id="jform_joomlatoken_enabled">
                <legend class="visually-hidden">{{ __('com_users::com_users.api_token_active') }}</legend>
                <div class="switcher">
                    <x-larajoom::toggle
                        name="jform[joomlatoken][enabled]"
                        :checked="(bool) ($apiEnabled ?? true)"
                        :unchecked="0"
                    />
                    <label for="jform_joomlatoken_enabled">{{ __('larajoom_admin::admin.JYES') }}</label>
                    <label for="jform_joomlatoken_enabled_no">{{ __('larajoom_admin::admin.JNO') }}</label>
                </div>
            </fieldset>
            <div id="jform_joomlatoken_enabled-desc">
                <small class="form-text">{{ __('com_users::com_users.api_token_active_desc') }}</small>
            </div>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <label for="jform_joomlatoken_reset">{{ __('com_users::com_users.api_token_reset') }}</label>
        </div>
        <div class="controls">
            <fieldset id="jform_joomlatoken_reset">
                <legend class="visually-hidden">{{ __('com_users::com_users.api_token_reset') }}</legend>
                <div class="switcher">
                    <x-larajoom::toggle
                        name="jform[joomlatoken][reset]"
                        :checked="(bool) ($apiReset ?? false)"
                        :unchecked="0"
                    />
                    <label for="jform_joomlatoken_reset">{{ __('larajoom_admin::admin.JYES') }}</label>
                    <label for="jform_joomlatoken_reset_no">{{ __('larajoom_admin::admin.JNO') }}</label>
                </div>
            </fieldset>
            <div id="jform_joomlatoken_reset-desc">
                <small class="form-text">{{ __('com_users::com_users.api_token_reset_desc') }}</small>
            </div>
        </div>
    </div>
</div>