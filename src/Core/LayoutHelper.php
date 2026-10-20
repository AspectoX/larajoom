<?php
namespace Aspectox\LaraJoom\Core;

class LayoutHelper
{
    public static function render($name, $data = [], $namespace = 'admin_tpl')
    {
        $viewKey = $namespace . '::layouts.' . $name;
        if (view()->exists($viewKey)) {
            return view($viewKey, ['displayData' => $data])->render();
        }
        // Fallback a site y genérico para cualquier template
        foreach (['site_tpl', 'tpl'] as $ns) {
            $alt = $ns . '::layouts.' . $name;
            if (view()->exists($alt)) {
                return view($alt, ['displayData' => $data])->render();
            }
        }
        return '';
    }
}
