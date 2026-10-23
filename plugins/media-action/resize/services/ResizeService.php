<?php

namespace Aspectox\LaraJoom\Plugins\MediaAction\Resize\Services;

use Aspectox\LaraJoom\Core\MediaAction\MediaActionServiceInterface;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ResizeService implements MediaActionServiceInterface
{
    public function getName(): string
    {
        return 'resize';
    }

    public function getLabel(): string
    {
        return 'Resize';
    }

    public function getIcon(): string
    {
        return 'resize';
    }

    public function getFormFields(): array
    {
        return [
            'width' => [
                'type' => 'number',
                'label' => 'Width',
                'min' => 1,
                'required' => true,
            ],
            'height' => [
                'type' => 'number',
                'label' => 'Height',
                'min' => 1,
                'required' => true,
            ],
            'maintain_ratio' => [
                'type' => 'checkbox',
                'label' => 'Maintain Aspect Ratio',
                'default' => true,
            ],
            'upscale' => [
                'type' => 'checkbox',
                'label' => 'Allow Upscaling',
                'default' => false,
            ],
        ];
    }

    public function execute(string $filePath, array $params): array
    {
        $disk = Storage::disk('public');
        
        if (!$disk->exists($filePath)) {
            return ['success' => false, 'error' => 'File not found'];
        }

        try {
            $manager = new ImageManager(new Driver());
            $fullPath = $disk->path($filePath);
            $image = $manager->read($fullPath);
            
            $width = (int) ($params['width'] ?? $image->width());
            $height = (int) ($params['height'] ?? $image->height());
            $maintainRatio = (bool) ($params['maintain_ratio'] ?? true);
            $upscale = (bool) ($params['upscale'] ?? false);
            
            if (!$upscale) {
                $width = min($width, $image->width());
                $height = min($height, $image->height());
            }
            
            if ($maintainRatio) {
                $image->scale(width: $width, height: $height);
            } else {
                $image->resize($width, $height);
            }
            
            $image->save($fullPath);
            
            return [
                'success' => true,
                'path' => $filePath,
                'width' => $image->width(),
                'height' => $image->height(),
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}