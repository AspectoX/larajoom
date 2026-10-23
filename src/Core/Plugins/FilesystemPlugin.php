<?php

namespace Aspectox\LaraJoom\Core\Plugins;

use Aspectox\LaraJoom\Core\Filesystem\FilesystemProviderInterface;

abstract class FilesystemPlugin
{
    abstract public function getProviderClass(): string;

    public function isEnabled(): bool
    {
        return true;
    }

    public function getProvider(): ?FilesystemProviderInterface
    {
        $class = $this->getProviderClass();
        if (class_exists($class)) {
            return new $class();
        }
        return null;
    }
}