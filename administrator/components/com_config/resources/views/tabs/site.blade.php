<fieldset class="options-form">
    <legend>{{ __('com_config::com_config.site_settings') }}</legend>

    <x-larajoom::input name="sitename" :label="__('com_config::com_config.sitename')" :value="config('larajoom.sitename', 'LaraJoom')" size="60" required />

    <x-larajoom::toggle name="offline" :label="__('com_config::com_config.offline')" :checked="(int) config('larajoom.offline', 0) === 1" />

    <x-larajoom::select name="display_offline_message" :label="__('com_config::com_config.display_offline_message')">
        <option value="0" @selected(config('larajoom.display_offline_message', '1') == '0')>{{ __('com_config::com_config.offline_hide') }}</option>
        <option value="1" @selected(config('larajoom.display_offline_message', '1') == '1')>{{ __('com_config::com_config.offline_custom') }}</option>
        <option value="2" @selected(config('larajoom.display_offline_message', '1') == '2')>{{ __('com_config::com_config.offline_language') }}</option>
    </x-larajoom::select>

    <x-larajoom::input name="offline_message" :label="__('com_config::com_config.offline_message')" :value="config('larajoom.offline_message', '')" size="60" />
    <x-larajoom::input name="offline_image" :label="__('com_config::com_config.offline_image')" :value="config('larajoom.offline_image', '')" size="60" />

    <x-larajoom::select name="frontediting" :label="__('com_config::com_config.frontediting')">
        <option value="0" @selected(config('larajoom.frontediting', '1') == '0')>{{ __('com_config::com_config.frontediting_none') }}</option>
        <option value="1" @selected(config('larajoom.frontediting', '1') == '1')>{{ __('com_config::com_config.frontediting_modules') }}</option>
        <option value="2" @selected(config('larajoom.frontediting', '1') == '2')>{{ __('com_config::com_config.frontediting_menus') }}</option>
    </x-larajoom::select>

    <x-larajoom::select name="editor" :label="__('com_config::com_config.editor')">
        <option value="ckeditor" @selected(config('larajoom.editor', 'tinymce') == 'ckeditor')>{{ __('com_config::com_config.editor_ckeditor') }}</option>
        <option value="tinymce" @selected(config('larajoom.editor', 'tinymce') == 'tinymce')>{{ __('com_config::com_config.editor_tinymce') }}</option>
        <option value="none" @selected(config('larajoom.editor', 'tinymce') == 'none')>{{ __('com_config::com_config.editor_none') }}</option>
    </x-larajoom::select>

    <x-larajoom::select name="captcha" :label="__('com_config::com_config.captcha')">
        <option value="0" @selected(config('larajoom.captcha', '0') == '0')>{{ __('larajoom_admin::admin.JOPTION_DO_NOT_USE') }}</option>
        <option value="recaptcha" @selected(config('larajoom.captcha', '0') == 'recaptcha')>reCAPTCHA</option>
    </x-larajoom::select>

    <x-larajoom::select name="access" :label="__('com_config::com_config.access')">
        <option value="1" @selected(config('larajoom.access', '1') == '1')>{{ __('larajoom_admin::admin.JALL') }}</option>
        <option value="2" @selected(config('larajoom.access', '1') == '2')>{{ __('larajoom_admin::admin.JREGISTERED') }}</option>
        <option value="3" @selected(config('larajoom.access', '1') == '3')>{{ __('larajoom_admin::admin.JSPECIAL') }}</option>
    </x-larajoom::select>

    <x-larajoom::select name="list_limit" :label="__('com_config::com_config.list_limit')">
        @foreach ([5 => 'J5', 10 => 'J10', 15 => 'J15', 20 => 'J20', 25 => 'J25', 30 => 'J30', 50 => 'J50', 100 => 'J100', 200 => 'J200', 500 => 'J500'] as $value => $key)
            <option value="{{ $value }}" @selected((int) config('larajoom.list_limit', 10) === $value)>{{ __('larajoom_admin::admin.' . $key) }}</option>
        @endforeach
    </x-larajoom::select>

    <x-larajoom::select name="feed_limit" :label="__('com_config::com_config.feed_limit')">
        @foreach ([5 => 'J5', 10 => 'J10', 15 => 'J15', 20 => 'J20', 25 => 'J25', 30 => 'J30', 50 => 'J50', 100 => 'J100'] as $value => $key)
            <option value="{{ $value }}" @selected((int) config('larajoom.feed_limit', 10) === $value)>{{ __('larajoom_admin::admin.' . $key) }}</option>
        @endforeach
    </x-larajoom::select>

    <x-larajoom::select name="feed_email" :label="__('com_config::com_config.feed_email')">
        <option value="author" @selected(config('larajoom.feed_email', 'none') == 'author')>{{ __('com_config::com_config.feed_email_author') }}</option>
        <option value="site" @selected(config('larajoom.feed_email', 'none') == 'site')>{{ __('com_config::com_config.feed_email_site') }}</option>
        <option value="none" @selected(config('larajoom.feed_email', 'none') == 'none')>{{ __('com_config::com_config.feed_email_none') }}</option>
    </x-larajoom::select>

    <x-larajoom::input name="cookie_domain" :label="__('com_config::com_config.cookie_domain')" :value="config('larajoom.cookie_domain', '')" size="60" />
    <x-larajoom::input name="cookie_path" :label="__('com_config::com_config.cookie_path')" :value="config('larajoom.cookie_path', '/')" size="60" />
