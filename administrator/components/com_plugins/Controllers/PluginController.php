<?php

namespace Aspectox\LaraJoom\Admin\Components\com_plugins\Controllers;

use Aspectox\LaraJoom\Admin\Components\com_installer\Services\PackageInstaller;
use Aspectox\LaraJoom\Admin\Components\com_plugins\Models\Plugin;
use Aspectox\LaraJoom\Admin\Components\com_plugins\Services\PluginParams;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class PluginController extends Controller
{
    /**
     * Listado de plugins desde la DB (solo los registrados).
     * Para sumar plugins del disco: botón "Detectar plugins".
     */
    public function index(Request $request)
    {
        $folder = $request->input('folder');

        $query = Plugin::plugins()->orderBy('folder')->orderBy('ordering')->orderBy('element');
        if ($folder) {
            $query->where('folder', $folder);
        }

        $groups = Plugin::plugins()->select('folder')->distinct()->orderBy('folder')->pluck('folder');

        return view('admin.components.com_plugins::plugins.index', [
            'plugins' => $query->get(),
            'groups'  => $groups,
            'folder'  => $folder,
        ]);
    }

    /**
     * Detecta plugins del disco y registra los que falten en la DB (bajo demanda).
     */
    public function register()
    {
        $count = (new PackageInstaller())->syncPluginsFromDisk();

        return redirect()
            ->route('larajoom.admin.plugins.index')
            ->with('success', __('com_plugins::com_plugins.detected', ['count' => $count]));
    }

    /**
     * Edición de un plugin (estado, acceso, orden, nota y params).
     */
    public function edit($id)
    {
        $plugin = Plugin::plugins()->findOrFail($id);

        $fields = PluginParams::fields($plugin->folder, $plugin->element);
        $values = PluginParams::values($plugin->params ?? [], $fields);

        return view('admin.components.com_plugins::plugins.edit', [
            'plugin'  => $plugin,
            'fields'  => $fields,
            'values'  => $values,
            'rawJson' => empty($fields),
        ]);
    }

    /**
     * Guarda estado, acceso, orden, nota y params.
     */
    public function update(Request $request, $id)
    {
        $plugin = Plugin::plugins()->findOrFail($id);

        $data = $request->validate([
            'access'   => 'nullable|integer',
            'ordering' => 'nullable|integer|min:0',
            'note'     => 'nullable|string|max:255',
        ]);

        $plugin->enabled      = $request->boolean('enabled');
        $plugin->access       = $data['access'] ?? 1;
        $plugin->ordering     = $data['ordering'] ?? 0;
        $plugin->note         = $data['note'] ?? null;
        $plugin->save();

        $fields = PluginParams::fields($plugin->folder, $plugin->element);
        $params = $fields
            ? PluginParams::normalize($request, $fields)
            : $this->decodeJsonParams($request->input('params_json'));

        PluginParams::save($plugin->extension_id, $params);

        return match ($request->input('task')) {
            'save2close' => redirect()->route('larajoom.admin.plugins.index'),
            default      => redirect()->route('larajoom.admin.plugins.edit', $plugin->extension_id)
                ->with('success', __('com_plugins::com_plugins.saved')),
        };
    }

    /**
     * Habilita / deshabilita el plugin.
     */
    public function toggle($id)
    {
        $plugin = Plugin::plugins()->findOrFail($id);

        $plugin->enabled = !$plugin->enabled;
        $plugin->save();

        return redirect()->route('larajoom.admin.plugins.index');
    }

    protected function decodeJsonParams(?string $json): array
    {
        if ($json === null || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }
}
