<?php

namespace Aspectox\LaraJoom\Admin\Components\com_modules\Services;

use Aspectox\LaraJoom\Core\Modules\ModuleTypes;
use Illuminate\Http\Request;

class ModuleParams
{
    /**
     * Lee el esquema de parámetros (param_fields) del manifest.json del módulo.
     */
    public static function fields(?string $module): array
    {
        if ($module === null || !str_starts_with($module, 'mod_')) {
            return [];
        }

        foreach (ModuleTypes::folders() as $folder) {
            $manifestPath = $folder
                . DIRECTORY_SEPARATOR . $module
                . DIRECTORY_SEPARATOR . 'manifest.json';

            if (!is_file($manifestPath)) {
                continue;
            }

            $manifest = json_decode((string) file_get_contents($manifestPath), true);

            return is_array($manifest['param_fields'] ?? null) ? $manifest['param_fields'] : [];
        }

        return [];
    }

    /**
     * Fusiona valores guardados (DB) con defaults del esquema.
     */
    public static function values(mixed $saved, array $fields): array
    {
        if (is_string($saved)) {
            $saved = json_decode($saved, true) ?: [];
        }
        $saved = is_array($saved) ? $saved : [];

        $values = [];

        foreach ($fields as $field) {
            $name = $field['name'] ?? null;
            if ($name === null) continue;

            $values[$name] = $saved[$name] ?? ($field['default'] ?? null);
        }

        foreach ($saved as $key => $value) {
            $values[$key] = $value;
        }

        return $values;
    }

    /**
     * Normaliza los params recibidos del request según el esquema
     * (checkboxes ausentes = 0, números, strings).
     */
    public static function normalize(Request $request, array $fields): array
    {
        $data = [];

        foreach ($fields as $field) {
            $name = $field['name'] ?? null;
            if ($name === null) continue;

            $type = $field['type'] ?? 'text';

            $data[$name] = match ($type) {
                'checkbox' => $request->has('params.' . $name) ? 1 : 0,
                'number'   => $request->input('params.' . $name) !== null ? (int) $request->input('params.' . $name) : null,
                default    => $request->input('params.' . $name),
            };
        }

        return $data;
    }
}
