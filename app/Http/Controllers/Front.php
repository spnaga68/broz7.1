<?php

namespace App\Http\Controllers;

use App;
use App\Model\api;
use App\Model\cms;
use App\Model\contactus;
use App\Model\users;
use DB;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
//use JWTAuth;
use Mail;
use OpenGraph;
use SEOMeta;
use Session;
use Socialite;
use Twitter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Text;
use Illuminate\Support\Facades\Validator;
//~ DB::enableQueryLog();
use App\Model\telr_payment;
use App\Model\customer_cores;
use App\Model\payment_history;
//use Services_Twilio;
//use Twilio\Rest\Client;

use Tymon\JWTAuth\Facades\JWTAuth;

class Front extends Controller {
	const USERS_SIGNUP_EMAIL_TEMPLATE = 1;
	const USERS_WELCOME_EMAIL_TEMPLATE = 3;
	const COMMON_MAIL_TEMPLATE = 8;
	const ADMIN_MAIL_TEMPLATE_CONTACT = 13;
	const USER_MAIL_TEMPLATE_CONTACT = 15;
	const VENDORS_REGISTER_EMAIL_TEMPLATE = 4;
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */

	public function __construct() {
		$this->site_name = isset(getAppConfig()->site_name) ? ucfirst(getAppConfig()->site_name) : '';
		//~ print_r(getAppConfig()->site_name);exit;
		$this->client = new Client([
			// Base URI is used with relative requests
			'base_uri' => url('/'),
			// You can set any number of default request options.
			'timeout' => 3000.0,
		]);
		$this->theme = Session::get("general")->theme;
		$this->api = New Api;
		$this->telrManager = new \TelrGateway\TelrManager();

	}

	public function check_login() {
		$user_details = array();
		$user_id = Session::get('user_id');
		$token = Session::get('token');
		if ($user_id != "") {
			$user_array = array("user_id" => $user_id, "token" => $token);
			$method = "POST";
			$data = array('form_params' => $user_array);
			$response = $this->api->call_api($data, 'api/user_detail', $method);
			$user_details = $response->response->user_data[0];
		}
		return $user_details;
	}

