<fieldset class="options-form">
    <legend>{{ __('com_config::com_config.server_settings') }}</legend>

    <x-larajoom::input name="tmp_path" :label="__('com_config::com_config.tmp_path')" :value="config('larajoom.tmp_path', '')" size="60" />

    <x-larajoom::toggle name="gzip" :label="__('com_config::com_config.gzip')" :checked="(int) config('larajoom.gzip', 0) === 1" />

    <x-larajoom::select name="error_reporting" :label="__('com_config::com_config.error_reporting')">
        <option value="default" @selected(config('larajoom.error_reporting', 'default') == 'default')>{{ __('com_config::com_config.error_reporting_default') }}</option>
        <option value="none" @selected(config('larajoom.error_reporting', 'default') == 'none')>{{ __('com_config::com_config.error_reporting_none') }}</option>
        <option value="simple" @selected(config('larajoom.error_reporting', 'default') == 'simple')>{{ __('com_config::com_config.error_reporting_simple') }}</option>
        <option value="maximum" @selected(config('larajoom.error_reporting', 'default') == 'maximum')>{{ __('com_config::com_config.error_reporting_maximum') }}</option>
    </x-larajoom::select>

    <x-larajoom::select name="force_ssl" :label="__('com_config::com_config.force_ssl')">
        <option value="-1" @selected(config('larajoom.force_ssl', '-1') == '-1')>{{ __('larajoom_admin::admin.JNO') }}</option>
        <option value="0" @selected(config('larajoom.force_ssl', '-1') == '0')>{{ __('com_config::com_config.ssl_none') }}</option>
        <option value="1" @selected(config('larajoom.force_ssl', '-1') == '1')>{{ __('com_config::com_config.ssl_admin') }}</option>
        <option value="2" @selected(config('larajoom.force_ssl', '-1') == '2')>{{ __('com_config::com_config.ssl_site') }}</option>
    </x-larajoom::select>
</fieldset>

<fieldset class="options-form">
    <legend>{{ __('com_config::com_config.session_settings') }}</legend>

    <x-larajoom::select name="session_handler" :label="__('com_config::com_config.session_handler')">
        <option value="filesystem" @selected(config('larajoom.session_handler', 'filesystem') == 'filesystem')>{{ __('com_config::com_config.session_handler_file') }}</option>
        <option value="database" @selected(config('larajoom.session_handler', 'filesystem') == 'database')>{{ __('com_config::com_config.session_handler_database') }}</option>
        <option value="redis" @selected(config('larajoom.session_handler', 'filesystem') == 'redis')>{{ __('com_config::com_config.session_handler_redis') }}</option>
        <option value="memcached" @selected(config('larajoom.session_handler', 'filesystem') == 'memcached')>{{ __('com_config::com_config.session_handler_memcached') }}</option>
    </x-larajoom::select>

    <x-larajoom::input name="session_filesystem_path" :label="__('com_config::com_config.session_filesystem_path')" :value="config('larajoom.session_filesystem_path', '')" size="60" />
    <x-larajoom::input name="session_memcached_server_host" :label="__('com_config::com_config.session_memcached_server_host')" :value="config('larajoom.session_memcached_server_host', 'localhost')" size="60" />
    <x-larajoom::input name="session_memcached_server_port" type="number" :label="__('com_config::com_config.session_memcached_server_port')" :value="config('larajoom.session_memcached_server_port', '11211')" min="1" max="65535" />

    <x-larajoom::toggle name="session_redis_persist" :label="__('com_config::com_config.session_redis_persist')" :checked="(int) config('larajoom.session_redis_persist', 1) === 1" />

    <x-larajoom::input name="session_redis_server_host" :label="__('com_config::com_config.session_redis_server_host')" :value="config('larajoom.session_redis_server_host', 'localhost')" size="60" />
    <x-larajoom::input name="session_redis_server_port" type="number" :label="__('com_config::com_config.session_redis_server_port')" :value="config('larajoom.session_redis_server_port', '6379')" min="1" max="65535" />
    <x-larajoom::input name="session_redis_server_auth" type="password" :label="__('com_config::com_config.session_redis_server_auth')" :value="config('larajoom.session_redis_server_auth', '')" size="60" />
    <x-larajoom::input name="session_redis_server_db" type="number" :label="__('com_config::com_config.session_redis_server_db')" :value="config('larajoom.session_redis_server_db', '0')" min="0" />

    <x-larajoom::input name="lifetime" type="number" :label="__('com_config::com_config.lifetime')" :value="config('larajoom.lifetime', '15')" min="1" max="16383" />

    <x-larajoom::toggle name="shared_session" :label="__('com_config::com_config.shared_session')" :checked="(int) config('larajoom.shared_session', 0) === 1" />

    <x-larajoom::toggle name="session_metadata" :label="__('com_config::com_config.session_metadata')" :checked="(int) config('larajoom.session_metadata', 1) === 1" />

    <x-larajoom::toggle name="session_metadata_for_guest" :label="__('com_config::com_config.session_metadata_for_guest')" :checked="(int) config('larajoom.session_metadata_for_guest', 1) === 1" />
</fieldset>
