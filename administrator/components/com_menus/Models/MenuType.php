<?php

namespace Aspectox\LaraJoom\Admin\Components\com_menus\Models;

use Illuminate\Database\Eloquent\Model;

class MenuType extends Model
{
    protected $table = 'larajoom_menu_types';

    protected $fillable = [
        'menutype', 'title', 'description', 'client_id', 'ordering',
    ];

    protected $casts = [
        'client_id' => 'integer',
        'ordering'  => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(MenuItem::class, 'menutype', 'menutype');
    }
}
