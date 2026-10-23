<?php

namespace Aspectox\LaraJoom\Core\Filesystem;

use Aspectox\LaraJoom\Core\Plugins\PluginManager;

class ProviderManager
{
    protected PluginManager $pluginManager;
    protected array $providers = [];

    public function __construct(PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    public function getProviders(): array
    {
        if (!empty($this->providers)) {
            return $this->providers;
        }

        $plugins = $this->pluginManager->getPluginsByGroup('filesystem');
        
        foreach ($plugins as $plugin) {
            if ($plugin->isEnabled()) {
                $providerClass = $plugin->getProviderClass();
                if ($providerClass && class_exists($providerClass)) {
                    $provider = new $providerClass();
                    if ($provider instanceof FilesystemProviderInterface && $provider->isEnabled()) {
                        $this->providers[$provider->getID()] = $provider;
                    }
                }
            }
        }

        // Fallback: siempre incluir local si no hay ninguno
        if (empty($this->providers)) {
            $this->providers['local'] = new \Aspectox\LaraJoom\Plugins\Filesystem\Local\Services\LocalFilesystemProvider();
        }

        return $this->providers;
    }

    public function getProvider(string $id): ?FilesystemProviderInterface
    {
        $providers = $this->getProviders();
        return $providers[$id] ?? null;
    }

    public function getDefaultProvider(): FilesystemProviderInterface
    {
        $providers = $this->getProviders();
        return $providers['local'] ?? reset($providers);
    }
}