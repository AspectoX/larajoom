@extends('admin_tpl::index')

@section('title', __('larajoom_admin::admin.articles'))
@section('icon', 'article')

@section('toolbar')
    <x-larajoom::toolbar :buttons="[
        [
            'label' => __('larajoom_admin::admin.new_article'),
            'icon'  => 'add',
            'url'   => route('larajoom.admin.articles.create'),
        ],
    ]" />
@endsection

@section('content')
    @php
        $rows = [];
        $actions = [];
        foreach ($articles as $i => $article) {
            $rows[] = [
                $article->id,
                ['html' => '<a href="' . route('larajoom.admin.articles.edit', $article->id) . '">' . e($article->title) . '</a>'],
                $article->category->title ?? 'Sin Categoría',
                $article->published ? __('larajoom_admin::admin.published') : __('larajoom_admin::admin.unpublished'),
                $article->hits,
                $article->created_at?->format('Y-m-d'),
            ];
            $actions[$i] = [
                'edit'   => route('larajoom.admin.articles.edit', $article->id),
                'delete' => route('larajoom.admin.articles.destroy', $article->id),
            ];
        }
    @endphp

    <x-larajoom::table
        :headers="[
            'ID',
            __('larajoom_admin::admin.title'),
            __('larajoom_admin::admin.categories'),
            __('larajoom_admin::admin.status'),
            'Hits',
            'Fecha',
        ]"
        :rows="$rows"
        :actions="$actions"
        :asset="'com_content'"
        :draggable="false"
    />

    <x-ax-pagination :paginator="$articles" />
@endsection