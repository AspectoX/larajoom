<?php

namespace Aspectox\LaraJoom\Admin\Components\com_menus\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Aspectox\LaraJoom\Admin\Components\com_menus\Models\MenuItem;

class MenuItemController extends Controller
{
    protected const ITEM_TYPES = ['component', 'url', 'alias', 'heading', 'separator'];

    public function index(Request $request)
    {
        $menuTypes = DB::table('larajoom_menu_types')->orderBy('ordering')->get();

        $query = MenuItem::query();
        if ($menutype = $request->query('menutype')) {
            $query->where('menutype', $menutype);
        }
        $items = $this->orderTree($query->get());

        $levels = Schema::hasTable('larajoom_viewlevels')
            ? DB::table('larajoom_viewlevels')->orderBy('id')->pluck('title', 'id')
            : collect();

        return view('admin.components.com_menus::items.index', compact('items', 'menuTypes', 'menutype', 'levels'));
    }

    public function create(Request $request)
    {
        [$menuTypes, $parents, $levels] = $this->formData($request->query('menutype'));
        $components = $this->adminComponents();

        return view('admin.components.com_menus::items.create', compact('menuTypes', 'parents', 'levels', 'components'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['level'] = $data['parent_id'] > 0
            ? (int) MenuItem::findOrFail($data['parent_id'])->level + 1
            : 1;

        $data['ordering'] = $data['ordering'] ?: 0;

        $item = MenuItem::create($data);

        if ($data['home']) {
            MenuItem::where('menutype', $data['menutype'])
                ->whereNot('home', true)
                ->update(['home' => false]);
        }

        return match ($request->input('task')) {
            'save'     => redirect()->route('larajoom.admin.menuitems.edit', $item->id),
            'save2new' => redirect()->route('larajoom.admin.menuitems.create', ['menutype' => $data['menutype']]),
            default    => redirect()->route('larajoom.admin.menuitems.index', ['menutype' => $data['menutype']]),
        };
    }

    public function edit(int $id)
    {
        $item = MenuItem::findOrFail($id);
        [$menuTypes, $parents, $levels] = $this->formData($item->menutype);
        $components = $this->adminComponents();

        return view('admin.components.com_menus::items.edit', compact('item', 'menuTypes', 'parents', 'levels', 'components'));
    }

    public function update(Request $request, int $id)
    {
        $item = MenuItem::findOrFail($id);

        $data = $this->validated($request);

        $data['level'] = $data['parent_id'] > 0
            ? (int) MenuItem::findOrFail($data['parent_id'])->level + 1
            : 1;

        $item->update($data);

        if ($data['home'] !== $item->getOriginal('home')) {
            MenuItem::where('menutype', $data['menutype'])
                ->where('id', '<>', $item->id)
                ->where('home', true)
                ->update(['home' => false]);
        }

        return match ($request->input('task')) {
            'save'     => redirect()->route('larajoom.admin.menuitems.edit', $item->id),
            'save2new' => redirect()->route('larajoom.admin.menuitems.create', ['menutype' => $data['menutype']]),
            default    => redirect()->route('larajoom.admin.menuitems.index', ['menutype' => $data['menutype']]),
        };
    }

    public function reorder(Request $request)
    {
        $nodes = (array) $request->input('nodes', []);

        if (!empty($nodes)) {
            $levels = [0 => 0];
            $order  = [];

            foreach ($nodes as $node) {
                $id     = (int) ($node['id'] ?? 0);
                $parent = (int) ($node['parent'] ?? 0);

                if (!$id) {
                    continue;
                }

                $level = ($levels[$parent] ?? 0) + 1;
                $levels[$id] = $level;
                $order[$parent] = ($order[$parent] ?? 0) + 1;

                MenuItem::where('id', $id)->update([
                    'parent_id' => $parent,
                    'level'     => $level,
                    'ordering'  => $order[$parent],
                ]);
            }

            return response()->json(['ok' => true]);
        }

        foreach ((array) $request->input('order', []) as $i => $id) {
            MenuItem::where('id', $id)->update(['ordering' => $i + 1]);
        }

        return response()->json(['ok' => true]);
    }

    public function destroy(int $id)
    {
        $item = MenuItem::findOrFail($id);
        $ids = $this->descendantIds([$item->id]);
        MenuItem::whereIn('id', $ids)->delete();

        return redirect()->route('larajoom.admin.menuitems.index');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'menutype'  => 'required|string|max:100',
            'title'     => 'required|string|max:255',
            'alias'     => 'nullable|alpha_dash|max:255',
            'type'      => 'nullable|string|in:' . implode(',', self::ITEM_TYPES),
            'link'      => 'nullable|string|max:255',
            'icon'      => 'nullable|string|max:255',
            'ordering'  => 'nullable|integer|min:0',
            'access'    => 'nullable|integer',
        ]);

        $data['alias'] = !empty($data['alias']) ? Str::slug($data['alias']) : Str::slug($data['title']);
        $data['type'] = $data['type'] ?? 'url';
        $data['parent_id'] = (int) $request->input('parent_id', 0);
        $data['published'] = $request->has('published');
        $data['home'] = $request->has('home');
        $data['show_icon'] = $request->has('show_icon');
        $data['icon'] = $data['icon'] ?? null;

        return $data;
    }

