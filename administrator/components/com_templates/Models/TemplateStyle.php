<?php

namespace Aspectox\LaraJoom\Admin\Components\com_templates\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateStyle extends Model
{
    protected $table = 'larajoom_template_styles';

    protected $fillable = [
        'template', 'title', 'params', 'home',
    ];

    protected $casts = [
        'params' => 'array',
        'home'   => 'boolean',
    ];
}
