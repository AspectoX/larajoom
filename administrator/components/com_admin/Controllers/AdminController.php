<?php

namespace Aspectox\LaraJoom\Admin\Components\com_admin\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Aspectox\LaraJoom\Core\ConfigLoader;

class AdminController extends Controller
{
    public function index()
    {
        $config = ConfigLoader::load();
        $extensions = DB::table('larajoom_extensions')
            ->select('name', 'type', 'enabled')
            ->get();
        $usersCount = DB::table('larajoom_users')->count();

        return view('admin.components.com_admin::index', [
            'sitename' => $config['sitename'] ?? config('larajoom.sitename', 'LaraJoom'),
            'extensions' => $extensions,
            'usersCount' => $usersCount,
        ]);
    }
}
