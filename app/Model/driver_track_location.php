<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class driver_track_location extends Model 
{
    public $timestamps  = false;
	 protected $table = 'driver_track_location';
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

