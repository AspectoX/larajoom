<?php
namespace Aspectox\LaraJoom\Core\Middleware;

use Aspectox\LaraJoom\Core\Access;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ACL de las rutas del panel.
 *
 * 1. Solo los usuarios autenticados con un grupo de perfil administrativo
 *    (Author, Editor, Publisher, Manager, Administrator, SuperAdmin) entran al panel.
 * 2. Se deriva el asset (componente) y la acción (core.manage, core.create,
 *    core.edit, core.delete) de la ruta y se valida contra `larajoom_assets`.
 *
 * Niveles: 1 = leer, 2 = leer y escribir, 3 = leer, escribir y borrar.
 */
class LarajoomAcl
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('ax-larajoom')->check()) {
            return redirect()->route('login');
        }

        // Sin un grupo con perfil administrativo no se entra al panel
        if (!Access::canAccessAdmin()) {
            abort(403, 'Tu usuario no tiene un rol con acceso al panel de administración.');
        }

        $route = $request->route();

        if (!$route) {
            return $next($request);
        }

        $asset = $this->assetFor($request);
        $action = Access::actionForRouteName((string) $route->getName());

        // Sin asset reconocible (p.ej. rutas del core) se deja pasar
        if ($asset === null) {
            return $next($request);
        }

        if (!Access::can($asset, $action)) {
            abort(403, 'No tenés permisos para esta acción (' . $action . ' sobre ' . $asset . ').');
        }

        return $next($request);
    }

    /**
     * Asset de la ruta: se deduce del controlador o del enlace.
     */
    protected function assetFor(Request $request): ?string
    {
        $route = $request->route();

        $controller = $route->getController();

        if ($controller) {
            $asset = Access::assetForController($controller::class);

            if ($asset) {
                return $asset;
            }
        }

        $uri = $route->uri();

        // Rutas del panel: administrator/{componente}/...
        if (preg_match('#^administrator/([a-z0-9_]+)#i', $uri, $matches)) {
            $segment = $matches[1];

            return str_starts_with($segment, 'com_') ? $segment : 'com_' . $segment;
        }

        return null;
    }
}
