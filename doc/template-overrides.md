# Template Overrides (Sobrescritura de Vistas)

## 1. Funcionamiento
LaraJoom replica el sistema de Overrides de Joomla. Si un template define una vista personalizada dentro de su subcarpeta `html/`, esta tiene precedencia sobre la vista por defecto de la extensión.

## 2. Convención de Carpetas
Para sobrescribir una vista de un componente o módulo:

```text
templates/
└── [nombre-template]/
    └── html/
        ├── com_content/
        │   └── article.blade.php    # Reemplaza la vista del artículo
        └── mod_menu/
            └── default.blade.php    # Reemplaza la vista del menú
```

El motor `OverrideResolver` buscará primero en esa ruta y, si no existe, recurrirá a la vista interna del paquete.
