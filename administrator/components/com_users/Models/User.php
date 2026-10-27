<?php
namespace Aspectox\LaraJoom\Admin\Components\com_users\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'larajoom_users';
    protected $fillable = ['name','username','email','password','group_id','enabled','activated','params','registerDate'];
    protected $hidden = ['password'];
    public $timestamps = true;

    public function hasGroup($group): bool
    {
        $required = is_int($group) ? $group : $this->groupIdByName($group);
        if ($required === null) {
            return false;
        }
        return (int) $this->group_id >= $required;
    }

    protected function groupIdByName(string $name): ?int
    {
        $map = [
            'Public'         => 1,
            'Guest'          => 2,
            'Registered'     => 3,
            'Author'         => 4,
            'Editor'         => 5,
            'Publisher'      => 6,
            'Manager'        => 7,
            'Administrator'  => 8,
            'SuperAdmin'     => 9,
        ];
        return $map[$name] ?? null;
    }
}
