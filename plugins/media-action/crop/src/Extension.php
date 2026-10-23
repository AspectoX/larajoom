<?php

namespace Aspectox\LaraJoom\Plugins\MediaAction\Crop;

use Aspectox\LaraJoom\Core\Plugins\MediaActionPlugin;

class Extension extends MediaActionPlugin
{
    public function getActionName(): string
    {
        return 'crop';
    }

    public function getLabel(): string
    {
        return 'plg_media_action_crop.label';
    }

    public function getIcon(): string
    {
        return 'crop';
    }

    public function getServiceClass(): string
    {
        return \Aspectox\LaraJoom\Plugins\MediaAction\Crop\Services\CropService::class;
    }
}