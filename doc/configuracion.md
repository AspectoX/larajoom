# Auditoría de configuración global (LaraJoom)

> Estado: 2026-10-20 · Ámbito: `administrator/components/com_config` + `src/Core/ConfigLoader.php`

## 1. Diagnóstico actual

Hoy toda la configuración vive en **una sola tabla EAV** (`larajoom_configuration`: `name`, `value`, `group`).
El flujo es:

1. `LaraJoomServiceProvider::boot()` → `ConfigLoader::load()` → **1 SELECT** de toda la tabla.
2. Merge con los defaults del archivo de config (`config/larajoom.php`) → `config()->set('larajoom', $merged)`.
3. En el resto del request, todo se lee de memoria: `config('larajoom.offline')`.
4. `ConfigController::save()` escribe filas en la tabla → el valor nuevo aplica en el **request siguiente**.

**Lo que funciona bien:** el mecanismo de carga (1 query por request, todo en memoria), la persistencia y el guardado.

**Problemas:**

| # | Problema | Consecuencia |
|---|---|---|
| 1 | **Todo es `string`** (`value` es TEXT) | Cada consumidor castea a mano: `(int) config('larajoom.offline', 0) === 1`. Los booleanos se comparan con `==` porque `'0' == false` es `true` en PHP. |
| 2 | **EAV genérico** | Buscar un valor = scan por `name`; sin tipos, sin defaults, sin validación, sin índices útiles. |
| 3 | **Campos de Laravel mezclados con campos del CMS** | `session_*`, `cache_*`, `mail*`/`smtp*`, `debug`, `cors*`, `proxy_*`, `error_reporting`, `timezone` son de Laravel: deberían vivir en `config/*.php` + `.env`, no en una tabla del CMS. |
| 4 | **Campos obsoletos** | `dbtype`, `dbprefix` (heredados de Joomla), `timezone` (duplica `offset`). |
| 5 | **Sin caché** | 1 query en cada request, incluso en requests que no usan config. |
| 6 | **Opciones inertes** | 70 de 84 opciones se guardan pero **nadie las lee** (ver §4). |

## 2. Diseño objetivo

### 2.1 Criterio de ubicación

| Tipo de opción | Dónde debe vivir | Por qué |
|---|---|---|
| **Ajuste de Laravel** (sesión, cache, mail, CORS, proxy, debug, timezone, error_reporting, force_ssl) | `config/*.php` + `.env` de la app | Laravel ya los lee desde ahí; guardarlos en una tabla del CMS obliga a "empujar" los valores en cada boot. Se pueden **seguir editando desde el panel**, pero aplicándolos al config en el boot. |
| **Ajuste de LaraJoom** (sitio, metadata, SEO, filtros de texto, offline) | **Tablas propias por dominio, con columnas tipadas** (una fila por dominio) | Búsqueda directa por columna, tipos nativos, defaults en la migración, sin casts manuales. |
| **Ajuste de un componente** (parámetros de módulos/plugins/artículos) | Tabla del componente (`larajoom_modules`, `larajoom_plugins`, …) | Ya existe; no va en la config global. |
| **Obsoleto** | — | Se elimina del formulario, del seeder y del registro. |

### 2.2 Tablas por dominio (ajustes de LaraJoom)

Una tabla por dominio, **una sola fila**, con columnas tipadas:

| Tabla | Columnas (tipo) |
|---|---|
| `larajoom_site_settings` | `sitename` varchar, `offline` bool, `display_offline_message` tinyint, `offline_message` text, `offline_image` varchar, `frontediting` tinyint, `editor` varchar, `captcha` varchar, `access` tinyint, `list_limit` int, `feed_limit` int, `feed_email` varchar, `cookie_domain` varchar, `cookie_path` varchar |
| `larajoom_meta_settings` | `meta_desc` text, `robots` varchar, `meta_rights` varchar, `meta_author` bool |
| `larajoom_seo_settings` | `sef` bool, `sef_rewrite` bool, `sef_suffix` bool, `unicodeslugs` bool, `sitename_pagetitles` tinyint |
| `larajoom_text_filters` | `filter_type` varchar, `filter_tag` varchar, `filter_attributes` text, `published` bool (ya existe como JSON → pasa a filas) |

Los dominios que **son de Laravel** (`cache`, `session`, `mail`, `proxy`, `debug`, `cors`, `server`) se guardan en
`larajoom_settings` (tabla `key/value` tipada) **solo como override del panel**, y el boot los aplica a `config()`.

### 2.3 Capa de acceso

Nueva clase `src/Core/Settings/Settings.php` + registro canónico `src/Core/Settings/Registry.php`:

