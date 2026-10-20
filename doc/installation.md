# Manual de Instalación — LaraJoom (aspectox/larajoom)

## Requisitos
- PHP ^8.2
- Laravel 13 (o 11/12 compatible)
- Composer 2.8+

## Instalación paso a paso

1. Copiar el componente en `packages/larajoom` o instalar vía Composer local.
2. Agregar `composer.json` con el repositorio `path` a `packages/*`.
3. Ejecutar `composer update` para registrar `aspectox/larajoom`.
4. Registrar `Aspectox\LaraJoom\LaraJoomServiceProvider` en `config/app.php` o auto-descubrir.
5. Ejecutar `php artisan migrate` para crear las tablas del core (`larajoom_configuration`, `larajoom_extensions`, `larajoom_content`, `larajoom_users_acl`, etc.).
6. Ejecutar `php artisan db:seed --class=LaraJoomConfigSeeder` para cargar los 56 valores por defecto de configuración.
7. Copiar `media/system/css/*.css` si se desea usar los estilos del sistema.
8. Acceder al panel de administración: `/administrator`

## Notas importantes
- No se usa XML para la configuración (solo DB + `config/larajoom.php`).
- El componente soporta Livewire y Alpine.js para interactividad sin CSS obligatorio.
- El core es resiliente: cualquier error en un plugin o módulo no bloquea el sistema.
