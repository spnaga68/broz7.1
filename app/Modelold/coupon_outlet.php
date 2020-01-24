<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class coupon_outlet extends Model 
{
    public $timestamps  = false;
    protected $table = 'coupon_outlet';
    protected $primaryKey = 'id';
}
