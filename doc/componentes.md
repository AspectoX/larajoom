# Diseño y Estructura de Componentes — LaraJoom

Esta guía documenta la arquitectura de carpetas, convenciones de código y patrones de vista que siguen los componentes de LaraJoom, basándose en `com_menus` y `com_modules` (implementados de referencia).

---

## Regla de creación — Seeders
Al crear cualquier componente, preguntar al usuario si hay que crear seeders con datos por defecto (`database/seeders/`). Si confirma, incluir `LaraJoom*Seeder.php`. No asumir datos por defecto sin confirmación.

---

## 1. Estructura de Carpetas

Cada extensión ocupa su propia carpeta, sin anidar módulos ni plugins dentro del componente:

```
administrator/components/com_menus/          # Componente (administrador)
root/components/com_menus/                   # Componente (site)

administrator/modules/mod_menu/              # Módulo (administrador)
root/modules/mod_menu/                       # Módulo (site)

administrator/plugins/plg_menu/              # Plugin (administrador)
root/plugins/plg_menu/                       # Plugin (site)
```

### Ejemplo completo de un componente de administrador

```
administrator/components/com_menus/
├── manifest.json                       # Metadatos del componente
├── routes/
│   └── admin.php                       # Rutas del backend (siempre admin.php en administrator)
├── database/
│   ├── migrations/
│   │   ├── 2026_10_15_000004_create_larajoom_menu_types_table.php
│   │   └── 2026_10_17_000001_align_menu_table.php
│   └── seeders/
│       └── LaraJoomMenusSeeder.php
├── language/
│   ├── en/
│   │   └── com_menus.php               # Claves de idioma en inglés
│   └── es/
│       └── com_menus.php               # Claves de idioma en español
├── Models/
│   ├── MenuItem.php                    # Modelo tabla larajoom_menu
│   └── MenuType.php                    # Modelo tabla larajoom_menu_types
├── Controllers/
│   ├── MenuController.php              # Controlador de tipos de menú
│   └── MenuItemController.php          # Controlador de items de menú
└── resources/
    └── views/
        ├── menus/                      # Vistas de la entidad "menus"
        │   ├── index.blade.php
        │   ├── create.blade.php
        │   └── edit.blade.php
        └── items/                      # Vistas de la entidad "items"
            ├── index.blade.php
            ├── create.blade.php
            └── edit.blade.php
```

### Ejemplo completo de un componente de site

```
site/components/com_content/
├── manifest.json
├── routes/
│   └── web.php                         # Rutas del frontend
├── database/
│   └── migrations/
├── language/
│   ├── en/
│   │   └── com_content.php
│   └── es/
│       └── com_content.php
├── Models/
├── Controllers/
└── resources/
    └── views/
```

---

## 2. Archivo `manifest.json`

Define la extensión para el autodescubrimiento:

```json
{
    "type": "component",
    "name": "com_menus",
    "view_namespace": "admin.components.com_menus",
    "lang_namespace": "com_menus"
}
```

| Campo | Descripción |
|-------|-------------|
| `type` | `component`, `module` o `plugin` |
| `name` | Identificador (ej. `com_menus`) |
| `view_namespace` | Namespace Blade para las vistas (`admin.components.com_menus` en admin, `site.components.com_content` en site) |
| `lang_namespace` | Namespace de archivos de idioma (`com_menus`, `com_modules`, etc.) |

---

## 3. Rutas

### Administrador: `routes/admin.php`
El `ExtensionLoader` registra automáticamente con el middleware `web` y añade el prefijo `administrator`. El componente declara solo su propio prefijo (sin `administrator/`) y los middlewares de seguridad:

