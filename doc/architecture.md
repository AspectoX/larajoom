# Arquitectura de LaraJoom

## 1. Visión General
LaraJoom es un componente de Laravel 13 diseñado bajo la arquitectura modular y desacoplada de Joomla 6. Permite que extensiones (componentes, módulos, plugins) coexistan de manera aislada sin comprometer la estabilidad del sistema core.

## 2. Aislamiento y Resiliencia (Circuit Breaker)
Cada extensión se ejecuta a través del `SafeRunner`:
- Si un módulo o plugin lanza una excepción, esta es capturada y registrada en el log (`Log::error`).
- La ejecución del ciclo HTTP de Laravel no se interrumpe y la vista continúa renderizándose.

```php
use Aspectox\LaraJoom\Core\Resilience\SafeRunner;

SafeRunner::execute('mod_custom', function() {
    // Código de la extensión
}, $fallbackHtml);
```

## 3. Autodescubrimiento Dinámico
El `LaraJoomServiceProvider` junto con `ExtensionLoader` escanean en tiempo de arranque:
- Rutas: `{extension}/routes/web.php` (site) y `{extension}/routes/admin.php` (administrador, con prefijo `administrator` automático)
- Vistas: `{extension}/resources/views` y `{extension}/tmpl`
- Migraciones: `{extension}/database/migrations`

## 4. Registro de Idiomas (namespaces)
Los archivos de traducción se registran por extensión con `loadTranslationsFrom()`:

| Namespace | Origen | Ejemplo de uso |
|-----------|--------|----------------|
| `larajoom_admin` | `administrator/language` | `__('larajoom_admin::admin.cancel')` |
| `tmp` | Todos los `administrator/templates/*/language` (manifiesto con `type: "template"`) | `__('tmp::atum.lj_logout')` |
| `mdl` | Módulos con `language/` | `__('mdl::mod_menu.title')` |
| `pgi` | Plugins con `language/` | `__('pgi::plugin.clave')` |
| `{lang_namespace}` del manifiesto | Componentes con `language/` | `__('com_menus::com_menus.menus')` |

**Importante:** los templates viven en `administrator/templates/`, que no está en el listado de extensiones escaneadas del provider; el namespace `tmp` se registra por el bloque específico de templates del `LaraJoomServiceProvider`. Si un template nuevo no traduce, verificar que su `manifest.json` tenga `type: "template"` y que las claves existan en `{template}/language/{en,es}/{nombre}.php`.
