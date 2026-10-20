<?php
namespace Aspectox\LaraJoom\Admin\Components\com_users\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'larajoom_users';
    protected $fillable = ['name','username','email','password','group_id','activated','params','registerDate'];
    protected $hidden = ['password'];
    public $timestamps = true;
}
