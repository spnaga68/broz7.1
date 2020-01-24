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

class Welcome extends Controller
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
            // 'base_uri' => url('http://127.0.0.1/'),
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
            if($this->user_details->email == "")
            {
                Session::flash('message-failure',trans("messages.Please fill your personal details"));
                return Redirect::to('/profile')->send();
            } 
            return $this->user_details;
        }
    }
    /*
    public function profile(Request $data)
    {
        $user_details = $this->check_login();
        $user_id = Session::get('user_id');
        $token = Session::get('token');
        
        $user_array = array("user_id" => $user_id,"token"=>$token);
        $method = "POST";
        $data = array('form_params' => $user_array);
        SEOMeta::setTitle(Session::get('general')->site_name);
        SEOMeta::setDescription(Session::get('general')->site_name);
        SEOMeta::addKeyword(Session::get('general')->site_name);
        OpenGraph::setTitle(Session::get('general')->site_name);
        OpenGraph::setDescription(Session::get('general')->site_name);
        OpenGraph::setUrl(URL::to('/'));
        Twitter::setTitle(Session::get('general')->site_name);
        Twitter::setSite(Session::get('general')->site_name);
        return view('front.edit_profile')->with("user_details",$this->user_details);
    }
    */
    public function edit_card($id)
    {
        $method = "POST";
        $user_array = array();
        $user_array['token'] = Session::get('token');
        $user_array['user_id'] = Session::get('user_id');
        $user_array['card_id'] = $id;
        $data = array('form_params' => $user_array);
        $response = $this->api->call_api($data,'api/card_detail',$method);
        $card_detail = $response->response->card_detail;
        SEOMeta::setTitle(Session::get('general')->site_name);
        SEOMeta::setDescription(Session::get('general')->site_name);
        SEOMeta::addKeyword(Session::get('general')->site_name);
        OpenGraph::setTitle(Session::get('general')->site_name);
        OpenGraph::setDescription(Session::get('general')->site_name);
        OpenGraph::setUrl(URL::to('/'));
        Twitter::setTitle(Session::get('general')->site_name);
        Twitter::setSite(Session::get('general')->site_name);
        $user_details = $this->check_login();
        return view('front.'.$this->theme.'.edit_card')->with("user_details",$this->user_details)->with("card_detail",$card_detail);
    }
    
    
    
    public function cards(Request $data)
    {
        $user_details = $this->check_login();
    //    echo "asdf";exit;
        $method = "POST";
        $user_array = array();
        $user_array['token'] = Session::get('token');
        $user_array['user_id'] = Session::get('user_id');
        $user_array['language'] = getCurrentLang();
        $data = array('form_params' => $user_array);
        $response = $this->api->call_api($data,'api/get_address',$method);
        $address_list = $response->response->address_list;
        $response = $this->api->call_api($data,'api/get_cards',$method);
        $card_list = $response->response->card_list;
        SEOMeta::setTitle(Session::get("general_site")->site_name.' - '.'Cards');
        SEOMeta::setDescription(Session::get("general_site")->site_name.' - '.'Cards');
        SEOMeta::addKeyword(Session::get("general_site")->site_name.' - '.'Cards');
        OpenGraph::setTitle(Session::get("general_site")->site_name.' - '.'Cards');
        OpenGraph::setDescription(Session::get("general_site")->site_name.' - '.'Cards');
        OpenGraph::setUrl(URL::to('/'));
        Twitter::setTitle(Session::get("general_site")->site_name.' - '.'Cards');
        Twitter::setSite(Session::get("general_site")->site_name);
        return view('front.'.$this->theme.'.cards')->with("user_details",$this->user_details)->with("card_list",$card_list)->with("address_list",$address_list);
    }
    
    public function new_address(Request $data)
    {
        $user_details = $this->check_login();
        $method = "POST";
        $user_array = array();
        $user_array['token'] = Session::get('token');
        $user_array['user_id'] = Session::get('user_id');
        $user_array['language'] = getCurrentLang();
        $data = array('form_params' => $user_array);
        $response = $this->api->call_api($data,'api/address_type',$method);
        $address_type = $response->response->address_type;
        $address_types[""] = trans("messages.Select address type");
        foreach ($address_type as $row)
        {
            $address_types[$row->id] = ucfirst($row->name);
        }
        $user_details = $this->check_login();
        SEOMeta::setTitle(Session::get('general')->site_name);
        SEOMeta::setDescription(Session::get('general')->site_name);
        SEOMeta::addKeyword(Session::get('general')->site_name);
        OpenGraph::setTitle(Session::get('general')->site_name);
        OpenGraph::setDescription(Session::get('general')->site_name);
        OpenGraph::setUrl(URL::to('/'));
        Twitter::setTitle(Session::get('general')->site_name);
        Twitter::setSite(Session::get('general')->site_name);
        return view('front.'.$this->theme.'.new_address')->with("user_details",$user_details)->with("address_types",$address_types);
    }
    
    public function get_city(Request $data)
    {
        $post_data = $data->all();
        $user_array['token'] = Session::get('token');
        $user_array['country_id'] = $post_data['country_id'];
        $user_array['language'] = getCurrentLang();
        $data = array('form_params' => $user_array);
        $method = "POST";
        $response = $this->api->call_api($data,'api/get_city',$method);
        $country = $response->response->data;
        $city_array = array();
        foreach ($country as $countrys)
        {
            $id = $countrys->cid;
            $city_name = $countrys->city_name;
            $city_array[$id] = $city_name;
        }
        return json_encode($city_array);
    }
    
    public function new_card(Request $data)
    {
        $user_details = $this->check_login();
        SEOMeta::setTitle(Session::get('general')->site_name);
        SEOMeta::setDescription(Session::get('general')->site_name);
        SEOMeta::addKeyword(Session::get('general')->site_name);
        OpenGraph::setTitle(Session::get('general')->site_name);
        OpenGraph::setDescription(Session::get('general')->site_name);
        OpenGraph::setUrl(URL::to('/'));
        Twitter::setTitle(Session::get('general')->site_name);
        Twitter::setSite(Session::get('general')->site_name);
        return view('front.'.$this->theme.'.new_card')->with("user_details",$this->user_details);
    }
    
    public function update_card(Request $data)
    {
        $user_details = $this->check_login();
        $method = "POST"; 
        $post_data = $data->all();
        $user_array['token'] = Session::get('token');
        $user_array['user_id'] = Session::get('user_id');
        $user_array['language'] = getCurrentLang();
        $user_array['credit_card_number'] = $post_data['card_number'];
        $user_array['card_id'] = $post_data['card_id'];
        $user_array['credit_card_expiry'] = $post_data['month']."/".$post_data['year'];        
        $data = array('form_params' => $user_array);
        $response = $this->api->call_api($data,'api/update_card',$method);
        if($response->response->httpCode == 200)
        {
            Session::flash('message-success', $response->response->Message);
            return Redirect::to('/cards');
        }
        else
        {
            Session::flash('message-failure', $response->response->Message);
            return Redirect::to('/new-card');
        }
    }
    public function delete_card($id)
    {
        $user_details = $this->check_login();
        $method = "POST";
        $user_array['token'] = Session::get('token');
        $user_array['language'] = getCurrentLang();
        $user_array['user_id'] = Session::get('user_id');
        $user_array['card_id'] = $id;
        $data = array('form_params' => $user_array);
        $response = $this->api->call_api($data,'api/delete_card',$method);
        if($response->response->httpCode == 200)
        {
            Session::flash('message-success', $response->response->Message);
        }
        else
        {
            Session::flash('message-failure', $response->response->Message);
        }
        return Redirect::to('/cards');
    }
    
    public function store_card(Request $data)
    {
        $user_details = $this->check_login();
        $method = "POST";
        $post_data = $data->all();
        $user_array['language'] = getCurrentLang();
        $user_array['token'] = Session::get('token');
        $user_array['user_id'] = Session::get('user_id');
        $user_array['credit_card_number'] = $post_data['card_number'];
        $user_array['name_on_card'] = $post_data['name_on_card'];
        $user_array['credit_card_expiry'] = $post_data['month']."/".$post_data['year'];        
        $data = array('form_params' => $user_array);
        $response = $this->api->call_api($data,'api/store_card',$method);
        if($response->response->httpCode == 200)
        {
            Session::flash('message-success', $response->response->Message);
            return Redirect::to('/cards');
        }
        else
        {
            Session::flash('message-failure', $response->response->Message);
            return Redirect::to('/new-card');
        }
    }
    
    public function change_password(Request $data)
    {
        $user_details = $this->check_login();
        SEOMeta::setTitle(Session::get('general')->site_name);
        SEOMeta::setDescription(Session::get('general')->site_name);
        SEOMeta::addKeyword(Session::get('general')->site_name);
        OpenGraph::setTitle(Session::get('general')->site_name);
        OpenGraph::setDescription(Session::get('general')->site_name);
        OpenGraph::setUrl(URL::to('/'));
        Twitter::setTitle(Session::get('general')->site_name);
        Twitter::setSite(Session::get('general')->site_name);
        return view('front.'.$this->theme.'.change_password')->with("user_details",$this->user_details);
    }
    
    public function update_password(Request $data)
    {
        $user_details = $this->check_login();
        $method = "POST";
        $user_array = $data->all();
        $user_array['token'] = Session::get('token');
        $user_array['user_id'] = Session::get('user_id');
        $user_array['language'] = getCurrentLang();
        $data = array('form_params' => $user_array);
        $response = $this->api->call_api($data,'api/update_password',$method);
        if($response->response->httpCode == 200)
        {
            Session::flash('message-success', $response->response->Message);
        }
        else
        {
            Session::flash('message-failure', $response->response->Message);
        }
        return Redirect::to('/change-password');
    }
    public function store_address(Request $data)
    {
        $user_details = $this->check_login();
        $method = "POST";
        $post_data = $data->all();
        $post_data['token'] = Session::get('token');
        $post_data['user_id'] = Session::get('user_id');
        $post_data['language'] = getCurrentLang();
        $user_array = array('form_params' => $post_data);
        $response = $this->api->call_api($user_array,'api/store_address',$method);
        if($response->response->httpCode == 200)
        {
            Session::flash('message-success', $response->response->Message);
            return Redirect::to('/cards');
        }
        else
        {
            Session::flash('message-failure', $response->response->Message);
            return Redirect::to('/new-address');
        }
    }
    public function store_address_ajax(Request $data)
    {
        $method = "POST";
        $post_data = $data->all();
        $post_data['token'] = Session::get('token');
        $post_data['user_id'] = Session::get('user_id');
        $post_data['language'] = getCurrentLang();
        $user_array = array('form_params' => $post_data);
        
        $response = $this->api->call_api($user_array,'api/store_address',$method);
        return response()->json($response->response);
    }
    
    public function edit_address($id)
    {
        $user_details = $this->check_login();
        $method = "POST";
        $user_array = array();
        $user_array['token'] = Session::get('token');
        $user_array['user_id'] = Session::get('user_id');
        $user_array['address_id'] = $id;
        $user_array['language'] = getCurrentLang();
        $data = array('form_params' => $user_array);
        $response = $this->api->call_api($data,'api/address_detail',$method);
        $address_detail = $response->response->address_detail;
        $city_list = $this->api->call_api($data,'api/get_city_list',"POST");
        $country = $this->api->getcountry_select()->response->data;
        $country_array = array();
        $country_array[""] = trans("messages.Select Country");
        foreach ($country as $countrys)
        {
            $id = $countrys->cid;
            $country_name = $countrys->country_name;
            $country_array[$id] = $country_name;
        }
        
        $country = $this->api->getcountry_select()->response->data;
        $city_list = $city_list->response->data;
        
        $city_array[""] = trans("messages.Select City");
        foreach ($city_list as $city)
        {
            $id = $city->cid;
            $city_name = $city->city_name;
            $city_array[$id] = $city_name;
        }
        SEOMeta::setTitle(Session::get('general')->site_name);
        SEOMeta::setDescription(Session::get('general')->site_name);
        SEOMeta::addKeyword(Session::get('general')->site_name);
        OpenGraph::setTitle(Session::get('general')->site_name);
        OpenGraph::setDescription(Session::get('general')->site_name);
        OpenGraph::setUrl(URL::to('/'));
        Twitter::setTitle(Session::get('general')->site_name);
        Twitter::setSite(Session::get('general')->site_name);
        $user_details = $this->check_login();
        return view('front.'.$this->theme.'.edit_address')->with("user_details",$this->user_details)->with("address_detail",$address_detail)->with("country_list",$country_array)->with("city_list",$city_array);
    }
    
    public function update_address(Request $data)
    {
        $user_details = $this->check_login();
        $method = "POST";
        $post_data = $data->all();
        $post_data['token'] = Session::get('token');
        $post_data['user_id'] = Session::get('user_id');    
        $data = array('form_params' => $post_data);
        $response = $this->api->call_api($data,'api/update_address',$method);
        
        
        if($response->response->httpCode == 200)
        {
            Session::flash('message-success', $response->response->Message);
            return Redirect::to('/cards');
        }
        else
        {
            Session::flash('message-failure', $response->response->Message);
            return Redirect::to('/edit-address/'.$post_data['address_id']);
        }
    }
    
    public function delete_address($id)
    {
        $user_details = $this->check_login();
        $method = "POST";
        $user_array['token'] = Session::get('token');
        $user_array['user_id'] = Session::get('user_id');
        $user_array['address_id'] = $id;
        $user_array['language'] = getCurrentLang();
        $data = array('form_params' => $user_array);
        $response = $this->api->call_api($data,'api/delete_address',$method);
        if($response->response->httpCode == 200)
        {
            Session::flash('message-success', $response->response->Message);
        }
        else
        {
            Session::flash('message-failure', $response->response->Message);
        }
        return Redirect::to('/cards');
    }
    
    public function favourites()
    {
        $user_details = $this->check_login();
        $method = "POST";
        $post_data['language'] = getCurrentLang();
        $post_data['token'] = Session::get('token');
        $post_data['user_id'] = Session::get('user_id');  
	
        $data = array('form_params' => $post_data); 
        $response = $this->api->call_api($data,'api/favourites',$method);
			
        if($response->response->httpCode == 400)
        {
            $stores = array();
        }
        $stores = $response->response->data;
        $user_details = $this->check_login();
        SEOMeta::setTitle(Session::get("general_site")->site_name.' - '.'Favourites');
        SEOMeta::setDescription(Session::get("general_site")->site_name.' - '.'Favourites');
        SEOMeta::addKeyword(Session::get("general_site")->site_name.' - '.'Favourites');
        OpenGraph::setTitle(Session::get("general_site")->site_name.' - '.'Favourites');
        OpenGraph::setDescription(Session::get("general_site")->site_name.' - '.'Favourites');
        OpenGraph::setUrl(URL::to('/'));
        Twitter::setTitle(Session::get("general_site")->site_name.' - '.'Favourites');
        Twitter::setSite(Session::get("general_site")->site_name);
        return view('front.'.$this->theme.'.favourites')->with("user_details",$this->user_details)->with("store_list",$stores);
    }
    
    public function profile_image(Request $data)
    {
        $user_details = $this->check_login();
        $post_array = $data->all();
        $post_data['language'] = getCurrentLang();
        $post_data['token'] = Session::get('token');
        $post_data['user_id'] = Session::get('user_id');    
        $token = Session::get('token');
        if(isset($post_array['image']) && $post_array['image'] != '')
        {
            $image_path = $post_array['image']->getPathname();
            $image_mime = $post_array['image']->getmimeType();
            $image_org  = $post_array['image']->getClientOriginalName();
            $image_ext  = $post_array['image']->getClientOriginalExtension();
            $post_data = [
                'multipart' => [
                    [
                        'name'     => 'image',
                        'filename' => $image_org,
                        'Mime-Type'=> $image_mime,
                        'contents' => fopen( $post_array['image']->getPathname(), 'r' ),
                    ],
                    ['name'     => 'user_id', 'contents' => Session::get('user_id')],
                    ['name'     => 'token', 'contents' => $token],
                    ['name'     => 'language', 'contents' => getCurrentLang()],
                ],
            ];
            $method = "POST";
            $response = $this->api->call_api($post_data,'api/update_profile_image',$method);
            Session::flash('message-success', $response->response->Message);
            return response()->json($response->response);
        }
    }
    
    public function orders()
    { 
        $user_details = $this->check_login();
        $method = "POST";
        $post_data['language'] = getCurrentLang();
        $post_data['token'] = Session::get('token');
        $post_data['user_id'] = Session::get('user_id');
        $data = array('form_params' => $post_data);
        $response = $this->api->call_api($data,'api/orders',$method);
        $order_list = $response->response->order_list;
        $user_details = $this->check_login();
        SEOMeta::setTitle(Session::get("general_site")->site_name.' - '.'Orders');
        SEOMeta::setDescription(Session::get("general_site")->site_name.' - '.'Orders');
        SEOMeta::addKeyword(Session::get("general_site")->site_name.' - '.'Orders');
        OpenGraph::setTitle(Session::get("general_site")->site_name.' - '.'Orders');
        OpenGraph::setDescription(Session::get("general_site")->site_name.' - '.'Orders');
        OpenGraph::setUrl(URL::to('/'));
        Twitter::setTitle(Session::get("general_site")->site_name.' - '.'Orders');
        Twitter::setSite(Session::get("general_site")->site_name);
        return view('front.'.$this->theme.'.orders')->with("user_details",$this->user_details)->with("orders",$order_list);
    }

    public function orders_info($id)
    {
        $user_details = $this->check_login();
        $method = "POST";
        $post_data['language'] = getCurrentLang();
        $post_data['token'] = Session::get('token');
        $post_data['user_id'] = Session::get('user_id');
        $order_id = decrypt($id);
        $post_data['order_id']= $order_id;
        $data = array('form_params' => $post_data);
        $response = $this->api->call_api($data,'api/order_detail',$method);
        $order_detail = $response->response->order_items;
        $delivery_details = $response->response->delivery_details;
        $vendor_info = $response->response->vendor_info;
        $reviews = $response->response->reviews;
        $return_reasons = $response->response->return_reasons;
        $tracking_result = $response->response->tracking_result;
        /*echo "<pre>";
        print_r($tracking_result);exit; */
        $last_state = $response->response->last_state;
        //Get the return order details if order was returned by customer
        $return_orders_data = DB::table('return_orders')
                ->select('return_orders.*','return_action.name as return_action_name','return_reason.name as return_reason_name','return_status.name as return_status_name','orders.order_key_formated')
                ->leftJoin('orders','orders.id','=','return_orders.order_id')
                ->leftJoin('return_action','return_action.id','=','return_orders.return_action_id')
                ->leftJoin('return_reason','return_reason.id','=','return_orders.return_reason')
                ->leftJoin('return_status','return_status.id','=','return_orders.return_status')
                ->where('return_orders.order_id',$order_id)
                ->orderBy('return_orders.created_at', 'desc')
                ->get();
        $user_fav = DB::table('outlet_reviews')->select(DB::raw('count(id)'))->where('customer_id',$post_data['user_id'])->where('order_id',$order_id)->first();        
        $return_orders = array();
        if(count($return_orders_data)>0){
            $return_orders = $return_orders_data[0];
        }
        $user_details = $this->check_login();
        SEOMeta::setTitle(Session::get("general_site")->site_name.' - '.'Order info');
        SEOMeta::setDescription(Session::get("general_site")->site_name.' - '.'Order info');
        SEOMeta::addKeyword(Session::get("general_site")->site_name.' - '.'Order info');
        OpenGraph::setTitle(Session::get("general_site")->site_name.' - '.'Order info');
        OpenGraph::setDescription(Session::get("general_site")->site_name.' - '.'Order info');
        OpenGraph::setUrl(URL::to('/'));
        Twitter::setTitle(Session::get("general_site")->site_name.' - '.'Order info');
        Twitter::setSite(Session::get("general_site")->site_name);
        return view('front.'.$this->theme.'.orders_info')->with("user_details",$this->user_details)->with("order_items",$order_detail)->with("delivery_details",$delivery_details)->with("vendor_info",$vendor_info[0])->with("return_reasons",$return_reasons)->with("tracking_result",$tracking_result)->with("last_state",$last_state)->with("return_orders_result",$return_orders)->with($reviews->review_status)->with("user_fav",$user_fav);
    }

    public function invoice($id)
    {
        //echo '<pre>';print_r(Session::get("general"));die;
        $user_details = $this->check_login();
        $method = "POST";
        $post_data['language'] = getCurrentLang();
        $post_data['token'] = Session::get('token');
        $post_data['user_id'] = Session::get('user_id');
        $order_id = decrypt($id);
        $post_data['order_id']= $order_id;
        $data = array('form_params' => $post_data);
        $response = $this->api->call_api($data,'api/order_detail',$method);
        $order_detail = $response->response->order_items;
        $delivery_details = $response->response->delivery_details;
        $vendor_info = $response->response->vendor_info;
        $user_details = $this->check_login();
        $logo = url('/assets/front/'.Session::get("general")->theme.'/images/'.Session::get("general")->theme.'.png');
        if(file_exists(base_path().'/public/assets/admin/base/images/vendors/list/'.$vendor_info[0]->logo_image)) { 
            $vendor_image ='<img width="100px" height="100px" src="'.URL::to("assets/admin/base/images/vendors/list/".$vendor_info[0]->logo_image).'") >';
        } else{  
            $vendor_image ='<img width="100px" height="100px" src="'.URL::to("assets/front/".Session::get("general")->theme."/images/blog_no_images.png").'") >';
        }
        $delivery_date = date("d F, l", strtotime($delivery_details[0]->delivery_date)); 
        $delivery_time = date('g:i a', strtotime($delivery_details[0]->start_time)).'-'.date('g:i a', strtotime($delivery_details[0]->end_time));
        $sub_total = 0;$item = '';
        $currency_side   = getCurrencyPosition()->currency_side;
        $currency_symbol = getCurrency($post_data['language']); 
        foreach($order_detail as $items)
        {
            if($currency_side == 1)
            {
                $item_cost = $currency_symbol.$items->item_cost;
                $unit_cost = $currency_symbol.($items->item_cost*$items->item_unit);
            }
            else {
                $item_cost = $items->item_cost.$currency_symbol;
                $unit_cost = ($items->item_cost*$items->item_unit).$currency_symbol;
            }
            $item .= '<tr><td align="center" style="font-size:15px;padding:10px 0; font-family:dejavu sans,arial; font-weight:normal; border-bottom:1px solid #ccc;">'.wordwrap(ucfirst(strtolower($items->product_name)),40,"<br>\n").'</td><td align="center" style="font-size:15px;padding:10px 0;border-bottom:1px solid #ccc; font-family:dejavu sans,arial; font-weight:normal;">'.wordwrap(ucfirst(strtolower($items->description)),40,"<br>\n").'</td><td align="center" style="font-size:15px;padding:10px 0;border-bottom:1px solid #ccc; font-family:dejavu sans,arial; font-weight:normal;">'.$items->item_unit.'</td><td align="center" style="font-size:15px;padding:10px 0;border-bottom:1px solid #ccc; font-family:dejavu sans,arial; font-weight:normal;">'.$item_cost.'</td><td align="center" style="font-size:15px;padding:10px 0;border-bottom:1px solid #ccc; font-family:dejavu sans,arial; font-weight:normal;">'.$unit_cost.'</td></tr>';
            /*$item .='<tr>
            <td style="width: 200px; padding: 15px 15px;" valign="middle"><a style="text-decoration: none; font-size: 16px; color: #333; font-family: arial;" title="" href="#"><img width="50px" height="50px" style="vertical-align: middle;" src='.url('/assets/admin/'.Session::get("general")->theme.'/images/products/thumb/'.$items->product_image).' alt="del" /></a>
            <p style="margin: 10px 0 0 0;">'.str_limit(ucfirst(strtolower($items->product_name)),30).'</p>
            </td>
            <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 0 15px;" width="100">'.$items->item_cost.getCurrency().'</td>
            <td style="font-size: 16px; color: #333; font-family: arial; text-align: right;" width="100">'.$items->item_unit.'</td>
            <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 0 15px;" width="100">'.($items->item_cost*$items->item_unit).getCurrency().'</td>
            </tr>
            <tr>
            <td colspan="5" width="100%">
            <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tbody>
            <tr>
            <td style="width: 100%; border-bottom: 1px solid #ccc;">&nbsp;</td>
            </tr>
            </tbody>
            </table>
            </td>
            </tr>';*/
            $sub_total += $items->item_cost*$items->item_unit;
        }
        if($currency_side == 1)
        {
            $delivery_charge = $currency_symbol.'0';
            $total_amount    = $currency_symbol.$delivery_details[0]->total_amount;
            $sub_total       = $currency_symbol.$sub_total;
            $service_tax     = $currency_symbol.$delivery_details[0]->service_tax;
        }
        else {
            $delivery_charge = '0'.$currency_symbol;
            $total_amount    = $delivery_details[0]->total_amount.$currency_symbol;
            $sub_total       = $sub_total.$currency_symbol;
            $service_tax     = $delivery_details[0]->service_tax.$currency_symbol;
        }
        if($delivery_details[0]->order_type == 1)
        {
            if($currency_side == 1)
            {
                $delivery_charge = $currency_symbol.$delivery_details[0]->delivery_charge;
            }
            else {
                $delivery_charge = $delivery_details[0]->delivery_charge.$currency_symbol;
            }
        }
        $delivery_email   = $delivery_details[0]->email;
        $delivery_address = ($delivery_details[0]->contact_address != '')?ucfirst($delivery_details[0]->contact_address):'-';
        if($delivery_details[0]->order_type == 1)
        {
            $delivery_type   = 'DELIVERY ADDRESS :';
            $delivery_address = ($delivery_details[0]->user_contact_address != '')?ucfirst($delivery_details[0]->user_contact_address):'-';
        }
        else {
            $delivery_type   = 'PICKUP ADDRESS :';
            $delivery_address = ($delivery_details[0]->contact_address != '')?ucfirst($delivery_details[0]->contact_address):'-';
        }
        /*if($delivery_details[0]->order_type == 1){
            $delivery_address ='<tr>
            <td style="font-size: 16px; color: #333; font-family: arial; text-align: left; padding: 15px 13px;">'.trans('messages.Delivery address').'</td>
            <td>:</td>
            <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 15px 13px;">'.$delivery_details[0]->user_contact_address.'</td>
            </tr><tr>
        <td style="font-size: 16px; color: #333; font-family: arial; text-align: left; padding: 15px 13px;">'.trans('messages.Delivery slot').'</td>
        <td>:</td>
        <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 15px 13px;">'.$delivery_date ." : ". $delivery_time.'</td>
        </tr><tr><td style="font-size: 16px; color: #333; font-family: arial; text-align: left; padding: 15px 13px;">'.trans('messages.Delivery mode').'</td>
            <td>:</td>
            <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 15px 13px;">'.trans('messages.Delivery to your address').'</td></tr>';

            $delivery_charge = '<tr>
        
        <td style="padding-bottom: 15px;" width="300">&nbsp;</td>
        
        <td style="font-size: 16px; color: #e91e63; font-family: arial; text-align: right; padding-bottom: 15px;">'.trans('messages.Delivery fee').'</td>
        <td style="font-size: 20px; color: #e91e63; font-family: arial; text-align: right; padding: 0 15px 15px;">'.$delivery_details[0]->delivery_charge.getCurrency().'</td>
        </tr>';
        }else {
            $delivery_address ='<tr>
            <td style="font-size: 16px; color: #333; font-family: arial; text-align: left; padding: 15px 13px;">'.trans('messages.Pickup address').'</td>
            <td>:</td>
            <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 15px 13px;">'.$delivery_details[0]->contact_address.'</td>
            </tr><tr><td style="font-size: 16px; color: #333; font-family: arial; text-align: left; padding: 15px 13px;">'.trans('messages.Delivery mode').'</td>
            <td>:</td>
            <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 15px 13px;">'.trans('messages.Pickup directly in store').'</td></tr>';
            $delivery_charge = '';
        }*/
        /*$html ='<table style="border: 1px solid #ccc;" border="0" width="450" align="left" cellspacing="0" cellpadding="0" bgcolor="#fff">
        <tbody>
        <tr>
        <td style="border-bottom: 1px solid #ccc; padding: 15px 15px;">
        <table border="0" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td><a title="" href="#"><img src='.$logo.' alt=".Session::get("general")->site_name." /></a></td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        <tr>
        <td style="border-bottom: 1px solid #ccc; padding: 15px 15px;">
        <table border="0" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td style="font-size: 20px; font-weight: normal; color: #333; font-family: arial;">Order Summary</td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        <tr>
        <td>
        <table border="0" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td style="width: 250px; padding: 15px 15px;">'.$vendor_image.'
        <h3 style="font-size: 20px; font-family: arial; color: #888; font-weight: normal;">'.$vendor_info[0]->vendor_name.'</h3>
        </td>
        <td style="width: 350px;">
        <table border="0" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td style="width: 50%; font-size: 20px; font-family: arial; color: #333; font-weight: normal; text-align: right; padding-bottom: 15px;">'.trans('messages.Order Id').'</td>
        <td width="150">&nbsp;</td>
        <td style="width: 50%; font-size: 20px; font-family: arial; color: #888; font-weight: normal; text-align: right; padding: 0 15px 15px;">'.$vendor_info[0]->order_key_formated.'</td>
        </tr>
        <tr>
        <td style="width: 50%; font-size: 20px; font-family: arial; color: #333; font-weight: normal; text-align: right; padding-bottom: 15px;">'.trans('messages.Date').'</td>
        <td width="150">&nbsp;</td>
        <td style="width: 50%; font-size: 20px; font-family: arial; color: #888; font-weight: normal; text-align: right; padding: 0 15px 15px;">'.date('d M, Y', strtotime($vendor_info[0]->created_date)).'</td>
        </tr>
        <tr>
        <td style="width: 50%; font-size: 20px; font-family: arial; color: #333; font-weight: normal; text-align: right;">'.trans('messages.Status').'</td>
        <td width="150">&nbsp;</td>
        <td style="width: 50%; font-size: 20px; font-family: arial; color: #888; font-weight: normal; text-align: right; padding: 0 15px;">'.$vendor_info[0]->name.'</td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        <tr>
        <td style="border-bottom: 1px solid #ccc; padding: 15px 15px;">
        <table border="0" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td style="font-size: 20px; font-weight: normal; color: #333; font-family: arial;">'.trans('messages.Bill details').'</td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        <tr>
        <td>
        <table border="0" cellspacing="0" cellpadding="0">
        <tbody>'.$item.'</tbody>
        </table>
        </td>
        </tr>
        <tr>
        <td>
        <table style="width: 100%;" border="0" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        
        <td style="padding-bottom: 15px; padding-top: 15px;" width="300">&nbsp;</td>
        
        <td style="font-size: 16px; color: #333; padding-bottom: 15px; padding-top: 15px; font-family: arial; text-align: right;">'.trans('messages.Subtotal').'</td>
        <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 15px 15px 15px;">'.$sub_total.getCurrency().'</td>
        </tr>'.$delivery_charge.'<tr>
        <td style="padding-bottom: 15px;" width="300">&nbsp;</td>
        <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding-bottom: 15px;">'.trans('messages.Tax').'</td>
        <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 0 15px 15px;">'.$delivery_details[0]->service_tax.getCurrency().'</td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        <tr>
        <td style="border-top: 1px solid #ccc; border-bottom: 1px solid #ccc; padding: 15px 0;">
        <table style="width: 100%;" border="0" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td style="font-size: 16px; color: #333; font-family: arial; text-align: right;" width="450">'.trans('messages.Total').'</td>
        <td style="font-size: 20px; color: #e91e63; font-family: arial; text-align: right; padding: 0 15px;">'.$delivery_details[0]->total_amount.getCurrency().'</td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        <tr>
        <td>
        <table style="width: 100%;" border="0" cellspacing="0" cellpadding="0">
        
        <tbody>
        
        '.$delivery_address.'
        <tr>
        
        <td style="font-size: 16px; color: #333; font-family: arial; text-align: left; padding: 15px 13px;">'.trans('messages.Payment mode').'</td>
        <td>:</td>
        <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 15px 13px;">'.$vendor_info[0]->payment_gateway_name.'</td>
        </tr>
        </tbody>
        
        </table>
        </td>
        </tr>
        <tr><td style="font-size: 10px; color: #333; font-family: arial; text-align: left; padding: 15px 13px;"><strong>'.trans('messages.Returns Policy:').'</strong>' .trans('messages.At '.Session::get("general")->site_name.' we try to deliver perfectly each and every time. But in the off-chance that you need to return the item, please do so with the ').'<strong>'.trans('messages.original Brand').'<strong></td></tr>
        <tr><td style="font-size: 10px; color: #333; font-family: arial; text-align: left; padding: 15px 13px;"><strong>'.trans('messages.box/price tag, original packing and invoice').'</strong> '.trans('messages.without which it will be really difficult for us to act on your request. Please help us in helping you. Terms and conditions apply').'</td></tr>
        </tbody>
        </table>';*/
        $site_name = ucfirst(Session::get("general")->site_name);
        $html = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><table width="700px" cellspacing="0" cellpadding="0" bgcolor="#fff" style="border:1px solid #ccc;">
        <tbody>
        <tr>
        <td style="border-bottom:1px solid #ccc;">
        <table style="padding-top: 25px; padding-bottom: 25px;" width="700px" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td width="20">&nbsp;</td>
        <td>
        <table>
        <tr>
        <td style="font-size:16px; font-weight:bold; font-family:Verdana; color:#000; padding-bottom:10px;">BILL FORM :</td>
        </tr>
        <tr>
        <td style="font-size:16px; font-weight:500; font-family:dejavu sans,arial; color:#666; line-height:28px;">'.ucfirst($vendor_info[0]->vendor_name).','.wordwrap(ucfirst($vendor_info[0]->contact_address),70,"<br>\n").'<br/>'.ucfirst($vendor_info[0]->contact_email).'</td>
        </tr>
        </table>
        </td>
        <td align="right"><a title="'.$site_name.'" href="'.url('/').'"><img src="'.$logo.'" alt="'.$site_name.'" /></a></td>
        <td width="20">&nbsp;</td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        <!-- end 1 tr -->
        <tr>
        <td>
        <table style="padding-top: 25px; padding-bottom: 25px;" width="700px" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td width="20">&nbsp;</td>
        <td colspan="4">
        <table>
        <tr>
        <td style="font-size:16px; font-weight:bold; font-family:Verdana; color:#000; padding-bottom:10px;">'.$delivery_type.'</td>
        </tr>
        <tr>
        <td style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;">'.wordwrap($delivery_address,70,"<br>\n").'
        <br/>'.$delivery_email.'</td>
        </tr>
        </table>
        </td>
        <td align="right">
        <table cellpadding="0" cellspacing="0">
        <tr>
        <td style="font-size:15px; font-weight:bold; font-family:Verdana; color:#000; line-height:28px;">Invoice</td>
        <td></td>
        <td align="left" style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;">'.$vendor_info[0]->invoice_id.'</td>
        </tr>
        <tr>
        <td style="font-size:15px; font-weight:bold; font-family:Verdana; color:#000; line-height:28px;">Delivery date</td>
        <td></td>
        <td align="left" style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;">'.date('F d, Y', strtotime($delivery_details[0]->delivery_date)).'</td>
        </tr>
        <tr>
        <td style="font-size:15px; font-weight:bold; font-family:Verdana; color:#000; line-height:28px;">Invoice date</td>
        <td></td>
        <td align="left" style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;">'.date('F d, Y', strtotime($vendor_info[0]->created_date)).'</td>
        </tr>
        <tr>
        <td style="font-size:11px; font-weight:bold; font-family:Verdana; color:#000; line-height:28px; background:#d1d5d4; padding:0 9px;">AMOUNT DUE</td>
        <td></td>
        <td align="left" style="font-size:16px; font-weight:500; font-family:dejavu sans,arial; color:#666; line-height:28px;background:#d1d5d4;padding:0 9px;">'.$total_amount.'</td>
        </tr>
        </table>
        </td>
        <td width="20">&nbsp;</td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        <!-- end 2 tr -->
        <tr>
        <td>
        <table cellpadding="0" cellspacing="0" width="100%">
        <tr style="background:#d1d5d4;padding:0 9px;">
        <td align="center" style=" padding:7px 0; font-size:17px; font-family:Verdana; font-weight:bold;">Item</th>
        <td align="center" style=" padding:7px 0;font-size:17px; font-family:Verdana; font-weight:bold;">Description</th>
        <td align="center" style=" padding:7px 0;font-size:17px; font-family:Verdana; font-weight:bold;">Quantity</th>
        <td align="center" style=" padding:7px 0;font-size:17px; font-family:Verdana; font-weight:bold;">Unit cost</th>
        <td align="center" style=" padding:7px 0;font-size:17px; font-family:Verdana; font-weight:bold;">Line total</th>
        </tr>'.$item.'
        </table>
        </td>
        </tr>
        <!-- end 3 tr -->
        <tr>
        <td>
        <table style="padding-top: 25px; padding-bottom: 25px;" width="787" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td width="20">&nbsp;</td>
        <td>
        <table>
        <tbody><tr>
        <td style="font-size:16px; font-weight:bold; font-family:Verdana; color:#000; padding-bottom:10px;">NOTES / MEMO :</td>
        </tr>
        <tr>
        <td style="font-size:16px; font-weight:500; font-family:dejavu sans,arial; color:#666; line-height:28px;">Free shipping with 30-day money-back guarntee </td>
        </tr>
        </tbody></table>
        </td>
        <td align="right">
        <table cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td style="font-size:15px; font-weight:bold; font-family:dejavu sans,arial; color:#000; line-height:28px;">SUBTOTAL</td>
        <td width="10"></td>
        <td style="font-size:16px; font-weight:500; font-family:dejavu sans,arial; color:#666; line-height:28px;" align="right">'.$sub_total.'</td>
        </tr>
        <tr>
        <td style="font-size:15px; font-weight:bold; font-family:dejavu sans,arial; color:#000; line-height:28px;">Delivery fee</td>
        <td width="10"></td>
        <td style="font-size:16px; font-weight:500; font-family:dejavu sans,arial; color:#666; line-height:28px;" align="right">'.$delivery_charge.'</td>
        </tr>
       <tr>
		<td style="font-size:15px; font-weight:bold; font-family:dejavu sans,arial; color:#000; line-height:28px;">Tax </td>
		<td width="10"></td>
		<td style="font-size:16px; font-weight:500; font-family:dejavu sans,arial; color:#666; line-height:28px;" align="right">'.$service_tax.'</td>
		</tr>
        <tr>
        <td style="font-size:15px; font-weight:bold; font-family:dejavu sans,arial; color:#000; line-height:28px; background:#d1d5d4; padding:0 9px;">TOTAL</td>
        <td style="background:#d1d5d4;padding:0 9px;" width="10"></td>
        <td style="font-size:16px; font-weight:500; font-family:dejavu sans,arial; color:#666; line-height:28px;background:#d1d5d4;padding:0 9px;" align="right">'.$total_amount.'</td>
        </tr>
        </tbody></table>
        </td>
        <td width="20">&nbsp;</td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        <tr>
        <td>
        <table>
        <tr>
        <td width="20">&nbsp;</td>
        <td style="font-size:12px; font-family:dejavu sans,arial; color:#666;padding:10px 10px 0 0;direction:rtl; text-alignment:right;"><b style="font-family: dejavu sans,arial; font-weight: bold;">'.trans('messages.Returns Policy: ').'</b>' .trans('messages.At Oddappz we try to deliver perfectly each and every time. But in the off-chance that you need to return the item, please do so with the').'<b style="font-family: dejavu sans,arial; font-weight: bold;">'.trans('messages.original Brand').trans('messages.box/price tag, original packing and invoice').'</b> '.trans('messages.without which it will be really difficult for us to act on your request. Please help us in helping you. Terms and conditions apply').'</td>
        <td width="20">&nbsp;</td>
        </tr>
        </tbody>
        </table>';/*<tr>
        <td>
        <table>
        <tr>
        <td width="20">&nbsp;</td>
        <td style="font-size:13px; padding:10px 0 0 0"><b style="font-size: 17px;font-family: arial;
    font-weight: bold;">fhfhgfghfhgfhgfgfhgfgfgfhfgfgh</b> htttryttyutu yttyutuytyutuytuyt yutytyutuytutyut</td>
        <td width="20">&nbsp;</td>
        </tr>
        <tr>
        <td width="20">&nbsp;</td>
        <td style="padding:10px 0 20px 0;">fhfhgfghfhgfhgfgfhgfgfgfhfgfgh htttryttyutu yttyutuytyutuytuyt yutytyutuytutyut</td>
        <td width="20">&nbsp;</td>
        </tr>
        <tr height="15"></tr>
        </table>
        </td>
        </tr>*/
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($html);//->save(base_path().'/public/assets/front/'.Session::get("general")->theme.'/images/invoice/invoice.pdf');
        //$file = base_path().'/public/assets/front/'.Session::get("general")->theme.'/images/invoice/'.$vendor_info[0]->order_key_formated.'.pdf';
        //$pdf->loadFile($file);
        return $pdf->stream('invoice.pdf',array('Attachment'=>0));
        //return $pdf->download('invoice.pdf');
        
    }

    public function return_order(Request $data)
    {
        $user_details = $this->check_login();
        $post_data = $data->all();
        $post_data['token'] = Session::get('token');
        $post_data['user_id'] = Session::get('user_id');    
        $data = array('form_params' => $post_data);
        $response = $this->api->call_api($data,'api/return_order','POST');
        //Session::flash('message-success', $response->response->Message);
        return response()->json($response->response);
    }
    
    
}
