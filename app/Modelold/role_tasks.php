<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
class role_tasks extends Model
{
	public $timestamps  = false;
	protected $table = 'role_tasks';
    protected $primaryKey = 'role_task_id';

    
   
}
