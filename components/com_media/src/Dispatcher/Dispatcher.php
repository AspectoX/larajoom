<?php

namespace Aspectox\LaraJoom\Site\Components\com_media\Dispatcher;

use Aspectox\LaraJoom\Core\Dispatcher\ComponentDispatcher;
use Illuminate\Routing\Controller;

class Dispatcher extends ComponentDispatcher
{
    protected function loadLanguage(): void
    {
        $this->app->make('translation.loader')->load('com_media', 'admin');
        parent::loadLanguage();
    }

    protected function checkAccess(): void
    {
        $user = $this->app->make('auth')->guard('ax-larajoom')->user();

        if (!$user || (!$user->can('core.manage', 'com_media') && !$user->can('core.create', 'com_media'))) {
            abort(403, __('larajoom_admin::admin.access_denied'));
        }
    }

    public function getController(string $name, string $client = '', array $config = []): Controller
    {
        $config['base_path'] = base_path('administrator/components/com_media');
        return parent::getController($name, 'Admin', $config);
    }
}