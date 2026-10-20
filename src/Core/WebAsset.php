<?php
namespace Aspectox\LaraJoom\Core;

class WebAssetManager
{
    public function usePreset($name)
    {
        return $this;
    }

    public function useStyle($name)
    {
        return $this;
    }

    public function addInlineStyle($css)
    {
        return $this;
    }

    public function disableScript($name)
    {
        return $this;
    }

    public function registerStyle($name, $url = '', $deps = [], $media = [], $priority = [])
    {
        return $this;
    }
}
