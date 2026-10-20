<?php

namespace Aspectox\LaraJoom\Admin\Components\com_modules\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table = 'larajoom_modules';

    protected $fillable = [
        'title', 'module', 'position', 'published', 'ordering',
        'showtitle', 'params', 'access',
    ];

    protected $casts = [
        'published' => 'boolean',
        'showtitle' => 'boolean',
        'ordering'  => 'integer',
        'access'    => 'integer',
    ];
}
