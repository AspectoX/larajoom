<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaraJoomConfigSeeder extends Seeder
{
    public function run(): void
    {
        $fields = [
            // -------------------------------------------------- Site
            ['name' => 'sitename', 'value' => 'LaraJoom', 'group' => 'site'],
            ['name' => 'offline', 'value' => '0', 'group' => 'site'],
            ['name' => 'display_offline_message', 'value' => '1', 'group' => 'site'],
            ['name' => 'offline_message', 'value' => '', 'group' => 'site'],
            ['name' => 'offline_image', 'value' => '', 'group' => 'site'],
            ['name' => 'frontediting', 'value' => '1', 'group' => 'site'],
            ['name' => 'editor', 'value' => 'tinymce', 'group' => 'site'],
            ['name' => 'captcha', 'value' => '0', 'group' => 'site'],
            ['name' => 'access', 'value' => '1', 'group' => 'site'],
            ['name' => 'list_limit', 'value' => '10', 'group' => 'site'],
            ['name' => 'feed_limit', 'value' => '10', 'group' => 'site'],
            ['name' => 'feed_email', 'value' => 'none', 'group' => 'site'],
            ['name' => 'cookie_domain', 'value' => '', 'group' => 'site'],
            ['name' => 'cookie_path', 'value' => '/', 'group' => 'site'],

            // -------------------------------------------------- Metadata
            ['name' => 'MetaDesc', 'value' => '', 'group' => 'metadata'],
            ['name' => 'robots', 'value' => 'index, follow', 'group' => 'metadata'],
            ['name' => 'MetaRights', 'value' => '', 'group' => 'metadata'],
            ['name' => 'MetaAuthor', 'value' => '1', 'group' => 'metadata'],

            // -------------------------------------------------- SEO
            ['name' => 'sef', 'value' => '1', 'group' => 'seo'],
            ['name' => 'sef_rewrite', 'value' => '0', 'group' => 'seo'],
            ['name' => 'sef_suffix', 'value' => '0', 'group' => 'seo'],
            ['name' => 'unicodeslugs', 'value' => '0', 'group' => 'seo'],
            ['name' => 'sitename_pagetitles', 'value' => '0', 'group' => 'seo'],

            // -------------------------------------------------- Cache
            ['name' => 'caching', 'value' => '2', 'group' => 'cache'],
            ['name' => 'cache_handler', 'value' => '', 'group' => 'cache'],
            ['name' => 'cache_platformprefix', 'value' => '0', 'group' => 'cache'],
            ['name' => 'cachetime', 'value' => '15', 'group' => 'cache'],
            ['name' => 'cache_path', 'value' => 'cache', 'group' => 'cache'],
            ['name' => 'memcached_persist', 'value' => '1', 'group' => 'cache'],
            ['name' => 'memcached_compress', 'value' => '0', 'group' => 'cache'],
            ['name' => 'memcached_server_host', 'value' => 'localhost', 'group' => 'cache'],
            ['name' => 'memcached_server_port', 'value' => '11211', 'group' => 'cache'],
            ['name' => 'redis_persist', 'value' => '1', 'group' => 'cache'],
            ['name' => 'redis_server_host', 'value' => 'localhost', 'group' => 'cache'],
            ['name' => 'redis_server_port', 'value' => '6379', 'group' => 'cache'],
            ['name' => 'redis_server_auth', 'value' => '', 'group' => 'cache'],
            ['name' => 'redis_server_db', 'value' => '0', 'group' => 'cache'],

            // -------------------------------------------------- Debug
            ['name' => 'debug', 'value' => '0', 'group' => 'debug'],
            ['name' => 'debug_lang', 'value' => '0', 'group' => 'debug'],
            ['name' => 'debug_lang_const', 'value' => '1', 'group' => 'debug'],

            // -------------------------------------------------- Webservices (CORS)
            ['name' => 'cors', 'value' => '0', 'group' => 'webservices'],
            ['name' => 'cors_allow_origin', 'value' => '*', 'group' => 'webservices'],
            ['name' => 'cors_allow_headers', 'value' => 'Content-Type,X-LaraJoom-Token,Authorization', 'group' => 'webservices'],
            ['name' => 'cors_allow_methods', 'value' => 'GET,POST,PUT,DELETE,PATCH', 'group' => 'webservices'],

            // -------------------------------------------------- Proxy
            ['name' => 'behind_loadbalancer', 'value' => '0', 'group' => 'proxy'],
            ['name' => 'proxy_enable', 'value' => '0', 'group' => 'proxy'],
            ['name' => 'proxy_host', 'value' => '', 'group' => 'proxy'],
            ['name' => 'proxy_port', 'value' => '8080', 'group' => 'proxy'],
            ['name' => 'proxy_user', 'value' => '', 'group' => 'proxy'],
            ['name' => 'proxy_pass', 'value' => '', 'group' => 'proxy'],

            // -------------------------------------------------- Locale
            ['name' => 'offset', 'value' => 'UTC', 'group' => 'locale'],

            // -------------------------------------------------- Mail
            ['name' => 'mailonline', 'value' => '1', 'group' => 'mail'],
            ['name' => 'massmailoff', 'value' => '0', 'group' => 'mail'],
            ['name' => 'mailfrom', 'value' => '', 'group' => 'mail'],
            ['name' => 'fromname', 'value' => '', 'group' => 'mail'],
            ['name' => 'replyto', 'value' => '', 'group' => 'mail'],
            ['name' => 'replytoname', 'value' => '', 'group' => 'mail'],
            ['name' => 'mailer', 'value' => '', 'group' => 'mail'],
            ['name' => 'sendmail', 'value' => '/usr/sbin/sendmail', 'group' => 'mail'],
            ['name' => 'smtphost', 'value' => 'localhost', 'group' => 'mail'],
            ['name' => 'smtpport', 'value' => '25', 'group' => 'mail'],
            ['name' => 'smtpsecure', 'value' => 'none', 'group' => 'mail'],
            ['name' => 'smtpauth', 'value' => '0', 'group' => 'mail'],
            ['name' => 'smtpuser', 'value' => '', 'group' => 'mail'],
            ['name' => 'smtppass', 'value' => '', 'group' => 'mail'],

            // -------------------------------------------------- Server
            ['name' => 'tmp_path', 'value' => '', 'group' => 'server'],
            ['name' => 'gzip', 'value' => '0', 'group' => 'server'],
            ['name' => 'error_reporting', 'value' => 'default', 'group' => 'server'],
            ['name' => 'force_ssl', 'value' => '-1', 'group' => 'server'],

            // -------------------------------------------------- Session
            ['name' => 'session_handler', 'value' => '', 'group' => 'session'],
            ['name' => 'session_filesystem_path', 'value' => '', 'group' => 'session'],
            ['name' => 'session_memcached_server_host', 'value' => 'localhost', 'group' => 'session'],
            ['name' => 'session_memcached_server_port', 'value' => '11211', 'group' => 'session'],
            ['name' => 'session_redis_persist', 'value' => '1', 'group' => 'session'],
            ['name' => 'session_redis_server_host', 'value' => 'localhost', 'group' => 'session'],
            ['name' => 'session_redis_server_port', 'value' => '6379', 'group' => 'session'],
            ['name' => 'session_redis_server_auth', 'value' => '', 'group' => 'session'],
            ['name' => 'session_redis_server_db', 'value' => '0', 'group' => 'session'],
            ['name' => 'lifetime', 'value' => '15', 'group' => 'session'],
            ['name' => 'shared_session', 'value' => '0', 'group' => 'session'],
            ['name' => 'session_metadata', 'value' => '1', 'group' => 'session'],
            ['name' => 'session_metadata_for_guest', 'value' => '1', 'group' => 'session'],

            // -------------------------------------------------- Logging
            ['name' => 'log_path', 'value' => '', 'group' => 'logging'],
            ['name' => 'log_everything', 'value' => '0', 'group' => 'logging'],
            ['name' => 'log_deprecated', 'value' => '0', 'group' => 'logging'],
            ['name' => 'log_priorities', 'value' => 'all', 'group' => 'logging'],
            ['name' => 'log_categories', 'value' => '', 'group' => 'logging'],
            ['name' => 'log_category_mode', 'value' => '0', 'group' => 'logging'],

            // -------------------------------------------------- Filtros de texto
            ['name' => 'filters', 'value' => '', 'group' => 'filters'],

            // -------------------------------------------------- Sistema / DB
            ['name' => 'dbtype', 'value' => 'mysqli', 'group' => 'system'],
            ['name' => 'dbprefix', 'value' => '', 'group' => 'system'],
            ['name' => 'log_channel', 'value' => 'larajoom', 'group' => 'system'],
            ['name' => 'timezone', 'value' => 'UTC', 'group' => 'system'],
        ];

        foreach ($fields as $row) {
            DB::table('larajoom_configuration')->updateOrInsert(
                ['name' => $row['name']],
                [
                    'value'      => $row['value'],
                    'group'      => $row['group'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->normalizeLaravelValues();
    }

    /**
     * Normaliza los valores que usaban nomenclatura de Joomla a los de Laravel.
     *
     * - session_handler: 'filesystem' → 'file'
     * - mailer:         'mail'        → 'smtp'
     *
     * Sin esto, al aplicar esos ajustes al config() de Laravel la app lanza
     * InvalidArgumentException (driver/mailer inexistente) en cada request.
     */
    protected function normalizeLaravelValues(): void
    {
        if (!Schema::hasTable('larajoom_configuration')) {
            return;
        }

        DB::table('larajoom_configuration')
            ->where('name', 'session_handler')
            ->where('value', 'filesystem')
            ->update(['value' => 'file', 'updated_at' => now()]);

        DB::table('larajoom_configuration')
            ->where('name', 'mailer')
            ->where('value', 'mail')
            ->update(['value' => 'smtp', 'updated_at' => now()]);
    }
}
