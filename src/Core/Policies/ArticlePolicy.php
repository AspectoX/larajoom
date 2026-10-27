<?php

namespace Aspectox\LaraJoom\Core\Policies;

use Aspectox\LaraJoom\Admin\Components\com_content\Models\Article;
use Aspectox\LaraJoom\Admin\Components\com_users\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('core.view.article');
    }

    public function view(User $user, Article $article): bool
    {
        return $user->can('core.view.article');
    }

    public function create(User $user): bool
    {
        return $user->can('core.create.article');
    }

    public function update(User $user, Article $article): bool
    {
        return $user->can('core.edit.article');
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->can('core.delete.article');
    }
}