	public function chcek()
	{
		$user_id = Session::get('user_id');
        $error = $result = array();
        $user = get_user_details($user_id);
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
           return $user;
            }
		}

	public function home() {
		//print_r("1");exit();
		$user_details = $this->chcek();
		//print_r($user_details);exit();
		SEOMeta::setTitle($this->site_name);
		SEOMeta::setDescription($this->site_name);
		SEOMeta::addKeyword($this->site_name);
		OpenGraph::setTitle($this->site_name);
		OpenGraph::setDescription($this->site_name);
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle($this->site_name);
		Twitter::setSite('@' . $this->site_name);
		//return view('front.' . $this->theme . '.home')->with("user_details", $user_details);
		return view('front.' . $this->theme . '.home_front')->with("user_details", $user_details);

	}

	public function dynamics() {
		$user_details = $this->chcek();
		SEOMeta::setTitle($this->site_name);
		SEOMeta::setDescription($this->site_name);
		SEOMeta::addKeyword($this->site_name);
		OpenGraph::setTitle($this->site_name);
		OpenGraph::setDescription($this->site_name);
		OpenGraph::setUrl(URL::to('/dynamic'));
		Twitter::setTitle($this->site_name);
		Twitter::setSite('@' . $this->site_name);
		return view('front.' . $this->theme . '.dynamic')->with("user_details", $user_details);

	}


	public function table() {
		$user_details = $this->chcek();
		SEOMeta::setTitle($this->site_name);
		SEOMeta::setDescription($this->site_name);
		SEOMeta::addKeyword($this->site_name);
		OpenGraph::setTitle($this->site_name);
		OpenGraph::setDescription($this->site_name);
		OpenGraph::setUrl(URL::to('/table'));
		Twitter::setTitle($this->site_name);
		Twitter::setSite('@' . $this->site_name);
		return view('front.' . $this->theme . '.table')->with("user_details", $user_details);

	}

	public function forgot_password(Request $data) {
		$api = New Api;
		$post_array = $data->all();
		$user_array = array("phoneNumber" => $post_array['phone_number'],"countryCode" => $post_array['countryCode'], "language" => getCurrentLang());
		$method = "POST";
		$data = array('form_params' => $user_array);

		$response = $api->call_api($data, 'api/mforgotPassword', $method);

		if ($response->status == '1') {

			return response()->json($response);
		} else {
			return response()->json($response);
		}

	}

    public function forgotOtp(Request $data) {
        $api = New Api;
        $post_array = $data->all();
        $user_array = array("otpUnique" => $post_array['otp'],"language" => "en","otp"=> $post_array['otp']);
        $method = "POST";
        $data = array('form_params' => $user_array);

        $response = $api->call_api($data, 'api/mforgotOtp', $method);

        if ($response->status == '1') {

            return response()->json($response);
        } else {
            return  response()->json($response);
        }
    }

    public function mupdateNewPassword(Request $data) {
        $api = New Api;
        $post_array = $data->all();
        $user_array = array("phoneNumber" => $post_array['phone_number'],"countryCode" => $post_array['countryCode'], "language" => getCurrentLang());
        $method = "POST";
        $data = array('form_params' => $user_array);

        $response = $api->call_api($data, 'api/mforgotPassword', $method);

        if ($response->status == '1') {

            return 1;
        } else {
            return 2;
        }
    }

	public function login_user(Request $data,Controllername $login ) {
		$api = New Api;
		$post_array = $data->all();



		$login->userLogin($post_array);



		$user_array = array("email" => $post_array['email'], "password" => $post_array['password'], "login_type" => 1, "user_type" => 3, "language" => getCurrentLang());
//		$method = "POST";
//		$data = array('form_params' => $user_array);
//
//		$response = $model->call_api($data, 'api/login_user', $method);
		dd($response);

		if ($response->response->httpCode == '200') {
		
			Session::put('user_id', $response->response->user_id);
			Session::put('mobile', $response->response->mobile);
			Session::put('email', $response->response->email);
			Session::put('first_name', $response->response->first_name);
			Session::put('last_name', $response->response->last_name);
			Session::put('name', $response->response->name);
			Session::put('social_title', $response->response->social_title);
			Session::put('profile_image', $response->response->image);
			Session::put('token', $response->response->token);
			return response()->json($response->response);
		} else {
			return response()->json($response->response);
		}
	}

	public function signup_user(Request $data) {
		$api = New Api;
		$user_array = $data->all();
		$url = url("/api/signup_user");
		$method = "POST";
		$user_array["login_type"] = 1;
		$user_array["language"] = getCurrentLang();
		$data = array('form_params' => $user_array);
		$response = $api->call_api($data, $url, $method);

		if ($response->response->httpCode == '200') {
			return response()->json($response->response);
		} else {
			return response()->json($response->response);
		}
	}
	public function store_register_user(Request $data) {
		$api = New Api;
		$user_array = $data->all();
		$url = url("/api/store_register_user");
		$method = "POST";
		$user_array["login_type"] = 1;
		$user_array["language"] = getCurrentLang();
		$data = array('form_params' => $user_array);
		$response = $api->call_api($data, $url, $method);

		if ($response->response->httpCode == '200') {
			return response()->json($response->response);
		} else {
			return response()->json($response->response);
		}
	}
	public function redirectToProvider() {
		return Socialite::driver('facebook')->redirect();
	}

	public function handleProviderCallback() {

		$user = Socialite::driver('facebook')->stateless()->user();
		//  print_r($user);exit;
		$gender = '';
		$name = $user["name"];
		$fb_token = $user->token;
		$name_splitted = explode(" ", $name);
		$first_name = isset($name_splitted[0]) ? $name_splitted[0] : "";
		$last_name = isset($name_splitted[1]) ? $name_splitted[1] : "";
		$email = isset($user["email"]) ? $user["email"] : "";
		if (!empty($user["gender"])) {
			$gender = ($user["gender"] == "male") ? "M" : "F";
		}
		$facebook_id = $user["id"];
		$api = New Api;
		$user_array = array("name" => $name, "first_name" => $first_name, "last_name" => $last_name, "email" => $email, "gender" => $gender, "login_type" => 1, "facebook_id" => $facebook_id, "image_url" => $user->getAvatar(), "language" => getCurrentLang());
		//print_r($user_array); exit;
		$url = url("/api/signup_fb_user");
		$method = "POST";
		$data = array('form_params' => $user_array);
		$response = $api->call_api($data, $url, $method);
		// print_r($response->response->Message);exit;
		if ($response->response->status == false) {
			//echo "if1";exit;
			Session::flash('message', $response->response->Message);
			return Redirect::to('/');
		} else if ($response->response->status == true) {
			//echo "elseif";exit;
			Session::flash('message', $response->response->Message);
			Session::put('user_id', $response->response->user_id);
			Session::put('mobile', $response->response->mobile);
			Session::put('email', $response->response->email);
			Session::put('first_name', $response->response->first_name);
			Session::put('last_name', $response->response->last_name);
			Session::put('name', $response->response->name);
			Session::put('social_title', $response->response->social_title);
			Session::put('profile_image', $response->response->image);
			Session::put('token', $response->response->token);
			Session::put('facebook_id', $response->response->facebook_id);
			Session::put('fb_token', $fb_token);
			if ($response->response->email == "" || $response->response->mobile == "") {
				return Redirect::to('/profile');
			}
			return Redirect::to('/');
		} else {
			//echo "else";exit;
			Session::flash('message', $response->response->Message);
			return Redirect::to('/');
		}

	}

	public function welcome($index = '') {
		SEOMeta::setTitle(Session::get('general_site')->site_name);
		SEOMeta::setDescription(Session::get('general_site')->site_name);
		SEOMeta::addKeyword(Session::get('general_site')->site_name);
		OpenGraph::setTitle(Session::get('general_site')->site_name);
		OpenGraph::setDescription(Session::get('general_site')->site_name);
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get('general_site')->site_name);
		Twitter::setSite(Session::get('general_site')->site_name);
		return view('front.' . $this->theme . '.welcome');
	}
	/** Signup Email Confirmation **/
	public function signup_confirmation($verifictaion_key, $email, $password) {

		$api = New Api;
		if ($verifictaion_key && $email) {
			$user_array = array("key" => $verifictaion_key, "email" => $email, "u_password" => $password, "language" => getCurrentLang());
			$url = url("/api/signup_confirmation");
			$method = "POST";
			$data = array('form_params' => $user_array);
			$response = $api->call_api($data, $url, $method);
			if ($response->response->httpCode == '200') {
				Session::put('user_id', $response->response->user_id);
				Session::put('mobile', $response->response->mobile);
				Session::put('email', $response->response->email);
				Session::put('first_name', $response->response->first_name);
				Session::put('last_name', $response->response->last_name);
				Session::put('name', $response->response->name);
				Session::put('social_title', $response->response->social_title);
				Session::put('profile_image', $response->response->image);
				Session::put('token', $response->response->token);
				Session::flash('message', $response->response->Message);
				return Redirect::to('/');
			} else {
				Session::flash('message', $response->response->Message);
				return Redirect::to('/');
			}

		}
	}

	public function aboutus($index = '') {
		//print_r(Session::get("general_site")->site_name);exit;
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'About us');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'About us');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'About us');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'About us');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'About us');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'About us');
		Twitter::setSite(Session::get("general_site")->site_name);
		$query = '"blog_infos"."language_id" = (case when (select count(*) as totalcount from blog_infos where blog_infos.language_id = ' . getCurrentLang() . ' and blogs.id = blog_infos.blog_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
		$blogs = DB::table('blogs')
			->select('blogs.*', 'blog_infos.*')
			->leftJoin('blog_infos', 'blog_infos.blog_id', '=', 'blogs.id')
			->whereRaw($query)
			->orderBy('blogs.id', 'asc')
			->paginate(3);
		$type = 2;
		$query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = ' . getCurrentLang() . ' and categories.id = categories_infos.category_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
		$category = DB::table('categories')
			->select('categories.*', 'categories_infos.*')
			->leftJoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
			->whereRaw($query)
			->where("categories.category_type", "=", $type)
			->orderBy('categories_infos.category_name', 'asc')
			->get();
		return view('front.' . $this->theme . '.aboutus')->with('blog', $blogs)->with('category', $category);
	}

	public function weare_hiring() {
		// Section description
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'Weare hiring');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'Weare hiring');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'Weare hiring');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'Weare hiring');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'Weare hiring');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'Weare hiring');
		Twitter::setSite(Session::get("general_site")->site_name);
		return view('front.' . $this->theme . '.weare_hiring');
	}

	public function product_info() {
		// Section description
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'Product info');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'Product info');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'Product info');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'Product info');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'Product info');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'Product info');
		Twitter::setSite(Session::get("general_site")->site_name);
		return view('front.' . $this->theme . '.product_info');
	}

	public function sitmap() {
		// Section description
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'Sitemap');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'Sitemap');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'Sitemap');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'Sitemap');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'Sitemap');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'Sitemap');
		Twitter::setSite(Session::get("general_site")->site_name);
		return view('front.' . $this->theme . '.sitmap');
	}

	public function press_contact() {
		// Section description
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'press contact');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'press contact');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'press contact');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'press contact');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'press contact');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'press contact');
		Twitter::setSite(Session::get("general_site")->site_name);
		return view('front.' . $this->theme . '.press_contact');
	}
	public function ourservice_areas() {
		// Section description
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'our service areas');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'our service areas');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'our service areas');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'our service areas');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'our service areas');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'our service areas');
		Twitter::setSite(Session::get("general_site")->site_name);
		return view('front.' . $this->theme . '.ourservice_areas');
	}

	public function portfolios($index = "") {
		SEOMeta::setTitle(getAppConfig()->site_name);
		SEOMeta::setDescription(getAppConfig()->site_name);
		SEOMeta::addKeyword(getAppConfig()->site_name);
		OpenGraph::setTitle(getAppConfig()->site_name);
		OpenGraph::setDescription(getAppConfig()->site_name);
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(getAppConfig()->site_name);
		Twitter::setSite(getAppConfig()->site_name);
		// Section description
		if (!$index) {
			$portfolios = DB::table('portfolios')->orderBy('id', 'desc')->get(); //->paginate(20);
		} else {
			$type = 2;
			$category_id = DB::select('select id from  categories where category_type = "' . $type . '" and url_key = "' . $index . '" limit 1');
			if (count($category_id)) {
				$portfolios = DB::select('select * from  portfolios where FIND_IN_SET("' . $category_id[0]->id . '",category_ids) > 0 order by id desc');
			} else {
				return Redirect::to('/portfolios');
			}
		}
		// load the view and list the portfolios
		$type = 2;
		$category = DB::select('select     id,category_name,url_key from  categories where category_type = "' . $type . '" and category_status = 1 order by id desc limit 3');

		$category1 = DB::select('select id,category_name,url_key from  categories where category_type = "' . $type . '" and category_status = 1 order by id desc');
		return view('front.' . $this->theme . '.portfolios')->with('portfolio', $portfolios)->with('category', $category)->with('category1', $category1);
	}

	public function portfolios_info($index) {
		$portfolios = DB::select('select * from portfolios where portfolio_index = "' . $index . '"');
		if (!count($portfolios)) {
			Session::flash('message', 'Invalid Portfolio');
			Session::flash('alert-class', 'alert-danger');
			return Redirect::to('/');
		}
		SEOMeta::setTitle($portfolios[0]->title);
		SEOMeta::setDescription($portfolios[0]->short_notes);
		SEOMeta::addKeyword('portfolio, Nextbrain portfolio, ' . $portfolios[0]->title . '');
		OpenGraph::setTitle($portfolios[0]->title);
		OpenGraph::setDescription($portfolios[0]->short_notes);
		OpenGraph::setUrl('Solutek');
		Twitter::setTitle($portfolios[0]->title);
		// Section description
		$type = 2;
		$category = DB::select('select     id,category_name from  categories where category_type = "' . $type . '" and category_status = 1 order by id desc');
		return view('front.' . $this->theme . '.portfolios_info')->with('portfolio', $portfolios)->with('category', $category);
	}

	public function contactus() {
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'Contact us');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'Contact us');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'Contact us');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'Contact us');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'Contact us');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'Contact us');
		Twitter::setSite(Session::get("general_site")->site_name);
		// Section description
		return view('front.' . $this->theme . '.contactus');
	}

	/**
	 * Store contactus in storage.
	 *
	 * @return Response
	 */
	/* public function storecontact(Request $data)
		    {

		        //print_r($data->all());exit;

		        // validate
		        // read more on validation at http://laravel.com/docs/validation
		        $validation = Validator::make($data->all(), array(

		            'name' => ['required','alpha', 'max:56'],
		            'email'      => ['required', 'email', 'max:250'],
		            'mobile_number'      => ['required','regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/'],
		            'message' => 'required',
		            'city' => 'required',
		            'enquery_type' => 'required',
		            //'captcha' => 'required|valid_captcha',
		        ));

		        // process the validation
		        if ($validation->fails()) {
		                //echo "asdfasdf";exit;
		                //return redirect('/contact-us')->withErrors($validation);
		                return Redirect::back()->withErrors($validation)->withInput();
		        } else {

		            //echo "asdf";exit;
		            // store
		            $contactus = new Contactus;
		            $contactus->name      = $_POST['name'];
		            $contactus->email    = $_POST['email'];
		            $contactus->phone_number    = $_POST['mobile_number'];
		            $contactus->message    = $_POST['message'];
		            $contactus->enquery_type    = $_POST['enquery_type'];
		            $contactus->city_id    = $_POST['city'];
		            $contactus->created_at = date("Y-m-d H:i:s");
		            $contactus->save();

		            $to=getAppConfigEmail()->support_mail;
		            $email = $_POST['email'];
		            $subject = "Contact request from ". getAppConfig()->site_name." by ".$_POST['name'];
		            $content = $_POST['message'];
		            $template=DB::table('email_templates')
		            ->select('*')
		            ->where('template_id','=',self::ADMIN_MAIL_TEMPLATE_CONTACT)
		            ->get();
		            if(count($template)){
		                $from = $template[0]->from_email;
		                $from_name=$template[0]->from;
		                //$subject = $template[0]->subject;
		                if(!$template[0]->template_id){
		                    $template = 'mail_template';
		                    $from=getAppConfigEmail()->contact_mail;
		                    $subject = "Welcome to ".getAppConfig()->site_name;
		                    $from_name="";
		                }
		                $content =array("notification" => array('name' => $_POST['name'],'email' => $_POST['email'],'message' => $_POST['message'],'mobile' => $_POST['mobile_number']));
		                $email=smtp($from,$from_name,$to,$subject,$content,$template);
		            }
		            $to=$_POST['email'];
		            $email = $_POST['email'];
		            $subject = "Contact request reply from ". getAppConfig()->site_name;
		            $content = $_POST['message'];
		            $template=DB::table('email_templates')
		            ->select('*')
		            ->where('template_id','=',self::USER_MAIL_TEMPLATE_CONTACT)
		            ->get();
		            if(count($template)){
		                $from = $template[0]->from_email;
		                $from_name=$template[0]->from;
		                //$subject = $template[0]->subject;
		                if(!$template[0]->template_id){
		                    $template = 'mail_template';
		                    $from=getAppConfigEmail()->contact_mail;
		                    $subject = "Welcome to ".getAppConfig()->site_name;
		                    $from_name="";
		                }
		                $content =array("notification" => array('name' => $_POST['name'],'email' => $_POST['email'],'message' => $_POST['message'],'mobile' => $_POST['mobile_number']));
		                $email=smtp($from,$from_name,$to,$subject,$content,$template);
		            }
		            Session::flash('message', 'Your request has been posted successfully');
		            return Redirect::to('/contact-us');
		        }
	*/
	public function storecontact(Request $data) {

		if (isset($post_data['language']) && $post_data['language'] == 2) {
			App::setLocale('es');
		} else {
			App::setLocale('en');
		}
		$method = "POST";
		$post_data = $data->all();
		//print_r($post_data);exit;
		$url = url("/api/store_contact");
		$data = array('form_params' => $post_data);
		$response = $this->api->call_api($data, $url, $method);
		return response()->json($response->response);
	}

	public function blog($index = '') {
		$url_index = Input::get('filter');
		$keyword = Input::get('keyword');
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'Blog');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'Blog');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'Blog');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'Blog');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'Blog');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'Blog');
		Twitter::setSite(Session::get("general_site")->site_name);
		$type = 3;
		$category_id = '';
		if ($url_index) {
			$category_id = DB::select("select id from  categories where category_type = $type and url_key ='" . $url_index . "' limit 1");
		}
		$query = '"blog_infos"."language_id" = (case when (select count(*) as totalcount from blog_infos where blog_infos.language_id = ' . getCurrentLang() . ' and blogs.id = blog_infos.blog_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
		$condtion = 'blogs.status = 1';
		if ($category_id) {
			$c_ids = $category_id[0]->id;
			$condtion .= " and (regexp_split_to_array(category_ids,',')::integer[] @> '{" . $c_ids . "}'::integer[]  and category_ids !='')";
		}
		if ($keyword) {
			$condtion .= " and blog_infos.title ILIKE '%" . $keyword . "%'";
		}
		$blogs = DB::table('blogs')
			->select('blogs.*', 'blog_infos.*')
			->leftJoin('blog_infos', 'blog_infos.blog_id', '=', 'blogs.id')
			->whereRaw($query)
			->whereRaw($condtion)
			->orderBy('blogs.id', 'asc')
			->get();

		$query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = ' . getCurrentLang() . ' and categories.id = categories_infos.category_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
		$category = DB::table('categories')
			->select('categories.*', 'categories_infos.*')
			->leftJoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
			->whereRaw($query)
			->where("categories.category_type", "=", $type)
			->orderBy('categories_infos.category_name', 'asc')
			->get();
		return view('front.' . $this->theme . '.blog')->with('blog', $blogs)->with('category', $category);
	}

	public function blog_info($index) {

		$query = '"blog_infos"."language_id" = (case when (select count(*) as totalcount from blog_infos where blog_infos.language_id = ' . getCurrentLang() . ' and blogs.id = blog_infos.blog_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
		$blog = DB::table('blogs')
			->select('blogs.*', 'blog_infos.*')
			->leftJoin('blog_infos', 'blog_infos.blog_id', '=', 'blogs.id')
			->whereRaw($query)
			->where("url_index", "=", $index)
			->get();
		if (!count($blog)) {
			Session::flash('message', 'Invalid Blog');
			Session::flash('alert-class', 'alert-danger');
			return Redirect::to('/');
		}
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . $blog[0]->title);
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . $blog[0]->short_notes);
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . $blog[0]->title . '');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . $blog[0]->title);
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . $blog[0]->short_notes);
		OpenGraph::setUrl(Url::to('/'));
		Twitter::setTitle($blog[0]->title);
		Twitter::setSite(Session::get("general_site")->site_name);
		DB::table('blogs')->where('url_index', $index)->increment('view_count');
		// Section description
		$type = 2;
		$query1 = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = ' . getCurrentLang() . ' and categories.id = categories_infos.category_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
		$category = DB::table('categories')
			->select('categories.*', 'categories_infos.*')
			->leftJoin('categories_infos', 'categories_infos.category_id', '=', 'categories.id')
			->whereRaw($query1)
			->where("categories.category_type", "=", $type)
			->orderBy('categories_infos.category_name', 'asc')
			->get();
		$tre_ids = $blog[0]->category_ids;
		$condtion = "(regexp_split_to_array(category_ids,',')::integer[] @> '{" . $tre_ids . "}'::integer[]  and category_ids !='')";
		$related_blog = DB::table('blogs')
			->select('blogs.*', 'blog_infos.*')
			->leftJoin('blog_infos', 'blog_infos.blog_id', '=', 'blogs.id')
			->whereRaw($query)
			->whereRaw($condtion)
			->where("url_index", "!=", $index)
			->limit(3)
			->get();
		$users = Users::find($blog[0]->created_by);
		return view('front.' . $this->theme . '.blog_info')->with('blog', $blog[0])->with('category', $category)->with('related_blog', $related_blog)->with('users', $users);
	}

	public function offer() {
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'Offers');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'Offers');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'Offers');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'Offers');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'Offers');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'Offers');
		Twitter::setSite(Session::get("general_site")->site_name . ' - ' . 'Offers');
		// Section description
		return view('front.' . $this->theme . '.offer');
	}

	public function register_your_store() {
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'Register your store');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'Register your store');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'Register your store');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'Register your store');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'Register your store');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'Register your store');
		Twitter::setSite(Session::get("general_site")->site_name);
		// Section description
		return view('front.' . $this->theme . '.register_your_store');
	}

	public function cms($index = "") {
		$query = '"cms_infos"."language_id" = (case when (select count(*) as totalcount from cms_infos where cms_infos.language_id = ' . getCurrentLang() . ' and cms.id = cms_infos.cms_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
		$cms_info = DB::table('cms')
			->select('cms.*', 'cms_infos.*')
			->leftJoin('cms_infos', 'cms_infos.cms_id', '=', 'cms.id')
			->whereRaw($query)
			->where("url_index", "=", $index)
			->limit(1)
			->get();
		if (!count($cms_info)) {
			Session::flash('message', 'Invalid Page');
			Session::flash('alert-class', 'alert-danger');
			return Redirect::to('/');
		}
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . $cms_info[0]->title);
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . $cms_info[0]->title);
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . $cms_info[0]->title);
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . $cms_info[0]->title);
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . $cms_info[0]->title);
		// OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . $cms_info[0]->title);
		Twitter::setSite(Session::get("general_site")->site_name);
		return view('front.' . $this->theme . '.cms')->with('cmsinfo', $cms_info);
	}
	public function cms_mob($index, $language) {
		$query = '"cms_infos"."language_id" = (case when (select count(*) as totalcount from cms_infos where cms_infos.language_id = ' . $language . ' and cms.id = cms_infos.cms_id) > 0 THEN ' . $language . ' ELSE 1 END)';
		$cms_info = DB::table('cms')
			->select('cms_infos.title', 'cms_infos.content', 'cms.id', 'cms.url_index')
			->leftJoin('cms_infos', 'cms_infos.cms_id', '=', 'cms.id')
			->whereRaw($query)
			->where("url_index", "=", $index)
			->limit(1)
			->first();
		//print_r($cms_info);exit;
		return view('front.' . $this->theme . '.cmsinfo_mob')->with('cmsinfo', $cms_info);
	}
	public function logout() {
		//print("arg");exit();
		JWTAuth::invalidate(Session::get('token'));
		if (Session::has('message-success')) {
			$fb_token = Session::get('fb_token');
			$url = 'https://www.facebook.com/logout.php?next=' . '{{url("/")}}' . '&access_token=' . $fb_token;
			Session::flush();
			return Redirect::to($url);
		}
		Session::flush();
		Session::flash('message', trans("messages.Logout successfully completed"));
		return Redirect::to('/');
	}

	public function user_membership(Request $data) {

		$api = New Api;
		$user_array = $data->all();
		$url = url("/api/user_membership");
		$method = "POST";
		$post_array = array_merge($user_array, array('user_id' => Session::get('user_id')));
		$data = array('form_params' => $post_array);
		$response = $api->call_api($data, $url, $method);
		if ($response->response->httpCode == '200') {
			return response()->json($response->response);
		} else {
			return response()->json($response->response);
		}
	}

	public function user_rating(Request $data) {
		$api = New Api;
		$user_array = $data->all();
		$url = url("/api/user_rating");
		$method = "POST";
		$post_array = array_merge($user_array, array('user_id' => Session::get('user_id')));
		$data = array('form_params' => $post_array);
		$response = $api->call_api($data, $url, $method);
		if ($response->response->httpCode == '200') {
			return response()->json($response->response);
		} else {
			return response()->json($response->response);
		}
	}
	public function user_subscribe(Request $data) {
		$api = New Api;
		$post_array = $data->all();
		$user_array = array("subscribe_email" => isset($post_array['subscribe_email']) ? $post_array['subscribe_email'] : '', "language" => $post_array['language']);
		$url = url("/api/user_subscribe");
		$method = "POST";
		$data = array('form_params' => $user_array);
		$response = $api->call_api($data, $url, $method);
		return response()->json($response->response);
	}
	public function user_unsubscribe($email) {
		$api = New Api;
		$user_array = array("email" => decrypt($email));
		$url = url("/api/user_unsubscribe");
		$method = "POST";
		$data = array('form_params' => $user_array);
		$response = $api->call_api($data, $url, $method);
		Session::flash('message', $response->response->Message);
		return Redirect::to('/');
	}
	public function product_rating(Request $data) {
		$api = New Api;
		$user_array = $data->all();
		$url = url("/api/product_rating");
		$method = "POST";
		$post_array = array_merge($user_array, array('user_id' => Session::get('user_id')));
		$data = array('form_params' => $post_array);
		$response = $api->call_api($data, $url, $method);
		if ($response->response->httpCode == '200') {
			return response()->json($response->response);
			return Redirect::to('product-info');
		} else {
			return response()->json($response->response);
		}
	}
	public function location_outlet(Request $data) {

		$rules = [
			'latitude' => ['required'],
			'longitude' => ['required'],
		];
		$api = New Api;
		$post_data = $data->all();
		$language = getCurrentLang();
		//$language  = getCurrentLang();
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

			$address = DB::select("select zones.url_index as location_url_index,cities.url_index as  city_url_index,earth_distance(ll_to_earth(" . $post_data['latitude'] . ', ' . $post_data['longitude'] . "), ll_to_earth(outlets.latitude, outlets.longitude)) as distance from outlets  left Join cities  on cities.id = outlets.city_id  left join zones on zones.id =outlets.location_id where earth_box(ll_to_earth(" . $post_data['latitude'] . ', ' . $post_data['longitude'] . '), ' . $distance . ") @> ll_to_earth(outlets.latitude, outlets.longitude) ");
			$city_url_index = $location_url_index = '';
			if (count($address) > 0) {
				$city_url_index = $address[0]->city_url_index;
				$location_url_index = $address[0]->location_url_index;
				$result = array("response" => array("httpCode" => 200, "city_url_index" => $city_url_index, "location_url_index" => $location_url_index));
			} else {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.No service available in your location. Please select available location.")));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}
	public function faq($index = '') {
		//print_r(Session::get("general_site")->site_name);exit;
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		Twitter::setSite(Session::get("general_site")->site_name);
		return view('front.' . $this->theme . '.faq');
	}
	public function accountsettings($index = '') {
		//echo "in";exit;
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		Twitter::setSite(Session::get("general_site")->site_name);
		return view('front.' . $this->theme . '.account_settings');
	}
	public function mint($index = '') {
		//print_r(Session::get("general_site")->site_name);exit;
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		Twitter::setSite(Session::get("general_site")->site_name);
		return view('front.' . $this->theme . '.mint');
	}
	public function price($index = '') {
		//print_r(Session::get("general_site")->site_name);exit;
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		Twitter::setSite(Session::get("general_site")->site_name);
		return view('front.' . $this->theme . '.price');
	}
	public function request($index = '') {
		//print_r(Session::get("general_site")->site_name);exit;
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		Twitter::setSite(Session::get("general_site")->site_name);
		return view('front.' . $this->theme . '.request');
	}
	public function editrequest($index = '') {
		//print_r(Session::get("general_site")->site_name);exit;
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'FAQ');
		Twitter::setSite(Session::get("general_site")->site_name);
		return view('front.' . $this->theme . '.edit_request');
	}
	public function aboutus_mob($language) {
		if (isset($language) && $language == 2) {
			App::setLocale('ar');
		} else {
			App::setLocale('en');
		}
		return view('front.' . $this->theme . '.aboutus_mob');
	}

	public function faq_mob($index, $language) {

		$query = 'cms_infos.language_id = (case when (select count(cms_infos.language_id) as totalcount from cms_infos where cms_infos.language_id = ' . $language . ' and cms.id = cms_infos.cms_id) > 0 THEN ' . $language . ' ELSE 1 END)';
		$cms = DB::table('cms')->select('cms.id', 'cms.url_index', 'cms.sort_order', 'cms_infos.title')
			->leftJoin('cms_infos', 'cms_infos.cms_id', '=', 'cms.id', 'cms_infos.content', '=', 'content')
			->whereRaw($query)
			->where('cms.cms_type', '=', 2)
			->where('cms.cms_status', '=', 1)
			->where("url_index", "=", $index)
			->orderBy('cms.sort_order', 'asc')
			->get();
		$faq_items = array();
		if (count($cms) > 0) {
			$faq_items = $cms;
		}
		return view('front.' . $this->theme . '.faq_mob')->with('faq_items', $faq_items);

		//return response()->json($cms);
	}

	/** Driver Email Confirmation **/
	public function driver_confirmation() {
		$api = New Api;
		$verifictaion_key = Input::get('key');
		$email = Input::get('email');
		$password = Input::get('password');
		$success = false;
		$errors = array();
		if ($verifictaion_key && $email) {
			$user_array = array("key" => $verifictaion_key, "email" => $email, "password" => $password, "language" => getCurrentLang());
			$url = url("/api/driver_confirmation");
			$method = "POST";
			$data = array('form_params' => $user_array);
			$response = $api->call_api($data, $url, $method);
			Session::flash('message', $response->response->Message);
			return Redirect::to('/');
		}
	}

	public function register_check_otp(Request $data) {
		$post_data = $data->all();
		$post_data['language'] = getCurrentLang();
		$method = "POST";
		$data = array('form_params' => $post_data);
		$checkout_details = $this->api->call_api($data, 'api/check-otp-registration', $method);
		//print_r($checkout_details);exit;
		if ($checkout_details->response->httpCode == '200') {
			Session::put('user_id', $checkout_details->response->user_id);
			Session::put('email', $checkout_details->response->email);
			Session::put('first_name', $checkout_details->response->first_name);
			Session::put('last_name', $checkout_details->response->last_name);
			Session::put('name', $checkout_details->response->name);
			Session::put('mobile', $checkout_details->response->mobile);
			Session::put('social_title', $checkout_details->response->social_title);
			Session::put('profile_image', $checkout_details->response->image);
			Session::put('token', $checkout_details->response->token);
			Session::put('is_verified', $checkout_details->response->is_verified);
			return response()->json($checkout_details->response);
		} else {
			return response()->json($checkout_details->response);
		}
		// return response()->json($checkout_details->response);

	}

	public function reg_send_otp(Request $data) {
		$post_data = $data->all();
		$post_data['language'] = getCurrentLang();
		$post_data['token'] = Session::get('token');
		$method = "POST";
		// print_r($data->all());exit;
		$data = array('form_params' => $post_data);
		$checkout_details = $this->api->call_api($data, '/api/reg-send-otp', $method);
		//print_r($checkout_details);exit;
		return response()->json($checkout_details->response);
	}

	public function driverabout($index = '') {
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'About us');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'About us');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'About us');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'About us');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'About us');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'About us');
		Twitter::setSite(Session::get("general_site")->site_name);
		$data=DB::table('driver_cores')
				->select('*')
				->first();
		return view('front.' . $this->theme . '.driverabout')->with('data', $data);
	}

	public function customerabout($index = '') {
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'About us');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'About us');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'About us');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'About us');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'About us');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'About us');
		Twitter::setSite(Session::get("general_site")->site_name);
		$data=DB::table('customer_cores')
				->select('*')
				->first();
		return view('front.' . $this->theme . '.customerabout')->with('data', $data);
	}


	public function driverterms_condition($index = '') {
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'About us');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'About us');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'About us');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'About us');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'About us');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'About us');
		Twitter::setSite(Session::get("general_site")->site_name);
		$data=DB::table('driver_cores')
				->select('*')
				->first();
		return view('front.' . $this->theme . '.drivertermscondtion')->with('data', $data);
	}

	public function customerterms_condition($index = '') {
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'About us');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'About us');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'About us');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'About us');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'About us');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'About us');
		Twitter::setSite(Session::get("general_site")->site_name);
		$data=DB::table('customer_cores')
				->select('*')
				->first();
		return view('front.' . $this->theme . '.customertermscondtion')->with('data', $data);
	}

	public function privacy_policy($index = '') {
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'About us');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'About us');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'About us');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'About us');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'About us');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'About us');
		Twitter::setSite(Session::get("general_site")->site_name);
		$data=DB::table('customer_cores')
				->select('*')
				->first();
		return view('front.' . $this->theme . '.privacypolicy')->with('data', $data);
	}

	public function promotion() {
		//print_r("expression");exit();
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'promotion');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'promotion');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'promotion');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'promotion');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'promotion');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'promotion');
		Twitter::setSite(Session::get("general_site")->site_name . ' - ' . 'promotion');
		// Section description
		return view('front.' . $this->theme . '.promotion');
	}
	public function promotion_new() {
			//print_r("expression");exit();
			SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'promotion_new');
			SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'promotion_new');
			SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'promotion_new');
			OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'promotion_new');
			OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'promotion_new');
			OpenGraph::setUrl(URL::to('/'));
			Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'promotion_new');
			Twitter::setSite(Session::get("general_site")->site_name . ' - ' . 'promotion_new');
			// Section description
			return view('front.' . $this->theme . '.promotion_new');
	}

	public function outlets() {

		//print_r("expression");exit();
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'Offers');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'Offers');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'Offers');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'Offers');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'Offers');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'Offers');
		Twitter::setSite(Session::get("general_site")->site_name . ' - ' . 'Offers');
		// Section description
		return view('front.' . $this->theme . '.outlets');
	}
	public function custlogin() {

		//print_r("expression");exit();
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'Login');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'Login');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'Login');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'Login');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'Login');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'Login');
		Twitter::setSite(Session::get("general_site")->site_name . ' - ' . 'Login');
		// Section description
		
		$user_id = Session::get('user_id');
		//print_r($user_id);exit();
		$token = Session::get('token');
		if ($user_id != "") {
			$user_array = array("user_id" => $user_id, "token" => $token);
			$method = "POST";
			$data = array('form_params' => $user_array);
			$response = $this->api->call_api($data, 'api/user_detail', $method);
			$user_details = $response->response->user_data[0];
		}

		return view('front.' . $this->theme . '.custlogin');
	}


	public function cust_login() {
		//print_r("expression");exit();
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'Login');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'Login');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'Login');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'Login');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'Login');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'Login');
		Twitter::setSite(Session::get("general_site")->site_name . ' - ' . 'Login');
		// Section description
		
		$user_id = Session::get('user_id');
		$token = Session::get('token');
		if ($user_id != "") {
			$user_array = array("user_id" => $user_id, "token" => $token);
			$method = "POST";
			$data = array('form_params' => $user_array);
			$response = $this->api->call_api($data, 'api/user_detail', $method);
			$user_details = $response->response->user_data[0];
		}

		return view('front.' . $this->theme . '.custloginnew');
	}

	 public function check_login_front()
    {  
        $user_id = Session::get('user_id');
       // print_r("expression");echo "<br>";print_r($user_id);exit();
        $token = Session::get('token');
        if(empty($user_id))
        { 
            return Redirect::to('/')->send();
        } 
        $user_array = array("user_id" => $user_id,"token"=>$token);
        $method = "POST";

       	$response =  user_detail($user_array);
       //	print_r($response['response']['httpCode']);exit();
       // $data = array('form_params' => $user_array);
      //  $response = $this->api->call_api($data,'api/user_detail',$method);
        if($response['response']['httpCode'] == 400)
        { 
            return Redirect::to('/')->send();
        }
        else
        {
            $this->user_details = $response['response']['user_data'][0];
            if($this->user_details->email == "")
            {
                Session::flash('message-failure',trans("messages.Please fill your personal details"));
                return Redirect::to('/profile')->send();
            } 
            return $this->user_details;
        }
    }
	public function profile() {

		$user_details = $this->check_login_front();
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'Profile');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'Profile');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'Profile');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'Profile');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'Profile');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'Profile');
		Twitter::setSite(Session::get("general_site")->site_name . ' - ' . 'Profile');
		// Section description
		$user_id = Session::get('user_id');
		//print_r("expression");exit();
		//print_r($token);exit();
		$token = Session::get('token');
		if ($user_id != "") {
			$user_array = array("user_id" => $user_id, "token" => $token);
			$method = "POST";
			$data = array('form_params' => $user_array);
			$response =  user_detail($user_array);

			//$response = $this->api->call_api($data, 'api/user_detail', $method);
			$user_details = $response['response']['user_data'][0];

			$user_detail = DB::table('transaction')
			->select('transaction.outlet_id','transaction.created_date','transaction.total_amount','outlet_infos.outlet_name','outlet_infos.contact_address')
			->join('outlet_infos', 'outlet_infos.id', '=', 'transaction.outlet_id')
			->where('transaction.customer_id', $user_id)
			->get();
			//print_r($user_detail);exit;
		}
		return view('front.' . $this->theme . '.profile')->with("user_details",$user_details)->with("user_detail",$user_detail);
	}

 	public function custsignup() {

		//print_r("expression");exit();
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'Profile');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'Profile');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'Profile');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'Profile');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'Profile');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'Profile');
		Twitter::setSite(Session::get("general_site")->site_name . ' - ' . 'Profile');
		// Section description
		$user_id = Session::get('user_id');
		$token = Session::get('token');
		if ($user_id != "") {
			$user_array = array("user_id" => $user_id, "token" => $token);
			$method = "POST";
			$data = array('form_params' => $user_array);
			$response = $this->api->call_api($data, 'api/user_detail', $method);
			$user_details = $response->response->user_data[0];

			$user_detail = DB::table('transaction')
			->select('transaction.outlet_id','transaction.created_date','transaction.total_amount','outlet_infos.outlet_name','outlet_infos.contact_address')
			->join('outlet_infos', 'outlet_infos.id', '=', 'transaction.outlet_id')
			->where('transaction.customer_id', $user_id)
			->get();
			//print_r($user_detail);exit;
		}
		return view('front.' . $this->theme . '.custsignup')->with("user_details",$user_details)->with("user_detail",$user_detail);
	}

	public function userotpexpire(Request $data)
	{
		$post_data = $data->all();
		 $user = DB::table('users')
                ->where('users.id', $post_data['id'])
                ->update(['verfiy_pin' => null]);
                return 1;


	}



	/*public function PromotionwalletAdd(Request $data)
	{
		
		$post_data = $data->all();
		$user_id = $post_data['customer_id'];
		$promotion_type = $post_data['promotion_type'];
		$details= getCustPromotiondetails($promotion_type);
		$base_amount = isset($details->base_amount)?$details->base_amount:0;
        $addition_promotion = isset($details->addition_promotion)?$details->addition_promotion:0;
        $amount = $base_amount + $addition_promotion;
        $cart_id = rand(1000, 9999);
        $datas['cart_id'] = $cart_id;
        $datas['amount'] = $amount;
        $datas['user_id'] = $user_id;
        $telrPayment =telrPayment($datas);
	}
	//After the payment success can u please send back the order-ref in the return_auth (url ).is that possible?
    public function walletAdd(Request $data)
    {   
    	$post =$data->all();
        $validation = Validator::make($data->all(), array(
            'customer_id' => 'required|integer',
            'amount' => 'required|integer',
        ));
        // process the validation
        if ($validation->fails()) {
            return Redirect::back()->withErrors($validation)->withInput();
        } else {
            $post =$data->all();
            $user_id = isset($post['customer_id'])?$post['customer_id']:0;
            $cart_id = rand(1000, 9999);
            $amount = isset($post['amount'])?$post['amount']:0;
            $datas['cart_id'] = $cart_id;
            $datas['amount'] = $amount;
            $datas['user_id'] = $user_id;
            //$telrPayment =telrPayment($datas);

	        $currency_code = 'AED';
			$desc = "customer wallet money added";
	        $paymentgateway= getPaymentDetails(30);
	 		$auth_key = isset($paymentgateway->merchant_secret_key)?$paymentgateway->merchant_secret_key:'DRsmq^MG9m@fjX3z';
	        $store_id = isset($paymentgateway->merchant_key)?$paymentgateway->merchant_key:'21961';
			$params = array(
	            'ivp_method'=>'create',
	            'ivp_store'=>$store_id,
	            'ivp_authkey'=>$auth_key,
	            'ivp_cart'=>$datas['cart_id'],
	            'ivp_test'=>'1',
	            'ivp_amount'=>$datas['amount'],
	            'ivp_currency'=>$currency_code,
	            'ivp_desc'=>$desc,
	            'return_auth'=>'https://brozapp.com/payment_sucess',
	            'return_can'=>'https://brozapp.com/payment_cancel',
	            'return_decl'=>'https://brozapp.com/payment_declain'
	        );
			//echo"<pre>";print_r($params);exit();
	        $ch = curl_init();
	        curl_setopt($ch, CURLOPT_URL, "https://secure.telr.com/gateway/order.json");
	        curl_setopt($ch, CURLOPT_POST, count($params));
	        curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
	        $results = curl_exec($ch);
	        curl_close($ch);
	        $results = json_decode($results,true);
	        isset($results['error'])?$error=1:$error =0;
	        if($error !=1)
	        {
	            $ref= trim($results['order']['ref']);
	            $url= trim($results['order']['url']);
	            $terms      = new telr_payment;
	            $terms->order_ref =$results['order']['ref'];
	            $terms->wallet_unique_id =$data['cart_id'];
	            $terms->customer_id =$data['user_id'];
	            $terms->created_at =date("Y-m-d H:i:s");
	            $terms->updated_at = date("Y-m-d H:i:s");
	            $terms->save();
	            if (empty($ref) || empty($url)) {
	                echo"<pre>";print_r("fails");exit();
	            }else
	            {						
	            	return redirect::to($url);
	            }
	        }else{
	            print_r("sdcfsdfsdf");exit();
	        }
        }
       
    }	*/

    public function common_promotion_old(Request $data)
    {
    	$post = $data->all();
		$user_id = $post['customer_id'];
		$add_wallet = $post['add_wallet'];
    	//echo"<pre>";print_r($user_id);exit();

		$users = DB::table('users')
                //->join('user_address','user_address.user_id','=','users.id')
                ->where('users.id',$user_id)
                ->select('users.*')
                ->get();
        //print_r($users);exit();
	    $cart_id = rand(1000, 9999);    	
    	if($add_wallet==2)
    	{	
    		$promotion_type = $post['promotion_type'];
			$details= getCustPromotiondetails($promotion_type);
			$base_amount = isset($details->base_amount)?$details->base_amount:0;
	        $addition_promotion = isset($details->addition_promotion)?$details->addition_promotion:0;
	        $amount = $base_amount;
	        /*$datas['cart_id'] = $cart_id;
	        $datas['amount'] = $amount;
	        $datas['user_id'] = $user_id;*/
	        $currency_code = CURRENCYCODE;
	        //$str = "AWM-123-2-1";//eg paymwnt name - user id -wallet type -offer id 

			$desc = "AWM-".$user_id."-".$add_wallet."-".$promotion_type;
			//print_r($amount);exit();
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
		            'bill_addr1'=>isset($users[0]->address)?$users[0]->address:'57JG+3C Dubai - United Arab Emirates',
		            'bill_city'=>isset($users[0]->city)?$users[0]->city:'Dubai',
		            'bill_region'=>isset($users[0]->region)?$users[0]->region:'Dubai',
		            'bill_country'=>'AE',
		            'bill_email'=>isset($users[0]->email)?$users[0]->email:'',
		            'ivp_trantype'=>'sale'

		        );

				//echo"<pre>";print_r($params);exit();
		        $ch = curl_init();
		        curl_setopt($ch, CURLOPT_URL, "https://secure.telr.com/gateway/order.json");
		        curl_setopt($ch, CURLOPT_POST, count($params));
		        curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
		        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
		        $results = curl_exec($ch);
		        curl_close($ch);
		        $results = json_decode($results,true);
		        //print_r($results);exit();
		        isset($results['error'])?$error=1:$error =0;
		        if($error !=1)
		        {
		            $ref= trim($results['order']['ref']);
		            $url= trim($results['order']['url']);
		            /* $terms      = new telr_payment;
		            $terms->order_ref =$results['order']['ref'];
		            $terms->wallet_unique_id =$cart_id;
		            $terms->customer_id =$user_id;
		            $terms->created_at =date("Y-m-d H:i:s");
		            $terms->updated_at = date("Y-m-d H:i:s");
		            $terms->save();*/
		            if (empty($ref) || empty($url))
		            {
		            	Session::flash('message', trans("messages.There is some problem"));

		                return Redirect::to('/promotion');

		            }else
		            {	
		            	//print_r($url);exit();
		            	return redirect::to($url);

		            }
		        }else
		        {	
		            Session::flash('message', trans("messages.There is some problem"));
					return Redirect::to('/promotion');
		        }
	        //$telrPayment =telrPayment($datas);
	    }elseif($add_wallet==1)
	    {

	        $validation = Validator::make($data->all(), array(
	            'customer_id' => 'required|integer',
	            'amount' => 'required|integer',
	        ));


	        // process the validation
	        if ($validation->fails())
	        {

	            return Redirect::back()->withErrors($validation)->withInput();
	        } else
	        {



	            $amountt = isset($post['amount'])?$post['amount']:0;
	          //  echo"<pre>";print_r($users);exit();
	            $datas['cart_id'] = $cart_id;
	            $datas['amount'] = $amountt;
	            $datas['user_id'] = $user_id;
	            //$telrPayment =telrPayment($datas);
		        $currency_code = CURRENCYCODE;
				$desc = "AWM-".$user_id."-".$add_wallet."-0";
		        $paymentgateway= getPaymentDetails(30);
		 		$auth_key = isset($paymentgateway->merchant_secret_key)?$paymentgateway->merchant_secret_key:'DRsmq^MG9m@fjX3z';
		        $store_id = isset($paymentgateway->merchant_key)?$paymentgateway->merchant_key:'21961';
				$params = array(
		            'ivp_method'=>'create',
		            'ivp_store'=>$store_id,
		            'ivp_authkey'=>$auth_key,
		            'ivp_cart'=>$datas['cart_id'],
		            'ivp_test'=>'1',
		            'ivp_amount'=>$datas['amount'],
		            'ivp_currency'=>$currency_code,
		            'ivp_desc'=>$desc,
		            'return_auth'=>'https://brozapp.com/payment_sucess',
		            'return_can'=>'https://brozapp.com/payment_cancel',
		            'return_decl'=>'https://brozapp.com/payment_declain',
		            'bill_title'=>isset($users[0]->social_title)?$users[0]->social_title:'',
		            'bill_fname'=>isset($users[0]->name)?$users[0]->name:'',
		            'bill_sname'=>isset($users[0]->last_name)?$users[0]->last_name:'',
		            'bill_addr1'=>isset($users[0]->address)?$users[0]->address:'57JG+3C Dubai - United Arab Emirates',
		            'bill_city'=>isset($users[0]->city)?$users[0]->city:'Dubai',
		            'bill_region'=>isset($users[0]->region)?$users[0]->region:'Dubai',
		            'bill_country'=>'AE',
		            'bill_email'=>isset($users[0]->email)?$users[0]->email:'',
		            'ivp_trantype'=>'sale'
		        );
				//echo"<pre>";print_r($params);exit();
		        $ch = curl_init();
		        curl_setopt($ch, CURLOPT_URL, "https://secure.telr.com/gateway/order.json");
		        curl_setopt($ch, CURLOPT_POST, count($params));
		        curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
		        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
		        $results = curl_exec($ch);
		        curl_close($ch);
		        $results = json_decode($results,true);
		        isset($results['error'])?$error=1:$error =0;
		        if($error !=1)
		        {
		           	$ref= trim($results['order']['ref']);
		            $url= trim($results['order']['url']);
		             /*$terms      = new telr_payment;
		            $terms->order_ref =$results['order']['ref'];
		            $terms->wallet_unique_id =$data['cart_id'];
		            $terms->customer_id =$data['user_id'];
		            $terms->created_at =date("Y-m-d H:i:s");
		            $terms->updated_at = date("Y-m-d H:i:s");
		            $terms->save();*/
		            if (empty($ref) || empty($url))
		            {
						Session::flash('message', trans("messages.There is some problem"));
		                return Redirect::to('/promotion');	
		            }else
		            {	
		            	return redirect::to($url);
		            }
		        }else
		        {
		            Session::flash('message', trans("messages.There is some problem"));
					return Redirect::to('/promotion');
		        }
	       }
       }
    }
  	public function common_promotion(Request $data)
    {
    	$post = $data->all();
		$user_id = $post['customer_id'];
		$add_wallet = $post['add_wallet'];
		$users = DB::table('users')
                //->join('user_address','user_address.user_id','=','users.id')
                ->where('users.id',$user_id)
                ->select('users.*')
                ->get();
        //print_r($users);exit();
	    $cart_id = rand(1000, 9999);   
	    $currency_code = CURRENCYCODE;
    	if($add_wallet==2)
    	{	
    		$promotion_type = $post['promotion_type'];
			$details= getCustPromotiondetails($promotion_type);
			$base_amount = isset($details->base_amount)?$details->base_amount:0;
	        $addition_promotion = isset($details->addition_promotion)?$details->addition_promotion:0;
	        $amount = $base_amount;
	        //$str = "AWM-123-2-1";//eg paymwnt name - user id -wallet type -offer id 
			$desc = "AWM-".$user_id."-".$add_wallet."-".$promotion_type;
	       
	    }elseif($add_wallet==1)
	    {
            $amountt = isset($post['amount'])?$post['amount']:0;
            $amount = $amountt;
			$desc = "AWM-".$user_id."-".$add_wallet."-0";
				
       	}
       	$name = isset($users[0]->name)?$users[0]->name:'';
       	$last_name = isset($users[0]->last_name)?$users[0]->last_name:'user';
       	$address = isset($users[0]->address)?$users[0]->address:'57JG+3C Dubai - United Arab Emirates';
       	$city = isset($users[0]->city)?$users[0]->city:'Dubai';
       	$region = isset($users[0]->region)?$users[0]->region:'Dubai';
       	$email = isset($users[0]->email)?$users[0]->email:'sample@gmail.com';
		$billingParams = [
		        'first_name' => $name,
		        'sur_name' => $last_name,
		        'address_1' => $address,
		        'address_2' => 'Dubai',
		        'city' => $city,
		        'region' => $region,
		        'zip' => '11231',
		        'country' => 'AE',
		        'email' => $email,
		    ];
		//echo"<pre>"; print_r($cart_id);exit();
		return $this->telrManager->pay($cart_id, $amount, $desc, $billingParams)->redirect();
    }

 

	
   	public function payment_sucess()
    {

//    	echo"<pre>";print_r($_REQUEST['cart_id']);exit();
    	$cart_id = isset($_REQUEST['cart_id'])?$_REQUEST['cart_id']:'8d5d480f-2e86-4fc4-8cd3-cb3afba058ed-1577945609';
    	$user_array = array("cart_id" => $cart_id, "result_type" => 1);
		$method = "POST";
		$data = array('form_params' => $user_array);
		$response = walletpayment($user_array);
		//$response = $this->api->call_api($data, 'api/walletPaymentResult', $method);
		//print_r($response);exit();// [amount] => 10.00 [userId] => 123 [walletType] => 2 [offerId] => 1 
		$user_details= DB::table('users')->select('*')->where('users.id', '=', $response['userId'])->get();
    	$wallet_amount = isset($user_details[0]->wallet_amount)?$user_details[0]->wallet_amount:0;
        $offer_wallet  = isset($user_details[0]->offer_wallet)?$user_details[0]->offer_wallet:0;
		if($response['walletType'] ==2)
		{
			$details= getCustPromotiondetails($response['offerId']);

			$offer_wallet =$offer_wallet + $details->offer_wallet;
			$wall =$details->base_amount+$details->addition_promotion;
			$wallet_amount1 =$wall - $details->offer_wallet;
			$wallet = abs($wallet_amount1) + abs($wallet_amount);

			//$userDetails=DB::table('users')->where('users.id', '=',$response->userId)->update(['offer_wallet'=>abs($offer_wallet),'wallet_amount'=>abs($wallet)]);
			$userDetails=DB::table('users')->where('users.id', '=',$response['userId'])->update(['offer_wallet'=>abs($offer_wallet),'wallet_amount'=>abs($wallet)]);
		}else{
			$wallet_amount =$wallet_amount + $response['amount'];
			$userDetails=DB::table('users')->where('users.id', '=', $response['userId'])->update(['wallet_amount'=>abs($wallet_amount)]);
		}
        Session::flash('message', trans("messages.The amount add to wallet successfully"), 'Error!');

		return Redirect::to('/profile');
      	//return view('front.' . $this->theme . '.profile');
 
    } 


    public function payment_sucess_copy()
    {

    	echo"<pre>";print_r("fddffd");exit();
    	//902F078ECD6A23C2B786858EC15AE34C8178E3B121359F3247A1E15776E82BEC
    	$order_ref = $data->headers->get('referer');
    	//echo"<pre>";print_r($data->headers);exit();
    	//	$order_ref = "https://secure.telr.com/gateway/process.html?o=997D7D3E53DF70EFE5A9A685606FF2CF97D0794B7057AE82307E812D8F2CFE81";
		$url_components = parse_url($order_ref); 
		parse_str($url_components['query'], $params); 
		/*echo ' Hi '.$params['o']; 
		exit();*/
    	$ref = explode("=",$order_ref);
    	$user_array = array("order_ref" => $params['o'], "result_type" => 1);
		$method = "POST";
		$data = array('form_params' => $user_array);
		$response = $this->api->call_api($data, 'api/walletPaymentResult', $method);
		//print_r($response);exit();// [amount] => 10.00 [userId] => 123 [walletType] => 2 [offerId] => 1 
		$user_details= DB::table('users')->select('*')->where('users.id', '=', $response->userId)->get();
    	$wallet_amount = isset($user_details[0]->wallet_amount)?$user_details[0]->wallet_amount:0;
        $offer_wallet  = isset($user_details[0]->offer_wallet)?$user_details[0]->offer_wallet:0;
		if($response->walletType ==2)
		{
			$details= getCustPromotiondetails($response->offerId);

			$offer_wallet =$offer_wallet + $details->offer_wallet;
			$wall =$details->base_amount+$details->addition_promotion;
			$wallet_amount1 =$wall - $details->offer_wallet;
			$wallet = abs($wallet_amount1) + abs($wallet_amount);

			//$userDetails=DB::table('users')->where('users.id', '=',$response->userId)->update(['offer_wallet'=>abs($offer_wallet),'wallet_amount'=>abs($wallet)]);
			$userDetails=DB::table('users')->where('users.id', '=',$response->userId)->update(['offer_wallet'=>abs($offer_wallet),'wallet_amount'=>abs($wallet)]);
		}else{
			$wallet_amount =$wallet_amount + $response->amount;
			$userDetails=DB::table('users')->where('users.id', '=', $response->userId)->update(['wallet_amount'=>abs($wallet_amount)]);
		}
        Session::flash('message', trans("messages.The amount add to wallet successfully"), 'Error!');

		return Redirect::to('/profile');
      	//return view('front.' . $this->theme . '.profile');
 
    } 
    public function payment_cancel(Request $data)
    {
    	/*$order_ref = $data->headers->get('referer');
    	//print_r($order_ref);exit();
    	//$order_ref = "https://secure.telr.com/gateway/process.html?o=997D7D3E53DF70EFE5A9A685606FF2CF97D0794B7057AE82307E812D8F2CFE81";
    	$ref = explode("=",$order_ref);
    	$user_array = array("order_ref" => $ref[1], "result_type" => 2);
		$method = "POST";
		$data = array('form_params' => $user_array);
		$response = $this->api->call_api($data, 'api/walletPaymentResult', $method);*/

		$user_id = Session::get('user_id');
		//print_r($user_id);exit();
		/*$terms      = new payment_history;
        $terms->outlet_id =0;
        $terms->customer_id =isset($user_id)?$user_id:0;
        $terms->amount =0;
        $terms->payment_type =2;//fail
        $terms->wallet_type =0;
        $terms->created_at =date("Y-m-d H:i:s");
        $terms->created_date =date("Y-m-d H:i:s");
        $terms->updated_at =date("Y-m-d H:i:s");
        $terms->save();*/

    	$cart_id = isset($_REQUEST['cart_id'])?$_REQUEST['cart_id']:'8d5d480f-2e86-4fc4-8cd3-cb3afba058ed-1577945609';
      	$res = DB::table('payment_transaction')
            ->where('cart_id', $cart_id)
            ->update(['payment_type' => 2, 'wallet_type' => 0, 'customer_id' => $user_id]);
        Session::flash('error', trans("messages.The Payment is cancelled due to some reason"));
      	return Redirect::to('/profile');

    }
    public function payment_declain(Request $data)
    {
      	/*$order_ref = $data->headers->get('referer');
    	echo"<prE>";print_r($data->headers);exit();
    	//$order_ref = "https://secure.telr.com/gateway/process.html?o=997D7D3E53DF70EFE5A9A685606FF2CF97D0794B7057AE82307E812D8F2CFE81";
    	$ref = explode("=",$order_ref);
    	$user_array = array("order_ref" => $ref[1], "result_type" => 2);
		$method = "POST";
		$data = array('form_params' => $user_array);
		$response = $this->api->call_api($data, 'api/walletPaymentResult', $method);*/
      	$user_id = Session::get('user_id');
		/*$terms      = new payment_history;
        $terms->outlet_id =0;
        $terms->customer_id =isset($user_id)?$user_id:0;
        $terms->amount =0;
        $terms->payment_type =2;//fail
        $terms->wallet_type =0;
        $terms->created_at =date("Y-m-d H:i:s");
        $terms->created_date =date("Y-m-d H:i:s");
        $terms->updated_at =date("Y-m-d H:i:s");
        $terms->save();*/

        $cart_id = isset($_REQUEST['cart_id'])?$_REQUEST['cart_id']:'8d5d480f-2e86-4fc4-8cd3-cb3afba058ed-1577945609';
      	$res = DB::table('payment_transaction')
            ->where('cart_id', $cart_id)
            ->update(['payment_type' => 4, 'wallet_type' => 0, 'customer_id' => $user_id]);
        Session::flash('error', trans("messages.The Payment is declained due to some reason"), 'Error!');
      	return Redirect::to('/profile');
    } 

	public function ajaxcheck()
	    {
	      return view('front.' . $this->theme . '.ajaxchcek');
	    } 



    public function getOutlet(Request $data)
    {
		$post_data = $data->all();
		print_r($post_data);exit();
    }

    /**users Login**/
   /* public function loginPhoneCheck(Request $data)
    {
        //print_r("expression");exit();
		$post_data = $data->all();
		$phoneNo =  $post_data['phone_number'];
		//$phoneNo =  8281715079;
		$id=1;
        $customer_cores = customer_cores::find($id);
 		//$countryCode =  $customer_cores['country_code'];
        $countryCode =  $post_data['countryCode'];
        $user_data = DB::select('SELECT users.id,users.password ,users.name, users.email, users.status, users.is_verified,users.phone_otp,users.user_token,users.mobile,users.country_code FROM users where users.mobile = ? AND users.country_code = ? AND users.user_type = 3  limit 1', array($phoneNo,$countryCode));
        $users = new Users;
        if (count($user_data) == 0 || empty($user_data[0]->password)) {
        	print_r($countryCode);exit();
            $usertoken = sha1(uniqid(Text::random('alnum', 32), true));

            if (!$users->user_token) {
                $users->user_token = $usertoken;
            }
            $users->mobile = $phoneNo;
            $users->user_type = 3;
            $users->is_verified = 0;
            $users->facebook_id = '';
            $users->ip_address = $_SERVER['REMOTE_ADDR'];
            $users->created_date = date("Y-m-d H:i:s");
            $users->user_created_by = 3;
            $users->login_type = 1;
            //Check if the login type from mobile app update the device details here
            $verification_key = Text::random('alnum', 12);
            $users->verification_key = $verification_key;
			$otp = rand(1000, 9999);
            $users->phone_otp = $otp;
            $users->country_code= $countryCode;
            $users->updated_date = date("Y-m-d H:i:s");
             $users->save();
            // print_r("expression");exit();

            /*delete the old record/
            if (count($user_data) != 0 /*&& empty($user_data[0]->password)/) {
                $data = DB::table('users')
                    ->select('*')
                    ->where('id', $user_data[0]->id)
                    ->delete();
            }
            /*delete the old record/

            $phone=$countryCode.$phoneNo ;

            $app_config = getAppConfig();
            $number = str_replace('-', '', $phone);
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

               return 1;
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
            }
        } else {
        	//print_r("expression");exit();
			return 2;            
        }

        //print_r($user_data);exit();
    } */

  	public function signupUserCheck(Request $data)
    {
        //print_r("expression");exit();
		$post_data = $data->all();
		$phoneNo =  $post_data['phone_number'];
		$userName =  $post_data['userName'];
		$lastName =  $post_data['lastName'];
		$userEmail =  $post_data['userEmail'];
		$userPassword =  $post_data['userPassword'];
		
        $user_data = DB::select('SELECT users.id,users.password ,users.name, users.email, users.status, users.is_verified,users.phone_otp,users.user_token,users.mobile,users.country_code FROM users where users.mobile = ? limit 1', array($phoneNo));
       // print_r($user_data);


        if(count($user_data)>0){
	        $password=md5($userPassword);
	        $user = DB::table('users')
	                    ->where('users.mobile', $phoneNo)
	                    ->update(['password' => $password,
	                    		 'name' => $userName,
	                    		 'first_name' => $userName,
	                    		 'last_name' => $lastName,
	                    		 'email' => $userEmail,
	                    		 'updated_date' => date("Y-m-d H:i:s")]);

			$user_datas =$user_data[0];

	 		$token = JWTAuth::fromUser($user_datas, array('exp' => 200000000000));

     		Session::put('user_id', $user_datas->id);
			Session::put('mobile', $phoneNo);
			Session::put('email', $userEmail);
			Session::put('first_name', $userName);

			Session::put('last_name', $lastName);
			Session::put('name', $userName);
			// Session::put('social_title', $user_datas->social_title);
			// Session::put('profile_image', $user_datas->image);

			Session::put('token', $token);
            //Session::flash('message', "welcome");


	            return 1;
        }
        else {
			echo "Mobile number is not Registered.";            
        }
  
    } 

    public function loginPasswordCheck(Request $data)
    {
        
		$post_data = $data->all();
		$phoneNo =  $post_data['phone_number'];
		$password =  $post_data['password'];
		//$phoneNo =  8281715079;
		$id=1;
        $customer_cores = customer_cores::find($id);
 		//$countryCode =  $customer_cores['country_code'];
        $countryCode =  $post_data['countryCode'];
       	$user_data = DB::select('SELECT users.id, users.name, users.email, users.social_title, users.first_name, users.last_name, users.image, users.status, users.is_verified, users.facebook_id,users.loggedin_status, users.mobile FROM users where users.password = ? AND users.mobile = ? AND users.user_type=3  limit 1', array(md5($password), $phoneNo));
       
       // $users = new Users;
      	if (count($user_data) > 0) {
      		$user_datas =$user_data[0];
            //$token = JWTAuth::fromUser($user_datas, array('exp' => 200000000000));
            $token = "CHKQWWRRERERERRRRR";

     		Session::put('user_id', $user_datas->id);
			Session::put('mobile', $user_datas->mobile);
			Session::put('email', $user_datas->email);
			Session::put('first_name', $user_datas->first_name);
			Session::put('last_name', $user_datas->last_name);
			Session::put('name', $user_datas->name);
			Session::put('social_title', $user_datas->social_title);
			Session::put('profile_image', $user_datas->image);
			Session::put('token', $token);
            Session::flash('message', "welcome");
            return 3;

        } else {
        	return 4;

        }

        //print_r($user_data);exit();
    }

   public function loginotpCheck(Request $data)
    {
    	$post_data = $data->all();
		$phoneNumber =  $post_data['phone_number'];
		$otp =  $post_data['otp'];
        //print_r($post_data); exit;
        $user_details = DB::table('users')
            ->select('id')
            ->where('mobile', '=', $phoneNumber)
            ->where('phone_otp', '=', $otp)
            ->first();
        if (count($user_details) > 0) {
        	return 1;
        } else    {
        	return 2;
        }
    }



  
