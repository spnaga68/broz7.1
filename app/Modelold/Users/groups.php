<?php

namespace App\Model\Users;

use Illuminate\Database\Eloquent\Model;

class groups extends Model
{
	public $timestamps  = false;
	protected $table = 'users_group';    
    protected $primaryKey = 'group_id';
    //
}
