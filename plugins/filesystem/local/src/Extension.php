<?php

namespace Aspectox\LaraJoom\Plugins\Filesystem\Local;

use Aspectox\LaraJoom\Core\Plugins\FilesystemPlugin;

class Extension extends FilesystemPlugin
{
    public function getProviderClass(): string
    {
        return \Aspectox\LaraJoom\Plugins\Filesystem\Local\Services\LocalFilesystemProvider::class;
    }
}