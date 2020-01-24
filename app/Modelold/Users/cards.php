<?php

namespace App\Model\Users;

use Illuminate\Database\Eloquent\Model;

class cards extends Model
{
	public $timestamps  = false;
	protected $table = 'users_cards';    
    protected $primaryKey = 'card_id';
    //
}
