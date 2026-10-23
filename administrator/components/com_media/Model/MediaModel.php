<?php

namespace Aspectox\LaraJoom\Admin\Components\com_media\Model;

use Illuminate\Support\Facades\Storage;

class MediaModel
{
    protected string $disk = 'media';
    protected string $path = '';

    public function __construct(string $path = '')
    {
        $this->path = $path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function rootName(): string
    {
        return config('larajoom.media_root_name', 'images');
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function setDisk(string $disk): self
    {
        $this->disk = $disk;
        return $this;
    }

    /**
     * Ruta física raíz del gestor de medios. Resuelve la primera que exista
     * entre public/images y storage/images (y storage/app/public/images).
     */
    public function mediaRoot(): string
    {
        $candidates = [
            public_path('storage/images'),
            storage_path('app/public/images'),
            public_path('images'),
            storage_path('images'),
        ];

        foreach ($candidates as $candidate) {
            if (is_dir($candidate)) {
                return $candidate;
            }
        }

        return storage_path('images');
    }

    /**
     * Adapter del disco de medios. Se construye en línea contra la raíz real
     * (mediaRoot) para no depender del registro de un disco global.
     */
    public function disk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::build([
            'driver' => 'local',
            'root' => $this->mediaRoot(),
        ]);
    }

    public function getFolders(): array
    {
        $disk = $this->disk();
        $path = $this->path ?: '.';
        $folders = [];

        try {
            $directories = $disk->directories($path);
            foreach ($directories as $dir) {
                $folders[] = [
                    'name' => basename($dir),
                    'path' => $dir,
                    'relative_path' => $path === '.' ? basename($dir) : $path . '/' . basename($dir),
                ];
            }
        } catch (\Throwable) {
        }

        return $folders;
    }

    public function getFiles(): array
    {
        $disk = $this->disk();
        $path = $this->path ?: '.';
        $files = [];

        try {
            $allFiles = $disk->files($path);
            foreach ($allFiles as $file) {
                $dimensions = $this->getImageDimensions($file);
                $files[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'relative_path' => $path === '.' ? basename($file) : $path . '/' . basename($file),
                    'size' => $disk->size($file),
                    'mime' => $disk->mimeType($file),
                    'modified' => $disk->lastModified($file),
                    'extension' => strtolower(pathinfo($file, PATHINFO_EXTENSION)),
                    'width' => $dimensions['width'] ?? null,
                    'height' => $dimensions['height'] ?? null,
                ];
            }
        } catch (\Throwable) {
        }

        return $files;
    }

    /**
     * Árbol completo (recursivo) de carpetas y archivos desde la raíz del disco,
     * para el árbol de la barra lateral. Se lee directamente del sistema de
     * archivos (storage/images) para no depender del adaptador de Flysystem.
     */
    public function getTree(): array
    {
        $root = $this->mediaRoot();

        return [
            'name' => $this->rootName(),
            'relative_path' => '',
            'children' => $this->scanDir($root, ''),
            'files' => [],
        ];
    }

    protected function scanDir(string $abs, string $relative): array
    {
        $children = [];
        if (!is_dir($abs)) {
            return $children;
        }

        foreach (scandir($abs) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $childAbs = $abs . DIRECTORY_SEPARATOR . $item;
            $childRel = $relative ? $relative . '/' . $item : $item;

            if (is_dir($childAbs)) {
                $children[] = [
                    'name' => $item,
                    'relative_path' => $childRel,
                    'children' => $this->scanDir($childAbs, $childRel),
                    'files' => $this->scanFiles($childAbs, $childRel),
                ];
            }
        }

        return $children;
    }

    protected function scanFiles(string $abs, string $relative): array
    {
        $files = [];
        if (!is_dir($abs)) {
            return $files;
        }

        foreach (scandir($abs) as $item) {
            if ($item === '.' || $item === '..' || is_dir($abs . DIRECTORY_SEPARATOR . $item)) {
                continue;
            }
            $files[] = [
                'name' => $item,
                'relative_path' => $relative . '/' . $item,
            ];
        }

        return $files;
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [['name' => $this->rootName(), 'path' => '']];
        
        if ($this->path) {
            $parts = explode('/', $this->path);
            $currentPath = '';
            foreach ($parts as $part) {
                $currentPath .= ($currentPath ? '/' : '') . $part;
                $breadcrumbs[] = [
                    'name' => $part,
                    'path' => $currentPath,
                ];
            }
        }

        return $breadcrumbs;
    }

    public function createFolder(string $name): bool
    {
        $disk = $this->disk();
        $path = $this->path ? $this->path . '/' . $name : $name;
        
        try {
            return $disk->makeDirectory($path);
        } catch (\Throwable) {
            return false;
        }
    }

    public function delete(string $relativePath): bool
    {
        $base = $this->mediaRoot();
        $abs = $base . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, ltrim($relativePath, '/\\'));

        $realBase = realpath($base);
        $realAbs = realpath(dirname($abs)) . DIRECTORY_SEPARATOR . basename($abs);

        if ($realBase === false || !str_starts_with($realAbs, $realBase . DIRECTORY_SEPARATOR)) {
            return false;
        }

        try {
            if (is_dir($realAbs)) {
                $this->removeDir($realAbs);
                return !is_dir($realAbs);
            }
            if (is_file($realAbs)) {
                return @unlink($realAbs);
            }
            return false;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function removeDir(string $dir): void
    {
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $p = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($p)) {
                $this->removeDir($p);
            } else {
                @unlink($p);
            }
        }
        @rmdir($dir);
    }

    public function upload(\Illuminate\Http\UploadedFile $file): array
    {
        $disk = $this->disk();
        $name = $file->getClientOriginalName();

        try {
            $stored = $disk->putFileAs($this->path ?: '.', $file, $name);
            if ($stored === false) {
                return [
                    'success' => false,
                    'error' => 'No se pudo almacenar el archivo',
                ];
            }

            return [
                'success' => true,
                'path' => $stored,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function rename(string $oldName, string $newName): bool
    {
        $disk = $this->disk();
        $oldPath = $this->path ? $this->path . '/' . $oldName : $oldName;
        $newPath = $this->path ? $this->path . '/' . $newName : $newName;
        
        try {
            if ($disk->exists($oldPath)) {
                return $disk->move($oldPath, $newPath);
            }
            return false;
        } catch (\Throwable) {
            return false;
        }
    }

    public function isImage(string $filename): bool
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
    }

    public function getImageDimensions(string $relativePath): ?array
    {
        $disk = $this->disk();
        
        try {
            if ($disk->exists($relativePath)) {
                $path = $disk->path($relativePath);
                $size = getimagesize($path);
                if ($size) {
                    return ['width' => $size[0], 'height' => $size[1]];
                }
            }
        } catch (\Throwable) {
        }
        
        return null;
    }
}