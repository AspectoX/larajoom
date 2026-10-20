<?php

namespace Aspectox\LaraJoom\Admin\Components\com_plugins\Models;

use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    protected $table = 'larajoom_extensions';

    protected $primaryKey = 'extension_id';

    protected $fillable = [
        'name', 'type', 'element', 'folder', 'client_id', 'enabled',
        'access', 'ordering', 'params', 'protected', 'locked',
        'manifest_cache', 'state', 'note',
    ];

    protected $casts = [
        'manifest_cache' => 'array',
        'params'         => 'array',
        'enabled'        => 'boolean',
        'protected'      => 'boolean',
        'locked'         => 'boolean',
        'state'          => 'integer',
        'client_id'      => 'integer',
        'access'         => 'integer',
        'ordering'       => 'integer',
    ];

    public function scopePlugins($query)
    {
        return $query->where('type', 'plugin');
    }
}
