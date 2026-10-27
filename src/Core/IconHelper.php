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

if (!function_exists('larajoom_icon_toggle')) {
    function larajoom_icon_toggle(string $iconGrid, string $iconList): string
    {
        $grid = larajoom_icon($iconGrid);
        $list = larajoom_icon($iconList);

        $grid = str_replace('<svg', '<svg x-show="!listView"', $grid);
        $list = str_replace('<svg', '<svg x-show="listView"', $list);

        return $grid . $list;
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
            $icons = \aspectox\AxAdminLara\Support\getIcons();
            ksort($icons);
            return $icons;
        }
        return [];
    }
}
