<fieldset class="options-form">
    <legend>{{ __('com_config::com_config.cache_settings') }}</legend>

    <x-larajoom::select name="caching" :label="__('com_config::com_config.caching')">
        <option value="0" @selected(config('larajoom.caching', '2') == '0')>{{ __('com_config::com_config.caching_off') }}</option>
        <option value="1" @selected(config('larajoom.caching', '2') == '1')>{{ __('com_config::com_config.caching_conservative') }}</option>
        <option value="2" @selected(config('larajoom.caching', '2') == '2')>{{ __('com_config::com_config.caching_progressive') }}</option>
    </x-larajoom::select>

    <x-larajoom::select name="cache_handler" :label="__('com_config::com_config.cache_handler')">
        <option value="file" @selected(config('larajoom.cache_handler', 'file') == 'file')>{{ __('com_config::com_config.cache_handler_file') }}</option>
        <option value="database" @selected(config('larajoom.cache_handler', 'file') == 'database')>{{ __('com_config::com_config.cache_handler_database') }}</option>
        <option value="redis" @selected(config('larajoom.cache_handler', 'file') == 'redis')>{{ __('com_config::com_config.cache_handler_redis') }}</option>
        <option value="memcached" @selected(config('larajoom.cache_handler', 'file') == 'memcached')>{{ __('com_config::com_config.cache_handler_memcached') }}</option>
    </x-larajoom::select>

    <x-larajoom::toggle name="cache_platformprefix" :label="__('com_config::com_config.cache_platformprefix')" :checked="(int) config('larajoom.cache_platformprefix', 0) === 1" />

    <x-larajoom::input name="cachetime" type="number" :label="__('com_config::com_config.cachetime')" :value="config('larajoom.cachetime', '15')" min="1" />
    <x-larajoom::input name="cache_path" :label="__('com_config::com_config.cache_path')" :value="config('larajoom.cache_path', 'cache')" size="60" />

    <x-larajoom::toggle name="memcached_persist" :label="__('com_config::com_config.memcached_persist')" :checked="(int) config('larajoom.memcached_persist', 1) === 1" />

    <x-larajoom::toggle name="memcached_compress" :label="__('com_config::com_config.memcached_compress')" :checked="(int) config('larajoom.memcached_compress', 0) === 1" />

    <x-larajoom::input name="memcached_server_host" :label="__('com_config::com_config.memcached_server_host')" :value="config('larajoom.memcached_server_host', 'localhost')" size="60" />
    <x-larajoom::input name="memcached_server_port" type="number" :label="__('com_config::com_config.memcached_server_port')" :value="config('larajoom.memcached_server_port', '11211')" min="1" max="65535" />

    <x-larajoom::toggle name="redis_persist" :label="__('com_config::com_config.redis_persist')" :checked="(int) config('larajoom.redis_persist', 1) === 1" />

    <x-larajoom::input name="redis_server_host" :label="__('com_config::com_config.redis_server_host')" :value="config('larajoom.redis_server_host', 'localhost')" size="60" />
    <x-larajoom::input name="redis_server_port" type="number" :label="__('com_config::com_config.redis_server_port')" :value="config('larajoom.redis_server_port', '6379')" min="1" max="65535" />
    <x-larajoom::input name="redis_server_auth" type="password" :label="__('com_config::com_config.redis_server_auth')" :value="config('larajoom.redis_server_auth', '')" size="60" />
    <x-larajoom::input name="redis_server_db" type="number" :label="__('com_config::com_config.redis_server_db')" :value="config('larajoom.redis_server_db', '0')" min="0" />
</fieldset>

<fieldset class="options-form">
    <legend>{{ __('com_config::com_config.debug_settings') }}</legend>

    <x-larajoom::toggle name="debug" :label="__('com_config::com_config.debug')" :checked="(int) config('larajoom.debug', 0) === 1" />

    <x-larajoom::toggle name="debug_lang" :label="__('com_config::com_config.debug_lang')" :checked="(int) config('larajoom.debug_lang', 0) === 1" />

    <x-larajoom::toggle name="debug_lang_const" :label="__('com_config::com_config.debug_lang_const')" :checked="(int) config('larajoom.debug_lang_const', 1) === 1" />
</fieldset>

<fieldset class="options-form">
    <legend>{{ __('com_config::com_config.webservices_settings') }}</legend>

    <x-larajoom::toggle name="cors" :label="__('com_config::com_config.cors')" :checked="(int) config('larajoom.cors', 0) === 1" />

    <x-larajoom::input name="cors_allow_origin" :label="__('com_config::com_config.cors_allow_origin')" :value="config('larajoom.cors_allow_origin', '*')" size="60" />
    <x-larajoom::input name="cors_allow_headers" :label="__('com_config::com_config.cors_allow_headers')" :value="config('larajoom.cors_allow_headers', '')" size="60" />
    <x-larajoom::input name="cors_allow_methods" :label="__('com_config::com_config.cors_allow_methods')" :value="config('larajoom.cors_allow_methods', '')" size="60" />
</fieldset>