```php
<?php

use Illuminate\Support\Facades\Route;
use Aspectox\LaraJoom\Admin\Components\com_menus\Controllers\MenuController;
use Aspectox\LaraJoom\Admin\Components\com_menus\Controllers\MenuItemController;

Route::prefix('menus')->middleware(['larajoom.auth', 'larajoom.acl'])->group(function () {

    // Items primero (para que "items" no sea capturado por {id})
    Route::get('/items', [MenuItemController::class, 'index'])->name('larajoom.admin.menuitems.index');
    Route::get('/items/create', [MenuItemController::class, 'create'])->name('larajoom.admin.menuitems.create');
    Route::post('/items', [MenuItemController::class, 'store'])->name('larajoom.admin.menuitems.store');
    Route::post('/items/reorder', [MenuItemController::class, 'reorder'])->name('larajoom.admin.menuitems.reorder');
    Route::get('/items/{id}/edit', [MenuItemController::class, 'edit'])->name('larajoom.admin.menuitems.edit');
    Route::put('/items/{id}', [MenuItemController::class, 'update'])->name('larajoom.admin.menuitems.update');
    Route::delete('/items/{id}', [MenuItemController::class, 'destroy'])->name('larajoom.admin.menuitems.destroy');

    // Tipos de menú
    Route::get('/', [MenuController::class, 'index'])->name('larajoom.admin.menus.index');
    Route::get('/create', [MenuController::class, 'create'])->name('larajoom.admin.menus.create');
    Route::post('/', [MenuController::class, 'store'])->name('larajoom.admin.menus.store');
    Route::post('/reorder', [MenuController::class, 'reorder'])->name('larajoom.admin.menus.reorder');
    Route::get('/{id}/edit', [MenuController::class, 'edit'])->name('larajoom.admin.menus.edit');
    Route::put('/{id}', [MenuController::class, 'update'])->name('larajoom.admin.menus.update');
    Route::delete('/{id}', [MenuController::class, 'destroy'])->name('larajoom.admin.menus.destroy');
});
```

### Seguridad y prefijo de rutas admin

**El prefijo `administrator` lo añade el `ExtensionLoader` automáticamente. En los archivos de rutas de componentes admin NO se incluye el prefijo `administrator` dentro del `Route::prefix`** (hacerlo genera rutas duplicadas tipo `/administrator/administrator/menus`).

Todos los grupos de rutas utilizan `['larajoom.auth', 'larajoom.acl']`:

```php
Route::prefix('menus')->middleware(['larajoom.auth', 'larajoom.acl'])->group(function () {
    // ...
});
```

**No usar el middleware estándar `auth`**: valida el guard por defecto del host (`web`), mientras que la sesión vive bajo el guard `ax-larajoom` (el alias `larajoom.auth` valida ese guard correcto).

Resultado: `Route::prefix('users')` → URI final `/administrator/users`.



### Site: `routes/web.php`
Igual pero sin el prefijo `administrator` y con nombres `larajoom.site.{componente}...`

---

## 4. Modelos

Ubicación: `Models/`, namespace `Aspectox\LaraJoom\Admin\Components\{componente}\Models\`

Convenciones:
- `$table` = nombre de tabla en `larajoom_*`
- `$fillable` = campos masivamente asignables
- `$casts` = tipos (boolean, integer, etc.)

Ejemplo (`MenuItem.php`):

```php
<?php

