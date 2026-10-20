<?php

namespace Aspectox\LaraJoom\Admin\Components\com_content\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'larajoom_content';

    protected $fillable = [
        'title', 'alias', 'introtext', 'fulltext',
        'published', 'catid', 'created_by', 'access', 'hits',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'catid');
    }
}
