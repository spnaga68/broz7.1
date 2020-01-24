<?php
namespace App\Http\Controllers\Api;

use App;
use App\Http\Controllers\Controller;
use App\Model\contactus;
use App\Model\drivers;
//use PushNotification;
use App\Model\newsletter_subscribers;
use App\Model\outlets;
use App\Model\orders;
use App\Model\outlet_reviews;
use App\Model\product_reviews;
use App\Model\users;
use App\Model\Users\address;
use App\Model\Users\addresstype;
use App\Model\Users\cards;
use App\Model\vendors;
use App\Model\vendors_infos;
use App\Student;
use DB;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Text;
use Image;
use JWTAuth;
//use Services_Twilio;
use Session;
use Tymon\JWTAuth\Exceptions\JWTException;
use URL;
use App\Model\driver_reviews;
use App\Model\customer_cores;
use App\Model\products;

use PHPExcel;
use PHPExcel_IOFactory;
use Maatwebsite\Excel\Facades\Excel;
use  App\Model\user_feedback;
use  App\Model\payment_history;
use Illuminate\Support\Facades\Redirect;

//use Twilio;
use Twilio\Rest\Client;

class Account extends Controller
{
    const USER_SIGNUP_EMAIL_TEMPLATE = 1;
    const USERS_WELCOME_EMAIL_TEMPLATE = 3;
    const USERS_FORGOT_PASSWORD_EMAIL_TEMPLATE = 6;
    const USER_CHANGE_PASSWORD_EMAIL_TEMPLATE = 13;
    const VENDORS_REGISTER_EMAIL_TEMPLATE = 4;
    const SUBSCRIBE_EMAIL_TEMPLATE = 19;
    const ADMIN_MAIL_TEMPLATE_CONTACT = 13;
    const USER_MAIL_TEMPLATE_CONTACT = 15;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $data)
    {
        $post_data = $data->all();

        if (isset($post_data['language']) && $post_data['language'] != '' && $post_data['language'] == 2) {
            App::setLocale('ar');
        } else {
            App::setLocale('en');
        }

        // if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
        //     App::setLocale($post_data['language']);
        // } else {
        //     App::setLocale('en');
        // }
        //  file_put_contents($_SERVER['DOCUMENT_ROOT'].'/response.txt', "Hello World. Testing!", FILE_APPEND);
    }

    /*
             * user login
    */
    public function login_user(Request $data)
    {
        $post_data = $data->all();
        $rules = [
            'email' => ['required'],
            'password' => ['required'],
            'login_type' => ['required'],
            // 'user_type'    => ['required'],
            'language' => ['required'],
            'device_id' => ['required_unless:login_type,1,2,3'],
            'device_token' => ['required_unless:login_type,1,2,3'],
        ];
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $errors = $result = array();
        $post_data['email'] = isset($post_data['email']) ? trim(strtolower($post_data['email'])) : '';
        // print_r($post_data['email']);exit;
        $validator = app('validator')->make($post_data, $rules);
        $email = !empty($post_data['email']) ? $post_data['email'] : '';
        $password = !empty($post_data['password']) ? $post_data['password'] : '';
        $validator->after(function ($validator) use ($post_data) {
            if (!empty($post_data['email'])) {
                $user_data = DB::select('SELECT users.id, users.name, users.email, users.social_title, users.first_name, users.last_name, users.image, users.status, users.is_verified, users.facebook_id, users.mobile, orders.id as ordersId FROM users  LEFT JOIN orders
					ON users.id = orders.customer_id  where  users.password = ? AND users.email = ? AND users.user_type = 3   limit 1 ', array(md5($post_data['password']), $post_data['email']));


                //print_r($order);exit();
                //print_r($user_data);exit();

                if (count($user_data) == 0) {
                    $validator->errors()->add('email', 'Invalid login credentials');
                } else {
                    $user_data = $user_data[0];
                    if ($user_data->is_verified == 0) {
                        $validator->errors()->add('email', 'Kinldy verify your mobile');
                    }
                }
            }
        });
        if ($validator->fails()) {
            $user_id = $mobile = 0;
            $phone_verify = 1;
            $errors = array();
            if (!empty($email)) {
                $user_data = DB::select('SELECT users.id, users.name, users.email, users.social_title, users.first_name, users.last_name, users.image, users.status, users.is_verified, users.facebook_id, users.mobile, orders.id as ordersId FROM users  INNER JOIN orders
					ON users.id = orders.customer_id  where users.password = ? AND users.email = ? AND users.user_type = 3 AND orders.customer_id=users.id  limit 1 ', array(md5($post_data['password']), $post_data['email']));
                //$phone_verify = isset($user_data[0]->phone_verify)?$user_data[0]->phone_verify:0;
                $user_id = isset($user_data[0]->id) ? $user_data[0]->id : 0;
                $mobile = isset($user_data[0]->mobile) ? $user_data[0]->mobile : 0;
            }
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => $errors, "phone_verify" => $phone_verify, "user_id" => $user_id, "mobile" => $mobile));
        } else {
            if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
            } else {
            App::setLocale('en');
            }
                $post_data['email'] = trim(strtolower($post_data['email']));
            $user_data = DB::select('SELECT users.id, users.name, users.email, users.social_title, users.first_name, users.last_name, users.image, users.status, users.is_verified, users.facebook_id, users.mobile, orders.id as ordersId FROM users  INNER JOIN orders
					ON users.id = orders.customer_id  where users.password = ? AND users.email = ? AND users.user_type = 3 AND orders.customer_id=users.id  limit 1 ', array(md5($post_data['password']), $post_data['email']));
            $user_data = $user_data[0];
            if (count($user_data) > 0) {
                if ($user_data->is_verified == 0) {
                    $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Please confirm you mail to activation.")));
                } elseif ($user_data->status == 0) {
                    $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Your registration has blocked pls contact Your Admin.")));
                } else {
                    // Check login type based on mobile api parameters
                    if (isset($post_data['login_type']) && !empty($post_data['login_type'])) {
                        //Update the device token & id for Android
                        if ($post_data['login_type'] == 2) {
                            $res = DB::table('users')
                                ->where('id', $user_data->id)
                                ->update(['android_device_token' => $post_data['device_token'], 'android_device_id' => $post_data['device_id'], 'login_type' => $post_data['login_type'], 'user_type' => $post_data['user_type']]);
                        }
                        //Update the device token & id for iOS
                        if ($post_data['login_type'] == 3) {
                            $res = DB::table('users')
                                ->where('id', $user_data->id)
                                ->update(['ios_device_token' => $post_data['device_token'], 'ios_device_id' => $post_data['device_id'], 'login_type' => $post_data['login_type'], 'user_type' => $post_data['user_type']]);
                        }
                    }
                    $token = JWTAuth::fromUser($user_data, array('exp' => 200000000000));

                    $order=DB::select("select orders.id from orders LEFT JOIN users ON orders.customer_id = users.id where orders.customer_id = $user_data->id and orders.order_status!=12 and  orders.order_status!=16 and orders.order_status!=11");
                    //print_r($order);exit();

                    $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.User Logged-in Successfully"), "user_id" => $user_data->id, "token" => $token, "email" => $user_data->email, "name" => $user_data->name, "social_title" => !empty($user_data->social_title) ? $user_data->social_title : '', "first_name" => isset($user_data->first_name) ? $user_data->first_name : "", "last_name" => isset($user_data->last_name) ? $user_data->last_name : "", "image" => isset($user_data->image) ? $user_data->image : "", "mobile" => isset($user_data->mobile) ? $user_data->mobile : "", "facebook_id" => isset($user_data->facebook_id) ? $user_data->facebook_id : "", "ordersId"=>$order, "phone_verify" => isset($user_data->phone_verify) ? $user_data->phone_verify : "", "user_type" => isset($post_data['user_type']) ? (int) $post_data['user_type'] : "0"));
                }
            } else {
                $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Your account is inactive mode. Kindly contact admin.")));
            }
        }
        return $result;
    }

    public function add_user($post_data = array(), $verification_key = "", $pass_string)
    {
        $users = new Users;
        $users->name = $post_data['name'];
        $users->email = isset($post_data['email']) ? strtolower($post_data['email']) : "";
        $users->password = $pass_string;
        $users->user_type = 3;
        $users->gender = $post_data['gender'];
        $users->social_title = ($post_data['gender'] == 'F') ? "Ms." : "Mr.";
        $users->first_name = $post_data['first_name'];
        $users->last_name = $post_data['last_name'];
        $users->ip_address = $_SERVER['REMOTE_ADDR'];
        $users->created_date = date("Y-m-d H:i:s");
        $users->updated_date = date("Y-m-d H:i:s");
        $users->user_created_by = 3;
        $users->login_type = 1;
        //Check if the login type from mobile app update the device details here
        if (isset($post_data['login_type']) && !empty($post_data['login_type'])) {
            //Store Android Device details
            if ($post_data['login_type'] == 2) {
                $users->login_type = 2;
                $users->android_device_id = isset($post_data['device_id']) ? $post_data['device_id'] : '';
                $users->android_device_token = isset($post_data['device_token']) ? $post_data['device_token'] : '';
            }
            //Store iOS Device details
            if ($post_data['login_type'] == 3) {
                $users->login_type = 3;
                $users->ios_device_id = isset($post_data['device_id']) ? $post_data['device_id'] : '';
                $users->ios_device_token = isset($post_data['device_token']) ? $post_data['device_token'] : '';
            }
        }
        $users->facebook_id = isset($post_data['facebook_id']) ? $post_data['facebook_id'] : '';
        $users->verification_key = $verification_key;
        $users->save();
        $users->id;
        if ($post_data['image_url'] != "") {
            $url = $post_data['image_url'];
            $data = file_get_contents($url);
            $fileName = base_path() . '/public/assets/admin/base/images/admin/profile/' . $users->id . '.jpg';

            $filethumb = base_path() . '/public/assets/admin/base/images/admin/profile/thumb/' . $users->id . '.jpg';
            //$fileName = 'fb_profilepic.jpg';
            $file = fopen($fileName, 'w+');
            fputs($file, $data);
            fclose($file);

            $file2 = fopen($filethumb, 'w+');
            fputs($file2, $data);
            fclose($file2);

            $users->image = $users->id . '.jpg';
            $users->save();
        }
        return $users->id;
    }

    /* public function signup_fb_user(Request $data)
            {
                $post_data = $data->all();
                //print_r($post_data);exit;
                if($post_data['language']==2)
                {
                    App::setLocale('ar');
                }
                else
                {
                    App::setLocale('en');
                }
                $users     = new Users;
                $usertoken = sha1(uniqid(Text::random('alnum', 32), TRUE));
                $verification_key    = Text::random('alnum',12);
                $string      = str_random(8);
                $pass_string = md5($string);
                if(!$users->user_token)
                {
                    $users->user_token = $usertoken;
                }
                if($post_data['email'] !="" || $post_data['facebook_id'] !="")
                {
                    $check_user=DB::table('users')
                             ->select('*')
                             ->where('email','=',strtolower($post_data['email']))
                             ->orwhere('facebook_id','=',strtolower($post_data['facebook_id']))
                             ->get();
                    if(count($check_user) == 0)
                    {
                        $user_id = $this->add_user($post_data,$verification_key,$pass_string);
                        if($post_data['email'] != "")
                        {
                            $template = DB::table('email_templates')
                                            ->select('from_email', 'from','subject', 'template_id','content')
                                            ->where('template_id','=',self::USER_SIGNUP_EMAIL_TEMPLATE)
                                            ->get();
                            if(count($template))
                            {
                                $from      = $template[0]->from_email;
                                $from_name = $template[0]->from;
                                $subject   = $template[0]->subject;
                                if(!$template[0]->template_id)
                                {
                                    $template  = 'mail_template';
                                    $from      = getAppConfigEmail()->contact_email;
                                    $subject   = "Welcome to ".getAppConfig()->site_name;
                                    $from_name = "";
                                }
                                $url1 ='<a href="'.url('/').'/signup/confirmation?key='.$verification_key.'&email='.strtolower($post_data['email']).'&u_password='.$string.'"> This Confirmation Link </a>';
                                $user = array("name"=>ucfirst($post_data['name']),"email"=> strtolower($post_data['email']));
                                $content = array("customer" => $user,"confirmation_link" => $url1);
                                $email   = smtp($from, $from_name, strtolower($post_data['email']), $subject, $content, $template);
                            }
                            $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.Registration has been completed. Please verify your email to activation.")));
                        }
                        $user_data = DB::table('users')
                                ->select('users.id','users.name','users.email','users.social_title','users.first_name','users.last_name','users.image','users.status','users.is_verified','users.facebook_id','users.mobile')
                                ->where('id',$user_id)
                                ->first();
                        $token = JWTAuth::fromUser($user_data,array('exp' => 200000000000));
                        $user_image = url('/assets/admin/base/images/default_avatar_male.jpg');
                        if(file_exists(base_path().'/public/assets/admin/base/images/admin/profile/'.$user_data->image)&& $user_data->image!='' ) {
                            $user_image =URL::to("assets/admin/base/images/admin/profile/".$user_data->image);
                        }
                        $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.Can't get email"), "user_id" => $user_data->id, "token" => $token, "email" => "","name"=>$user_data->name, "social_title" => $user_data->social_title, "first_name" => $user_data->first_name, "last_name" => $user_data->last_name, "image" => $user_image,"facebook_id"=>$user_data->facebook_id,"mobile"=>($user_data->mobile)?$user_data->mobile:''));
                    }
                    else
                    {
                        $user_data = DB::table('users')
                                ->select('users.id','users.name','users.email','users.social_title','users.first_name','users.last_name','users.image','users.status','users.is_verified','users.mobile')
                                ->where('email',strtolower($post_data['email']))
                                ->where('user_type',3)
                                ->where('status',1)
                                ->where('is_verified',1)
                                ->where('active_status','A')
                                ->first();
                        if(count($user_data) > 0)
                        {
                            $update_array = array();
                            if($user_data->first_name == "")
                            {
                                $update_array['first_name'] = $post_data['first_name'];
                                $update_array['last_name'] = $post_data['last_name'];
                                $update_array['gender'] = $post_data['gender'];
                            }
                            /*if($user_data->image != "")
                            {
                                $url = $post_data['image_url'];
                                $data = file_get_contents($url);
                                $fileName  = base_path() .'/public/assets/admin/base/images/admin/profile/'.$user_data->id.'.jpg';
                                $filethumb  = base_path().'/public/assets/admin/base/images/admin/profile/thumb/'.$user_data->id.'.jpg';
                                $file = fopen($fileName, 'w+');
                                fputs($file, $data);
                                fclose($file);
                                $file2 = fopen($filethumb, 'w+');
                                fputs($file2, $data);
                                fclose($file2);
                                $image = $user_data->id.'.jpg';
                                $update_array['image'] = $image;
                            }

                            if ($post_data['login_type'] == 2) {
                            $update_array['login_type'] = 2;
                            $update_array['android_device_id'] = isset($post_data['device_id']) ? $post_data['device_id'] : '';
                            $update_array['android_device_token'] = isset($post_data['device_token']) ? $post_data['device_token'] : '';
                            }
                            if ($post_data['login_type'] == 3) {
                            $update_array['login_type'] = 3;
                            $update_array['ios_device_id'] = isset($post_data['device_id']) ? $post_data['device_id'] : '';
                            $update_array['ios_device_token'] = isset($post_data['device_token']) ? $post_data['device_token'] : '';
                            }
                            if(count($update_array)>0)
                            {
                                $res = DB::table('users')
                                    ->where('id', $user_data->id)
                                    ->update($update_array);
                            }
                            $user_data = DB::table('users')
                                ->select('users.id','users.name','users.email','users.social_title','users.first_name','users.last_name','users.image','users.status','users.is_verified','users.facebook_id','users.mobile')
                                ->where('email',strtolower($post_data['email']))
                                ->where('user_type',3)
                                ->where('status',1)
                                ->where('active_status','A')
                                ->first();
                            $token = JWTAuth::fromUser($user_data,array('exp' => 200000000000));
                            $user_image = url('/assets/admin/base/images/default_avatar_male.jpg');
                            if(file_exists(base_path().'/public/assets/admin/base/images/admin/profile/'.$user_data->image)&& $user_data->image!='' ) {
                                $user_image =URL::to("assets/admin/base/images/admin/profile/".$user_data->image);
                            }
                            $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.User Logged-in Successfully"), "user_id" => $user_data->id, "token" => $token, "email" => $user_data->email,"name"=>$user_data->name, "social_title" => $user_data->social_title, "first_name" => $user_data->first_name, "last_name" => $user_data->last_name, "image" => $user_image,"facebook_id"=>$user_data->facebook_id,"mobile"=>($user_data->mobile)?$user_data->mobile:''));
                        }
                        else
                        {
                            $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.These credentials do not match our records")));
                        }
                    }
                }
                else
                {
                    $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Email field is required.")));
                }
                return json_encode($result,JSON_UNESCAPED_UNICODE);
    */
    public function signup_fb_user(Request $data)
    {
        $post_data = $data->all();
       if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $users = new Users;
        $usertoken = sha1(uniqid(Text::random('alnum', 32), true));
        $verification_key = Text::random('alnum', 12);
        $string = str_random(8);
        $pass_string = md5($string);
        if (!$users->user_token) {
            $users->user_token = $usertoken;
        }

        if ($post_data['email'] != "" && $post_data['facebook_id'] != "") {
            $email = trim(strtolower($post_data['email']));
            $check_user = DB::table('users')
                ->select('id', 'email', 'first_name', 'image', 'social_title')
            //~ ->where('email', '=', trim(strtolower($post_data['email'])))
            //~ ->orwhere('facebook_id', '=', strtolower($post_data['facebook_id']))
                ->where('facebook_id', '=', strtolower($post_data['facebook_id']))
                ->first();
            if (count($check_user) == 0) {
                $check_user1 = DB::table('users')
                    ->select('id', 'email', 'first_name', 'image', 'social_title')
                    ->where('email', '=', $email)
                    ->first();
                if (count($check_user1) == 0) {
                    $user_id = $this->add_user($post_data, $verification_key, $pass_string);
                    /*if ($post_data['email'] != "")
                                            {
                                                $template = DB::table('email_templates')
                                                        ->select('from_email', 'from', 'subject', 'template_id', 'content')
                                                        ->where('template_id', '=', self::USER_SIGNUP_EMAIL_TEMPLATE)
                                                        ->get();
                                                if (count($template))
                                                {
                                                    $from = $template[0]->from_email;
                                                    $from_name = $template[0]->from;
                                                    $subject = $template[0]->subject;
                                                    if (!$template[0]->template_id)
                                                    {
                                                        $template = 'mail_template';
                                                        $from = getAppConfigEmail()->contact_email;
                                                        $subject = "Welcome to " . getAppConfig()->site_name;
                                                        $from_name = "";
                                                    }
                                                    $url1 = '<a href="' . url('/') . '/signup/confirmation?key=' . $verification_key . '&email=' . trim(strtolower($post_data['email'])) . '&u_password=' . encrypt($string) . '"> This Confirmation Link </a>';
                                                    $user = array("name" => ucfirst($post_data['name']), "email" => strtolower($post_data['email']));
                                                    $content = array("customer" => $user, "confirmation_link" => $url1);
                                                    $email = smtp($from, $from_name, strtolower($post_data['email']), $subject, $content, $template);
                                                }
                                                $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.Registration has been completed. Please verify your email to activation.")));
                    */
                    $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.Registration has been completed.")));
                } else {
                    $user_id = $check_user1->id;
                    $users = Users::find($user_id);
                    $update_array = array();
                    $update_array['email'] = $post_data['email'];
                    //$update_array['mobile']      = isset($post_data['mobile'])?preg_replace("/[^+0-9]+/", "", $post_data['mobile']):$users->mobile;
                    $update_array['facebook_id'] = $post_data['facebook_id'];
                    if ($check_user1->first_name == "") {
                        $update_array['first_name'] = $post_data['first_name'];
                        $update_array['last_name'] = $post_data['last_name'];
                        $update_array['gender'] = $post_data['gender'];
                        $update_array['user_type'] = 4;
                    }
                    if ($check_user1->image != "") {
                        $url = $post_data['image_url'];
                        $data = file_get_contents($url);
                        $fileName = base_path() . '/public/assets/admin/base/images/admin/profile/' . $user_id . '.jpg';
                        $filethumb = base_path() . '/public/assets/admin/base/images/admin/profile/thumb/' . $user_id . '.jpg';
                        $file = fopen($fileName, 'w+');
                        fputs($file, $data);
                        fclose($file);
                        $file2 = fopen($filethumb, 'w+');
                        fputs($file2, $data);
                        fclose($file2);
                        $image = $user_id . '.jpg';
                        $update_array['image'] = $image;
                        $update_array['user_type'] = 4;
                    }
                    if ($post_data['login_type'] == 2) {
                        $update_array['login_type'] = 2;
                        $update_array['android_device_id'] = isset($post_data['device_id']) ? $post_data['device_id'] : '';
                        $update_array['android_device_token'] = isset($post_data['device_token']) ? $post_data['device_token'] : '';
                        $update_array['user_type'] = 4;
                    }
                    if ($post_data['login_type'] == 3) {
                        $update_array['login_type'] = 3;
                        $update_array['ios_device_id'] = isset($post_data['device_id']) ? $post_data['device_id'] : '';
                        $update_array['ios_device_token'] = isset($post_data['device_token']) ? $post_data['device_token'] : '';
                        $update_array['user_type'] = 4;
                    }
                    if (count($update_array) > 0) {
                        $res = DB::table('users')->where('id', $user_id)->update($update_array);
                    }
                }
                $user_data = DB::table('users')
                    ->select('users.id', 'users.name', 'users.email', 'users.social_title', 'users.first_name', 'users.last_name', 'users.image', 'users.status', 'users.is_verified', 'users.facebook_id', 'users.mobile', 'users.user_type')
                    ->where('id', $user_id)
                    ->first();

                $user_signup_image = url('/assets/admin/base/images/default_avatar_male.jpg');
                if (file_exists(base_path() . '/public/assets/admin/base/images/admin/profile/' . $user_data->image) && $user_data->image != '') {
                    $user_signup_image = URL::to("assets/admin/base/images/admin/profile/" . $user_data->image . '?' . time());
                }
                $token = JWTAuth::fromUser($user_data, array('exp' => 200000000000));
                $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.Registration has been completed successfully"), "user_id" => $user_data->id, "token" => $token, "email" => $user_data->email, "name" => $user_data->name, "social_title" => !empty($user_data->social_title) ? $user_data->social_title : '', "first_name" => $user_data->first_name, "last_name" => $user_data->last_name, "image" => $user_signup_image, "facebook_id" => $user_data->facebook_id, "mobile" => ($user_data->mobile) ? $user_data->mobile : '', "user_type" => isset($user_data->user_type) ? $user_data->user_type : "0"));
            } else {
                $user_id = $check_user->id;
                $update_array = array();
                $update_array['email'] = $post_data['email'];
                //  $update_array['mobile']      = isset($post_data['mobile'])?preg_replace("/[^+0-9]+/", "", $post_data['mobile']):'';
                $update_array['facebook_id'] = $post_data['facebook_id'];
                if ($check_user->first_name == "") {
                    $update_array['first_name'] = $post_data['first_name'];
                    $update_array['last_name'] = $post_data['last_name'];
                    $update_array['gender'] = $post_data['gender'];
                    $update_array['user_type'] = 4;
                }
                if ($check_user->image != "") {
                    $url = $post_data['image_url'];
                    $data = file_get_contents($url);
                    $fileName = base_path() . '/public/assets/admin/base/images/admin/profile/' . $user_id . '.jpg';
                    $filethumb = base_path() . '/public/assets/admin/base/images//admin/profile/thumb' . $user_id . '.jpg';
                    $file = fopen($fileName, 'w+');
                    fputs($file, $data);
                    fclose($file);
                    $file2 = fopen($filethumb, 'w+');
                    fputs($file2, $data);
                    fclose($file2);
                    $image = $user_id . '.jpg';
                    $update_array['image'] = $image;
                    $update_array['user_type'] = 4;
                }
                if ($post_data['login_type'] == 2) {
                    $update_array['login_type'] = 2;
                    $update_array['android_device_id'] = isset($post_data['device_id']) ? $post_data['device_id'] : '';
                    $update_array['android_device_token'] = isset($post_data['device_token']) ? $post_data['device_token'] : '';
                    $update_array['user_type'] = 4;
                }
                if ($post_data['login_type'] == 3) {
                    $update_array['login_type'] = 3;
                    $update_array['ios_device_id'] = isset($post_data['device_id']) ? $post_data['device_id'] : '';
                    $update_array['ios_device_token'] = isset($post_data['device_token']) ? $post_data['device_token'] : '';
                    $update_array['user_type'] = 4;
                }
                if (count($update_array) > 0) {
                    $res = DB::table('users')->where('id', $user_id)->update($update_array);
                }
                $user_data = DB::table('users')
                    ->select('users.id', 'users.name', 'users.email', 'users.social_title', 'users.first_name', 'users.last_name', 'users.image', 'users.status', 'users.is_verified', 'users.facebook_id', 'users.mobile', 'users.user_type')
                    ->where('id', $user_id)
                    ->first();
                $user_signup_image = url('/assets/admin/base/images/default_avatar_male.jpg');
                if (file_exists(base_path() . '/public/assets/admin/base/images/admin/profile/' . $user_data->image) && $user_data->image != '') {
                    $user_signup_image = URL::to("assets/admin/base/images/admin/profile/" . $user_data->image);
                }
                $token = JWTAuth::fromUser($user_data, array('exp' => 200000000000));
                $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.User Logged-in Successfully"), "user_id" => $user_data->id, "token" => $token, "email" => $user_data->email, "name" => $user_data->name, "social_title" => !empty($user_data->social_title) ? $user_data->social_title : '', "first_name" => $user_data->first_name, "last_name" => $user_data->last_name, "image" => $user_signup_image, "facebook_id" => $user_data->facebook_id, "mobile" => ($user_data->mobile) ? $user_data->mobile : '', "user_type" => isset($user_data->user_type) ? $user_data->user_type : "0"));
            }
        } else {
            $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Email field is required.")));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    public function update_password(Request $data)
    {
        $data_all = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $rules = [
            'user_id' => ['required'],
            'token' => ['required'],
            'old_password' => ['required'],
            'password' => ['required', 'confirmed'],
            'password_confirmation' => ['required'],
        ];
        $errors = $result = array();

        $validator = app('validator')->make($data->all(), $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => $errors, "Error" => trans("messages.Error List")));
        } else {
            $errors = '';
            $password = $data_all['password'];
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*?[0-9])[a-zA-Z\d]{6,}$/', $password)) {
                $errors .= $validator->errors()->add('password', 'The password Minimum 6 characters at least 1 Uppercase Alphabet, 1 Lowercase Alphabet and 1 Number.');
            }
            if ($errors != '') {
                $result = array("response" => array("httpCode" => 400, "status" => true, "Message" => trans("messages.The password Minimum 6 characters at least 1 Uppercase Alphabet, 1 Lowercase Alphabet and 1 Number.")));
            } else {
                $check_auth = JWTAuth::toUser($data_all['token']);
                //Get new password details from posts

                $old_password = $data_all['old_password'];
                $string = $data_all['password'];
                $pass_string = md5($string);
                $session_userid = $data_all['user_id'];
                $users = DB::table('users')
                    ->select('users.id', 'users.first_name', 'users.last_name', 'users.email')
                    ->where('users.id', $session_userid)
                    ->where('users.password', md5($old_password))
                    ->where('users.status', 1)
                    ->get();

                if (count($users) > 0) {
                    $users_data = $users[0];
                    //Sending the mail to student
                    $template = DB::table('email_templates')
                        ->select('from_email', 'from', 'subject', 'template_id', 'content')
                        ->where('template_id', '=', self::USER_CHANGE_PASSWORD_EMAIL_TEMPLATE)
                        ->get();
                    if (count($template)) {
                        $from = $template[0]->from_email;
                        $from_name = $template[0]->from;
                        $subject = $template[0]->subject;
                        if (!$template[0]->template_id) {
                            $template = 'mail_template';
                            $from = getAppConfigEmail()->contact_email;
                            $subject = getAppConfig()->site_name . " New Password Request Updated";
                            $from_name = "";
                        }
                        $content = array("name" => ucfirst($users_data->first_name) . ' ' . $users_data->last_name, "email" => $users_data->email, "password" => $string);
                        $email = smtp($from, $from_name, $users_data->email, $subject, $content, $template);
                    }
                    //Update random password to vendors table to coreesponding vendor id
                    $res = DB::table('users')
                        ->where('id', $users_data->id)
                        ->update(['password' => $pass_string]);
                    //After updating new password details logout the session and redirects to login page
                    $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.Your Password Changed Successfully")));
                } else {
                    $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Old password is incorrect")));
                }
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function get_cards(Request $data)
    {
        $post_data = $data->all();
        $cards = DB::table('users_cards')
            ->select('*')
            ->where('user_id', '=', $post_data['user_id'])
            ->orderBy('card_id', 'desc')
            ->get();

        if (count($cards) > 0) {
            $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Card list"), "card_list" => $cards));
        } else {
            $result = array("response" => array("httpCode" => 400, "Message" => trans("messages.No cards found"), "card_list" => $cards));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function card_detail(Request $data)
    {
        $post_data = $data->all();
        $cards = DB::table('users_cards')
            ->select('*')
            ->where('user_id', '=', $post_data['user_id'])
            ->where('card_id', '=', $post_data['card_id'])
            ->orderBy('card_id', 'desc')
            ->first();

        if (count($cards) > 0) {
            $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Card detail"), "card_detail" => $cards));
        } else {
            $result = array("response" => array("httpCode" => 400, "Message" => trans("messages.No cards found"), "card_detail" => $cards));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    public function delete_address(Request $data)
    {
        $post_data = $data->all();
       // if (isset($post_data['language']) && $post_data['language'] == 2) {
       //      App::setLocale('ar');
       //  } else {
       //      App::setLocale('en');
       //  }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $address = DB::table('user_address')
            ->select('*')
            ->where('user_id', '=', $post_data['user_id'])
            ->where('id', '=', $post_data['address_id'])
            ->first();
        if (count($address) > 0) {
            DB::table('user_address')
                ->where('id', $post_data['address_id'])
                ->where('user_id', $post_data['user_id'])
                ->delete();
            $result = array("httpCode" => 200, "status" => 1, "message" => trans("messages.Address deleted Successfully"), "data" => $address);
        } else {
            $result = array("httpCode" => 200, "status" => 2, "message" => trans("messages.No Address found"));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function delete_card(Request $data)
    {
        $post_data = $data->all();
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $cards = DB::table('users_cards')
            ->select('*')
            ->where('user_id', '=', $post_data['user_id'])
            ->where('card_id', '=', $post_data['card_id'])
            ->orderBy('card_id', 'desc')
            ->first();
        if (count($cards) > 0) {
            DB::table('users_cards')
                ->where('card_id', $post_data['card_id'])
                ->where('user_id', $post_data['user_id'])
                ->delete();
            $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Card deleted Successfully")));
        } else {
            $result = array("response" => array("httpCode" => 400, "Message" => trans("messages.No cards found"), "card_detail" => $cards));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function update_card(Request $data)
    {
        $post_data = $data->all();

       // if (isset($post_data['language']) && $post_data['language'] == 2) {
       //      App::setLocale('ar');
       //  } else {
       //      App::setLocale('en');
       //  }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $rules = [
            'credit_card_number' => ['required', 'ccn'],
            'credit_card_expiry' => ['required', 'ccd'],
        ];

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            //echo "asdf";exit;
            $check_auth = JWTAuth::toUser($post_data['token']);
            $Cards = new Cards;
            $Cards = Cards::find($post_data['card_id']);
            $Cards->card_number = $post_data['credit_card_number'];
            $Cards->expiry_date = $post_data['credit_card_expiry'];
            $Cards->created_date = date("Y-m-d H:i:s");
            $Cards->updated_date = date("Y-m-d H:i:s");
            $Cards->card_status = 1;
            $Cards->user_id = $post_data['user_id'];
            $Cards->save();
            $result = array("httpCode" => 200, "status" => 1, "message" => trans("messages.Card Details Saved Successfully"));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function store_card(Request $data)
    {
        $post_data = $data->all();
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $rules = [
            'credit_card_number' => ['required', 'unique:users_cards,card_number', 'ccn'],
            'credit_card_expiry' => ['required', 'ccd'],
        ];
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            $check_auth = JWTAuth::toUser($post_data['token']);
            $Cards = new Cards;
            $Cards->card_number = $post_data['credit_card_number'];
            $Cards->expiry_date = $post_data['credit_card_expiry'];
            $Cards->name_on_card = $post_data['name_on_card'];
            $Cards->created_date = date("Y-m-d H:i:s");
            $Cards->updated_date = date("Y-m-d H:i:s");
            $Cards->card_status = 1;
            $Cards->user_id = $post_data['user_id'];
            $Cards->save();
            $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.Card Details Saved Successfully")));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    public function meditProfile(Request $data)
    {
        $post_data = $data->all();
        $id = $post_data['userId'];
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $rules = [
            'userName' => ['required', 'max:56'],
            //'userEmail' => ['required', 'email', 'max:250', 'unique:users,email'],

            'userEmail' => ['required', 'email', 'max:250', 'unique:users,email,' . $id . ',id'],
            //'last_name' => ['required', 'max:56'],
            // 'phone' => ['required', 'max:50', 'regex:/^\+91\d{10}$/'],
            //'phoneNumber' => ['required', 'max:50', 'regex:/^\d{10}$/'],
            'countryCode' => ['required'],
            'gender' => ['required'],
        ];
        //regex:/^(\+91)+\d{10}/
        //regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result =  array( "status" => 2, "Error" => trans("messages.Error List"), "message" => $errors);
        } else {
           // $check_auth = JWTAuth::toUser($post_data['token']);

            /*$reg_php = "/^\+91\d{10}$/";
                                   if(preg_match($reg_php, $post_data['phone'])){
                                    echo "1";exit;
                                   }else{
                                    echo "0";exit;
            */
            /*  $filtered_phone_number = filter_var($post_data['phone'], FILTER_SANITIZE_NUMBER_INT);
                            //echo $filtered_phone_number;exit;
                             if (strlen($filtered_phone_number) < 10 || strlen($filtered_phone_number) > 13) {
                                echo "0";exit;
                             } else {
                                 echo "1";exit;
            */
            // Remove "-" from number
            //  $phone_to_check = str_replace("-", "", $filtered_phone_number);
            // store datas in to database
            $users = Users::find($id);
            $users->name = $post_data['userName'];
            //$users->last_name = $post_data['last_name'];
            $users->email = $post_data['userEmail'];
            //$users->name = $post_data['first_name'] . " " . $post_data['last_name'];
            //$users->mobile = $post_data['phone'];
            //$users->country_code = $post_data['countryCode'];
            $users->gender = $post_data['gender'];
            $users->social_title = ($post_data['gender'] == 'F') ? "Ms." : "Mr.";
            //$users->civil_id    = $post_data['civil_id'];
            //$users->cooperative = $post_data['cooperative'];
            //$users->member_id   = isset($post_data['member_id'])?$post_data['member_id']:'';
            $users->updated_date = date("Y-m-d H:i:s");
            $users->android_device_id = isset($post_data['android_device_id']) ? $post_data['android_device_id'] : '';
            $users->android_device_token = isset($post_data['android_device_token']) ? $post_data['android_device_token'] : '';
            $users->ios_device_id = isset($post_data['ios_device_id']) ? $post_data['ios_device_id'] : '';
            $users->ios_device_token = isset($post_data['ios_device_token']) ? $post_data['ios_device_token'] : '';
            $users->save();

            // $data=array();
            //  $getCode=DB::table('users')
            //         ->select('users.country_code','users.mobile')
            //         ->where('users.mobile',$phoneNumber)
            //         ->get();

            // if(count($getCode)>0){      
            // foreach ($getCode as $key => $value) {
            //            $data[$key]['countryCode']= $value->country_code;
            //            $data[$key]['mobile']= $value->mobile;
            //         }  
            // }
            
            $list = array("userId" => $users->id, "userEmail" => $users->email, "userName" => $users->name, "socialTitle" => !empty($users->social_title) ? $users->social_title : '', "countryCode" => isset($users->country_code) ? $users->country_code : "", "phoneNumber" => isset($users->mobile) ? $users->mobile : "", "image" => isset($users->image) ? $users->image : "", "facebookId" => isset($users->facebook_id) ? $users->facebook_id : "", "phoneVerify" => isset($users->phone_verify) ? $user_data->phone_verify : "");

            $result = array("status" => 1, "message" => trans("messages.User information has been updated successfully"),"detail"=>$list );

            //$result =  array("httpCode" => 200, "status" => 1, "message" => trans("messages.User information has been updated successfully"), "userId" => $users->id, "email" => $users->email, "name" => $users->name, "socialTitle" => !empty($users->social_title) ? $users->social_title : '', "firstName" => isset($users->first_name) ? $users->first_name : "", "lastName" => isset($users->last_name) ? $users->last_name : "", "image" => isset($users->image) ? $users->image : "", "mobile" => isset($users->mobile) ? $users->mobile : "", "facebookId" => isset($users->facebook_id) ? $users->facebook_id : "", "phoneVerify" => isset($users->phone_verify) ? $user_data->phone_verify : "");
        }
        //return json_encode($result);
        //1312312332423422
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function signup_user(Request $data)
    {
        $post_data = $data->all();
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $rules = [
            'first_name' => ['required', 'alpha', 'max:56'],
            // 'last_name' => ['required','alpha', 'max:56'],
            'email' => ['required'],
            'password' => ['required', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{6,}$/'],
            //'phone'      => ['required', 'max:50','regex:^(\+\d{1,2}\s)?\(?\d{3}\)?[\s.-]?\d{3}[\s.-]?\d{4}$'],
            'phone' => ['required'],
            'gender' => ['required'],
            'terms_condition' => ['required'],
            'login_type' => ['required'],
            'device_id' => ['required_unless:login_type,1,2,3'],
            'device_token' => ['required_unless:login_type,1,2,3'],
            'image' => ['mimes:png,jpeg,bmp', 'max:2024'],
        ];

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            $errors = '';
            $password = $post_data['password'];
            if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/', $password)) {
                $errors .= $validator->errors()->add('password', 'Password must contain at least 6 characters, one Uppercase letter and one number');
            }
            if ($errors != '') {
                $result = array("response" => array("httpCode" => 400, "status" => true, "Message" => trans("messages.The password Minimum 6 characters at least 1 Uppercase Alphabet, 1 Lowercase Alphabet and 1 Number.")));
            } else {
                $user_data = DB::select('SELECT users.id, users.name, users.email, users.social_title, users.first_name, users.last_name, users.image, users.status, users.is_verified,users.user_type, users.facebook_id, users.mobile FROM users where (users.email = ? OR users.mobile = ?) AND users.user_type=3  limit 1', array($post_data['email'], $post_data['phone']));

                if (count($user_data) > 0) {
                    $user_data = $user_data[0];
                    if ($user_data->is_verified == 0) {
                        $otp = rand(100000, 999999);
                        $number = $user_data->mobile;
                        $message = 'You have received OTP password for ' . getAppConfig()->site_name . '. Your OTP code is ' . $otp . '. This code can be used only once and dont share this code with anyone.';
                        $twilo_sid = TWILIO_ACCOUNTSID;
                        $twilio_token = TWILIO_AUTHTOKEN;
                        $from_number = TWILIO_NUMBER;
                        /*$twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
                        $twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
                        $from_number = "+14783471785";*/
                        //$from_number = "+15105551234";


                        //$client = new Services_Twilio($twilo_sid, $twilio_token);
                        $client = new Client($twilo_sid, $twilio_token);

                        //print_r ($client);exit;
                        // Create an authenticated client for the Twilio API
                        try {
                           /* $m = $client->account->messages->sendMessage(
                                $from_number, // the text will be sent from your Twilio number
                                $number, // the phone number the text will be sent to
                                $message // the body of the text message
                            );*/
                            $m =  $client->messages->create($number, array('from' => $from_number, 'body' => $message));


                            $users_data = Users::find($user_data->id);
                            $users_data->phone_otp = $otp;
                            $users_data->save();
                        } catch (Exception $e) {
                            $result = array("response" => array("httpCode" => 400, "Message" => $e->getMessage()));
                            return json_encode($result);
                        } catch (\Services_Twilio_RestException $e) {
                            $result = array("response" => array("httpCode" => 400, "Message" => $e->getMessage()));
                            return json_encode($result);
                        }

                        $token = JWTAuth::fromUser($user_data, array('exp' => 200000000000));
                        $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.OTP sent"), "mobile_number" => $post_data['phone'], "user_id" => $user_data->id, "token" => $token, "mobile" => $user_data->mobile, "user_type" => $user_data->user_type, "email" => isset($user_data->email) ? $user_data->email : "", "name" => isset($user_data->name) ? $user_data->name : "", "facebook_id" => $user_data->facebook_id));
                        return json_encode($result);
                    }
                }

                $users = new Users;
                $usertoken = sha1(uniqid(Text::random('alnum', 32), true));
                if (!$users->user_token) {
                    $users->user_token = $usertoken;
                }
                $password = $post_data['password'];
                $users->first_name = $post_data['first_name'];
                $users->last_name = isset($post_data['last_name']) ? $post_data['last_name'] : "";
                $users->name = $post_data['first_name'] . " " . $post_data['last_name'];

                $users->password = md5($post_data['password']);
                $users->gender = $post_data['gender'];
                $users->social_title = ($post_data['gender'] == 'F') ? "Ms." : "Mr.";
                //$users->civil_id    = $post_data['civil_id'];
                //$users->cooperative = $post_data['cooperative'];
                //$users->member_id   = isset($post_data['member_id'])?$post_data['member_id']:'';
                $users->user_type = 3;
                $users->is_verified = 0;
                $users->ip_address = $_SERVER['REMOTE_ADDR'];
                $users->created_date = date("Y-m-d H:i:s");
                $users->updated_date = date("Y-m-d H:i:s");
                $users->user_created_by = 3;
                $users->login_type = 1;
                //Check if the login type from mobile app update the device details here
                if (isset($post_data['login_type']) && !empty($post_data['login_type'])) {
                    //Store Android Device details
                    if ($post_data['login_type'] == 2) {
                        $users->login_type = 2;
                        $users->android_device_id = isset($post_data['device_id']) ? $post_data['device_id'] : '';
                        $users->android_device_token = isset($post_data['device_token']) ? $post_data['device_token'] : '';
                    }
                    //Store iOS Device details
                    if ($post_data['login_type'] == 3) {
                        $users->login_type = 3;
                        $users->ios_device_id = isset($post_data['device_id']) ? $post_data['device_id'] : '';
                        $users->ios_device_token = isset($post_data['device_token']) ? $post_data['device_token'] : '';
                    }
                }
                $verification_key = Text::random('alnum', 12);
                $users->verification_key = $verification_key;
                $users->save();
                if (isset($post_data['image']) && $post_data['image'] != '') {
                    $destinationPath = base_path() . '/public/assets/admin/base/images/admin/profile/'; // upload path
                    $imageName = $users->id . '.' . $post_data['image']->getClientOriginalExtension();
                    $data->file('image')->move($destinationPath, $imageName);
                    $destinationPath1 = url('/assets/admin/base/images/admin/profile/' . $imageName);
                    Image::make($destinationPath1)->fit(75, 75)->save(base_path() . '/public/assets/admin/base/images/admin/profile/thumb/' . $imageName);
                    Image::make($destinationPath1)->fit(260, 170)->save(base_path() . '/public/assets/admin/base/images/admin/profile/' . $imageName);

                    $users->image = $imageName;
                    $users->save();
                }
                $imageName = url('/assets/admin/base/images/default_avatar_male.jpg');
                if (file_exists(base_path() . '/public/assets/admin/base/images/admin/profile/' . $users->image) && $users->image != '') {
                    $imageName = URL::to("assets/admin/base/images/admin/profile/" . $users->image);
                }
                if ($post_data['login_type'] == 2 || $post_data['login_type'] == 3 || $post_data['login_type'] == 1) {
                    $otp = rand(100000, 999999);
                    $app_config = getAppConfig();
                    $number = $post_data['phone'];
                    $message = 'You have received OTP password for ' . getAppConfig()->site_name . '. Your OTP code is ' . $otp . '. This code can be used only once and dont share this code with anyone.';
                    $twilo_sid = TWILIO_ACCOUNTSID;
                        $twilio_token = TWILIO_AUTHTOKEN;
                        $from_number = TWILIO_NUMBER;
                        /*$twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
                        $twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
                        $from_number = "+14783471785";*/
                    //$client = new Services_Twilio($twilo_sid, $twilio_token);
                    $client = new Client($twilo_sid, $twilio_token);

                    //print_r ($client);exit;
                    // Create an authenticated client for the Twilio API
                    try {
                        /*$m = $client->account->messages->sendMessage(
                            $from_number, // the text will be sent from your Twilio number
                            $number, // the phone number the text will be sent to
                            $message // the body of the text message
                        );*/
                        $m =  $client->messages->create($number, array('from' => $from_number, 'body' => $message));


                        $users->phone_otp = $otp;
                        $users->updated_date = date("Y-m-d H:i:s");
                        $users->email = strtolower($post_data['email']);
                        $users->mobile = preg_replace("/[^+0-9]+/", "", $post_data['phone']);
                        //print_r($users);exit;
                        $users->save();
                        $token = JWTAuth::fromUser($users, array('exp' => 200000000000));
                        $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.OTP sent"), "mobile_number" => $post_data['phone'], "user_id" => $users->id, "token" => $token, "mobile" => $users->mobile, "user_type" => $users->user_type, "guest" => isset($users->guest_type) ? $users->guest_type : "0", "email" => isset($users->email) ? $users->email : "", "name" => isset($users->name) ? $users->name : "", "facebook_id" => $users->facebook_id, "phone_verify" => 1));
                    } catch (Exception $e) {
                        $result = array("response" => array("httpCode" => 400, "Message" => $e->getMessage()));
                        return json_encode($result);
                    } catch (\Services_Twilio_RestException $e) {
                        $result = array("response" => array("httpCode" => 400, "Message" => $e->getMessage()));
                        return json_encode($result);
                    }
                }

                $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.OTP sent"), "mobile_number" => $post_data['phone'], "user_id" => $users->id, "token" => $token, "mobile" => $users->mobile, "user_type" => $users->user_type, "guest" => isset($users->guest_type) ? $users->guest_type : "0", "email" => isset($users->email) ? $users->email : "", "name" => isset($users->name) ? $users->name : "", "facebook_id" => $users->facebook_id, "phone_verify" => 1));
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    /**store register**/

    public function store_register_user(Request $data)
    {
        $rules = [
            'first_name' => ['required', 'alpha', 'max:56'],
            'last_name' => ['required', 'alpha', 'max:56'],
            'terms_condition' => ['required'],
            'email' => ['required', 'email', 'max:250', 'unique:vendors,email'],
            'password' => ['required', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{6,}$/', 'confirmed'],
            'password_confirmation' => ['required', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{6,}$/'],
            'phone_number' => ['required', 'max:15', 'regex:/(^[0-9 +]+$)+/'],
            'vendor_name' => ['required', 'alpha', 'max:56'],
            //'vendor_description'      => ['required'],

        ];
        $post_data = $data->all();
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $error = $result = array();
        $validation = app('validator')->make($post_data, $rules);
        if ($validation->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validation->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 400, "Status" => "Failure", "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            $Vendors = new Vendors;
            $Vendors->first_name = $_POST['first_name'];
            $Vendors->last_name = $_POST['last_name'];
            $Vendors->email = $_POST['email'];
            $Vendors->hash_password = md5($_POST['password']);
            $Vendors->phone_number = preg_replace("/[^+0-9]+/", "", $_POST['phone_number']);
            $Vendors->created_date = date('Y-m-d H:i:s');
            $Vendors->save();
            $this->store_save_after($Vendors, $_POST, 1);
            $result = array("response" => array("httpCode" => 200, "status" => "Success", "Message" => trans("messages.Registration has been completed. Please verify your email to activation.")));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    public static function store_save_after($object, $post, $method = 0)
    {
        if (isset($_POST['vendor_name']) && isset($_POST['vendor_description'])) {
            $vendor_name = $_POST['vendor_name'];
            $vendor_description = $_POST['vendor_description'];
            $data = Vendors_infos::find($object->id);
            if (count($data) > 0) {
                $data->delete();
            }
            $infomodel = new Vendors_infos;
            $infomodel->lang_id = 1;
            $infomodel->id = $object->id;
            $infomodel->vendor_name = $vendor_name;
            $infomodel->vendor_description = $vendor_description;
            $infomodel->save();
            $template = DB::table('email_templates')
                ->select('from_email', 'from', 'subject', 'template_id', 'content')
                ->where('template_id', '=', self::VENDORS_REGISTER_EMAIL_TEMPLATE)
                ->get();
            if (count($template)) {
                $from = $template[0]->from_email;
                $from_name = $template[0]->from;
                $subject = $template[0]->subject;
                if (!$template[0]->template_id) {
                    $template = 'mail_template';
                    $from = getAppConfigEmail()->contact_email;
                    $subject = "Welcome to " . getAppConfig()->site_name;
                    $from_name = "";
                }
                $content = array("vendor_name" => $_POST['vendor_name'], "email" => $_POST['email'], "password" => $_POST['password']);
                $email = smtp($from, $from_name, $_POST['email'], $subject, $content, $template);
            }
            return true;
        }
    }

    public function signup_confirmation(Request $data)
    {
        $post_data = $data->all();
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $verifictaion_key = $post_data['key'];
        $email = strtolower($post_data['email']);
        $password_raw = $post_data['u_password'];
        $success = false;
        $errors = array();
        if ($verifictaion_key && $email) {
            $customer = DB::table('users')
                ->select('*')
                ->where('verification_key', '=', $verifictaion_key)
                ->where('email', '=', $email)
                ->get();
            try {
                if (count($customer)) {
                    $user_data = $customer[0];
                    $verified = $this->_isVerification($verifictaion_key, $email);
                    if ($verified) {
                        $user_id = $this->_isUser($email);
                        $user = Users::find($user_id);
                        $user->is_verified = 1;
                        $user->save();
                        $customer = $user->getAttributes();
                        $template = DB::table('email_templates')
                            ->select('*')
                            ->where('template_id', '=', self::USERS_WELCOME_EMAIL_TEMPLATE)
                            ->get();
                        if (count($template)) {
                            $from = $template[0]->from_email;
                            $from_name = $template[0]->from;
                            $subject = $template[0]->subject;
                            if (!$template[0]->template_id) {
                                $template = 'mail_template';
                                $from = getAppConfigEmail()->contact_mail;
                                $subject = "Welcome to " . getAppConfig()->site_name;
                                $from_name = "";
                            }
                            $customer['password'] = $password_raw;
                            $content = array("customer" => $customer, 'u_password' => $password_raw);
                            $email = smtp($from, $from_name, $user['email'], $subject, $content, $template);
                            $token = JWTAuth::fromUser($user_data, array('exp' => 200000000000));
                            $user_image = url('/assets/admin/base/images/default_avatar_male.jpg');
                            if (file_exists(base_path() . '/public/assets/admin/base/images/admin/profile/' . $user_data->image) && $user_data->image != '') {
                                $user_image = URL::to("assets/admin/base/images/admin/profile/" . $user_data->image);
                            }
                            $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messagesThe email confirmation has been done kindly check your email for further process"), "user_id" => $user_data->id, "token" => $token, "email" => $user_data->email, "name" => $user_data->name, "social_title" => $user_data->social_title, "first_name" => isset($user_data->first_name) ? $user_data->first_name : "", "last_name" => isset($user_data->last_name) ? $user_data->last_name : "", "image" => $user_image, "facebook_id" => isset($user_data->facebook_id) ? $user_data->facebook_id : ""));
                        }
                    } else {
                        $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Email alredy verified.")));
                    }
                } else {
                    $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Invalid key or email.")));
                }
            } catch (Exception $e) {
                $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Problem in server. try again later.")));
                Log::Instance()->add(Log::ERROR, $e);
            }
        } else {
            $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Invalid key or email.")));
        }
        return $result;
    }

    /** Check is verified or not **/
    public function _isVerification($key, $email)
    {
        $db = DB::table('users')->select('id', DB::raw('count(id) AS total_count'))->where('email', '=', $email)->where('verification_key', '=', $key)
            ->where('is_verified', '=', 0)
            ->groupBy('users.id')
            ->get();
        if (count($db)) {
            return $db[0]->total_count > 0 ? true : false;
        }
        return false;
    }

    /** Check is verified or not **/
    public function _isUser($email)
    {
        $db = DB::table('users')->select('id')->where('email', '=', $email)
            ->groupBy('users.id')
            ->get();
        if (count($db)) {
            return $db[0]->id;
        }
        return 0;
    }
    public function get_coperatives()
    {
        $category_id = DB::table('categories')->select('id')->where('url_key', '=', 'cooperative')
            ->first();

        $vendors = array();
        $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Vendors not found"), 'vendors' => $vendors));
        if (isset($category_id->id)) {
            $condition = "(regexp_split_to_array(category_ids,',')::integer[] @> '{" . $category_id->id . "}'::integer[]  and category_ids !='')";
            $query = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.language_id) as totalcount from outlet_infos where outlet_infos.language_id = ' . getCurrentLang() . ' and outlets.id = outlet_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';

            $vendors = Outlets::Leftjoin('vendors', 'vendors.id', '=', 'outlets.vendor_id')
            //->Leftjoin('vendors_infos','vendors_infos.id','=','vendors.id')
                ->Leftjoin('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
                ->select('outlets.id as outlets_id', 'outlet_infos.outlet_name')
                ->whereRaw($condition)
                ->whereRaw($query)
                ->get();
            if (count($vendors) > 0) {
                foreach ($vendors as $cooprative) {
                    $cooprative_list[$cooprative->outlets_id] = $cooprative->outlet_name;
                }
                //return $cooprative_list;
                $result = array("response" => array("httpCode" => 200, "status" => true, "data" => $vendors));
            }
        }
        echo json_encode($result);
        exit;
    }

    public function user_detail(Request $data)
    {
        $post_data = $data->all();
        $rules = [
            'user_id' => ['required', 'integer'],
            'token' => ['required'],
        ];
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("response" =>  array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            try {
                $check_auth = JWTAuth::toUser($post_data['token']);
                $user = get_user_details($post_data['user_id']);
                if (count($user) > 0) {
                    $user->first_name = ($user->first_name != '') ? $user->first_name : '';
                    $user->mobile = ($user->mobile != '') ? $user->mobile : '';
                    $user->last_name = ($user->last_name != '') ? $user->last_name : '';
                    $user->civil_id = ($user->civil_id != '') ? $user->civil_id : '';
                    $user->cooperative_id = ($user->cooperative_id != '') ? $user->cooperative_id : '';
                    $user->cooperative = ($user->cooperative != '') ? $user->cooperative : '';
                    $user->member_id = ($user->member_id != '') ? $user->member_id : '';
                    $imageName = url('/assets/admin/base/images/default_avatar_male.jpg');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/admin/profile/' . $user->image) && $user->image != '') {
                        $imageName = URL::to("assets/admin/base/images/admin/profile/" . $user->image);
                    }
                    $user->image = $imageName;
                    $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.User details"), 'user_data' => array($user)));
                } else {
                    $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.No user found")));
                }
            } catch (JWTException $e) {
                $result =array("response" =>  array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
            } catch (TokenExpiredException $e) {
                $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
            }
        }
        return json_encode($result);
        exit;
    }

    public function forgot_password(Request $data)
    {
        $post_data = $data->all();
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $rules = [
            'email' => ['required', 'email'],
        ];
        $errors = $result = array();

        $validator = app('validator')->make($post_data, $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Error List"), "Error" => $errors));
        } else {
            $email = strtolower($post_data['email']);
            //echo $email;
            $users = DB::table('users')
                ->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.name')
                ->where('email', $email)
                ->where('status', 1)
                ->get();

            //print_r($users);exit;
            if (count($users) > 0) {
                $users_data = $users[0];
                //Generate random password string
                $string = str_random(8);
                $pass_string = md5($string);
                //Sending the mail to universities
                $template = DB::table('email_templates')
                    ->select('from_email', 'from', 'subject', 'template_id', 'content')
                    ->where('template_id', '=', self::USERS_FORGOT_PASSWORD_EMAIL_TEMPLATE)
                    ->get();
                if (count($template)) {
                    $from = $template[0]->from_email;
                    $from_name = $template[0]->from;
                    $subject = $template[0]->subject;
                    if (!$template[0]->template_id) {
                        $template = 'mail_template';
                        $from = getAppConfigEmail()->contact_email;
                        $subject = getAppConfig()->site_name . " Password Request Details";
                        $from_name = "";
                    }
                    $content = array("name" => ucfirst($users_data->name), "email" => $users_data->email, "password" => $string);
                    $email = smtp($from, $from_name, $users_data->email, $subject, $content, $template);
                }
                //Update random password to universities table to coreesponding university id
                $res = DB::table('users')
                    ->where('id', $users_data->id)
                    ->update(['password' => $pass_string]);
                //Show the flash message if successful logged in
                $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.Password was sent your email successfully")));
            } else {
                $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.These email do not match our records")));
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function address_type(Request $data)
    {
        $post_data = $data->all();
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $address_type = array();
        $language_id = getAdminCurrentLang();
        $query = '"address_infos"."language_id" = (case when (select count(*) as totalcount from address_infos where address_infos.language_id = ' . $post_data['language'] . ' and address_type.id = address_infos.address_id) > 0 THEN ' . $post_data['language'] . ' ELSE 1 END)';
        $address_type = Addresstype::Leftjoin('address_infos', 'address_infos.address_id', '=', 'address_type.id')
            ->select('address_type.*', 'address_infos.*')
            ->whereRaw($query)
            ->orderBy('id', 'desc')
            ->get();
        if (count($address_type)) {
            $result = array("response" => array("httpCode" => 200, "status" => true, 'address_type' => $address_type));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function get_city_list(Request $data)
    {
        $post_data = $data->all();
        $language_id = $post_data['language'];
        $data = array();
        $result = array("response" => array("httpCode" => 400, "status" => false, "data" => $data));
        $query = '"cities_infos"."language_id" = (case when (select count(*) as totalcount from cities_infos where cities_infos.language_id = ' . $language_id . ' and cities.id = cities_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $cities = DB::table('cities')
            ->select(DB::raw('cities.* ,cities.id as cid'), 'cities_infos.*')
            ->leftJoin('cities_infos', 'cities_infos.id', '=', 'cities.id')
            ->whereRaw($query)
            ->orderBy('city_name', 'asc')
            ->get();
        if (count($cities)) {
            $result = array("response" => array("httpCode" => 200, "status" => true, 'data' => $cities));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function get_city(Request $data)
    {
        $post_data = $data->all();
        $language_id = $post_data['language'];
        $country_id = $post_data['country_id'];
        $data = array();
        $result = array("response" => array("httpCode" => 400, "status" => false, "data" => $data));
        $query = '"cities_infos"."language_id" = (case when (select count(*) as totalcount from cities_infos where cities_infos.language_id = ' . $language_id . ' and cities.id = cities_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $cities = DB::table('cities')
            ->select(DB::raw('cities.* ,cities.id as cid'), 'cities_infos.*')
            ->leftJoin('cities_infos', 'cities_infos.id', '=', 'cities.id')
            ->where('cities.country_id', '=', $country_id)
            ->whereRaw($query)
            ->orderBy('city_name', 'asc')
            ->get();
        if (count($cities)) {
            $result = array("response" => array("httpCode" => 200, "status" => true, 'data' => $cities));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    public function store_address(Request $data)
    {
        $post_data = $data->all();
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $rules = [
            'address_type' => ['required'],
            'address' => ['required'],
        ];

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }

            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            $check_auth = JWTAuth::toUser($post_data['token']);
            $address = new Address;
            $address->address = $post_data['address'];
            $address->address_type = $post_data['address_type'];
            $address->latitude = $post_data['latitude'];
            $address->longitude = $post_data['longitude'];
            $address->created_date = date("Y-m-d H:i:s");
            $address->modified_date = date("Y-m-d H:i:s");
            $address->active_status = 1;
            $address->user_id = $post_data['user_id'];
            $address->save();
            // print_r($address->id);exit;
            $result = array("httpCode" => 200, "status" => true, "id" => $address->id, "status" => 1, "message" => trans("messages.Address Details Saved Successfully"));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function mstore_address(Request $data)
    {
        $post_data = $data->all();
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $rules = [
            'address_type' => ['required'],
            'address' => ['required'],
        ];

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }

            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            // $check_auth = JWTAuth::toUser($post_data['token']);
            $address = new Address;
            $address->address = $post_data['address'];
            $address->address_type = $post_data['address_type'];
            $address->latitude = $post_data['latitude'];
            $address->longitude = $post_data['longitude'];
            $address->created_date = date("Y-m-d H:i:s");
            $address->modified_date = date("Y-m-d H:i:s");
            $address->active_status = 1;
            $address->user_id = $post_data['user_id'];
            $address->save();
            // print_r($address->id);exit;
            $result = array("status" => 200, "success" => true, "id" => $address->id, "status" => 1, "message" => trans("messages.Address Details Saved Successfully"));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function get_address(Request $data)
    {
        $post_data = $data->all();
        $language_id = $post_data["language"];
        // if ($post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $rules = [
            'userId' => ['required'],

        ];
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            //print_r($error);exit;
            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 200, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            // $address = DB::table('user_address')
            //     ->select("*")
            //     ->where('user_id', '=', $post_data['userId'])

            //     ->orderBy('user_address.id', 'desc')
            //     ->get();


	$address = DB::table('user_address')
					->select('user_address.address', 'user_address.latitude', 'user_address.longitude', 'user_address.address_type', 'user_address.user_id', 'user_address.id as address_id','user_address.land_mark','user_address.home_no','user_address.created_date','user_address.modified_date','user_address.active_status','user_address.type_text','user_address.id'/*,'address_infos.type_text as type_text'*/)
					//->leftJoin('address_infos', 'address_infos.address_id', '=', 'user_address.id')
					->where('user_id', '=', $post_data['userId'])

					->orderBy('user_address.id', 'desc')
					->get();



            if (count($address) > 0) {
                $result = array("httpCode" => 200, "status" => 1, "message" => trans("messages.Address list"), "data" => $address);
            } else {
                $result = array("httpCode" => 200, "status" => 2, "message" => trans("There is no User address available on this userId."), "data" => $address);
            }
        }

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function mget_address(Request $data)
    {
        $post_data = $data->all();
        $language_id = $post_data["language"];
        // if ($post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $rules = [
            'userId' => ['required'],

        ];
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            //print_r($error);exit;
            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 200, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            $address = DB::table('user_address')
                ->select("*")
                ->where('user_id', '=', $post_data['userId'])

                ->orderBy('user_address.id', 'desc')
                ->get();
            if (count($address) > 0) {
                $result = array("httpCode" => 200, "status" => 1, "message" => trans("messages.Address list"), "data" => $address);
            } else {
                $result = array("httpCode" => 200, "status" => 2, "message" => trans("There is no User address available on this userId."), "data" => $address);
            }
        }

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    // public function mget_address(Request $data) {

    // 	$post_data = $data->all();
    // 	$language_id = $post_data["language"];
    // 	if ($post_data['language'] == 2) {
    // 		App::setLocale('ar');
    // 	} else {
    // 		App::setLocale('en');
    // 	}
    // 	$rules = [
    // 		'userId' => ['required'],

    // 	];

    // 	// {
    // 	//          "address": "prasanth extension",
    // 	//          "home_no": "15",
    // 	//          "land_mark": "Sakthi Vinayagar Kovil",
    // 	//          "type_text": "HOME",
    // 	//          "user_id": 511,
    // 	//          "id": 453,
    // 	//          "latitude": 12.25588,
    // 	//          "longitude": 77.255888888,
    // 	//          "address_type": 11
    // 	//      }

    // 	$error = $result = array();
    // 	$validator = app('validator')->make($post_data, $rules);
    // 	if ($validator->fails()) {
    // 		$errors = '';
    // 		$j = 0;
    // 		foreach ($validator->errors()->messages() as $key => $value) {
    // 			$error[] = is_array($value) ? implode(',', $value) : $value;
    // 		}
    // 		//print_r($error);exit;
    // 		$errors = implode(", \n ", $error);
    // 		$result = array("response" => array("httpCode" => 200, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
    // 	} else {

    // 		$address = DB::table('user_address')
    // 			->select('user_address.address', 'user_address.latitude', 'user_address.longitude', 'user_address.address_type', 'user_address.user_id', 'user_address.id as address_id', 'user_address.land_mark', 'user_address.home_no', 'address_infos.type_text as type_text')

    // 			->where('user_id', '=', $post_data['userId'])
    // 			->leftJoin('address_infos', 'address_infos.address_id', '=', 'user_address.id')
    // 			->orderBy('user_address.id', 'desc')
    // 			->get();
    // 		if (count($address) > 0) {
    // 			$result = array("httpCode" => 200, "status" => 1, "message" => trans("messages.Address list"), "data" => $address);
    // 		} else {
    // 			$result = array("httpCode" => 200, "status" => 2, "message" => trans("There is no User address available on this userId."), "data" => $address);
    // 		}
    // 	}

    // 	return json_encode($result, JSON_UNESCAPED_UNICODE);
    // }

    public function address_detail(Request $data)
    {
        $post_data = $data->all();
        $language_id = $post_data["language"];
        $query = '"cities_infos"."language_id" = (case when (select count(*) as totalcount from cities_infos where cities_infos.language_id = ' . $language_id . ' and cities.id = cities_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $query2 = '"countries_infos"."language_id" = (case when (select count(*) as totalcount from countries_infos where countries_infos.language_id = ' . $language_id . ' and countries.id = countries_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

        $address = DB::table('user_address')
            ->select('user_address.address', 'user_address.latitude', 'user_address.longitude', 'user_address.address_type', 'user_address.user_id', 'user_address.id as address_id')
            ->where('user_id', '=', $post_data['user_id'])
            ->where('user_address.id', '=', $post_data['address_id'])
            ->leftJoin('cities', 'cities.id', '=', 'user_address.city_id')
            ->leftJoin('cities_infos', 'cities_infos.id', '=', 'user_address.city_id')
            ->leftJoin('countries', 'countries.id', '=', 'cities.country_id')
            ->leftJoin('countries_infos', 'countries_infos.id', '=', 'countries.id')
            ->orderBy('user_address.id', 'desc')
        //->whereRaw($query)
        // ->whereRaw($query2)
            ->first();
        if (count($address) > 0) {
            $result = array("httpCode" => 200, "status" => 1, "message" => trans("messages.Address detail"), "data" => $address);
        } else {
            $result = array("httpCode" => 400, "status" => 2, "message" => trans("messages.There is no User address available on this userId or addressId."), "data" => $address);
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function update_address(Request $request)
    {
        $rules = [
            'id' => ['required'],

            'address' => ['required'],
            'address_type' => ['required'],
            'latitude' => ['required'],
            'longitude' => ['required'],

        ];
        $post_data = $request->all();
        //$id = $post_data['id'];
        // if ($request['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            // $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("httpCode" => 400, "status" => 2, "Error" => trans("messages.Error List"), "Message" => $errors);
        } else {
            $id = $request->input('id');
            $address = $request->input('address');
            $address_type = $request->input('address_type');
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');

            //$a = 'user_address.id';
            //	echo($id==="-1");exit();
            if ($id != -1) {
                $data = DB::table('user_address')
                    ->where('id', $id)
                    ->update(['address' => $address, 'address_type' => $address_type,
                        'latitude' => $latitude, 'longitude' => $longitude, "type_text" => $request->input('type_text')
                        , "home_no" => $request->input('home_no'), "land_mark" => $request->input('land_mark')
                        , "modified_date" => date("Y-m-d H:i:s")]);

                $dat = DB::select('select id,address_type,latitude,longitude,address,type_text,land_mark,home_no,user_id from user_address where id=?', [$id]);

                $result = array("httpCode" => 200, "status" => 1, 'message' => trans('Address Updated successfully'), 'data' => $dat);
            } else {
                $address = new Address;
                $address->address = $post_data['address'];
                $address->address_type = $post_data['address_type'];
                $address->type_text = $post_data['type_text'];
                $address->latitude = $post_data['latitude'];
                $address->longitude = $post_data['longitude'];
                $address->land_mark = $post_data['land_mark'];
                $address->home_no = $post_data['home_no'];
                $address->created_date = date("Y-m-d H:i:s");
                $address->modified_date = date("Y-m-d H:i:s");
                $address->active_status = 1;
                $address->user_id = $post_data['user_id'];
                $address->save();
                // print_r($address->id);exit;
                $result = array("status" => 200, "success" => true, "id" => $address->id, "status" => 1, "message" => trans("messages.Address Details Saved Successfully"), 'data' => $address);
            }

            return json_encode($result, JSON_UNESCAPED_UNICODE);
            //}
            // else {

            // 	$result = array("httpCode" => 400, "status" => 2, "message" => trans("There is no User address available on this id."));
            // }
            // return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function favourites(Request $data)
    {
        $post_data = $data->all();

        $data = array();
        $rules = [
            'user_id' => ['required'],
            'token' => ['required'],
            'language' => ['required'],
        ];
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("response" => array("httpCode" => 200, "status" => false, "Message" => $errors));
        } else {
            try {
                $check_auth = JWTAuth::toUser($post_data['token']);
                $result = array("response" => array("httpCode" => 200, "status" => false, "Message" => trans("messages.No stores found"), "data" => $data));
                $language_id = $post_data["language"];
                $query = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
                $query1 = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.language_id) as totalcount from outlet_infos where outlet_infos.language_id = ' . getCurrentLang() . ' and outlets.id = outlet_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
                $vendors = DB::table('favorite_vendors')
                    ->select('vendors.id as vendor_id', 'vendors_infos.vendor_name', 'vendors_infos.vendor_description', 'vendors.first_name', 'vendors.last_name', 'vendors.featured_image', 'vendors.logo_image', 'favorite_vendors.status', 'outlets.id as outlet_id', 'outlet_infos.outlet_name', 'outlet_infos.contact_address as outlets_contact_address', 'outlets.delivery_time as outlets_delivery_time', 'outlets.average_rating as outlets_average_rating', 'favorite_vendors.status as favorite_vendors_status', 'vendors.category_ids', 'outlets.delivery_charges_fixed as outlets_delivery_charges_fixed', 'outlets.delivery_charges_variation as outlets_delivery_charges_variation', 'outlets.minimum_order_amount', 'outlets.url_index')
                    ->join('outlets', 'outlets.id', '=', 'favorite_vendors.vendor_id')
                    ->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
                    ->join('vendors', 'vendors.id', '=', 'outlets.vendor_id')
                    ->join('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
                    ->where("favorite_vendors.customer_id", "=", $post_data['user_id'])
                    ->where('vendors.active_status', '=', 1)
                    ->where('favorite_vendors.status', '=', 1)
                    ->whereRaw($query)
                    ->whereRaw($query1)
                    ->get();
                $vendor_list = array();
                if (count($vendors) > 0) {
                    $v = 0;
                    foreach ($vendors as $ven) {
                        $vendor_list[$v]['vendor_id'] = $ven->vendor_id;
                        $vendor_list[$v]['vendor_name'] = $ven->vendor_name;
                        $vendor_list[$v]['vendor_description'] = $ven->vendor_description;
                        $vendor_list[$v]['first_name'] = $ven->first_name;
                        $vendor_list[$v]['last_name'] = $ven->last_name;
                        $featured_image = $logo_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                        if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/thumb/detail/' . $ven->featured_image) && $ven->featured_image != '') {
                            $featured_image = url('/assets/admin/base/images/vendors/thumb/detail/' . $ven->featured_image);
                        }
                        if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $ven->logo_image) && $ven->logo_image != '') {
                            $logo_image = url('/assets/admin/base/images/vendors/logos/' . $ven->logo_image);
                        }
                        $vendor_list[$v]['featured_image'] = $featured_image;
                        $vendor_list[$v]['logo_image'] = $logo_image;
                        $vendor_list[$v]['status'] = $ven->status;
                        $vendor_list[$v]['outlet_id'] = $ven->outlet_id;
                        $vendor_list[$v]['outlet_name'] = $ven->outlet_name;
                        $vendor_list[$v]['url_index'] = $ven->url_index;
                        $vendor_list[$v]['outlets_contact_address'] = $ven->outlets_contact_address;
                        $vendor_list[$v]['outlets_delivery_time'] = $ven->outlets_delivery_time;
                        $vendor_list[$v]['outlets_average_rating'] = ($ven->outlets_average_rating == null) ? 0 : $ven->outlets_average_rating;
                        $vendor_list[$v]['favorite_vendors_status'] = $ven->favorite_vendors_status;
                        $vendor_list[$v]['outlets_delivery_charges_fixed'] = $ven->outlets_delivery_charges_fixed;
                        $vendor_list[$v]['minimum_order_amount'] = $ven->minimum_order_amount;
                        $vendor_list[$v]['outlets_delivery_charges_variation'] = $ven->outlets_delivery_charges_variation;
                        $vendor_list[$v]['logo_image'] = $logo_image;
                        $category_ids = explode(',', $ven->category_ids);
                        $category_name = '';
                        if (count($category_ids) > 0) {
                            foreach ($category_ids as $cate) {
                                $get_category_name = getCategoryListsById($cate);
                                $category_name .= isset($get_category_name->category_name) ? $get_category_name->category_name : '';
                            }
                        }
                        $vendor_list[$v]['category_ids'] = rtrim($category_name, ', ');
                        $v++;
                    }
                }
                if (count($vendors)) {
                    $result = array("response" => array("httpCode" => 200, "status" => true, 'data' => $vendor_list));
                }
            } catch (JWTException $e) {
                $result = array("response" => array("httpCode" => 200, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
            } catch (TokenExpiredException $e) {
                $result = array("response" => array("httpCode" => 200, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function update_profile_image(Request $data)
    {
        $data_all = $data->all();
        // if ($data_all['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $rules = [
            'image' => ['mimes:jpeg,jpg,png', 'max:5120'],
        ];
        $errors = $result = array();
        $validator = app('validator')->make($data->all(), $rules);
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => $errors, "Error" => trans("messages.Error List")));
        } else {
            $check_auth = JWTAuth::toUser($data_all['token']);
            $id = $data_all['user_id'];
            // store datas in to database
            $users = Users::find($id);
            if (isset($data_all['image']) && $data_all['image'] != '') {
                $destinationPath = base_path() . '/public/assets/admin/base/images/admin/profile/'; // upload path
                $imageName = $users->id . '.' . $data_all['image']->getClientOriginalExtension();
                $data->file('image')->move($destinationPath, $imageName);
                $destinationPath1 = url('/assets/admin/base/images/admin/profile/' . $imageName);
                Image::make($destinationPath1)->fit(75, 75)->save(base_path() . '/public/assets/admin/base/images/admin/profile/thumb/' . $imageName);
                Image::make($destinationPath1)->fit(260, 170)->save(base_path() . '/public/assets/admin/base/images/admin/profile/' . $imageName);

                $users->image = $imageName;
                $users->save();
            }
            $imageName = url('/assets/admin/base/images/default_avatar_male.jpg');
            if (file_exists(base_path() . '/public/assets/admin/base/images/admin/profile/' . $users->image) && $users->image != '') {
                $imageName = URL::to("assets/admin/base/images/admin/profile/" . $users->image);
            }
            $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.Profile Image uploaded successfully"), "image" => $imageName));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function orders(Request $data)
    {
        $post_data = $data->all();
        $language_id = $post_data["language"];
        $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.No stores found"), "order_list" => array()));
        $query = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $query1 = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.language_id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $query2 = '"order_status"."lang_id" = (case when (select count(*) as totalcount from order_status where order_status.lang_id = ' . $language_id . ' and orders.order_status = order_status.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $order_list = DB::table('orders')
            ->select('vendors_infos.vendor_name', 'orders.total_amount', 'order_status.name as status_name', 'order_status.id as status_id', 'orders.id', 'orders.created_date', 'order_status.color_code', 'orders.order_key_formated', 'vendors.logo_image', 'orders.delivery_date', 'delivery_time_interval.start_time', 'delivery_time_interval.end_time', 'delivery_charge', 'orders.invoice_id', 'orders.order_type')
            ->leftJoin('outlets', 'outlets.id', '=', 'orders.outlet_id')
            ->leftJoin('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
            ->leftJoin('vendors', 'vendors.id', '=', 'orders.vendor_id')
            ->leftJoin('vendors_infos', 'vendors_infos.id', '=', 'orders.vendor_id')
            ->leftJoin('order_status', 'order_status.id', '=', 'orders.order_status')
            ->leftJoin('delivery_time_slots', 'delivery_time_slots.id', '=', 'orders.delivery_slot')
            ->leftJoin('delivery_time_interval', 'delivery_time_interval.id', '=', 'delivery_time_slots.time_interval_id')
            ->where('orders.customer_id', '=', $post_data['user_id'])
            ->whereRaw($query)
            ->whereRaw($query1)
            ->whereRaw($query2)
            ->orderBy('orders.id', 'desc')
            ->get();
        $orders = array();
        if (count($order_list) > 0) {
            $o = 0;
            foreach ($order_list as $ord) {
                $orders[$o]['id'] = $ord->id;
                $orders[$o]['encrypt_id'] = Crypt::encrypt($ord->id);
                $orders[$o]['vendor_name'] = $ord->vendor_name;
                $orders[$o]['total_amount'] = $ord->total_amount;
                $orders[$o]['status_name'] = $ord->status_name;
                $orders[$o]['status_id'] = $ord->status_id;
                $orders[$o]['created_date'] = $ord->created_date;
                $orders[$o]['color_code'] = $ord->color_code;
                $orders[$o]['delivery_date'] = $ord->delivery_date;
                $orders[$o]['order_type'] = $ord->order_type;
                $orders[$o]['start_time'] = ($ord->start_time != '') ? $ord->start_time : '';
                $orders[$o]['end_time'] = ($ord->end_time != '') ? $ord->end_time : '';
                $orders[$o]['delivery_charge'] = $ord->delivery_charge;
                $orders[$o]['order_key_formated'] = $ord->order_key_formated;
                $orders[$o]['invoice_id'] = $ord->invoice_id;
                $logo_image = URL::asset('assets/admin/base/images/no_image.png');

                if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $ord->logo_image) && $ord->logo_image != '') {
                    $logo_image = url('/assets/admin/base/images/vendors/logos/' . $ord->logo_image);
                }
                $orders[$o]['logo_image'] = $logo_image;
                $o++;
            }
        }
        $result = array("response" => array("httpCode" => 200, "status" => true, 'order_list' => $orders));
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function user_membership(Request $data)
    {
        $rules = [
            'cooperative' => ['required'],
            'membership_id' => ['required', 'max:15'],
        ];
        $post_data = $data->all();
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            $users = Users::find($post_data['user_id']);
            $users->cooperative = $post_data['cooperative'];
            $users->member_id = $post_data['membership_id'];
            $users->save();
            $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.Membership has been updated successfully")));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function user_rating(Request $data)
    {
        $rules = [
            'starrating' => ['required'],
            //'title'=> ['required'],
            'comments' => ['required'],
            'user_id' => ['required'],
            'vendor_id' => ['required'],
            'outlet_id' => ['required'],
            'order_id' => ['required'],
        ];
        $post_data = $data->all();
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            $reviews = new Outlet_reviews;
            $reviews->customer_id = $post_data['user_id'];
            $reviews->vendor_id = $post_data['vendor_id'];
            $reviews->outlet_id = $post_data['outlet_id'];
            $reviews->comments = $post_data['comments'];
            //~ $reviews->title        = $post_data['title'];
            $reviews->ratings = $post_data['starrating'];
            $reviews->created_date = date("Y-m-d H:i:s");
            $reviews->order_id = $post_data['order_id'];
            $reviews->save();
            $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.Your rating has been posted successfully")));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function user_subscribe(Request $data)
    {
        $rules = ['subscribe_email' => ['required', 'email', 'unique:newsletter_subscribers,email']];
        $post_data = $data->all();

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            $newsletter_subscribers = new Newsletter_subscribers;
            $newsletter_subscribers->email = $post_data['subscribe_email'];
            $newsletter_subscribers->created_date = date("Y-m-d H:i:s");
            $newsletter_subscribers->modified_date = date("Y-m-d H:i:s");
            $template = DB::table('email_templates')
                ->select('from_email', 'from', 'subject', 'template_id', 'content')
                ->where('template_id', '=', self::SUBSCRIBE_EMAIL_TEMPLATE)
                ->get();
            if (count($template)) {
                $from = $template[0]->from_email;
                $from_name = $template[0]->from;
                $subject = $template[0]->subject;
                if (!$template[0]->template_id) {
                    $template = 'mail_template';
                    $from = getAppConfigEmail()->contact_email;
                    $subject = getAppConfig()->site_name . " Subscribe";
                    $from_name = "";
                }
                $unsubscribe_url = url('user-unsubscribe/' . encrypt($post_data['subscribe_email']));
                $content = array('unsubscribe_link' => '<a href="' . $unsubscribe_url . '" style="text-decoration: none; color: #e91e63;" target="_blank">unsubscribe here</a>');
                $email = smtp($from, $from_name, $post_data['subscribe_email'], $subject, $content, $template);
            }
            $newsletter_subscribers->save();
            $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.You have subscribed successfully")));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /*driver rating*/
    public function driver_rating(Request $data)
    {
        $rules = [
            'starrating' => ['required'],
            //'title'=> ['required'],
            'comments' => ['required'],
            'user_id' => ['required'],
            'vendor_id' => ['required'],
            'outlet_id' => ['required'],
            'order_id' => ['required'],
            'driver_id' => ['required'],
        ];
        $post_data = $data->all();
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {		//print_r($post_data);exit;

            $reviews = new driver_reviews;
            //print_r($reviews);exit;
            $reviews->customer_id = $post_data['user_id'];
            $reviews->vendor_id = $post_data['vendor_id'];
            $reviews->outlet_id = $post_data['outlet_id'];
            $reviews->comments = $post_data['comments'];
            //~ $reviews->title        = $post_data['title'];
            $reviews->ratings = $post_data['starrating'];
            $reviews->created_date = date("Y-m-d H:i:s");
            $reviews->order_id = $post_data['order_id'];
            $reviews->driver_id = $post_data['driver_id'];
            $reviews->save();
            $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.Your rating for driver has been posted successfully")));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }


    public function rating(Request $data)
    {
        $rules = [
            'outlet_starrating' => ['required'],
            'driver_starrating' => ['required'],
            //'title'=> ['required'],
           // 'comments' => ['required'],
            'user_id' => ['required'],
            'vendor_id' => ['required'],
            'outlet_id' => ['required'],
            'order_id' => ['required'],
            //'driver_id' => ['required'],
        ];
        $post_data = $data->all();
        //print_r($post_data);exit;

        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);


        // $join=DB::select("select orders.id from orders LEFT JOIN outlet_reviews ON orders.customer_id = outlet_reviews.customer_id INTERSECT select orders.id from orders RIGHT JOIN outlet_reviews ON orders.customer_id = outlet_reviews.customer_id where  orders.id != outlet_reviews.order_id " );
        //print_r($join);exit();



        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array( "status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {		//print_r($post_data);exit;

            // $reviews = new driver_reviews;
            // //print_r($reviews);exit;
            // $reviews->customer_id = $post_data['user_id'];
            // $reviews->vendor_id = $post_data['vendor_id'];
            // $reviews->outlet_id = $post_data['outlet_id'];
            // $reviews->comments = $post_data['comments'];
            // //~ $reviews->title        = $post_data['title'];
            // $reviews->ratings = $post_data['driver_starrating'];
            // $reviews->created_date = date("Y-m-d H:i:s");
            // $reviews->order_id = $post_data['order_id'];
            // $reviews->driver_id = $post_data['driver_id'];
            // $reviews->save();


            // $reviews = new Outlet_reviews;
            // $reviews->customer_id = $post_data['user_id'];
            // $reviews->vendor_id = $post_data['vendor_id'];
            // $reviews->outlet_id = $post_data['outlet_id'];
            // $reviews->comments = $post_data['comments'];
            // //~ $reviews->title        = $post_data['title'];
            // $reviews->ratings = $post_data['outlet_starrating'];
            // $reviews->created_date = date("Y-m-d H:i:s");
            // $reviews->order_id = $post_data['order_id'];
            // $reviews->save();


            // $outletsRating=DB::select("select ((sum(ratings)/count(outlet_id)*5)) as total from outlet_reviews where outlet_id=$outlet_id");



            //Ram(20-08-19):

        
            $driver_starrating = $post_data['driver_starrating'];

            $driver_id = $post_data['driver_id'];

            $customer_id=$post_data['user_id'];
            $vendor_id = $post_data['vendor_id'];
            $outlet_id = $post_data['outlet_id'];
            $comments = $post_data['comments'];
            $created_date = date("Y-m-d H:i:s");
            $order_id=$post_data['order_id'];
            $outlet_starrating=$post_data['outlet_starrating'];
            //print_r($order_id);exit();
        
            $reviews=DB::table('outlet_reviews')

                    ->where('order_id', $order_id)

                    ->update(['ratings' => $outlet_starrating,
                            'customer_id' =>$customer_id,
                            'vendor_id'=>$vendor_id,
                            'outlet_id'=>$outlet_id,
                            'comments'=>$comments,
                            'created_date'=>$created_date,
                            'order_id'=>$order_id]);


            /*outletsRating*/


    
            $outletsRating=DB::select("select round(Avg(ratings),1) as total from outlet_reviews where ratings != '-1'  and outlet_id=$outlet_id");

            //print_r($outletsRating);exit();

            $array = array();
            if (count($outletsRating) > 0) {
                $o = 0;
                foreach ($outletsRating as $ord) {
                    $array[$o]['total'] = $ord->total;

                    $o++;

                    $total= $ord->total;
                }
            }


            $reviews=DB::table('outlets')

                                ->where('id', $outlet_id)

                                ->update(['average_rating' => $total]);

            /*driversRating*/


               if(!empty($driver_id)){

            $reviews=DB::table('driver_reviews')

                    ->where('driver_id', $driver_id)

                    ->update(['ratings' => $driver_starrating,
                            'customer_id' =>$customer_id,
                            'vendor_id'=>$vendor_id,
                            'outlet_id'=>$outlet_id,
                            'comments'=>$comments,
                            'created_date'=>$created_date,
                            'order_id'=>$order_id,
                            'driver_id'=>$driver_id
                                ]);

                

            $driversRating=DB::select("select round(Avg(ratings),1) as totals from driver_reviews where driver_id=$driver_id");
            //print_r($driversRating);exit();

            $driversArray = array();
            if (count($driversRating) > 0) {
                $l = 0;
                foreach ($driversRating as $driver) {
                    $driversArray[$l]['totals'] = $driver->totals;

                    $l++;

                    $totals= $driver->totals;
                }
            }


            $reviews=DB::table('drivers')

                                ->where('id', $driver_id)

                                ->update(['average_rating' => $totals]);
                }

            $result = array("status" => 1, "message" => trans("messages.Your rating has been posted successfully"));
        }


        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }



    /*driver rating*/

    /* User unsubscribe */
    public function user_unsubscribe(Request $data)
    {
        $rules = ['email' => ['required', 'email']];
        $post_data = $data->all();
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            $unsubscribe = DB::table('newsletter_subscribers')
                ->select('id')
                ->where('email', '=', $data['email'])
                ->first();
            if (count($unsubscribe) > 0) {
                $newsletter_unsubs = DB::table('newsletter_subscribers')
                    ->where('email', $data['email'])
                    ->delete();
                $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.You have been unsubscribed successfully")));
            } else {
                $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Invalid URL")));
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    /* Notification list */
    public function notification_list(Request $data)
    {
        $post_data = $data->all();
        // if ($post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $rules = [
            'user_id' => ['required'],
            'token' => ['required'],
        ];
        $errors = $result = $notification_list = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => $errors));
        } else {
            try {
                $check_auth = JWTAuth::toUser($post_data['token']);
                $result = array("response" => array("httpCode" => 200, "status" => false, 'data' => array(), "Message" => trans("messages.No notifications found")));
                $notifications = DB::table('notifications')
                    ->select('notifications.id', 'notifications.order_id', 'notifications.message', 'notifications.read_status', 'notifications.created_date', 'vendors.logo_image')
                    ->join('vendors', 'vendors.id', '=', 'notifications.vendor_id')
                    ->where('customer_id', $post_data['user_id'])
                    ->orderBy('notifications.id', 'desc')
                    ->get();
                if (count($notifications) > 0) {
                    $n = 0;
                    foreach ($notifications as $no) {
                        $notification_list[$n]['notification_id'] = $no->id;
                        $notification_list[$n]['order_id'] = $no->order_id;
                        $notification_list[$n]['message'] = $no->message;
                        $notification_list[$n]['read_status'] = $no->read_status;
                        $notification_list[$n]['created_date'] = timeAgo($no->created_date);
                        $logo_image = URL::asset('assets/admin/base/images/no_image.png');
                        if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $no->logo_image) && $no->logo_image != '') {
                            $logo_image = url('/assets/admin/base/images/vendors/logos/' . $no->logo_image);
                        }
                        $notification_list[$n]['logo_image'] = $logo_image;
                        $n++;
                    }
                }
                if (count($notifications)) {
                    $result = array("response" => array("httpCode" => 200, "status" => true, 'data' => $notification_list, 'Message' => trans('messages.Notification list')));
                }
            } catch (JWTException $e) {
                $result = array("response" => array("httpCode" => 200, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
            } catch (TokenExpiredException $e) {
                $result = array("response" => array("httpCode" => 200, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    /* Delete Notification list */
    public function delete_notification(Request $data)
    {
        $post_data = $data->all();
        // if ($post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $rules = [
            'notification_id' => ['required'],
            'user_id' => ['required'],
            'token' => ['required'],
        ];
        $errors = $result = $notification_list = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => $errors));
        } else {
            try {
                $check_auth = JWTAuth::toUser($post_data['token']);
                $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Invalid notification id")));
                $notifications = DB::table('notifications')->where('id', '=', $post_data['notification_id'])->delete();
                if ($notifications) {
                    $result = array("response" => array("httpCode" => 200, "status" => true, 'Message' => trans('messages.Notifications has been deleted')));
                }
            } catch (JWTException $e) {
                $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
            } catch (TokenExpiredException $e) {
                $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    public function cms()
    {
        $query = '"cms_infos"."language_id" = (case when (select count(*) as totalcount from cms_infos where cms_infos.language_id = ' . getCurrentLang() . ' and cms.id = cms_infos.cms_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
        $cms_info = DB::table('cms')
            ->select('cms.*', 'cms_infos.*')
            ->leftJoin('cms_infos', 'cms_infos.cms_id', '=', 'cms.id')
            ->whereRaw($query)
        //->where("url_index","=",$index)
        //->limit(1)
            ->get();

        if (count($cms_info) > 0) {
            $result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Page details"), "cms_info" => $cms_info));
        } else {
            $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Invalid Page"), "cms_info" => $cms_info));
        }
        return json_encode($result);
    }
    public function location_outlet(Request $data)
    {
        $rules = [
            'language' => ['required'],
            'latitude' => ['required'],
            'longitude' => ['required'],
        ];

        $post_data = $data->all();
        $language_id = $post_data["language"];
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        $distance = 5 * 1000;
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 200, "status" => false, "Error" => trans("messages.No Areas found."), "Message" => $errors));
        } else {
            $query = 'vendors_infos.lang_id = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
            $query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
            $query2 = 'cities_infos.language_id = (case when (select count(cities_infos.language_id) as totalcount from cities_infos where cities_infos.language_id = ' . $language_id . ' and cities.id = cities_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
            $query3 = 'zones_infos.language_id = (case when (select count(zones_infos.language_id) as totalcount from zones_infos where zones_infos.language_id = ' . $language_id . ' and zones.id = zones_infos.zone_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
            $address = DB::select("select outlet_infos.contact_address,outlets.vendor_id,outlets.id as outlet_id,outlet_infos.outlet_name,outlets.delivery_time as outlets_delivery_time,outlets.average_rating as outlets_average_rating,vendors_infos.vendor_name, vendors.logo_image,vendors.category_ids,outlets.delivery_charges_fixed,outlets.city_id,outlets.location_id, outlets.delivery_charges_variation,outlets.minimum_order_amount,outlets.active_status,zones_infos.zone_name,cities_infos.city_name,earth_distance(ll_to_earth(" . $post_data['latitude'] . ", " . $post_data['longitude'] . "), ll_to_earth(outlets.latitude, outlets.longitude)) as distance  from outlets left join vendors on outlets.vendor_id =vendors.id left join vendors_infos on vendors.id = vendors_infos.id left Join cities  on cities.id = outlets.city_id left join cities_infos on cities_infos.id =outlets.city_id left join zones on zones.id =outlets.location_id left join zones_infos on zones_infos.zone_id =zones.id left join outlet_infos on outlet_infos.id = outlets.id where earth_box(ll_to_earth(" . $post_data['latitude'] . ", " . $post_data['longitude'] . "), " . $distance . ") @> ll_to_earth(outlets.latitude, outlets.longitude)and " . $query . " and " . $query1 . " and " . $query2 . " and " . $query3 . " and outlets.active_status='1' and vendors.active_status=1 and vendors.featured_vendor=1 ");
            if (count($address) > 0) {
                foreach ($address as $add => $items) {
                    $logo_image = URL::asset('assets/admin/base/images/no_image.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $items->logo_image) && $items->logo_image != '') {
                        $logo_image = url('/assets/admin/base/images/vendors/logos/' . $items->logo_image);
                    }
                    $address[$add]->logo_image = $logo_image;
                    $address[$add]->outlets_average_rating = ($items->outlets_average_rating != '') ? $items->outlets_average_rating : '';

                    //$cart_data[$key]->product_image = $product_image;
                    $city_id = $items->city_id;
                    $location_id = $items->location_id;
                    $city_name = $items->city_name;
                    $location_name = $items->zone_name;

                    $cids = explode(',', $items->category_ids);
                    //Get the categories data
                    $query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = ' . getCurrentLang() . ' and categories.id = categories_infos.category_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
                    $data = DB::table('categories')
                        ->select('categories.id', 'categories_infos.category_name')
                        ->leftJoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
                        ->whereRaw($query)
                        ->where('category_status', 1)
                        ->where('category_type', 2)
                        ->whereIn('categories.id', $cids)
                        ->orderBy('category_name', 'asc')
                        ->get();
                    $category_name = '';
                    if (count($data) > 0) {
                        foreach ($data as $items) {
                            $category_name .= ucfirst($items->category_name) . ', ';
                        }
                        $category_name = rtrim($category_name, ', ');
                    }
                    $address[$add]->category_name = $category_name;
                }
                $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Address Details"), "location_outlet" => $address, "city_id" => $city_id, "location_id" => $location_id, "city_name" => $city_name, "location_name" => $location_name));
            } else {
                $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.No service available in your location. Please select available location."), "location_outlet" => $address));
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function product_rating(Request $data)
    {
        $rules = [
            'starrating' => ['required'],
            //'title'=> ['required'],
            'comments' => ['required'],
            'user_id' => ['required'],
            'vendor_id' => ['required'],
            'outlet_id' => ['required'],
            'product_id' => ['required'],
        ];
        $post_data = $data->all();
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            $product_reviews = new Product_reviews;
            $product_reviews->customer_id = $post_data['user_id'];
            $product_reviews->vendor_id = $post_data['vendor_id'];
            $product_reviews->outlet_id = $post_data['outlet_id'];
            $product_reviews->comments = $post_data['comments'];
            //~ $reviews->title        = $post_data['title'];
            $product_reviews->ratings = $post_data['starrating'];
            $product_reviews->created_date = date("Y-m-d H:i:s");
            $product_reviews->product_id = $post_data['product_id'];
            $product_reviews->save();
            $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.Your rating has been posted successfully")));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /* Driver Confirmation */
    public function driver_confirmation(Request $data)
    {
        $post_data = $data->all();
        // if ($post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $verifictaion_key = $post_data['key'];
        $password = $post_data['password'];
        $email = strtolower($post_data['email']);
        $errors = array();
        if ($verifictaion_key && $email) {
            $drivers = DB::table('drivers')->select('id')->where('verification_key', '=', $verifictaion_key)->where('email', '=', $email)->first();
            try {
                if (count($drivers)) {
                    //$driver->save();
                    $verified = $this->_isDriverVerification($verifictaion_key, $email);
                    if ($verified) {
                        $driver_id = $this->_isDriver($email);
                        $driver = Drivers::find($driver_id);
                        $driver->is_verified = 1;
                        $driver->save();
                        $customer = $driver->getAttributes();
                        $template = DB::table('email_templates')->select('from_email', 'from', 'subject', 'template_id', 'content')->where('template_id', '=', self::USERS_WELCOME_EMAIL_TEMPLATE)->get();
                        if (count($template)) {
                            $from = $template[0]->from_email;
                            $from_name = $template[0]->from;
                            $subject = $template[0]->subject;
                            if (!$template[0]->template_id) {
                                $template = 'mail_template';
                                $from = getAppConfigEmail()->contact_mail;
                                $subject = "Welcome to " . getAppConfig()->site_name;
                                $from_name = "";
                            }
                            $customer['name'] = $password;
                            $customer['password'] = ucfirst($driver->first_name);
                            $content = array("customer" => $customer, 'u_password' => $password);
                            $email = smtp($from, $from_name, $driver['email'], $subject, $content, $template);
                            $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.The email confirmation has been done kindly check your email for further process.")));
                        }
                    } else {
                        $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Email alredy verified.")));
                    }
                } else {
                    $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Invalid key or email.")));
                }
            } catch (Exception $e) {
                $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Problem in server. try again later.")));
                Log::Instance()->add(Log::ERROR, $e);
            }
        } else {
            $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Invalid key or email.")));
        }
        return $result;
    }
    /** Check is verified or not **/
    public function _isDriverVerification($key, $email)
    {
        $db = DB::table('drivers')->select('id', DB::raw('count(id) AS total_count'))->where('email', '=', $email)->where('verification_key', '=', $key)->where('is_verified', '=', 0)->groupby('id')->first();
        if (count($db)) {
            return (($db->total_count > 0) ? true : false);
        }
        return false;
    }
    /** Check is verified or not **/
    public function _isDriver($email)
    {
        $db = DB::table('drivers')->select('id')->where('email', '=', $email)->first();
        if (count($db)) {
            return $db->id;
        }
        return 0;
    }
    public static function check_social_login_id(Request $data)
    {
        $post_data = $data->all();
        $rules = [
            'language' => ['required'],
            'user_type' => ['required'],
            'facebook_id' => ['required_if:user_type,4'],
        ];
        $post_data = $data->all();
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.Invalid user type")));
            if ($post_data['user_type'] == 4) {
                $result = array("response" => array("httpCode" => 200, "status" => true, "code" => 0, "Message" => trans("messages.Facebook Id does not exist"), "email" => '', "mobile" => ''));
                $check_social_id = Users::check_social_login_id($post_data['user_type'], $post_data['facebook_id']);
                if (count($check_social_id) > 0) {
                    $email = $check_social_id->email;
                    $mobile = $check_social_id->mobile;
                    if (!empty($email) && !empty($mobile)) {
                        $result = array("response" => array("httpCode" => 200, "status" => true, "code" => 1, "Message" => trans("messages.Facebook Id already exist"), "email" => $email, "mobile" => $mobile));
                    } else {
                        $result = array("response" => array("httpCode" => 200, "status" => true, "code" => 0, "Message" => trans("messages.Facebook Id already exist"), "email" => !empty($email) ? $email : '', "mobile" => !empty($mobile) ? $mobile : ''));
                    }
                }
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    public static function check_social_user_credientials(Request $data)
    {
        $post_data = $data->all();
        $rules = [
            'language' => ['required'],
            'user_type' => ['required'],
            'email' => ['required'],
            'phone' => ['required'],
        ];
        $post_data = $data->all();
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            $result = array("response" => array("httpCode" => 200, "status" => true, "Code" => 1, "Message" => trans("messages.Email and Mobile number are available")));
            $check_email = Users::Check_user_credientials(trim(strtolower($post_data['email'])), '');
            $check_phone = Users::Check_user_credientials('', $post_data['phone']);
            if (count($check_email) > 0 && count($check_phone) > 0) {
                $result = array("response" => array("httpCode" => 400, "status" => false, "Code" => 2, "Message" => trans("messages.Email and Mobile number already exist")));
            } elseif (count($check_email) > 0) {
                $result = array("response" => array("httpCode" => 400, "status" => false, "Code" => 3, "Message" => trans("messages.Email already exist")));
            } elseif (count($check_phone) > 0) {
                $result = array("response" => array("httpCode" => 400, "status" => false, "Code" => 4, "Message" => trans("messages.Mobile number already exist")));
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    public function store_contact(Request $data)
    {
        $post_data = $data->all();
        //print_r($post_data);exit;
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('es');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $rules = [
            'name' => ['required', 'regex:/^[\p{L} ]+$/u', 'max:56'],
            'email' => ['required', 'email', 'max:250'],
            'mobile_number' => ['required', 'regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/'],
            'message' => ['required'],
            'city' => ['required'],
            'enquery_type' => ['required'],
            //'terms_condition' => ['required'],
            //'captcha' => 'required|valid_captcha',
        ];

        // process the validation
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }

            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            $errors = '';
            $name = $post_data['name'];
            if (!preg_match("/^[\p{L} ]+$/u", $name)) {
                $errors .= $validator->errors();
            }
            if ($errors != '') {
                $result = array("response" => array("httpCode" => 400, "status" => true, "Message" => trans("messages.Invalid name,Please use letters only")));
            } else {
                $contactus = new Contactus;
                $contactus->name = $_POST['name'];
                $contactus->email = $_POST['email'];
                $contactus->phone_number = $_POST['mobile_number'];
                $contactus->message = $_POST['message'];
                $contactus->enquery_type = $_POST['enquery_type'];
                $contactus->city_id = $_POST['city'];
                $contactus->created_at = date("Y-m-d H:i:s");
                $contactus->save();
                $enquery_type = '-';
                if ($_POST['enquery_type'] == 1) {
                    $enquery_type = 'General';
                } elseif ($_POST['enquery_type'] == 2) {
                    $enquery_type = 'Product';
                } elseif ($_POST['enquery_type'] == 3) {
                    $enquery_type = 'Delivery';
                } elseif ($_POST['enquery_type'] == 4) {
                    $enquery_type = 'Payment';
                } elseif ($_POST['enquery_type'] == 5) {
                    $enquery_type = 'Outlet';
                }

                $city_details = get_city_details($_POST['city']);
                $city_name = isset($city_details->city_name) ? $city_details->city_name : '-';

                $to = getAppConfigEmail()->support_mail;
                $email = $_POST['email'];
                $subject = "Contact request from " . getAppConfig()->site_name . " by " . $_POST['name'];
                $content = $_POST['message'];
                $template = DB::table('email_templates')
                    ->select('*')
                    ->where('template_id', '=', self::ADMIN_MAIL_TEMPLATE_CONTACT)
                    ->get();
                if (count($template)) {
                    $from = $template[0]->from_email;
                    $from_name = $template[0]->from;
                    //$subject = $template[0]->subject;
                    if (!$template[0]->template_id) {
                        $template = 'mail_template';
                        $from = getAppConfigEmail()->contact_mail;
                        $subject = "Welcome to " . getAppConfig()->site_name;
                        $from_name = "";
                    }
                    $content = array('name' => $_POST['name'], 'email' => $_POST['email'], 'phone_number' => $_POST['mobile_number'], 'enquery_type' => $enquery_type, 'city' => $city_name, 'message' => $_POST['message']);
                    $email = smtp($from, $from_name, $to, $subject, $content, $template);
                }
                $to = $_POST['email'];
                $email = $_POST['email'];
                $subject = "Contact request reply from " . getAppConfig()->site_name;
                $content = $_POST['message'];
                $template = DB::table('email_templates')
                    ->select('*')
                    ->where('template_id', '=', self::USER_MAIL_TEMPLATE_CONTACT)
                    ->get();
                if (count($template)) {
                    $from = $template[0]->from_email;
                    $from_name = $template[0]->from;
                    //$subject = $template[0]->subject;
                    if (!$template[0]->template_id) {
                        $template = 'mail_template';
                        $from = getAppConfigEmail()->contact_mail;
                        $subject = "Welcome to " . getAppConfig()->site_name;
                        $from_name = "";
                    }
                    $content = array("notification" => array('name' => $_POST['name'], 'email' => $_POST['email'], 'message' => $_POST['message'], 'mobile' => $_POST['mobile_number']));
                    $email = smtp($from, $from_name, $to, $subject, $content, $template);
                }

                $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.Your request has been posted successfully")));
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function check_otp_registration(Request $data)
    {
        $post_data = $data->all();

        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        //print_r($post_data); exit;
        $user_details = DB::table('users')
            ->select('id')
            ->where('id', '=', $post_data['user_id'])
            ->where('phone_otp', '=', $post_data['otp'])
            ->first();
        //  print_r($user_details); exit;
        $result = array("response" => array("httpCode" => 400, "Message" => trans("messages.OTP seems wrong"), "order_items" => array()));
        if (count($user_details) > 0) {
            $user_data = Users::find($user_details->id);
            $user_data->is_verified = 1;
            $user_data->save();
            $token = JWTAuth::fromUser($user_data, array('exp' => 200000000000));
            $result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.User Logged-in Successfully"), "user_id" => $user_data->id, "token" => $token, "email" => $user_data->email, "name" => $user_data->name, "social_title" => !empty($user_data->social_title) ? $user_data->social_title : '', "first_name" => isset($user_data->first_name) ? $user_data->first_name : "", "last_name" => isset($user_data->last_name) ? $user_data->last_name : "", "image" => isset($user_data->image) ? $user_data->image : "", "mobile" => isset($user_data->mobile) ? $user_data->mobile : "", "facebook_id" => isset($user_data->facebook_id) ? $user_data->facebook_id : "", "phone_verify" => 1, "package_type" => isset($user_data->package_type) ? $user_data->package_type : "", "user_type" => isset($post_data['user_type']) ? (int) $post_data['user_type'] : "0", "is_verified" => isset($user_data->is_verified) ? $user_data->is_verified : ""));
        }
        return json_encode($result);
    }

    public function reg_send_otp(Request $data)
    {
        $rules = array(
            'language' => 'required',
            'user_id' => 'required',
        );
        $post_data = $data->all();
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            $post_data = $data->all();
            $user_id = $post_data['user_id'];
            $users = Users::find($user_id);
            $otp = rand(100000, 999999);
            $app_config = getAppConfig();
            $number = $users->mobile;
            $message = 'You have received OTP password for ' . getAppConfig()->site_name . '. Your OTP code is ' . $otp . '. This code can be used only once and dont share this code with anyone.';
            $twilo_sid = TWILIO_ACCOUNTSID;
            $twilio_token = TWILIO_AUTHTOKEN;
            $from_number = TWILIO_NUMBER;
            /*$twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
            $twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
            $from_number = "+14783471785";*/
            //$client = new Services_Twilio($twilo_sid, $twilio_token);
            $client = new Client($twilo_sid, $twilio_token);

            //print_r ($client);exit;
            // Create an authenticated client for the Twilio API
            try {
               /* $m = $client->account->messages->sendMessage(
                    $from_number, // the text will be sent from your Twilio number
                    $number, // the phone number the text will be sent to
                    $message // the body of the text message
                );*/
                $m =  $client->messages->create($number, array('from' => $from_number, 'body' => $message));


                $users->phone_otp = $otp;
                $users->updated_date = date("Y-m-d H:i:s");
                $users->save();
                $token = JWTAuth::fromUser($users, array('exp' => 200000000000));
                $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.we have sent verification code to your mobile."), "mobile_number" => $number, "otp" => $otp));
            } catch (Exception $e) {
                $result = array("response" => array("httpCode" => 400, "Message" => $e->getMessage()));
                return json_encode($result);
            } catch (\Services_Twilio_RestException $e) {
                $result = array("response" => array("httpCode" => 400, "Message" => $e->getMessage()));
                return json_encode($result);
            }
            $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.we have sent verification code to your mobile."), "mobile_number" => $number, "otp" => $otp));
        }
        return json_encode($result);
    }

    public function signup_new(Request $data)
    {
        $post_data = $data->all();
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
         if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $rules = [
            'first_name' => ['required', 'alpha', 'max:56'],
            // 'last_name' => ['required','alpha', 'max:56'],
            'email' => ['required', 'email', 'max:250', 'unique:users,email'],
            'password' => ['required', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{6,}$/'],
            //'phone'      => ['required', 'max:50','regex:^(\+\d{1,2}\s)?\(?\d{3}\)?[\s.-]?\d{3}[\s.-]?\d{4}$'],
            'phone' => ['required', 'max:15', 'regex:/(^[0-9 +]+$)+/', 'unique:users,mobile'],
            'gender' => ['required'],
            'terms_condition' => ['required'],
            'login_type' => ['required'],
            'device_id' => ['required_unless:login_type,1,2,3'],
            'device_token' => ['required_unless:login_type,1,2,3'],
            'image' => ['mimes:png,jpeg,bmp', 'max:2024'],
        ];

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 400, "status" => false, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            $errors = '';
            $password = $post_data['password'];
            if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/', $password)) {
                $errors .= $validator->errors()->add('password', 'Password must contain at least 6 characters, one Uppercase letter and one number');
            }
            if ($errors != '') {
                $result = array("response" => array("httpCode" => 400, "status" => true, "Message" => trans("messages.The password Minimum 6 characters at least 1 Uppercase Alphabet, 1 Lowercase Alphabet and 1 Number.")));
            } else {
                $users = new Users;
                $usertoken = sha1(uniqid(Text::random('alnum', 32), true));
                if (!$users->user_token) {
                    $users->user_token = $usertoken;
                }
                $password = $post_data['password'];
                $users->first_name = $post_data['first_name'];
                $users->last_name = isset($post_data['last_name']) ? $post_data['last_name'] : "";
                $users->name = $post_data['first_name'] . " " . $post_data['last_name'];
                $users->email = strtolower($post_data['email']);
                $users->password = md5($post_data['password']);
                $users->mobile = preg_replace("/[^+0-9]+/", "", $post_data['phone']);
                $users->gender = $post_data['gender'];
                $users->social_title = ($post_data['gender'] == 'F') ? "Ms." : "Mr.";
                //$users->civil_id    = $post_data['civil_id'];
                //$users->cooperative = $post_data['cooperative'];
                //$users->member_id   = isset($post_data['member_id'])?$post_data['member_id']:'';
                $users->user_type = 3;
                $users->is_verified = 0;
                $users->ip_address = $_SERVER['REMOTE_ADDR'];
                $users->created_date = date("Y-m-d H:i:s");
                $users->updated_date = date("Y-m-d H:i:s");
                $users->user_created_by = 3;
                $users->login_type = 1;
                //Check if the login type from mobile app update the device details here
                if (isset($post_data['login_type']) && !empty($post_data['login_type'])) {
                    //Store Android Device details
                    if ($post_data['login_type'] == 2) {
                        $users->login_type = 2;
                        $users->android_device_id = isset($post_data['device_id']) ? $post_data['device_id'] : '';
                        $users->android_device_token = isset($post_data['device_token']) ? $post_data['device_token'] : '';
                    }
                    //Store iOS Device details
                    if ($post_data['login_type'] == 3) {
                        $users->login_type = 3;
                        $users->ios_device_id = isset($post_data['device_id']) ? $post_data['device_id'] : '';
                        $users->ios_device_token = isset($post_data['device_token']) ? $post_data['device_token'] : '';
                    }
                }
                $verification_key = Text::random('alnum', 12);
                $users->verification_key = $verification_key;
                $users->save();
                if (isset($post_data['image']) && $post_data['image'] != '') {
                    $destinationPath = base_path() . '/public/assets/admin/base/images/admin/profile/'; // upload path
                    $imageName = $users->id . '.' . $post_data['image']->getClientOriginalExtension();
                    $data->file('image')->move($destinationPath, $imageName);
                    $destinationPath1 = url('/assets/admin/base/images/admin/profile/' . $imageName);
                    Image::make($destinationPath1)->fit(75, 75)->save(base_path() . '/public/assets/admin/base/images/admin/profile/thumb/' . $imageName);
                    Image::make($destinationPath1)->fit(260, 170)->save(base_path() . '/public/assets/admin/base/images/admin/profile/' . $imageName);

                    $users->image = $imageName;
                    $users->save();
                }
                $imageName = url('/assets/admin/base/images/default_avatar_male.jpg');
                if (file_exists(base_path() . '/public/assets/admin/base/images/admin/profile/' . $users->image) && $users->image != '') {
                    $imageName = URL::to("assets/admin/base/images/admin/profile/" . $users->image);
                }
                if ($post_data['login_type'] == 2 || $post_data['login_type'] == 3 || $post_data['login_type'] == 1) {
                    $otp = rand(100000, 999999);
                    $app_config = getAppConfig();
                    $number = $post_data['phone'];
                    $message = 'You have received OTP password for ' . getAppConfig()->site_name . '. Your OTP code is ' . $otp . '. This code can be used only once and dont share this code with anyone.';
                    $twilo_sid = TWILIO_ACCOUNTSID;
                        $twilio_token = TWILIO_AUTHTOKEN;
                        $from_number = TWILIO_NUMBER;
                        /*$twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
                        $twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
                        $from_number = "+14783471785";*/
                   // $client = new Services_Twilio($twilo_sid, $twilio_token);
                    $client = new Client($twilo_sid, $twilio_token);

                    //print_r ($client);exit;
                    // Create an authenticated client for the Twilio API
                    try {
                        /*$m = $client->account->messages->sendMessage(
                            $from_number, // the text will be sent from your Twilio number
                            $number, // the phone number the text will be sent to
                            $message // the body of the text message
                        );*/
                        $m =  $client->messages->create($number, array('from' => $from_number, 'body' => $message));


                        $users->phone_otp = $otp;
                        $users->updated_date = date("Y-m-d H:i:s");
                        //print_r($users);exit;
                        $users->save();
                        $token = JWTAuth::fromUser($users, array('exp' => 200000000000));
                        $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.OTP sent"), "mobile_number" => $post_data['phone'], "user_id" => $users->id, "token" => $token, "mobile" => $users->mobile, "user_type" => $users->user_type, "guest" => isset($users->guest_type) ? $users->guest_type : "0", "email" => isset($users->email) ? $users->email : "", "name" => isset($users->name) ? $users->name : "", "facebook_id" => $users->facebook_id, "phone_verify" => 1));
                    } catch (Exception $e) {
                        $result = array("response" => array("httpCode" => 400, "Message" => $e->getMessage()));
                        return json_encode($result);
                    } catch (\Services_Twilio_RestException $e) {
                        $result = array("response" => array("httpCode" => 400, "Message" => $e->getMessage()));
                        return json_encode($result);
                    }
                }

                $result = array("response" => array("httpCode" => 200, "Message" => trans("messages.OTP sent"), "mobile_number" => $post_data['phone'], "user_id" => $users->id, "token" => $token, "mobile" => $users->mobile, "user_type" => $users->user_type, "guest" => isset($users->guest_type) ? $users->guest_type : "0", "email" => isset($users->email) ? $users->email : "", "name" => isset($users->name) ? $users->name : "", "facebook_id" => $users->facebook_id, "phone_verify" => 1));
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /*
             * Phone Number Verification
    */
    public function verifyPhone(Request $data)
    {
        $post_data = $data->all();
        $rules = [
            'phone' => ['required'],
            'login_type' => ['required'],
            'device_id' => ['required_unless:login_type,1,2,3'],
            'device_token' => ['required_unless:login_type,1,2,3'],
        ];
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
         if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => "-1", "message" => $errors, "detail" => array());
        } else {
            $user_data = DB::select('SELECT users.id, users.name, users.email, users.status, users.is_verified, users.mobile FROM users where users.mobile = ? AND users.user_type = 3  limit 1', array($post_data['phone']));
            $users = new Users;
            if (count($user_data) == 0) {
                //reponse to redirect to otp page and send otp sms

                $usertoken = sha1(uniqid(Text::random('alnum', 32), true));
                if (!$users->user_token) {
                    $users->user_token = $usertoken;
                }

                $users->mobile = preg_replace("/[^+0-9]+/", "", $post_data['phone']);
                $users->user_type = 3;
                $users->is_verified = 0;
                $users->ip_address = $_SERVER['REMOTE_ADDR'];
                $users->created_date = date("Y-m-d H:i:s");
                $users->user_created_by = 3;
                $users->login_type = 1;
                //Check if the login type from mobile app update the device details here
                if (isset($post_data['login_type']) && !empty($post_data['login_type'])) {
                    //Store Android Device details
                    if ($post_data['login_type'] == 2) {
                        $users->login_type = 2;
                        $users->android_device_id = isset($post_data['device_id']) ? $post_data['device_id'] : '';
                        $users->android_device_token = isset($post_data['device_token']) ? $post_data['device_token'] : '';
                    }
                    //Store iOS Device details
                    if ($post_data['login_type'] == 3) {
                        $users->login_type = 3;
                        $users->ios_device_id = isset($post_data['device_id']) ? $post_data['device_id'] : '';
                        $users->ios_device_token = isset($post_data['device_token']) ? $post_data['device_token'] : '';
                    }
                }
                $verification_key = Text::random('alnum', 12);
                $users->verification_key = $verification_key;

                $otp = rand(100000, 999999);
                $users->phone_otp = $otp;
                $users->updated_date = date("Y-m-d H:i:s");
                //print_r($users);exit;
                $users->save();

                $app_config = getAppConfig();
                $number = $post_data['phone'];
                $message = 'You have received OTP password for ' . getAppConfig()->site_name . '. Your OTP code is ' . $otp . '. This code can be used only once and dont share this code with anyone.';
                $twilo_sid = TWILIO_ACCOUNTSID;
                        $twilio_token = TWILIO_AUTHTOKEN;
                        $from_number = TWILIO_NUMBER;
                        /*$twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
                        $twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
                        $from_number = "+14783471785";*/
                //$client = new Services_Twilio($twilo_sid, $twilio_token);
                    $client = new Client($twilo_sid, $twilio_token);

                //print_r ($client);exit;
                // Create an authenticated client for the Twilio API
                try {
                    /*$m = $client->account->messages->sendMessage(
                        $from_number, // the text will be sent from your Twilio number
                        $number, // the phone number the text will be sent to
                        $message // the body of the text message
                    );*/
                    $m =  $client->messages->create($number, array('from' => $from_number, 'body' => $message));


                    $token = JWTAuth::fromUser($users, array('exp' => 200000000000));
                    $result = array("status" => 2, "message" => trans("messages.OTP sent"), "detail" => array("userId" => $users->id, "phoneNumber" => $post_data['phone'], "token" => $token, "isNewUser" => 1, "userEmail" => isset($users->email) ? $users->email : "", "userName" => isset($users->name) ? $users->name : "", "userOtp" => $users->phone_otp));
                } catch (Exception $e) {
                    // $result = array("response" => array("httpCode" => 400,"Message" => $e->getMessage()));
                    $result = array("status" => -1, "message" => $e->getMessage());
                    return json_encode($result);
                } catch (\Services_Twilio_RestException $e) {
                    // $result = array("response" => array("httpCode" => 400,"Message" => $e->getMessage()));
                    $result = array("status" => -1, "message" => $e->getMessage());
                    return json_encode($result);
                }
            } else {
                $user_data = $user_data[0];
                $token = JWTAuth::fromUser($users, array('exp' => 200000000000));
                $result = array("status" => 1, "message" => trans("Please enter your Password"), "detail" => array("userId" => $users->id, "phoneNumber" => $post_data['phone'], "token" => $token, "isNewUser" => 0, "userEmail" => isset($users->email) ? $users->email : "", "userName" => isset($users->name) ? $users->name : "", "userOtp" => ""));
            }
        }

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function signupOtpVerify(Request $data)
    {
        $post_data = $data->all();

        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
         if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        //print_r($post_data); exit;
        $user_details = DB::table('users')
            ->select('id')
            ->where('mobile', '=', $post_data['phone'])
            ->where('phone_otp', '=', $post_data['otp'])
            ->first();
        //  print_r($user_details); exit;
        $result = array("status" => 0, "message" => trans("messages.Verification Failed kindly check your OTP"));
        if (count($user_details) > 0) {
            $user_data = Users::find($user_details->id);
            $user_data->is_verified = 1;
            $user_data->save();
            $token = JWTAuth::fromUser($user_data, array('exp' => 200000000000));

            $result = array("status" => 1, "message" => trans("messages.OTP Verified Successfully.Please fill your pofile details"), "detail" => array("userId" => $user_data->id, "phoneNumber" => $post_data['phone'], "token" => $token, "isNewUser" => 0, "userEmail" => isset($user_data->email) ? $user_data->email : "", "userName" => isset($user_data->name) ? $users->name : "", "userOtp" => "", "isVerified" => isset($user_data->is_verified) ? $user_data->is_verified : ""));
        }
        return json_encode($result);
    }

    public function signupSendOtp(Request $data)
    {
        $rules = array(
            'language' => 'required',
            'phone' => 'required',
        );
        $post_data = $data->all();
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
         if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 0, "Error" => trans("messages.Error List"), "message" => $errors);
        } else {
            $post_data = $data->all();
            $phone = $post_data['phone'];
            $user_details = DB::table('users')
                ->select('id')
                ->where('mobile', '=', $phone)
                ->first();
            //  print_r($user_details); exit;
            $result = array("status" => 0, "message" => trans("messages.Mobile Number is not regiser in Broz app"));
            if (count($user_details) > 0) {
                $users = Users::find($user_details->id);
                $otp = rand(100000, 999999);
                $app_config = getAppConfig();
                $number = $users->mobile;
                $message = 'You have received OTP password for ' . getAppConfig()->site_name . '. Your OTP code is ' . $otp . '. This code can be used only once and dont share this code with anyone.';
                $twilo_sid = TWILIO_ACCOUNTSID;
                        $twilio_token = TWILIO_AUTHTOKEN;
                        $from_number = TWILIO_NUMBER;
                        /*$twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
                        $twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
                        $from_number = "+14783471785";*/
                //$client = new Services_Twilio($twilo_sid, $twilio_token);
                $client = new Client($twilo_sid, $twilio_token);

                //print_r ($client);exit;
                // Create an authenticated client for the Twilio API
                try {
                    /*$m = $client->account->messages->sendMessage(
                        $from_number, // the text will be sent from your Twilio number
                        $number, // the phone number the text will be sent to
                        $message // the body of the text message
                    );*/
                    $m =  $client->messages->create($number, array('from' => $from_number, 'body' => $message));


                    $users->phone_otp = $otp;
                    $users->updated_date = date("Y-m-d H:i:s");
                    $users->save();
                    $token = JWTAuth::fromUser($users, array('exp' => 200000000000));
                    $result = array("status" => 1, "message" => trans("messages.New OTP has been sent to your register mobile number."), "detail" => array("mobile_number" => $number, "otp" => $otp));
                } catch (Exception $e) {
                    $result = array("status" => 0, "message" => $e->getMessage());
                    return json_encode($result);
                } catch (\Services_Twilio_RestException $e) {
                    $result = array("status" => 0, "message" => $e->getMessage());
                    return json_encode($result);
                }
                $result = array("status" => 1, "message" => trans("messages.New OTP has been sent to your register mobile number."), "detail" => array("mobile_number" => $number, "otp" => $otp));
            }
        }
        return json_encode($result);
    }

    //mob apis:

    // public function mverifyPhone(Request $data)
    // {
    //     $post_data = $data->all();
    //     // print_r($post_data);exit;
    //     $rules = [
    //         'countryCode' => ['required'],
    //         'phoneNumber' => ['required'],
    //         'deviceType' => ['required'],
    //         'deviceId' => ['required_unless:login_type,1,2,3'],
    //         'deviceToken' => ['required_unless:login_type,1,2,3'],
    //     ];
    //     if (isset($post_data['language'])) {
    //         App::setLocale($post_data['language']);
    //     } else {
    //         App::setLocale('en');
    //     }
    //     $errors = $result = array();
    //     $validator = app('validator')->make($post_data, $rules);
    //     if ($validator->fails()) {
    //         $errors = '';
    //         $j = 0;
    //         foreach ($validator->errors()->messages() as $key => $value) {
    //             $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
    //         }
    //         $errors = implode(", \n ", $error);
    //         $result = array("status" => 0, "message" => $errors, "detail" => array());
    //     } else {
    //         $phoneNo = $post_data['countryCode'] . $post_data['phoneNumber'];
    //         $user_data = DB::select('SELECT users.id,users.password ,users.name, users.email, users.status, users.is_verified,users.phone_otp,users.user_token,users.mobile FROM users where users.mobile = ? AND users.user_type = 3  limit 1', array($phoneNo));
    //         $users = new Users;
    //         //print_r($user_data[0]->id);exit;
    //         if (count($user_data) == 0 || empty($user_data[0]->password)) {
    //             $usertoken = sha1(uniqid(Text::random('alnum', 32), true));

    //             if (!$users->user_token) {
    //                 $users->user_token = $usertoken;
    //             }
    //             $users->mobile = $phoneNo;
    //             $users->user_type = 3;
    //             $users->is_verified = 0;
    //             $users->facebook_id = !empty($post_data['facebookId']) ? $post_data['facebookId'] : '';
    //             $users->ip_address = $_SERVER['REMOTE_ADDR'];
    //             $users->created_date = date("Y-m-d H:i:s");
    //             $users->user_created_by = 3;
    //             $users->login_type = 1;
    //             //Check if the login type from mobile app update the device details here
    //             if (isset($post_data['deviceType']) && !empty($post_data['deviceType'])) {
    //                 //Store Android Device details
    //                 if ($post_data['deviceType'] == 2) {
    //                     $users->login_type = 2;
    //                     $users->android_device_id = isset($post_data['deviceId']) ? $post_data['deviceId'] : '';
    //                     $users->android_device_token = isset($post_data['deviceToken']) ? $post_data['deviceToken'] : '';
    //                 }
    //                 //Store iOS Device details
    //                 if ($post_data['deviceType'] == 3) {
    //                     $users->login_type = 3;
    //                     $users->ios_device_id = isset($post_data['deviceId']) ? $post_data['deviceId'] : '';
    //                     $users->ios_device_token = isset($post_data['deviceToken']) ? $post_data['deviceToken'] : '';
    //                 }
    //                 // }
    //                 $verification_key = Text::random('alnum', 12);
    //                 $users->verification_key = $verification_key;

    //                 $otp = rand(1000, 9999);
    //                 $users->phone_otp = $otp;
    //                 $users->updated_date = date("Y-m-d H:i:s");
    //                 // print_r($users);exit;
    //                 $users->save();
    //                 /*delete the old record*/
    //                 if (count($user_data) != 0 /*&& empty($user_data[0]->password)*/) {
    //                     $data = DB::table('users')
    //                         ->select('*')
    //                         ->where('id', $user_data[0]->id)
    //                         ->delete();
    //                 }
    //                 /*delete the old record*/
    //             }

    //             $app_config = getAppConfig();
    //             $number = str_replace('-', '', $phoneNo);
    //             $message = 'You have received OTP password for ' . getAppConfig()->site_name . '. Your OTP code is ' . $otp . '. This code can be used only once and dont share this code with anyone.';
    //             $twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
    //             $twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
    //             $from_number = "+14783471785";
    //             $client = new Services_Twilio($twilo_sid, $twilio_token);
    //             //print_r ($client);exit;
    //             // Create an authenticated client for the Twilio API
    //             try {
    //                 $m = $client->account->messages->sendMessage(
    //                     $from_number, // the text will be sent from your Twilio number
    //                     $number, // the phone number the text will be sent to
    //                     $message // the body of the text message
    //                 );

    //                 $token = JWTAuth::fromUser($users, array('exp' => 200000000000));
    //                 $result = array("status" => 2, "message" => trans("messages.OTP sent"), "detail" => array("userId" => $users->id, "countryCode" => $post_data['countryCode'], "phoneNumber" => $post_data['phoneNumber'], "token" => $token, "isNewUser" => 1, "userEmail" => isset($users->email) ? $users->email : "", "userName" => isset($users->name) ? $users->name : "", "userOtp" => $otp));
    //             } catch (Exception $e) {
    //                 // $result = array("response" => array("httpCode" => 400,"Message" => $e->getMessage()));

    //                 $result = array("status" => 0, "message" => $e->getMessage());
    //                 return json_encode($result);
    //             } catch (\Services_Twilio_RestException $e) {
    //                 // $result = array("response" => array("httpCode" => 400,"Message" => $e->getMessage()));
    //                 //print_r("exception->" . $e->getCode());
    //                 $result = array("status" => 0, "message" => $e->getMessage());
    //                 return json_encode($result);
    //             }
    //         } else {
    //             if (!empty($post_data['facebookId'])) {
    //                 //while signup through facebook
    //                 $result = array("status" => 0, "message" => trans("messages.The Entered phone number is already registered."));
    //             } else {
    //                 $user_data = $user_data[0];
    //                 $token = JWTAuth::fromUser($users, array('exp' => 200000000000));
    //                 $result = array("status" => 1, "message" => trans("Please enter your Password"), "detail" => array("userId" => $user_data->id, "countryCode" => $post_data['countryCode'], "phoneNumber" => $post_data['phoneNumber'], "token" => $token, "isNewUser" => 0, "userEmail" => isset($user_data->email) ? $user_data->email : "", "userName" => isset($user_data->name) ? $user_data->name : "", "userOtp" => ""));
    //             }
    //         }
    //     }

    //     return json_encode($result, JSON_UNESCAPED_UNICODE);
    // }

     public function mverifyPhone(Request $data)
    {
        $post_data = $data->all();
        $rules = [
            'countryCode' => ['required'],
            'phoneNumber' => ['required'],
            'deviceType' => ['required_unless:login_type,2,3'],
            'deviceId' => ['required_unless:login_type,2,3'],
            'deviceToken' => ['required_unless:login_type,2,3'],
        ];
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
         if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 0, "message" => $errors, "detail" => array());
        } else {

            // $phoneNo = $post_data['countryCode'] . $post_data['phoneNumber'];
            $phoneNo =  $post_data['phoneNumber'];

            $countryCode = $post_data['countryCode'];
            $user_data = DB::select('SELECT users.id,users.password ,users.name, users.email, users.status, users.is_verified,users.phone_otp,users.user_token,users.mobile,users.country_code FROM users where users.mobile = ? AND users.country_code = ? AND users.user_type = 3  limit 1', array($phoneNo,$countryCode));

          
            $users = new Users;
            //print_r($user_data[0]->id);exit;
            if (count($user_data) == 0 || empty($user_data[0]->password)) {
                //echo(sha1(uniqid(random_bytes(24),true)));exit();
                $usertoken = sha1(uniqid(random_bytes(24),true));
                //$usertoken = sha1(uniqid(Text::random('alnum', 32), true));

                if (!$users->user_token) {
                    $users->user_token = $usertoken;
                }
                $users->mobile = $phoneNo;
                $users->user_type = 3;
                $users->is_verified = 0;
                $users->facebook_id = !empty($post_data['facebookId']) ? $post_data['facebookId'] : '';
                $users->ip_address = $_SERVER['REMOTE_ADDR'];
                $users->created_date = date("Y-m-d H:i:s");
                $users->user_created_by = 3;
                $users->login_type = 1;
                //Check if the login type from mobile app update the device details here
                if (isset($post_data['deviceType']) && !empty($post_data['deviceType'])) {
                    //Store Android Device details
                    if ($post_data['deviceType'] == 2) {
                        $users->login_type = 2;
                        $users->android_device_id = isset($post_data['deviceId']) ? $post_data['deviceId'] : '';
                        $users->android_device_token = isset($post_data['deviceToken']) ? $post_data['deviceToken'] : '';
                    }
                    //Store iOS Device details
                    if ($post_data['deviceType'] == 3) {
                        $users->login_type = 3;
                        $users->ios_device_id = isset($post_data['deviceId']) ? $post_data['deviceId'] : '';
                        $users->ios_device_token = isset($post_data['deviceToken']) ? $post_data['deviceToken'] : '';
                    }
                    // }
                   // $verification_key = Text::random('alnum', 12);
                    $verification_key = str_random(12);

                   // print_r($verification_key);exit();
                    $users->verification_key = $verification_key;

                    $otp = rand(1000, 9999);
                    $users->phone_otp = $otp;
                    $users->country_code= $countryCode;
                    $users->updated_date = date("Y-m-d H:i:s");
                    // print_r($users);exit;
                   
                    /*delete the old record*/
                    if (count($user_data) != 0 /*&& empty($user_data[0]->password)*/) {
                        $data = DB::table('users')
                            ->select('*')
                            ->where('id', $user_data[0]->id)
                            ->delete();
                    }
                    /*delete the old record*/

                    $phone=$countryCode.$phoneNo ;
                }
                $users->save();

                $app_config = getAppConfig();
                $number = str_replace('-', '', $phone);
                $message = 'You have received OTP password for ' . getAppConfig()->site_name . '. Your OTP code is ' . $otp . '. This code can be used only once and dont share this code with anyone.';
                $twilo_sid = TWILIO_ACCOUNTSID;
                        $twilio_token = TWILIO_AUTHTOKEN;
                        $from_number = TWILIO_NUMBER;
                        /*$twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
                        $twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
                        $from_number = "+14783471785";*/
                $token = JWTAuth::fromUser($users, array('exp' => 200000000000));
                $result = array("status" => 2, "message" => trans("messages.OTP sent"), "detail" => array("userId" => $users->id, "countryCode" =>$countryCode, "phoneNumber" => $post_data['phoneNumber'], "token" => $token, "isNewUser" => 1, "userEmail" => isset($users->email) ? $users->email : "", "userName" => isset($users->name) ? $users->name : "", "userOtp" => $otp));
                /*$client = new Services_Twilio($twilo_sid, $twilio_token);
                //print_r ($client);exit;
                // Create an authenticated client for the Twilio API
                try {
                    $m = $client->account->messages->sendMessage(
                        $from_number, // the text will be sent from your Twilio number
                        $number, // the phone number the text will be sent to
                        $message // the body of the text message
                    );

                    // $data=array();
                    // $getCode=DB::table('users')
                    //         ->select('users.country_code')
                    //         ->where('users.mobile',$phoneNo)
                    //         ->get();
                    //   if(count($getCode)>0){      
                    //     foreach ($getCode as $key => $value) {
                    //            $data[$key]['countryCode']= $value->country_code;
                    //         }  
                    //     }
                           // print_r($value->country_code);exit();  
                    $result = array("status" => 2, "message" => trans("messages.OTP sent"), "detail" => array("userId" => $users->id, "countryCode" =>$countryCode, "phoneNumber" => $post_data['phoneNumber'], "token" => $token, "isNewUser" => 1, "userEmail" => isset($users->email) ? $users->email : "", "userName" => isset($users->name) ? $users->name : "", "userOtp" => $otp));
                    //Saved to database only after sms send succesful 
                 
                } catch (Exception $e) {
                    // $result = array("response" => array("httpCode" => 400,"Message" => $e->getMessage()));

                    $result = array("status" => 0, "message" => $e->getMessage());
                    return json_encode($result);
                } catch (\Services_Twilio_RestException $e) {
                    // $result = array("response" => array("httpCode" => 400,"Message" => $e->getMessage()));
                    //print_r("exception->" . $e->getCode());
                    $result = array("status" => 0, "message" => $e->getMessage());
                    return json_encode($result);
                }*/
            } else {
                // if (!empty($post_data['facebookId'])) {
                //     //while signup through facebook
                //     $result = array("status" => 0, "message" => trans("messages.The Entered phone number is already registered."));
                // } 

                /*if ($post_data['isFacebookLogin']!==false) {
                    //while signup through facebook
                    $result = array("status" => 0, "message" => trans("messages.The Entered phone number is already registered."));
                }
                else {*/
                    $user_data = $user_data[0];
                    $token = JWTAuth::fromUser($users, array('exp' => 200000000000));
                    $result = array("status" => 1, "message" => trans("messages.Please enter your Password"), "detail" => array("userId" => $user_data->id, "countryCode" =>$user_data->country_code, "phoneNumber" => $user_data->mobile, "token" => $token, "isNewUser" => 0, "userEmail" => isset($user_data->email) ? $user_data->email : "", "userName" => isset($user_data->name) ? $user_data->name : "", "userOtp" => ""));
               // }
            }

            
        }

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    /*public function mverifyPhone(Request $data) {

        $post_data = $data->all();
        // print_r($post_data);exit;
        $rules = [
            'countryCode' => ['required'],
            'phoneNumber' => ['required'],
            'deviceType' => ['required'],
            'deviceId' => ['required_unless:login_type,1,2,3'],
            'deviceToken' => ['required_unless:login_type,1,2,3'],
        ];
        if (isset($post_data['language'])) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => "-1", "message" => $errors, "detail" => array());
        } else {
            $phoneNo = $post_data['countryCode'] . $post_data['phoneNumber'];
            $user_data = DB::select('SELECT users.id,users.password ,users.name, users.email, users.status, users.is_verified,users.phone_otp,users.user_token,users.mobile FROM users where users.mobile = ? AND users.user_type = 3  limit 1', array($phoneNo));
            print_r($user_data);exit;
            $users = new Users;

            // print_r("expression" .$user_data[0]->password);exit();

            if (count($user_data) == 0 || empty($user_data[0]->password)) {
                //reponse to redirect to otp page and send otp sms

                // if(count($user_data) > 0){

                //     $users   =$user_data[0];
                                //       $otp        = rand(1000,9999);
                                //        $res = DB::table('users')
                                //                         ->where('id', $user_data[0]->id)
                                //                         ->update(['phone_otp' => $otp,'android_device_token' => $post_data['deviceToken'], 'android_device_id' => $post_data['deviceId'], 'login_type' => $post_data['deviceType']] );

                //       // print_r("$usertoken");exit;
                //  }else{
                $usertoken = sha1(uniqid(Text::random('alnum', 32), TRUE));

                if (!$users->user_token) {
                    $users->user_token = $usertoken;
                }

                // $users->mobile       = preg_replace("/[^+0-9]+/", "", $post_data['phone']);
                $users->mobile = $phoneNo;
                $users->user_type = 3;
                $users->is_verified = 0;
                $users->facebook_id = !empty($post_data['facebookId']) ? $post_data['facebookId'] : '';
                $users->ip_address = $_SERVER['REMOTE_ADDR'];
                $users->created_date = date("Y-m-d H:i:s");
                $users->user_created_by = 3;
                $users->login_type = 1;
                //Check if the login type from mobile app update the device details here
                if (isset($post_data['deviceType']) && !empty($post_data['deviceType'])) {
                    //Store Android Device details
                    if ($post_data['deviceType'] == 2) {
                        $users->login_type = 2;
                        $users->android_device_id = isset($post_data['deviceId']) ? $post_data['deviceId'] : '';
                        $users->android_device_token = isset($post_data['deviceToken']) ? $post_data['deviceToken'] : '';
                    }
                    //Store iOS Device details
                    if ($post_data['deviceType'] == 3) {
                        $users->login_type = 3;
                        $users->ios_device_id = isset($post_data['deviceId']) ? $post_data['deviceId'] : '';
                        $users->ios_device_token = isset($post_data['deviceToken']) ? $post_data['deviceToken'] : '';
                    }
                    // }
                    $verification_key = Text::random('alnum', 12);
                    $users->verification_key = $verification_key;

                    $otp = rand(1000, 9999);
                    $users->phone_otp = $otp;
                    $users->updated_date = date("Y-m-d H:i:s");
                    // print_r($users);exit;
                    $users->save();
                }

                $app_config = getAppConfig();
                $number = str_replace('-', '', $phoneNo);
                $message = 'You have received OTP password for ' . getAppConfig()->site_name . '. Your OTP code is ' . $otp . '. This code can be used only once and dont share this code with anyone.';
                $twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
                $twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
                $from_number = "+14783471785";
                $client = new Services_Twilio($twilo_sid, $twilio_token);
                //print_r ($client);exit;
                // Create an authenticated client for the Twilio API
                try {
                    $m = $client->account->messages->sendMessage(
                        $from_number, // the text will be sent from your Twilio number
                        $number, // the phone number the text will be sent to
                        $message // the body of the text message
                    );

                    $token = JWTAuth::fromUser($users, array('exp' => 200000000000));
                    $result = array("status" => 2, "message" => trans("messages.OTP sent"), "detail" => array("userId" => $users->id, "countryCode" => $post_data['countryCode'], "phoneNumber" => $post_data['phoneNumber'], "token" => $token, "isNewUser" => 1, "userEmail" => isset($users->email) ? $users->email : "", "userName" => isset($users->name) ? $users->name : "", "userOtp" => $otp));
                } catch (Exception $e) {
                    // $result = array("response" => array("httpCode" => 400,"Message" => $e->getMessage()));

                    $result = array("status" => -1, "message" => $e->getMessage());
                    return json_encode($result);
                } catch (\Services_Twilio_RestException $e) {
                    // $result = array("response" => array("httpCode" => 400,"Message" => $e->getMessage()));
                    //print_r("exception->" . $e->getCode());
                    $result = array("status" => -1, "message" => $e->getMessage());
                    return json_encode($result);
                }
            } else {
                if (!empty($post_data['facebookId'])) {
                //while signup through facebook
                    $result = array("status" => 0, "message" => trans("messages.The Entered phone number is already registered."));
                } else {
                    $user_data = $user_data[0];
                    $token = JWTAuth::fromUser($users, array('exp' => 200000000000));
                    $result = array("status" => 1, "message" => trans("Please enter your Password"), "detail" => array("userId" => $user_data->id, "countryCode" => $post_data['countryCode'], "phoneNumber" => $post_data['phoneNumber'], "token" => $token, "isNewUser" => 0, "userEmail" => isset($user_data->email) ? $user_data->email : "", "userName" => isset($user_data->name) ? $user_data->name : "", "userOtp" => ""));
                }

            }
        }

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
*/
    public function msignupOtpVerify(Request $data)
    {
        $post_data = $data->all();

       if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $countryCode = !empty($post_data['countryCode']) ? $post_data['countryCode'] : '';
        $phone = !empty($post_data['phoneNumber']) ? $post_data['phoneNumber'] : '';

        // print_r($phone);
        // print_r($countryCode);
        // $phoneNumber = $countryCode . $phone;
        $phoneNumber = $phone;
        //print_r($post_data); exit;
        $user_details = DB::table('users')
            ->select('id')
            ->where('mobile', '=', $phoneNumber)
            ->where('phone_otp', '=', $post_data['otp'])
            ->first();
        //	print_r($phoneNumber); exit;
        $result = array("status" => 0, "message" => trans("messages.Verification Failed kindly check your OTP"));


         $data=array();
         $getCode=DB::table('users')
                ->select('users.country_code','users.mobile')
                ->where('users.mobile',$phoneNumber)
                ->get();

        if(count($getCode)>0){      
        foreach ($getCode as $key => $value) {
                   $data[$key]['countryCode']= $value->country_code;
                   $data[$key]['mobile']= $value->mobile;
                }  
        }
        if (count($user_details) > 0) {
            $user_data = Users::find($user_details->id);
            $user_data->is_verified = 1;
            $user_data->save();
            $token = JWTAuth::fromUser($user_data, array('exp' => 200000000000));

            $result = array("status" => 1, "message" => trans("messages.OTP Verified Successfully.Please fill your pofile details"), "detail" => array("userId" => $user_data->id, "countryCode" => $value->country_code, "phoneNumber" =>$value->mobile, "token" => $token, "isNewUser" => 0, "userEmail" => isset($user_data->email) ? $user_data->email : "", "userName" => isset($user_data->name) ? $user_data->name : "", "userOtp" => "", "isVerified" => isset($user_data->is_verified) ? $user_data->is_verified : ""));
        }
        return json_encode($result);
    }
 
    public function mresendOtp(Request $data)
    {
        $rules = array(
            'language' => 'required',
            'phoneNumber' => 'required',
            'countryCode' => 'required',
        );
        $post_data = $data->all();
        // if (isset($post_data['language'])) {
        //     App::setLocale($post_data['language']);
        // } else {
        //     App::setLocale('en');
        // }
         if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 0, "Error" => trans("messages.Error List"), "message" => $errors);
        } else {
            $post_data = $data->all();
            $phone = $post_data['phoneNumber'];
            $countryCode = $post_data['countryCode'];
            // $phoneNumber = $countryCode . '-' . $phone;
            $phoneNumber = $countryCode . $phone;
            $user_details = DB::table('users')
                ->select('id')
                ->where('mobile', '=', $phone)
                ->first();
            //  print_r($user_details); exit;
            $result = array("status" => 0, "message" => trans("messages.Mobile Number is not register in Broz app"));
            if (count($user_details) > 0) {
                $users = Users::find($user_details->id);
                $otp = rand(1000, 9999);

                $app_config = getAppConfig();
             //   $number = str_replace('-', '', $users->mobile); //to remove the '-'
                $number=$phoneNumber;

                $message = 'You have received OTP password for ' . getAppConfig()->site_name . '. Your OTP code is ' . $otp . '. This code can be used only once and dont share this code with anyone.';
                $twilo_sid = TWILIO_ACCOUNTSID;
                        $twilio_token = TWILIO_AUTHTOKEN;
                        $from_number = TWILIO_NUMBER;
                        /*$twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
                        $twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
                        $from_number = "+14783471785";*/
                //$client = new Services_Twilio($twilo_sid, $twilio_token);
                $client = new Client($twilo_sid, $twilio_token);
                //print_r ($client);exit;
                // Create an authenticated client for the Twilio API
                try {
                   /* $m = $client->account->messages->sendMessage(
                        $from_number, // the text will be sent from your Twilio number
                        $number, // the phone number the text will be sent to
                        $message // the body of the text message
                    );*/
                    $m =  $client->messages->create($number, array('from' => $from_number, 'body' => $message));

                    $users->phone_otp = $otp;
                    $users->updated_date = date("Y-m-d H:i:s");
                    $users->save();
                    $token = JWTAuth::fromUser($users, array('exp' => 200000000000));
                    $result = array("status" => 1, "message" => trans("messages.New OTP has been sent to your register mobile number."), "detail" => array("countryCode" => $post_data['countryCode'], "phoneNumber" => $post_data['phoneNumber'], "userOtp" => $otp));
                } catch (Exception $e) {
                    $result = array("status" => 0, "message" => $e->getMessage());
                    return json_encode($result);
                } catch (\Services_Twilio_RestException $e) {
                    $result = array("status" => 0, "message" => $e->getMessage());
                    return json_encode($result);
                }
            }
        }
        return json_encode($result);
    }

    public function mverifyPassword(Request $data)
    {
        $post_data = $data->all();
        $rules = [
            'phoneNumber' => ['required'],
            'countryCode' => ['required'],
            'userPassword' => ['required'],
            'deviceType' => ['required'],
            // 'user_type'    => ['required'],
            'language' => ['required'],
            'deviceId' => ['required_unless:login_type,1,2,3'],
            'deviceToken' => ['required_unless:login_type,1,2,3'],
        ];
        // if (isset($post_data['language'])) {
        //     App::setLocale($post_data['language']);
        // } else {
        //     App::setLocale('en');
        // }
         if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $errors = $result = array();
        //print_r($post_data['phoneNumber']);exit;
        $validator = app('validator')->make($post_data, $rules);
        $phone = !empty($post_data['phoneNumber']) ? $post_data['phoneNumber'] : '';
        $countryCode = !empty($post_data['countryCode']) ? $post_data['countryCode'] : '';
       // $phoneNumber = $countryCode . $phone;
        $phoneNumber =  $phone;
        $password = !empty($post_data['userPassword']) ? $post_data['userPassword'] : '';
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            $user_data = DB::select('SELECT users.id, users.name, users.email, users.social_title, users.first_name, users.last_name, users.image, users.status, users.is_verified, users.facebook_id,users.loggedin_status, users.mobile FROM users where users.password = ? AND users.mobile = ? AND users.user_type=3  limit 1', array(md5($password), $phoneNumber));

            if (count($user_data) > 0) {
                $user_data = $user_data[0];
                if ($user_data->is_verified == 0) {
                    $result = array("status" => 0, "message" => trans("messages.Please verify your phone number."));
                } elseif ($user_data->status == 0) {
                    $result = array("status" => 0, "message" => trans("messages.Your registration has blocked pls contact Your Admin."));
                } else {
                    // Check login type based on mobile api parameters
                    if (isset($post_data['deviceType']) && !empty($post_data['deviceType'])) {
                        //Update the device token & id for Android
                        if ($post_data['deviceType'] == 2) {
                            $res = DB::table('users')
                                ->where('id', $user_data->id)
                                ->update(['android_device_token' => $post_data['deviceToken'], 'android_device_id' => $post_data['deviceId'], 'login_type' => $post_data['deviceType']]);
                        }
                        //Update the device token & id for iOS
                        if ($post_data['deviceType'] == 3) {
                            $res = DB::table('users')
                                ->where('id', $user_data->id)
                                ->update(['ios_device_token' => $post_data['deviceToken'], 'ios_device_id' => $post_data['deviceId'], 'login_type' => $post_data['deviceType']]);
                        }
                    }

                    if ($user_data->loggedin_status == 0) {
                        $res = DB::table('users')
                                ->where('id', $user_data->id)
                                ->update(['loggedin_status' => 1]);
                    }


                    

                    //print_r($userAddress);exit();


                    $token = JWTAuth::fromUser($user_data, array('exp' => 200000000000));

                    // $userAddress=DB::table('user_address')
                                               
                    //         ->join('users', 'users.id', '=', 'user_address.user_id')
                    //         ->select('user_address.address', 'user_address.latitude', 'user_address.longitude')
                    //         ->where('users.id', $user_data->id)
                    //         ->get();


                            	$userAddress = DB::table('user_address')
					->select('user_address.address', 'user_address.latitude', 'user_address.longitude', 'user_address.address_type', 'user_address.user_id', 'user_address.id as address_id','user_address.land_mark','user_address.home_no','user_address.created_date','user_address.modified_date','user_address.active_status','user_address.type_text','user_address.id'/*,'address_infos.type_text as type_text'*/)
					//->leftJoin('address_infos', 'address_infos.address_id', '=', 'user_address.id')
					->where('user_id', '=', $user_data->id)

					->orderBy('user_address.id', 'desc')
					->get();


                     // $userOrders=DB::table('orders')
                                                                   
                     //        ->join('users', 'users.id', '=', 'orders.customer_id')
                     //        ->select('orders.id')
                     //        ->where('users.id', $user_data->id)
                     //        ->get();

                    $orders_data = DB::table('orders')
                ->join('users', 'users.id', '=', 'orders.customer_id')
                ->join('outlet_infos', 'outlet_infos.id', '=', 'orders.outlet_id')
                ->select('orders.id as orderId', 'orders.vendor_id as vendorId', 'orders.customer_id as customerId', 'orders.outlet_id as outletId', 'orders.driver_ids as driverId', 'orders.total_amount as totalAmount', 'orders.order_status as orderStatus', 'outlet_infos.outlet_name as outletName')
                ->where('users.id', $user_data->id)
                 ->where('orders.order_status', '<>', '12')
                //->where('orders.order_status', '!=', '12')
               // ->where('orders.order_status', '!=', '11')
                ->orderByRaw('orders.id DESC')
                ->get();

                //print_r($orders_data);exit();

                if(count($orders_data)>0){
                    $datas=array();
                    foreach ($orders_data as $key => $value) {
                       $datas[$key]['orderId']=$value->orderId;
                       $datas[$key]['vendorId']=$value->vendorId;
                       $datas[$key]['customerId']=$value->customerId;
                       $datas[$key]['totalAmount']=$value->totalAmount;
                       $datas[$key]['orderStatus']=$value->orderStatus;
                       $datas[$key]['outletName']=$value->outletName;
                       $datas[$key]['driverId']=isset($value->driverId) ? $value->driverId : '';
                    }


                }

           
                // print_r($data);exit();

                     $data=array();
                     $getCode=DB::table('users')
                            ->select('users.country_code','users.mobile')
                            ->where('users.mobile',$phoneNumber)
                            ->get();

                    if(count($getCode)>0){      
                    foreach ($getCode as $key => $value) {
                               $data[$key]['countryCode']= $value->country_code;
                               $data[$key]['mobile']= $value->mobile;
                            }  
                    }
                            
                            Session::put('user_id', $user_data->id);
                            Session::put('mobile', $value->mobile);
                            Session::put('email', $user_data->email);
                            Session::put('first_name', $user_data->name);
                            Session::put('last_name', $user_data->name);
                            Session::put('name', $user_data->name);
                            Session::put('social_title', "");
                            Session::put('profile_image', "");
                            Session::put('token', $token);
 
                        $result = array("status" => 1, "message" => trans("messages.Success, Signed in successfully."), "detail" => array("userId" => $user_data->id, "token" => $token, "phoneNumber" => $value->mobile, "countryCode" => $value->country_code, "userOtp" => "", "isNewUser" => 0, "userName" => $user_data->name, "userEmail" => $user_data->email,"userAddress"=>$userAddress,"userOrders"=>$orders_data));
                            
                    // if (count($userAddress)>0) {
                        
                    //     $result = array("status" => 1, "message" => trans("messages.Success, Signed in successfully."), "detail" => array("userId" => $user_data->id, "token" => $token, "phoneNumber" => $value->mobile, "countryCode" => $value->country_code, "userOtp" => "", "isNewUser" => 0, "userName" => $user_data->name, "userEmail" => $user_data->email,"userAddress"=>$userAddress,"userOrders"=>$orders_data));
                    // } else {
                    //     $result = array("status" => 1, "message" => trans("messages.Success, Signed in successfully."), "detail" => array("userId" => $user_data->id, "token" => $token, "phoneNumber" => $value->mobile, "countryCode" => $value->country_code, "userOtp" => "", "isNewUser" => 0, "userName" => $user_data->name, "userEmail" => $user_data->email,"userAddress"=>array(),"userOrders"=>array()));
                    // }
                }
            } else {
                $result = array("status" => 0, "message" => trans("messages.Password seems to be incorrect."));
            }
        }
        return $result;
    }


    public function mfacebookSignup(Request $data)
    {

        $post_data = $data->all();
        // if ($post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
         if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $users = new Users;
        $usertoken = sha1(uniqid(Text::random('alnum', 32), true));
        $verification_key = Text::random('alnum', 12);
        $string = str_random(8);
        $pass_string = md5($string);
        if (!$users->user_token) {
            $users->user_token = $usertoken;
        }


        $userEmail=$post_data['userEmail'];
        $name=$post_data['userName'];
        
        $facebookId=$post_data['facebookId'];

        if ($post_data['facebookId'] != "") {
            $check_user = DB::table('users')
                ->select('id', 'email', 'mobile','country_code', 'name', 'image')
                ->where('facebook_id', '=', $post_data['facebookId'])
                ->first();

            if (count($check_user) == 0) {
                $result = array("status" => 2, "message" => trans("messages.Welcome to Broz! Please enter your phone number to continue the signup process."), "detail" => array("isNewUser" => 1, "userName" => $name, "userEmail" => $userEmail,"phoneNumber"=>"","countryCode"=>""));
            } else {
                $user_id = $check_user->id;
                $email = $check_user->email;
                $userName = $check_user->name;
                $image = $check_user->image;
                $mobile = $check_user->mobile;
                $countryCode = $check_user->country_code;
                // $countryCode = "";
                $phoneNumber = $mobile;
                if (strpos($mobile, '-') !== false) {
                    $phoneArr = explode('-', $mobile);
                    // $countryCode = $phoneArr[0];
                    $phoneNumber = $phoneArr[1];

                }

                if (empty($email)) {
                    $result = array("status" => 3, "message" => trans("messages.Welcome to Broz! Please enter your profile details to continue the signup process."), "detail" => array("userId" => $check_user->id, "phoneNumber" => $phoneNumber,"countryCode" => $countryCode, "isNewUser" => 0, "userName" => "", "userEmail" => ""));
                }
                 else {
                    $update_array = array();
                    if ($post_data['deviceType'] == 2) {
                        $update_array['login_type'] = 2;
                        $update_array['android_device_id'] = isset($post_data['deviceId']) ? $post_data['deviceId'] : '';
                        $update_array['android_device_token'] = isset($post_data['deviceToken']) ? $post_data['deviceToken'] : '';
                        $update_array['user_type'] = 4;
                    }
                    if ($post_data['loginType'] == 3) {
                        $update_array['login_type'] = 3;
                        $update_array['ios_device_id'] = isset($post_data['deviceId']) ? $post_data['deviceId'] : '';
                        $update_array['ios_device_token'] = isset($post_data['deviceToken']) ? $post_data['deviceToken'] : '';
                        $update_array['user_type'] = 4;
                    }
                    if (count($update_array) > 0) {
                        $res = DB::table('users')->where('id', $user_id)->update($update_array);
                    }

                    $user_signup_image = url('/assets/admin/base/images/default_avatar_male.jpg');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/admin/profile/' . $image) && $image != '') {
                        $user_signup_image = URL::to("assets/admin/base/images/admin/profile/" . $image);
                    }



                    


                    $token = JWTAuth::fromUser($check_user, array('exp' => 200000000000));

                    $userAddress=DB::table('user_address')
                                               
                            ->join('users', 'users.id', '=', 'user_address.user_id')
                            ->select('user_address.address')
                            ->where('users.id', $check_user->id)
                            ->get();

                    $orders_data = DB::table('orders')
                            ->join('users', 'users.id', '=', 'orders.customer_id')
                            ->join('outlet_infos', 'outlet_infos.id', '=', 'orders.outlet_id')
                            ->select('orders.id as orderId', 'orders.vendor_id as vendorId', 'orders.customer_id as customerId', 'orders.outlet_id as outletId', 'orders.driver_ids as driverId', 'orders.total_amount as totalAmount', 'orders.order_status as orderStatus', 'outlet_infos.outlet_name as outletName ORDER BY orders.id')
                            ->where('users.id', $user_id)
                            ->where('orders.order_status', '<>', '12')
                            ->orderByRaw('orders.id DESC')
                            ->get();

                    $result = array("status" => 1, "message" => trans("messages.Success, Welcome to Broz!"), "detail" => array("userId" => $check_user->id, "phoneNumber" => $phoneNumber,"countryCode" => $check_user->country_code,  "isNewUser" => 0, "userName" => $userName, "userEmail" => $email,"userOrders" => $orders_data,'userAddress'=>$userAddress));
                            
                    //print_r($userAddress);exit();
                    // if (count($userAddress)>0) {
                    //     $result = array("status" => 1, "message" => trans("messages.Success, Welcome to Broz!"), "detail" => array("userId" => $check_user->id, "phoneNumber" => $phoneNumber,"countryCode" => $check_user->country_code, "isNewUser" => 0, "userName" => $userName, "userEmail" => $email,'userAddress'=>$userAddress));
                    // } else {
                    //     $result = array("status" => 1, "message" => trans("messages.Success, Welcome to Broz!"), "detail" => array("userId" => $check_user->id, "phoneNumber" => $phoneNumber,"countryCode" => $check_user->country_code,  "isNewUser" => 0, "userName" => $userName, "userEmail" => $email,'userAddress'=>array()));
                    // }
                    $subject = 'Success,Thank you for signup with us, Welcome to Broz!';
                    $values = array(
                         'customer_id' => $check_user->id,               
                         'message' => $subject,
                         'read_status' => 0,
                         'created_date' => date('Y-m-d H:i:s'));
                        DB::table('notifications')->insert($values);


                    $to = $email;

                    $template = DB::table('email_templates')->select('*')->where('template_id', '=', self::USER_SIGNUP_EMAIL_TEMPLATE)->get();
                                       // print_r($template);exit();

                   // print_r($to);exit();

                    if (count($template)) {
                     $from = $template[0]->from_email;


                     $from_name = $template[0]->from;

                     if (!$template[0]->template_id) {


                         $template = 'mail_template';
                         $from = getAppConfigEmail()->contact_mail;


                     }
                     $subject = 'Welcome to '. getAppConfig()->site_name ;

                      $url1 = '<a href="' . url('/')  . '"> This  Link </a>';
                                                        

                     $content = array('name' => "" . $userName,'email' => "" . $email,'password' => "" ,"confirmation_link" => $url1);


                     $attachment = "";
                     $email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
                    }


                }
            }
        } else {
            $result = array("status" => 0, "message" => trans("messages.Facebook Id field is required."));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /*athira - forgot paswd for user mob*/
    
    
    public function forgotPassword(Request $data)
    {
       // print_r(TWILIO_ACCOUNTSID);exit();
        $rules = array(
            'language' => 'required',
            'phoneNumber' => 'required',
            'countryCode' => 'required',
        );

        $post_data = $data->all();

       // if (isset($post_data['language']) && $post_data['language'] == 2) {
       //      App::setLocale('ar');
       //  } else {
       //      App::setLocale('en');
       //  }
         if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);

        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 0, "Error" => trans('messages.Error List'), "message" => $errors);
        } else {
            $post_data = $data->all();
            $phone = $post_data['phoneNumber'];
            $countryCode = $post_data['countryCode'];
            // $phoneNumber = $countryCode.$phone;
            $phoneNumber = $phone;
            //print_r($phoneNumber);exit;
            $user_details = DB::table('users')
                ->select('id')
                ->where('mobile', '=', $phoneNumber)
                ->first();
            //print_r($user_details);exit;
            $result = array("status" => 2, "message" => trans('messages.Mobile Number is not register'));
            if (count($user_details) > 0) {
                $users = Users::find($user_details->id);
                $otp = rand(1000, 9999);
                $otp_unique = str_random(8);
                $pass_string = md5($otp_unique);
                $app_config = getAppConfig();
                $mob =$users->mobile; 
                //print_r($number);exit;
                $code=$users->country_code;
                // $number = str_replace('-', '', $code); //to remove the '-'
                $number=$code.$mob;

                $message = 'You have received OTP password for ' . getAppConfig()->site_name . '. Your OTP code is ' . $otp . '. This code can be used only once and dont share this code with anyone.';
                $twilo_sid = TWILIO_ACCOUNTSID;
                $twilio_token = TWILIO_AUTHTOKEN;
                $from_number = TWILIO_NUMBER;
                /*$twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
                $twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
                $from_number = "+14783471785";*/

                $client = new Client($twilo_sid, $twilio_token);
                // print_r($client);exit();
                //$client = new Services_Twilio($twilo_sid, $twilio_token);
                //print_r ($client);exit;
                // Create an authenticated client for the Twilio API
                try {
                   /* $m = $client->account->messages->sendMessage(
                        $from_number, // the text will be sent from your Twilio number
                        $number, // the phone number the text will be sent to
                        $message // the body of the text message
                    );
                    */
                    $m =  $client->messages->create($number,
                        array('from' => $from_number, 'body' => $message));

                    $users->phone_otp = $otp;
                    $users->otp_unique = $pass_string;
                    $users->updated_date = date("Y-m-d H:i:s");
                    $users->save();
                    $token = JWTAuth::fromUser($users, array('exp' => 200000000000));
                    $result = array("status" => 1, "otpUnique" => $pass_string, "userOtp" => $otp, "message" => trans('messages.New OTP has been sent to your register mobile number.'));
                } catch (Exception $e) {
                    $result = array("status" => 0, "message" => $e->getMessage());
                    return json_encode($result);
                } catch (\Services_Twilio_RestException $e) {
                    $result = array("status" => 0, "message" => $e->getMessage());
                    return json_encode($result);
                }
            }
        }

        return json_encode($result);
    }

    public function mforgotOtp(Request $data)
    {
        //print_r("expression");exit;
        $post_data = $data->all();

        $rules = array(
                'otpUnique' => 'required',

            );

        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
         if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        //print_r($post_data); exit;
        $user_details = DB::table('users')
                ->select('id')
                ->where('otp_unique', '=', $post_data['otpUnique'])
                ->where('phone_otp', '=', $post_data['otp'])
                ->first();
        $result = array("status" => 0, "message" => trans('messages.Verification Failed kindly check your otp or otpUnique'));
        if (count($user_details) > 0) {
            $user_data = Users::find($user_details->id);
            $otp_unique = str_random(8);
            $pass_string = md5($otp_unique);
            $user_data->otp_unique = $pass_string;
            $user_data->is_verified = 1;
            $user_data->save();
            $token = JWTAuth::fromUser($user_data, array('exp' => 200000000000));
            $result = array("status" => 1, "userUnique" => $pass_string, "message" => trans('messages.OTP Verified Successfully,Please change password.'));
        }
        return json_encode($result);
    }

    public function update_new_password(Request $data)
    {
        $data_all = $data->all();
        // if ($data_all['language'] == 2) {
        // 	App::setLocale('ar');
        // } else {
        // 	App::setLocale('en');
        // }

        $rules = [
            'userUnique' => ['required'],
            'newPassword' => ['required'],
            'retypePassword' => ['required'],
            'deviceType' => ['required'],
            'deviceId' => ['required_unless:login_type,1,2,3'],
            'deviceToken' => ['required_unless:login_type,1,2,3'],
        ];
        $errors = $result = array();
        $validator = app('validator')->make($data->all(), $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("status" => 0,  "message" => trans('messages.Error List'),"detail" => $errors);
        } else {
            $errors = '';
            $password = $data_all['newPassword'];
            // print_r($password);exit();
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*?[0-9])[a-zA-Z\d]{6,}$/', $password)) {
                $errors .= $validator->errors()->add('newPassword', 'The password Minimum 6 characters at least 1 Uppercase Alphabet, 1 Lowercase Alphabet and 1 Number.');
            }
            if ($errors != '') {
                $result = array("status" => 3,  "message" => trans("messages.The password Minimum 6 characters at least 1 Uppercase Alphabet, 1 Lowercase Alphabet and 1 Number."));
            }

            $retypePassword = $data_all['retypePassword'];
            if ($retypePassword != $password) {
                $result = array("status" => 2, "message" => trans('messages.Password and retypePassword is not matched please check'));
            } else {

                //$check_auth = JWTAuth::toUser($data_all['token']);
                //Get new password details from posts

                $string = $data_all['newPassword'];
                $pass_string = md5($string);
                $session_userid = $data_all['userUnique'];
                $users = DB::table('users')
                    ->select('otp_unique', 'users.name', 'users.email')
                    ->where('users.otp_unique', $session_userid)

                    ->get();

                 $res = DB::table('users')
                    ->where('otp_unique', '=', $session_userid)
                    ->update(['password' => $pass_string]);

                 $user_data = DB::select('SELECT users.id, users.name, users.email, users.social_title, users.first_name, users.last_name, users.image, users.status, users.is_verified, users.facebook_id,users.loggedin_status, users.mobile FROM users where users.password = ? AND users.otp_unique = ? AND users.user_type=3  limit 1', array(md5($password), $session_userid));
                 // print_r($user_data);exit();


                 if (count($user_data) > 0) {
                $user_data = $user_data[0];

                 if ($user_data->status == 0) {
                    $result = array("status" => 0, "message" => trans("messages.Your registration has blocked pls contact Your Admin."));
                } else {
                    // Check login type based on mobile api parameters
                    if (isset($post_data['deviceType']) && !empty($post_data['deviceType'])) {
                        //Update the device token & id for Android
                        if ($post_data['deviceType'] == 2) {
                            $res = DB::table('users')
                                ->where('id', $user_data->id)
                                ->update(['android_device_token' => $post_data['deviceToken'], 'android_device_id' => $post_data['deviceId'], 'login_type' => $post_data['deviceType']]);
                        }
                        //Update the device token & id for iOS
                        if ($post_data['deviceType'] == 3) {
                            $res = DB::table('users')
                                ->where('id', $user_data->id)
                                ->update(['ios_device_token' => $post_data['deviceToken'], 'ios_device_id' => $post_data['deviceId'], 'login_type' => $post_data['deviceType']]);
                        }
                    }

                    if ($user_data->loggedin_status == 0) {
                        $res = DB::table('users')
                                ->where('id', $user_data->id)
                                ->update(['loggedin_status' => 1]);
                    }



                // print_r($user_data);exit();


                $token = JWTAuth::fromUser($user_data, array('exp' => 200000000000));



               
                //->save();

                $userAddress = DB::table('user_address')
                        ->select('user_address.address', 'user_address.latitude', 'user_address.longitude', 'user_address.address_type', 'user_address.user_id', 'user_address.id as address_id','user_address.land_mark','user_address.home_no','user_address.created_date','user_address.modified_date','user_address.active_status','user_address.type_text','user_address.id'/*,'address_infos.type_text as type_text'*/)
                   
                        ->where('user_id', '=', $user_data->id)

                        ->orderBy('user_address.id', 'desc')
                        ->get();

                $orders_data = DB::table('orders')
                        ->join('users', 'users.id', '=', 'orders.customer_id')
                        ->join('outlet_infos', 'outlet_infos.id', '=', 'orders.outlet_id')
                        ->select('orders.id as orderId', 'orders.vendor_id as vendorId', 'orders.customer_id as customerId', 'orders.outlet_id as outletId', 'orders.driver_ids as driverId', 'orders.total_amount as totalAmount', 'orders.order_status as orderStatus', 'outlet_infos.outlet_name as outletName ORDER BY orders.id')
                        ->where('users.id', $user_data->id)
                         ->where('orders.order_status', '<>', '12')
                        
                        ->orderByRaw('orders.id DESC')
                        ->get();

                if(count($orders_data)>0){
                    $datas=array();
                    foreach ($orders_data as $key => $value) {
                       $datas[$key]['orderId']=$value->orderId;
                       $datas[$key]['vendorId']=$value->vendorId;
                       $datas[$key]['customerId']=$value->customerId;
                       $datas[$key]['totalAmount']=$value->totalAmount;
                       $datas[$key]['orderStatus']=$value->orderStatus;
                       $datas[$key]['outletName']=$value->outletName;
                       $datas[$key]['driverId']=isset($value->driverId) ? $value->driverId : '';
                    }


                }

                     $data=array();
                     $getCode=DB::table('users')
                            ->select('users.country_code','users.mobile')
                            ->where('users.mobile',$user_data->mobile)
                            ->get();

                    if(count($getCode)>0){      
                    foreach ($getCode as $key => $value) {
                               $data[$key]['countryCode']= $value->country_code;
                               $data[$key]['mobile']= $value->mobile;
                            }  
                    }
                            

                  if ($users['otp_unique'] = $rules['userUnique']) {

                    $result = array("status" => 1, "message" => trans("messages.Your Password Changed Successfully ,Please login again."), "detail" => array("userId" => $user_data->id, "token" => $token, "phoneNumber" => $value->mobile, "countryCode" => $value->country_code, "userOtp" => "", "isNewUser" => 0, "userName" => $user_data->name, "userEmail" => $user_data->email,"userAddress"=>$userAddress,"userOrders"=>$orders_data));

               
                    }


                }
            }   
        }
    }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }





    public function mchangePassword(Request $data)
    {
        $data_all = $data->all();
        // if ($data_all['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }

        $rules = [
            'userId' => ['required'],
            'token' => ['required'],
            'oldPassword' => ['required'],
            'password' => ['required', 'confirmed'],
            'password_confirmation' => ['required'],
        ];
        $errors = $result = array();

        $validator = app('validator')->make($data->all(), $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result =  array("status" => 0, "message" => $errors, "Error" => trans("messages.Error List"));
        } else {
            $errors = '';
            $password = $data_all['password'];
            if (!preg_match('/^[A-Za-z0-9]{6,}$/', $password)) {
                $errors .= $validator->errors()->add('password', 'The password Minimum 6 characters.');
            }
            if ($errors != '') {
                $result = array("status" => 0, "message" => trans("messages.The password Minimum 6 characters."));
            } else {

                //$check_auth = JWTAuth::toUser($data_all['token']);
                //Get new password details from posts

                $old_password = $data_all['oldPassword'];
                $string = $data_all['password'];
                $pass_string = md5($string);
                $session_userid = $data_all['userId'];
                $users = DB::table('users')
                    ->select('users.id', 'users.name', 'users.email')
                    ->where('users.id', $session_userid)
                    ->where('users.password', md5($old_password))
                    ->where('users.status', 1)
                    ->get();
                if (count($users) > 0) {
                    $users_data = $users[0];
                    //Sending the mail to student
                    $template = DB::table('email_templates')
                        ->select('from_email', 'from', 'subject', 'template_id', 'content')
                        ->where('template_id', '=', self::USER_CHANGE_PASSWORD_EMAIL_TEMPLATE)
                        ->get();
                    if (count($template)) {
                        $from = $template[0]->from_email;
                        $from_name = $template[0]->from;
                        $subject = $template[0]->subject;
                        if (!$template[0]->template_id) {
                            $template = 'mail_template';
                            $from = getAppConfigEmail()->contact_email;
                            $subject = getAppConfig()->site_name . " New Password Request Updated";
                            $from_name = "";
                        }
                        $content = array("name" => $users_data->name, "email" => $users_data->email, "password" => $string);
                        $email = smtp($from, $from_name, $users_data->email, $subject, $content, $template);
                    }
                    //Update random password to vendors table to coreesponding vendor id
                    $res = DB::table('users')
                        ->where('id', $users_data->id)
                        ->update(['password' => $pass_string]);
                    //After updating new password details logout the session and redirects to login page
                    $result =  array("status" => 1, "message" => trans("messages.Your Password Changed Successfully"));
                } else {
                    $result = array( "status" => 2, "message" => trans("messages.Old password is incorrect"));
                }
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /*athira - forgot paswd for user mob*/


    

    public function mforgotPassword(Request $data)
    {

        $rules = array(
            'language' => 'required',
            'phoneNumber' => 'required',
            'countryCode' => 'required',
        );
        $post_data = $data->all();
        // if (isset($post_data['language']) && $post_data['language'] == 2) {
        //     App::setLocale('ar');
        // } else {
        //     App::setLocale('en');
        // }
         if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            $post_data = $data->all();
            $phone = $post_data['phoneNumber'];
            $countryCode = $post_data['countryCode'];
            $phoneNumber = $countryCode . $phone;
            $user_details = DB::table('users')
                ->select('id')
                ->where('mobile', '=', $phoneNumber)
                ->first();
            //  print_r($user_details); exit;
            $result = array("status" => 0, "message" => trans("messages.Mobile Number is not register in Broz app"));
            if (count($user_details) > 0) {
                //print_r("expression");exit();
                $users = Users::find($user_details->id);
                $string = str_random(8);
                $pass_string = md5($string);
                $app_config = getAppConfig();
                $number = str_replace('-', '', $users->mobile); //to remove the '-'
                $message = 'You have received your new password for ' . getAppConfig()->site_name . '. Your New Password is ' . $string . '.';
                $twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
                $twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
                $from_number = "+14783471785";
                //$client = new Services_Twilio($twilo_sid, $twilio_token);
                $client = new Client($twilo_sid, $twilio_token);
                //print_r ($client);exit;
                // Create an authenticated client for the Twilio API
                try {
                   /* $m = $client->account->messages->sendMessage(
                        $from_number, // the text will be sent from your Twilio number
                        $number, // the phone number the text will be sent to
                        $message // the body of the text message
                    );*/
                    $m =  $client->messages->create($number, array('from' => $from_number, 'body' => $message));

                    $users->password = $pass_string;
                    $users->updated_date = date("Y-m-d H:i:s");
                    $users->save();
                    $token = JWTAuth::fromUser($users, array('exp' => 200000000000));


                    $data=array();
                     $getCode=DB::table('users')
                            ->select('users.country_code','users.mobile')
                            ->where('users.mobile',$phoneNumber)
                            ->get();
                
                        if(count($getCode)>0){      
                        foreach ($getCode as $key => $value) {
                                   $data[$key]['countryCode']= $value->country_code;
                                   $data[$key]['mobile']= $value->mobile;
                                }  
                        }

                    $result = array("status" => 1, "message" => trans("messages.New Password has been sent to your register mobile number."), "detail" => array("countryCode" => $value->country_code, "phoneNumber" => $value->mobile, "userPassword" => $string));
                } catch (Exception $e) {
                    $result = array("status" => 0, "message" => $e->getMessage());
                    return json_encode($result);
                } catch (\Services_Twilio_RestException $e) {
                    $result = array("status" => 0, "message" => $e->getMessage());
                    return json_encode($result);
                }
            }
        }
        return json_encode($result);
    }
    public function msignup_new(Request $data)
    {
        $post_data = $data->all();
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $rules = [
            'userName' => ['required', 'max:56'],
            // 'last_name' => ['required','alpha', 'max:56'],
            'userEmail' => ['required', 'email', 'max:250', 'unique:users,email'],
            'password' => ['required'],
            //'phone'      => ['required', 'max:50','regex:^(\+\d{1,2}\s)?\(?\d{3}\)?[\s.-]?\d{3}[\s.-]?\d{4}$'],
            // 'phone'        => ['required', 'max:15', 'regex:/(^[0-9 +]+$)+/', 'unique:users,mobile'],
            'phoneNumber' => ['required'],
            'countryCode' => ['required'],
            'gender' => ['required'],
            //'termsCondition' => ['required'],
            'deviceType' => ['required'],
            'deviceId' => ['required_unless:login_type,1,2,3'],
            'deviceToken' => ['required_unless:login_type,1,2,3'],
            'image' => ['mimes:png,jpeg,bmp', 'max:2024'],
        ];

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 0, "message" => $errors, "detail" => new \stdClass());
        } else {
            $errors = '';
            $password = $post_data['password'];
            if (strlen($password) < 6) {
                $errors .= $validator->errors()->add('password', 'Password must contain at least 6 characters, one Uppercase letter and one number');
            }
            if ($errors != '') {
                $result = array("status" => 0, "message" => trans("messages.The password Minimum 6 characters at least 1 Uppercase Alphabet, 1 Lowercase Alphabet and 1 Number."), "detail" => array());
            } else {
               // $phoneNumber = $post_data['countryCode'] . $post_data['phoneNumber'];
                $phoneNumber =  $post_data['phoneNumber'];
                $countryCode = $post_data['countryCode'] ;
                $user_data = DB::select('SELECT users.id, users.name, users.email, users.status, users.mobile, users.wallet_amount FROM users where users.mobile = ? AND users.user_type = 3 AND users.is_verified = 1  limit 1', array($phoneNumber));

                if (count($user_data) == 0) {
                    $result = array("status" => 2, "message" => trans("messages.Please register your phone number.")/*, "detail" => array("countryCode" => $post_data['countryCode'], "phoneNumber" => $post_data['phoneNumber'])*/);
                } else {
                    $id = $user_data[0]->id;
                    $wallet_amount =0;
                    /*check referral*/
                    if($post_data['referral'] != "")
                    {
                        $referral =getreferral();
                        $users_details=DB::table('users')
                            ->select('id','referral_code','android_device_token','ios_device_token','login_type')
                            ->where('users.referral_code',$post_data['referral'])
                            ->get();
                       // print_r($users_details);exit;
                        if($users_details){
                            $values = array(
                                 'customer_id' => $id,               
                                 'referral_code' => $post_data['referral'],
                                 'referral_amount' => $referral[0]->referral_amount,
                                 'referred_by' => $users_details[0]->id,
                                 'referal_amount_used' => 0,
                                 'created_date' => date('Y-m-d H:i:s'));
                                DB::table('customer_referral')->insert($values);
                            $wallet_amount = $referral[0]->referral_amount;
                            /*push notification*/


                            if($users_details[0]->login_type == 2){
                                $token = $users_details[0]->android_device_token;
                            }else if($users_details[0]->login_type == 3)
                            {
                                $token = $users_details[0]->ios_device_token;
                            }
                            $token =isset($token)?$token:'';
                            //$token =isset($token)?$token:'';
                            // print_r($token);exit();
                            //$token = "fMZKiUHw0UM:APA91bG8EN9VI8laR6crolH3fSY2L5oxm6qqUXNhQaiTLFUW5SuCTiklZvzOdgC0qGHhUozXunwnJecVLbxEWpzpfzRLykI61ICAdzgz0tAZKooqa2ZEWgj5b99z5oPxRJwuCY0kpTmy";
                            $order_title = "Your friend ".$post_data['userName']. " has successfully Signedup with your refferral code";
                            $description ="Your friend ".$post_data['userName']. " has successfully Signedup with your refferral code";
                            $data = array
                                (
                                'status' => 1,
                                'message' => $order_title,
                                'detail' =>array(
                                'description'=>$description,    
                                'type' => 2,
                                'title' => $order_title,
                                'request_type' => 1,
                                'notification_dialog' => "1",
                            ));
                            $fields = array
                                (
                                'registration_ids' => array($token),
                                'data' => array('title' => $order_title, 'body' =>  $data ,'sound'=>'Default','image'=>'Notification Image'),
                                'notification' => array('title' => $order_title, 'body' =>  $description,'sound'=>'Default','image'=>'Notification Image'),
                                'content_available' => true,
                                'priority'=>'high',
                                'apns' => array('headers' => array('apns-priority' => '10'))
                                );
                            $headers = array
                                (
                                'Authorization: key='.FCM_SERVER_KEY,
                               
                                'Content-Type: application/json'
                                );

                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                            $result = curl_exec($ch);
                            //print_r($result);exit;
                            curl_close($ch);
                            /*push notification*/


                        }else{
                            $result = array("status" => 2, "message" => trans("messages.Invalid referral code."));

                            return json_encode($result, JSON_UNESCAPED_UNICODE);
                            }

                        //referral_code
                    }

                    /*check referral*/
                    $users = Users::find($id);
                   // $usertoken = sha1(uniqid(Text::random('alnum', 32), true));
                     $usertoken = sha1(uniqid(random_bytes(24),true));

                    if (!$users->user_token) {
                        $users->user_token = $usertoken;
                    }
                    $password = $post_data['password'];
                    $users->name = $post_data['userName'];
                    //$users->last_name = isset($post_data['lastName']) ? $post_data['lastName'] : "";
                    //$users->name = $post_data['firstName'] . " " . $post_data['lastName'];
                    $users->email = strtolower($post_data['userEmail']);
                    $users->password = md5($post_data['password']);
                    // $users->mobile       = preg_replace("/[^+0-9]+/", "", $post_data['phone']);
                    $users->gender = $post_data['gender'];
                    $users->social_title = ($post_data['gender'] == 'F') ? "Ms." : "Mr.";
                    //$users->civil_id    = $post_data['civil_id'];
                    //$users->cooperative = $post_data['cooperative'];
                    //$users->member_id   = isset($post_data['member_id'])?$post_data['member_id']:'';
                    $users->user_type = 3;
                    $users->is_verified = 1;
                    $users->ip_address = $_SERVER['REMOTE_ADDR'];
                    $users->created_date = date("Y-m-d H:i:s");
                    $users->updated_date = date("Y-m-d H:i:s");
                    $users->user_created_by = 3;
                    $users->login_type = 1;
                    $users->wallet_amount = $wallet_amount;

                    //Check if the login type from mobile app update the device details here
                    if (isset($post_data['deviceType']) && !empty($post_data['deviceType'])) {
                        //Store Android Device details
                        if ($post_data['deviceType'] == 2) {
                            $users->login_type = 2;
                            $users->android_device_id = isset($post_data['deviceId']) ? $post_data['deviceId'] : '';
                            $users->android_device_token = isset($post_data['deviceToken']) ? $post_data['deviceToken'] : '';
                        }
                        //Store iOS Device details
                        if ($post_data['deviceType'] == 3) {
                            $users->login_type = 3;
                            $users->ios_device_id = isset($post_data['deviceId']) ? $post_data['deviceId'] : '';
                            $users->ios_device_token = isset($post_data['deviceToken']) ? $post_data['deviceToken'] : '';
                        }
                    }
                    //print_r($users);exit;

                   /* $verification_key = Text::random('alnum', 12);
                    $refferral_key = Text::random('alnum', 6);*/
                    $verification_key = str_random(12);
                    $refferral_key = str_random(6);

                    $users->verification_key = $verification_key;
                    $users->referral_code = $refferral_key;
                    $users->save();
                    if (isset($post_data['image']) && $post_data['image'] != '') {
                        $destinationPath = base_path() . '/public/assets/admin/base/images/admin/profile/'; // upload path
                        $imageName = $users->id . '.' . $post_data['image']->getClientOriginalExtension();
                        $data->file('image')->move($destinationPath, $imageName);
                        $destinationPath1 = url('/assets/admin/base/images/admin/profile/' . $imageName);
                        Image::make($destinationPath1)->fit(75, 75)->save(base_path() . '/public/assets/admin/base/images/admin/profile/thumb/' . $imageName);
                        Image::make($destinationPath1)->fit(260, 170)->save(base_path() . '/public/assets/admin/base/images/admin/profile/' . $imageName);

                        $users->image = $imageName;
                        $users->save();
                    }
                    $imageName = url('/assets/admin/base/images/default_avatar_male.jpg');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/admin/profile/' . $users->image) && $users->image != '') {
                        $imageName = URL::to("assets/admin/base/images/admin/profile/" . $users->image);
                    }
                    $token = JWTAuth::fromUser($users, array('exp' => 200000000000));

                     $data=array();
                     $getCode=DB::table('users')
                            ->select('users.country_code','users.mobile')
                            ->where('users.mobile',$phoneNumber)
                            ->get();
                
                        if(count($getCode)>0){      
                        foreach ($getCode as $key => $value) {
                                   $data[$key]['countryCode']= $value->country_code;
                                   $data[$key]['mobile']= $value->mobile;
                                }  
                        }

                        Session::put('user_id', $users->id);
                        Session::put('mobile', $value->mobile);
                        Session::put('email', $users->email);
                        Session::put('first_name', $users->name);
                        Session::put('last_name', $users->name);
                        Session::put('name', $users->name);
                        Session::put('social_title', "");
                        Session::put('profile_image', "");
                        Session::put('token', $token);


                    $result = array("status" => "1", "message" => trans("messages.Success,Thank you for signup with us, Welcome to Broz!"), "detail" => array("userId" => $users->id, "token" => $token, "countryCode" => $value->country_code, "phoneNumber" => $value->mobile, "userType" => $users->user_type, "guest" => isset($users->guest_type) ? $users->guest_type : "0", "userEmail" => isset($users->email) ? $users->email : "", "userName" => isset($users->name) ? $users->name : "", "facebookId" => $users->facebook_id, "phoneVerify" => 1,"referral_code"=>$refferral_key));


                    $subject = 'Success,Thank you for signup with us, Welcome to Broz!';
                    $values = array(
                         'customer_id' => $users->id,               
                         'message' => $subject,
                         'read_status' => 0,
                         'created_date' => date('Y-m-d H:i:s'));
                        DB::table('notifications')->insert($values);


                    $to = $users->email;
                   /* $template = DB::table('email_templates')->select('*')->where('template_id', '=', self::USER_SIGNUP_EMAIL_TEMPLATE)->get();
                    if (count($template)) {
                        $from = $template[0]->from_email;
                        $from_name = $template[0]->from;
                        if (!$template[0]->template_id) {
                             $template = 'mail_template';
                             $from = getAppConfigEmail()->contact_mail;
                        }
                        $subject = 'Welcome to Broz!';
                        $url1 = '<a href="' . url('/')  . '"> This  Link </a>';
                        $content = array('name' => "" . $users->name,'email' => "" . $users->email,'password' => "" . $password,"confirmation_link" => $url1);
                        $attachment = "";
                        $email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
                    }*/

                    $template = DB::table('email_templates')->select('*')->where('template_id', '=', self::USER_SIGNUP_EMAIL_TEMPLATE)->get();
                    if (count($template)) {
                        $from = $template[0]->from_email;
                        $from_name = $template[0]->from;
                        if (!$template[0]->template_id) {
                            $template = 'mail_template';
                            $from = getAppConfigEmail()->contact_mail;
                        }
                        $subject = 'Welcome to Broz!';
                        $logo_image = url('/assets/admin/email_temp/images/1570903488.jpg');
                        $image1 = url('/assets/admin/email_temp/images/1571073006.jpg');
                        $image2 = url('/assets/admin/email_temp/images/1571052316.jpg');
                        $image3 = url('/assets/admin/email_temp/images/1571074251.jpg');
                        $name = $users->name;
                        $phoneno = $users->mobile;
                    
                        $content = array("logo_image"=>$logo_image,"image1"=>$image1,"image2"=>$image2,"image3"=>$image3,"name"=>$name,"phoneno"=>$phoneno);
                        $attachment = "";
                        $email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
                        //print_r($email);exit;
                    }
                   //print_r($email);exit();


                }
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

      public function morders(Request $data)
    {
        $post_data = $data->all();

        $language_id = 1;
        if ($post_data['language'] == 'ar') {
            $language_id = 2;
        }

        //  if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
        //     App::setLocale($post_data['language']);
        // } else {
        //     App::setLocale('en');
        // }
       // $language_id = $post_data['language'];

        $result =  array("status" => 0, "message" => trans("messages.No stores found"), "detail" => array());
        $query = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $query1 = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.language_id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $query2 = '"order_status"."lang_id" = (case when (select count(*) as totalcount from order_status where order_status.lang_id = ' . $language_id . ' and orders.order_status = order_status.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
        $order_list = DB::table('orders')
            ->select('vendors_infos.vendor_name', 'orders.total_amount', 'order_status.name as status_name', 'order_status.id as status_id', 'orders.id', 'orders.created_date', 'order_status.color_code', 'orders.order_key_formated', 'vendors.logo_image', 'orders.delivery_date', 'delivery_time_interval.start_time', 'delivery_time_interval.end_time', 'delivery_charge', 'orders.invoice_id', 'orders.order_type')
            ->leftJoin('outlets', 'outlets.id', '=', 'orders.outlet_id')
            ->leftJoin('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
            ->leftJoin('vendors', 'vendors.id', '=', 'orders.vendor_id')
            ->leftJoin('vendors_infos', 'vendors_infos.id', '=', 'orders.vendor_id')
            ->leftJoin('order_status', 'order_status.id', '=', 'orders.order_status')
            ->leftJoin('delivery_time_slots', 'delivery_time_slots.id', '=', 'orders.delivery_slot')
            ->leftJoin('delivery_time_interval', 'delivery_time_interval.id', '=', 'delivery_time_slots.time_interval_id')
            ->where('orders.customer_id', '=', $post_data['userId'])
            ->whereRaw($query)
            ->whereRaw($query1)
            ->whereRaw($query2)
            ->orderBy('orders.id', 'desc')
            ->get();
        $orders = array();
        if (count($order_list) > 0) {
            $o = 0;
            foreach ($order_list as $ord) {
                $orders[$o]['id'] = $ord->id;
                $orders[$o]['encrypt_id'] = Crypt::encrypt($ord->id);
                $orders[$o]['outletName'] = $ord->vendor_name;
                $orders[$o]['total'] = $ord->total_amount;
                $orders[$o]['statusName'] = $ord->status_name;
                $orders[$o]['statusId'] = $ord->status_id;
                $orders[$o]['placedOn'] = $ord->created_date;
                $orders[$o]['colorCode'] = $ord->color_code;
                $orders[$o]['deliveryOn'] = $ord->delivery_date;
                $orders[$o]['orderType'] = $ord->order_type;
                $orders[$o]['startTime'] = ($ord->start_time != '') ? $ord->start_time : '';
                $orders[$o]['endTime'] = ($ord->end_time != '') ? $ord->end_time : '';
                $orders[$o]['deliveryCharge'] = $ord->delivery_charge;
                $orders[$o]['orderKeyFormated'] = $ord->order_key_formated;
                $orders[$o]['invoiceId'] = $ord->invoice_id;
                $orders[$o]['itemTotal'] = $ord->total_amount - $ord->delivery_charge;
                $logo_image = URL::asset('assets/admin/base/images/no_image.png');

                if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $ord->logo_image) && $ord->logo_image != '') {
                    $logo_image = url('/assets/admin/base/images/vendors/logos/' . $ord->logo_image);
                }
                $orders[$o]['vendorLogo'] = $logo_image;
                $o++;
            }
        }
        $result = array("status" => 200, "message" => "Success", 'orderDataList' => $orders);
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }



    public function coupon_list(Request $data)
    {
        $post_data = $data->all();
        $data = array();

        $rules = [

        ];
        $date = "coupons.start_date";
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            //   $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("httpCode" => 400, "status" => 0, "Message" => $errors);
        } else {
            $result = array("httpCode" => 400, "status" => 2, "message" => trans("messages.No coupon details found."), "data" => $data);

            $show = DB::table('coupons')
                ->join('coupons_infos', 'coupons_infos.id', '=', 'coupons.id')
                ->select('coupons.id', 'coupons.coupon_code as couponCode ', 'coupons.offer_type as offerType', 'coupons.coupon_value as couponValue', 'coupons.user_limit as userLimit', 'coupons.coupon_limit as couponLimit', 'coupons.coupon_type as couponType', 'coupons.products', 'coupons.coupon_image as couponImage', 'coupons.offer_amount as offerAmount', 'coupons.offer_percentage as offerPercentage', 'coupons.category_id as categoryId', 'coupons.start_date as startDate', 'coupons.end_date as endDate', 'coupons.created_by as createdBy', 'coupons.created_date as createdDate', 'coupons.modified_date as modifiedDate', 'coupons.active_status as activeStatus', 'coupons.coupon_status as couponStatus', 'coupons.vendor', 'coupons.minimum_order_amount as minimumOrderAmount', 'coupons_infos.coupon_title', 'coupons_infos.coupon_info')

            //$show = DB::table('coupons')

                ->where('end_date', '>=', DATE('Y-m-d'))
                ->orderBy('id', 'asc')
            //->limit(5)
                ->get();

            if (count($show)) {
                $coupons_list = array();
                $s = 0;
                foreach ($show as $st) {
                    $logo_image = URL::asset('/assets/admin/base/images/coupon/offers.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/coupon/' . $st->couponImage) && $st->couponImage != '') {
                        $logo_image = url('/assets/admin/base/images/coupon/' . $st->couponImage);
                    }
                    $coupons_list[$s]['id'] = $st->id;
                    $coupons_list[$s]['couponTitle'] = $st->coupon_title;
                    $coupons_list[$s]['couponCode'] = $st->couponCode;
                    $coupons_list[$s]['startDate'] = $st->startDate;
                    $coupons_list[$s]['endDate'] = $st->endDate;
                    $coupons_list[$s]['createdDate'] = $st->createdDate;
                    $coupons_list[$s]['activeStatus'] = $st->activeStatus;
                    $coupons_list[$s]['couponImage'] = $logo_image;
                    $coupons_list[$s]['couponDescription'] = $st->coupon_info;

                    $coupons_list[$s]['coupon details'] = [
                        "Offer not valid on all debit & credit cards",
                        "Other T&Cs may apply",
                        "Offer valid till " . date('Y-m-d', strtotime($st->endDate)),
                    ];
                    $s++;
                }

                //$endDate=$coupons_list[$s]['endDate'] = $st->endDate;

                //$result = array("httpCode" => 200, "status" => 1, "message" => trans("coupon details"), "status" => true, 'data' => $show);

                //	$couponDetail="{\"Offer not valid on all debit & credit cards\",\"Other T&Cs may apply\", \"Offer valid till\"$endDate}";

                $result = array("status" => 1, "httpCode" => 200, 'message' => trans('messages.Coupon list'), 'data' => $coupons_list);
            }
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function getcore(Request $data)
    {
        $data_all = $data->all();

        /*	$rules = [
                'deviceId' => 'required',
                //'deviceType' => 'required',
            ];
            $errors = $result = array();
            $validator = app('validator')->make($data->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    $errors[] = is_array($value) ? implode(',', $value) : $value;
                }
                $errors = implode(", \n ", $errors);
                $result = array("response" => array("httpCode" => 400, "status" => false, "Message" => $errors, "Error" => trans("messages.Error List")));
            } else {*/
        $id=1;
        $customer_cores = customer_cores::find($id);
        $deviceType =isset($data_all['deviceType'])?$data_all['deviceType']:'';
        $static = array();

        if ($deviceType == 1) {
            $static['iosupdateType'] =  $customer_cores['iosupdate_type'];
            $static['iosupdateMessage'] =  $customer_cores['iosupdate_message'];
            $static['iosforceMessage'] =  $customer_cores['iosupdate_message'];
            $static['iosforceUpdateVersion'] =  $customer_cores['iosforceupdate_version'];
            $static['ioslatestVersion'] =  $customer_cores['ioslatest_version'];
            $static['iosforceVersion'] =  $customer_cores['iosforceupdate_version'];
        } else {
            $static['updateType'] =  $customer_cores['update_type'];
            $static['updateMessage'] =  $customer_cores['update_message'];
            $static['forceMessage'] =  $customer_cores['update_message'];
            $static['forceUpdateVersion'] =  $customer_cores['forceupdate_version'];
            $static['latestVersion'] =  $customer_cores['latest_version'];
            $static['forceVersion'] =  $customer_cores['forceupdate_version'];
        }
        $static['appName'] = $customer_cores['app_name'];
        $static['appLogo'] =  "https://randomuser.me/api/portraits/men/82.jpg";
        $static['loginLogo'] =  "https://randomuser.me/api/portraits/men/82.jpg";
        $static['countryCode'] =  $customer_cores['country_code'];
        $static['aboutUs'] =  BASE_URL.'/driverabout-us';
        $static['terms'] =  BASE_URL.'/driverterms-condition';
        $static['androidKey'] =  $customer_cores['android_key'];
        //$static['latestVersion'] =  $customer_cores['latest_version'];
        //$static['forceVersion'] =  $customer_cores['forceupdate_version'];
        //$static['updateType'] =  $customer_cores['update_type'];
        //$static['updateMessage'] =  $customer_cores['update_message'];
        //$static['forceMessage'] =  $customer_cores['update_message'];
        $static['noImageUrl'] =  $customer_cores['no_imageurl'];
        $static['errorReportCase'] =  $customer_cores['error_reportcase'];
        $static['socket_url'] =  $customer_cores['socket_url'];
        $static['status'] = 1;
        $static['message'] = "success";
        $static['referalCode'] = "34ERD22";
        $static['referalDesc'] = "Refer your friends and earn upto AED 30 cashback. After their first purchase, you and your friend each get AED 30 cashback";
        $static['contact_no'] = "+91-8667730776";
        $static['currentVersion'] = "12";
         $static['currencySymbol'] = "AED";
        $result =json_encode($static);
        // $a = "{
        // \"message\": \"Success\",
        //    \"status\": 1,
        //    \"referalCode\": \"34ERD22\",
        //    \"referalDesc\": \"Refer your friends and earn upto AED 30 cashback. After their first purchase, you and your friend each get AED 30 cashback\",
        //    \"contact_no\": \"+91-8667730776\",
        //      \"currentVersion\": \"12\",
        //        \"forceVersion\": \"2\",

        // 		}";
        //print_r($a);exit;

        //}



        return $result;
    }

    //need to delete
    public function getGroceryDetail(Request $request)
    {
        $a = " [{
		    \"title\": \"Coca cola\",
		    \"description\": \"The Coca-Cola Company's distribution strategy.... While it is
		     generally perceived that Coca-Cola runs all its operations globally it, this
		     process it done through various local channels\",
		    \"orginalPrice\": \"200\",
		    \"dicountPrice\": \"80\",
		    \"offer\": \"70\",
		    \"unit\": \"2L\",
		    \"image\": [\"https://images-na.ssl-images-amazon.com/images/I/5156FefjlqL._SX425_.jpg\",
		            \"https://target.scene7.com/is/image/Target/GUEST_e8455efe-9e6f-434f-86b8-c73e8549035c?wid=488&hei=488&fmt=pjpeg\",
		            \"https://www.rd.com/wp-content/uploads/2018/02/The-Real-Reason-the-Coca-Cola-Logo-is-Red_570538087_tok-anas.jpg\"]
		}]";

        return $a;
    }

    /*public function feedback(Request $data)
    {
        $rules = [
            'id' => ['required'],
            'userName' => ['required'],
            'description' => ['required'],
            'mailId' => ['required'],
            'phone' => ['required'],
            'deviceId' => ['required'],
            'deviceModel' => ['required'],
            'appVersion' => ['required'],
            //'dateTime' => ['required'],

        ];

        $post_data = $data->all();

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            // $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 400, "status" => 0, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            $errors = '';

            $phone = $post_data['phone'];

            if (!preg_match('/^((\+){0,1}91(\s){0,1}(\-){0,1}(\s){0,1})?([0-9]{10})$/', $phone)) {
                $result = array("httpCode" => 400, "status" => 2, "message" => trans("messages.Please enter  valid phone number!"));
            } else {
                $students = new Student();

                $students->id = $post_data['id'];
                $students->userName = $post_data['userName'];
                $students->description = $post_data['description'];
                $students->mailId = ($post_data['mailId']);
                $students->phone = $post_data['phone'];
                $students->deviceId = $post_data['deviceId'];
                $students->appVersion = $post_data['appVersion'];
                $students->deviceModel = $post_data['deviceModel'];
                //$students->dateTime = date("Y-m-d H:i:s");

                $students->save();

                $result = array( "status" => 1, "message" => trans("created successfully"), "detail" => $students);
            }
        }

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }*/
    public function feedback(Request $data)
    {
        $rules = [
            'userId' => ['required'],
            'userName' => ['required'],
            'feedback' => ['required'],
            'email' => ['required'],
            'phone' => ['required'],

        ];

        $post_data = $data->all();

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            // $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("response" => array("httpCode" => 400, "status" => 0, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            $errors = '';

            $phone = $post_data['phone'];
            if (!preg_match('/^((\+){0,1}91(\s){0,1}(\-){0,1}(\s){0,1})?([0-9]{10})$/', $phone)) {
                $result = array("status" => 2, "message" => trans("messages.Please enter  valid phone number!"));
            } else {
                $users = Users::find($post_data['userId']);
                if($users){
                    $feedback = new User_feedback;
                    $feedback->user_id = $post_data['userId'];
                    $feedback->user_name = $post_data['userName'];
                    $feedback->mobile   = $post_data['phone'];
                    $feedback->email     = ($post_data['email']);
                    $feedback->feedback  = $post_data['feedback'];
                    $feedback->created_date = date("Y-m-d H:i:s");
                    $feedback->save();
                    $result = array( "status" => 1, "message" => trans("created successfully"), "detail" => $feedback);
                }else{
                    $result = array( "status" => 2, "message" => trans("invalid user"));
  
                }
            }
        }

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }


    public function cms_infos(Request $data)
    {
        $res = array();

        $show = DB::select('select info_id ,content from cms_infos');

        if ($show) {
            for ($i = 0; $i < count($show); $i++) {
                $res[$i]["Ques"] = ($show[$i]->content);
                $res[$i]["id"] = $show[$i]->info_id;
            }

            $result = array("httpcode" => 200, "status" => 1, "data" => $res);
            //$str = str_replace(PHP_EOL, '\n', $result);
            return json_encode($result);

            // wordwrap($str,15,"<br>\n");

            //print_r($res);
        }
    }

    public function form(request $request)
    {
        if ($request) {
            if (!isset($request['submit'])) {

                //$id = $request['box'];
                $name = $request['name'];
                $value = $request['value'];
                $type = $request['type'];

                if (empty($name)) {
                    return "Please fill in this field";
                }
                if (empty($value)) {
                    return "Please fill in this field";
                }

                if (empty($type)) {
                    return "Please fill in this field";
                } else {
                    $result = DB::insert('insert into faq(question,answer,type,created_date,updated_date
) values(?,?,?,?,?)', [$name, $value, $type, date("Y-m-d H:i:s"), date("Y-m-d H:i:s")])
                        ->save();

                    return "data created successfully";
                }
            }
        }
    }

    public function formUpdate(request $request)
    {
        if ($request) {
            if (!isset(($request['submit']))) {
                $id = $request['box'];
                $name = $request['name'];
                $value = $request['value'];
                $type = $request['type'];

                $result = DB::table("faq")
                    ->where("id", $id)
                    ->update(['question' => $name, 'answer' => $value, 'type' => $type, 'updated_date' => date("Y-m-d H:i:s")])
                    ->save();
                if ($result) {
                    echo " " . "data saved successfully";
                }
            }
        }
    }

    // 	public function formDelete(request $request) {

    // $data = DB::table('faq')
    // 	->select('*')
    // 	->where('id', "$_SERVER[QUERY_STRING]")
    // 	->delete();
    // }

    public function faq_details(Request $data)
    {
        $post_data = $data->all();
        $data = array();

        $rules = [

        ];

        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            //   $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("httpCode" => 400, "status" => 0, "Message" => $errors);
        } else {
            $result = array("httpCode" => 400, "status" => 2, "message" => trans("messages.No faq details found."), "data" => $data);

            $show = DB::table('faq')
                ->select('faq.id', 'faq.question', 'faq.answer', 'faq.type')
                ->orderBy('id', 'asc')
                ->get();

            $result = array("status" => 1, "httpCode" => 200, "message" => trans("faq details"), 'data' => $show);
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function page()
    {
        $data = Faq::paginate(5);
        return view('faq.index', compact('data'));
    }

    
    public function get_profile(Request $data)
    {
        $post_data = $data->all();
        $rules = [
            'id' => ['required', 'integer'],
            //'driverId' => ['required', 'integer'],
            //'token' => ['required'],
        ];
        //$error = $result = array();
        //print_r($post_data);exit;
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            $post_data['userId'] =$post_data['id'];
    

            try {
                //$check_auth = JWTAuth::toUser($post_data['token']);
                $user_data = DB::table('users')
                    ->select('users.id', 'name as userName', 'email as userEmail', 'mobile as phoneNumber', 'country_code as countryCode', 'gender', 'users.image as imageUrl')
                    ->where('users.id', $post_data['id'])
                    //->where('users.active_status', 'A')
                    ->first();
                // print_r(empty($user_data));exit();
                if (!empty($user_data)) {
                    $user_data->userName = ($user_data->userName != '') ? $user_data->userName : '';
                    // $user_data->lastName = ($user_data->lastName != '') ? $user_data->lastName : '';
                    $imageName = url('/assets/admin/base/images/default_avatar_male.jpg');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/admin/profile/' . $user_data->imageUrl) && $user_data->imageUrl != '') {
                        $imageName = URL::to("/assets/admin/base/images/admin/profile/" . $user_data->imageUrl . '?' . time());
                    }
  $user_data->userId = $user_data->id;

                    $user_data->imageUrl = $imageName;

                    if($user_data->userName == ""){

                        $result = array("status" => 2,  "message" => trans("messages.user details"), 'detail' => $user_data);
                    }
                    if($user_data->userName !== ""){
                    
                    $result = array("status" => 1,  "message" => trans("messages.user details"), 'detail' => $user_data);
                }
                } else {
                    $result = array("status" => -1, "message" => trans("messages.No user found"));
                }
            } catch (JWTException $e) {
                $result = array("httpCode" => 400, "message" => trans("messages.Kindly check the user credentials"));
            } catch (TokenExpiredException $e) {
                $result = array("httpCode" => 400, "message" => trans("messages.Kindly check the user credentials"));
            }
        }
        return json_encode($result);
        exit;
    }


    
    /*public function order_shortlist_copy(Request $data) {

        $post_data = $data->all();
        $rules = [
            'userId' => ['required'],
            //'token' => ['required'],
        ];
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 0, "httpCode" => 400, "Error" => trans("messages.Error List"), "message" => $errors);
        } else {
            //$check_auth = JWTAuth::toUser($post_data['token']);
            $id = $rules;
            $orders_data = DB::table('orders')
                ->where('orders.customer_id', $post_data['userId'])
                ->where('orders.order_status','<>','12')
                ->join('outlet_infos', 'outlet_infos.id', '=', 'orders.outlet_id')

                ->select('orders.id as orderId', 'orders.vendor_id as vendorId', 'orders.customer_id as customerId', 'orders.outlet_id as outletId', 'orders.driver_ids as driverId', 'orders.total_amount as totalAmount', 'orders.order_status as orderStatus', 'outlet_infos.outlet_name as outletName')
                ->get();
            // $count  = DB::table('orders_info')
   //                  ->select('orders_info.id')
   //                  ->where("orders_info.order_id","=",$post_data['userId'])
   //                  ->count();
           // $count =isset($count)?$count:0;
//			print_r($count);exit;

            if ($orders_data) {
                //$orders_data[0]->itemCount =0;

                $result = array("status" => 1, "httpCode" => 200, "message" => trans("messages.order short list"), 'data' => $orders_data);
            } else {

                $orders_data = DB::table('orders')
                ->where('orders.customer_id', $post_data['userId'])
                ->where('orders.order_status','=','12')
                ->join('outlet_infos', 'outlet_infos.id', '=', 'orders.outlet_id')
                //->join('outlet_reviews', 'outlet_reviews.order_id', '=', 'orders.id')

                ->select('orders.id as orderId', 'orders.vendor_id as vendorId', 'orders.customer_id as customerId', 'orders.outlet_id as outletId', 'orders.driver_ids as driverId', 'orders.total_amount as totalAmount', 'orders.order_status as orderStatus', 'outlet_infos.outlet_name as outletName')
                ->get();
            if ($orders_data) {

                $result = array("status" => 2, "httpCode" => 200, "message" => trans("messages.order short list"), 'data' => $orders_data);

                }else{

                $result = array("status" => 3, "httpCode" => 400, "message" => trans("messages.No order found"));
                }



            }

        }
        return json_encode($result);exit;
    }
*/


 public function order_shortlist(Request $data)
    {
        $post_data = $data->all();
        $rules = [
            'userId' => ['required'],
            //'token' => ['required'],
        ];
        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 0,  "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            //$check_auth = JWTAuth::toUser($post_data['token']);
            $id = $rules;


            $orders_data = DB::table('orders')
                
                ->join('outlet_infos', 'outlet_infos.id', '=', 'orders.outlet_id')
                ->select('orders.id as orderId', 'orders.vendor_id as vendorId', 'orders.customer_id as customerId', 'orders.outlet_id as outletId', 'orders.driver_ids as driverId', 'orders.total_amount as totalAmount', 'orders.order_status as orderStatus', 'outlet_infos.outlet_name as outletName')
                ->where('orders.customer_id', $post_data['userId'])
                //->where('orders.order_status', '<>', '12')
                ->where('orders.order_status', '!=', '12')
                ->where('orders.order_status', '!=', '11')
                ->where('orders.order_status', '!=', '33')
                ->orderByRaw('orders.id DESC')
                ->distinct()
                ->get();
                //print_r($orders_data);exit();
                if (count($orders_data)>0) {

                $data=array();
                foreach ($orders_data as $key => $value) {
                    $data[$key]['orderId']=$value->orderId;
                    $data[$key]['vendorId']=$value->vendorId;
                    $data[$key]['customerId']=$value->customerId;
                    $data[$key]['outletId']=$value->outletId;
                    $data[$key]['driverId']=isset($value->driverId)?$value->driverId:"";
                    $data[$key]['totalAmount']=$value->totalAmount;
                    $data[$key]['orderStatus']=$value->orderStatus;
                    $data[$key]['outletName']=$value->outletName;

                    $order_info=DB::select("select SUM(item_unit) as item_unit from orders_info where order_id = $value->orderId ");

                 if (count($order_info)>0) {

                    $orderInfoArray=array();

                    foreach ($order_info as $keys => $values) {

                    $orderInfoArray[$keys]['itemCount']= $values->item_unit;

                    }

                }

                    $data[$key]['itemCount']= $values->item_unit;
    
                }

                 // print_r($data);exit();


                $result = array("status" => 1,  "message" => trans("messages.order short list"), 'detail' => $data);
                } else {
                $orders_data = DB::table('orders')
                ->join('outlet_infos', 'outlet_infos.id', '=', 'orders.outlet_id')
                ->join('outlet_reviews', 'outlet_reviews.order_id', '=', 'orders.id')
                ->select('orders.id as orderId', 'orders.vendor_id as vendorId', 'orders.customer_id as customerId', 'orders.outlet_id as outletId', 'orders.driver_ids as driverId', 'orders.total_amount as totalAmount', 'orders.order_status as orderStatus', 'outlet_infos.outlet_name as outletName')
                ->where('orders.customer_id', $post_data['userId'])
                
                ->where('outlet_reviews.ratings', '=', "-2")
                ->limit(1)
                ->get();

                $data=array();
                foreach ($orders_data as $key => $value) {
                    $data[$key]['orderId']=$value->orderId;
                    $data[$key]['vendorId']=$value->vendorId;
                    $data[$key]['customerId']=$value->customerId;
                    $data[$key]['outletId']=$value->outletId;
                    $data[$key]['driverId']=isset($value->driverId)?$value->driverId:"";
                    $data[$key]['totalAmount']=$value->totalAmount;
                    $data[$key]['orderStatus']=$value->orderStatus;
                    $data[$key]['outletName']=$value->outletName;

                    $order_info=DB::select("select SUM(item_unit) as item_unit from orders_info where order_id = $value->orderId ");

                if (count($order_info)>0) {

                    $orderInfoArray=array();

                foreach ($order_info as $keys => $values) {

                    $orderInfoArray[$keys]['itemCount']= $values->item_unit;

                    }

                }
  
                    $data[$key]['itemCount']= $values->item_unit;
    
                }



                if (count($data)>0) {
                    $result = array("status" => 2,  "message" => trans("messages.order short list"), 'detail' => $data);
                } else {
                    $result = array("status" => 3,  "message" => trans("messages.no_order"), 'detail' => array());
                }
            }
        }
        return json_encode($result);
    }


    public function muserLogout(Request $data)
    {
        $post_data = $data->all();
        // if ($post_data['language'] == 2) {
        // 	App::setLocale('ta');
        // } else {
        // 	App::setLocale('en');
        // }
        $rules = [
            //'userId' => ['required'],
            //'language' => ['required'],
        ];
        $errors = $result = array();

        $validator = app('validator')->make($post_data, $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("status" => 0,  "message" => trans('messages.Error List'), "detail" => $errors);
        } else {
            //$check_auth = JWTAuth::toUser($post_data['token']);
            $deviceId = $post_data['deviceId'];
            $post_data['userId'] =$post_data['id'];
            $user_det = Users::find($post_data['userId']);
            $user_det['android_device_id'] = '';
            $user_det['android_device_token'] = '';
            $user_det['ios_device_id'] = '';
            $user_det['ios_device_token'] = '';
            $user_det['loggedin_status'] = 0;
            $user_det->save();
            $result = array("status" => 1, "message" => trans('messages.Logged out successfully'));
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

   //  public function getUserReview()
   //  {
   //      $jsonResponse='{
			// "status": 200,
			// "message": "success",
			// "overAllRating": "5.5",
			// "details": [
			//   {
			// 	"userId": 123,
			// 	"userName": "Vignesh",
			// 	"userRating": 3,
			// 	"userComment": "is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum."
			//   },
			//   {
			// 	"userId": 123,
			// 	"userName": "Vignesh",
			// 	"userRating": 3,
			// 	"userComment": "is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum."
			//   },
			//   {
			// 	"userId": 123,
			// 	"userName": "Vignesh",
			// 	"userRating": 3,
			// 	"userComment": "is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum."
			//   },
			//   {
			// 	"userId": 123,
			// 	"userName": "Vignesh",
			// 	"userRating": 3,
			// 	"userComment": "is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum."
			//   },
			//   {
			// 	"userId": 123,
			// 	"userName": "Vignesh",
			// 	"userRating": 3,
			// 	"userComment": "is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum."
			//   },
			//   {
			// 	"userId": 123,
			// 	"userName": "Vignesh",
			// 	"userRating": 3,
			// 	"userComment": "is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum."
			//   }
			// ]
		 //  }';
   //      return ($jsonResponse);
   //  }

    public function getUserReview(Request $data)
    {

        $post_data = $data->all();
       if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $rules = [
            'userId' => ['required'],
            //'language' => ['required'],
        ];
        $errors = $result = array();

        $validator = app('validator')->make($post_data, $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("status" => 0,  "message" => trans('messages.Error List'), "detail" => $errors);
        } else {


            $userId=$post_data['userId'];


            $userReview = DB::table('outlet_reviews')
                        ->join('users','users.id','=','outlet_reviews.customer_id')
                        ->select('outlet_reviews.customer_id as userId',
                                 'users.name as userName','outlet_reviews.ratings as userRating','outlet_reviews.comments as userComment'
                                    )
                        ->where('outlet_reviews.customer_id',$userId)
                        ->get();


                    $result = array("status" => 1, "message" =>trans('messages.success'),"overAllRating"=> "5.5","detail"=>$userReview);

        }
                return json_encode($result);

    }


    public function barcodes (Request $data){
        //print_r("expression");exit();
        // require_once 'Classes/PHPExcel.php';
        $tmpfname="/var/www/html/PRODUCT LISTS 1.xls";
        $excelReader = PHPExcel_IOFactory::createReaderForFile($tmpfname);
        $excelObj = $excelReader->load($tmpfname);
        $worksheet = $excelObj->getSheet(0);
        $lastRow = $worksheet->getHighestRow();
        $main_array=array();
        for ($row = 1; $row <= $lastRow; $row++) {
              $val2 = $worksheet->getCell('A'.$row)->getValue();
              $val1 = $worksheet->getCell('B'.$row)->getValue();
                for($i=0; $i<$lastRow;$i++){
                $sub_array = array(rtrim($val1));

                }

              $products=DB::table('products')
                ->select('id','product_url','barcode')
                ->where('product_url','=',rtrim($val1))         
                ->get();
               //  $products=DB::table('products')
               //  ->select('id','product_url','barcode')
               //  //->where('product_url','=',$val1) 
               //  ->where('barcode','=',null)
               //  ->get();
               // print_r($products);exit;
              foreach ($products as $key => $value) {
                if(in_array($value->product_url, $sub_array)){
                    if($value->barcode != null){
                        
                        $pro=DB::table('products')
                            ->where('id','=',$value->id)
                            ->update(['product_info_image'=> $val2.'.jpg']);

                    }else{
                        print_r("adsfsf");
                    }
                }else
                {
                    print_r("expression");
                }
            }
           
        }
    }

    public function outletDetails(Request $data){
        
        $post_data = $data->all();
  
        $post_data['language']= 1;
        $post_data['outlet_category']= 1;
        $post_data['vendor_id']= 236;

       if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $rules = [
            'outlet_category' => ['required'],
            'vendor_id' => ['required'],
            //'language' => ['required'],
        ];
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("status" => 0,  "message" => trans('messages.Error List'), "detail" => $errors);
        } else {

            
            $vendor_id=   isset($post_data['vendor_id'])?$post_data['vendor_id']:0;
            $outlet_category=   isset($post_data['outlet_category'])?$post_data['outlet_category']:1;


            $stores = DB::table('outlets')
                    ->join('vendors','vendors.id','=','outlets.vendor_id')
                    ->join('outlet_infos','outlet_infos.id','=','outlets.id')
                    ->join('vendors_infos','vendors_infos.id','=','outlets.vendor_id')
                    ->select('outlets.vendor_id as vendors_id ', 'vendors.first_name', 'vendors.last_name','outlets.id as outlets_id','vendors_infos.vendor_name', 'outlet_infos.outlet_name', 'vendors.featured_image', 'vendors.logo_image','vendors.delivery_time as vendors_delivery_time', 'vendors.average_rating as vendors_average_rating', 'vendors.featured_vendor',  'outlets.delivery_time as outlets_delivery_time','outlets.category_ids','outlets.latitude' , 'outlets.longitude', 'outlets.average_rating as outlets_average_rating','vendors_infos.vendor_description' , 'outlet_infos.contact_address','outlets.image as outletImage')
                    ->where('outlets.outlet_category',$outlet_category)
                    ->where('outlets.vendor_id',$vendor_id)
                    ->get();
           /* $query = 'earth_box(ll_to_earth(' . $latitude . ',' . $longitude . '), 3000000) @> ll_to_earth( outlets.latitude , outlets.longitude ) and outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';

            $condition = " and outlets.active_status = '1' ";
            $condition = " and outlets.outlet_category = ".$outlet_category;
            $query1 = ' and outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';

            $orderby = '';
            $stores = DB::select("select outlets.vendor_id as vendors_id , vendors.first_name, vendors.last_name,outlets.id as outlets_id,vendors_infos.vendor_name, outlet_infos.outlet_name, vendors.featured_image, vendors.logo_image,vendors.delivery_time as vendors_delivery_time, vendors.average_rating as vendors_average_rating, vendors.featured_vendor,  outlets.delivery_time as outlets_delivery_time, outlets.category_ids,outlets.latitude , outlets.longitude, outlets.average_rating as outlets_average_rating,vendors_infos.vendor_description , outlet_infos.contact_address
                ,earth_distance(ll_to_earth( " . $latitude . " , " . $longitude . " ), ll_to_earth(outlets.latitude , outlets.longitude)) as distance
             from outlets  LEFT JOIN outlet_infos ON outlet_infos.id = outlets.id LEFT JOIN vendors ON vendors.id = outlets.vendor_id LEFT JOIN vendors_infos ON vendors_infos.id = vendor_id   where " . $query . $query1 . $condition ."");*/

            

            foreach ($stores as $key => $value) {
                //
                 if (file_exists(base_path() . '/public/assets/admin/base/images/outlets/' . $stores->outletImage) && $stores->outletImage != '') {
                        $imageName = URL::to("/assets/admin/base/images/outlets/" . $stores->outletImage . '?' . time());
                    }
                    $stores[$key]->image = $imageName;
            }
            $result = array("status" => 1, "message" =>trans('messages.success'),"detail"=>$stores);
        }
        return json_encode($result);


    }
    public function promotionOffers(Request $data){
        $promotion = DB::table('customer_promotion')
                ->select('*')
                ->get();
        foreach ($promotion as $key => $value) {
                $imageName = URL::asset('assets/admin/base/images/default_avatar_male.jpg');

         if (file_exists(base_path() . '/public/assets/admin/base/images/customerPromotion/' . $value->image) && $value->image != '') {
                $imageName = URL::to("/assets/admin/base/images/customerPromotion/" . $value->image . '?' . time());
            }
                $promotion[$key]->image = $imageName;
            }        
        $result = array("status" => 1, "message" =>trans('messages.success'),"detail"=>$promotion,
          "quickpay"=>["100", "500", "1000", "1500", "2000", "2500"]);
        return json_encode($result);
    }

    public function PaymentHistory(Request $data){
        
        $post_data = $data->all();
        $rules = [
            'customer_id' => ['required'],
        ];
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("status" => 0,  "message" => trans('messages.Error List'), "detail" => $errors);
        } else {

            $user_details= DB::table('users')->select('*')->where('users.id', '=', $post_data['customer_id'])->get();

           // print_r($user_details[0]);exit();
            // [wallet_amount] => 450
            //[offer_wallet] => 500
            $payment_history_cashier = DB::table('payment_history')
                    ->join('outlets','payment_history.outlet_id','=','outlets.id')
                    ->join('outlet_infos','outlet_infos.id','=','outlets.id')
                    ->select('payment_history.id','payment_history.payment_type','payment_history.amount','payment_history.order_id','payment_history.created_date','payment_history.outlet_id','outlets.latitude' , 'outlets.longitude','outlet_infos.contact_address','outlet_infos.outlet_name','outlets.image','payment_history.offer_id')
                    ->where('payment_history.customer_id',$post_data['customer_id'])
                    ->where('payment_history.payment_type',3)
                    ->orderBy('payment_history.created_date', 'desc')

                    ->get();
            $payment_history_wallet = DB::table('payment_history')
                    ->select('payment_history.id','payment_history.payment_type','payment_history.amount','payment_history.created_date','payment_history.outlet_id','payment_history.offer_id')
                    ->where('payment_history.customer_id',$post_data['customer_id'])
                   ->where('payment_history.payment_type','!=',3)

                    ->orderBy('payment_history.created_date', 'desc')


                    ->get();

            /*merge and sort the array based on created date*/
            $payment_history=array_merge($payment_history_cashier,$payment_history_wallet);
            //print_r($payment_history);exit();
            $sort = array();
            foreach($payment_history as $k=>$v) {
                $sort['created_date'][$k] = $v->created_date;
            }

            if($payment_history){

                array_multisort($sort['created_date'], SORT_DESC, $payment_history);
            }
            /*merge and sort the array based on created date*/

            foreach ($payment_history as $key => $value) {
                $payment_history[$key]->promotion_name = '';
                $payment_history[$key]->addition_promotion = '';
                $payment_history[$key]->label = "Payment Success";
                $payment_history[$key]->order_id = isset($value->order_id)?$value->order_id:0;
                $payment_history[$key]->type = "DE";

                $payment_history[$key]->base_amount = '';
                if($value->offer_id)
                {
                    $offer_details = DB::table('customer_promotion')
                        ->select('customer_promotion.*')
                        ->where('customer_promotion.id',$value->offer_id)
                        ->get();
                    $payment_history[$key]->promotion_name = isset($offer_details[0]->promotion_name)?$offer_details[0]->promotion_name:'';
                    $payment_history[$key]->addition_promotion = isset($offer_details[0]->addition_promotion)?$offer_details[0]->addition_promotion:'';
                    $payment_history[$key]->base_amount = isset($offer_details[0]->base_amount)?$offer_details[0]->base_amount:'';


                }
                $payment_history[$key]->currencyCode =CURRENCYCODE;
                $logo_image = URL::asset('assets/admin/base/images/online-store.png');
                $img = isset($value->image)?$value->image:'online-store.png';
                if (file_exists(base_path() . '/public/assets/admin/base/images/outlets/' . $img) && $value->image != '') {
                 $logo_image = URL::asset('assets/admin/base/images/outlets/' . $img);
                }
                if(!isset($value->outlet_name)){
                    $payment_history[$key]->order_id ="";
                    if($value->payment_type == 1)
                    {
                        $payment_history[$key]->label = "Added Wallet";

                        $logo_image = URL::asset('assets/admin/base/images/credit-card.png');
                        $payment_history[$key]->outlet_name = "Add Wallet money";
                        $payment_history[$key]->order_id = "";
                        $payment_history[$key]->type = "CR";

                    }else{
                        $payment_history[$key]->label = "Payment Cancelled";

                        $logo_image = URL::asset('assets/admin/base/images/declain.png');
                        $payment_history[$key]->outlet_name = "Declained/Cancelled";
                        $payment_history[$key]->order_id = "";
                        $payment_history[$key]->type = "DE";


                    }
                    $payment_history[$key]->latitude = "";
                    $payment_history[$key]->longitude = "";
                    $payment_history[$key]->contact_address = "";

                } 
                $payment_history[$key]->image = $logo_image;

            }
            $wallet = isset($user_details[0]->wallet_amount)?$user_details[0]->wallet_amount:0;
            $offer_wallet = isset($user_details[0]->offer_wallet)?$user_details[0]->offer_wallet:0;
            $total_wallet = $wallet + $offer_wallet ;
      
            $result = array("status" => 1, "message" =>trans('messages.success'),"detail"=>$payment_history,"wallet_amount"=>$total_wallet,"grocery_wallet"=>$offer_wallet,"actualWalletBalance"=>$wallet);
        }
        return json_encode($result);
    }
    function dateSort($a,$b){
            $dateA = strtotime($a->created_date);
            $dateB = $b->created_date;//already unixtime
            return ($dateA-$dateB);
            }
   

    public function walletPaymentResult(Request $data)
    {
        $post_data = $data->all();
        $rules = [
            'cart_id' => ['required'],
            'result_type' => ['required'],
        ];
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("status" => 0,  "message" => trans('messages.Error List'), "detail" => $errors);
        } else {
				$user_id = Session::get('user_id');

        		$wallet_transaction = DB::table('payment_transaction')
					->select('*')
					->where('cart_id',$post_data['cart_id'])
					->get();
				if(count($wallet_transaction)){

					$dec = $wallet_transaction[0]->description;
	              	//  $str = "AWM-123-2-1";//eg paymwnt name - user id -wallet type -offer id 
	                $dec_explode =explode("-",$dec);
					$amount = isset($wallet_transaction[0]->amount)?$wallet_transaction[0]->amount:0;
					$wallet_type = isset($dec_explode[2])?$dec_explode[2]:0;
	                /**payment log**/
	 				if($post_data['result_type'] == 1)
	                {  
	                    $payment_type =1;//success
	                }else
	                {
	                   $payment_type =2;//fail
	                }
	                $res = DB::table('payment_transaction')
	                        ->where('cart_id', $post_data['cart_id'])
	                        ->update(['payment_type' => $payment_type, 'wallet_type' => $wallet_type, 'customer_id' => $user_id]);
	              
	                /**payment log**/
	               
	                $result = array("status" => 1, "message" =>trans('messages.success'),"amount"=>$amount,'userId'=>$dec_explode[1],'walletType'=>$dec_explode[2],'offerId'=>$dec_explode[3]);
	            }else{
	            	$result = array("status" => 0,  "message" => trans('messages.Invalid '));

	            }
           	}
        	return json_encode($result);
    }




    public function walletPaymentResult_copy(Request $data)
    {
        $post_data = $data->all();
        $rules = [
            'order_ref' => ['required'],
            'result_type' => ['required'],
        ];


       
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("status" => 0,  "message" => trans('messages.Error List'), "detail" => $errors);
        } else {

            $params = array(
                    'ivp_method'=>'check',
                    'ivp_store'=>'21961',
                    'ivp_authkey'=>'DRsmq^MG9m@fjX3z',
                    'order_ref'=>$post_data['order_ref'],
                    'ivp_test'=>'1',
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://secure.telr.com/gateway/order.json");
            curl_setopt($ch, CURLOPT_POST, count($params));
            curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
            $results = curl_exec($ch);
            curl_close($ch);
            $results = json_decode($results,true);
            //echo"<pre>";print_r($results['order']);exit();
            isset($results['error'])?$error=1:$error =0;
            if($error !=1)
            {

                /**payment log**/
                $dec = $results['order']['description'];
                $str = "AWM-123-2-1";//eg paymwnt name - user id -wallet type -offer id 
                $dec_explode =explode("-",$str);
                $terms      = new payment_history;
                $terms->outlet_id =0;
                $terms->customer_id =isset($dec_explode[1])?$dec_explode[1]:0;
                $terms->amount =$results['order']['amount'];
                if($post_data['result_type'] == 1)
                {  
                    $terms->payment_type =1;//success
                }else
                {
                   $terms->payment_type =2;//fail
                }
                $terms->wallet_type =isset($dec_explode[2])?$dec_explode[2]:0;
                $terms->created_at =date("Y-m-d H:i:s");
                $terms->created_date =date("Y-m-d H:i:s");
                $terms->updated_at =date("Y-m-d H:i:s");
                $terms->save();
                /**payment log**/

                /*carddetails store*/


                $card = DB::table('users_cards')
                    ->where('card_number',$post_data['cardfirst6'])
                    ->where('last_card_number',$post_data['cardlast4'])
                    ->select('*')
                    ->get();
                if(count($card))
                {
                    $userDetails=DB::table('users_cards')->where('users_cards.card_id', '=', $card[0]->card_id)->update(['reference_id'=>$post_data['tranref']]);
                }else
                {
                    $card_details_values = array('card_status' => $post_data['status'],
                        'address_verification_system' => $post_data['avs'],
                        'authorization_code' => $post_data['code'],
                        'message' => $post_data['message'],
                        'card_authorization_valid' => $post_data['ca_valid'],
                        'card_code' => $post_data['cardCode'],
                        'card_number' => $post_data['cardfirst6'],
                        'last_card_number' => $post_data['cardlast4'],
                        'cvv' => $post_data['cvv'],
                        'updated_date' => date("Y-m-d H:i:s"),
                        'created_date' => date("Y-m-d H:i:s"),
                        'reference_id' => $post_data['tranref'],
                        'expiry_date' => "test",
                        'user_id' => $dec_explode[1]);
                    DB::table('users_cards')->insert($card_details_values);
                }

               
                $result = array("status" => 1, "message" =>trans('messages.success'));
            }else {
                $result = array("status" => 0,  "message" => trans('messages.Invalid order ref'));
            }
        }
        return json_encode($result);
    }


    public function walletAddTelr()
    {
        $post_data = $_GET;
        $rules = [
            'customer_id' => ['required'],
            'add_wallet' => ['required'],
            'promotion_type' => ['required'],
            'amount' => ['required'],
            //'language' => ['required'],
        ];
        $errors = $result = array();
        $validator = app('validator')->make($post_data, $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result = array("status" => 0,  "message" => trans('messages.Error List'), "detail" => $errors);
        } else {
            $user_id = isset($post_data['customer_id'])?$post_data['customer_id']:0;
            $add_wallet = isset($post_data['add_wallet'])?$post_data['add_wallet']:0;
            $promotion_type = isset($post_data['promotion_type'])?$post_data['promotion_type']:0;

            $users = DB::table('users')
                ->join('user_address','user_address.user_id','=','users.id')
                ->where('users.id',$user_id)
                ->select('users.*','user_address.address')
                ->get();
            $cart_id = rand(1000, 9999); 
           // print_r($add_wallet);exit();
            if($add_wallet == 1) {
                $amount = $post_data['amount'];

            } else {
                $details= getCustPromotiondetails($promotion_type);
                $base_amount = isset($details->base_amount)?$details->base_amount:0;
                $addition_promotion = isset($details->addition_promotion)?$details->addition_promotion:0;
                $amount = $base_amount + $addition_promotion;
            }
            $currency_code = 'AED';
            $desc = "AWM-".$user_id."-".$add_wallet."-".$promotion_type;
            $paymentgateway= getPaymentDetails(30);
            $auth_key = isset($paymentgateway->merchant_secret_key)?$paymentgateway->merchant_secret_key:'DRsmq^MG9m@fjX3z';
            $store_id = isset($paymentgateway->merchant_key)?$paymentgateway->merchant_key:'21961';
            $params = array(
                    'ivp_method'=>'create',
                    'ivp_store'=>$store_id,
                    'ivp_authkey'=>$auth_key,
                    'ivp_cart'=>$cart_id,
                    'ivp_test'=>'1',
                    'ivp_amount'=>$amount,
                    'ivp_currency'=>$currency_code,
                    'ivp_desc'=>$desc,
                    'return_auth'=>'https://brozapp.com/payment_sucess',
                    'return_can'=>'https://brozapp.com/payment_cancel',
                    'return_decl'=>'https://brozapp.com/payment_declain',
                    'bill_title'=>isset($users[0]->social_title)?$users[0]->social_title:'',
                    'bill_fname'=>isset($users[0]->name)?$users[0]->name:'',
                    'bill_sname'=>isset($users[0]->last_name)?$users[0]->last_name:'',
                    'bill_addr1'=>isset($users[0]->address)?$users[0]->address:'Dubai',
                    'bill_city'=>isset($users[0]->city)?$users[0]->city:'Dubai',
                    'bill_region'=>isset($users[0]->region)?$users[0]->region:'Dubai',
                    'bill_country'=>'AE',
                    'bill_email'=>isset($users[0]->email)?$users[0]->email:'',
                    'ivp_trantype'=>'sale'
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://secure.telr.com/gateway/order.json");
            curl_setopt($ch, CURLOPT_POST, count($params));
            curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
            $results = curl_exec($ch);
            curl_close($ch);
            $results = json_decode($results,true);
           //echo"<prE>" ;print_r($results['error']['message']);exit();
            isset($results['error'])?$error=1:$error =0;
            if($error !=1)
            {
                $ref= trim($results['order']['ref']);
                $url= trim($results['order']['url']);
                if (empty($ref) || empty($url))
                {
                    $result = array("status" => 2, "message" =>trans('messages.fails'));

                }else
                {   
                    //print_r($url);exit();
                    return redirect::to($url);
                }
            }else {  
             
                $result = array("status" => 2, "message" =>$results['error']['message']);
            }

        }
        return json_encode($result);

    
      
        
    }

}
