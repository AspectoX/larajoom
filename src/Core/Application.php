<?php
namespace Aspectox\LaraJoom\Core;

class Application
{
    public function getIdentity()
    {
        return auth()->user();
    }

    public function getInput()
    {
        return request();
    }

    public function getLanguage()
    {
        return app()->getLocale();
    }
}
