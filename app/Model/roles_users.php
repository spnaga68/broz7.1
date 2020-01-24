<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
class roles_users extends Model
{
	public $timestamps  = false;
	protected $table = 'roles_users';
    protected $primaryKey = 'ruid';
    //public $primarykey = 'id';
    
   
}
