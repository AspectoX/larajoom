<?php

namespace Aspectox\LaraJoom\Plugins\MediaAction\Rotate;

use Aspectox\LaraJoom\Core\Plugins\MediaActionPlugin;

class Extension extends MediaActionPlugin
{
    public function getActionName(): string
    {
        return 'rotate';
    }

    public function getLabel(): string
    {
        return 'plg_media_action_rotate.label';
    }

    public function getIcon(): string
    {
        return 'rotate-left';
    }

    public function getServiceClass(): string
    {
        return \Aspectox\LaraJoom\Plugins\MediaAction\Rotate\Services\RotateService::class;
    }
}