# Plan LaraJoom — Extensiones de Joomla 6 (Completo)

---

## Estructura de carpetas (igual a Joomla y LaraJoom)

Cada extensión tiene su **propia carpeta independiente**, sin anidar módulos ni plugins dentro del componente:

| Tipo | Administrador | Site |
|------|--------------|------|
| Componente | `administrator/components/com_nombre/` | `root/components/com_nombre/` |
| Módulo | `administrator/modules/mod_nombre/` | `root/modules/mod_nombre/` |
| Plugin | `administrator/plugins/plg_nombre/` | `root/plugins/plg_nombre/` |

Cuando un componente tiene módulo y plugins dependientes, se crean junto con el componente, pero **cada uno en su propia carpeta arriba** (no anidados).
Ejemplo: `com_contact` → `administrator/components/com_contact/`, `administrator/modules/mod_contact/`, `administrator/plugins/plg_contact/`.

Por ahora, lo general es crear solo el componente. Si después se necesita el módulo, se crea aparte.

---

## ACL (núcleo)

**El ACL está en el núcleo (`Joomla\CMS\Access`), NO es un componente.**

- Gestiona grupos de usuarios, niveles de acceso y la tabla `#__assets`.
- Todos los componentes, módulos y plugins heredan los mismos grupos/niveles de acceso.
- No hay un "componente de roles"; se administra desde `com_users`, Configuración global y la pestaña Permisos de cada componente.

---

## Ya implementado en LaraJoom

| Tipo | Nombre | Ámbito | Estado |
|------|--------|--------|--------|
| Componente del sistema | `table` | System | CRUD, tabla, search/sort/drag, tree mode |
| Componente del sistema | `toolbar` | System | Botones con `@icon`, submit/links |
| Componente del sistema | `alias` | System | Readonly, auto-slug |
| Componente del sistema | `iconselect` | System | Picker de íconos |
| Componente del sistema | `select` | System | Customizable-select con `@error` |
| Componente | `com_menus` | Administrador | CRUD completo |
| Componente | `com_modules` | Administrador | CRUD completo |
| Módulo | `mod_menu` | Administrador | Menú árbol con `@icon`, detección activa |
| Módulo | `mod_title` | Administrador | Título dinámico `@section('title')` |

---

## Componentes — Administrador (administrator/components/com_*)

| Componente | Módulo | Plugin |
|------------|--------|--------|
| com_ajax | mod_ajax | plg_system_ajax |
| com_associations | — | — |
| com_banners | mod_banners | plg_content_banner, plg_finder_banners, plg_search_banners |
| com_cache | mod_cache | plg_system_cache |
| com_categories | mod_categories | plg_fields_categories, plg_content_categories, plg_finder_categories, plg_search_categories |
| com_checkin | — | — |
| com_contact | mod_contact, mod_contact_latest | plg_content_contact, plg_fields_contact, plg_finder_contact, plg_search_contact |
| com_content | mod_articles, mod_articles_archive, mod_articles_categories, mod_articles_category, mod_articles_latest, mod_articles_news, mod_articles_popular | plg_content_pagebreak, plg_content_approvedby, plg_content_archive, plg_content_fields, plg_content_joomla, plg_content_loadmodule, plg_content_pagenavigation, plg_content_vote, plg_fields_article, plg_finder_content, plg_search_content |
| com_contenthistory | — | — |
| com_fields | mod_fields | plg_fields_*, plg_content_fields |
| com_finder | mod_finder, mod_finder_filter | plg_finder_*, plg_search_* |
| com_installer | mod_installer | plg_installer_* |
| com_joomlaupdate | mod_joomlaupdate | plg_system_joomlaupdate, plg_quickicon_joomlaupdate |
| com_languages | mod_languages, mod_languages_admin | plg_system_languagecode, plg_system_langfilter, plg_system_languages |
| com_login | mod_login | plg_authentication_joomla, plg_authentication_ldap, plg_user_joomla, plg_user_profile |
| com_mails | mod_mails | plg_mails_* |
| com_messages | mod_messages, mod_messages_site | plg_system_messages |
| com_media | mod_media | plg_media-action_crop, plg_media-action_resize, plg_media-action_rotate |
| com_menus | mod_menu, mod_breadcrumbs | plg_system_menu, plg_menu_* |
| com_modules | todos los mod_* | plg_system_module, plg_module_* |
| com_newsfeeds | mod_feed, mod_syndicate | plg_content_newsfeeds, plg_finder_newsfeeds, plg_search_newsfeeds |
| com_privacy | mod_privacy_dashboard, mod_privacy_status | plg_privacy_* |
| com_redirect | mod_redirect | plg_redirect_* |
| com_scheduler | mod_scheduler | plg_system_schedulerunner, plg_task_* |
| com_tags | mod_tags_popular, mod_tags_similar | plg_content_tags, plg_fields_tags, plg_finder_tags, plg_search_tags |
| com_users | mod_login, mod_whosonline, mod_user | plg_user_*, plg_authentication_* |
| com_wrapper | mod_wrapper | plg_wrapper_* |
| com_workflow | mod_workflow | plg_workflow_* |
| com_guidedtours | mod_guidedtours | plg_system_guidedtours |

