<?php
namespace Aspectox\LaraJoom\Core;

class Session
{
    public static function get($key, $default = null)
    {
        return session($key, $default);
    }

    public static function put($key, $value)
    {
        session([$key => $value]);
    }

    public static function forget($key)
    {
        session()->forget($key);
    }
}