namespace Aspectox\LaraJoom\Admin\Components\com_menus\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $table = 'larajoom_menu';

    protected $fillable = [
        'menutype', 'title', 'alias', 'link', 'type', 'published',
        'parent_id', 'level', 'ordering', 'home', 'show_icon',
        'icon', 'access',
    ];

    protected $casts = [
        'published' => 'boolean',
        'home'      => 'boolean',
        'show_icon' => 'boolean',
        'parent_id' => 'integer',
        'level'     => 'integer',
        'ordering'  => 'integer',
        'access'    => 'integer',
    ];

    public function menuType()
    {
        return $this->belongsTo(MenuType::class, 'menutype', 'menutype');
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('ordering');
    }
}
```

---

## 5. Controladores

Ubicación: `Controllers/`, namespace `Aspectox\LaraJoom\Admin\Components\{componente}\Controllers\`

Métodos estándar: `index`, `create`, `store`, `edit`, `update`, `destroy`, `reorder`

### Patrón de store/update (task)
Después de guardar, se redirige según el botón pulsado (`task`):

```php
return match ($request->input('task')) {
    'save'     => redirect()->route('larajoom.admin.menus.edit', $menu->id),
    'save2new' => redirect()->route('larajoom.admin.menus.create'),
    default    => redirect()->route('larajoom.admin.menus.index'),
};
```

### Patrón de reorder (drag & drop)
Soporta dos modos:
1. **Lista plana**: `order[]` con IDs
2. **Árbol jerárquico**: `nodes[]` con `{id, parent}` que actualiza `parent_id`, `level` y `ordering`

Ejemplo de árbol (`MenuItemController::orderTree`):

```php
protected function orderTree($items)
{
    $grouped = $items->groupBy('parent_id');
    $ordered = collect();
    $queue = collect([$items->where('parent_id', 0)->sortBy('ordering')->values()]);

    while ($queue->isNotEmpty()) {
        $siblings = $queue->shift();
        if (!$siblings) continue;

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

    $missing = $items->whereNotIn('id', $ordered->pluck('id'))->sortBy('ordering');
    foreach ($missing as $item) {
        if (isset($item->parent_id) && $item->parent_id > 0) {
            $item->level = 1;
        }
        $ordered->push($item);
    }

    return $ordered;
}
```

---

## 6. Vistas Blade

Todas las vistas extienden el layout base del template. El extends **no puede** fijar el nombre del template a "atum", porque pueden existir múltrones templates y cada uno personaliza su propio `index.blade.php`:

```blade
@extends('tpl::index')   // ✅ Namespace genérico para cualquier template
```

NO usar `larajoom_atum::index` (fija el template, rompe con otros templates).

### Secciones obligatorias

| Sección | Contenido |
|---------|-----------|
| `title` | Título de la página (`__('com_menus::com_menus.menus')`) |
| `icon` | Icono de la página (`menus`, `list`, etc.) |
| `toolbar` | Barra de herramientas con `<x-larajoom::toolbar>` |
| `content` | Contenido principal de la vista |

### Índices con tabla (`<x-larajoom::table>`)

El componente sistema `table` recibe:
- `:headers` — array de cabeceras
- `:rows` — array de filas (cada celda puede ser string o `['html' => '...']`)
- `:actions` — array de acciones por fila (`edit`, `delete` con rutas)
- `:ids` — IDs de las filas (para reordenar)
- `:meta` — metadatos adicionales (para árbol: id, parent, level)
- `:tree="true"` — activa modo árbol/árbol jerárquico
- `:reorder-url` — ruta para persistencia del drag & drop

Ejemplo (`menus/index.blade.php`):

```blade
@php
    $rows = [];
    $actions = [];
    foreach ($menus as $i => $menu) {
        $rows[] = [
            ['html' => '<a href="' . route('larajoom.admin.menuitems.index', ['menutype' => $menu->menutype]) . '" class="btn transparent circle" title="' . e(__('com_menus::com_menus.menu_items')) . '">' . larajoom_icon('menus') . '</a>'],
            $menu->menutype,
            $menu->title,
            $menu->client_id ? __('com_menus::com_menus.administrator') : __('com_menus::com_menus.site'),
            $menu->items_count,
            $menu->id,
        ];
        $actions[$i] = [
            'edit'   => route('larajoom.admin.menus.edit', $menu->id),
            'delete' => route('larajoom.admin.menus.destroy', $menu->id),
        ];
    }
@endphp

<x-larajoom::table
    :headers="[
        __('com_menus::com_menus.menu_items'),
        __('com_menus::com_menus.menutype'),
        __('com_menus::com_menus.title'),
        __('com_menus::com_menus.client'),
        __('com_menus::com_menus.items'),
        __('com_menus::com_menus.id'),
    ]"
    :rows="$rows"
    :actions="$actions"
    :ids="$menus->pluck('id')"
    :reorder-url="route('larajoom.admin.menus.reorder')"