---

## Componentes — Site (site/components/com_*)

| Componente | Módulo | Plugin |
|------------|--------|--------|
| com_ajax | mod_ajax | plg_system_ajax |
| com_banners | mod_banners | plg_content_banner, plg_finder_banners, plg_search_banners |
| com_config | — | plg_system_config |
| com_contact | mod_contact, mod_contact_latest | plg_content_contact, plg_fields_contact, plg_finder_contact, plg_search_contact |
| com_content | mod_articles, mod_articles_archive, mod_articles_categories, mod_articles_category, mod_articles_latest, mod_articles_news, mod_articles_popular | plg_content_pagebreak, plg_content_approvedby, plg_content_archive, plg_content_fields, plg_content_joomla, plg_content_loadmodule, plg_content_pagenavigation, plg_content_vote, plg_fields_article, plg_finder_content, plg_search_content |
| com_contenthistory | — | — |
| com_fields | mod_fields | plg_fields_*, plg_content_fields |
| com_finder | mod_finder, mod_finder_filter | plg_finder_*, plg_search_* |
| com_media | mod_media | plg_media-action_* |
| com_menus | mod_menu, mod_breadcrumbs | plg_system_menu, plg_menu_* |
| com_modules | todos los mod_* | plg_system_module, plg_module_* |
| com_newsfeeds | mod_feed, mod_syndicate | plg_content_newsfeeds, plg_finder_newsfeeds, plg_search_newsfeeds |
| com_privacy | — | plg_privacy_* |
| com_tags | mod_tags_popular, mod_tags_similar | plg_content_tags, plg_fields_tags, plg_finder_tags, plg_search_tags |
| com_users | mod_login, mod_whosonline, mod_user | plg_user_*, plg_authentication_* |
| com_wrapper | mod_wrapper | plg_wrapper_* |
| com_workflow | mod_workflow | plg_workflow_* |

---

## Plugins (sin componente principal)

Cada plugin en su **propia carpeta `plg_nombre`** (no agrupados):

| Grupo | Plugins |
|-------|---------|
| authentication | plg_authentication_joomla, plg_authentication_ldap, plg_authentication_gssapi, plg_authentication_kerberos, plg_authentication_oauth2 |
| behaviour | plg_behaviour_compat6 |
| captcha | plg_captcha_* |
| editors | plg_editors_none, plg_editors_tinymce, plg_editors_codemirror |
| editors-xtd | plg_editors-xtd_image, plg_editors-xtd_article, plg_editors-xtd_module, plg_editors-xtd_page, plg_editors-xtd_readmore |
| extension | plg_extension_joomla, plg_extension_local |
| filesystem | plg_filesystem_* |
| installer | plg_installer_folderinstaller, plg_installer_packageinstaller, plg_installer_urlinstaller, plg_installer_webinstaller |
| media-action | plg_media-action_crop, plg_media-action_resize, plg_media-action_rotate |
| multifactorauth | plg_multifactorauth_email, plg_multifactorauth_fixed, plg_multifactorauth_totp, plg_multifactorauth_webauthn, plg_multifactorauth_yubikey |
| privacy | plg_privacy_* |
| quickicon | plg_quickicon_autoupdate, plg_quickicon_eos, plg_quickicon_extensionupdate, plg_quickicon_joomlaupdate, plg_quickicon_overridecheck, plg_quickicon_privacycheck |
| sampledata | plg_sampledata_* |
| schemaorg | plg_schemaorg_* |
| system | plg_system_debug, plg_system_guidedtours, plg_system_jooa11y, plg_system_shortcut, plg_system_stats, plg_system_webauthn, plg_system_redirect, plg_system_log, plg_system_remember, plg_system_sef, plg_system_cache, plg_system_languagefilter, plg_system_languagecode, plg_system_fields, plg_system_highlight, plg_system_logout, plg_system_p3p, plg_system_profiler, plg_system_remoteaddr |
| task | plg_task_* |
| user | plg_user_joomla, plg_user_profile, plg_user_contactcreator, plg_user_terms |
| webservices | plg_webservices_* |
| workflow | plg_workflow_* |

