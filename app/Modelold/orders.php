<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
class orders extends Model
{
	//public $timestamps  = false;
	protected $table = 'orders';
	protected $fillable = ['ratings'];

}
