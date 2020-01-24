<?php

namespace App\Model\Users;

use Illuminate\Database\Eloquent\Model;

class addresstype extends Model
{
	public $timestamps  = false;
	protected $table = 'address_type';    
    protected $primaryKey = 'id';
    //
}
