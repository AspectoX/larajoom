<?php
namespace Aspectox\LaraJoom\Core\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LarajoomAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('ax-larajoom')->check()) {
            // Guarda la URL actual como "intended": tras loguearse se vuelve a la última página
            return redirect()->guest(route('login'));
        }

        return $next($request);
    }
}
