<?php

namespace Aspectox\LaraJoom\Admin\Components\com_templates\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Aspectox\LaraJoom\Admin\Components\com_templates\Models\TemplateStyle;

class TemplateController extends Controller
{
    /**
     * Templates detectados en disco (administrator/templates y templates).
     */
    protected function templatesFromDisk(?string $clientFilter = null): array
    {
        $locations = [
            ['dir' => 'administrator/templates', 'client' => 'administrator'],
            ['dir' => 'templates',               'client' => 'site'],
        ];

        $templates = [];

        foreach ($locations as $loc) {
            $dir = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . $loc['dir'];

            if (!is_dir($dir)) {
                continue;
            }

            foreach (scandir($dir) as $item) {
                if ($item === '.' || $item === '..' || $item === 'system') {
                    continue;
                }

                $tplDir = $dir . DIRECTORY_SEPARATOR . $item;
                if (!is_dir($tplDir)) {
                    continue;
                }

                $manifest = [
                    'title'  => $item,
                    'version' => '-',
                    'date'   => '-',
                    'author' => '-',
                ];

                $manifestPath = $tplDir . DIRECTORY_SEPARATOR . 'manifest.json';
                if (is_file($manifestPath)) {
                    $data = json_decode(file_get_contents($manifestPath), true);
                    if (is_array($data)) {
                        $manifest['title']   = $data['title'] ?? $data['name'] ?? $item;
                        $manifest['version']  = $data['version'] ?? '-';
                        $manifest['date']     = $data['creationDate'] ?? $data['creationdate'] ?? '-';
                        $manifest['author']    = $data['author'] ?? '-';
                    }
                }

                $templates[] = [
                    'element'   => $item,
                    'title'     => $manifest['title'],
                    'version'   => $manifest['version'],
                    'date'      => $manifest['date'],
                    'author'    => $manifest['author'],
                    'client'    => $loc['client'],
                    'override'  => $this->overrideCount($tplDir),
                    'image'     => $this->templateImage($loc['client'], $item),
                    'positions' => $this->positionsOf($tplDir),
                ];
            }
        }

        if ($clientFilter === 'site' || $clientFilter === 'administrator') {
            $templates = array_values(array_filter($templates, fn ($t) => $t['client'] === $clientFilter));
        }

        return $templates;
    }

    protected function templateImage(string $client, string $element): ?string
    {
        $mediaBase = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'media'
            . DIRECTORY_SEPARATOR . 'templates'
            . DIRECTORY_SEPARATOR . $client
            . DIRECTORY_SEPARATOR . $element
            . DIRECTORY_SEPARATOR . 'images';

        foreach (['template_thumbnail.png', 'template_preview.png', 'preview.png', 'thumbnail.png'] as $img) {
            if (is_file($mediaBase . DIRECTORY_SEPARATOR . $img)) {
                return 'media/templates/' . $client . '/' . $element . '/images/' . $img;
            }
        }

        return null;
    }

    protected function positionsOf(string $dir): array
    {
        $file = $dir . DIRECTORY_SEPARATOR . 'positions.json';

        if (!is_file($file)) {
            return [];
        }

        $json = json_decode(file_get_contents($file), true);

        return array_map(fn ($p) => $p['name'] ?? '', $json['positions'] ?? []);
    }

    /**
     * Cantidad de archivos de vista/override del template.
     */
    protected function overrideCount(string $dir): int
    {
        $count = 0;

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($it as $file) {
            if ($file->isFile() && $file->getExtension() === 'blade.php') {
                $count++;
            }
        }

        return $count;
    }

    public function index(Request $request)
    {
        $client = (string) $request->input('client', '');
        $templates = $this->templatesFromDisk($client !== '' ? $client : null);

        $styles = collect();
        if (Schema::hasTable('larajoom_template_styles')) {
            $styles = TemplateStyle::orderBy('template')->orderBy('title')->get();
        }

        $firstTemplate = $templates[0]['element'] ?? '';

        return view('admin.components.com_templates::index', compact('templates', 'styles', 'firstTemplate', 'client'));
    }

