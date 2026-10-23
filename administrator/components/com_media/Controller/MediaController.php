<?php

namespace Aspectox\LaraJoom\Admin\Components\com_media\Controller;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Aspectox\LaraJoom\Admin\Components\com_media\Model\MediaModel;

class MediaController extends BaseController
{
    protected MediaModel $model;

    public function __construct(MediaModel $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $path = $request->route('path', '');
        $this->model->setPath($path);

        $folders = $this->model->getFolders();
        $files = $this->model->getFiles();
        $breadcrumbs = $this->model->getBreadcrumbs();
        $tree = $this->model->getTree();

        $debugMedia = [
            'root' => $this->model->mediaRoot(),
            'folders' => count($folders),
            'files' => count($files),
            'treeChildren' => count($tree['children'] ?? []),
        ];

        try {
            return view('admin.components.com_media::media.index', compact(
                'folders',
                'files',
                'breadcrumbs',
                'tree',
                'path',
                'debugMedia'
            ));
        } catch (\Throwable $e) {
            return response('<pre style="background:#000;color:#0f0;padding:10px">' . e($e->getMessage()) . "\n" . e($e->getFile() . ':' . $e->getLine()) . '</pre>', 500);
        }
    }

    public function createFolder(Request $request)
    {
        $name = $request->validate(['name' => 'required|string|max:255'])['name'];
        $path = (string) ($request->get('path', '') ?? '');
        $this->model->setPath($path);

        $success = $this->model->createFolder($name);

        if ($request->ajax()) {
            return response()->json(['success' => $success]);
        }

        return redirect()->back()->with(
            $success ? 'success' : 'error',
            $success ? __('com_media::com_media.folder_created') : __('com_media::com_media.folder_create_failed')
        );
    }

    public function upload(Request $request)
    {
        try {
            $path = (string) ($request->get('path', '') ?? '');
            $this->model->setPath($path);

            $request->validate([
                'files.*' => 'file|max:10240',
            ]);

            $uploaded = $request->file('files');
            if (!$uploaded || !count((array) $uploaded)) {
                return redirect()->back()->with('error', __('com_media::com_media.no_files_selected'));
            }

            $results = [];
            foreach ($uploaded as $file) {
                $results[] = $this->model->upload($file);
            }

            $success = collect($results)->every(fn ($r) => $r['success']);

            if ($request->ajax()) {
                return response()->json(['success' => $success, 'results' => $results]);
            }

            return redirect()->back()->with(
                $success ? 'success' : 'error',
                $success ? __('com_media::com_media.files_uploaded') : __('com_media::com_media.upload_failed')
            );
        } catch (\Throwable $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Upload error: ' . $e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        $relativePath = $request->validate(['path' => 'required|string'])['path'];
        
        $success = $this->model->delete($relativePath);

        if ($request->ajax()) {
            return response()->json(['success' => $success]);
        }

        return redirect()->back()->with(
            $success ? 'success' : 'error',
            $success ? __('com_media::com_media.deleted') : __('com_media::com_media.delete_failed')
        );
    }

    public function deleteBulk(Request $request)
    {
        $paths = (array) $request->input('paths', []);
        $deleted = 0;

        foreach ($paths as $p) {
            if (is_string($p) && $p !== '' && $this->model->delete($p)) {
                $deleted++;
            }
        }

        return redirect()->back()->with(
            $deleted ? 'success' : 'error',
            $deleted ? __('com_media::com_media.deleted') : __('com_media::com_media.delete_failed')
        );
    }

    public function rename(Request $request)
    {
        $data = $request->validate([
            'old_name' => 'required|string',
            'new_name' => 'required|string|max:255',
        ]);
        $path = (string) ($request->get('path', '') ?? '');
        $this->model->setPath($path);

        $success = $this->model->rename($data['old_name'], $data['new_name']);

        if ($request->ajax()) {
            return response()->json(['success' => $success]);
        }

        return redirect()->back()->with(
            $success ? 'success' : 'error',
            $success ? __('com_media::com_media.renamed') : __('com_media::com_media.rename_failed')
        );
    }

    public function preview(Request $request)
    {
        $relativePath = $request->validate(['path' => 'required|string'])['path'];
        $disk = $this->model->disk();

        if (!$disk->exists($relativePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $mime = $disk->mimeType($relativePath);
        $content = $disk->get($relativePath);

        return response($content, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline',
        ]);
    }
}