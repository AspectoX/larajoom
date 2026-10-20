<?php
namespace Aspectox\LaraJoom\Admin\Components\com_cpanel\Controllers;

use Illuminate\Http\Request;

class CpanelController
{
    public function index(Request $request)
    {
        return '<h1>Admin LaraJoom - Cpanel</h1><p>Logueado: ' . (auth('ax-larajoom')->check() ? auth('ax-larajoom')->user()->email : 'no') . '</p><a href="/">Ir al site</a>';
    }
}
