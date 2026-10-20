# PLAN-CONFIG.md — Plan de configuración de LaraJoom

## 0. Principio rector

Cada opción global se clasifica por **ámbito**:

| Ámbito      | Significado                            | Dónde vive                        | Cómo se consume                |
| ----------- | -------------------------------------- | --------------------------------- | ------------------------------ |
| **SITE**    | Solo afecta al sitio público (front)   | `config('larajoom.site.*')`       | Middleware/vistas del front    |
| **ADMIN**   | Solo afecta al panel de administración | `config('larajoom.admin.*')`      | Controladores/vistas del admin |
| **BOTH**    | Afecta a site y admin                  | `config('larajoom.core.*')`       | Ambos                          |
| **LARAVEL** | Es un ajuste del framework, no del CMS | `config/*.php` + `.env` de la app | Laravel (aplicado en el boot)  |

> Regla: si una opción es **LARAVEL**, no se guarda en la tabla del CMS: se edita desde el panel
> pero se aplica al `config()` de la app en el boot. Si es SITE/ADMIN/BOTH, se guarda en la tabla
> tipada con la columna `scope`.

## 1. Clasificación de las 84 opciones

### SITE (solo front)

| Opción                              | Tipo   | Default       | Se usa en                                           |
| ----------------------------------- | ------ | ------------- | --------------------------------------------------- |
| `offline`                           | bool   | 0             | Middleware de sitio en mantenimiento                |
| `display_offline_message`           | int    | 1             | Pantalla offline (0=oculta, 1=propio, 2=idioma)     |
| `offline_message`                   | text   | —             | Pantalla offline                                    |
| `offline_image`                     | string | —             | Pantalla offline                                    |
| `frontediting`                      | int    | 1             | Edición frontal de artículos                        |
| `captcha`                           | string | 0             | Registro/recaptcha del front                        |
| `access`                            | int    | 1             | Nivel de acceso por defecto del front               |
| `feed_limit`                        | int    | 10            | Feeds del front                                     |
| `feed_email`                        | string | none          | Autor/email en los feeds                            |
| `cookie_domain`                     | string | —             | Cookies del front (`config/session.php` → `domain`) |
| `cookie_path`                       | string | /             | Cookies del front (`config/session.php` → `path`)   |
| `MetaDesc`                          | text   | —             | `<meta name="description">` del front               |
| `robots`                            | string | index, follow | `<meta name="robots">` del front                    |
| `MetaRights`                        | string | —             | `<meta name="copyright">` del front                 |
| `MetaAuthor`                        | bool   | 1             | `<meta name="author">` del front                    |
| `sef`                               | bool   | 1             | Router SEF del front                                |
| `sef_rewrite`                       | bool   | 0             | Reescritura de URLs del front                       |
| `sef_suffix`                        | bool   | 0             | Sufijo `.html` en URLs del front                    |
| `cors`                              | bool   | 0             | `config/cors.php` (API/webservices)                 |
| `cors_allow_origin/headers/methods` | string | —             | `config/cors.php`                                   |

### ADMIN (solo panel)

| Opción   | Tipo   | Default | Se usa en                                           |
| -------- | ------ | ------- | --------------------------------------------------- |
| `editor` | string | tinymce | Editor por defecto del panel (edición de contenido) |

### BOTH (site + admin)

| Opción                                                                                                       | Tipo   | Default  | Se usa en                                                      |
| ------------------------------------------------------------------------------------------------------------ | ------ | -------- | -------------------------------------------------------------- |
| `sitename`                                                                                                   | string | LaraJoom | Título del front, `<meta author>`, nombre del panel            |
| `sitename_pagetitles`                                                                                        | int    | 0        | `<title>` del front y del admin                                |
| `unicodeslugs`                                                                                               | bool   | 0        | Slugs de artículos/menús (se editan en admin, se usan en site) |
| `list_limit`                                                                                                 | int    | 10       | Paginación (admin) y listados del front                        |
| `filters`                                                                                                    | json   | —        | Filtros de texto (editor admin + búsqueda del front)           |
| `caching` / `cache_handler` / `cache_platformprefix` / `cachetime` / `cache_path`                            | var.   | —        | `config/cache.php` (ambos lados)                               |
| `memcached_*` / `redis_*`                                                                                    | var.   | —        | `config/database.php` (ambos lados)                            |
| `debug`                                                                                                      | bool   | 0        | `config/app.php` → `debug`                                     |
| `debug_lang` / `debug_lang_const`                                                                            | bool   | —        | Depuración de idioma (ambos lados)                             |
| `behind_loadbalancer` / `proxy_*`                                                                            | var.   | —        | `TrustProxies` (ambos lados)                                   |
| `offset`                                                                                                     | string | UTC      | `config/app.php` → `timezone`                                  |
| `mailonline`                                                                                                 | bool   | 1        | Bloquea el envío si el site está offline                       |
| `massmailoff`                                                                                                | bool   | 0        | Bloquea el mail masivo                                         |
| `mailfrom` / `fromname` / `replyto` / `replytoname`                                                          | string | —        | `config/mail.php` (notificaciones de ambos lados)              |
| `mailer` / `sendmail` / `smtphost` / `smtpport` / `smtpsecure` / `smtpauth` / `smtpuser` / `smtppass`        | var.   | —        | `config/mail.php`                                              |
| `tmp_path`                                                                                                   | string | —        | `config/filesystems.php` → temp                                |
| `gzip`                                                                                                       | bool   | 0        | Compresión de salida (ambos lados)                             |
| `error_reporting`                                                                                            | string | default  | `error_reporting()`                                            |
| `force_ssl`                                                                                                  | int    | -1       | `URL::forceScheme()` (-1=no, 0=admin, 1=site, 2=todo)          |
| `session_handler` / `session_filesystem_path` / `session_memcached_*` / `session_redis_*`                    | var.   | —        | `config/session.php` (sesión del front y del panel)            |
| `lifetime`                                                                                                   | int    | 15       | `config/session.php` → `lifetime`                              |
| `shared_session`                                                                                             | bool   | 0        | Comparte la sesión entre site y admin                          |
| `session_metadata` / `session_metadata_for_guest`                                                            | bool   | 1        | Metadatos de sesión (ambos lados)                              |
| `log_path` / `log_everything` / `log_deprecated` / `log_priorities` / `log_categories` / `log_category_mode` | var.   | —        | `config/logging.php` (ambos lados)                             |