    public function create(Request $request)
    {
        $template = (string) $request->route('template', '');

        if ($template === '') {
            $templates = $this->templatesFromDisk();
            $template = $templates[0]['element'] ?? '';
        }

        $dir    = $this->templateDir($template);
        $client = $this->clientOf($dir);
        $isSite = $client === 'site';

        $mediaDir = $this->mediaDirFor($template, $client);

        $activeFile = trim((string) $request->query('file', ''));
        $source     = null;
        $filename   = '';
        $imageUrl   = null;
        $filePath   = '';

        if ($dir !== '' && $activeFile !== '') {
            $resolved = $this->resolveTemplateFile($template, $activeFile, $dir, $mediaDir, $client);

            if ($resolved === null) {
                Log::warning('[com_templates] Archivo no válido para vista de solo lectura.', [
                    'template' => $template,
                    'file'     => $activeFile,
                ]);

                return redirect()->route('larajoom.admin.templates.create', $template)
                    ->withErrors(['file' => 'Archivo no válido.']);
            }

            $ext      = strtolower(pathinfo($resolved['path'], PATHINFO_EXTENSION));
            $filename = basename($resolved['path']);
            $filePath = $resolved['path'];

            if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'ico', 'bmp'], true)) {
                $imageUrl = $resolved['url'];
            } else {
                $source = file_get_contents($resolved['path']);
            }
        }

        return view('admin.components.com_templates::edit', [
            'style'     => null,
            'template'  => $template,
            'client'    => $client,
            'positions' => $this->positionsOf($dir !== '' ? $dir : dirname(__DIR__, 4) . '/administrator/templates/' . $template),
            'tree'      => $dir !== '' ? $this->treeChildren($dir) : [],
            'rootLabel' => ($isSite ? '/templates/' : '/administrator/templates/') . $template,
            'mediaTree' => $this->mediaTree($mediaDir),
            'rootMediaLabel' => '/media/templates/' . ($isSite ? 'site' : 'administrator') . '/' . $template,
            'activeFile' => $activeFile,
            'filename'  => $filename,
            'filePath'  => $filePath,
            'source'    => $source,
            'imageUrl'  => $imageUrl,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'template' => 'required|string|max:50',
            'title'    => 'required|string|max:150',
            'params'   => 'nullable|array',
        ]);

        if (!Schema::hasTable('larajoom_template_styles')) {
            return redirect()->back()->withErrors(['template' => 'La tabla de estilos no existe (ejecutá las migraciones).']);
        }

        $style = TemplateStyle::create([
            'template' => $data['template'],
            'title'    => $data['title'],
            'params'   => $data['params'] ?? [],
            'home'     => $request->boolean('home'),
        ]);

        if ($request->boolean('home')) {
            TemplateStyle::where('template', $style->template)
                ->where('id', '!=', $style->id)
                ->update(['home' => false]);
        }

        return redirect()->route('larajoom.admin.templates.index')
            ->with('success', 'Estilo creado.');
    }

    public function edit(Request $request, int $id)
    {
        $style = TemplateStyle::findOrFail($id);

        $dir    = $this->templateDir($style->template);
        $client = $this->clientOf($dir);

        $mediaDir = $this->mediaDirFor($style->template, $client);

        $activeFile = trim((string) $request->query('file', ''));
        $source     = null;
        $filename   = '';
        $imageUrl   = null;
        $filePath   = '';

        if ($dir !== '' && $activeFile !== '') {
            $resolved = $this->resolveTemplateFile($style->template, $activeFile, $dir, $mediaDir, $client);

            if ($resolved === null) {
                Log::warning('[com_templates] Archivo no válido o fuera del template solicitado.', [
                    'template' => $style->template,
                    'file'     => $activeFile,
                    'dirs'     => [$dir, $mediaDir],
                ]);

                $activeFile = '';
            } else {
                $ext      = strtolower(pathinfo($resolved['path'], PATHINFO_EXTENSION));
                $filename = basename($resolved['path']);
                $filePath = $resolved['path'];

                if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'ico', 'bmp'], true)) {
                    $imageUrl = $resolved['url'];
                } else {
                    $source = file_get_contents($resolved['path']);
                }
            }
        }

        $rootLabel = ($client === 'site' ? '/templates/' : '/administrator/templates/') . $style->template;

        return view('admin.components.com_templates::edit', [
            'style'      => $style,
            'template'   => $style->template,
            'client'     => $client,
            'positions'  => $this->positionsOf($dir !== '' ? $dir : dirname(__DIR__, 4) . '/administrator/templates/' . $style->template),
            'tree'       => $dir !== '' ? $this->treeChildren($dir) : [],
            'rootLabel'  => $rootLabel,
            'mediaTree'  => $this->mediaTree($mediaDir),
            'rootMediaLabel' => '/media/templates/' . ($client === 'site' ? 'site' : 'administrator') . '/' . $style->template,
            'activeFile' => $activeFile,
            'filename'   => $filename,
            'filePath'   => $filePath,
            'source'     => $source,
            'imageUrl'   => $imageUrl,
        ]);
    }

    public function files(int $id)
    {
        $style = TemplateStyle::findOrFail($id);

        $dir = $this->templateDir($style->template);

        if ($dir === '') {
            return response()->json(['tree' => []]);
        }

        return response()->json(['tree' => $this->treeChildren($dir)]);
    }

    public function file(int $id, Request $request)
    {
        $style = TemplateStyle::findOrFail($id);

        $baseDir  = $this->templateDir($style->template);
        $client   = $this->clientOf($baseDir);
        $mediaDir = $this->mediaDirFor($style->template, $client);

        $resolved = $this->resolveTemplateFile($style->template, (string) $request->query('file', ''), $baseDir, $mediaDir, $client);

        if ($resolved === null) {
            abort(404);
        }

        $ext = strtolower(pathinfo($resolved['path'], PATHINFO_EXTENSION));

        return response()->json([
            'content'  => in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'ico', 'bmp'], true) ? null : file_get_contents($resolved['path']),
            'filename' => basename($resolved['path']),
        ]);
    }

    public function newfile(int $id, Request $request)
    {
        $style = TemplateStyle::findOrFail($id);
        $name  = trim((string) $request->query('name', ''));

        $allowedExt = ['css', 'js', 'html', 'txt', 'xml', 'json', 'scss', 'less', 'md'];
        $ext        = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if ($name === '' || $ext === '' || !in_array($ext, $allowedExt, true) || str_contains($name, '/') || str_contains($name, '\\')) {
            return redirect()->route('larajoom.admin.templates.edit', $style->id)
                ->withErrors(['file' => 'Nombre de archivo no válido o extensión no permitida.']);
        }

        $baseDir = $this->templateDir($style->template);
        $baseReal  = realpath($baseDir);
        $target    = $baseReal . DIRECTORY_SEPARATOR . basename($name);

        if ($baseReal === false) {
            return redirect()->route('larajoom.admin.templates.edit', $style->id)
                ->withErrors(['file' => 'El directorio del template no existe.']);
        }

        if (file_exists($target)) {
            return redirect()->route('larajoom.admin.templates.edit', $style->id)
                ->withErrors(['file' => 'El archivo ya existe.']);
        }

        file_put_contents($target, '');

        return redirect()->route('larajoom.admin.templates.edit', $style->id)
            ->with('success', 'Archivo creado: ' . basename($name));
    }

    public function savefile(int $id, Request $request)
    {
        $style = TemplateStyle::findOrFail($id);
        $file  = trim((string) $request->input('file', ''));

        if ($file === '') {
            return redirect()->route('larajoom.admin.templates.edit', $style->id)
                ->withErrors(['file' => 'No hay archivo seleccionado para guardar.']);
        }

        $baseDir  = $this->templateDir($style->template);
        $client   = $this->clientOf($baseDir);
        $mediaDir = $this->mediaDirFor($style->template, $client);

        $resolved = $this->resolveTemplateFile($style->template, $file, $baseDir, $mediaDir, $client);

        if ($resolved === null) {
            Log::warning('[com_templates] Intento de guardar en archivo no válido.', [
                'template' => $style->template,
                'file'     => $file,
            ]);

            return redirect()->route('larajoom.admin.templates.edit', $style->id)
                ->withErrors(['file' => 'Archivo no válido.']);
        }

        $ext = strtolower(pathinfo($resolved['path'], PATHINFO_EXTENSION));

        if ($ext === 'php') {
            return redirect()->route('larajoom.admin.templates.edit', $style->id)
                ->withErrors(['file' => 'No se pueden escribir archivos PHP.']);
        }

        if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'ico', 'bmp'], true)) {
            return redirect()->route('larajoom.admin.templates.edit', $style->id)
                ->withErrors(['file' => 'Las imágenes no son editables como texto.']);
        }

        file_put_contents($resolved['path'], (string) $request->input('content', ''));

        return redirect()->route('larajoom.admin.templates.edit', ['id' => $style->id, 'file' => $file])
            ->with('success', 'Archivo guardado: ' . basename($resolved['path']));
    }

    public function preview(string $template)
    {
        $element = preg_replace('/[^a-z0-9_\-]/i', '', $template);
        $dir     = dirname(__DIR__, 4) . '/administrator/templates/' . $element;

        if ($element === '' || !is_dir($dir)) {
            abort(404);
        }

        return response()->make('<!DOCTYPE html><html><head><meta charset="utf-8"><title>Preview: ' . e($element) . '</title></head><body><h3>Preview de template: ' . e($element) . '</h3><p>La vista previa del sitio está pendiente de implementar.</p></body></html>');
    }

    /**
     * Devuelve el directorio del template (sitio o admin) si existe, '' si no.
     */
    protected function templateDir(string $template): string
    {
        $base = dirname(__DIR__, 4);

        foreach (['templates', 'administrator' . DIRECTORY_SEPARATOR . 'templates'] as $clientDir) {
            $dir = $base . DIRECTORY_SEPARATOR . $clientDir . DIRECTORY_SEPARATOR . $template;

            if (is_dir($dir)) {
                return $dir;
            }
        }

        return '';
    }

    /**
     * Cliente ('site' | 'administrator') según el directorio del template elegido.
     */
    protected function clientOf(string $tplDir): string
    {
        $adminPart = 'administrator' . DIRECTORY_SEPARATOR . 'templates';

        return ($tplDir !== '' && str_contains($tplDir, $adminPart)) ? 'administrator' : 'site';
    }

    /**
     * Directorio media del template (media/templates/{client}/{element})
     * publicado en el public/ del host, que es lo que sirve el navegador.
     */
    protected function mediaDirFor(string $template, string $client): string
    {
        return public_path('media' . DIRECTORY_SEPARATOR . 'templates'
            . DIRECTORY_SEPARATOR . $client
            . DIRECTORY_SEPARATOR . $template);
    }

    /**
     * Árbol media separado del template (como Joomla): los hijos van directo
     * bajo la etiqueta raíz /media/templates/{client}/{template}; los paths
     * llevan prefijo 'media/' para desambiguar en resolveTemplateFile().
     */
    protected function mediaTree(string $mediaDir): array
    {
        if ($mediaDir === '' || !is_dir($mediaDir)) {
            return [];
        }

        return $this->treeChildren($mediaDir, 'media');
    }

    /**
     * Resuelve la ruta relativa (?file=) contra el directorio del template
     * o contra su carpeta media/ (css/js/images), como Joomla.
     */
    protected function resolveTemplateFile(string $template, string $rel, string $tplDir, string $mediaDir, string $client): ?array
    {
        $rel = str_replace('\\', '/', trim($rel, '/'));

        if ($rel === '' || str_contains($rel, '..')) {
            return null;
        }

        $isMedia = str_starts_with($rel, 'media/');
        $mediaRel = $isMedia ? substr($rel, strlen('media/')) : '';

        $bases = [];

        if ($isMedia) {
            $bases[] = ['dir' => $mediaDir, 'rel' => $mediaRel, 'url' => 'media/templates/' . $client . '/' . $template . '/'];
        } else {
            $bases[] = ['dir' => $tplDir, 'rel' => $rel, 'url' => null];
        }

        foreach ($bases as $base) {
            if ($base['dir'] === '' || $base['rel'] === '') {
                continue;
            }

            $target   = realpath($base['dir'] . DIRECTORY_SEPARATOR . $base['rel']);
            $baseReal = realpath($base['dir']);

            if ($target === false || $baseReal === false || strpos($target, $baseReal . DIRECTORY_SEPARATOR) !== 0 || !is_file($target)) {
                continue;
            }

            return [
                'path' => $target,
                'url'  => $base['url'] === null ? null : $base['url'] . $base['rel'],
            ];
        }

        return null;
    }

    /**
     * Construye la lista anidada de carpetas/archivos (estilo Joomla)
     * de forma recursiva, con rutas relativas al directorio del template.
     */
    protected function treeChildren(string $dir, string $prefix = ''): array
    {
        $nodes = [];

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..' || $item === 'index.html' || $item === '.DS_Store') {
                continue;
            }

            $full = $dir . DIRECTORY_SEPARATOR . $item;
            $rel  = $prefix === '' ? $item : $prefix . '/' . $item;

            if (is_dir($full)) {
                $nodes[] = [
                    'name'     => $item,
                    'type'     => 'folder',
                    'path'     => $rel,
                    'children' => $this->treeChildren($full, $rel),
                ];
            } else {
                $nodes[] = [
                    'name' => $item,
                    'type' => 'file',
                    'path' => $rel,
                ];
            }
        }

        return $nodes;
    }

    public function update(Request $request, int $id)
    {
        $style = TemplateStyle::findOrFail($id);

        $data = $request->validate([
            'title'  => 'required|string|max:150',
            'params' => 'nullable|array',
        ]);

        $style->update([
            'title'  => $data['title'],
            'params' => $data['params'] ?? [],
            'home'   => $request->boolean('home'),
        ]);

        if ($request->boolean('home')) {
            TemplateStyle::where('template', $style->template)
                ->where('id', '!=', $style->id)
                ->update(['home' => false]);
        }

        return redirect()->route('larajoom.admin.templates.index')
            ->with('success', 'Estilo actualizado.');
    }

    public function destroy(int $id)
    {
        TemplateStyle::findOrFail($id)->delete();

        return redirect()->route('larajoom.admin.templates.index')
            ->with('success', 'Estilo eliminado.');
    }

    public function setHome(int $id)
    {
        $style = TemplateStyle::findOrFail($id);

        TemplateStyle::where('template', $style->template)->update(['home' => false]);
        $style->update(['home' => true]);

        return redirect()->route('larajoom.admin.templates.index')
            ->with('success', 'Estilo predeterminado actualizado.');
    }
}