<fieldset class="options-form">
    <legend>{{ __('com_config::com_config.proxy_settings') }}</legend>

    <x-larajoom::toggle name="behind_loadbalancer" :label="__('com_config::com_config.behind_loadbalancer')" :checked="(int) config('larajoom.behind_loadbalancer', 0) === 1" />

    <x-larajoom::toggle name="proxy_enable" :label="__('com_config::com_config.proxy_enable')" :checked="(int) config('larajoom.proxy_enable', 0) === 1" />

    <x-larajoom::input name="proxy_host" :label="__('com_config::com_config.proxy_host')" :value="config('larajoom.proxy_host', '')" size="60" />
    <x-larajoom::input name="proxy_port" type="number" :label="__('com_config::com_config.proxy_port')" :value="config('larajoom.proxy_port', '8080')" min="1" max="65535" />
    <x-larajoom::input name="proxy_user" :label="__('com_config::com_config.proxy_user')" :value="config('larajoom.proxy_user', '')" size="60" />
    <x-larajoom::input name="proxy_pass" type="password" :label="__('com_config::com_config.proxy_pass')" :value="config('larajoom.proxy_pass', '')" size="60" />
</fieldset>

<fieldset class="options-form">
    <legend>{{ __('com_config::com_config.locale_settings') }}</legend>

    <x-larajoom::select name="offset" :label="__('com_config::com_config.offset')">
        @foreach (timezone_identifiers_list() as $timezone)
            <option value="{{ $timezone }}" @selected(config('larajoom.offset', 'UTC') === $timezone)>{{ $timezone }}</option>
        @endforeach
    </x-larajoom::select>
</fieldset>

<fieldset class="options-form">
    <legend>{{ __('com_config::com_config.mail_settings') }}</legend>

    <x-larajoom::toggle name="mailonline" :label="__('com_config::com_config.mailonline')" :checked="(int) config('larajoom.mailonline', 1) === 1" />

    <x-larajoom::toggle name="massmailoff" :label="__('com_config::com_config.massmailoff')" :checked="(int) config('larajoom.massmailoff', 0) === 1" />

    <x-larajoom::input name="mailfrom" type="email" :label="__('com_config::com_config.mailfrom')" :value="config('larajoom.mailfrom', '')" size="60" />
    <x-larajoom::input name="fromname" :label="__('com_config::com_config.fromname')" :value="config('larajoom.fromname', '')" size="60" />
    <x-larajoom::input name="replyto" type="email" :label="__('com_config::com_config.replyto')" :value="config('larajoom.replyto', '')" size="60" />
    <x-larajoom::input name="replytoname" :label="__('com_config::com_config.replytoname')" :value="config('larajoom.replytoname', '')" size="60" />

    @php
        $mailer = \Aspectox\LaraJoom\Core\Settings\FrameworkConfig::normalizeMailer();
    @endphp

    <x-larajoom::select name="mailer" :label="__('com_config::com_config.mailer')">
        <option value="smtp" @selected($mailer === 'smtp')>{{ __('com_config::com_config.mailer_smtp') }}</option>
        <option value="sendmail" @selected($mailer === 'sendmail')>{{ __('com_config::com_config.mailer_sendmail') }}</option>
        <option value="log" @selected($mailer === 'log')>{{ __('com_config::com_config.mailer_log') }}</option>
        <option value="array" @selected($mailer === 'array')>{{ __('com_config::com_config.mailer_array') }}</option>
        <option value="failover" @selected($mailer === 'failover')>{{ __('com_config::com_config.mailer_failover') }}</option>
    </x-larajoom::select>

    <x-larajoom::input name="sendmail" :label="__('com_config::com_config.sendmail')" :value="config('larajoom.sendmail', '/usr/sbin/sendmail')" size="60" />
    <x-larajoom::input name="smtphost" :label="__('com_config::com_config.smtphost')" :value="config('larajoom.smtphost', 'localhost')" size="60" />
    <x-larajoom::input name="smtpport" type="number" :label="__('com_config::com_config.smtpport')" :value="config('larajoom.smtpport', '25')" min="1" max="65535" />

    <x-larajoom::select name="smtpsecure" :label="__('com_config::com_config.smtpsecure')">
        <option value="none" @selected(config('larajoom.smtpsecure', 'none') == 'none')>{{ __('com_config::com_config.smtpsecure_none') }}</option>
        <option value="ssl" @selected(config('larajoom.smtpsecure', 'none') == 'ssl')>{{ __('com_config::com_config.smtpsecure_ssl') }}</option>
        <option value="tls" @selected(config('larajoom.smtpsecure', 'none') == 'tls')>{{ __('com_config::com_config.smtpsecure_tls') }}</option>
    </x-larajoom::select>

    <x-larajoom::toggle name="smtpauth" :label="__('com_config::com_config.smtpauth')" :checked="(int) config('larajoom.smtpauth', 0) === 1" />

    <x-larajoom::input name="smtpuser" :label="__('com_config::com_config.smtpuser')" :value="config('larajoom.smtpuser', '')" size="60" />
    <x-larajoom::input name="smtppass" type="password" :label="__('com_config::com_config.smtppass')" :value="config('larajoom.smtppass', '')" size="60" />
</fieldset>
