<div class="alert alert-info"><span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden">Info</span>{{ __('com_users::com_users.api_token_info') }}</div>
<div class="form-grid">
    <div class="form-group">
        <label class="form-label" id="jform_joomlatoken_token-lbl" for="jform_joomlatoken_token">{{ __('com_users::com_users.api_token_token') }}</label>
        <div class="input-button gtc-1">
            <x-larajoom::input type="text" name="jform[joomlatoken][token]" id="jform_joomlatoken_token" :value="$apiToken ?? ''" readonly />
            <button class="btn btn-primary" type="button" id="token-copy" title="Copy the token to your clipboard">{{ __('larajoom_admin::admin.copy') }}</button>
        </div>
        <span></span>
        <div id="jform_joomlatoken_token-desc span2">
            <small class="form-text">{{ __('com_users::com_users.api_token_token_desc') }}</small>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" id="jform_joomlatoken_enabled-lbl" for="jform_joomlatoken_enabled">{{ __('com_users::com_users.api_token_active') }}</label>
        <x-larajoom::toggle name="jform[joomlatoken][enabled]" :checked="(bool)($apiEnabled ?? true)" :unchecked="0" />
        <span></span>
        <div id="jform_joomlatoken_enabled-desc">
            <small class="form-text">{{ __('com_users::com_users.api_token_active_desc') }}</small>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label" id="jform_joomlatoken_reset-lbl" for="jform_joomlatoken_reset">{{ __('com_users::com_users.api_token_reset') }}</label>
        <x-larajoom::toggle name="jform[joomlatoken][reset]" :checked="(bool)($apiReset ?? false)" :unchecked="0" />
        <span></span>
        <div id="jform_joomlatoken_reset-desc">
            <small class="form-text">{{ __('com_users::com_users.api_token_reset_desc') }}</small>
        </div>
    </div>
</div>