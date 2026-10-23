<?php

namespace Aspectox\LaraJoom\Core\MediaAction;

interface MediaActionServiceInterface
{
    public function getName(): string;
    
    public function getLabel(): string;
    
    public function getIcon(): string;
    
    public function getFormFields(): array;
    
    public function execute(string $filePath, array $params): array;
}