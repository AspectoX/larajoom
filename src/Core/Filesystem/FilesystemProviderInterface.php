<?php

namespace Aspectox\LaraJoom\Core\Filesystem;

interface FilesystemProviderInterface
{
    public function getID(): string;
    
    public function getDisplayName(): string;
    
    public function getAdapterName(): string;
    
    public function getAdapterConfig(): array;
    
    public function isEnabled(): bool;
    
    public function getCapabilities(): array;
}