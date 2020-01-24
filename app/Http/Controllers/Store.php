<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Text;
use PushNotification;
use Session;
use Closure;
use DB;
use Image;
use MetaTag;
use Mail;
use File;
use SEO;
use SEOMeta;
use OpenGraph;
use Twitter;
use App;
use Hash;
use URL;
use Yajra\Datatables\Datatables;
use App\Model\categories;
use App\Model\categories_infos;
use App\Model\products;
use App\Model\products_infos;
use App\Model\vendors;
use App\Model\vendors_infos;
use App\Model\users;
use App\Model\outlets;
use App\Model\outlet_infos;
use App\Model\delivery_timings;
use App\Model\opening_timings;
use App\Model\settings;
use App\Model\outlet_managers;
use App\Model\return_orders;
use App\Model\return_reasons;
use App\Model\return_actions;
use App\Model\return_status;
use App\Model\return_orders_log;
use App\Model\payment_request_vendors;
use Twilio;
//use Services_Twilio;
use Twilio\Rest\Client;

use App\Model\order;
use App\Model\driver_settings;
use App\Model\drivers;

class Store extends Controller
{
    const VENDORS_FORGOT_PASSWORD_EMAIL_TEMPLATE = 6;
    const VENDORS_CHANGE_PASSWORD_EMAIL_TEMPLATE = 7;
    const MANAGER_WELCOME_EMAIL_TEMPLATE = 11;
    const MANAGER_SIGNUP_EMAIL_TEMPLATE = 12;
    const RETURN_STATUS_CUSTOMER_EMAIL_TEMPLATE = 17;
    const ORDER_STATUS_UPDATE_USER = 18;
    const REFUND_APPROVE_EMAIL_TEMPLATE = 20;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->site_name = isset(getAppConfig()->site_name)?ucfirst(getAppConfig()->site_name):'';
        SEOMeta::setTitle('Vendor Panel - '.$this->site_name);
        SEOMeta::setDescription('Vendor Panel - '.$this->site_name);
        SEOMeta::addKeyword('Vendor Panel - '.$this->site_name);
        OpenGraph::setTitle('Vendor Panel - '.$this->site_name);
        OpenGraph::setDescription('Vendor Panel - '.$this->site_name);
        OpenGraph::setUrl('Vendor Panel - '.$this->site_name);
        Twitter::setTitle('Vendor Panel - '.$this->site_name);
        Twitter::setSite('@Vendor Panel - '.$this->site_name);
       // App::setLocale('en');
    }
    //Get city list for ajax request
    public function getCityData(Request $request)
    {
        if($request->ajax()){
            $country_id = $request->input('cid');
            $city_data = getCityList($country_id);
            return response()->json([
                'data' => $city_data
            ]);
        }
    }
    //Get city list for ajax request
    public function getLocationData(Request $request)
    {
        if($request->ajax()){
            $country_id = $request->input('country_id');
            $city_id = $request->input('city_id');
            $location_data = getLocationList($country_id,$city_id);
            return response()->json([
                'data' => $location_data
            ]);
        }
    }

    //Get city list for ajax request
    public function getFrontLocationData(Request $request)
    {
        if($request->ajax()){
            $city_url = $request->input('city_url');
            $location_data = getFrontLocationList($city_url);
            return response()->json([
                'data' => $location_data
            ]);
        }
    }
    
    


    /* Get outlets list based on the vendor */
    public function getOutletData(Request $request)
    {
        if($request->ajax()){
            $c_id = $request->input('cid');
            $data = getOutletList($c_id);
            $cdata = getVendorsubCategoryLists($c_id);
            //print_r($cdata);die;
            return response()->json([
                'data' => $data,'cdata'=>$cdata
            ]);
        }
    }
    /* Get outlets list based on the vendor */
    public function getSubCategoryData(Request $request)
    {
        if($request->ajax()){
            $c_id = $request->input('cid');
            $language = $request->input('language');
            $data = getSubCategoryLists1(1,$c_id,$language); // get product sub category data here
            return response()->json([
                'data' => $data
            ]);
        }
    }


        /* Get outlets list based on the vendor */
    public function getSubCategoryDataUpdated(Request $request)
    {
        if($request->ajax()){
            $c_id = $request->input('cid');
            $language = $request->input('language');
            $head_category = $request->input('head_category');
            $data = getSubCategoryListsupdated(1,$c_id,'',$language,$head_category); // get product sub category data here
            return response()->json([
                'data' => $data
            ]);
        }
    }
    
    
    
    //Get city list for ajax request
    public function notifications_read(Request $request)
    {
        if($request->ajax()){
            $vendor_id = Session::get('vendor_id');
            $c_id = $request->input('cid');
            $res = DB::table('notifications')
                ->where('id', $c_id)
                ->update(['read_status' => 1,'modified_date'=>date('Y-m-d H:i:s')]);
            $notifications = DB::table('notifications')
                ->select('notifications.id','notifications.order_id','notifications.message','notifications.created_date','notifications.read_status','users.name','users.image')
                ->leftJoin('users','users.id','=','notifications.customer_id')
                ->where('read_status', 0);
                if(!empty($vendor_id) && $vendor_id!=1){
                    $notifications = $notifications->where('vendor_id', $vendor_id);
                }
                $notifications = $notifications->orderBy('created_date', 'desc')
                ->get();
                
                $count = count($notifications);
            $data = ($res==true)?1:0;
            return response()->json([
                'data' => $data,'count' => $count,'vid'=>$vendor_id
            ]);
        }
    }
    /*
     * Vendor login process goes here
    */
    public function login () 
    {        
        return view('vendors.login');
    }
    
    /**
     * Get post values from form and checks the details exists
     * and redirects to dashboard page or else redirec to errors
     * @return \Illuminate\Http\Response
     */
    public function signin(Request $data) 
    {
        $datas=Input::all();
        // validate
        // read more on validation at http://laravel.com/docs/validation        
        $validation = Validator::make($data->all(), array(
            'email' => 'required|email',
            'password' => 'required',
        ));
        
        // process the validation
        if ($validation->fails()) {
                //return redirect('create')->withInput($datas)->withErrors($validation);
                return Redirect::back()->withErrors($validation)->withInput();
        } else {
            $email = Input::get('email');
            $password = md5(Input::get('password'));
            $vendors = DB::table('vendors')
                        ->select('vendors.id','vendors.first_name','vendors.email','vendors_infos.vendor_name','vendors.logo_image')
                        ->leftJoin('vendors_infos','vendors_infos.id','=','vendors.id')
                        ->where('email',$email)
                        ->where('hash_password',$password)
                        ->where('active_status',1)
                        ->get();
            //print_r($vendors);exit;            
            if(count($vendors) > 0){
                $vendors_data = $vendors[0];
                
                //Store the session data for future usage
                Session::put('vendor_id', $vendors_data->id);
                Session::put('user_name', ucfirst($vendors_data->first_name));
                Session::put('vendor_name', $vendors_data->vendor_name);
                Session::put('vendor_email', $vendors_data->email);
                Session::put('vendor_image', $vendors_data->logo_image);
                //Show the flash message if successful logged in
                Session::flash('message', trans('messages.Logged in successfully'));
                return Redirect::to('vendors/dashboard');
            } else {
                $validation->errors()->add('email', 'These credentials do not match our records.');
                return Redirect::back()->withErrors($validation)->withInput();
            }
        }
    }
    /*
     * After logged in redirects to dashboard page
    */
    public function home() 
    {
        if (!Session::get('vendor_id'))
        {
            return redirect()->guest('vendors/login');
        }
        else {
            $vendor_id = Session::get('vendor_id');
            // Section description
              $transaction_query = "SELECT  
                    (
                    SELECT COUNT(1)
                    FROM transaction
                    WHERE date_trunc('day', created_date) = date_trunc('day', CURRENT_DATE) AND vendor_id = $vendor_id) AS day_count,

                    (SELECT COUNT(1)
                    FROM transaction
                    WHERE date_trunc('WEEK', created_date) = date_trunc('WEEK', CURRENT_DATE) AND vendor_id = $vendor_id) AS week_count,

                    (SELECT COUNT(1)
                    FROM transaction
                    WHERE date_trunc('month', created_date) = date_trunc('month', CURRENT_DATE) AND vendor_id = $vendor_id) AS month_count,

                    (SELECT COUNT(1)
                    FROM transaction
                    WHERE date_trunc('year', created_date) = date_trunc('year', CURRENT_DATE) AND vendor_id = $vendor_id) AS year_count,
                    COUNT(1) AS total_count
                    FROM transaction WHERE vendor_id = $vendor_id ";
        $transaction_period_count = DB::select($transaction_query);
        
         $orders_query = "SELECT  
        (
        SELECT COUNT(1)
        FROM orders
        WHERE date_trunc('day', created_date) = date_trunc('day', CURRENT_DATE) AND vendor_id = $vendor_id ) AS day_count,

        (SELECT COUNT(1)
        FROM orders
        WHERE date_trunc('WEEK', created_date) = date_trunc('WEEK', CURRENT_DATE)  AND vendor_id = $vendor_id ) AS week_count,

        (SELECT COUNT(1)
        FROM orders
        WHERE date_trunc('month', created_date) = date_trunc('month', CURRENT_DATE) AND vendor_id = $vendor_id ) AS month_count,

        (SELECT COUNT(1)
        FROM orders
        WHERE date_trunc('year', created_date) = date_trunc('year', CURRENT_DATE) AND vendor_id = $vendor_id ) AS year_count,
        COUNT(1) AS total_count 
        FROM orders WHERE vendor_id = $vendor_id "; 
        
        $order_period_count = DB::select($orders_query);
        
          $outlets_query = "SELECT  
        (
        SELECT COUNT(1)
        FROM outlets
        WHERE date_trunc('day', created_date) = date_trunc('day', CURRENT_DATE) AND vendor_id = $vendor_id ) AS day_count,

        (SELECT COUNT(1)
        FROM outlets
        WHERE date_trunc('WEEK', created_date) = date_trunc('WEEK', CURRENT_DATE) AND vendor_id = $vendor_id ) AS week_count,

        (SELECT COUNT(1)
        FROM outlets
        WHERE date_trunc('month', created_date) = date_trunc('month', CURRENT_DATE) AND vendor_id = $vendor_id ) AS month_count,

        (SELECT COUNT(1)
        FROM outlets
        WHERE date_trunc('year', created_date) = date_trunc('year', CURRENT_DATE) AND vendor_id = $vendor_id  ) AS year_count,
        COUNT(1) AS total_count
        FROM outlets WHERE vendor_id = $vendor_id ";
        $outlets_period_count = DB::select($outlets_query); 
        
         $outlet_reviews_query = "SELECT  
        (SELECT COUNT(1)
        FROM outlet_reviews
        WHERE date_trunc('day', created_date) = date_trunc('day', CURRENT_DATE) AND vendor_id = $vendor_id ) AS day_count,

        (SELECT COUNT(1)
        FROM outlet_reviews
        WHERE date_trunc('WEEK', created_date) = date_trunc('WEEK', CURRENT_DATE) AND vendor_id = $vendor_id ) AS week_count,

        (SELECT COUNT(1)
        FROM outlet_reviews
        WHERE date_trunc('month', created_date) = date_trunc('month', CURRENT_DATE) AND vendor_id = $vendor_id ) AS month_count,

        (SELECT COUNT(1)
        FROM outlet_reviews
        WHERE date_trunc('year', created_date) = date_trunc('year', CURRENT_DATE) AND vendor_id = $vendor_id ) AS year_count,
        COUNT(1) AS total_count
        FROM outlet_reviews WHERE vendor_id = $vendor_id ";
        $outlet_reviews_query = DB::select($outlet_reviews_query);
        $outletmanager_query = "SELECT  
        (
        SELECT COUNT(1)
        FROM outlet_managers
        WHERE date_trunc('day', created_date) = date_trunc('day', CURRENT_DATE) AND vendor_id = $vendor_id ) AS day_count,

        (SELECT COUNT(1)
        FROM outlet_managers
        WHERE date_trunc('WEEK', created_date) = date_trunc('WEEK', CURRENT_DATE) AND vendor_id = $vendor_id ) AS week_count,

        (SELECT COUNT(1)
        FROM outlet_managers
        WHERE date_trunc('month', created_date) = date_trunc('month', CURRENT_DATE) AND vendor_id = $vendor_id ) AS month_count,

        (SELECT COUNT(1)
        FROM outlet_managers
        WHERE date_trunc('year', created_date) = date_trunc('year', CURRENT_DATE) AND vendor_id = $vendor_id ) AS year_count,
        COUNT(1) AS total_count
        FROM outlet_managers WHERE vendor_id = $vendor_id ";
        $outletmanager_query = DB::select($outletmanager_query);
        
            $outlets = DB::table('outlets')->select('outlets.id')->where('vendor_id',$vendor_id)->get();
            $outlet_managers = DB::table('outlet_managers')->select('outlet_managers.id')->where('vendor_id',$vendor_id)->get();
            $products = DB::table('products')->select(DB::raw('count(id) as product_count'))->where('vendor_id',$vendor_id)->first();
            $orders   = DB::table('orders')->select(DB::raw('count(id) as orders_count'))->where('vendor_id',$vendor_id)->first();

            $query = 'SELECT (SELECT COUNT(orders.order_status) FROM orders WHERE orders.order_status = ?) AS oreder_initiated, (SELECT COUNT(orders.order_status) FROM orders WHERE orders.order_status = ?) AS oreder_processed, (SELECT COUNT(orders.order_status) FROM orders WHERE orders.order_status = ?) AS oreder_shipped, (SELECT COUNT(orders.order_status) FROM orders WHERE orders.order_status = ?) AS oreder_packed, (SELECT COUNT(orders.order_status) FROM orders WHERE orders.order_status = ?) AS oreder_dispatched FROM orders where vendor_id='.$vendor_id.' limit 1';
            $order_status_count = DB::select($query,array(1,10,14,18,19));
            $reviews_avg = DB::table('outlet_reviews')->where('vendor_id', $vendor_id)->where('approval_status', 1)->avg('ratings');
            
            $query1 = "SELECT to_char(i, 'YYYY') as year_data, to_char(i, 'MM') as month_data, to_char(i, 'Month') as month_string, sum(total_amount) as total_amount FROM generate_series(now() - INTERVAL '1 year', now(), '1 month') as i left join orders on (to_char(i, 'YYYY') = to_char(created_date, 'YYYY') and to_char(i, 'MM') = to_char(created_date, 'MM') and vendor_id = ".$vendor_id." ) GROUP BY 1,2,3 order by year_data desc, month_data desc limit 12";
            $year_transaction = DB::select($query1);

            $language_id = getAdminCurrentLang();
            $store_transaction_query = "SELECT outlet_infos.outlet_name, SUM (orders.total_amount) AS total FROM orders JOIN outlets on outlets.id = orders.outlet_id JOIN outlet_infos on outlets.id = outlet_infos.id where orders.vendor_id=$vendor_id GROUP BY outlet_infos.outlet_name ORDER BY total DESC";
            $store_transaction_count = DB::select($store_transaction_query);
            $currency_symbol = getCurrency($language_id);
            $currency_side   = getCurrencyPosition()->currency_side;

            return view('vendors.home')->with('outlets', $outlets)->with('products', $products)->with('order_status_count', $order_status_count)->with('year_transaction', $year_transaction)->with('store_transaction_count', $store_transaction_count)->with('currency_symbol', $currency_symbol)->with('currency_side', $currency_side)->with('orders', $orders)->with('outlet_managers', $outlet_managers)->with('ratings',$reviews_avg)->with('order_period_count', $order_period_count)->with('transaction_period_count',$transaction_period_count)->with('outlets_period_count',$outlets_period_count)->with('outlet_reviews_query', $outlet_reviews_query)->with('outletmanager_query', $outletmanager_query);
        }
    }
    /*
     * Log out vendor and current sessions
    */
    public function logout() 
    {
        Session::flush();
        return Redirect::to('vendors/login')->with('status', 'Your are now logged out!');
    }
    
    /*
     * Render the forgot password view
    */
    public function forgot() 
    {
        return view('vendors.email');    
    }
    
    /*
     * Forgot password details & post data
    */
    public function forgot_details(Request $data) 
    {
        $datas=Input::all();
        // validate
        // read more on validation at http://laravel.com/docs/validation        
        $validation = Validator::make($data->all(), array(
            'email' => 'required|email'
        ));
        // process the validation
        if ($validation->fails()) {
            //return redirect('create')->withInput($datas)->withErrors($validation);
            return Redirect::back()->withErrors($validation)->withInput();
        } else {
            $email = Input::get('email');
            $vendors=DB::table('vendors')
                        ->select('vendors.id','vendors.first_name','vendors.email')
                        ->where('email',$email)
                        ->where('active_status',1)
                        ->get();
            if(count($vendors) > 0){
                $vendors_data = $vendors[0];
                //Generate random password string
                $string = str_random(8);
                $pass_string = md5($string);
                //Sending the mail to vendors
                $template=DB::table('email_templates')
                        ->select('*')
                        ->where('template_id','=',self::VENDORS_FORGOT_PASSWORD_EMAIL_TEMPLATE)
                        ->get();
                if(count($template)){
                   $from = $template[0]->from_email;
                   $from_name = $template[0]->from;
                   $subject = $template[0]->subject;
                   if(!$template[0]->template_id){
                       $template = 'mail_template';
                       $from = getAppConfigEmail()->contact_email;
                       $subject = getAppConfig()->site_name." Password Request Details";
                       $from_name = "";
                   }
                   $content = array("name" => $vendors_data->first_name,"email"=>$vendors_data->email,"password"=>$string);
                   $email = smtp($from,$from_name,$vendors_data->email,$subject,$content,$template);
                }
                //Update random password to vendors table to coreesponding vendor id
                $res = DB::table('vendors')
                    ->where('id', $vendors_data->id)
                    ->update(['hash_password' => $pass_string]);
                //Show the flash message if successful logged in
                Session::flash('status', trans('messages.Password was sent your email successfully.'));
                return Redirect::to('vendors/login');
            } else {
                $validation->errors()->add('email', 'These credentials do not match our records.');
                return Redirect::back()->withErrors($validation)->withInput();
            }
        }
    }
    
    /*
     * Render the change password view
    */
    public function change_password() 
    {
        if (!Session::get('vendor_id')){
            return redirect()->guest('vendors/login');
        }else{
            return view('vendors.reset');
        }
    }
    
    /*
     * Vendor change password request goes here
    */
    public function change_details(Request $data) 
    {
        if (!Session::get('vendor_id')) {
            return redirect()->guest('vendors/login');
        }
        $datas=Input::all();
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $validation = Validator::make($data->all(), array(
            'old_password' => 'required|min:5|max:16|regex:/(^[A-Za-z0-9 !@#$%]+$)+/',
            'password' => 'required|min:5|max:16|confirmed|regex:/(^[A-Za-z0-9 !@#$%]+$)+/',
            'password_confirmation' => 'required|min:5|max:16|regex:/(^[A-Za-z0-9 !@#$%]+$)+/'
        ));
        // process the validation
        if ($validation->fails()) {
            //return redirect('create')->withInput($datas)->withErrors($validation);
            return Redirect::back()->withErrors($validation)->withInput();
        } else {
            //Get new password details from posts
            $old_password = Input::get('old_password');
            $string = Input::get('password');
            $pass_string = md5($string);
            $session_userid = Session::get('vendor_id');
            $vendors=DB::table('vendors')
                        ->select('vendors.id','vendors.first_name','vendors.email')
                        ->where('id',$session_userid)
                        ->where('hash_password',md5($old_password))
                        ->where('active_status',1)
                        ->get();
            if(count($vendors) > 0){
                $vendors_data = $vendors[0];
                //Sending the mail to vendors
                $template=DB::table('email_templates')
                        ->select('*')
                        ->where('template_id','=',self::VENDORS_CHANGE_PASSWORD_EMAIL_TEMPLATE)
                        ->get();
                if(count($template)){
                   $from = $template[0]->from_email;
                   $from_name = $template[0]->from;
                   $subject = $template[0]->subject;
                   if(!$template[0]->template_id){
                       $template = 'mail_template';
                       $from = getAppConfigEmail()->contact_email;
                       $subject = getAppConfig()->site_name." New Password Request Updated";
                       $from_name = "";
                   }
                   $content = array("name" => $vendors_data->first_name,"email"=>$vendors_data->email,"password"=>$string);
                   $email = smtp($from,$from_name,$vendors_data->email,$subject,$content,$template);
                }
                //Update random password to vendors table to coreesponding vendor id
                $res = DB::table('vendors')
                    ->where('id', $vendors_data->id)
                    ->update(['hash_password' => $pass_string]);
                //After updating new password details logout the session and redirects to login page
                Session::flash('message', trans('messages.Your Password Changed Successfully.'));
                return Redirect::to('vendors/dashboard');
            } else {
                $validation->errors()->add('old_password', 'Old password is incorrect.');
                return Redirect::back()->withErrors($validation)->withInput();
            }
        }
    }
    /*
     * Vendor Edit Profile
    */
    public function edit_profile() 
    {
        if (!Session::get('vendor_id')){
            return redirect()->guest('vendors/login');
        }else{
            //Get session user details.        
            $id=Session::get('vendor_id');
            //Get vendor details
            $vendors = Vendors::find($id);
            if(!count($vendors)){
                 Session::flash('message', 'Invalid Vendor Details'); 
                 return Redirect::to('vendors/dashboard');    
            }
            //Get the vendors information
            $info = new Vendors_infos;
            //Get countries data
            $countries = getCountryLists();
            //Get the categories data with type vendor
            $categories= getCategoryLists(2);
            return view('vendors.edit_profile')->with('countries', $countries)->with('categories', $categories)->with('data', $vendors)->with('infomodel', $info);
        }
    }
    
    /**
     * Update the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update_profile(Request $data, $id)
    {
        if (!Session::get('vendor_id')) {
            return redirect()->guest('vendors/login');
        }
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation
        $fields['first_name'] = Input::get('first_name');
        $fields['last_name'] = Input::get('last_name');
        $fields['email'] = Input::get('email');
        $fields['mobile_number'] = Input::get('mobile_number');
        $fields['phone_number'] = Input::get('phone_number');
        $fields['country'] = Input::get('country');
        $fields['city'] = Input::get('city');
        $fields['category'] = Input::get('category');
        $fields['delivery_time'] = Input::get('delivery_time');
        $fields['pickup_time'] = Input::get('pickup_time');
        $fields['cancel_time'] = Input::get('cancel_time');
        $fields['return_time'] = Input::get('return_time');
        $fields['delivery_charges_fixed'] = Input::get('delivery_charges_fixed');
        $fields['delivery_cost_variation'] = Input::get('delivery_cost_variation');
        $fields['service_tax'] = Input::get('service_tax');
        $fields['contact_email'] = Input::get('contact_email');
        $fields['contact_address'] = Input::get('contact_address');
        $fields['featured_vendor'] = Input::get('featured_vendor');
        $fields['active_status'] = Input::get('active_status');
        $fields['logo'] = Input::file('logo');
        $fields['featured_image'] = Input::file('featured_image');
        $rules = array(
            'first_name' => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:3|max:32',
            'last_name' => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:1|max:32',
            'email' => 'required|email|max:255|unique:vendors,email,'.$id,
            /*'mobile_number' => 'required|regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
            'phone_number' => 'required|regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',*/
            'mobile_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            'phone_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            'country' => 'required',
            'city' => 'required',
            'category' => 'required',
            'delivery_time' => 'required|numeric|min:0',
            'pickup_time' => 'required|numeric|min:0',
            'cancel_time' => 'required|numeric|min:0',
            'return_time' => 'required|numeric|min:0',
            'delivery_charges_fixed' => 'required|numeric|min:0',
            'delivery_cost_variation' => 'required|numeric|min:0',
            'service_tax' => 'required|numeric|min:0.1|max:99.9',
            'contact_email' => 'required|email',
            'contact_address' => 'required',
            //'featured_vendor' => 'required',
            //'active_status' => 'required',
            'logo' => 'mimes:png,jpg,jpeg,bmp',
            'featured_image' => 'mimes:png,jpg,jpeg,bmp'
        );
        $vendor_name = Input::get('vendor_name');
        foreach ($vendor_name  as $key => $value) {
            $fields['vendor_name'.$key] = $value;
            $rules['vendor_name'.'1'] = 'required|regex:/(^[A-Za-z0-9 ]+$)+/|unique:vendors_infos,vendor_name,'.$id;
        }
        $vendor_description = Input::get('vendor_description');
        foreach ($vendor_description  as $key => $value) {
            $fields['vendor_description'.$key] = $value;
            $rules['vendor_description'.'1'] = 'required';
        }
        $validator = Validator::make($fields, $rules);    
        // process the validation
        if ($validator->fails())
        { 
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            try{
                $Vendors = Vendors::find($id); 
                $Vendors->first_name = $_POST['first_name'];
                $Vendors->last_name = $_POST['last_name'];
                $Vendors->email = $_POST['email'];
                //$Vendors->hash_password = md5(123456);
                $Vendors->phone_number = $_POST['phone_number'];
                $Vendors->mobile_number = $_POST['mobile_number'];
                $Vendors->country_id = $_POST['country'];
                $Vendors->city_id = $_POST['city'];
                $Vendors->delivery_time = $_POST['delivery_time'];
                $Vendors->pickup_time = $_POST['pickup_time'];
                $Vendors->category_ids = implode(',',$_POST['category']);
                $Vendors->modified_date = date('Y-m-d H:i:s');
                //~ if(isset($_POST['active_status']) && $_POST['active_status']!=''){
                //~ $Vendors->active_status      = $_POST['active_status'];
                //~ }
                $Vendors->contact_email = $_POST['contact_email'];
                $Vendors->contact_address = $_POST['contact_address'];
                //~ if(isset($_POST['featured_vendor']) && $_POST['featured_vendor']!=''){
                //~ $Vendors->featured_vendor      = $_POST['featured_vendor'];
                //~ }
                $Vendors->cancel_time = $_POST['cancel_time'];
                $Vendors->return_time = $_POST['return_time'];
                $Vendors->delivery_charges_fixed = $_POST['delivery_charges_fixed'];
                $Vendors->delivery_cost_variation = $_POST['delivery_cost_variation'];
                $Vendors->service_tax = $_POST['service_tax'];
                $Vendors->latitude = $_POST['latitude'];
                $Vendors->longitude = $_POST['longitude'];
                $Vendors->save();
                
                if(isset($_FILES['logo']['name']) && $_FILES['logo']['name']!=''){
                    //get last insert id
                    $imageName = $id . '.' . $data->file('logo')->getClientOriginalExtension();
                    $data->file('logo')->move(
                        base_path() . '/public/assets/admin/base/images/vendors/logos/', $imageName
                    );
                    $destinationPath1 = url('/assets/admin/base/images/vendors/logos/'.$imageName.'');
                    Image::make( $destinationPath1 )->fit(253, 133)->save(base_path() .'/public/assets/admin/base/images/vendors/logos/'.$imageName)->destroy();
                    $Vendors->logo_image=$imageName;
                    $Vendors->save();
                }
                if(isset($_FILES['featured_image']['name']) && $_FILES['featured_image']['name']!=''){
                    $imageName = $id . '.' . $data->file('featured_image')->getClientOriginalExtension();
                    $data->file('featured_image')->move(
                        base_path() . '/public/assets/admin/base/images/vendors/', $imageName
                    );
                    $destinationPath2 = url('/assets/admin/base/images/vendors/'.$imageName.'');
                    $size=getImageResize('VENDOR');
                    Image::make( $destinationPath2 )->fit($size['LIST_WIDTH'], $size['LIST_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/vendors/list/'.$imageName)->destroy();
                    Image::make( $destinationPath2 )->fit($size['DETAIL_WIDTH'], $size['DETAIL_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/vendors/detail/'.$imageName)->destroy();
                    Image::make( $destinationPath2 )->fit($size['THUMB_WIDTH'], $size['THUMB_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/vendors/thumb/'.$imageName)->destroy();
                    $Vendors->featured_image=$imageName;
                    $Vendors->save();
                }
                $this->vendor_save_after($Vendors,$_POST);
                Session::put('user_name', ucfirst($_POST['first_name']));
            }catch(Exception $e) {
                Log::Instance()->add(Log::ERROR, $e);
            }
            Session::flash('message', trans('messages.Profile has been successfully updated'));
            return Redirect::to('vendors/editprofile');
        }
    }

    public static function vendor_save_after($object,$post,$method=0)
   {
        if(isset($post['vendor_name']) && isset($post['vendor_description'])){
            $vendor_name = $post['vendor_name'];
            $vendor_description = $post['vendor_description'];
            try{
                $data = Vendors_infos::find($object->id);
                if(count($data)>0){
                    $data->delete();
                }
                $languages = DB::table('languages')->where('status', 1)->get();
                foreach($languages as $key => $lang){
                    if((isset($vendor_name[$lang->id]) && $vendor_name[$lang->id]!="") && (isset($vendor_description[$lang->id]) && $vendor_description[$lang->id]!="")){
                        $infomodel = new Vendors_infos;
                        $infomodel->lang_id = $lang->id;
                        $infomodel->id = $object->id; 
                        $infomodel->vendor_name = $vendor_name[$lang->id];
                        $infomodel->vendor_description = $vendor_description[$lang->id];
                        $infomodel->save();
                    }
                }
            }catch(Exception $e) {
                Log::Instance()->add(Log::ERROR, $e);
            }
        }
   }

    /**
     * Show the application outlets.
     * @return \Illuminate\Http\Response
     */
    public function branches()
    {
        //print_r("expression");exit;
        $id=Session::get('vendor_id');
        if (!$id){
            return redirect()->guest('vendors/login');
        }else{   
            return view('vendors.outlets.list');
        }
    }

    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxBranches()
    {
        $id    = Session::get('vendor_id');
        $query = '"vendors_infos"."lang_id" = (case when (select count(lang_id) as totalcount from vendors_infos where vendors_infos.lang_id = '.getAdminCurrentLang().' and vendors.id = vendors_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $query1 = '"outlet_infos"."language_id" = (case when (select count(id) as totalcount from outlet_infos where outlet_infos.language_id = '.getAdminCurrentLang().' and outlets.id = outlet_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $vendors = Outlets::Leftjoin('vendors','vendors.id','=','outlets.vendor_id')
                    ->Leftjoin('vendors_infos','vendors_infos.id','=','vendors.id')
                    ->Leftjoin('outlet_infos','outlet_infos.id','=','outlets.id')
                    ->select('vendors_infos.vendor_name','outlets.id','outlets.active_status','outlets.modified_date','outlets.contact_email','outlets.contact_phone','outlet_infos.contact_address','outlets.created_date','outlets.modified_date','outlet_infos.outlet_name')
                    ->whereRaw($query)
                    ->whereRaw($query1)
                    ->where('outlets.vendor_id','=',$id)
                    ->orderby('outlets.id','desc')
                    ->get();
        return Datatables::of($vendors)->addColumn('action', function ($vendors) {
                $html='<div class="btn-group"><a href="'.URL::to("vendor/edit_outlet/".$vendors->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                            <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu xs pull-right" role="menu">
                            <li><a href="'.URL::to("vendor/outlet_details/".$vendors->id).'" class="view-'.$vendors->id.'" title="'.trans("messages.View").'"><i class="fa fa-file-text-o"></i>&nbsp;&nbsp;'.@trans("messages.View").'</a></li>
                            <li><a href="'.URL::to("vendor/delete_outlet/".$vendors->id).'" class="delete-'.$vendors->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                            </ul>
                        </div><script type="text/javascript">
                        $( document ).ready(function() {
                        $(".delete-'.$vendors->id.'").on("click", function(){
                             return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });});</script>';
                return $html;
            })
            ->addColumn('active_status', function ($vendors) {
                if($vendors->active_status==0):
                    $data = '<span class="label label-warning">'.trans("messages.Inactive").'</span>';
                elseif($vendors->active_status==1):
                    $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
                elseif($vendors->active_status==2):
                    $data = '<span class="label label-danger">'.trans("messages.Delete").'</span>';
                endif;
                return $data;
            })
            ->addColumn('modified_date', function ($vendors) {
            $data = '-';
            if($vendors->modified_date != ''):
            $data = $vendors->modified_date;
            endif;
            return $data;
            })
            ->rawColumns(['modified_date','active_status','action'])

            ->make(true);
    }

    /**
     * Create the specified outlet in view.
     * @param  int  $id
     * @return Response
     */
    
    public function branch_create()
    {
        $id=Session::get('vendor_id');
        if (!$id)
        {
            return redirect()->guest('vendors/login');
        }
        else {
            //Get countries data
            $countries = getCountryLists();
            return view('vendors.outlets.create')->with('countries', $countries);
        }
    }

    /**
     * Add the specified outlet in storage.
     * @param  int  $id
     * @return Response
     */
    public function branch_store(Request $data)
    {
        $id=Session::get('vendor_id');
        if (!$id)
        {
            return redirect()->guest('vendors/login');
        }
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation
        $fields['contact_phone_number'] = Input::get('contact_phone_number');
        $fields['country'] = Input::get('country');
        $fields['city'] = Input::get('city');
        $fields['location'] = Input::get('location');
        $fields['delivery_areas'] = Input::get('delivery_areas');
        $fields['delivery_time'] = Input::get('delivery_time');
        $fields['pickup_time'] = Input::get('pickup_time');
        $fields['cancel_time'] = Input::get('cancel_time');
        $fields['return_time'] = Input::get('return_time');
        $fields['delivery_charges_fixed'] = Input::get('delivery_charges_fixed');
        $fields['delivery_cost_variation'] = Input::get('delivery_cost_variation');
        $fields['service_tax'] = Input::get('service_tax');
        $fields['minimum_order_amount'] = Input::get('minimum_order_amount');
        $fields['contact_email'] = Input::get('contact_email');
       // $fields['contact_address'] = Input::get('contact_address');
        $fields['latitude'] = Input::get('latitude');
        $fields['longitude'] = Input::get('longitude');
        $fields['active_status'] = Input::get('active_status');
        $rules = array(
            'country' => 'required',
            'city' => 'required',
            'location' => 'required',
            //'contact_phone_number' => 'required|regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
            'contact_phone_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            'contact_email' => 'required|email',
            //'contact_address' => 'required',
            'delivery_areas' => 'required',
            'delivery_time' => 'required|numeric|min:0',
            'pickup_time' => 'required|numeric|min:0',
            'cancel_time' => 'required|numeric|min:0',
            'return_time' => 'required|numeric|min:0',
            'delivery_charges_fixed' => 'required|numeric|min:0',
            'delivery_cost_variation' => 'required|numeric|min:0',
            'service_tax' => 'required|numeric|min:0.1|max:99.9',
            'minimum_order_amount' => 'required|numeric|min:0',
            
            //~ 'active_status' => 'required',
        );
        $outlet_name = Input::get('outlet_name');
        foreach ($outlet_name  as $key => $value)
        {
            $fields['outlet_name'.$key] = $value;
            $rules['outlet_name'.'1']   = 'required|regex:/(^[A-Za-z0-9 ]+$)+/|unique:outlet_infos,outlet_name';
        }
           $contact_address = Input::get('contact_address');
        foreach ($contact_address  as $key => $value)
        {
            $fields['contact_address'.$key] = $value;
            $rules['contact_address'.'1']   = 'required';
        }
        $validation = Validator::make($fields, $rules);
        // process the validation
        if ($validation->fails())
        { 
            return Redirect::back()->withErrors($validation)->withInput();
        } else {
            //Store the data here with database
            
            try{
                $Outlets = new Outlets;
                $Outlets->contact_phone = $_POST['contact_phone_number'];
                $Outlets->url_index     =  str_slug($_POST['outlet_name'][1]);
                $Outlets->country_id = $_POST['country'];
                $Outlets->city_id = $_POST['city'];
                $Outlets->location_id = $_POST['location'];
                $Outlets->vendor_id = Session::get('vendor_id');
                $Outlets->delivery_time = $_POST['delivery_time'];
                $Outlets->pickup_time = $_POST['pickup_time'];
                $Outlets->delivery_areas = implode(',',$_POST['delivery_areas']);
                $Outlets->created_date = date('Y-m-d H:i:s');
                $Outlets->created_by = Session::get('vendor_id');
                $Outlets->active_status = isset($_POST['active_status'])?$_POST['active_status']:'0';
                $Outlets->contact_email = $_POST['contact_email'];
                //$Outlets->contact_address = $_POST['contact_address'];
                $Outlets->latitude = $_POST['latitude'];
                $Outlets->longitude = $_POST['longitude'];
                $Outlets->cancel_time = $_POST['cancel_time'];
                $Outlets->return_time = $_POST['return_time'];
                $Outlets->delivery_charges_fixed = $_POST['delivery_charges_fixed'];
                $Outlets->delivery_charges_variation = $_POST['delivery_cost_variation'];
                $Outlets->service_tax = $_POST['service_tax'];
                $Outlets->minimum_order_amount = $_POST['minimum_order_amount'];
                $Outlets->save();
                $last_insert_id = $Outlets->id;
                
                //Store the opening timing schedules here
                $opening_time = $_POST['opening_timing'];
                $opentime_array = getDaysWeekArray();
                foreach($opening_time as $key => $values) {
                    $Open_Timings = new Opening_timings;
                    if(isset($values['istrue']) && $values['istrue']==1 && (array_key_exists($key, $opentime_array))) {
                        $day_week = $opentime_array[$key];
                        $Open_Timings->vendor_id = $last_insert_id;
                        $Open_Timings->day_week = $day_week;
                        $Open_Timings->start_time = $values['from'];
                        $Open_Timings->end_time = $values['to'];
                        $Open_Timings->created_date = date('Y-m-d');
                        $Open_Timings->save();
                    }
                }
                //Store the delivery timing schedules here
                //$delivery_time = $_POST['delivery_timing'];
                //$deliverytime_array = getDaysWeekArray();
                //foreach($delivery_time as $key => $values) {
                    //$Delivery_Timings = new Delivery_timings;
                    //if(isset($values['istrue']) && $values['istrue']==1 && (array_key_exists($key, $deliverytime_array))) {
                        //$day_week = $deliverytime_array[$key];
                        //$Delivery_Timings->vendor_id = $last_insert_id;
                        //$Delivery_Timings->day_week = $day_week;
                        //$Delivery_Timings->start_time = $values['from'];
                        //$Delivery_Timings->end_time = $values['to'];
                        //$Delivery_Timings->created_date = date('Y-m-d');
                        //$Delivery_Timings->save();
                    //}
                //}
                $this->branch_save_after($Outlets,$_POST);
                Session::flash('message', trans('messages.Outlet has been added successfully'));
            }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('vendor/outlets');
        }
    }
    /**
     * add,edit datas  saved in main table 
     * after inserted in sub tabel.
     *
     * @param  int  $id
     * @return Response
     */
    public static function branch_save_after($object,$post)
    {
        if(isset($post['outlet_name']))
        {
            $outlet_name = $post['outlet_name'];
            $contact_address = $post['contact_address'];
            try {
                $data = Outlet_infos::find($object->id);
                if(count($data)>0)
                {
                    $data->delete();
                }
                $languages = DB::table('languages')->where('status', 1)->get();
                foreach($languages as $key => $lang)
                {
                    if((isset($outlet_name[$lang->id]) && $outlet_name[$lang->id]!="") )
                    {
                        $infomodel = new Outlet_infos;
                        $infomodel->language_id = $lang->id;
                        $infomodel->id          = $object->id; 
                        $infomodel->outlet_name = $outlet_name[$lang->id];
                        $infomodel->contact_address = $contact_address[$lang->id];
                        $infomodel->save();
                    }
                }
            }
            catch(Exception $e) {
                Log::Instance()->add(Log::ERROR, $e);
            }
        }
   }

    /**
     * Create the specified outlet in view.
     * @param  int  $id
     * @return Response
     */
    public function branch_edit($id)
    {
        if (!Session::get('vendor_id'))
        {
            return redirect()->guest('vendors/login');
        }
        else{
            //Get vendor details
            $data = Outlets::find($id);
            if(!count($data))
            {
                Session::flash('message', 'Invalid Outlet Details'); 
                return Redirect::to('vendor/outlets');    
            }
            //Get countries data
            $countries = getCountryLists();
            $info      = new Outlet_infos;
            return view('vendors.outlets.edit')->with('countries', $countries)->with('data', $data)->with('infomodel', $info);
        }
    }

     /**
     * Update the specified outlet in storage.
     * @param  int  $id
     * @return Response
     */
    public function branch_update(Request $data, $id)
    {
        if (!Session::get('vendor_id')){
            return redirect()->guest('vendors/login');
        }
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation
       
        $fields['contact_phone_number'] = Input::get('contact_phone_number');
        $fields['country'] = Input::get('country');
        $fields['city'] = Input::get('city');
        $fields['location'] = Input::get('location');
        $fields['delivery_areas'] = Input::get('delivery_areas');
        $fields['delivery_time'] = Input::get('delivery_time');
        $fields['pickup_time'] = Input::get('pickup_time');
        $fields['cancel_time'] = Input::get('cancel_time');
        $fields['return_time'] = Input::get('return_time');
        $fields['delivery_charges_fixed'] = Input::get('delivery_charges_fixed');
        $fields['delivery_cost_variation'] = Input::get('delivery_cost_variation');
        $fields['service_tax'] = Input::get('service_tax');
        $fields['minimum_order_amount'] = Input::get('minimum_order_amount');
        $fields['contact_email'] = Input::get('contact_email');
        //$fields['contact_address'] = Input::get('contact_address');
        $fields['latitude'] = Input::get('latitude');
        $fields['longitude'] = Input::get('longitude');
        $fields['active_status'] = Input::get('active_status');
        $rules = array(
            
            'country' => 'required',
            'city' => 'required',
            'location' => 'required',
            //'contact_phone_number' => 'required|regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
            'contact_phone_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            'contact_email' => 'required|email',
           // 'contact_address' => 'required',
            'delivery_areas' => 'required',
            'delivery_time' => 'required|numeric|min:0',
            'pickup_time' => 'required|numeric|min:0',
            'cancel_time' => 'required|numeric|min:0',
            'return_time' => 'required|numeric|min:0',
            'delivery_charges_fixed' => 'required|numeric|min:0',
            'delivery_cost_variation' => 'required|numeric|min:0',
            'service_tax' => 'required|numeric|min:0.1|max:99.9',
            'minimum_order_amount' => 'required|numeric|min:0',
            
            //'active_status' => 'required',
        );
        $outlet_name = Input::get('outlet_name');
        foreach ($outlet_name  as $key => $value)
        {
            $fields['outlet_name'.$key] = $value;
            $rules['outlet_name'.'1']   = 'required|regex:/(^[A-Za-z0-9 ]+$)+/|unique:outlet_infos,outlet_name,'.$id;
        }
        $contact_address = Input::get('contact_address');
        foreach ($contact_address  as $key => $value)
        {
            $fields['contact_address'.$key] = $value;
            $rules['contact_address'.'1']   = 'required';
        }
        $validator = Validator::make($fields, $rules);    
        // process the validation
        if ($validator->fails())
        { 
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            try{
                $Outlets = Outlets::find($id); 
                $Outlets->contact_phone = $_POST['contact_phone_number'];
                $Outlets->url_index     =  str_slug($_POST['outlet_name'][1]);
                $Outlets->country_id = $_POST['country'];
                $Outlets->city_id = $_POST['city'];
                $Outlets->location_id = $_POST['location'];
                $Outlets->vendor_id = Session::get('vendor_id');
                $Outlets->delivery_time = $_POST['delivery_time'];
                $Outlets->pickup_time = $_POST['pickup_time'];
                $Outlets->delivery_areas = implode(',',$_POST['delivery_areas']);
                $Outlets->modified_date = date('Y-m-d H:i:s');
                $Outlets->created_by = Session::get('vendor_id');
                $Outlets->active_status = isset($_POST['active_status'])?$_POST['active_status']:0;
                $Outlets->contact_email = $_POST['contact_email'];
                //$Outlets->contact_address = $_POST['contact_address'];
                $Outlets->latitude = $_POST['latitude'];
                $Outlets->longitude = $_POST['longitude'];
                $Outlets->cancel_time = $_POST['cancel_time'];
                $Outlets->return_time = $_POST['return_time'];
                $Outlets->delivery_charges_fixed = $_POST['delivery_charges_fixed'];
                $Outlets->delivery_charges_variation = $_POST['delivery_cost_variation'];
                $Outlets->service_tax = $_POST['service_tax'];
                $Outlets->minimum_order_amount = $_POST['minimum_order_amount'];
                $Outlets->save();
                $last_insert_id = $id;
                //If posting new data means delete the old data in timings table and inserts new data here
                $del = DB::table('opening_timings')->where('vendor_id', $last_insert_id)->delete();
                //Store the timing schedules here
                $opening_time = $_POST['opening_timing'];
                $opening_time_array = getDaysWeekArray();
                foreach($opening_time as $key => $values) {
                    $Timings = new Opening_timings;
                    if(isset($values['istrue']) && $values['istrue']==1 && (array_key_exists($key, $opening_time_array))) {
                        $day_week = $opening_time_array[$key];
                        $Timings->vendor_id = $last_insert_id;
                        $Timings->day_week = $day_week;
                        $Timings->start_time = $values['from'];
                        $Timings->end_time = $values['to'];
                        $Timings->created_date = date('Y-m-d');
                        $Timings->save();
                    }
                }
                //If posting new data means delete the old data in timings table and inserts new data here
                //$del = DB::table('delivery_timings')->where('vendor_id', $last_insert_id)->delete();
                //Store the timing schedules here
                //$delivery_time = $_POST['delivery_timing'];
                //$delivery_time_array = getDaysWeekArray();
                //foreach($delivery_time as $key => $values) {
                    //$Timings = new Delivery_timings;
                    //if(isset($values['istrue']) && $values['istrue']==1 && (array_key_exists($key, $delivery_time_array))) {
                        //$day_week = $delivery_time_array[$key];
                        //$Timings->vendor_id = $last_insert_id;
                        //$Timings->day_week = $day_week;
                        //$Timings->start_time = $values['from'];
                        //$Timings->end_time = $values['to'];
                        //$Timings->created_date = date('Y-m-d');
                        //$Timings->save();
                    //}
                //}
                $this->branch_save_after($Outlets,$_POST);
                Session::flash('message', trans('messages.Outlet has been updated successfully'));
            }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('vendor/outlets');
        }
    }

    public function branch_show($id)
    {
        if (!Session::get('vendor_id'))
        {
            return redirect()->guest('vendors/login');
        }
        else {
            //Get vendor details
            $query  = '"vendors_infos"."lang_id" = (case when (select count(lang_id) as totalcount from vendors_infos where vendors_infos.lang_id = '.getAdminCurrentLang().' and outlets.vendor_id = vendors_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $query1 = '"outlet_infos"."language_id" = (case when (select count(language_id) as totalcount from outlet_infos where outlet_infos.language_id = '.getAdminCurrentLang().' and outlets.id = outlet_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $data = DB::table('outlets')
                    ->select('vendors_infos.vendor_name','outlets.id','outlets.country_id','outlets.city_id','outlets.location_id','outlet_infos.contact_address','outlets.contact_phone','outlets.contact_email','outlets.active_status','outlets.delivery_time','outlets.pickup_time','outlets.cancel_time','outlets.return_time','outlets.delivery_charges_fixed','outlets.delivery_charges_variation','outlets.service_tax','outlets.minimum_order_amount','outlet_infos.outlet_name')
                    ->leftJoin('vendors_infos','vendors_infos.id','=','outlets.vendor_id')
                    ->leftJoin('outlet_infos','outlet_infos.id','=','outlets.id')
                    ->whereRaw($query)
                    ->whereRaw($query1)
                    ->where('outlets.id',$id)
                    ->get();
            if(!count($data))
            {
                Session::flash('message', 'Invalid Outlet Details'); 
                return Redirect::to('vendor/outlets');
            }
            //Get countries data
            $countries = getCountryLists();
            return view('vendors.outlets.show')->with('countries', $countries)->with('data', $data);
        }
    }

    public function branch_destory($id)
    {
        if (!Session::get('vendor_id')){
            return redirect()->guest('vendors/login');
        }
        $data = Outlets::find($id);
        if(!count($data)){
            Session::flash('message', 'Invalid Outlet Details'); 
            return Redirect::to('vendor/outlets');    
        }
        //$data->delete();
        //Update delete status while deleting
        $data->active_status = 2;
        $data->save();
        Session::flash('message', trans('messages.Outlet has been deleted successfully!'));
        return Redirect::to('vendor/outlets');
    }

    /**
     * Show the application outlets.
     * @return \Illuminate\Http\Response
     */
    public function outlet_managers()
    {
        if (!Session::get('vendor_id'))
        {
            return redirect()->guest('vendors/login');
        }
        else{
            return view('vendors.outlets.managers.list');
        }
    }

    /**
     * Create the specified outlet manager in view.
     * @param  int  $id
     * @return Response
     */
    
    public function outlet_managers_create()
    {
        if (!Session::get('vendor_id')){
            return redirect()->guest('vendor/login');
        }
        else{
            $id = 1;
            $settings = Settings::find($id);
            //Get countries data
            $countries = getCountryLists();
            return view('vendors.outlets.managers.create')->with('countries', $countries)->with('settings', $settings);
        }
    }

    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxVendorBranchmanager()
    {
        $query = '"vendors_infos"."lang_id" = (case when (select count(id) as totalcount from vendors_infos where vendors_infos.lang_id = '.getAdminCurrentLang().' and outlet_managers.vendor_id = vendors_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $query1 = '"outlet_infos"."language_id" = (case when (select count(id) as totalcount from outlet_infos where outlet_infos.language_id = '.getAdminCurrentLang().' and outlets.id = outlet_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $outlet_managers = DB::table('outlet_managers')
                            ->select('outlet_managers.first_name','outlet_managers.email','outlet_managers.mobile_number','outlet_managers.email','outlet_managers.active_status','outlet_managers.created_date','outlet_managers.id as outletmanagerid','vendors_infos.vendor_name','outlet_infos.outlet_name')
                            ->leftJoin('vendors_infos','vendors_infos.id','=','outlet_managers.vendor_id')
                            ->leftJoin('outlets','outlets.id','=','outlet_managers.outlet_id')
                            ->leftJoin('outlet_infos','outlet_infos.id','=','outlets.id')
                            ->whereRaw($query)
                            ->whereRaw($query1)
                            ->where('outlet_managers.vendor_id','=',Session::get('vendor_id'))
                            ->orderBy('outlet_managers.id', 'desc');
        return Datatables::of($outlet_managers)->addColumn('action', function ($outlet_managers) {
                $html='<div class="btn-group"><a href="'.URL::to("vendor/edit_outlet_manager/".$outlet_managers->outletmanagerid).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                            <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu xs pull-right" role="menu">
                            <li><a href="'.URL::to("vendor/delete_outlet_managers/".$outlet_managers->outletmanagerid).'" class="delete-'.$outlet_managers->outletmanagerid.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                            </ul>
                        </div><script type="text/javascript">
                        $( document ).ready(function() {
                        $(".delete-'.$outlet_managers->outletmanagerid.'").on("click", function(){
                             return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });});</script>';
            
                return $html;
            })
            ->addColumn('activestatus', function ($outlet_managers) {
                if($outlet_managers->active_status==0):
                    $data = '<span class="label label-warning">'.trans("messages.Inactive").'</span>';
                elseif($outlet_managers->active_status==1):
                    $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
                elseif($outlet_managers->active_status==2):
                    $data = '<span class="label label-danger">'.trans("messages.Delete").'</span>';
                endif;
                return $data;
            })
            ->addColumn('mobile_number', function ($outlet_managers) {
                $data = '-';
                if($outlet_managers->mobile_number != ''):
                $data = $outlet_managers->mobile_number;
                endif;
                return $data;
                })
                        ->rawColumns(['mobile_number','activestatus','action'])

            ->make(true);
    }

    /**
     * Edit the specified driver in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function outlet_managers_edit($id)
    {
        if (!Session::get('vendor_id')) {
            return redirect()->guest('vendors/login');
        } else {
            
            //Get driver details
            $managers = Outlet_managers::find($id);
            if(!count($managers))
            {
                Session::flash('message', 'Invalid manager Details'); 
                return Redirect::to('vendor/outletmanagers');
            }
            $settings = Settings::find(1);
            $countries = getCountryLists();
            SEOMeta::setTitle('Edit Manager - '.$this->site_name);
            SEOMeta::setDescription('Edit Manager - '.$this->site_name);
            return view('vendors.outlets.managers.edit')->with('settings', $settings)->with('countries', $countries)->with('data', $managers);
        }
    }

    /**
     * Update the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function outlet_managers_update(Request $data ,$id)
    {
        if (!Session::get('vendor_id')) {
            return redirect()->guest('vendors/login');
        }
        $validation = Validator::make($data->all(), array(
            //~ 'social_title'  => 'required',
            'first_name'    => 'required|regex:/(^[A-Za-z0-9 ]+$)+/',
            'last_name'     => 'required|regex:/(^[A-Za-z0-9 ]+$)+/',
            'email' => 'required|email|max:255|unique:outlet_managers,email,'.$id,
            //'mobile' => 'regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
            'mobile' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            //'user_password' => 'required|min:5|max:32',
            'date_of_birth' => 'date',
            'gender'        => 'required',
            'gender'        => 'required',
            'outlet_name'=>'required',
             'postal_code'=> 'required|numeric',
            'address'=>'required',
            'image'       => 'mimes:png,jpeg,bmp|max:2024',
            'country_code'  => 'required',
            'pin'  => 'required',

        ));
        // process the validation
        if ($validation->fails()) {
            return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store datas in to database
            $managers = Outlet_managers::find($id);
            /** $manager_token = sha1(uniqid(Text::random('alnum', 32), TRUE));
            if(!$managers->manager_token)
            {
                $managers->manager_token = $manager_token;
            }**/
            //~ $managers->social_title  = $_POST['social_title'];
             $managers->first_name    = $_POST['first_name'];
            $managers->last_name     = $_POST['last_name'];
            $managers->email         = $_POST['email'];
           // $managers->hash_password = $_POST['user_password'];
            $managers->mobile_number = $_POST['mobile'];
             //$managers->date_of_birth = $_POST['date_of_birth'];
            $managers->gender        = $_POST['gender'];
             if($_POST['date_of_birth']!='')
            {
                $managers->date_of_birth = $_POST['date_of_birth'];
            }
            if(isset($_POST['country']) && $_POST['country']!='')
            {
                $managers->country_id = $_POST['country'];
            }
            if(isset($_POST['city']) && $_POST['city']!='')
            {
                $managers->city_id = $_POST['city'];
            }
            $managers->active_status     = isset($_POST['active_status'])?$_POST['active_status']:0;
            $managers->is_verified       = isset($_POST['is_verified'])?$_POST['is_verified']:0;
            //$drivers->ip_address      = Request::ip();
            $managers->modified_date     = date("Y-m-d H:i:s");
               $managers->hash_password = md5($_POST['user_password']);
            //$managers->created_by = Auth::id();
            $verification_key           = Text::random('alnum',12);
            $managers->verification_key  = $verification_key;
            $managers->postal_code        = $_POST['postal_code'];
            $managers->address        = $_POST['address'];
            $managers->vendor_id        = Session::get('vendor_id');
            $managers->outlet_id        = $_POST['outlet_name'];
            $managers->country_code     = $_POST['country_code'];
            $managers->outlet_key     = isset($_POST['pin'])? $_POST['pin']:'';
            $managers->save();
            if(isset($_FILES['image']['name']) && $_FILES['image']['name']!='')
            {
                $destinationPath = base_path().'/public/assets/admin/base/images/managers/'; // upload path
                $imageName = $managers->id.'.'.$data->file('image')->getClientOriginalExtension();
                $data->file('image')->move($destinationPath, $imageName);
                $destinationPath1 = url('/assets/admin/base/images/managers/'.$imageName);
                Image::make( $destinationPath1 )->fit(75, 75)->save(base_path().'/public/assets/admin/base/images/managers/thumb/'.$imageName)->destroy();
                $managers->profile_image = $imageName;
                $managers->save();
            } 
            // redirect
            Session::flash('message', trans('messages.Outlet manager has been updated successfully'));
            return Redirect::to('vendor/outletmanagers');
        }
    }

    /**
     * Update the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function outlet_managers_store(Request $data)
    {
        if (!Session::get('vendor_id'))
        {
            return redirect()->guest('vendors/login');
        }
        $validation = Validator::make($data->all(), array(
            //~ 'social_title'  => 'required',
            'first_name'    => 'required|regex:/(^[A-Za-z0-9 ]+$)+/',
            'last_name'     => 'required|regex:/(^[A-Za-z0-9 ]+$)+/',
            'email'         => 'required|email|unique:outlet_managers,email',
            'user_password' => 'required|min:5|max:32',
            'gender'        => 'required',
            //'mobile'        => 'regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
            'mobile'        => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            'outlet_name'   => 'required',
            'postal_code'   => 'required|numeric',
            'address'       => 'required',
            'country_code'  => 'required',
        ));
        // process the validation
        if ($validation->fails())
        {
            return Redirect::back()->withErrors($validation)->withInput();
        }
        else {
            // store datas in to database
            $managers      = new Outlet_managers;
            $manager_token = sha1(uniqid(Text::random('alnum', 32), TRUE));
            if($manager_token)
            {
                $managers->manager_token = $manager_token;
            } 
            //~ $managers->social_title  = $_POST['social_title'];
            $managers->first_name    = $_POST['first_name'];
            $managers->last_name     = $_POST['last_name'];
            $managers->email         = $_POST['email'];
            $managers->hash_password = md5($_POST['user_password']);
            $managers->mobile_number = $_POST['mobile'];
            //$managers->date_of_birth = $_POST['date_of_birth'];
            $managers->gender        = $_POST['gender'];
            if($_POST['date_of_birth']!='')
            {
                $managers->date_of_birth = $_POST['date_of_birth'];
            }
            if(isset($_POST['country']) && $_POST['country']!='')
            {
                $managers->country_id = $_POST['country'];
            }
            if(isset($_POST['city']) && $_POST['city']!='')
            {
                $managers->city_id = $_POST['city'];
            }
            $managers->active_status    = isset($_POST['active_status'])?$_POST['active_status']:0;
            $managers->is_verified      = isset($_POST['is_verified'])?$_POST['is_verified']:0;
            //$drivers->ip_address      = Request::ip();
            $managers->created_date     = date("Y-m-d H:i:s");
            $managers->modified_date    = date("Y-m-d H:i:s");
            $managers->created_by       = Auth::id();
            $verification_key           = Text::random('alnum',12);
            $managers->verification_key = $verification_key;
            $managers->postal_code      = $_POST['postal_code'];
            $managers->address          = $_POST['address'];
            $managers->vendor_id        = Session::get('vendor_id');
            $managers->outlet_id        = $_POST['outlet_name'];
            $managers->country_code     = $_POST['country_code'];

            $a = '';
            for ($i = 0; $i<4; $i++) 
            {
                $a .= mt_rand(0,9);
            }
            //echo $a;exit();
            $managers->outlet_key     = $a;
            $managers->save();
            $this->manager_save_after($managers,$_POST);
            //$managers->hash_password = hash::make($_POST['user_password']);
            $managers->save();
            if(isset($_FILES['image']['name']) && $_FILES['image']['name']!='')
            {
                $destinationPath = base_path().'/public/assets/admin/base/images/managers/'; // upload path
                $imageName = $managers->id.'.'.$data->file('image')->getClientOriginalExtension();
                $data->file('image')->move($destinationPath, $imageName);
                $destinationPath1 = url('/assets/admin/base/images/managers/'.$imageName);
                Image::make( $destinationPath1 )->fit(75, 75)->save(base_path().'/public/assets/admin/base/images/managers/thumb/'.$imageName)->destroy();
                $managers->profile_image = $imageName;
                $managers->save();
            } 
            // redirect
            Session::flash('message', trans('messages.Outlet manager has been created successfully'));
            return Redirect::to('vendor/outletmanagers');
        }
    }

    public function manager_save_after($object,$post)
    { 
        $manager = $object->getAttributes();
        /*if($manager['is_verified'])
        {*/
            $template = DB::table('email_templates')
                        ->select('from_email', 'from', 'subject', 'template_id','content')
                        ->where('template_id','=',self::MANAGER_WELCOME_EMAIL_TEMPLATE)
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
                $content = array("driver" => $manager,'u_password' => $manager['hash_password']);
                $email   = smtp($from,$from_name,$manager['email'],$subject,$content,$template);
            }
        /*}
        else {
            $template = DB::table('email_templates')
                        ->select('from_email', 'from', 'subject', 'template_id','content')
                        ->where('template_id','=',self::MANAGER_SIGNUP_EMAIL_TEMPLATE)
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
                $url1 ='<a href="'.url('/').'/managers/confirmation?key='.$manager['verification_key'].'&email='.$manager['email'].'&u_password='.$manager['hash_password'].'"> This Confirmation Link </a>';
                $content = array("driver" => $manager,"first_name" => $manager['first_name'], "confirmation_link" => $url1);
                $email   = smtp($from,$from_name,$manager['email'],$subject,$content,$template);
            }
        }*/
    }
    /*
     * vendor based outlet list
     */
    public function getAllVendorOutletList(Request $request)
    { 
        if($request->ajax())
        {
            $vendor_id   = $request->input('vendor_name');
            $outlet_list = get_outlet_list($vendor_id);
            return response()->json([
                'data' => $outlet_list
            ]);
        }
    }
    /*
     * outlets based product list
     */
    public function getAllOutletProductList(Request $request)
    { 
        if($request->ajax())
        {
            $outlet_id    = $request->input('outlet_name');
            $product_list = get_product_list($outlet_id);
            return response()->json([
                'data' => $product_list
            ]);
        }
    }

        /**
     * Delete the specified vendor in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function outlet_managers_destory($id)
    {
        if (!Session::get('vendor_id')) {
            return redirect()->guest('vendors/login');
        }
        $data = Outlet_managers::find($id);
        if(!count($data))
        {
            Session::flash('message', 'Invalid Outlet manager details'); 
            return Redirect::to('vendor/outlet_managers');    
        }
        $data->delete();
        Session::flash('message', trans('messages.Outlet manager has been deleted successfully!'));
        return Redirect::to('vendor/outletmanagers');
    }

    /**
     * Display a listing of the categorys.
     *
     * @return Response
     */
    public function index()
    {        
        if (!Session::get('vendor_id'))
        {
            return redirect()->guest('vendors/login');
        }
        else {
            return view('vendors.products.list');
        }
    }

       /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxProductItems(Request $request)
    {
        $query = '"products_infos"."lang_id" = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = '.getAdminCurrentLang().' and products.id = products_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $query1 = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.language_id) as totalcount from outlet_infos where outlet_infos.language_id = '.getAdminCurrentLang().' and outlets.id = outlet_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $cdata = Products::join('products_infos','products.id','=','products_infos.id')
                    ->join('outlets','products.outlet_id','=','outlets.id')
                    ->join('outlet_infos','outlet_infos.id','=','outlets.id')
                    ->join("categories_infos",function($join){
                        $join->on("categories_infos.category_id","=","products.category_id")
                            ->on("categories_infos.language_id","=","products_infos.lang_id");
                    })
                    ->join("vendors_infos",function($join){
                        $join->on("vendors_infos.id","=","products.vendor_id")
                            ->on("vendors_infos.lang_id","=","products_infos.lang_id");
                    })
                    ->select('products.*','products_infos.*','categories_infos.category_name','vendors_infos.vendor_name','outlet_infos.outlet_name')
                    ->whereRaw($query)
                    ->whereRaw($query1)
                    //->where("products.active_status","=",1)
                    ->where('products.vendor_id','=',Session::get('vendor_id'))
                    ->orderBy('products.created_date', 'desc')
                    ->get();
        return Datatables::of($cdata)->addColumn('action', function ($cdata) {
                    $html='<div class="btn-group"><a href="'.URL::to("vendor/products/edit_product/".$cdata->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                                <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu xs pull-right" role="menu">
                                    <li><a href="'.URL::to("vendor/products/product_details/".$cdata->id).'" class="view-'.$cdata->id.'" title="'.trans("messages.View").'"><i class="fa fa-file-text-o"></i>&nbsp;&nbsp;'.@trans("messages.View").'</a></li>
                                    <li><a href="'.URL::to("vendor/products/delete_product/".$cdata->id).'" class="delete-'.$cdata->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                                </ul>
                            </div><script type="text/javascript">
                            $( document ).ready(function() {
                            $(".delete-'.$cdata->id.'").on("click", function(){
                                 return confirm("'.trans("messages.Are you sure want to delete?").'");
                            });});</script>';
                return $html;
            })
            ->addColumn('active_status', function ($cdata) {
                if($cdata->active_status==0):
                    $data = '<span class="label label-warning">'.trans("messages.Inactive").'</span>';
                elseif($cdata->active_status==1):
                    $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
                elseif($cdata->active_status==2):
                    $data = '<span class="label label-danger">'.trans("messages.Deleted").'</span>';
                endif;
                return $data;
            })
            ->addColumn('approval_status', function ($cdata) {
                if($cdata->approval_status==0):
                    $data = '<span class="label label-danger">'.trans("messages.UnPublished").'</span>';
                elseif($cdata->approval_status==1):
                    $data = '<span class="label label-success">'.trans("messages.Published").'</span>';
                endif;
                return $data;
            })
            ->editColumn('product_name', '{!! wordwrap($product_name,20) !!}')
            ->setRowId('id')
             ->rawColumns(['approval_status','active_status','action'])

            ->make(true);
    }

    /**
     * Show the form for creating a new product.
     * @return Response
     */
    public function product_create()
    {
        if (!Session::get('vendor_id')) {
            return redirect()->guest('vendors/login');
        } else {    
            // load the create form (resources/views/category/create.blade.php)
            return view('vendors.products.create');
        }
    }

    /**
     * Store a newly created category in storage.
     *
     * @return Response
     */
    public function product_store(Request $data)
    {
        if (!Session::get('vendor_id')) {
            return redirect()->guest('vendors/login');
        }
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation
       
        $fields['category'] = Input::get('category');
         $fields['product_type'] = Input::get('product_type');
        $fields['sub_category'] = Input::get('sub_category');
        $fields['head_category'] = Input::get('head_category');
        $fields['weight_class'] = Input::get('weight_class');
        $fields['weight_value'] = Input::get('weight_value');
        $fields['original_price'] = Input::get('original_price');
        $fields['discount_price'] = Input::get('discount_price');
        $fields['total_quantity'] = Input::get('total_quantity');
        //$fields['product_url'] = Input::get('product_url');
        $fields['publish_status'] = Input::get('publish_status');
        $fields['active_status'] = Input::get('active_status');
        $fields['product_image'] = Input::file('product_image');
        $rules = array(
           
             'category'       => 'required', 
            'sub_category'       => 'required', 
            'head_category'       => 'required',
            'product_type' => 'required',
            //'sub_category' => 'required',
            'weight_class' => 'required',
            'weight_value' => 'required|numeric',
            'total_quantity' => 'required|integer',
            'original_price' => 'required|numeric',
            'discount_price' => 'required|numeric',
            'product_image' => 'required|mimes:png,jpg,jpeg,bmp|max:2024',
            //'product_url' => 'required|regex:/(^[A-Za-z0-9-]+$)+/',
            //'publish_status' => 'required',
            //'active_status' => 'required',
        );
        $product_name = Input::get('product_name');
        foreach ($product_name  as $key => $value) {
            $fields['product_name'.$key] = $value;
            $rules['product_name'.'1'] = 'required|regex:/(^[A-Za-z0-9 ]+$)+/';
        }
        $description = Input::get('description');
        foreach ($description  as $key => $value) {
            $fields['description'.$key] = $value;
            $rules['description'.'1'] = 'required';
        }
        $meta_title = Input::get('meta_title');
        foreach ($meta_title  as $key => $value) {
            $fields['meta_title'.$key] = $value;
            $rules['meta_title'.'1'] = 'required';
        }
        $meta_keywords = Input::get('meta_keywords');
        foreach ($meta_keywords  as $key => $value) {
            $fields['meta_keywords'.$key] = $value;
            $rules['meta_keywords'.'1'] = 'required';
        }
        $meta_description = Input::get('meta_description');
        foreach ($meta_description  as $key => $value) {
            $fields['meta_description'.$key] = $value;
            $rules['meta_description'.'1'] = 'required';
        }
        $validation = Validator::make($fields, $rules);
        // process the validation
        if ($validation->fails()) {
           $errors = '';
            $j = 0;
            $error = array();
            foreach( $validation->errors()->messages() as $key => $value) 
            {
                $error[] = is_array($value)?implode( ',',str_replace("."," ",$value) ):str_replace("."," ",$value);
            }
            $errors = implode( "<br>", $error );
            $result = array("httpCode" => 400, "errors" => $errors);
        } 
        else {
					$err_msg = '';
					if($fields['original_price'] < $fields['discount_price'])
					{
						$err_msg = $validation->errors()->add('discount_price', trans('messages.Discount price should be less than original price.'));
					}
					if($err_msg != '')
					{
						return Redirect::back()->withErrors($validation)->withInput();
					}
				 else {
                       try{
						if(isset($_FILES['product_image']['name']) && $_FILES['product_image']['name']!=''){
					$profile_image_ext = $data->file('product_image')->getClientOriginalExtension();
				}
					if(isset($_FILES['product_info_image']['name']) && $_FILES['product_info_image']['name']!=''){
					$profile_info_image_ext = $data->file('product_info_image')->getClientOriginalExtension();
				}
				
					if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
					$product_zoom_image_ext = $data->file('product_zoom_image')->getClientOriginalExtension();
				}
					if( $_POST['product_type'] ==1){
						$product_id=0;
						//$vendor_id = Session::get('vendor_id');
						$outlet_list =getOutletList(Session::get('vendor_id'));
						//echo '<pre>';print_r($outlet_list);exit;
						foreach($outlet_list as $pro){
							$Products = new Products;
							$Products->outlet_id =$pro;
							$Products->vendor_id    = Session::get('vendor_id');
							$Products->outlet_id    = $pro->id;
							$Products->product_type = $_POST['product_type'];
							$Products->category_id = $_POST['category'];
			                $Products->sub_category_id = $_POST['sub_category'];
			                $Products->vendor_category_id = $_POST['head_category'];
							$Products->weight_class_id = $_POST['weight_class'];
							$Products->weight = $_POST['weight_value'];
							$Products->quantity = $_POST['total_quantity'];
							$Products->original_price = $_POST['original_price'];
							$Products->discount_price = $_POST['discount_price'];
							$Products->active_status =  isset($_POST['active_status']) ? $_POST['active_status']: 0;
							$Products->approval_status =  isset($_POST['publish_status']) ? $_POST['publish_status']: 0;
							$Products->created_date = date("Y-m-d H:i:s");
							$Products->created_by = Auth::id();
							$Products->product_url =  $_POST['product_url'] ? str_slug($_POST['product_url']): str_slug($_POST['product_name'][1]);
							$Products->save();
							if(isset($_FILES['product_image']['name']) && $_FILES['product_image']['name']!=''){
								$imageName = $Products->id.'.'.$profile_image_ext;
							    }
							    if(isset($_FILES['product_info_image']['name']) && $_FILES['product_info_image']['name']!=''){
							  	$info_imageName = $Products->id.'.'.$profile_info_image_ext;
							         }
								if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
								$zoom_imageName = $Products->id.'.'.$product_zoom_image_ext;
							     }
							if($product_id != 0)
							{
								 if(isset($_FILES['product_image']['name']) && $_FILES['product_image']['name']!=''){
									Image::make( $destinationPath2 )->save(base_path() .'/public/assets/admin/base/images/products/'.$imageName);
								               }
								      if(isset($_FILES['product_info_image']['name']) && $_FILES['product_info_image']['name']!=''){           
									Image::make( $destinationPath1 )->save(base_path() .'/public/assets/admin/base/images/products/detail/'.$info_imageName);
								}
									if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
									Image::make( $destinationPath3 )->save(base_path() .'/public/assets/admin/base/images/products/zoom/'.$zoom_imageName);
								     }
							}
							else {
								
								if(isset($_FILES['product_image']['name']) && $_FILES['product_image']['name']!=''){
									$destinationPath2 = url('/assets/admin/base/images/products/'.$imageName);
									$data->file('product_image')->move(base_path().'/public/assets/admin/base/images/products/', $imageName);
								}
								if(isset($_FILES['product_info_image']['name']) && $_FILES['product_info_image']['name']!=''){
									$destinationPath1 = url('/assets/admin/base/images/products/detail/'.$info_imageName);
									$data->file('product_info_image')->move(base_path().'/public/assets/admin/base/images/products/detail/', $info_imageName);
								}
									if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
									$destinationPath3 = url('/assets/admin/base/images/products/zoom/'.$zoom_imageName);
									$data->file('product_zoom_image')->move(base_path().'/public/assets/admin/base/images/products/zoom/', $zoom_imageName);
								     }
							}
							$size = getImageResize('PRODUCT');
								if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
								Image::make( $destinationPath2 )->fit($size['LIST_WIDTH'], $size['LIST_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/list/'.$imageName);

								Image::make( $destinationPath2 )->fit($size['THUMB_WIDTH'], $size['THUMB_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/thumb/'.$imageName);
							}
							if(isset($_FILES['product_info_image']['name']) && $_FILES['product_info_image']['name']!=''){
								$Products->product_info_image = $info_imageName;
							}
							if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
								$Products->product_zoom_image = $zoom_imageName;
							}
							if(isset($_FILES['product_image']['name']) && $_FILES['product_image']['name']!=''){
								$Products->product_image = $imageName;
							}
							$Products->save();
							$this->product_save_after($Products,$_POST);
							$product_id = $Products->id;
						}
					}
					if( $_POST['product_type'] ==2)
					{           $product_id = 0;
						if(count($_POST['outlet']) > 0)
						{
							foreach($_POST['outlet'] as $pro)
							{   
							    $Products = new Products;
								$Products->vendor_id    = Session::get('vendor_id');
								//$Products->outlet_id    = $_POST['outlet'];
								$Products->product_type = $_POST['product_type'];
								$Products->outlet_id = $pro;
								$Products->category_id = $_POST['category'];
			                $Products->sub_category_id = $_POST['sub_category'];
			                $Products->vendor_category_id = $_POST['head_category'];
								$Products->weight_class_id = $_POST['weight_class'];
								$Products->weight = $_POST['weight_value'];
								$Products->quantity = $_POST['total_quantity'];
								$Products->original_price = $_POST['original_price'];
								$Products->discount_price = $_POST['discount_price'];
								$Products->active_status =  isset($_POST['active_status']) ? $_POST['active_status']: 0;
								$Products->approval_status =  isset($_POST['publish_status']) ? $_POST['publish_status']: 0;
								$Products->created_date = date("Y-m-d H:i:s");
								$Products->created_by = Auth::id();
								$Products->product_url =  $_POST['product_url'] ? str_slug($_POST['product_url']): str_slug($_POST['product_name'][1]);
								$Products->save();
									if(isset($_FILES['product_image']['name']) && $_FILES['product_image']['name']!=''){
										$imageName = $Products->id.'.'.$profile_image_ext;
									}
									   if(isset($_FILES['product_info_image']['name']) && $_FILES['product_info_image']['name']!=''){
										$info_imageName = $Products->id.'.'.$profile_info_image_ext;
									}
										if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
								        $zoom_imageName = $Products->id.'.'.$product_zoom_image_ext;
							         }
								if($product_id != 0)
								{
									 if(isset($_FILES['product_image']['name']) && $_FILES['product_image']['name']!=''){
											Image::make( $destinationPath2 )->save(base_path() .
											'/public/assets/admin/base/images/products/'.$imageName);
										}
										if(isset($_FILES['product_info_image']['name']) && $_FILES['product_info_image']['name']!=''){
											Image::make( $destinationPath1 )->save(base_path() .'/public/assets/admin/base/images/products/detail/'.$info_imageName);
										}
										
											if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
											Image::make( $destinationPath3 )->save(base_path() .'/public/assets/admin/base/images/products/zoom/'.$zoom_imageName);
										   }
								}
								else {
								    
											if(isset($_FILES['product_image']['name']) && $_FILES['product_image']['name']!=''){
												$destinationPath2 = url('/assets/admin/base/images/products/'.$imageName);
											$data->file('product_image')->move(base_path().'/public/assets/admin/base/images/products/', $imageName);
										}
										    if(isset($_FILES['product_info_image']['name']) && $_FILES['product_info_image']['name']!=''){
												$destinationPath1 = url('/assets/admin/base/images/products/detail/'.$info_imageName);
											$data->file('product_info_image')->move(base_path().'/public/assets/admin/base/images/products/detail/', $info_imageName);
										         }
											if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
												
											$destinationPath3 = url('/assets/admin/base/images/products/zoom/'.$info_imageName);
									        $data->file('product_zoom_image')->move(base_path().'/public/assets/admin/base/images/products/zoom/', $zoom_imageName);
								             }
								}
								
										$size = getImageResize('PRODUCT');
										$size = getImageResize('PRODUCT');
										 if(isset($_FILES['product_image']['name']) && $_FILES['product_image']['name']!=''){
										Image::make( $destinationPath2 )->fit($size['LIST_WIDTH'], $size['LIST_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/list/'.$imageName)->destroy();
									}
										 if(isset($_FILES['product_image']['name']) && $_FILES['product_image']['name']!=''){
										Image::make( $destinationPath2 )->fit($size['THUMB_WIDTH'], $size['THUMB_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/thumb/'.$imageName)->destroy();
									}
									 if(isset($_FILES['product_info_image']['name']) && $_FILES['product_info_image']['name']!=''){
										Image::make( $destinationPath1 )->save(base_path() .'/public/assets/admin/base/images/products/detail/'.$info_imageName);
									}
										 if(isset($_FILES['product_image']['name']) && $_FILES['product_image']['name']!=''){
										$Products->product_image=$imageName;
									}
									 if(isset($_FILES['product_info_image']['name']) && $_FILES['product_info_image']['name']!=''){
									    $Products->product_info_image=$info_imageName;
									}
									    if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
									    $Products->product_zoom_image = $zoom_imageName;
									}
								$Products->save();
								$this->product_save_after($Products,$_POST);
								$product_id = $Products->id;
							}
						}
					}
					// redirect
					$result["status"] = 200;
					Session::flash('message', trans('messages.Product has been created successfully'));
					$result["errors"] = "";
				}
					 catch(Exception $e) {
					Log::Instance()->add(Log::ERROR, $e);
				}					 
				 
				     
			}
	    }
		  return json_encode($result);exit;
    }
    /**
     * add,edit datas  saved in main table 
     * after inserted in sub tabel.
     *
     * @param  int  $id
     * @return Response
     */
   public static function product_save_after($object,$post)
   {
        if(isset($post['product_name'])){
            $product_name = $post['product_name'];
            $description = $post['description'];
            $meta_title = $post['meta_title'];
            $meta_keywords = $post['meta_keywords'];
            $meta_description = $post['meta_description'];
            try{
                $affected = DB::table('products_infos')->where('id', '=', $object->id)->delete();
                $languages = DB::table('languages')->where('status', 1)->get();
                foreach($languages as $key => $lang){
                    if(isset($product_name[$lang->id]) && $product_name[$lang->id]!=""){
                        $infomodel = new Products_infos;
                        $infomodel->lang_id = $lang->id;
                        $infomodel->id = $object->id; 
                        $infomodel->product_name = $product_name[$lang->id];
                        $infomodel->description = $description[$lang->id];
                        $infomodel->meta_title = $meta_title[$lang->id];
                        $infomodel->meta_keywords = $meta_keywords[$lang->id];
                        $infomodel->meta_description = $meta_description[$lang->id];
                        $infomodel->save();
                    }
                }
            }catch(Exception $e) {
                Log::Instance()->add(Log::ERROR, $e);
            }
        }
    }

    /**
     * Show the form for editing the specified category.
     *
     * @param  int  $id
     * @return Response
     */
    public function product_edit($id)
    {
        if (!Session::get('vendor_id')) {
            return redirect()->guest('vendors/login');
        } else {
            $data = Products::find($id);
            if(!count($data)){
                Session::flash('message', 'Invalid Product Details'); 
                return Redirect::to('vendor/products');    
            }
            $info = new Products_infos;
            return view('vendors.products.edit')->with('data', $data)->with('infomodel', $info);
        }
    }

    /**
     * Update the specified category in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update_product(Request $data, $id)
    {
        if (!Session::get('vendor_id')) {
            return redirect()->guest('vendors/login');
        }
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $fields['outlet'] = Input::get('outlet');
        $fields['category'] = Input::get('category');
        $fields['sub_category'] = Input::get('sub_category');
        $fields['head_category'] = Input::get('head_category');
       // $fields['sub_category'] = Input::get('sub_category');
        $fields['weight_class'] = Input::get('weight_class');
        $fields['weight_value'] = Input::get('weight_value');
        $fields['original_price'] = Input::get('original_price');
        $fields['discount_price'] = Input::get('discount_price');
        $fields['total_quantity'] = Input::get('total_quantity');
        $fields['product_url'] = Input::get('product_url');
        $fields['publish_status'] = Input::get('publish_status');
        $fields['active_status'] = Input::get('active_status');
        $fields['product_image'] = Input::file('product_image');
        $rules = array(
            'outlet' => 'required',
            'category'       => 'required', 
            'sub_category'       => 'required', 
            'head_category'       => 'required',
            //'sub_category' => 'required',
            'weight_class' => 'required',
            'weight_value' => 'required|numeric',
            'total_quantity' => 'required|integer',
            'original_price' => 'required|numeric',
            'discount_price' => 'required|numeric',
            'product_image' => 'mimes:png,jpg,jpeg,bmp|max:2024',
            //'product_url' => 'required|regex:/(^[A-Za-z0-9-]+$)+/',
            //'publish_status' => 'required',
            //'active_status' => 'required',
        );
        $product_name = Input::get('product_name');
        foreach ($product_name  as $key => $value) {
            $fields['product_name'.$key] = $value;
            $rules['product_name'.'1'] = 'required|regex:/(^[A-Za-z0-9 ]+$)+/';
        }
        $description = Input::get('description');
        foreach ($description  as $key => $value) {
            $fields['description'.$key] = $value;
            $rules['description'.'1'] = 'required';
        }
        $meta_title = Input::get('meta_title');
        foreach ($meta_title  as $key => $value) {
            $fields['meta_title'.$key] = $value;
            $rules['meta_title'.'1'] = 'required';
        }
        $meta_keywords = Input::get('meta_keywords');
        foreach ($meta_keywords  as $key => $value) {
            $fields['meta_keywords'.$key] = $value;
            $rules['meta_keywords'.'1'] = 'required';
        }
        $meta_description = Input::get('meta_description');
        foreach ($meta_description  as $key => $value) {
            $fields['meta_description'.$key] = $value;
            $rules['meta_description'.'1'] = 'required';
        }
        $validation = Validator::make($fields, $rules);
        // process the validation
        if ($validation->fails()) {
              $errors = '';
            $j = 0;
            $error = array();
            foreach( $validation->errors()->messages() as $key => $value) 
            {
                $error[] = is_array($value)?implode( ',',str_replace("."," ",$value) ):str_replace("."," ",$value);
            }
            $errors = implode( "<br>", $error );
            $result = array("httpCode" => 400, "errors" => $errors);return json_encode($result);exit;
        } else { 
            $err_msg = '';
            if($fields['original_price'] < $fields['discount_price'])
            {
                $err_msg = $validation->errors()->add('discount_price', trans('messages.Discount price should be less than original price.'));
            }
            if($err_msg != '')
            {
                return Redirect::back()->withErrors($validation)->withInput();
            }
            else {
            // store datas in to database
            $Products = Products::find($id);
            $Products->vendor_id    = Session::get('vendor_id');
            $Products->outlet_id    = $_POST['outlet'];
            $Products->category_id = $_POST['category'];
			$Products->sub_category_id = $_POST['sub_category'];
			$Products->vendor_category_id = $_POST['head_category'];
            $Products->weight_class_id = $_POST['weight_class'];
            $Products->weight = $_POST['weight_value'];
            $Products->quantity = $_POST['total_quantity'];
            $Products->original_price = $_POST['original_price'];
            $Products->discount_price = $_POST['discount_price'];
            $Products->active_status =  isset($_POST['active_status']) ? $_POST['active_status']: 0;
		    $Products->approval_status =  isset($_POST['publish_status']) ? $_POST['publish_status']: 0;
            $Products->modified_date = date("Y-m-d H:i:s");
            $Products->product_url =  $_POST['product_url'] ? str_slug($_POST['product_url']): str_slug($_POST['product_name'][1]);
            $Products->save();
            
            if(isset($_FILES['product_image']['name']) && $_FILES['product_image']['name']!=''){ 
                $imageName = $id . '.' . $data->file('product_image')->getClientOriginalExtension();
                $data->file('product_image')->move(
                    base_path() . '/public/assets/admin/base/images/products/', $imageName
                );
                $destinationPath2 = url('/assets/admin/base/images/products/'.$imageName.'');
                $size=getImageResize('PRODUCT');
                Image::make( $destinationPath2 )->fit($size['LIST_WIDTH'], $size['LIST_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/list/'.$imageName)->destroy();
                /*Image::make( $destinationPath2 )->fit($size['DETAIL_WIDTH'], $size['DETAIL_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/detail/'.$imageName)->destroy();*/
                Image::make( $destinationPath2 )->fit($size['THUMB_WIDTH'], $size['THUMB_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/thumb/'.$imageName)->destroy();
                $Products->product_image=$imageName;
                $Products->save();
            }
 if(isset($_FILES['product_info_image']['name']) && $_FILES['product_info_image']['name']!=''){
				$info_imageName = $id . '.' . $data->file('product_info_image')->getClientOriginalExtension();
                $data->file('product_info_image')->move(
                    base_path() . '/public/assets/admin/base/images/products/detail', $info_imageName
                );
                $Products->product_info_image=$info_imageName;
                $Products->save();
		}
		if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
			$zoom_imageName = $id . '.' . $data->file('product_zoom_image')->getClientOriginalExtension();
                $data->file('product_zoom_image')->move(
                    base_path() . '/public/assets/admin/base/images/products/zoom', $zoom_imageName
                );
                $Products->product_zoom_image=$zoom_imageName;
                $Products->save();
				}
            $this->product_save_after($Products,$_POST);
            // redirect
             $result["status"] = 200;
            Session::flash('message',trans('Product updated successfully'));
            $result["errors"] = "";
        }
    } 
    return json_encode($result);exit;	
  }

    /**
     * Display the specified category.
     *
     * @param  int  $id
     * @return Response
     */
    public function product_show($id)
    {
        if (!Session::get('vendor_id')){
            return redirect()->guest('vendors/login');
        } else {
            $query = '"products_infos"."lang_id" = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = '.getAdminCurrentLang().' and products.id = products_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $query1 = '"outlet_infos"."language_id" = (case when (select count(language_id) as totalcount from outlet_infos where outlet_infos.language_id = '.getAdminCurrentLang().' and products.outlet_id = outlet_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $data = DB::table('products')
                    ->join('products_infos','products.id','=','products_infos.id')
                    ->join('outlet_infos','products.outlet_id','=','outlet_infos.id')
                    ->join("categories_infos",function($join){
                        $join->on("categories_infos.category_id","=","products.category_id")
                            ->on("categories_infos.language_id","=","products_infos.lang_id");
                    })
                    ->join("vendors_infos",function($join){
                        $join->on("vendors_infos.id","=","products.vendor_id")
                            ->on("vendors_infos.lang_id","=","products_infos.lang_id");
                    })
                    ->select('products.*','products_infos.*','categories_infos.category_name','vendors_infos.vendor_name','outlet_infos.outlet_name')
                    ->whereRaw($query)
                    ->where('products.id',$id)
                    ->where('products.vendor_id','=',Session::get('vendor_id'))
                    ->orderBy('products.created_date', 'desc')
                    ->get();
            if(!count($data))
            {
                Session::flash('message', 'Invalid Product Details'); 
                return Redirect::to('vendors/products');
            }
            $info = new Products_infos;
            return view('vendors.products.show')->with('data', $data)->with('infomodel', $info);
        }
    }

    /**
     * Delete the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function product_destory($id)
    {
        if (!Session::get('vendor_id')) {
            return redirect()->guest('vendors/login');
        }
        $data = Products::find($id);
        if(!count($data)){
            Session::flash('message', 'Invalid Product Details'); 
            return Redirect::to('vendors/products');    
        }
        //$data->delete();
        //Update delete status while deleting
        $data->active_status = 2;
        $data->save();
        Session::flash('message', trans('messages.Product has been deleted successfully!'));
        return Redirect::to('vendor/products');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function reviews()
    {
        if (!Session::get('vendor_id')){
            return redirect()->guest('vendors/login');
        } else {
            return view('vendors.reviews.list');
        }
    }
    
         /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function anyAjaxreviewlistvendor()
    {
        $query = '"outlet_infos"."language_id" = (case when (select count(language_id) as totalcount from outlet_infos where outlet_infos.language_id = '.getAdminCurrentLang().' and outlets.id = outlet_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $reviews = DB::table('outlet_reviews')
                    ->select('outlet_reviews.id as review_id','outlet_reviews.customer_id as review_customer_id','outlet_reviews.vendor_id as review_vendor_id','outlet_reviews.comments','outlet_reviews.title','outlet_reviews.approval_status','outlet_reviews.ratings','outlet_reviews.created_date as review_posted_date','users.name as user_name','users.email as user_email','users.id as user_id','users.image as user_image','vendors.id as store_id','vendors.first_name as store_first_name','vendors.last_name as store_last_name','vendors.email as store_email','vendors.phone_number as store_phone_number','outlet_infos.outlet_name','outlets.id as outletid')
                    ->leftJoin('users','users.id','=','outlet_reviews.customer_id')
                    ->leftJoin('outlets','outlets.id','=','outlet_reviews.outlet_id')
                    ->leftJoin('outlet_infos','outlets.id','=','outlet_infos.id')
                    ->leftJoin('vendors','vendors.id','=','outlets.vendor_id')
                    ->where("outlet_reviews.vendor_id","=",Session::get('vendor_id'))
                    ->whereRaw($query)
                    ->orderBy('outlet_reviews.id', 'desc');
            return Datatables::of($reviews)->addColumn('action', function ($reviews) {
                return '<div class="btn-group"><a href="'.URL::to("vendors/reviews/view/?review_id=".$reviews->review_id).'" class="btn btn-xs btn-white" title="'.trans("messages.View").'"><i class="fa fa-eye"></i>&nbsp;'.trans("messages.View").'</a>
                        <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                        </button>
                    </div><script type="text/javascript">
                    $( document ).ready(function() {
                    $(".delete-'.$reviews->review_id.'").on("click", function(){
                         return confirm("'.trans("messages.Are you sure want to approve ?").'");
                    });});</script>';
            })
            ->addColumn('approval_status', function ($reviews) {
                if($reviews->approval_status==0):
                    $data = '<span  class="label label-danger">'.trans("messages.Pending").'</span>';
                elseif($reviews->approval_status==1):
                    $data = '<span  class="label label-success">'.trans("messages.Approved").'</span>';
                endif;
                return $data;
            })
            ->addColumn('transaction_date', function ($reviews) {
                    $data = '<span> '.date('d - M - Y h:i A' , strtotime($reviews->review_posted_date)).'</span>';
                return $data;
            })
                         ->rawColumns(['transaction_date','approval_status','action'])

            ->make(true);
    }
    
    /**
     * Display the specified review.
     *
     * @param  int  $id
     * @return Response
     */
    public function view_review()
    {
        if (!Session::get('vendor_id'))
        {
            return redirect()->guest('vendors/login');
        }
        else {
            $review_id = Input::get('review_id');
            $query1    = '"outlet_infos"."language_id" = (case when (select count(language_id) as totalcount from outlet_infos where outlet_infos.language_id = '.getAdminCurrentLang().' and outlets.id = outlet_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $reviews   = DB::table('outlet_reviews')
                            ->select('outlet_reviews.id as review_id','outlet_reviews.customer_id as review_customer_id','outlet_reviews.vendor_id as review_vendor_id','outlet_reviews.comments','outlet_reviews.title','outlet_reviews.approval_status','outlet_reviews.ratings','outlet_reviews.created_date as review_posted_date','users.name as user_name','users.email as user_email','users.id as user_id','users.image as user_image','vendors.id as store_id','vendors.first_name as store_first_name','vendors.last_name as store_last_name','vendors.email as store_email','vendors.phone_number as store_phone_number','outlet_infos.outlet_name','outlets.id as outletid')
                            ->leftJoin('users','users.id','=','outlet_reviews.customer_id')
                            ->leftJoin('outlets','outlets.id','=','outlet_reviews.outlet_id')
                            ->leftJoin('outlet_infos','outlets.id','=','outlet_infos.id')
                            ->leftJoin('vendors','vendors.id','=','outlets.vendor_id')
                            ->where("outlet_reviews.id","=",$review_id)
                            ->where("outlet_reviews.vendor_id","=",Session::get('vendor_id'))
                            ->whereRaw($query1)
                            ->get();
            if(!count($reviews))
            {
                Session::flash('message', trans('messages.Invalid Request'));
                return Redirect::to('vendors/reviews');
            }
            return view('vendors.reviews.show')->with('review', $reviews[0]);
        }
    }
    /*
    * Return orders listing
    */
    public function return_orders_list()
    {
        if (!Session::get('vendor_id'))
        {
            return redirect()->guest('vendors/login');
        }
        else {
            $vendor_id = Session::get('vendor_id');
            $condition = '1=1';
            if(Input::get('from') && Input::get('to'))
            {
                $from = date('Y-m-d H:i:s', strtotime(Input::get('from')));
                $to = date('Y-m-d H:i:s', strtotime(Input::get('to')));
                $condition .=" and return_orders.created_at BETWEEN '".$from."'::timestamp and '".$to."'::timestamp";
            }
            if(Input::get('outlet'))
            {
                $outlet = Input::get('outlet');
                $condition .=" and orders.outlet_id = ".$outlet."";
            }
            $query  = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = '.getAdminCurrentLang().' and orders.outlet_id = outlet_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $query1 = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = '.getAdminCurrentLang().' and orders.vendor_id = vendors_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $list   = DB::table('return_orders')
                        ->select('return_orders.id','return_orders.return_comments','return_orders.created_at','return_orders.modified_at','return_action.name as return_action_name','return_reason.name as return_reason_name','return_status.name as return_status_name','users.name as username','outlet_infos.outlet_name','vendors_infos.vendor_name','orders.id as order_id')
                        ->leftJoin('return_action','return_action.id','=','return_orders.return_action_id')
                        ->leftJoin('return_reason','return_reason.id','=','return_orders.return_reason')
                        ->leftJoin('return_status','return_status.id','=','return_orders.return_status')
                        ->leftJoin('orders','orders.id','=','return_orders.order_id')
                        ->leftJoin('users','users.id','=','orders.customer_id')
                        ->leftJoin('vendors_infos','vendors_infos.id','=','orders.vendor_id')
                        ->leftJoin('outlet_infos','outlet_infos.id','=','orders.outlet_id')
                        ->where('orders.vendor_id',$vendor_id)
                        ->whereRaw($query)
                        ->whereRaw($query1)
                        ->whereRaw($condition)
                        ->orderBy('return_orders.created_at', 'desc')
                        ->paginate(10);
            if(Input::get('export'))
            {
                $out = '"Order Id","Name","Vendor Name","Outlet Name","Status","Total Amount","Payment Mode","Order Date"'."\r\n";
                foreach($orders as $d)
                {
                    $out .= $d->id.',"'.$d->user_name.'","'.$d->vendor_name.'","'.$d->outlet_name.'","'.$d->status_name.'","'.$d->total_amount.$d->currency_code.'","'.$d->payment_gateway_name.'","'.date("d F, Y", strtotime($d->order_created)).'"'."\r\n";
                }
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=returnOrders.csv');
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                echo "\xEF\xBB\xBF"; // UTF-8 BOM
                echo $out;
                exit;
            }
            return view('vendors.return_orders.list')->with('data', $list);
        }
    }
    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxReturnOrders()
    {
        if (!Session::get('vendor_id')) {
            return redirect()->guest('vendors/login');
        } else {
            $vendor_id = Session::get('vendor_id');
            $list = DB::table('return_orders')
                    ->select('return_orders.*','return_action.name as return_action_name','return_reason.name as return_reason_name','return_status.name as return_status_name','orders.order_key_formated','users.name as username')
                    ->leftJoin('return_action','return_action.id','=','return_orders.return_action_id')
                    ->leftJoin('return_reason','return_reason.id','=','return_orders.return_reason')
                    ->leftJoin('return_status','return_status.id','=','return_orders.return_status')
                    ->leftJoin('orders','orders.id','=','return_orders.order_id')
                    ->leftJoin('users','users.id','=','orders.customer_id')
                    ->where('orders.vendor_id',$vendor_id)
                    ->orderBy('return_orders.created_at', 'desc');
            return Datatables::of($list)->addColumn('action', function ($list) {
                    return '<div class="btn-group"><a href="'.URL::to("vendors/return_orders_view/".$list->id).'" class="btn btn-xs btn-white" title="'.trans("messages.View").'"><i class="fa fa-file-text-o"></i>&nbsp;'.trans("messages.View").'</a></div>';
                })
                ->addColumn('return_comments', function ($list) {
                    $data = '-';
                    if(!empty(trim($list->return_comments))):
                        $data = trim($list->return_comments);
                    endif;
                    return $data;
                })   
                           ->rawColumns(['return_comments','action'])

                ->make(true);
        }
    }

    /**
     * Display the specified return order details here.
     *
     * @param  int  $id
     * @return Response
     */
    public function return_orders_show($id)
    {
        if (!Session::get('vendor_id')) {
            return redirect()->guest('vendors/login');
        } 
        else {
            $vendor_id = Session::get('vendor_id');
            $query = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = '.getAdminCurrentLang().' and vendors.id = vendors_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $query1 = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.language_id) as totalcount from outlet_infos where outlet_infos.language_id = '.getAdminCurrentLang().' and outlets.id = outlet_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $os_query = '"order_status"."lang_id" = (case when (select count(*) as totalcount from order_status where order_status.lang_id = '.getAdminCurrentLang().' and orders.order_status = order_status.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $pay_query = 'payment_gateways_info.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.getAdminCurrentLang().' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $data = DB::table('return_orders')
                        ->select('return_orders.*','return_action.name as return_action_name','return_reason.name as return_reason_name','return_status.name as return_status_name','orders.order_key_formated','users.name as username','users.email','users.first_name','users.last_name','users.mobile','outlet_infos.outlet_name','vendors_infos.vendor_name','orders.order_key','orders.invoice_id','orders.total_amount','orders.vendor_id','orders.customer_id','orders.outlet_id','orders.delivery_date','orders.delivery_charge','orders.service_tax','orders.coupon_amount','order_status.name as order_status','orders.delivery_instructions','orders.created_date as ordered_date','user_address.address','payment_gateways.id as payment_gateway_id','payment_gateways_info.name','delivery_time_interval.start_time','delivery_time_interval.end_time','outlet_infos.contact_address','transaction.currency_code')
                        ->leftJoin('return_action','return_action.id','=','return_orders.return_action_id')
                        ->leftJoin('return_reason','return_reason.id','=','return_orders.return_reason')
                        ->leftJoin('return_status','return_status.id','=','return_orders.return_status')
                        ->leftJoin('orders','orders.id','=','return_orders.order_id')
                        ->leftJoin('users','users.id','=','orders.customer_id')
                        ->leftJoin('vendors','vendors.id','=','orders.vendor_id')
                        ->Join('transaction','transaction.order_id','=','return_orders.order_id')
                        ->leftJoin("vendors_infos",function($join){
                            $join->on("vendors_infos.id","=","orders.vendor_id")
                                ->on("vendors_infos.id","=","vendors.id");
                        })
                        ->leftJoin('outlets','outlets.id','=','orders.outlet_id')
                        ->leftJoin('outlet_infos','outlet_infos.id','=','outlets.id')
                        ->leftJoin('order_status','order_status.id','=','orders.order_status')
                        ->leftJoin('user_address','user_address.id', '=', 'orders.delivery_address')
                        ->leftJoin('payment_gateways','payment_gateways.id', '=', 'orders.payment_gateway_id')
                        ->leftJoin('payment_gateways_info','payment_gateways_info.payment_id', '=', 'payment_gateways.id')
                        ->leftJoin('delivery_time_slots','delivery_time_slots.id', '=', 'orders.delivery_slot')
                        ->leftJoin('delivery_time_interval','delivery_time_interval.id', '=', 'delivery_time_slots.time_interval_id')
                        ->whereRaw($query)
                        ->whereRaw($query1)
                        ->whereRaw($os_query)
                        ->whereRaw($pay_query)
                        ->where('return_orders.id',$id)
                        ->where('orders.vendor_id',$vendor_id)
                        ->orderBy('return_orders.created_at', 'desc')
                        ->get();
            $items_info = array();
            if(count($data)> 0 )
            {
                $pquery     = 'products_infos.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = '.getAdminCurrentLang().' and products.id = products_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
                $items_info = DB::table('orders')
                                ->select('products.product_image','products.id AS product_id','orders_info.item_cost','orders_info.item_unit','orders_info.item_offer','products_infos.product_name','products_infos.description')
                                ->leftJoin('orders_info', 'orders_info.order_id', '=','orders.id')
                                ->leftJoin('products', 'products.id', '=','orders_info.item_id')
                                ->leftJoin('products_infos', 'products_infos.id', '=','products.id')
                                ->whereRaw($pquery)
                                ->where('orders.id',$data[0]->order_id)
                                ->orderBy('products.id', 'desc')
                                ->get();
            }
            if(!count($data))
            {
                Session::flash('message', 'Invalid Order Details'); 
                return Redirect::to('orders/return_orders');
            }
            $return_reasons     = return_reasons::all();
            $return_statuses    = return_status::all();
            $return_actions     = return_actions::all();
            $return_orders_logs = DB::table('return_orders_log')
                                    ->select('return_status.name AS return_status_name','return_action.name AS return_actions_name','return_orders_log.created_date as date_added','return_orders_log.modified_date as date_changed','return_orders_log.customer_notified')
                                    //->leftJoin('return_orders', 'return_orders.id', '=','return_orders_log.order_id')
                                    ->leftJoin('return_status', 'return_status.id', '=','return_orders_log.return_status')
                                    ->leftJoin('return_action', 'return_action.id', '=','return_orders_log.return_action')
                                    ->where('return_orders_id',$id)
                                    ->get();
            return view('vendors.return_orders.show')->with('data', $data)->with('items_data', $items_info)->with('return_reasons', $return_reasons)->with('return_statuses', $return_statuses)->with('return_actions', $return_actions)->with('return_orders_logs', $return_orders_logs);
        }
    }
    /*
    * Update the return_orders & orders table
    */
    public function return_orders_update(Request $data,$id)
    {
        if (!Session::get('vendor_id'))
        {
            return redirect()->guest('vendors/login');
        } else
        {
            $vendor_id = Session::get('vendor_id');
            // validate
            // read more on validation at http://laravel.com/docs/validation
            $fields['return_order_id'] = $id;
            $fields['order_id'] = Input::get('order_id');
            $fields['vendor_id'] = Input::get('vendor_id');
            $fields['customer_id'] = Input::get('customer_id');
            $fields['return_action'] = Input::get('return_action');
            $fields['return_reason'] = Input::get('return_reason');
            $fields['return_status'] = Input::get('return_status');
            $fields['outlet_id'] = Input::get('outlet_id');
            $rules = array(
                'return_action' => 'required',
                'return_reason' => 'required',
                'return_status' => 'required',
            );
            $validation = Validator::make($fields, $rules);
            // process the validation
            if ($validation->fails())
            {
                   return Redirect::back()->withErrors($validation)->withInput();
            }
            else { 
                // store datas in to database
                $Return_orders = return_orders::find($id);
                $Return_orders->return_status = $_POST['return_status'];
                $Return_orders->return_reason = $_POST['return_reason'];
                $Return_orders->return_action_id = $_POST['return_action'];
                $Return_orders->save();

                $Orders = new return_orders_log;
                $Orders->vendor_id    = $_POST['vendor_id'];
                $Orders->outlet_id    = $_POST['outlet_id'];
                $Orders->order_id    = $_POST['order_id'];
                $Orders->return_orders_id = $id;
                $Orders->customer_id = $_POST['customer_id'];
                $Orders->return_status = $_POST['return_status'];
                $Orders->return_reason = $_POST['return_reason'];
                $Orders->return_action = $_POST['return_action'];
                $Orders->modified_date = date("Y-m-d H:i:s");
                $Orders->created_date = date("Y-m-d H:i:s");
                $Orders->customer_notified = 1;
                $Orders->modified_by = $vendor_id;
                $Orders->save();
                $this->return_orders_save_after($Orders,$_POST);
                // redirect
                Session::flash('message', trans('messages.Return Status has been successfully updated'));
                return Redirect::to('vendors/return_orders');
            }
        }
    }
    //Send mail to customer regarding the return order status was updated
    public static function return_orders_save_after($data,$post)
    {
        //Sending the mail to vendors
        $template=DB::table('email_templates')
                ->select('*')
                ->where('template_id','=',self::RETURN_STATUS_CUSTOMER_EMAIL_TEMPLATE)
                ->get();
        if(count($template))
        {
            $from = $template[0]->from_email;
            $from_name = $template[0]->from;
            $subject = $template[0]->subject;
            if(!$template[0]->template_id)
            {
                $template = 'mail_template';
                $from = getAppConfigEmail()->contact_email;
                $subject = getAppConfig()->site_name." Return Order Status Information";
                $from_name = "";
            }
            $customer = Users::find($post['customer_id']);
            $return_status = return_status::find($post['return_status']);
            $return_action = return_actions::find($post['return_action']);
            $cont_replace = "Your return order <b>". $post['invoice_id'] ."</b> status has been updated with store or outlet.";
            $cont_replace1 = "Your order has been <b>". $return_status->name ."</b> and make it necessary arrangements we are waiting for <b>". $return_action->name."</b>";
            $content = array("name" => $customer->name,"order_key"=>$post['invoice_id'],"return_status"=> $return_status->name ,"return_action"=> $return_action->name);
            $email = smtp($from,$from_name,$customer->email,$subject,$content,$template);
            return;
        }
    }

    public function orders()
    {
        if (!Session::get('vendor_id'))
        {
            return redirect()->guest('vendors/login');
        }
        else {
            $vendor_id = Session::get('vendor_id');
            $condition ="orders.order_type!=0";
            if(Input::get('from') && Input::get('to'))
            {
                $from = date('Y-m-d H:i:s', strtotime(Input::get('from')));
                $to = date('Y-m-d H:i:s', strtotime(Input::get('to')));
                $condition .=" and orders.created_date BETWEEN '".$from."'::timestamp and '".$to."'::timestamp";
            }
            if(Input::get('from_amount') && Input::get('to_amount'))
            {
                $from_amount = Input::get('from_amount');
                $to_amount = Input::get('to_amount');
                $condition .=" and orders.total_amount BETWEEN '".$from_amount."' and '".$to_amount."'";
            }
            if(Input::get('order_status'))
            {
                $order_status = Input::get('order_status');
                $condition .=" and orders.order_status = ".$order_status."";
            }
            if(Input::get('payment_type'))
            {
                $payment_type = Input::get('payment_type');
                $condition .=" and orders.payment_gateway_id = ".$payment_type."";
            }
            if(Input::get('outlet'))
            {
                $outlet = Input::get('outlet');
                $condition .=" and orders.outlet_id = ".$outlet."";
            }
            $query  = '"payment_gateways_info"."language_id" = (case when (select count(payment_gateways_info.payment_id) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.getAdminCurrentLang().' and orders.payment_gateway_id = payment_gateways_info.payment_id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $query1 = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = '.getAdminCurrentLang().' and orders.outlet_id = outlet_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $query2 = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = '.getAdminCurrentLang().' and orders.vendor_id = vendors_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $orders = DB::table('orders')
                        ->select('orders.id','orders.total_amount','orders.created_date','orders.modified_date','orders.delivery_date','users.first_name','users.last_name','order_status.name as status_name','order_status.color_code as color_code','users.name as user_name','transaction.currency_code','payment_gateways_info.name as payment_type','outlet_infos.outlet_name','vendors_infos.vendor_name as vendor_name','orders.id','outlet_infos.contact_address','vendors_infos.id as vendor_id', 'drivers.first_name as driver_name','orders.request_vendor as request_vendor','outlets.latitude as outlet_latitude','outlets.longitude as outlet_longitude') 
                        ->leftJoin('users','users.id','=','orders.customer_id')
                        ->leftJoin('order_status','order_status.id','=','orders.order_status')
                        ->leftjoin('transaction','transaction.order_id','=','orders.id')
                        ->Join('payment_gateways_info','payment_gateways_info.payment_id','=','orders.payment_gateway_id')
                        ->Join('vendors_infos','vendors_infos.id','=','orders.vendor_id')
                        ->Join('outlets','outlets.id','=','orders.outlet_id')
                        ->Join('outlet_infos','outlet_infos.id','=','orders.outlet_id')
                        ->leftJoin('driver_orders', 'driver_orders.order_id', '=', 'orders.id')
                        ->leftJoin('drivers', 'drivers.id', '=', 'driver_orders.driver_id')
                        ->where('orders.vendor_id',$vendor_id)
                        ->whereRaw($query)
                        ->whereRaw($query1)
                        ->whereRaw($query2)
                        ->whereRaw($condition)
                        ->orderBy('orders.id', 'desc')
                        ->paginate(10);
            $order_status = DB::table('order_status')->select('id','name')->orderBy('name', 'asc')->get();
            $query3       = '"payment_gateways_info"."language_id" = (case when (select count(payment_gateways_info.language_id) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.getAdminCurrentLang().' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $payment_seetings = DB::table('payment_gateways')
                                    ->select('payment_gateways.id','payment_gateways_info.name')
                                    ->leftJoin('payment_gateways_info','payment_gateways_info.payment_id','=','payment_gateways.id')
                                    ->whereRaw($query3)
                                    ->orderBy('id', 'asc')
                                    ->get();
            if(Input::get('export'))
            {
                $out = '"Order Id","Name","Vendor Name","Outlet Name","Status","Total Amount","Payment Mode","Order Date"'."\r\n";
                foreach($orders as $d)
                {
                    $out .= $d->id.',"'.$d->user_name.'","'.$d->vendor_name.'","'.$d->outlet_name.'","'.$d->status_name.'","'.$d->total_amount.$d->currency_code.'","'.$d->payment_gateway_name.'","'.date("d F, Y", strtotime($d->order_created)).'"'."\r\n";
                }
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=Orders.csv');
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                echo "\xEF\xBB\xBF"; // UTF-8 BOM
                echo $out;
                exit;
            }
            return view('vendors.orders.list')->with('orders', $orders)->with('order_status', $order_status)->with('payment_seetings', $payment_seetings);
        }
    }
    /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function anyAjaxOrderlist()
    {
        $vendor_id = Session::get('vendor_id');
        $orders = DB::table('orders')
        ->select('orders.id','orders.total_amount','orders.created_date','orders.delivery_date','users.first_name','users.last_name','order_status.name as status_name','order_status.color_code as color_code','users.name as user_name','transaction.currency_code')
        ->leftJoin('users','users.id','=','orders.customer_id')
        ->leftJoin('order_status','order_status.id','=','orders.order_status')
        ->leftJoin('transaction','transaction.order_id','=','orders.id')
        ->where('orders.vendor_id',$vendor_id)
        ->orderBy('orders.id', 'desc');
        return Datatables::of($orders)->addColumn('action', function ($orders) {
                $html = '--';
                $html ='<a href="'.URL::to("vendors/orders/info/".$orders->id).'" class="btn btn-xs btn-white" title="'.trans("messages.View").'"><i class="fa fa-view"></i>&nbsp;'.trans("messages.View").'</a>';
                 return $html;
            })
            ->addColumn('status_name', function ($orders) {
                $data = '<span style="color:'.$orders->color_code.';">'.$orders->status_name.'</span>';
               return $data;
           })
            ->editColumn('user_name', '{!! ucfirst($user_name) !!}')
            ->editColumn('created_date', '{!! date("d F, l", strtotime($created_date)) !!}') 
            ->editColumn('total_amount', '{!! $total_amount !!}{!!$currency_code!!}')
            ->rawColumns(['status_name','action'])

            ->make(true);
    }
    
    public function order_info($order_id)
    {
        if (!Session::get('vendor_id')) {
            return redirect()->guest('vendors/login');
        } else {
            $vendor_id = Session::get('vendor_id');
            $language_id = getAdminCurrentLang();
            $query3 = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = '.$language_id.' and vendors.id = vendors_infos.id) > 0 THEN '.$language_id.' ELSE 1 END)';
            $query4 = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.$language_id.' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN '.$language_id.' ELSE 1 END)';
            $vendor_info = DB::select('SELECT vendors_infos.vendor_name,vendors.email,vendors.logo_image,o.id as order_id,o.created_date,o.order_status,order_status.name as status_name,order_status.color_code as color_code,o.outlet_id,vendors.id as vendor_id,o.order_key_formated
            FROM orders o
            left join vendors vendors on vendors.id = o.vendor_id
            left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
            left join order_status order_status on order_status.id = o.order_status
            left join payment_gateways payment_gateways on payment_gateways.id = o.payment_gateway_id
            left join payment_gateways_info payment_gateways_info on payment_gateways_info.payment_id = payment_gateways.id
            where o.vendor_id='.$vendor_id.' AND '.$query3.' AND '.$query4.' AND o.id = ? ORDER BY o.id',array($order_id));

            $query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = '.$language_id.' and p.id = products_infos.id) > 0 THEN '.$language_id.' ELSE 1 END)';
            $order_items = DB::select('SELECT p.product_image,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.invoice_id,pi.product_name,pi.description,o.coupon_amount
            FROM orders o
            LEFT JOIN orders_info oi ON oi.order_id = o.id
            LEFT JOIN products p ON p.id = oi.item_id
            LEFT JOIN products_infos pi ON pi.id = p.id
            where o.vendor_id='.$vendor_id.' AND '.$query.' AND o.id = ? ORDER BY oi.id',array($order_id));

            $query1 = '"out_inf"."language_id" = (case when (select count(outlet_infos.language_id) as totalcount from outlet_infos where outlet_infos.language_id = '.getCurrentLang().' and o.outlet_id = outlet_infos.id) > 0 THEN '.getCurrentLang().' ELSE 1 END)';
            $query2 = 'pgi.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.$language_id.' and pg.id = payment_gateways_info.payment_id) > 0 THEN '.$language_id.' ELSE 1 END)';
            $delivery_details = DB::select('SELECT o.id as order_id, o.delivery_instructions,ua.address,pg.id as payment_gateway_id,pgi.name,o.total_amount,o.delivery_charge,o.service_tax,dti.start_time,end_time,o.created_date,o.delivery_date,o.order_type,out_inf.contact_address,o.coupon_amount,users.first_name,users.last_name,users.email,users.mobile,o.order_key_formated,outlet_infos.outlet_name,trans.currency_code,pgi.name as payment_gateway_name,users.name as user_name 
                        FROM orders o
                        left join users on o.customer_id = users.id
                        left join outlet_infos on o.outlet_id = outlet_infos.id

                        LEFT JOIN user_address ua ON ua.id = o.delivery_address
                        left join payment_gateways pg on pg.id = o.payment_gateway_id
                        left join payment_gateways_info pgi on pgi.payment_id = pg.id
                        left join delivery_time_slots dts on dts.id=o.delivery_slot
                        left join delivery_time_interval dti on dti.id = dts.time_interval_id
                        left join outlets out on out.id = o.outlet_id
                        left join outlet_infos out_inf on out.id = o.outlet_id
                        left join transaction trans on trans.order_id = o.id
                        where o.vendor_id='.$vendor_id.' AND '.$query1.' AND '.$query2.' AND o.id = ?',array($order_id));
            
            $order_history = DB::select('SELECT ol.order_comments,ol.order_status,log_time,order_status.name as status_name,order_status.color_code as color_code
                FROM orders_log ol
                left join order_status order_status on order_status.id = ol.order_status
                where ol.order_id = ? ORDER BY ol.id',array($order_id));
            $order_status_list = DB::select('SELECT * FROM order_status WHERE id in(1,10,18,19,14,12) ORDER BY order_status.id');
            return view('vendors.orders.show')->with('order_items', $order_items)->with('delivery_details', $delivery_details)->with('vendor_info', $vendor_info)->with('order_history', $order_history)->with('order_status_list', $order_status_list);
        }
    }
    public function update_status(Request $data)
    {
        if (!Session::get('vendor_id')) {
            return redirect()->guest('vendors/login');
        }

               // print_r("coming");exit;


        $post_data = $data->all();
        $driver_id=DB::table('orders')
            ->select('driver_ids','order_status','customer_id')
            ->where('orders.id',$post_data['order_id'])
            ->get();
       // print_r($driver_id);exit;
        $driverid =isset($driver_id[0]->driver_ids)?$driver_id[0]->driver_ids:0;
        $order_status =isset($driver_id[0]->order_status)?$driver_id[0]->order_status:0;
        $customer_id =isset($driver_id[0]->customer_id)?$driver_id[0]->customer_id:0;

        //print_r($post_data);exit;
        if($order_status !=12 && $order_status !=11){
            if($post_data['order_status_id'] == 12)
            {
                    $data['driverId'] = $driverid;
                    $data['orderId'] =$post_data['order_id'];
                    $delivery = commonDelivery($data); //common fun for delivery
                
            }else{
                  $affected = DB::update('update orders set order_status = ?,order_comments = ? where id = ?', array($post_data['order_status_id'],$post_data['comment'],$post_data['order_id']));
                $affected = DB::update('update orders_log set order_status=?, order_comments = ? where id = (select max(id) from orders_log where order_id = '. $post_data['order_id'].')', array($post_data['order_status_id'],$post_data['comment']));
                 /**cancel mail for customer**/
                if($post_data['order_status_id'] == 11)
                {
                    $users = Users::find($customer_id);
                    $to = $users->email;
                    /*$template = DB::table('email_templates')->select('*')->where('template_id', '=', 29)->get();
                    if (count($template)) {
                        $from = $template[0]->from_email;
                        $from_name = $template[0]->from;
                        if (!$template[0]->template_id) {
                            $template = 'mail_template';
                            $from = getAppConfigEmail()->contact_mail;
                        }
                        $subject = 'Your Order has been cancelled';
                        $content = array('name' => "" . $users->name);
                        $attachment = "";
                        $email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
                    }*/
                    $affected = DB::update('update drivers set driver_status = 1 where id = ?', array($driverid));


                    $delivery_address = DB::table('user_address')->select('address')->where('id', '=' ,$delivery_address)->get();
                    $currency =getCurrencyList();
                    $currency_code = isset($currency[0]->currency_code)?$currency[0]->currency_code:'AED';
                    $template = DB::table('email_templates')->select('*')->where('template_id', '=',29)->get();
                    if (count($template)) {
                        $from = $template[0]->from_email;
                        $from_name = $template[0]->from;
                        if (!$template[0]->template_id) {
                            $template = 'mail_template';
                            $from = getAppConfigEmail()->contact_mail;
                        }
                        $subject = 'Your Order has been cancelled';
                        $log_image = url('/assets/admin/email_temp/images/1570903488.jpg');
                        $cancel_image = url('/assets/admin/email_temp/images/c.jpg');
                        $order_id = (string)$post_data['order_id'];
                        $created_date = $created_date;
                        $shipping_address =isset($delivery_address[0]->address) ? $delivery_address[0]->address : '';
                        $currency_code = $currency_code;
                        $total = $total_amount;

                        $content = array("log_image"=>$log_image,"cancel_image"=>$cancel_image,"order_id"=>$order_id,"created_date"=>$created_date,"shipping_address"=>$shipping_address,"currency_code"=>$currency_code,"total"=>$total);                      
                        $attachment = "";
                        $email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
                    }

                }
            }
            $notify=push_notification($post_data['order_id'],$post_data['order_status_id'],1);
            $result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.Order Status updated successfully"));

           // $res


        }else
        {

            if($order_status == 12){
                $result = array("status" => 0, "httpCode" => 400, "Message" => trans("messages.order is Already completed"));

            }else
            {
                $result = array("status" => 0, "httpCode" => 400, "Message" => trans("messages.Order is cancelled Already"));

            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);

    } 

    /* public function update_status(Request $data)
    {
        if (!Session::get('vendor_id')) {
            return redirect()->guest('vendors/login');
        }

               // print_r("coming");exit;


        $post_data = $data->all();
        //print_r($post_data);exit;

        $affected = DB::update('update orders set order_status = ?,order_comments = ? where id = ?', array($post_data['order_status_id'],$post_data['comment'],$post_data['order_id']));
        $affected = DB::update('update orders_log set order_status=?, order_comments = ? where id = (select max(id) from orders_log where order_id = '. $post_data['order_id'].')', array($post_data['order_status_id'],$post_data['comment']));
        //$post_data['notify'] = 1;

        /*FCM push notification/
        $notify = DB::table('orders')
            ->select('orders.assigned_time', 'users.android_device_token', 'users.ios_device_token','users.id as customerId ','users.login_type', 'users.first_name', 'vendors_infos.vendor_name','vendors.id as vendorId','orders.total_amount','outlets.id as outletId','outlet_infos.outlet_name','orders.driver_ids')
            ->Join('users', 'users.id', '=', 'orders.customer_id')
            ->Join('vendors_infos', 'vendors_infos.id', '=', 'orders.vendor_id')
            ->Join('vendors', 'vendors.id', '=', 'orders.vendor_id')
            ->Join('outlets', 'outlets.id', '=', 'orders.outlet_id')
            ->Join('outlet_infos','outlet_infos.id', '=', 'orders.outlet_id')
            ->where('orders.id', '=', (int) $post_data['order_id'])
            ->get();


        if (count($notify) > 0 && $notify[0]->login_type != 1 ) {
                $notifys = $notify[0];
                if($post_data['order_status_id']==1){
                    $order_title = '' . 'order placed';
                    $description = '' . 'your placed order successfully';
                    $orderStatus =1;
                }

                if($post_data['order_status_id']==10){
                    $order_title = '' . 'processed';
                    $description = '' . 'your placed order is processed successfully';
                    $orderStatus =10;
                }


                if($post_data['order_status_id']==18){
                    $order_title = '' . 'packed';
                    $description = '' . 'your placed order is packed successfully';
                    $orderStatus =18;
                }

                if($post_data['order_status_id']==11){
                    $order_title = '' . 'cancelled';
                    $description = '' . 'your placed order is cancelled successfully';
                    $orderStatus =11;
                }

                if($post_data['order_status_id']==12){
                    $order_title = '' . 'delivered';
                    $description = '' . 'your placed order is delivered successfully';
                    $orderStatus =12;



                    $referral =getreferral();
                    $customer_id = isset($notify[0]->customerId)?$notify[0]->customerId:0;
                    $users_details=DB::table('customer_referral')
                               ->select('*')
                               ->where('customer_referral.customer_id',$customer_id)
                               ->where('customer_referral.referal_amount_used', '!=', '1')
                               ->get();
                 //  print_r($users_details);exit;
                  if($users_details && $users_details[0]->referal_amount_used !=1){
                    $count_order=DB::table('orders')
                        ->select('*')
                        ->where('orders.customer_id',$customer_id)
                        ->count();
                    //print_r($count_order);exit;
                    $wallet_details=DB::table('users')
                        ->select('wallet_amount')
                        ->where('users.id',$users_details[0]->referred_by)
                        ->get();
                    $wallet_amount = isset($wallet_details[0]->wallet_amount)?$wallet_details[0]->wallet_amount:0;
                    if($count_order == $referral[0]->order_to_complete)
                    {
                        $wallet_amount = $wallet_amount + $referral[0]->referred_amount;
                        //print_r($referral);exit;
                        $affected = DB::update('update users set wallet_amount =?  where id = ?', array($wallet_amount,$users_details[0]->referred_by));
                        $affected = DB::update('update customer_referral set referal_amount_used =1  where id = ?', array($users_details[0]->id));
                    }
                    
                    }
                }

                if($post_data['order_status_id']==14){
                    $order_title = '' . 'shipped';
                    $description = '' . 'your placed order is shipped successfully';
                    $orderStatus =14;
                }

                if($post_data['order_status_id']==19){
                    $order_title = '' . 'dispatched';
                    $description = '' . 'your placed order is dispatched successfully';
                    $orderStatus =19;
                }

                if($notifys->login_type == 2){// android device
                    $token = $notifys->android_device_token;
                }else if($notifys->login_type == 3)
                {
                    $token = $notifys->ios_device_token;
                }
                $token =isset($token)?$token:'';
                $data = array
                    (
                    'status' => 1,
                    'message' => $order_title,
                    'detail' =>array(
                    'description'=>$description,    
            
                    'customerId' => isset($notifys->customerId) ? $notifys->customerId : '',
                    'orderId' => $post_data['order_id'],
                    'driverId' => isset($notifys->driver_ids) ? $notifys->driver_ids : '',
                    'orderStatus' => $orderStatus,
                    'type' => 2,
                    'title' => $order_title,
                    'totalamount' => isset($notifys->total_amount) ? $notifys->total_amount : 0,
                    'vendorName' => isset($notifys->vendor_name) ? $notifys->vendor_name : '',
                    'vendorId' => isset($notifys->vendorId) ? $notifys->vendorId : '',
                    'outletId' => isset($notifys->outletId) ? $notifys->outletId : '',
                    'outlet_name' => isset($notifys->outlet_name) ? $notifys->outlet_name : '',
                    'request_type' => 1,
                    "order_assigned_time" => isset($notifys->assigned_time) ? $notifys->assigned_time : '',
                    'notification_dialog' => "1",
                ));

                $fields = array
                    (
                    'registration_ids' => array($token),
                    //'data' => $data,
                    'data' => array('title' => $order_title, 'body' =>  $data ,'sound'=>'Default','image'=>'Notification Image')


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



              
            }*/
      //  print_r($notify);exit;
        /*FCM push notification   =>changed bez this function is not working discussed with parasanth*/
        /*if(isset($post_data['notify']) && $post_data['notify'] == 1)
        {
            $order_detail = $this->get_order_detail($post_data['order_id']);
            $order_details = $order_detail["order_items"];
            $delivery_details = $order_detail["delivery_details"];
            $vendor_info = $order_detail["vendor_info"];
            $logo = url('/assets/front/'.Session::get("general")->theme.'/images/'.Session::get("general")->theme.'.png');
            if(file_exists(base_path().'/public/assets/admin/base/images/vendors/list/'.$vendor_info[0]->logo_image)) { 
                $vendor_image ='<img width="100px" height="100px" src="'.URL::to("assets/admin/base/images/vendors/list/".$vendor_info[0]->logo_image).'") >';
            }
            else
            {  
                $vendor_image ='<img width="100px" height="100px" src="'.URL::to("assets/front/base/images/blog_no_images.png").'") >';
            }
            $delivery_date = date("d F, l", strtotime($delivery_details[0]->delivery_date)); 
            $delivery_time = date('g:i a', strtotime($delivery_details[0]->start_time)).'-'.date('g:i a', strtotime($delivery_details[0]->end_time));
            $users=Users::find($delivery_details[0]->customer_id); 
            $to=$users->email;
            $subject = 'Your Order with '.getAppConfig()->site_name.' ['.$vendor_info[0]->order_key_formated .'] has been successfully '.$vendor_info[0]->status_name.'!';
            $template=DB::table('email_templates')->select('*')->where('template_id','=',self::ORDER_STATUS_UPDATE_USER)->get();
            if(count($template))
            {
                $from = $template[0]->from_email;
                $from_name=$template[0]->from;
                if(!$template[0]->template_id)
                {
                    $template = 'mail_template';
                    $from=getAppConfigEmail()->contact_mail;
                }
                $orders_link ='<a href="'.URL::to("orders").'" title="'.trans("messages.View").'">'.trans("messages.View").'</a>';
                $content =array('name' =>"".$users->name,'order_key'=>"".$vendor_info[0]->order_key_formated,'status_name'=>"".$vendor_info[0]->status_name,'orders_link'=>"".$orders_link);
                $attachment = "";
                $email=smtp($from,$from_name,$to,$subject,$content,$template,$attachment);
            }
            $order_title = 'Your order '.$vendor_info[0]->order_key_formated.'  has been '.$vendor_info[0]->status_name;
            $order = DB::select('SELECT o.vendor_id,o.outlet_id,o.delivery_date, o.customer_id,dti.start_time,end_time FROM orders o
            left join delivery_time_slots dts on dts.id=o.delivery_slot
            left join delivery_time_interval dti on dti.id = dts.time_interval_id
            where o.id = ?',array($post_data['order_id']));
            $customer_id = isset($delivery_details[0]->customer_id)?$delivery_details[0]->customer_id:$order[0]->customer_id;
            $vendor_id = isset($delivery_details[0]->vendor_id)?$delivery_details[0]->vendor_id:$order[0]->vendor_id;
            $outlet_id = isset($delivery_details[0]->outlet_id)?$delivery_details[0]->outlet_id:$order[0]->outlet_id;
            $users = Users::find($delivery_details[0]->customer_id); 
            if($post_data['order_status_id'] == 12){

                $message = 'Your order has been Delivered in  '.getAppConfig()->site_name.' Order reference:     '.$vendor_info[0]->order_key_formated;
                $twilo_sid    = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
                $twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
                $from_number  = "+14783471785";
                $client = new Services_Twilio($twilo_sid, $twilio_token);
                //print_r ($client);exit;
                // Create an authenticated client for the Twilio API
                try {
                    $m = $client->account->messages->sendMessage(
                            $from_number, // the text will be sent from your Twilio number
                            $users->mobile, // the phone number the text will be sent to
                            $message // the body of the text message
                       );
            
                
                
                }
                catch (Exception $e) {
                    $result11 = array("response" => array("httpCode" => 400,"Message" => $e->getMessage()));
                
                }
                catch(\Services_Twilio_RestException $e) {
                    $result1 = array("response" => array("httpCode" => 400,"Message" => $e->getMessage()));
                
                }

            }
            if(!empty($users->android_device_token)) {
                $optionBuiler = new OptionsBuilder();
                $optionBuiler->setTimeToLive(60*20);
                $notificationBuilder = new PayloadNotificationBuilder($subject);
                $notificationBuilder->setBody($subject)->setSound('default')->setBadge(1);      
                $dataBuilder = new PayloadDataBuilder();
                $dataBuilder->addData(['order_id' => $post_data['order_id'],"message"=>$subject]);
                $option = $optionBuiler->build();
                $notification = $notificationBuilder->build();
                $data  = $dataBuilder->build();
                $token = $users->android_device_token;
                $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
                $downstreamResponse->numberSuccess();
                if($downstreamResponse->numberSuccess() && $downstreamResponse->numberSuccess()==1){
                    //Notification success
                }
                $downstreamResponse->numberFailure();
                $downstreamResponse->numberModification();
                $downstreamResponse->tokensToDelete(); 
                $downstreamResponse->tokensToModify(); 
                $downstreamResponse->tokensToRetry();
            }
            if(!empty($users->ios_device_token)) {
                $optionBuiler = new OptionsBuilder();
                $optionBuiler->setTimeToLive(60*20);
                $notificationBuilder = new PayloadNotificationBuilder($subject);
                $notificationBuilder->setBody($subject)->setSound('default')->setBadge(1);      
                $dataBuilder = new PayloadDataBuilder();
                $dataBuilder->addData(['order_id' => $post_data['order_id'],"message"=>$subject]);
                $option = $optionBuiler->build();
                $notification = $notificationBuilder->build();
                $data = $dataBuilder->build();
                $token = $users->ios_device_token;
                $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
                $downstreamResponse->numberSuccess();
                /*if($downstreamResponse->numberSuccess() && $downstreamResponse->numberSuccess()==1){
                    //Notification success
                }///
                $downstreamResponse->numberFailure();
                $downstreamResponse->numberModification();
                $downstreamResponse->tokensToDelete(); 
                $downstreamResponse->tokensToModify(); 
                $downstreamResponse->tokensToRetry();
            }
                $values = array('order_id' => $post_data['order_id'],
                            'customer_id'  => $customer_id,
                            'vendor_id'    => $vendor_id,
                            'outlet_id'    => $outlet_id,
                            'message'      => $subject,
                            'read_status'  => 0,
                            'created_date' => date('Y-m-d H:i:s'));
                DB::table('notifications')->insert($values);
        }*/
        //return 1;
  //  }
    
    public function load_history($order_id)
    {
        if (!Session::get('vendor_id')) {
            return redirect()->guest('vendors/login');
        }
        $order_history = DB::select('SELECT ol.order_comments,ol.order_status,log_time,order_status.name as status_name,order_status.color_code as color_code
        FROM orders_log ol
        left join order_status order_status on order_status.id = ol.order_status
        where ol.order_id = ? ORDER BY ol.id',array($order_id));
        
        $data_tab = '<table class="table table-bordered"><thead><tr>
                    <td class="text-left">'.trans("messages.Date").'</td>
                    <td class="text-right">'.trans("messages.Comment").'</td>
                    <td class="text-right">'.trans("messages.Status").'</td>
                    </tr></thead><tbody>';
                            $subtotal = "";
                            foreach($order_history as $history) {
                    $data_tab .= '<tr><td class="text-left">'.date('M j Y g:i A', strtotime($history->log_time)).'</td>';
                    $data_tab .= '<td class="text-right">'.$history->order_comments.'</td>';
                    $data_tab .= '<td class="text-right">'.$history->status_name.'</td></tr>';
                            }
                    $data_tab .= '</tbody>';
                    $data_tab .= '</table>';
        echo $data_tab;exit;
    }
    
    
    public function get_order_detail($order_id)
    {
        if (!Session::get('vendor_id')) {
            return redirect()->guest('vendors/login');
        }
        $language_id = getCurrentLang();
        $query3 = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = '.$language_id.' and vendors.id = vendors_infos.id) > 0 THEN '.$language_id.' ELSE 1 END)';
        $query4 = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.$language_id.' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN '.$language_id.' ELSE 1 END)';
        $vendor_info = DB::select('SELECT vendors_infos.vendor_name,vendors.email,vendors.logo_image,o.id as order_id,o.created_date,o.order_status,order_status.name as status_name,order_status.color_code as color_code,payment_gateways_info.name as payment_gateway_name,o.outlet_id,vendors.id as vendor_id,o.order_key_formated
        FROM orders o
        left join vendors vendors on vendors.id = o.vendor_id
        left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
        left join order_status order_status on order_status.id = o.order_status
        left join payment_gateways payment_gateways on payment_gateways.id = o.payment_gateway_id
        left join payment_gateways_info payment_gateways_info on payment_gateways_info.payment_id = payment_gateways.id
        where '.$query3.' AND '.$query4.' AND o.id = ? ORDER BY o.id',array($order_id));

        $query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = '.$language_id.' and p.id = products_infos.id) > 0 THEN '.$language_id.' ELSE 1 END)';
        $order_items = DB::select('SELECT p.product_image,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.invoice_id,pi.product_name,pi.description,o.coupon_amount
        FROM orders o
        LEFT JOIN orders_info oi ON oi.order_id = o.id
        LEFT JOIN products p ON p.id = oi.item_id
        LEFT JOIN products_infos pi ON pi.id = p.id
        where '.$query.' AND o.id = ? ORDER BY oi.id',array($order_id));

        $query2 = 'pgi.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.$language_id.' and pg.id = payment_gateways_info.payment_id) > 0 THEN '.$language_id.' ELSE 1 END)';
        $delivery_details = DB::select('SELECT o.delivery_instructions,ua.address,pg.id as payment_gateway_id,pgi.name,o.total_amount,o.delivery_charge,o.service_tax,dti.start_time,end_time,o.created_date,o.delivery_date,o.order_type,out_inf.contact_address,o.coupon_amount,o.customer_id FROM orders o
                    LEFT JOIN user_address ua ON ua.id = o.delivery_address
                    left join payment_gateways pg on pg.id = o.payment_gateway_id
                    left join payment_gateways_info pgi on pgi.payment_id = pg.id
                    left join delivery_time_slots dts on dts.id=o.delivery_slot
                    left join delivery_time_interval dti on dti.id = dts.time_interval_id
                    left join outlets out on out.id = o.outlet_id
                      left join outlet_infos out_inf on out.id = o.outlet_id
                    where '.$query2.' AND o.id = ?',array($order_id));
        if(count($order_items)>0 && count($delivery_details)>0 && count($vendor_info)>0)
        {
            $result = array("order_items"=>$order_items,"delivery_details"=>$delivery_details,"vendor_info"=>$vendor_info);
        }
        return $result;
    }
    /*
    * Create new request money from admin
    */
    public function add_amount()
    {
        if (!Session::get('vendor_id')){
            return redirect()->guest('vendors/login');
        }
        else {
            $vendor_id = Session::get('vendor_id');
            $remaining_orders = DB::table('orders')
                                ->select('orders.id','orders.total_amount','orders.vendor_id','orders.delivery_charge','orders.service_tax','orders.vendor_commission','orders.admin_commission','orders.created_date')
                                ->leftjoin('payment_request_orders','payment_request_orders.order_id','!=','orders.id')
                                ->where('orders.vendor_id', $vendor_id)
                                ->orderBy('orders.id', 'desc')
                                ->get();
            return view('vendors.request_amount.create')->with('remaining_orders',$remaining_orders);
        }
    }
    /**
     * Add the request payment in storage.
     * @param  int  $id
     * @return Response
     */
    public function amount_store(Request $data)
    {
        if (!Session::get('vendor_id')){
            return redirect()->guest('vendors/login');
        }
        $settings = session::get('general');
        $min_amount = $settings->min_fund_request;
        $max_amount = $settings->max_fund_request;
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation
        $fields['request_amount'] = Input::get('request_amount');
        $rules = array(
            'request_amount' => 'required|numeric|between:'.$min_amount.','.$max_amount.''
        );
        $validation = Validator::make($fields, $rules);
        // process the validation
        if ($validation->fails())
        { 
            return Redirect::back()->withErrors($validation)->withInput();
        } else {
            $vendor_id = Session::get('vendor_id');
            $balance = getBalanceData($vendor_id,1);
            $current_bal = $balance['vendor_balance'] - $_POST['request_amount'];
            //Check the balance must be greater than Zero
            if($current_bal>0){
                //Store the data here with database
                try{
                    $random_id = Text::random('alnum', 8);
                    $Payment_request = new payment_request_vendors;
                    $Payment_request->request_amount = $_POST['request_amount'];
                    $Payment_request->vendor_id = $vendor_id;
                    $Payment_request->approve_status = 0;
                    $Payment_request->created_date = date('Y-m-d H:i:s');
                    $Payment_request->created_by = $vendor_id;
                    $Payment_request->current_balance = $current_bal;
                    $Payment_request->unique_id = $random_id;
                    $Payment_request->save();
                    $last_insert_id = $Payment_request->id;
                    Session::flash('message', trans('messages.Payment request has been added successfully'));
                    //Subtract the requested amount to vendor table
                    $result1 = DB::update('update vendors set current_balance = ? where id = ?', array($current_bal,$vendor_id));

                    //Send mail to admin regarding when vendor requested a fund amount
                    $template = DB::table('email_templates')
                                ->select('*')
                                ->where('template_id','=',self::REFUND_APPROVE_EMAIL_TEMPLATE)
                                ->get();
                    if(count($template))
                    {
                        $from = $template[0]->from_email;
                        $from_name = $template[0]->from;
                        $subject = $template[0]->subject;
                        if(!$template[0]->template_id)
                        {
                            $template = 'mail_template';
                            $from = getAppConfigEmail()->contact_email;
                            $subject = getAppConfig()->site_name." Refund Request Information";
                            $from_name = "";
                        }
                        $currency_side   = getCurrencyPosition()->currency_side;
                        $currency_symbol = getCurrency(); 
                        if($currency_side == 1)
                        {
                            $request_amount = $currency_symbol.$_POST['request_amount'];
                        }
                        else {
                            $request_amount = $_POST['request_amount'].$currency_symbol;
                        }
                        $users = Users::find(1);
                        $vendors = DB::table('vendors_infos')->select('vendor_name')->where('lang_id',getAdminCurrentLang())->where('id',$vendor_id)->get();
                        $cont_replace = "Following vendor <b>".$vendors[0]->vendor_name."</b> was requested his commision amount. Amount Requested : <b>".$request_amount."</b> Requested Date: <b>".date('Y-m-d H:i:s')."</b>";
                        $cont_replace1 = "Kindly find the above details of fund request and make it necessary arrangements to process it further";
                        $content = array("name" => $users->name,"email"=>$users->email,"replacement"=>$cont_replace,"replacement1"=>$cont_replace1);
                        $email = smtp($from,$from_name,$users->email,$subject,$content,$template);
                    }
                }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
                }
            } else {
                Session::flash('message', trans('messages.Your balance is low.So not able to make payment request'));
            }
            return Redirect::to('vendors/request_amount/index');
        }
    }
    /*
    * Vendor Fund Request List Amount
    */
    public function request_amount_list()
    {
        if (!Session::get('vendor_id')){
            return redirect()->guest('vendors/login');
        }
        else {
            $vendor_id = Session::get('vendor_id');
            $condition ="1=1";
            if(Input::get('from') && Input::get('to'))
            {
                $from = date('Y-m-d H:i:s', strtotime(Input::get('from')));
                $to   = date('Y-m-d H:i:s', strtotime(Input::get('to')));
                $condition .=" and payment_request_vendors.created_date BETWEEN '".$from."'::timestamp and '".$to."'::timestamp";
            }
            $query = '"vendors_infos"."lang_id" = (case when (select count(id) as totalcount from vendors_infos where vendors_infos.lang_id = '.getAdminCurrentLang().' and payment_request_vendors.vendor_id = vendors_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $list_data = DB::table('payment_request_vendors')
                            ->join('vendors_infos','vendors_infos.id','=','payment_request_vendors.vendor_id')
                            ->select('payment_request_vendors.id','payment_request_vendors.approve_status','payment_request_vendors.vendor_id','payment_request_vendors.created_date','payment_request_vendors.modified_date','payment_request_vendors.current_balance','payment_request_vendors.request_amount','payment_request_vendors.unique_id','vendors_infos.vendor_name')
                            ->whereRaw($query)
                            ->whereRaw($condition)
                            ->where('payment_request_vendors.vendor_id', $vendor_id)
                            ->orderBy('payment_request_vendors.created_date', 'desc')
                            ->get();
            return view('vendors.request_amount.list')->with('return_orders',$list_data);
        }
    }
    /*
    * Ajax request payment request for vendors
    */
    public function anyAjaxRequestPayment()
    {
        $currency_side   = getCurrencyPosition()->currency_side;
        $currency_symbol = getCurrency(); 
        $vendor_id = Session::get('vendor_id');
        $query = '"vendors_infos"."lang_id" = (case when (select count(id) as totalcount from vendors_infos where vendors_infos.lang_id = '.getAdminCurrentLang().' and payment_request_vendors.vendor_id = vendors_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $list_data = DB::table('payment_request_vendors')
                ->join('vendors_infos','vendors_infos.id','=','payment_request_vendors.vendor_id')
                ->select('payment_request_vendors.*','vendors_infos.vendor_name')
                ->where('payment_request_vendors.vendor_id',$vendor_id)
                ->whereRaw($query)
                ->orderBy('payment_request_vendors.created_date', 'desc');
        return Datatables::of($list_data)
            ->addColumn('approve_status', function ($list_data) {
                if($list_data->approve_status==0):
                    $data = '<span class="label label-warning">'.trans("messages.Pending").'</span>';
                elseif($list_data->approve_status==1):
                    $data = '<span class="label label-success">'.trans("messages.Completed").'</span>';
                elseif($list_data->approve_status==2):
                    $data = '<span class="label label-danger">'.trans("messages.Cancelled").'</span>';
                endif;
                return $data;
            })
            ->addColumn('modified_date', function ($list_data) {
                $data = '-';
                if(!empty($list_data->modified_date)):
                    $data = trim($list_data->modified_date);
                endif;
                return $data;
            })
            ->addColumn('current_balance', function ($list_data) {
                if($currency_side == 1)
                {
                    $data = $currency_symbol.$list_data->current_balance;
                }
                else {
                    $data = $list_data->current_balance.$currency_symbol;
                }
                return $data;
            })
            ->addColumn('request_amount', function ($list_data) {
                if($currency_side == 1)
                {
                    $data = $currency_symbol.$list_data->request_amount;
                }
                else {
                    $data = $list_data->request_amount.$currency_symbol;
                }
                return $data;
            })
            ->rawColumns(['request_amount','current_balance','modified_date','approve_status'])

            ->make(true);
    }
    
    /* Get outlets list based on the vendor */
    public function Maincategorylist(Request $request)
    {
        if($request->ajax()){
            $c_id = $request->input('cid');
            $data = getMainCategoryLists($c_id); // get product sub category data here
            return response()->json([
                'data' => $data
            ]);
        }
    }


        /* Get outlets list based on the vendor */
    public function ProductMaincategorylist(Request $request)
    {
        if($request->ajax()){
            $c_id = $request->input('cid');
            $data = getProductMainCategoryLists($c_id); // get product sub category data here
            return response()->json([
                'data' => $data
            ]);
        }
    }

    
	 public function getVendorcategorylist(Request $request)
    {
        if($request->ajax()){
            $c_id = $request->input('cid');
            $language = $request->input('language');
            $data = getCategoryVendorLists($c_id,$language);
			 $cdata = getMainCategoryLists($c_id,$language); 
            return response()->json([
                'data' => $data,'cdata' => $cdata
            ]);
        }
    }

     /*
     * outlets based product list
     */
    public function getAllUsersList(Request $request)
    { 
        if($request->ajax())
        {
            $users_list = get_user_list();
           // print_r($users_list);exit();
            return response()->json([
                'data' => $users_list
            ]);
        }
    }
     /* To assign driver for orders */
    public function assign_driver_orders(Request $data) {

        $data_all = $data->all();
        $validation = Validator::make($data_all, array(
            'order_id' => 'required',
            'driver' => 'required',
        ));
        if ($validation->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validation->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', str_replace(".", " ", $value)) : str_replace(".", " ", $value);
            }
            $errors = implode("<br>", $error);
            $result = array("response" => array("httpCode" => 400, "Error" => trans("messages.Error List"), "Message" => $errors));
        } else {
            
            $new_orders = Order::find($data_all['order_id']);
            if($new_orders->driver_ids == ''){
                $driver_settings = Driver_settings::find(1);

                $orders = DB::table('orders')
                    ->select('driver_ids')
                    ->where('orders.id', $data_all['order_id'])
                    ->first();
                $driver_ids = $orders->driver_ids;

                // $new_orders->driver_ids = $driver_ids.$data_all['driver'].',';
                $new_orders->driver_ids = /*$driver_ids . */$data_all['driver'];
                $assigned_time = strtotime("+ " . $driver_settings->order_accept_time . " minutes", strtotime(date('Y-m-d H:i:s')));
                $update_assign_time = date("Y-m-d H:i:s", $assigned_time);
                $new_orders->assigned_time = $update_assign_time;
                $new_orders->save();

                $order_title = 'order assigned to you';
                $driver_detail = Drivers::find($data_all['driver']);
                /*$order_logs = new Autoassign_order_logs;
                    $order_logs->driver_id = $data_all['driver'];
                    $order_logs->order_id = $data_all['order_id'];
                    $order_logs->driver_response = 0;
                    $order_logs->driver_token = $driver_detail->android_device_token;
                    $order_logs->order_delivery_status = 0;
                    $order_logs->assign_date = date("Y-m-d H:i:s");
                    $order_logs->created_date = date("Y-m-d H:i:s");
                    $order_logs->order_subject = $order_title;
                    // $order_logs->order_subject_arabic = $order_title1;
                    $order_logs->order_message = $order_title;
                */

                $affected = DB::update('update drivers set driver_status = 4 where id = ?', array($data_all['driver']));
                driver_assignlog($data_all['order_id'],$data_all['driver']);


                if ($driver_detail->android_device_token != '') {

                    $orders = Order::find($data_all['order_id']);

                    $data = array
                        (
                        'id' => $data_all['order_id'],
                        'type' => 2,
                        'title' => $order_title,
                        'message' => $order_title,
                        //  'log_id' => $order_logs->id,
                        'order_key_formated' => $orders->order_key_formated,
                        'request_type' => 2,
                        "order_accept_time" => $driver_settings->order_accept_time,
                        'notification_dialog' => "1",
                    );

                    $fields = array
                        (
                        'registration_ids' => array($driver_detail->android_device_token),
                        'data' => $data,
                    );

                    $headers = array
                        (
                        'Authorization: key=AAAAI_fAV4w:APA91bFSR1TLAn1Vh134nzXLznsUVYiGnR4KiUYdAa3u0OccC5S-DyDdQRdnR0XugSRArsJGXC8AHE342eNhBbnK8np10KuyuWwiJxtndV75O4DyT3QCGXKFu_fwUTNPdB51Cno6Rewc',
                        'Content-Type: application/json',
                    );

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                    $result = curl_exec($ch);
                    curl_close($ch);
                    //echo $result;

                }

                Session::flash('message', trans('messages.Driver assigned successfully'));
                $result = array("response" => array("httpCode" => 200, "Message" => trans('messages.Driver assigned successfully')));
            }else
            {
                Session::flash('message-failure', trans('messages.Driver Already assigned'));
                $result = array("response" => array("httpCode" => 400, "Message" => trans('messages.Driver Already assigned')));
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

	
}