```php
Settings::bool('site.offline');        // typed, sin casts
Settings::int('site.list_limit');
Settings::string('meta.description');
Settings::all();                        // array plano por dominio
```

- **Carga**: 1 query (unión de las tablas de LaraJoom) por request → memoria. Cache de Laravel opcional con invalidación en el guardado.
- **Escritura**: `Settings::save('site', ['offline' => 1, ...])` con cast según el registro.
- **Compatibilidad**: `config('larajoom.<clave>')` se sigue soportando (mapa plano) para no romper el código existente.

### 2.4 Registro canónico (fuente de verdad)

`Registry.php` define, para cada opción: `key`, `dominio`, `columna`, `tipo`, `default`, `grupo/pestaña`, `label`, `consumidor`, `estado`.
El registro es lo que genera: el formulario, el seeder, el guardado, la auditoría y los tests.

## 3. Inventario: qué cuenta cada opción y dónde debería estar

### 3.1 Sitio — `larajoom_site_settings`

| Clave actual | Tipo | Default | Consumidor | Dónde debería estar | Estado |
|---|---|---|---|---|---|
| `sitename` | string | `LaraJoom` | `com_admin/Controllers/AdminController.php:21`; falta en el front | tabla `site` | funcional (parcial) |
| `offline` | bool | `0` | **ninguno** | tabla `site` + middleware | **inerte** |
| `display_offline_message` | int | `1` | **ninguno** | tabla `site` + vista offline | **inerte** |
| `offline_message` | text | `''` | **ninguno** | tabla `site` + vista offline | **inerte** |
| `offline_image` | varchar | `''` | **ninguno** | tabla `site` + vista offline | **inerte** |
| `frontediting` | int | `1` | **ninguno** | tabla `site` (o borrar si no hay edición frontal) | inerte |
| `editor` | varchar | `tinymce` | **ninguno** | `config/editor.php` de la app | inerte |
| `captcha` | varchar | `0` | **ninguno** | tabla `site` (login/registro) | inerte |
| `access` | int | `1` | **ninguno** | tabla `site` (ACL del front) | inerte |
| `list_limit` | int | `10` | `com_users`, `com_installer`, `com_content`, `com_modules` | tabla `site` | **funcional** |
| `feed_limit` | int | `10` | **ninguno** (no hay feeds) | tabla `site` | inerte |
| `feed_email` | varchar | `none` | **ninguno** | tabla `site` | inerte |
| `cookie_domain` | varchar | `''` | **ninguno** | `config/session.php` (`cookie`/`domain`) | inerte |
| `cookie_path` | varchar | `/` | **ninguno** | `config/session.php` (`cookie`/`path`) | inerte |

### 3.2 Metadatos — `larajoom_meta_settings`

| Clave actual | Tipo | Default | Consumidor | Dónde debería estar | Estado |
|---|---|---|---|---|---|
| `MetaDesc` | text | `''` | **ninguno** (el `<head>` del front no tiene meta description) | tabla `meta` + `<head>` del front | **inerte** |
| `robots` | varchar | `index, follow` | **ninguno** | tabla `meta` + `<head>` del front | **inerte** |
| `MetaRights` | varchar | `''` | **ninguno** | tabla `meta` + `<head>` del front | **inerte** |
| `MetaAuthor` | bool | `1` | **ninguno** | tabla `meta` + `<head>` del front | **inerte** |

### 3.3 SEO — `larajoom_seo_settings`

| Clave actual | Tipo | Default | Consumidor | Dónde debería estar | Estado |
|---|---|---|---|---|---|
| `sef` | bool | `1` | **ninguno** | tabla `seo` + router | inerte |
| `sef_rewrite` | bool | `0` | **ninguno** | tabla `seo` + router | inerte |
| `sef_suffix` | bool | `0` | **ninguno** | tabla `seo` + router | inerte |
| `unicodeslugs` | bool | `0` | **ninguno** | tabla `seo` + generador de slugs | inerte |
| `sitename_pagetitles` | int | `0` | `<title>` del admin (atum `index.blade.php`) | tabla `seo` + `<title>` del front | funcional (solo admin) |

### 3.4 Ajustes de Laravel (override desde el panel)

