<?php

namespace Aspectox\LaraJoom\Admin\Components\com_menus\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $table = 'larajoom_menu';

    protected $fillable = [
        'menutype', 'title', 'alias', 'link', 'type', 'published',
        'parent_id', 'level', 'ordering', 'home', 'show_icon',
        'icon', 'access',
    ];

    protected $casts = [
        'published' => 'boolean',
        'home'      => 'boolean',
        'show_icon' => 'boolean',
        'parent_id' => 'integer',
        'level'     => 'integer',
        'ordering'  => 'integer',
        'access'    => 'integer',
    ];

    public function menuType()
    {
        return $this->belongsTo(MenuType::class, 'menutype', 'menutype');
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('ordering');
    }
}
