<?php

namespace Aspectox\LaraJoom\Plugins\MediaAction\Resize;

use Aspectox\LaraJoom\Core\Plugins\MediaActionPlugin;

class Extension extends MediaActionPlugin
{
    public function getActionName(): string
    {
        return 'resize';
    }

    public function getLabel(): string
    {
        return 'plg_media_action_resize.label';
    }

    public function getIcon(): string
    {
        return 'resize';
    }

    public function getServiceClass(): string
    {
        return \Aspectox\LaraJoom\Plugins\MediaAction\Resize\Services\ResizeService::class;
    }
}