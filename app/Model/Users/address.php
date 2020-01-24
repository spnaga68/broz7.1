<?php

namespace App\Model\Users;

use Illuminate\Database\Eloquent\Model;

class address extends Model
{
	public $timestamps  = false;
	protected $table = 'user_address';    
    protected $primaryKey = 'id';
    //
}
