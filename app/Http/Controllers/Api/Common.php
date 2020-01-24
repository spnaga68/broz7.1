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

class Common extends Controller
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
    public function get_payment_gateways($language_id)
    {
        //echo $language_id;
        $query = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.$language_id.' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN '.$language_id.' ELSE 1 END)';
        $gateways = DB::table('payment_gateways')
                ->select('*','payment_gateways.id as payment_gateway_id')
                ->leftJoin('payment_gateways_info','payment_gateways_info.payment_id','=','payment_gateways.id')
                ->orderBy('payment_gateways.id', 'desc')
                 ->where('active_status',"=",1)
                ->whereRaw($query)
                ->get();
        //print_r($gateways);exit;
        return $gateways;
    }
    public function languages()
    {
        $module_settings_data = modules_list();
        $languages = DB::table('languages')->select('name','language_code','is_rtl','created_at','id')->where('status', 1)->orderby("languages.id","asc")->get();
        $language = getCurrentLang();
$cquery = '"currencies_infos"."language_id" = (case when (select count(*) as totalcount from currencies_infos where currencies_infos.language_id = '. $language.' and currencies.id = currencies_infos.currency_id) > 0 THEN '. $language.' ELSE 1 END)';
        $squery = '"settings_infos"."language_id" = (case when (select count(settings_infos.language_id) as totalcount from settings_infos where settings_infos.language_id = '.$language.' and settings.id = settings_infos.id) > 0 THEN '.$language.' ELSE 1 END)';
        $general_settings = DB::table('settings')
							->select('settings_infos.site_name','default_language','default_country','contact_address','settings_infos.copyrights','site_owner','email','telephone','fax','geocode','settings_infos.site_description','default_currency','currency_side')
							->whereRaw($squery)
							->leftJoin('settings_infos','settings_infos.id','=','settings.id')
							->first();

							
        $social_settings = DB::table('socialmediasettings')->select('facebook_page','twitter_page','linkedin_page')->first();
         $delivery_settings = DB::table('delivery_settings') ->first();
        $currency_list = DB::table('currencies')
                        ->select('id','currencies_infos.currency_name','currencies_infos.language_id','currencies_infos.currency_symbol')
                        ->leftjoin('currencies_infos','currencies_infos.currency_id','=','currencies.id')
                       // ->whereRaw($cquery)
                        ->get();

             $currency =array();           
				if(count($currency_list)>0)
				{    foreach($currency_list as $key=>$cur)
					{  
                                              if($cur->language_id == 1){
						$currency[$key]['id'] = $cur->id; 
						$currency[$key]['currency_symbol'] = $cur->currency_symbol; 
						$currency[$key]['currency_name'] = $cur->currency_name; 
					    }
					    if($cur->language_id == 2){
						$currency[$key]['id'] = $cur->id; 
						$currency[$key]['currency_symbol_arabic'] = $cur->currency_symbol; 
						$currency[$key]['currency_name_arabic'] = $cur->currency_name; 
					    }
					    
					}
	
				}
//print_r($currency);exit;
        $result = array("response" => array("httpCode" => 200, "status" => true, 'languages' => $languages, 'modules_list' => $module_settings_data, "general_settings" => $general_settings, "social_settings" => $social_settings,"currency_list"=> $currency,"delivery_settings"=> $delivery_settings));
        return json_encode($result);
    }
    public function payment_gateway_list(Request $data)
    {
        //echo $language_id;
        $post_data = $data->all();
        $language_id = $post_data["language"];
        $query = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.$language_id.' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN '.$language_id.' ELSE 1 END)';
        $payment_detail = DB::table('payment_gateways')
                ->select('*','payment_gateways.id as payment_gateway_id')
                ->leftJoin('payment_gateways_info','payment_gateways_info.payment_id','=','payment_gateways.id')
                ->orderBy('payment_gateways.id', 'desc')
                 ->where('active_status',"=",1)
                ->whereRaw($query)
                ->get();
        //print_r($gateways);exit;
        if(count($payment_detail)>0)
        {
            foreach($payment_detail as $k=>$v)
            {
                $payment_detail[$k]->currency_id = ($payment_detail[$k]->currency_id!='')?$payment_detail[$k]->currency_id:'';
                $payment_detail[$k]->image = ($payment_detail[$k]->image!='')?$payment_detail[$k]->image:'';
            }
        }
        $result = array("response" => array("httpCode" => 200 , "Message" => trans("messages.Payment detail"), "payment_detail"=>$payment_detail));
        return json_encode($result,JSON_UNESCAPED_UNICODE);
	}
	public function currency_converter(Request $data)
    { 
       $data_all = $data->all();
        $rules = [
            'user_id'         => ['required'],
            'token'           => ['required'],
            'currency_amount' => ['required'],
            'from_currency'   => ['required'],
        ];
        $errors = $result = array();

        $validator = app('validator')->make($data->all(), $rules);
        if ($validator->fails()) 
        {
            foreach( $validator->errors()->messages() as $key => $value) 
            {
                $errors[] = is_array($value)?implode( ',',$value ):$value;
            }
            $errors = implode( ", \n ", $errors );
            $result = array("response" => array("httpCode" => 400, "Status" => "Failure", "Message" => $errors, "Error" => trans("messages.Error List")));
        }
        else {
            try {
                $check_auth      = JWTAuth::toUser($data_all['token']);
                $currency_amount = $data->currency_amount;
                $from_currency   = $data->from_currency;
                $to_currency     = 'USD';
                $amount          = urlencode($currency_amount);
                $from_currency   = urlencode($from_currency);
                $to_currency     = urlencode($to_currency);
 $request ='https://free.currencyconverterapi.com/api/v5/convert?q='.$from_currency.'_'.$to_currency.'&compact=y&callback=jQuery203017817078931262786_1521607731806&_=1521607731810';
                $file_contents = file_get_contents($request);
                    if(!empty($file_contents))
                    {
                        $file_contents = explode(':',$file_contents);
                        $result = substr($file_contents[2], 0, 5);
                        if(!empty($result))
                        {
                            $converted_amount = $result*$amount;
                            $result = array("response" => array("httpCode" => 200, "status" => "Success", "Message" => trans("messages.Currency converted successfully"), "converted_amount" => $converted_amount, "to_currency" => 'USD'));
                        }
                        else
                        {
                            $result = array("response" => array("httpCode" => 400, "status" => "Failure", "Message" => trans("messages.Currency converted failed")));

                        }
                    }
                    else {
                        $result = array("response" => array("httpCode" => 400, "status" => "Failure", "Message" => trans("messages.Currency converted failed")));
                    }
            }
            catch(JWTException $e) {
                $result = array("response" => array("httpCode" => 400, "status" => "Failure", "Message" => trans("messages.Kindly check the user credentials")));
            }
            catch(TokenExpiredException $e) {
                $result = array("response" => array("httpCode" => 400, "status" => "Failure", "Message" => trans("messages.Kindly check the user credentials")));
            }
        }
        return json_encode($result);
    }
    public function mob_faq($language)
	{
		
		$query = 'cms_infos.language_id = (case when (select count(cms_infos.language_id) as totalcount from cms_infos where cms_infos.language_id = '.$language.' and cms.id = cms_infos.cms_id) > 0 THEN '.$language.' ELSE 1 END)';
		$cms = DB::table('cms')->select('cms.id','cms.url_index','cms.sort_order','cms_infos.title')
			->leftJoin('cms_infos','cms_infos.cms_id','=','cms.id')
			->whereRaw($query)
			->where('cms.cms_type','=',2)
			->where('cms.cms_status','=',1)
			//->where('cms.url_index','=',$index)
			->orderBy('cms.sort_order', 'asc')
			->get();
			$cms_items=array();
			
				if(count($cms)){
                    $result = array("response" => array("httpCode" => 200, "status" => true,'data'=>$cms,'Message' => trans('messages.Faq list')));
                }
		
			return json_encode($result);
			
	}
	public function banners()
	{ 
        
		$banners = DB::table('banner_settings')->select('banner_settings.banner_setting_id','banner_settings.banner_title','banner_settings.banner_subtitle','banner_settings.banner_image','banner_settings.banner_link')->where('banner_type', 2)->where('status', 1)->orderBy('default_banner', 'desc')->get();

		if(count($banners)>0)
		{   

			foreach($banners as $ban=>$items)
			{
			$banner_image = URL::asset('assets/admin/base/images/no_image.png');
			if(file_exists(base_path().'/public/assets/admin/base/images/banner/'.$items->banner_image) && $items->banner_image != '')
			{
			$banner_image = url('/assets/admin/base/images/banner/'.$items->banner_image);
			}
			$banners[$ban]->banner_image = $banner_image;
			}   
			$result = array("response" => array("httpCode" => 200 , "Message" => trans("messages.Banner details"),"banners"=> $banners));	 
		}
		else 
		{
		    $result = array("response" => array("httpCode" => 400 , "Message" => trans("messages.Invalid banner"), "banners"=>$banners));

		}
		return json_encode($result);    
    }
	
}
