<?php
namespace Aspectox\LaraJoom\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LarajoomAcl
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('ax-larajoom')->check()) {
            return redirect()->route('login');
        }
        // ACL tipo Joomla 6: verificar acción sobre asset del componente
        $user = auth()->user();
        $action = 'core.manage'; // o derivar de ruta / componente
        // Aquí se integraría con Joomla\CMS\Access o larajoom_assets
        return $next($request);
    }
}
