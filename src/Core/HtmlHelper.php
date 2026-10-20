<?php
namespace Aspectox\LaraJoom\Core;

class HtmlHelper
{
    public static function image($src, $alt = '', $attrs = [], $pathOnly = false, $relative = 0)
    {
        $attrStr = '';
        foreach ($attrs as $k => $v) {
            $attrStr .= ' ' . $k . '="' . $v . '"';
        }
        return '<img src="' . $src . '" alt="' . $alt . '"' . $attrStr . '>';
    }
}
