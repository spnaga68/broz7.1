<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class coupon_users extends Model 
{
    public $timestamps  = false;
    protected $table = 'coupon_users';
    protected $primaryKey = 'id';
}