/>
```

### Formularios (`<x-larajoom::form>`)

Usa componentes sistema para inputs:

```blade
<x-larajoom::form :action="route('larajoom.admin.menus.store')" method="POST" id="adminForm">
    <fieldset>
        <legend>{{ __('com_menus::com_menus.menu_type') }}</legend>

        <x-larajoom::input
            name="menutype"
            :label="__('com_menus::com_menus.menutype')"
            required
            size="60"
        />

        <x-larajoom::select name="client_id" :label="__('com_menus::com_menus.client')">
            <option value="0" @selected(old('client_id', 0) == 0)>{{ __('com_menus::com_menus.site') }}</option>
            <option value="1" @selected(old('client_id') == 1)>{{ __('com_menus::com_menus.administrator') }}</option>
        </x-larajoom::select>

        <p>
            <label>
                <input type="checkbox" name="published" value="1" @checked(old('published', true))>
                {{ __('com_menus::com_menus.published') }}
            </label>
        </p>
    </fieldset>
</x-larajoom::form>
```

Componentes sistema disponibles en formularios:
- `<x-larajoom::input>` — texto, número, etc. (acepta `id` propio)
- `<x-larajoom::textarea>` — área de texto multilínea (`:label`, `:value`, `:rows`)
- `<x-larajoom::select>` — desplegable (acepta `id` propio y `multiple`)
- `<x-larajoom::toggle>` — interruptor sí/no (`:label`, `:checked`, `:value`, `:unchecked`)
- `<x-larajoom::checkbox>` — casilla con estilo `form-check` (`:label`, `:checked`)
- `<x-larajoom::alias>` — alias autogenerado (readonly con auto-slug vía JS)
- `<x-larajoom::iconselect>` — selector de iconos

### Toolbar (`<x-larajoom::toolbar>`)

Botones estándar en formularios:

```blade
@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('com_menus::com_menus.cancel'),
            'icon'  => 'cancel',
            'url'   => route('larajoom.admin.menus.index'),
            'color' => 'warning',
        ],
        [
            'label' => __('com_menus::com_menus.save'),
            'icon'  => 'save',
            'type'  => 'submit',
            'form'  => 'adminForm',
            'name'  => 'task',
            'value' => 'save',
        ],
        [
            'label' => __('com_menus::com_menus.save_new'),
            'icon'  => 'add',
            'type'  => 'submit',
            'form'  => 'adminForm',
            'name'  => 'task',
            'value' => 'save2new',
            'color' => 'info',
        ],
        [
            'label' => __('com_menus::com_menus.save_close'),
            'icon'  => 'check',
            'type'  => 'submit',
            'form'  => 'adminForm',
            'name'  => 'task',
            'value' => 'save2close',
        ],
    ]" />
@endsection
```

Atributos de botón:
- `label` — texto del botón
- `icon` — nombre del icono (se resuelve con `@icon()`)
- `type` — `submit` o `link` (por defecto `link`)
- `form` — ID del formulario (solo submit)
- `name` / `value` — para el campo `task` (solo submit)
- `url` — destino (solo link)
- `color` — `success`, `warning`, `info`, etc.

---

## 7. Idiomas

Archivos en `language/{en,es}/{componente}.php`, accesibles vía `__('com_menus::com_menus.clave')`:

```php
<?php

