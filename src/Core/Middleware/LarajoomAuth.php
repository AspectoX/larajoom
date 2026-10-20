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
            return redirect()->route('login');
        }

        return $next($request);
    }
}
