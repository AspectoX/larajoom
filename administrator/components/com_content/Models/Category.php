<?php

namespace Aspectox\LaraJoom\Admin\Components\com_content\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'larajoom_categories';

    protected $fillable = [
        'title', 'alias', 'description', 'published', 'access',
    ];

    public function articles()
    {
        return $this->hasMany(Article::class, 'catid');
    }
}
