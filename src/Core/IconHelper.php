<?php

if (!function_exists('larajoom_icon')) {
    function larajoom_icon(string $name): string
    {
        $iconsPath = __DIR__ . DIRECTORY_SEPARATOR . 'icons.php';
        if (!file_exists($iconsPath)) {
            return '<span aria-label="' . htmlspecialchars($name) . '">' . htmlspecialchars($name) . '</span>';
        }
        require_once $iconsPath;
        if (function_exists('aspectox\AxAdminLara\Support\getIcon')) {
            return \aspectox\AxAdminLara\Support\getIcon($name);
        }
        return '<span aria-label="' . htmlspecialchars($name) . '">' . htmlspecialchars($name) . '</span>';
    }
}

if (!function_exists('larajoom_icons')) {
    function larajoom_icons(): array
    {
        $iconsPath = __DIR__ . DIRECTORY_SEPARATOR . 'icons.php';
        if (!file_exists($iconsPath)) {
            return [];
        }
        require_once $iconsPath;
        if (function_exists('aspectox\AxAdminLara\Support\getIcons')) {
            return \aspectox\AxAdminLara\Support\getIcons();
        }
        return [];
    }
}
