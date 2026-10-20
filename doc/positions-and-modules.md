# Sistema de Posiciones y Módulos

## 1. Definición de Posiciones en Blade
Para declarar una posición dentro de un layout o vista de template, se utiliza la directiva `@position`:

```blade
<header>
    @position('top')
</header>

<main>
    <!-- Contenido del componente -->
</main>

<aside>
    @position('right')
</aside>

<footer>
    @position('footer')
</footer>
```

## 2. Asignación de Módulos
Los módulos se configuran en la tabla `larajoom_modules`:
- `module`: Identificador del módulo (ej. `mod_menu`).
- `position`: Posición asignada (ej. `top`, `right`, `footer`).
- `published`: Estado de publicación (1 = activo, 0 = inactivo).
- `ordering`: Orden de renderizado ascendente.