/*
    public function loginotpCheck(Request $data)
    {
    	$post_data = $data->all();
    	//print_r($post_data);exit();
		$user_array = array("phoneNumber" => $post_data['phone_number'],"countryCode" => $post_data['countryCode'],"otp"=>$post_data['otp'],"language"=>1);
		$method = "POST";
		$data = array('form_params' => $user_array);
		$response = $this->api->call_api($data, 'api/msignupOtpVerify', $method);
		//print_r($response);exit();
		if($response->status == 1)
		{
			return 1;
		}else{
			return 2;
		}
		
    }
    
    public function signupUserCheck(Request $data)
    {
        $api = New Api;
		$post_array = $data->all();
		$user_array = array("phoneNumber" => $post_array['phone_number'],"countryCode" => $post_array['countryCode'], "userName" => $post_array['userName'], "lastName" => $post_array['lastName'], "userEmail" => $post_array['userEmail'], "gender" => "M", "password" => $post_array['userPassword'], "deviceType" => 1, "login_type" => 1, "referral" => "", "language" => getCurrentLang());
		$method = "POST";
		$data = array('form_params' => $user_array);
		$response = $api->call_api($data, 'api/msignupNew', $method);
		if($response->status == 1)
		{

			Session::put('user_id', $response->detail->userId);
			Session::put('mobile', $response->detail->phoneNumber);
			Session::put('email', $response->detail->userEmail);
			Session::put('first_name', isset($response->detail->firstName)?$response->detail->firstName:'');
			Session::put('last_name', isset($response->detail->lastName)?$response->detail->lastName:'');
			Session::put('name', $response->detail->userName);
			Session::put('social_title', isset($response->detail->socialTitle)?$response->detail->socialTitle:'');
			Session::put('profile_image', isset($response->detail->image)?$response->detail->image:'');
			Session::put('token', $response->detail->token);
			return 1;

		}else{
			//print_r(response()->json($response));exit();
			return response()->json($response);
		}
  
    } 

   	public function loginPasswordCheck(Request $data)
    {
        
		$api = New Api;
		$post_array = $data->all();
		$user_array = array("phoneNumber" => $post_array['phone_number'],"countryCode" => $post_array['countryCode'], "userPassword" => $post_array['password'], "deviceType" => 1, "login_type" => 1, "user_type" => 3, "language" => getCurrentLang());
		$method = "POST";
		$data = array('form_params' => $user_array);
		$response = $api->call_api($data, 'api/mverifyPassword', $method);
		//echo"<pre>";print_r($response->detail);exit();

		if($response->status == 1)
		{
			Session::put('user_id', $response->detail->userId);
			Session::put('mobile', $response->detail->phoneNumber);
			Session::put('email', $response->detail->userEmail);
			Session::put('first_name', $response->detail->firstName);
			Session::put('last_name', $response->detail->lastName);
			Session::put('name', $response->detail->userName);
			Session::put('social_title', $response->detail->socialTitle);
			Session::put('profile_image', $response->detail->image);
			Session::put('token', $response->detail->token);
			return 3;
		}else {
			return 4;
		}
    }*/

    /**users Login**/

    public function loginPhoneCheck(Request $data)
    {	
    	
		$post_data = $data->all();
    	$user_array = array("phoneNumber" => $post_data['phone_number'],"countryCode" => $post_data['countryCode'], "login_type" =>2,"deviceType"=>1,"language"=>1,"facebookId"=>null,"isFacebookLogin"=>true);
		$method = "POST";
		$data = array('form_params' => $user_array);
		$response = $this->api->call_api($data, 'api/mverifyPhone', $method);
		//	print_r($response);exit();
		if($response->status == 1)
		{
			return 2;
		}else{
			return 1;
		}

    }






   



}