| Clave actual | Tipo | Default | Se aplica a | Estado |
|---|---|---|---|---|
| `caching` | int | `2` | `config/cache.php` → modo | inerte |
| `cache_handler` | varchar | `file` | `config/cache.php` → `default` | inerte |
| `cache_platformprefix` | bool | `0` | `config/cache.php` → `prefix` | inerte |
| `cachetime` | int | `15` | `config/cache.php` → `ttl` | inerte |
| `cache_path` | varchar | `cache` | `config/cache.php` → path | inerte |
| `memcached_*` / `redis_*` | var. | — | `config/database.php` | inerte |
| `debug` | bool | `0` | `config/app.php` → `debug` | inerte |
| `debug_lang` / `debug_lang_const` | bool | `0` | depuración de idioma | inerte |
| `cors` | bool | `0` | `config/cors.php` | inerte |
| `cors_allow_origin/headers/methods` | varchar | — | `config/cors.php` | inerte |
| `behind_loadbalancer` | bool | `0` | `TrustProxies` | inerte |
| `proxy_enable/host/port/user/pass` | var. | — | `TrustProxies` | inerte |
| `offset` | varchar | `UTC` | `config/app.php` → `timezone` | inerte |
| `mailonline` | bool | `1` | bloqueo de envío si el sitio está offline | inerte |
| `massmailoff` | bool | `0` | bloqueo de mail masivo | inerte |
| `mailfrom`/`fromname`/`replyto`/`replytoname` | varchar | — | `config/mail.php` | inerte |
| `mailer`/`sendmail`/`smtphost`/`smtpport`/`smtpsecure`/`smtpauth`/`smtpuser`/`smtppass` | var. | — | `config/mail.php` | inerte |
| `tmp_path` | varchar | `''` | `config/filesystems.php` → temp | inerte |
| `gzip` | bool | `0` | compresión de salida (middleware) | inerte |
| `error_reporting` | varchar | `default` | `error_reporting()` | inerte |
| `force_ssl` | int | `-1` | `URL::forceScheme()` | inerte |
| `session_handler` | varchar | `filesystem` | `config/session.php` → `driver` | inerte |
| `session_filesystem_path` | varchar | `''` | `config/session.php` | inerte |
| `session_memcached_*` / `session_redis_*` | var. | — | `config/session.php` / `database.php` | inerte |
| `lifetime` | int | `15` | `config/session.php` → `lifetime` | inerte |
| `shared_session` | bool | `0` | sesiones compartidas entre sitios | inerte |
| `session_metadata` / `session_metadata_for_guest` | bool | `1` | metadatos de sesión | inerte |
| `log_path` | varchar | `''` | `config/logging.php` → path | inerte |
| `log_everything` / `log_deprecated` | bool | `0` | niveles de log | inerte |
| `log_priorities` | varchar | `all` | niveles permitidos | inerte |
| `log_categories` / `log_category_mode` | var. | — | filtro por categoría | inerte |

### 3.5 Filtros de texto

| Clave actual | Tipo | Default | Consumidor | Dónde debería estar | Estado |
|---|---|---|---|---|---|
| `filters` | JSON | `''` | `ConfigController::getFilters()` (matriz de permisos de filtrado) | tabla `larajoom_text_filters` (filas) | funcional (parcial) |

### 3.6 Obsoletos (a eliminar)

| Clave | Motivo |
|---|---|
| `dbtype` | de Joomla 1.x; la DB la define Laravel (`.env`) |
| `dbprefix` |idem |
| `timezone` | duplica `offset` (group `system`) |
| `MetaVersion` | ya eliminado del formulario (decisión propia) |

## 4. Resumen

- **84** opciones en total.
- **Funcionales**: `list_limit`, `sitename` (parcial), `filters` (parcial), `sitename_pagetitles` (solo admin) → **4**
- **Inertes** (se guardan, nadie las lee): **~68**
- **Obsoletas**: `dbtype`, `dbprefix`, `timezone`, `MetaVersion` → **4**
- Resto: opciones de Laravel que hoy no tienen consumidor → **8**

## 5. Plan de implementación (por etapas)

1. **Registro + capa `Settings`** (tipos, defaults, carga única, escritura tipada) + migración que crea las tablas por dominio
   y **migra los valores actuales** desde `larajoom_configuration` (no se pierde nada).
2. **`ConfigController::save()`** escribe en las tablas nuevas (dejando la tabla vieja como respaldo/legacy).
3. **Bootstrap**: aplicar los overrides de Laravel (sesión, cache, mail, CORS, proxy, debug, timezone, error_reporting, force_ssl).
4. **Modo offline**: middleware + vista de "sitio fuera de línea" (`offline`, `offline_message`, `offline_image`).
5. **Metadatos del front**: `<meta description/robots/author/rights>` + `sitename_pagetitles` en el `<title>` del sitio.
6. **SEF**: rewriting de URLs con `sef`, `sef_rewrite`, `sef_suffix`; `unicodeslugs` en la generación de slugs.
7. **Limpieza**: retirar `dbtype`, `dbprefix`, `timezone`, `MetaVersion`; cache de la config con invalidación en el guardado.
