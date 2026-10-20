<?php

namespace Aspectox\LaraJoom\Admin\Components\com_installer\Controllers;

use Aspectox\LaraJoom\Admin\Components\com_installer\Models\Extension;
use Aspectox\LaraJoom\Admin\Components\com_installer\Services\PackageInstaller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class InstallerController extends Controller
{
    /**
     * Gestión de extensiones (auto-registro de lo que ya está en disco).
     */
    public function index(Request $request)
    {
        $limit = config('larajoom.list_limit', 10);
        $installer = new PackageInstaller();
        $registered = null;
        if (DB::getSchemaBuilder()->hasTable('larajoom_extensions')) {
            $registered = $installer->syncFromDisk();
        }

        $type = $request->input('type');
        $search = $request->input('search');

        $query = Extension::query()->whereNotIn('type', ['plugin'])->orderBy('type')->orderBy('element');
        if ($type) {
            $query->where('type', $type);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('element', 'like', '%' . $search . '%');
            });
        }

        $extensions = $query->paginate($limit);

        return view('admin.components.com_installer::manage.index', [
            'extensions'  => $extensions,
            'registered'  => $registered,
            'filterType'  => $type,
            'search'      => $search,
        ]);
    }

    /**
     * Formulario de instalación (tabs: upload / url / folder).
     */
    public function create()
    {
        return view('admin.components.com_installer::install.index');
    }

    /**
     * Procesa la instalación según installtype: upload | url | folder.
     */
    public function store(Request $request)
    {
        $installer = new PackageInstaller();

        try {
            switch ($request->input('installtype')) {
                case 'url':
                    $message = $installer->installFromUrl((string) $request->input('install_url'));
                    break;

                case 'folder':
                    $path = trim((string) $request->input('install_directory'));
                    if ($path === '' || !is_dir($path)) {
                        throw new \RuntimeException('La carpeta indicada no existe.');
                    }
                    $message = $installer->installFromDirectory(rtrim($path, '/\\'));
                    break;

                case 'upload':
                default:
                    $request->validate([
                        'install_package' => 'required|file|mimes:zip',
                    ]);
                    $message = $installer->installFromUpload($request->file('install_package'));
                    break;
            }

            return redirect()
                ->route('larajoom.admin.installer.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()
                ->route('larajoom.admin.installer.create')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Activa / desactiva una extensión.
     */
    public function toggle($id)
    {
        $extension = Extension::findOrFail($id);

        if ($extension->protected) {
            return redirect()
                ->route('larajoom.admin.installer.index')
                ->with('error', 'Las extensiones del núcleo no se pueden desactivar.');
        }

        $extension->enabled = !$extension->enabled;
        $extension->save();

        return redirect()
            ->route('larajoom.admin.installer.index')
            ->with('success', $extension->enabled ? 'Extensión activada.' : 'Extensión desactivada.');
    }

    /**
     * Desinstala una extensión (elimina el registro; archivos conservados).
     */
    public function destroy($id)
    {
        $installer = new PackageInstaller();

        try {
            $message = $installer->uninstall(Extension::findOrFail($id));

            return redirect()
                ->route('larajoom.admin.installer.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()
                ->route('larajoom.admin.installer.index')
                ->with('error', $e->getMessage());
        }
    }
}
