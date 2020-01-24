<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use cast_vote\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use GuzzleHttp\Client;
use DB;
use App\Model\contactus;
use App\Model\users;
use App\Model\settings;
use App\Model\emailsettings;
use App\Model\cms;
use Session;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Contracts\Auth\Registrar;
use MetaTag;
use Mail;
use SEO;
use SEOMeta;
use OpenGraph;
use Twitter;
use App;
//use App\Http\Controllers\Api\Api;
use App\Model\api;
use PDF;

class Profile extends Controller
{
    const USERS_SIGNUP_EMAIL_TEMPLATE = 1;
    const USERS_WELCOME_EMAIL_TEMPLATE = 3;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->user_details = array();
        $this->api = New Api;
        $this->client = new Client([
            // Base URI is used with relative requests
            'base_uri' => url('/'),
           //  'base_uri' => url('http://127.0.0.1/'),
            // You can set any number of default request options.
            'timeout'  => 3000.0,
        ]);
        $this->theme = Session::get("general")->theme;
    }
    
    public function check_login()
    {
        $user_id = Session::get('user_id');
        $token = Session::get('token');
        if(empty($user_id))
        {
            return Redirect::to('/')->send();
        }
        $user_array = array("user_id" => $user_id,"token"=>$token);
        $method = "POST";
        $data = array('form_params' => $user_array);
        $response = $this->api->call_api($data,'api/user_detail',$method);
        if($response->response->httpCode == 400)
        {
            return Redirect::to('/')->send();
        }
        else
        {
            $this->user_details = $response->response->user_data[0];
            return $this->user_details;
        }
    }
    
    public function profile(Request $data)
    { 
        $user_details = $this->check_login();
        //~ echo '<pre>';print_r($user_details);die;
        $user_id = Session::get('user_id');
        $token = Session::get('token');
       
        $user_array = array("user_id" => $user_id,"token"=>$token);
        $method = "POST";
        $data = array('form_params' => $user_array);
        SEOMeta::setTitle(Session::get("general_site")->site_name.' - '.'Profile');
        SEOMeta::setDescription(Session::get("general_site")->site_name.' - '.'Profile');
        SEOMeta::addKeyword(Session::get("general_site")->site_name.' - '.'Profile');
        OpenGraph::setTitle(Session::get("general_site")->site_name.' - '.'Profile');
        OpenGraph::setDescription(Session::get("general_site")->site_name.' - '.'Profile');
        OpenGraph::setUrl(URL::to('/'));
        Twitter::setTitle(Session::get("general_site")->site_name.' - '.'Profile');
        Twitter::setSite(Session::get("general_site")->site_name);
        return view('front.'.$this->theme.'.edit_profile')->with("user_details",$this->user_details);
    }
    
    public function update_profile(Request $data)
    {
        $method = "POST";
        $user_array = $data->all();
        //print_r($user_array);exit;
        $user_array['token'] = Session::get('token');
        $user_array['user_id'] = Session::get('user_id');
        $user_array['language'] = getCurrentLang();
        $data = array('form_params' => $user_array);
        $response = $this->api->call_api($data,'api/update_profile',$method);
        
        if($response->response->httpCode =='200')
        {
             Session::put('user_id', $response->response->user_id);
             Session::put('mobile', $response->response->mobile);
            Session::put('email', $response->response->email);
            Session::put('first_name', $response->response->first_name);
            Session::put('last_name', $response->response->last_name);
            Session::put('name', $response->response->name);
            Session::put('social_title', $response->response->social_title);
            Session::put('profile_image', $response->response->image);
             Session::flash('message', $response->response->Message);
            return Redirect::to(redirect()->getUrlGenerator()->previous());
        }
        else
        {
            Session::flash('message-failure', $response->response->Message);
            return Redirect::to('/');
        }
    }
    
    
}
