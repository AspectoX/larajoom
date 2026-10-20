<?php
namespace Aspectox\LaraJoom\Admin\Components\com_login\Models;

use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    protected $table = 'larajoom_login';
    protected $fillable = ['username', 'passwd', 'state'];
}
