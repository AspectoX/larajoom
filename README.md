# LaraJoom

**Joomla 6 portado a Laravel** — un CMS con la arquitectura modular y desacoplada de Joomla, sobre Laravel 11/12/13.

LaraJoom reproduce la estructura, los nombres y el comportamiento de las extensiones de Joomla 6 (componentes `com_*`, módulos `mod_*`, plugins `plg_*`), pero implementados como un **paquete Composer** con autodescubrimiento, middleware de ACL propio y componentes Blade reutilizables.

---

## Requisitos

| | |
|---|---|
| PHP | `^8.2` |
| Laravel | `11`, `12` o `13` (`illuminate/*`) |
| Composer | `2.8+` |

## Instalación

```bash
# 1. Agregar el repositorio local (path) en composer.json
"repositories": [
    { "type": "path", "url": "packages/larajoom" }
]

# 2. Instalar el paquete
composer require aspectox/larajoom

# 3. Crear las tablas del core y cargar la configuración por defecto
php artisan migrate
php artisan db:seed --class=LaraJoomConfigSeeder

# 4. Publicar los assets estáticos (CSS/JS de templates y sistema)
php artisan vendor:publish --tag=larajoom-assets
```

El service provider `Aspectox\LaraJoom\LaraJoomServiceProvider` se auto-descubre (Laravel Package Discovery). Si tu app no usa discovery, regístralo en `bootstrap/providers.php`.

Acceso al panel: **`/administrator`**

> Detalle completo en [`doc/installation.md`](doc/installation.md).

---

## Estructura

```
larajoom/
├── src/                              Núcleo del paquete
│   ├── LaraJoomServiceProvider.php   Arranque, autodescubrimiento, config
│   └── Core/
│       ├── ExtensionLoader.php       Escanea componentes, módulos y plugins
│       ├── ConfigLoader.php          Configuración global (DB → config())
│       ├── LayoutHelper.php          Render de layouts por posición
│       ├── Middleware/               Auth y ACL (guard `ax-larajoom`)
│       ├── Overrides/                Overrides de vistas (twig-like)
│       ├── Plugins/                  Gestor y helpers de plugins
│       ├── Positions/                Render de posiciones del template
│       └── Resilience/               SafeRunner (circuit breaker)
│
├── administrator/
│   ├── components/com_*/             Componentes del panel
│   ├── modules/mod_*/                Módulos del panel
│   ├── templates/atum/               Template de administración (Atum)
│   ├── templates/system/             Template base del sistema
│   └── language/{es,en}/             Traducciones del panel
│
├── components/com_*/                 Componentes del sitio
├── modules/mod_*/                    Módulos del sitio
├── plugins/{grupo}/{plugin}/         Plugins (auth, content, user, api…)
├── site/components/                  Componentes del sitio (legacy)
│
├── media/
│   ├── system/components/            Componentes Blade del sistema
│   ├── system/css/                   CSS del sistema (reset, root, grid, components)
│   ├── templates/administrator/atum/ CSS, JS e imágenes del template
│   └── system/language/{es,en}/
│
├── database/migrations/              Migraciones del core
├── database/seeders/                 Seeders (config, usuarios, ACL, menús)
├── config/larajoom.php               Defaults de configuración
└── doc/                              Documentación
```

Cada extensión vive en **su propia carpeta**, igual que en Joomla: los módulos y plugins de un componente **no** van anidados dentro de él.

---

## Características

### Autodescubrimiento
`LaraJoomServiceProvider` + `ExtensionLoader` recorren el paquete en cada arranque y registran, sin configuración manual:

| Recurso | Ubicación detectada |
|---|---|
| Rutas del sitio | `{extension}/routes/web.php` |
| Rutas del panel | `{extension}/routes/admin.php` (prefijo `/administrator` automático) |
| Vistas | `{extension}/resources/views` y `{extension}/tmpl` |
| Migraciones | `{extension}/database/migrations` |
| Traducciones | `{extension}/language` + namespace del manifiesto |

### Componentes Blade del sistema
Registrados con `Blade::anonymousComponentPath()`, se usan como `<x-larajoom::input>`:

`input` · `textarea` · `select` · `toggle` · `checkbox` · `form` · `table` · `toolbar` · `alias` · `iconselect` · `file` · `Message`