return [
    'menus'        => 'Menús',
    'menu_items'   => 'Elementos de menú',
    'new_menu'     => 'Nuevo menú',
    'edit_menu'    => 'Editar menú',
    'menutype'     => 'Tipo de menú',
    'title'        => 'Título',
    'client'       => 'Cliente',
    'site'         => 'Sitio',
    'administrator'=> 'Administrador',
    'items'        => 'Items',
    'cancel'       => 'Cancelar',
    'save'         => 'Guardar',
    'save_new'     => 'Guardar y nuevo',
    'save_close'   => 'Guardar y cerrar',
];
```

El namespace de idioma se declara en `manifest.json` como `lang_namespace`.

---

## 8. Migraciones y Seeders

Ubicación: `database/migrations/` y `database/seeders/`

Ejemplo de seeder (`LaraJoomMenusSeeder.php`): inserta los tipos de menú y items por defecto (Dashboard, Menú, Módulos).

---

## 9. Módulos de administrador (`com_modules`)

### Detalles específicos de `com_modules`

- Lee los tipos de módulo disponibles escaneando las carpetas `mod_*` en `modules/`
- Lee las posiciones del template desde `administrator/templates/atum/positions.json`
- Campos: `title`, `module`, `position`, `published`, `ordering`, `showtitle`, `params`, `access`
- Rutas bajo el prefijo `modules` (no tiene sub-entidades como `com_menus`)

### Vista de edición con título de página (`edit.blade.php`)

```blade
@section('content')
    <h2>{{ __('com_modules::com_modules.edit_module') }} #{{ $module->id }}</h2>
    <x-larajoom::form ...>
        ...
    </x-larajoom::form>
@endsection
```

Nota: el `<h2>` de la vista de edición de módulo es el título de la página. Según la arquitectura de títulos por extensión, esto debería ser condicional mediante el helper `larajoom_extension_param()` con los parámetros `show_page_heading` + `page_heading` guardados en `larajoom_extensions.params` / `larajoom_modules.params`.

---

## 10. Reglas de Estilo y Convenciones

1. **Cero CSS:** No incluir archivos `.css` ni atributos `style="..."`. Los estilos los provee el template.
2. **HTML semántico:** `<nav>`, `<header>`, `<main>`, `<article>`, `<section>`, `<footer>`, `<form>`, `<table>`.
3. **Componentes sistema:** Usar `<x-larajoom::table>`, `<x-larajoom::form>`, `<x-larajoom::input>`, `<x-larajoom::select>`, `<x-larajoom::checkbox>`, `<x-larajoom::toolbar>`, `<x-larajoom::alias>`, `<x-larajoom::iconselect>` en lugar de HTML nativo.
4. **Iconos:** Siempre con `@icon('nombre')` o `larajoom_icon('nombre')`, nunca clases de font-icon hardcodeadas.
5. **Layout base:** Todas las vistas `@extends('tpl::index')`. Nunca fijar el nombre del template (`larajoom_atum::index` rompe si se usa otro template).
6. **Rutas de plugins:** los plugins se gestionan en `com_plugins` (`/administrator/plugins`); ver `extensions-guide.md` para el runtime de eventos y el esquema `param_fields`.
6. **Secciones:** `title`, `icon`, `toolbar`, `content`.
7. **Traducciones:** Todo texto visible vía `__('namespace::clave')`.
8. **Rutas:** Nombres `larajoom.admin.{componente}.{entidad}.{accion}`.
9. **Task:** `save`, `save2new`, `save2close` en store/update.
10. **Árbol:** Para entidades jerárquicas (items de menú), usar `orderTree` con `parent_id` + `level` + `ordering` (no nested set lft/rgt).

---

## 11. ACL

El ACL está en el núcleo (`Joomla\CMS\Access`), NO es un componente. Gestiona grupos, niveles de acceso y la tabla `#__assets`. Todos los componentes, módulos y plugins heredan los mismos grupos/niveles de acceso.

---

## 12. Checklist al crear un nuevo componente

- [ ] Carpeta `administrator/components/com_xxx/` (o `site/components/com_xxx/`)
- [ ] `manifest.json` con type, name, view_namespace, lang_namespace
- [ ] `routes/admin.php` (o `routes/web.php`)
- [ ] `Models/` con Eloquent models
- [ ] `Controllers/` con index/create/store/edit/update/destroy/reorder
- [ ] `resources/views/` organizadas por entidad
- [ ] `language/en/{xxx}.php` y `language/es/{xxx}.php`
- [ ] `database/migrations/`
- [ ] `database/seeders/` (opcional)
- [ ] Vistas `@extends('tpl::index')` con secciones title/icon/toolbar/content
- [ ] Usar componentes sistema para tablas, formularios y toolbar
- [ ] Todos los textos vía `__('com_xxx::com_xxx.clave')`
