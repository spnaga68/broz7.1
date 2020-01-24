<?php 
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
//use PushNotification;
use Illuminate\Support\Facades\Redirect;
use DB;
use App;
use URL;
use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Support\Facades\Text;
use App\Model\users;
use App\Model\api;
use Hash;

class Api extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
     public function __construct(Request $data) {
		$post_data = $data->all();
		if(isset($post_data['language']) && $post_data['language']!='' && $post_data['language']==2)
			   {
				   App::setLocale('ar');
			   }
			   else {
				   App::setLocale('en');
			   }
   }
    
    /*
     * student login
     */
    public function login_user(Request $data)
    {
        //echo "asdfasdf";exit;
        $post_data = $data->all();
        //print_r($post_data);exit;
        $rules = [
            'email'    => ['required', 'email'],
            'password' => ['required'],
            'user_type'    => ['required'],
            'device_id'    => ['required_unless:user_type,1,2'],
            'device_token' => ['required_unless:user_type,1,2'],
        ];
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) 
        {
            $j = 0;
            foreach( $validator->errors()->messages() as $key => $value) 
            {
                $errors[] = is_array($value)?implode( ',',$value ):$value;
            }
            $errors = implode( ", \n ", $errors );
            $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => $errors));
        }
        else 
        {
            $user_data = Api::login_user($post_data);
            if(count($user_data) > 0)
            {
                $user_data = $user_data[0];
                /*if($_POST['user_type'] == 4 || $_POST['user_type'] == 5)
                {
                    $res = DB::table('users')
                            ->where('id', $students_data->id)
                            ->update(['device_id' => $_POST['device_id'],'device_token' => $_POST['device_token']]);
                }*/
                $token = JWTAuth::fromUser($user_data,array('exp' => 200000000000));
                $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.User Logged-in Successfully"), "user_id" => $user_data->id, "token" => $token, "email" => $user_data->email, "social_title" => $user_data->social_title, "first_name" => $user_data->first_name, "last_name" => $user_data->last_name, "image" => $user_data->image));
            }
            else 
            {
                $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.These credentials do not match our records")));
            }
        }
        return json_encode($result);
    }
	
}
