<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class return_orders_log extends Model 
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'return_orders_log';
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
