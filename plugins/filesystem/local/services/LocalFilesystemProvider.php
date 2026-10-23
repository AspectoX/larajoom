<?php

namespace Aspectox\LaraJoom\Plugins\Filesystem\Local\Services;

use Aspectox\LaraJoom\Core\Filesystem\FilesystemProviderInterface;
use Illuminate\Support\Facades\Storage;

class LocalFilesystemProvider implements FilesystemProviderInterface
{
    public function getID(): string
    {
        return 'local';
    }

    public function getDisplayName(): string
    {
        return 'Local Filesystem';
    }

    public function getAdapterName(): string
    {
        return 'local';
    }

    public function getAdapterConfig(): array
    {
        return [
            'driver' => 'local',
            'root' => Storage::disk('public')->getDriver()->getAdapter()->getPathPrefix(),
        ];
    }

    public function isEnabled(): bool
    {
        return true;
    }

    public function getCapabilities(): array
    {
        return [
            'create_folder' => true,
            'delete' => true,
            'rename' => true,
            'upload' => true,
            'download' => true,
            'preview' => true,
            'move' => false,
            'copy' => false,
        ];
    }
}