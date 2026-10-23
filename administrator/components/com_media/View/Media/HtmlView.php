<?php

namespace Aspectox\LaraJoom\Admin\Components\com_media\View\Media;

use Aspectox\LaraJoom\Admin\Components\com_media\Model\MediaModel;
use Illuminate\Routing\Controller;

class HtmlView extends Controller
{
    protected MediaModel $model;

    public function __construct(MediaModel $model)
    {
        $this->model = $model;
    }

    public function index($path = '')
    {
        $this->model->setPath($path);

        $folders = $this->model->getFolders();
        $files = $this->model->getFiles();
        $breadcrumbs = $this->model->getBreadcrumbs();

        return view('admin.components.com_media::media.index', compact(
            'folders',
            'files',
            'breadcrumbs',
            'path'
        ));
    }
}