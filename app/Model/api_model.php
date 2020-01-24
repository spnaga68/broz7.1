<?php

namespace App\Model;
use DB;
use Illuminate\Contracts\Support\JsonableInterface;
use Illuminate\Database\Eloquent\Model;

class Api_model extends Model
{
	
	public static function login_user($post_data =array())
	{
		$user_data = DB::table('users')
						->select('users.id','users.email','users.social_title','users.first_name','users.last_name','users.image','users.status','users.is_verified')
						->where('email',$post_data['email'])
						->where('password', md5($post_data['password']))
						->where('user_type',3)
						->where('is_verified',1)
						->where('status',1)
						->where('active_status','A')
						->get();
		//print_r($user_data);exit;
		return $user_data;
	}

	/* store banner list */
	public static function get_store_banner_list()
	{
		
		$banner_list = DB::table('banner_settings')
						->select('banner_setting_id', 'banner_title', 'banner_subtitle', 'banner_image', 'banner_link')
						->where('banner_type', 2)
						->where('banner_settings.language_type',1)
						->where('status', 1)
						->orderBy('banner_setting_id', 'desc')
						->get();
		return $banner_list;
	}
}
