<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;


class cart_info extends Model 
{    

	public $timestamps  = false;
    protected $table = 'cart_detail';
    protected $primaryKey = 'cart_detail_id';
}
