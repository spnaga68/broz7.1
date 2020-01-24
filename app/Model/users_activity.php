<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;


class users_activity extends Model 
{    
	
   public $timestamps  = false;
   protected $table = 'user_activity_log';
   public function save(array $options = array())
   {
	   //print_r($options); exit;
      // before save code 
     // $this->saving($options); 
     // $this->country_save_before($this,$options);
	  parent::save();
	 // $this->country_save_after($this,$options);
	 // $this->saved($options);
      // after save code
      
     // App::dispatchEvent('Company_Save_Before',array('post'=> $this->getData('post_data'),'company' => $this));
     // $this->save();
      //App::dispatchEvent('Company_Save_After',array('post'=> $this->getData('post_data'),'company' => $this));
     // return $this;
   }
   
   
   
   
   
   public static function boot()
   {
        parent::boot();
        static::saving(function($page)
        {
			//print_r($page); exit;
            // do stuff
        });

       /** static::creating(function($page)
        {
            // do stuff
        });
        * */

        /**static::updating(function($page)
        {
            // do stuff
        });
        */
    }
   
}
