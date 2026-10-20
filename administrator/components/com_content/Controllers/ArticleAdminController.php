<?php

namespace Aspectox\LaraJoom\Admin\Components\com_content\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'     => 'required|string|max:255',
            'alias'     => 'nullable|string|max:255|unique:larajoom_content,alias',
            'catid'     => 'required|integer',
            'introtext' => 'nullable|string',
            'fulltext'  => 'nullable|string',
            'published' => 'nullable|boolean',
        ]);

        $data['alias'] = empty($data['alias']) ? Str::slug($data['title']) : Str::slug($data['alias']);
        $data['published'] = $request->has('published');

        Article::create($data);
        larajoom_plugins()->dispatch('content', 'onContentAfterSave', ['com_content.article', $article, true]);
        return redirect()->route('larajoom.admin.articles.index');
    }

    public function edit(int $id)
    {
        $article = Article::findOrFail($id);
        $categories = Category::where('published', 1)->get();
        return view('admin.components.com_content::articles.edit', compact('article', 'categories'));
    }

    public function update(Request $request, int $id)
    {
        $article = Article::findOrFail($id);
        $data = $request->validate([
            'title'     => 'required|string|max:255',
            'alias'     => 'nullable|string|max:255|unique:larajoom_content,alias,' . $id,
            'catid'     => 'required|integer',
            'introtext' => 'nullable|string',
            'fulltext'  => 'nullable|string',
            'published' => 'nullable|boolean',
        ]);

        $data['alias'] = empty($data['alias']) ? Str::slug($data['title']) : Str::slug($data['alias']);
        $data['published'] = $request->has('published');

        $article->update($data);
        larajoom_plugins()->dispatch('content', 'onContentAfterSave', ['com_content.article', $article, false]);
        return redirect()->route('larajoom.admin.articles.index');
    }

    public function destroy(int $id)
    {
        Article::findOrFail($id)->delete();
        return redirect()->route('larajoom.admin.articles.index');
    }
}
