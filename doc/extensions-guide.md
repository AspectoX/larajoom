# Guía de Creación de Extensiones

## 1. Estructura de un Componente (`com_*`)
Cada componente debe ubicarse en `components/com_ejemplo/` (o en `administrator/components/com_ejemplo/` para el backend):

```text
com_ejemplo/
├── routes/
│   └── web.php
├── src/
│   ├── Controllers/
│   └── Models/
├── resources/views/
│   └── default.blade.php
└── database/migrations/
    └── xxxx_xx_xx_create_ejemplo_table.php
```

## 2. Reglas de Estilo
- **Cero CSS:** No incluir archivos `.css` ni atributos inline (`style="..."`).
- **HTML Semántico:** Utilizar etiquetas estándar (`<nav>`, `<header>`, `<main>`, `<article>`, `<section>`, `<footer>`, `<form>`, `<table>`).
- **Reactividad:** Utilizar Livewire y Alpine.js para comportamiento dinámico.

## 3. Instalador (`com_installer`)
Porta el `com_installer` de Joomla 6 sobre `manifest.json`. Ubicación: `/administrator/installer`.

- **Gestión (Manage):** lista de extensiones de `larajoom_extensions`. Al abrir el listado **auto-registra** en la DB todo lo que ya está en disco (componentes, módulos, templates con `manifest.json` y todos los plugins, con o sin manifiesto) que aún no esté registrado (Discover automático de Joomla).
- **Instalar:** única vía soportada en la interfaz — subida de `.zip` (el backend conserva el soporte interno de URL/carpeta para desarrollo).
- **Estado:** activar/desactivar extensiones (`enabled`). El núcleo (`protected`) no se puede desactivar ni desinstalar.
- **Desinstalar:** elimina el registro en `larajoom_extensions` y las instancias de `larajoom_modules`; los archivos se conservan (v1).
- Las columnas de datos tipo Joomla 6 replicadas: `package_id`, `protected`, `locked`, `manifest_cache` (JSON con version/author/description...), `state`, `note`.

### Paquete instalable
Un zip (o carpeta) con un `manifest.json` en su raíz:

```json
{
    "type": "component",            // component | module | plugin | template
    "name": "com_importador",
    "view_namespace": "admin.components.com_importador",   // o site.components.*
    "lang_namespace": "com_importador",
    "client": "administrator",       // opt: administrator | site (default site, excepto templates)
    "group": "system",               // solo plugins
    "version": "1.0.0", "author": "...", "description": "..."   // metadatos opcionales
}
```

El instalador copia la carpeta al destino estándar (`components/`, `administrator/components/`, `modules/` o `administrator/modules/`, `plugins/{group}/`, `administrator/templates/`), ejecuta sus `database/migrations` y registra la extensión con su `manifest_cache`.
Los plugins **sin `manifest.json`** dentro de `plugins/{group}/{name}/` también se auto-registran (con nombre inferido `plg_{group}_{name}`).

## 4. Plugins: gestión (`com_plugins`) y runtime

**Gestión** (`/administrator/plugins`):
- Lista las filas `type=plugin` de `larajoom_extensions` (auto-registro al abrir).
- Habilitar/deshabilitar por columna de estado (iconos `circle-check`/`circle-times`).
- Edición: estado, acceso, `ordering` (aplica **dentro del grupo**), nota y **parámetros**.
- Parámetros: si el manifest tiene `param_fields` se renderizan inputs reales; si no, edición como JSON libre. Se guardan en `larajoom_extensions.params`.

### Esquema de parámetros (`param_fields` en manifest.json)

```json
"param_fields": [
    {"name": "position", "type": "select", "label": "Posición", "options": {"top": "Arriba", "bottom": "Abajo"}, "default": "top"},
    {"name": "show_total_votes", "type": "checkbox", "label": "Mostrar total", "default": 0},
    {"name": "limit", "type": "number", "label": "Límite", "default": 10},
    {"name": "texto", "type": "textarea", "label": "Texto"},
    {"name": "titulo", "type": "text", "label": "Título"}
]
```

Tipos soportados: `text`, `textarea`, `number`, `select` (con `options`), `checkbox`.

### Runtime de eventos (`PluginManager`)

Los plugins exponen métodos estilo Joomla (`onContentPrepare`, `onUserAuthenticate`, `onContentAfterSave`...). El dispatcher:

```php
larajoom_plugins()->dispatch('content', 'onContentPrepare', ['com_content.article', &$article, &$params]);
```

- Solo ejecuta plugins `type=plugin` con `enabled=1` del grupo indicado, ordenados por `ordering`.
- La clase se resuelve **parseando el archivo** (`Extension/*.php` o `src/Extension/*.php`) — no exige convención de namespace.
- Cada invocación corre dentro de `SafeRunner` (un plugin roto no afecta al resto ni al sistema).
- Eventos cableados hoy: `onContentPrepare` (artículo en site) y `onContentAfterSave` (store/update en admin).
- Para que un plugin edite params, agregarle `param_fields` a su `manifest.json` (los plugins sin manifest editan JSON crudo).
