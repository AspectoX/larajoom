<?php

namespace Aspectox\LaraJoom\Core\Overrides;

use Illuminate\Support\Facades\View;

class OverrideResolver
{
    protected string $basePath;
    protected string $activeTemplate = 'default';

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
    }

    public function setTemplate(string $template): self
    {
        $this->activeTemplate = $template;
        return $this;
    }

    /**
     * Resolves view with priority:
     * 1. templates/{template}/html/{extension}/{view}.blade.php
     * 2. Default extension view: {namespace}::{view}
     */
    public function resolve(string $extension, string $view, string $fallbackNamespace): string
    {
        // Check template override: templates/{activeTemplate}/html/{extension}/{view}.blade.php
        $overridePath = $this->basePath . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 
                         $this->activeTemplate . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 
                         $extension . DIRECTORY_SEPARATOR . $view . '.blade.php';

        if (file_exists($overridePath)) {
            $viewName = "larajoom_override_{$extension}_{$view}";
            if (!View::exists($viewName)) {
                View::addLocation(dirname($overridePath));
            }
            return $view;
        }

        return "{$fallbackNamespace}::{$view}";
    }
}
