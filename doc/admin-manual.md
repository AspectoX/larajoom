# Manual del Administrador — LaraJoom

## Acceso
- URL: `/administrator` — si no hay sesión, redirige a `/administrator/login` (formulario de `com_login`).
- El POST de login se acepta en `/administrator` y en `/administrator/login` (alias).
- Tras un login correcto se redirige al dashboard `/administrator`.
- Login con `com_users` (autenticación local `plg_authentication_joomla`).
- Logout vía POST `administrator/logout` (nombre de ruta `administrator.logout`).

## Funciones principales

### Contenido (`com_content`)
- Lista de artículos (`/administrator/content`).
- Crear, editar, eliminar (CRUD completo con vistas Blade sin CSS ni `style=`).
- Asignación de categorías (`larajoom_categories`).

### Usuarios (`com_users`)
- Gestión de usuarios (`/administrator/users`).
- Grupos de acceso jerárquicos (`larajoom_usergroups`).
- Niveles de acceso (`larajoom_viewlevels`).
- Perfil extendido (`avatar`, `address`, `phone`, etc.).

### Configuración (`com_config`)
- `/administrator/config`
- Editar los ~56 valores de la tabla `larajoom_configuration` (cache, sitio, sistema, debug, permisos, mail).
- Los cambios se reflejan inmediatamente vía `ConfigLoader` en el core.

### Módulos (`com_modules`)
- Asignar módulos a posiciones (`top`, `right`, `footer`, etc.).
- Ordenar por prioridad (`ordering`).

## Posiciones de módulos
Usar `@position('nombre')` en Blade (template Atum).

## Extensiones
- Plugins: `authentication` (joomla/ldap/cookie), `api-authentication` (basic/token), `user/profile`.
- Componentes: `com_content`, `com_users`, `com_menus`, `com_config`, `com_modules`, `com_installer`.
- **No portado:** `com_ajax` — innecesario en Laravel (las extensiones exponen endpoints con sus propias rutas + `SafeRunner`).
- Todos operan sin bloquear el sistema por fallos (resiliencia del core).
