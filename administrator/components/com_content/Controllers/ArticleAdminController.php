<?php

namespace Aspectox\LaraJoom\Admin\Components\com_content\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Aspectox\LaraJoom\Core\Http\Requests\ArticleRequest;
use Aspectox\LaraJoom\Admin\Components\com_content\Models\Article;
use Aspectox\LaraJoom\Admin\Components\com_content\Models\Category;

class ArticleAdminController extends Controller
{
    public function index()
    {
        $limit = config('larajoom.list_limit', 20);
        $articles = Article::with('category')->latest()->paginate($limit);
        return view('admin.components.com_content::articles.index', compact('articles'));
    }

    public function create()
    {
        $categories = Category::where('published', 1)->get();
        return view('admin.components.com_content::articles.create', compact('categories'));
    }

    public function store(ArticleRequest $request)
    {
        $data = $request->validated();

        $data['alias'] = empty($data['alias']) ? Str::slug($data['title']) : Str::slug($data['alias']);
        $data['published'] = $request->boolean('published');

        $article = Article::create($data);
        larajoom_plugins()->dispatch('content', 'onContentAfterSave', ['com_content.article', $article, true]);

        $log = larajoom_plugins()->resolve('actionlog', 'joomla');
        if ($log && method_exists($log, 'onArticleSaved')) {
            $log->onArticleSaved($article->id, 'created');
        }

        return redirect()->route('larajoom.admin.articles.index');
    }

    public function edit(int $id)
    {
        $article = Article::findOrFail($id);
        $categories = Category::where('published', 1)->get();
        return view('admin.components.com_content::articles.edit', compact('article', 'categories'));
    }

    public function update(ArticleRequest $request, int $id)
    {
        $article = Article::findOrFail($id);
        $data = $request->validated();

        $data['alias'] = empty($data['alias']) ? Str::slug($data['title']) : Str::slug($data['alias']);
        $data['published'] = $request->boolean('published');

        $article->update($data);
        larajoom_plugins()->dispatch('content', 'onContentAfterSave', ['com_content.article', $article, false]);

        $log = larajoom_plugins()->resolve('actionlog', 'joomla');
        if ($log && method_exists($log, 'onArticleSaved')) {
            $log->onArticleSaved($article->id, 'updated');
        }

        return redirect()->route('larajoom.admin.articles.index');
    }

    public function destroy(int $id)
    {
        Article::findOrFail($id)->delete();

        $log = larajoom_plugins()->resolve('actionlog', 'joomla');
        if ($log && method_exists($log, 'onArticleSaved')) {
            $log->onArticleSaved($id, 'deleted');
        }

        return redirect()->route('larajoom.admin.articles.index');
    }
}
