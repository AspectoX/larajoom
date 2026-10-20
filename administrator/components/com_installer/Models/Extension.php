<?php

namespace Aspectox\LaraJoom\Admin\Components\com_installer\Models;

use Illuminate\Database\Eloquent\Model;

class Extension extends Model
{
    protected $table = 'larajoom_extensions';

    protected $primaryKey = 'extension_id';

    protected $fillable = [
        'package_id', 'name', 'type', 'element', 'folder', 'client_id',
        'enabled', 'access', 'ordering', 'params', 'protected',
        'locked', 'manifest_cache', 'state', 'note',
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
        'package_id'     => 'integer',
    ];

    public function scopeRegistered($query)
    {
        return $query->where('state', 0);
    }
}
