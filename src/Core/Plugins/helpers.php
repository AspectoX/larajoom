<?php

if (!function_exists('larajoom_plugins')) {
    function larajoom_plugins(): \Aspectox\LaraJoom\Core\Plugins\PluginManager
    {
        return app(\Aspectox\LaraJoom\Core\Plugins\PluginManager::class);
    }
}
