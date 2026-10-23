<?php

namespace Aspectox\LaraJoom\Plugins\MediaAction\Crop\Services;

use Aspectox\LaraJoom\Core\MediaAction\MediaActionServiceInterface;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CropService implements MediaActionServiceInterface
{
    public function getName(): string
    {
        return 'crop';
    }

    public function getLabel(): string
    {
        return 'Crop';
    }

    public function getIcon(): string
    {
        return 'crop';
    }

    public function getFormFields(): array
    {
        return [
            'x' => [
                'type' => 'number',
                'label' => 'X Position',
                'min' => 0,
                'required' => true,
            ],
            'y' => [
                'type' => 'number',
                'label' => 'Y Position',
                'min' => 0,
                'required' => true,
            ],
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
            
            $x = (int) ($params['x'] ?? 0);
            $y = (int) ($params['y'] ?? 0);
            $width = (int) ($params['width'] ?? $image->width());
            $height = (int) ($params['height'] ?? $image->height());
            
            // Asegurar que el crop no exceda los límites de la imagen
            $width = min($width, $image->width() - $x);
            $height = min($height, $image->height() - $y);
            
            $image->crop($width, $height, $x, $y);
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