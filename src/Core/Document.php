<?php
namespace Aspectox\LaraJoom\Core;

class HtmlDocument
{
    public $language = 'es';
    public $direction = 'ltr';
    public $params = [];
    public $error = null;
    public $debug = false;

    public function getWebAssetManager()
    {
        return new WebAssetManager();
    }

    public function addHeadLink($url, $rel, $type = 'rel', $attrs = [])
    {
        return '<link rel="' . $rel . '" href="' . $url . '"' . $this->attrString($attrs) . '>';
    }

    public function setMetaData($name, $content)
    {
        return '<meta name="' . $name . '" content="' . $content . '">';
    }

    private function attrString($attrs)
    {
        $s = '';
        foreach ($attrs as $k => $v) {
            $s .= ' ' . $k . '="' . $v . '"';
        }
        return $s;
    }

    public function renderBacktrace()
    {
        return '<pre>Backtrace not implemented</pre>';
    }
}
