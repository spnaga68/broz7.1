<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class payment_request_vendors extends Model 
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_request_vendors';
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
