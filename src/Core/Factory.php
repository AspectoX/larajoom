<?php
namespace Aspectox\LaraJoom\Core;

class Factory
{
    public static function getApplication()
    {
        return app();
    }

    public static function getDocument()
    {
        return new HtmlDocument();
    }

    public static function getUser()
    {
        return auth()->user();
    }

    public static function getLanguage()
    {
        return app()->getLocale();
    }
}
