<?php

namespace Aspectox\LaraJoom\Contracts;

interface ExtensionContract
{
    public function getName(): string;
    public function boot(): void;
    public function register(): void;
}
