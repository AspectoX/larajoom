<?php

namespace Aspectox\LaraJoom\Admin\Components\com_plugins\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PluginParams
{
    /**
     * Lee el esquema de parámetros (param_fields) del manifest.json del plugin.
     */
    public static function fields(string $group, string $element): array
    {
        $manifestPath = dirname(__DIR__, 4)
            . DIRECTORY_SEPARATOR . 'plugins'
            . DIRECTORY_SEPARATOR . $group
            . DIRECTORY_SEPARATOR . $element
            . DIRECTORY_SEPARATOR . 'manifest.json';

        if (!is_file($manifestPath)) {
            return [];
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);

        return is_array($manifest['param_fields'] ?? null) ? $manifest['param_fields'] : [];
    }

    /**
     * Fusiona valores guardados (DB) con defaults del esquema.
     */
    public static function values(array $saved, array $fields): array
    {
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

    /**
     * Guarda params como JSON en larajoom_extensions.
     */
    public static function save(int $extensionId, array $params): void
    {
        DB::table('larajoom_extensions')
            ->where('extension_id', $extensionId)
            ->update([
                'params'     => json_encode($params),
                'updated_at' => now(),
            ]);
    }
}
