<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class user_feedback extends Model 
{
    public $timestamps  = false;
     protected $table = 'user_feedback';
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