    protected function formData(?string $selectedMenutype = null): array
    {
        $menuTypes = DB::table('larajoom_menu_types')->orderBy('ordering')->get();

        $parents = $this->orderTree(MenuItem::query()->get())->map(function ($item) {
            $prefix = str_repeat('— ', max(0, $item->level - 1));

            return (object) ['id' => $item->id, 'label' => $prefix . $item->title];
        });

        $levels = Schema::hasTable('larajoom_viewlevels')
            ? DB::table('larajoom_viewlevels')->orderBy('id')->get()
            : collect();

        return [$menuTypes, $parents, $levels];
    }

    /**
     * Componentes del administrador para el selector de link de tipo "component":
     * mezcla DB y escaneo de disco (así un componente nuevo sin sync aparece igual).
     */
    protected function adminComponents(): array
    {
        $dir = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'administrator'
            . DIRECTORY_SEPARATOR . 'components';
        $components = [];

        if (is_dir($dir)) {
            foreach (scandir($dir) as $item) {
                if (str_starts_with($item, 'com_') && is_dir($dir . DIRECTORY_SEPARATOR . $item)) {
                    $components['/administrator/' . Str::after($item, 'com_')] = $item;
                }
            }
        }

        if (Schema::hasTable('larajoom_extensions')) {
            DB::table('larajoom_extensions')
                ->where('type', 'component')
                ->where('client_id', 1)
                ->orderBy('element')
                ->pluck('element')
                ->each(function ($element) use (&$components) {
                    $link = '/administrator/' . Str::after($element, 'com_');
                    if (!isset($components[$link])) {
                        $components[$link] = $element;
                    }
                });
        }

        ksort($components);

        return $components;
    }

    /**
     * Devuelve los items ordenados en forma de árbol (padres antes que hijos)
     * usando parent_id + ordering, sin necesidad de nested set (lft/rgt).
     */
    protected function orderTree($items)
    {
        $grouped = $items->groupBy('parent_id');
        $ordered = collect();
        $queue = collect([$items->where('parent_id', 0)->sortBy('ordering')->values()]);

        while ($queue->isNotEmpty()) {
            $siblings = $queue->shift();
            if (!$siblings) {
                continue;
            }

            foreach ($siblings as $item) {
                $ordered->push($item);

                $children = $grouped->get($item->id)
                    ? $grouped->get($item->id)->sortBy('ordering')->values()
                    : collect();
                if ($children->isNotEmpty()) {
                    $queue->push($children);
                }
            }
        }

        // Seguridad: items huérfanos (padre borrado) que no entraron al árbol
        $missing = $items->whereNotIn('id', $ordered->pluck('id'))->sortBy('ordering');
        foreach ($missing as $item) {
            if (isset($item->parent_id) && $item->parent_id > 0) {
                $item->level = 1;
            }
            $ordered->push($item);
        }

        return $ordered;
    }

    protected function descendantIds(array $roots): array
    {
        $ids = $roots;
        $queue = $roots;
        while ($queue !== []) {
            $children = MenuItem::whereIn('parent_id', $queue)->pluck('id');
            $queue = array_values(array_diff($children->all(), $ids));
            $ids = array_merge($ids, $queue);
        }

        return $ids;
    }
}
