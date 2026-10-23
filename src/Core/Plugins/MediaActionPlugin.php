<?php

namespace Aspectox\LaraJoom\Core\Plugins;

use Aspectox\LaraJoom\Core\MediaAction\MediaActionServiceInterface;

abstract class MediaActionPlugin
{
    abstract public function getActionName(): string;
    
    abstract public function getLabel(): string;
    
    abstract public function getIcon(): string;
    
    abstract public function getServiceClass(): string;

    public function isEnabled(): bool
    {
        return true;
    }

    public function getService(): ?MediaActionServiceInterface
    {
        $class = $this->getServiceClass();
        if (class_exists($class)) {
            return new $class();
        }
        return null;
    }
}