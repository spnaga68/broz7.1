<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
class api_account extends Model
{
	public $timestamps  = false;
	protected $table = 'api_account';
    protected $primaryKey = 'account_id';
    //public $primarykey = 'id';
    
}
