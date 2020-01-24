<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
class api_resources extends Model
{
	public $timestamps  = false;
	protected $table = 'api_resources';
    protected $primaryKey = 'resource_id';
    //public $primarykey = 'id';
    
}