```blade
<x-larajoom::input name="title" :label="__('com_menus::com_menus.title')" required />
<x-larajoom::textarea name="description" :value="$article->introtext" rows="5" />
<x-larajoom::toggle name="offline" :label="__('com_config::com_config.offline')" :checked="$offline" />
<x-larajoom::toolbar :buttons="[['label' => 'Save', 'icon' => 'save', 'type' => 'submit']]" />
```

### Namespaces de vistas
`tpl::` (genérico), `admin_tpl::`, `site_tpl::` (template activo) y `larajoom_{template}::` (template concreto). Las vistas de componentes usan siempre `admin_tpl::` / `site_tpl::`, así que funcionan con cualquier template.

### Namespaces de idioma
| Namespace | Origen |
|---|---|
| `larajoom_admin` | `administrator/language` |
| `tmp` | `administrator/templates/*/language` (manifiesto con `type: "template"`) |
| `mdl` / `pgi` | Módulos y plugins con `language/` |
| `{lang_namespace}` | Componentes (según `manifest.json`) |

### Resiliencia (circuit breaker)
`SafeRunner::execute()` aísla los errores de un módulo o plugin: la excepción se registra en el log y **el render de la página continúa**.

```php
SafeRunner::execute('mod_custom', fn () => $html, $fallbackHtml);
```

### ACL y seguridad
- Guard de administración propio: `ax-larajoom` (misma tabla `users` que el sitio, para que el mapa de roles tenga un único origen de ids).
- Niveles de permiso por componente y grupo: **sin acceso / leer / leer y escribir / leer, escribir y borrar**.
- Middleware `larajoom.auth` y `larajoom.acl` para proteger las rutas del panel: el ACL valida el componente y la acción de cada ruta (`core.manage`, `core.create`, `core.edit`, `core.delete`).
- Al panel solo entran los roles con perfil administrativo: Author, Editor, Publisher, Manager, Administrator y SuperAdmin. Public, Guest y Registered quedan afuera.
- El menú lateral oculta los ítems sin nivel de acceso o sin permiso de lectura; los botones editar/borrar se muestran según el nivel.
- Matriz de permisos por componente y grupo en **Configuración global → Permisos**.
- Jerarquía de roles: Public → Guest → Registered → Author → Editor → Publisher → Manager → Administrator → SuperAdmin.

### Configuración global
Los valores viven en la tabla `larajoom_configuration` y se cargan **una vez por request** en el boot, disponibles como `config('larajoom.<clave>')` en todo el código. Se editan desde **Configuración global** (`com_config`), organized en pestañas: Site, Metadata, SEO, System, Server, Logging, Filters y Permissions.

---

## Componentes incluidos

**Panel:** `com_admin` · `com_cpanel` · `com_config` · `com_content` · `com_users` · `com_menus` · `com_modules` · `com_plugins` · `com_installer` · `com_login` · `com_actionlogs`

**Sitio:** `com_content` · `com_users` · `com_login`

**Módulos:** `mod_menu` · `mod_title` · `mod_breadcrumbs` · `mod_login`

**Plugins:** `authentication/{cookie,joomla,ldap}` · `api-authentication/{basic,token}` · `content/{contact,emailcloak,loadmodule,pagebreak,pagenavigation,vote}` · `user/profile`

---

## Documentación

| Documento | Contenido |
|---|---|
| [`doc/installation.md`](doc/installation.md) | Instalación paso a paso |
| [`doc/architecture.md`](doc/architecture.md) | Arquitectura, autodescubrimiento, resiliencia, idiomas |
| [`doc/componentes.md`](doc/componentes.md) | Componentes Blade y reglas de UI |
| [`doc/extensions-guide.md`](doc/extensions-guide.md) | Guía para crear componentes, módulos y plugins |
| [`doc/positions-and-modules.md`](doc/positions-and-modules.md) | Posiciones del template y módulos |
| [`doc/template-overrides.md`](doc/template-overrides.md) | Overrides de vistas de plantilla |
| [`doc/admin-manual.md`](doc/admin-manual.md) | Manual del panel de administración |
| [`doc/configuracion.md`](doc/configuracion.md) | Auditoría de la configuración global |

---

## Licencia

MIT © Aspectox
