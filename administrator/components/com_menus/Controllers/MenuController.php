<?php

namespace Aspectox\LaraJoom\Admin\Components\com_menus\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Aspectox\LaraJoom\Admin\Components\com_menus\Models\MenuType;
use Aspectox\LaraJoom\Admin\Components\com_menus\Models\MenuItem;

class MenuController extends Controller
{
    public function index()
    {
        $limit = config('larajoom.list_limit', 20);
        $menus = MenuType::withCount('items')->orderBy('ordering')->orderBy('title')->paginate($limit);

        return view('admin.components.com_menus::menus.index', compact('menus'));
    }

    public function create()
    {
        return view('admin.components.com_menus::menus.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'menutype'    => 'required|alpha_dash|max:100|unique:larajoom_menu_types,menutype',
            'title'       => 'required|string|max:150',
            'description' => 'nullable|string|max:255',
            'client_id'   => 'nullable|integer|in:0,1',
        ]);

        $data['menutype'] = Str::slug($data['menutype']);
        $data['client_id'] = $data['client_id'] ?? 0;
        $data['ordering'] = (int) MenuType::max('ordering') + 1;

        $menu = MenuType::create($data);

        return match ($request->input('task')) {
            'save'     => redirect()->route('larajoom.admin.menus.edit', $menu->id),
            'save2new' => redirect()->route('larajoom.admin.menus.create'),
            default    => redirect()->route('larajoom.admin.menus.index'),
        };
    }

    public function edit(int $id)
    {
        $menu = MenuType::findOrFail($id);

        return view('admin.components.com_menus::menus.edit', compact('menu'));
    }

    public function update(Request $request, int $id)
    {
        $menu = MenuType::findOrFail($id);

        $data = $request->validate([
            'menutype'    => 'required|alpha_dash|max:100|unique:larajoom_menu_types,menutype,' . $id,
            'title'       => 'required|string|max:150',
            'description' => 'nullable|string|max:255',
            'client_id'   => 'nullable|integer|in:0,1',
        ]);

        $menu->update([
            'menutype'    => $menu->items()->exists() ? $menu->menutype : Str::slug($data['menutype']),
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'client_id'   => $data['client_id'] ?? 0,
        ]);

        return match ($request->input('task')) {
            'save'     => redirect()->route('larajoom.admin.menus.edit', $menu->id),
            'save2new' => redirect()->route('larajoom.admin.menus.create'),
            default    => redirect()->route('larajoom.admin.menus.index'),
        };
    }

    public function reorder(Request $request)
    {
        foreach ((array) $request->input('order', []) as $i => $id) {
            MenuType::where('id', $id)->update(['ordering' => $i + 1]);
        }

        return response()->json(['ok' => true]);
    }

    public function destroy(int $id)
    {
        $menu = MenuType::findOrFail($id);

        // El eliminado del tipo borra también sus items (paridad con Joomla)
        MenuItem::where('menutype', $menu->menutype)->delete();
        $menu->delete();

        return redirect()->route('larajoom.admin.menus.index');
    }
}
