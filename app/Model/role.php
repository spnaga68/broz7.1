<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
class role extends Model
{
	public $timestamps  = false;
	protected $table = 'user_roles';
    //protected $primaryKey = 'id';
    //public $primarykey = 'id';
    
   
}
