<?php

namespace Aspectox\LaraJoom\Plugins\MediaAction\Rotate\Services;

use Aspectox\LaraJoom\Core\MediaAction\MediaActionServiceInterface;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class RotateService implements MediaActionServiceInterface
{
    public function getName(): string
    {
        return 'rotate';
    }

    public function getLabel(): string
    {
        return 'Rotate';
    }

    public function getIcon(): string
    {
        return 'rotate-left';
    }

    public function getFormFields(): array
    {
        return [
            'angle' => [
                'type' => 'select',
                'label' => 'Rotation Angle',
                'options' => [
                    '90' => '90° (Right)',
                    '180' => '180°',
                    '270' => '270° (Left)',
                    '-90' => '-90° (Left)',
                ],
                'default' => '90',
                'required' => true,
            ],
            'flip_horizontal' => [
                'type' => 'checkbox',
                'label' => 'Flip Horizontal',
                'default' => false,
            ],
            'flip_vertical' => [
                'type' => 'checkbox',
                'label' => 'Flip Vertical',
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
            
            $angle = (int) ($params['angle'] ?? 90);
            $flipHorizontal = (bool) ($params['flip_horizontal'] ?? false);
            $flipVertical = (bool) ($params['flip_vertical'] ?? false);
            
            if ($angle !== 0) {
                $image->rotate($angle);
            }
            
            if ($flipHorizontal) {
                $image->flipHorizontal();
            }
            
            if ($flipVertical) {
                $image->flipVertical();
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