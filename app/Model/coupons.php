<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class coupons extends Model 
{
    public $timestamps  = false;
    public function save(array $options = array())
    {
        parent::save();
    }
    public static function boot()
    {
        parent::boot();
        static::saving(function($page)
        {
        });
    }
}