</fieldset>

<fieldset class="options-form">
    <legend>{{ __('com_config::com_config.metadata_settings') }}</legend>

    <x-larajoom::textarea name="MetaDesc" :label="__('com_config::com_config.MetaDesc')" :value="config('larajoom.MetaDesc', '')" rows="5" />

    <x-larajoom::select name="robots" :label="__('com_config::com_config.robots')">
        <option value="index, follow" @selected(config('larajoom.robots', 'index, follow') == 'index, follow')>index, follow</option>
        <option value="noindex, follow" @selected(config('larajoom.robots', 'index, follow') == 'noindex, follow')>noindex, follow</option>
        <option value="index, nofollow" @selected(config('larajoom.robots', 'index, follow') == 'index, nofollow')>index, nofollow</option>
        <option value="noindex, nofollow" @selected(config('larajoom.robots', 'index, follow') == 'noindex, nofollow')>noindex, nofollow</option>
    </x-larajoom::select>

    <x-larajoom::input name="MetaRights" :label="__('com_config::com_config.MetaRights')" :value="config('larajoom.MetaRights', '')" size="60" />

    <x-larajoom::toggle name="MetaAuthor" :label="__('com_config::com_config.MetaAuthor')" :checked="(int) config('larajoom.MetaAuthor', 1) === 1" />
</fieldset>

<fieldset class="options-form">
    <legend>{{ __('com_config::com_config.seo_settings') }}</legend>

    <x-larajoom::toggle name="sef" :label="__('com_config::com_config.sef')" :checked="(int) config('larajoom.sef', 1) === 1" />

    <x-larajoom::toggle name="sef_rewrite" :label="__('com_config::com_config.sef_rewrite')" :checked="(int) config('larajoom.sef_rewrite', 0) === 1" />

    <x-larajoom::toggle name="sef_suffix" :label="__('com_config::com_config.sef_suffix')" :checked="(int) config('larajoom.sef_suffix', 0) === 1" />

    <x-larajoom::toggle name="unicodeslugs" :label="__('com_config::com_config.unicodeslugs')" :checked="(int) config('larajoom.unicodeslugs', 0) === 1" />

    <x-larajoom::select name="sitename_pagetitles" :label="__('com_config::com_config.sitename_pagetitles')">
        <option value="0" @selected(config('larajoom.sitename_pagetitles', '0') == '0')>{{ __('larajoom_admin::admin.JNO') }}</option>
        <option value="1" @selected(config('larajoom.sitename_pagetitles', '0') == '1')>{{ __('com_config::com_config.pagetitles_before') }}</option>
        <option value="2" @selected(config('larajoom.sitename_pagetitles', '0') == '2')>{{ __('com_config::com_config.pagetitles_after') }}</option>
    </x-larajoom::select>
</fieldset>
