<?php

namespace Aspectox\LaraJoom\Admin\Components\com_modules\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Aspectox\LaraJoom\Admin\Components\com_modules\Models\Module;
use Aspectox\LaraJoom\Admin\Components\com_modules\Services\ModuleParams;

class ModuleController extends Controller
{
    public function index()
    {
        $limit = config('larajoom.list_limit', 10);
        $modules = Module::orderBy('position')->orderBy('ordering')->paginate($limit);

        return view('admin.components.com_modules::index', compact('modules'));
    }

    public function create()
    {
        return view('admin.components.com_modules::create', [
            'moduleTypes' => $this->moduleTypes(),
            'positions'   => $this->positions(),
        ]);
    }

    public function store(Request $request)
    {
        $module = Module::create($this->validated($request));

        $this->saveParams($request, $module);

        return match ($request->input('task')) {
            'save'     => redirect()->route('larajoom.admin.modules.edit', $module->id),
            'save2new' => redirect()->route('larajoom.admin.modules.create'),
            default    => redirect()->route('larajoom.admin.modules.index'),
        };
    }

    public function edit(int $id)
    {
        $module = Module::findOrFail($id);

        return view('admin.components.com_modules::edit', [
            'module'      => $module,
            'moduleTypes' => $this->moduleTypes(),
            'positions'   => $this->positions(),
            'fields'      => ModuleParams::fields($module->module),
            'values'      => ModuleParams::values($module->params, ModuleParams::fields($module->module)),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $module = Module::findOrFail($id);
        $module->update($this->validated($request));

        $this->saveParams($request, $module);

        return match ($request->input('task')) {
            'save'     => redirect()->route('larajoom.admin.modules.edit', $module->id),
            'save2new' => redirect()->route('larajoom.admin.modules.create'),
            default    => redirect()->route('larajoom.admin.modules.index'),
        };
    }

    public function reorder(Request $request)
    {
        foreach ((array) $request->input('order', []) as $i => $id) {
            Module::where('id', $id)->update(['ordering' => $i + 1]);
        }

        return response()->json(['ok' => true]);
    }

    public function destroy(int $id)
    {
        Module::findOrFail($id)->delete();

        return redirect()->route('larajoom.admin.modules.index');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'title'    => 'required|string|max:150',
            'module'   => 'required|string|max:100',
            'position' => 'required|string|max:50',
            'ordering' => 'nullable|integer|min:0',
            'access'   => 'nullable|integer',
        ]);

        $data['ordering']  = $data['ordering'] ?? 0;
        $data['access']    = $data['access'] ?? 1;
        $data['showtitle'] = $request->boolean('showtitle');
        $data['published'] = $request->boolean('published');

        return $data;
    }

    /**
     * Guarda los params en JSON: bootstrap_size (ancho en columnas del panel)
     * y los param_fields del manifest. Conserva las claves extra previas.
     * Si no hay nada nuevo que guardar, no toca nada.
     */
    protected function saveParams(Request $request, Module $module): void
    {
        $existing = ModuleParams::values($module->params, []);
        $save     = [];

        $size = $request->input('bootstrap_size');
        if ($size !== null && $size !== '') {
            $save['bootstrap_size'] = max(0, min(12, (int) $size));
        }

        $fields = ModuleParams::fields($module->module);
        if (!empty($fields)) {
            $save = array_merge($save, ModuleParams::normalize($request, $fields));
        }

        if ($save === []) {
            return;
        }

        $module->update(['params' => json_encode(array_merge($existing, $save))]);
    }

    /**
     * Tipos de módulo instalados.
     *
     * Se descubren en todas las carpetas "modules" del paquete (raíz, administrador,
     * componentes y plugins) a través de ModuleTypes.
     */
    protected function moduleTypes(): array
    {
        return \Aspectox\LaraJoom\Core\Modules\ModuleTypes::types();
    }

    /**
     * Posiciones del template (administrator/templates/atum/positions.json).
     */
    protected function positions(): array
    {
        $file = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'administrator'
            . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'atum'
            . DIRECTORY_SEPARATOR . 'positions.json';

        if (!is_file($file)) {
            return [];
        }

        $json = json_decode(file_get_contents($file), true);

        return array_map(fn ($p) => $p['name'], $json['positions'] ?? []);
    }
}
