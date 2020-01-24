<?php

namespace App\Model;

use Illuminate\Support\Facades\DB;
use Closure;
use Illuminate\Database\Eloquent\Model;

class users extends Model
{
    //
     public $timestamps  = false;
      public static function check_social_login_id($user_type, $login_id)
    {
        if($user_type == 4)
        {
            $check_type = DB::table('users')->select('id','email','mobile')->where('facebook_id', '=', $login_id)->first();
        }
        else if($user_type == 5)
        {
            $check_type = DB::table('users')->select('id','email','mobile')->where('gplus_id', '=', $login_id)->first();
        }
        return $check_type;
    }
    /* To check the email and mobile number exist or not */
    public static function Check_user_credientials($email = '', $mobile = '')
    {
        $user_details = DB::table('users')->select('users.id');
        if(!empty($email))
            $user_details = $user_details->where('email', '=', $email);
        if(!empty($mobile))
            $user_details = $user_details->where('mobile', '=', $mobile);
        $user_details = $user_details->first();
        return $user_details;
    }


/*
public static function Check_driver_credientials($email = '', $mobile = '')
    {
        $user_details = DB::table('drivers')->select('drivers.id');
        if(!empty($email))
            $user_details = $user_details->where('email', '=', $email);
        if(!empty($mobile))
            $user_details = $user_details->where('mobile_number', '=', $mobile );
        $user_details = $user_details->first();
        return $user_details;
    }



*/



}