---

## Plan de implementación por prioridad

### Prioridad 0 — Creación de componente (regla general)
- Al crear cualquier componente, preguntar al usuario si hay que crear seeders con datos por defecto (`database/seeders/`).
- Si dice sí, incluir `LaraJoom*Seeder.php` y agregar a `database/seeders/` según corresponda.
- No asumir datos por defecto sin confirmación.

### Prioridad 1 — Layout base genérico (Nº 1)
- Cambiar `@extends('larajoom_atum::index')` → `@extends('admin_tpl::index')` en **todas** las vistas de componentes y módulos. y en site `@extends('site_tpl::index')`
- `tpl::` es un namespace genérico que funciona con **cualquier template**, no solo Atum.
- Cada template define su propio `index.blade.php`
- Corregir lo ya hecho (com_menus, com_modules, mod_menu, mod_title).

### Prioridad 2 — ACL y seguridad de acceso (Joomla 6)
- El ACL está en el núcleo (`Joomla\CMS\Access`), no es un componente.
- Verificar que `larajoom_assets`, grupos de usuarios, niveles de acceso y `larajoom_usergroups` funcionen.
- Todas las extensiones (componentes, módulos, plugins) heredan los mismos grupos/niveles de acceso del núcleo.
- Control de acceso por acción en vistas y controladores (como Joomla 6).
- **Nota:** No existe aún el componente ACL; debe implementarse en el núcleo siguiendo el estándar de Joomla 6.

### Prioridad 3 — Seguridad de páginas y acceso general
- Protección de rutas del administrador (middleware de auth y ACL).
- Autorización a nivel de elemento (artículo, categoría, módulo, etc.).
- Tokens CSRF en formularios, validación de entradas, escapado de salidas.
- Permisos por grupo de usuario en cada vista y acción.

### Prioridad 4 — Título de página por extensión
- Helper `larajoom_extension_param(string $element, string $key, mixed $default)` que lee `larajoom_extensions.params` / `larajoom_modules.params`.
- Params Joomla: `show_page_heading` + `page_heading` (nombres iguales a Joomla).
- Condicional `@if` en vistas en lugar de `<h2>` siempre visible.
- Los params se guardan en la columna `params` (json) de `larajoom_extensions` o `larajoom_modules`, nunca en otro JSON.

### Prioridad 5 — Módulos esenciales del template Atum
- `mod_custom`, `mod_languages`, `mod_breadcrumbs`, `mod_footer`, `mod_status`, `mod_quickicon`

### Prioridad 6 — Componentes base (administrador y site)
- `com_contact` (+ mod_contact, plg_content_contact, plg_fields_contact)
- `com_fields` (+ mod_fields, plg_fields_*)
- `com_finder` (+ mod_finder, plg_finder_*, plg_search_*)
- `com_content` (+ mod_articles*, plg_content_*)
- `com_privacy` (+ plg_privacy_*)
- `com_config` (site)

### Prioridad 7 — Plugins esenciales
- `plg_content_vote`, `plg_system_debug`, `plg_quickicon_extensionupdate`, `plg_installer_*`

### Prioridad 6 — Componentes faltantes (ya listados en secciones 2 y 3)
Crear los que aún no existen: `com_media`, `com_finder`, `com_installer`, `com_joomlaupdate`, `com_workflow`, `com_contenthistory`, `com_banners`, `com_tags`, `com_contact`, `com_redirect`, `com_privacy`, `com_scheduler`, `com_guidedtours`, `com_ajax`, `com_fields`, `com_users`, `com_config`, etc., siguiendo la estructura de carpetas y las reglas de `componentes.md`.

---

## Notas de arquitectura

- Cada extensión guardada en su carpeta propia (componente, módulo o plugin) siguiendo exactamente la misma estructura que en Joomla.
- Cuando un componente tiene módulo y plugins dependientes, se crean junto con el componente, pero cada uno en su propia carpeta arriba.
- El ACL está en el núcleo (`Joomla\CMS\Access`), no es un componente.
- Los módulos y plugins de un componente pueden instalarse/activarse/desactivarse de forma independiente.
