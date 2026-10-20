<?php

namespace Aspectox\LaraJoom\Site\Components\com_content\Controllers;

use Illuminate\Routing\Controller;
use Aspectox\LaraJoom\Admin\Components\com_content\Models\Article;
use Aspectox\LaraJoom\Admin\Components\com_content\Models\Category;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::where('published', 1)->latest()->paginate(10);
        return view('components.com_content::featured', compact('articles'));
    }

    public function show(string $alias)
    {
        $article = Article::where('alias', $alias)->where('published', 1)->firstOrFail();
        $article->increment('hits');

        $params = null;
        larajoom_plugins()->dispatch('content', 'onContentPrepare', ['com_content.article', &$article, &$params]);

        return view('components.com_content::article', compact('article'));
    }

    public function category(string $alias)
    {
        $category = Category::where('alias', $alias)->where('published', 1)->firstOrFail();
        $articles = $category->articles()->where('published', 1)->latest()->paginate(10);
        return view('components.com_content::category', compact('category', 'articles'));
    }
}