### LARAVEL (ajuste del framework, no del CMS)

Se edita desde el panel, pero **no se guarda en la tabla del CMS**: se aplica al `config()` en el boot.
`cors*`, `proxy_*`, `session_*`, `cache_*`, `mail*`/`smtp*`, `debug`, `offset`, `error_reporting`, `force_ssl`, `gzip`, `tmp_path`, `log_*`.

### OBSOLETAS (a eliminar del seeder y del registro)

`dbtype`, `dbprefix` (Joomla 1.x), `timezone` (duplica `offset`), `MetaVersion` (ya fuera del formulario).

### HUÉRFANAS (en el seeder, sin campo en el formulario)

`log_channel`, `dbtype`, `dbprefix`, `timezone` → o se agrega el campo, o se eliminan.

## 2. Dónde debe estar cada dato (almacenamiento)

larajoom_settings (tabla tipada, única, con columna scope)
├── name (unique, index) clave canónica
├── scope (site|admin|both) NEW → define el ámbito
├── group (pestaña del panel: site, metadata, seo, cache, debug, mail, server, session, logging, webservices, proxy, locale, filters)
├── type (bool|int|string|text|json)
├── value_string / value_int / value_bool / value_text / value_json
└── timestamps
larajoom_text_filters (filas, no JSON) → filter_type, filter_tag, filter_attributes, published

Claves canónicas (mapa `scope` → clave de config):

- SITE → `config('larajoom.site.<clave>')`
- ADMIN → `config('larajoom.admin.<clave>')`
- BOTH → `config('larajoom.core.<clave>')`
- Se mantiene `config('larajoom.<clave>')` como alias compatible durante la transición.

## 3. Fases

**Fase 0 — Correcciones urgentes (bloquean la app)**

- `session_handler`: mapear `filesystem → file`, `database → database`, `redis → redis`, `memcached → memcached`.
- `mailer`: mapear `mail → smtp`, `sendmail → sendmail`.
- `ConfigLoader::get()`: envolver la query legacy en try/catch.
- Migración `larajoom_settings`: copiar los valores actuales desde `larajoom_configuration` (sin perder nada) y sembrar los defaults.

**Fase 1 — Scope en el modelo**

- Agregar columna `scope` + registrar el ámbito de las 84 opciones.
- `Registry` como fuente de verdad (ya existe) + `Settings` con getters por scope:
  `Settings::site('offline')`, `Settings::admin('editor')`, `Settings::core('sitename')`.

**Fase 2 — Conectar lo SITE (front)**

- Modo offline (middleware + pantalla) — ya hecho, falta validar.
- Metadatos del front (`<x-larajoom::seo>`) — ya hecho, falta validar.
- SEF: `sef`, `sef_rewrite`, `sef_suffix` en el router; `unicodeslugs` en los slugs.

**Fase 3 — Conectar lo ADMIN**

- `editor` → editor por defecto del panel.
- `sitename_pagetitles` → `<title>` del panel (ya hecho, ahora con el nombre de com_cpanel).
- `list_limit` (ya funcional), `filters` (ya funcional).

**Fase 4 — Conectar lo BOTH**

- Overrides de Laravel en el boot: `cache_*`, `session_*`, `mail*`/`smtp*`, `debug`, `offset`, `error_reporting`, `force_ssl`, `gzip`, `proxy_*`, `cors*`, `log_*`.
- `shared_session`: cookie de sesión compartida entre site y admin.

**Fase 5 — Limpieza**

- Eliminar `dbtype`, `dbprefix`, `timezone`, `MetaVersion`.
- Decidir `log_channel` (agregar campo o eliminar).
- Filtros de texto: pasar de JSON a filas.
- Caché de la config (`Cache::remember`) con invalidación en el guardado.

## 4. Pendiente de decisión

- ¿El panel y el front comparten la misma sesión o son independientes? (define `shared_session` y `session_*`).
- ¿Los ajustes LARAVEL se editan desde el panel o solo por `.env`? (hoy el panel los ofrece).
