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
use App\Model\products_ingredients;
use App\Model\vendors;
use App\Model\vendors_infos;
use App\Model\Users;
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
//use App\Model\orders;

class Manager extends Controller
{
    const MANAGER_FORGOT_PASSWORD_EMAIL_TEMPLATE = 6;
    const MANAGER_CHANGE_PASSWORD_EMAIL_TEMPLATE = 7;
    const RETURN_STATUS_CUSTOMER_EMAIL_TEMPLATE = 17;
    const ORDER_STATUS_UPDATE_USER = 18;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->site_name = isset(getAppConfig()->site_name)?ucfirst(getAppConfig()->site_name):'';
        SEOMeta::setTitle('Restaurant Manager Panel - '.$this->site_name);
        SEOMeta::setDescription('Restaurant Manager Panel - '.$this->site_name);
        SEOMeta::addKeyword('Restaurant Manager Panel - '.$this->site_name);
        OpenGraph::setTitle('Restaurant Manager Panel - '.$this->site_name);
        OpenGraph::setDescription('Restaurant Manager Panel - '.$this->site_name);
        OpenGraph::setUrl('Restaurant Manager Panel - '.$this->site_name);
        Twitter::setTitle('Restaurant Manager Panel - '.$this->site_name);
        Twitter::setSite('@Restaurant Manager Panel - '.$this->site_name);
        //~ App::setLocale('en');
    }
    
    /*
     * Vendor login process goes here
    */
    public function login () 
    {
        return view('managers.login');
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
            $password = Input::get('password');
            $outlet_managers = DB::table('outlet_managers')
                        ->select('outlet_managers.id','outlet_managers.first_name','outlet_managers.email','outlet_managers.outlet_id','outlet_managers.vendor_id','outlets.outlet_name','outlet_managers.profile_image')
                        ->leftJoin('outlets','outlets.id','=','outlet_managers.outlet_id')
                        ->leftJoin('vendors','vendors.id','=','outlet_managers.vendor_id')
                        ->where('outlet_managers.email',$email)
                        ->where('outlet_managers.hash_password',md5($password))
                        ->where('outlet_managers.active_status',1)
                        ->where('outlet_managers.is_verified',1)
                        ->where('vendors.active_status',1)
                        ->where('outlets.active_status',1)
                       // ->where('outlets.approve_status',1)
                        ->get();
            //print_r($outlet_managers);exit;            
            if(count($outlet_managers) > 0){
                $managers_data = $outlet_managers[0];
                
                //Store the session data for future usage
                Session::put('manager_id', $managers_data->id);
                Session::put('manager_name', ucfirst($managers_data->first_name));
                Session::put('outlet_name', $managers_data->outlet_name);
                Session::put('manager_email', $managers_data->email);
                Session::put('manager_outlet', $managers_data->outlet_id);
                Session::put('manager_vendor', $managers_data->vendor_id);
                Session::put('manager_image', $managers_data->profile_image);
                
                //Show the flash message if successful logged in
                Session::flash('message', trans('messages.Logged in successfully'));
                return Redirect::to('managers/dashboard');
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
        if (!Session::get('manager_id')) {
            return redirect()->guest('managers/login');
        } else {
            $vendor_id = Session::get('manager_vendor');
            $outlet_id = Session::get('manager_outlet');
            // Section description
            $transaction_query = "SELECT  (
                SELECT COUNT(1)
                FROM transaction
                WHERE date_trunc('day', created_date) = date_trunc('day', CURRENT_DATE) AND vendor_id = $vendor_id and outlet_id = $outlet_id) AS day_count,

                (SELECT COUNT(1)
                FROM transaction
                WHERE date_trunc('WEEK', created_date) = date_trunc('WEEK', CURRENT_DATE) AND vendor_id = $vendor_id and outlet_id = $outlet_id) AS week_count,

                (SELECT COUNT(1)
                FROM transaction
                WHERE date_trunc('month', created_date) = date_trunc('month', CURRENT_DATE) AND vendor_id = $vendor_id and outlet_id = $outlet_id) AS month_count,

                (SELECT COUNT(1)
                FROM transaction
                WHERE date_trunc('year', created_date) = date_trunc('year', CURRENT_DATE) AND vendor_id = $vendor_id and outlet_id = $outlet_id) AS year_count,
                COUNT(1) AS total_count
                FROM transaction WHERE vendor_id = $vendor_id and outlet_id = $outlet_id";
            $transaction_period_count = DB::select($transaction_query);
            
            $orders_query = "SELECT  (
                SELECT COUNT(1)
                FROM orders
                WHERE date_trunc('day', created_date) = date_trunc('day', CURRENT_DATE) AND vendor_id = $vendor_id and outlet_id = $outlet_id ) AS day_count,

                (SELECT COUNT(1)
                FROM orders
                WHERE date_trunc('WEEK', created_date) = date_trunc('WEEK', CURRENT_DATE)  AND vendor_id = $vendor_id and outlet_id = $outlet_id ) AS week_count,

                (SELECT COUNT(1)
                FROM orders
                WHERE date_trunc('month', created_date) = date_trunc('month', CURRENT_DATE) AND vendor_id = $vendor_id and outlet_id = $outlet_id ) AS month_count,

                (SELECT COUNT(1)
                FROM orders
                WHERE date_trunc('year', created_date) = date_trunc('year', CURRENT_DATE) AND vendor_id = $vendor_id and outlet_id = $outlet_id ) AS year_count,
                COUNT(1) AS total_count 
                FROM orders WHERE vendor_id = $vendor_id and outlet_id = $outlet_id "; 
            
            $order_period_count = DB::select($orders_query);
            
            $outlet_reviews_query = "SELECT  
                (SELECT COUNT(1)
                FROM outlet_reviews
                WHERE date_trunc('day', created_date) = date_trunc('day', CURRENT_DATE) AND vendor_id = $vendor_id and outlet_id = $outlet_id ) AS day_count,

                (SELECT COUNT(1)
                FROM outlet_reviews
                WHERE date_trunc('WEEK', created_date) = date_trunc('WEEK', CURRENT_DATE) AND vendor_id = $vendor_id and outlet_id = $outlet_id ) AS week_count,

                (SELECT COUNT(1)
                FROM outlet_reviews
                WHERE date_trunc('month', created_date) = date_trunc('month', CURRENT_DATE) AND vendor_id = $vendor_id and outlet_id = $outlet_id ) AS month_count,

                (SELECT COUNT(1)
                FROM outlet_reviews
                WHERE date_trunc('year', created_date) = date_trunc('year', CURRENT_DATE) AND vendor_id = $vendor_id and outlet_id = $outlet_id ) AS year_count,
                COUNT(1) AS total_count
                FROM outlet_reviews WHERE vendor_id = $vendor_id and outlet_id = $outlet_id ";
            $outlet_reviews_query = DB::select($outlet_reviews_query);
            $products_query = "SELECT  
                (
                SELECT COUNT(1)
                FROM products
                WHERE date_trunc('day', created_date) = date_trunc('day', CURRENT_DATE) AND vendor_id = $vendor_id and outlet_id = $outlet_id ) AS day_count,

                (SELECT COUNT(1)
                FROM products
                WHERE date_trunc('WEEK', created_date) = date_trunc('WEEK', CURRENT_DATE) AND vendor_id = $vendor_id and outlet_id = $outlet_id ) AS week_count,

                (SELECT COUNT(1)
                FROM products
                WHERE date_trunc('month', created_date) = date_trunc('month', CURRENT_DATE) AND vendor_id = $vendor_id and outlet_id = $outlet_id ) AS month_count,

                (SELECT COUNT(1)
                FROM products
                WHERE date_trunc('year', created_date) = date_trunc('year', CURRENT_DATE) AND vendor_id = $vendor_id and outlet_id = $outlet_id ) AS year_count,
                COUNT(1) AS total_count
                FROM products WHERE vendor_id = $vendor_id and outlet_id = $outlet_id ";
            $products_query = DB::select($products_query);
            
            $products = DB::table('products')->select(DB::raw('count(id) as product_count'))->where('vendor_id',$vendor_id)->where('outlet_id',$outlet_id)->first();
            $orders   = DB::table('orders')->select(DB::raw('count(id) as orders_count'))->where('vendor_id',$vendor_id)->where('outlet_id',$outlet_id)->first();

            $query = 'SELECT (SELECT COUNT(orders.order_status) FROM orders WHERE orders.order_status = ?) AS oreder_initiated, (SELECT COUNT(orders.order_status) FROM orders WHERE orders.order_status = ?) AS oreder_processed, (SELECT COUNT(orders.order_status) FROM orders WHERE orders.order_status = ?) AS oreder_shipped, (SELECT COUNT(orders.order_status) FROM orders WHERE orders.order_status = ?) AS oreder_packed, (SELECT COUNT(orders.order_status) FROM orders WHERE orders.order_status = ?) AS oreder_dispatched FROM orders where vendor_id='.$vendor_id.' and outlet_id='.$vendor_id.' limit 1';
            $order_status_count = DB::select($query,array(1,10,14,18,19));
            
            $reviews_avg = DB::table('outlet_reviews')->where('vendor_id', $vendor_id)->where('outlet_id', $outlet_id)->where('approval_status', 1)->avg('ratings');
            
            $query1 = "SELECT to_char(i, 'YYYY') as year_data, to_char(i, 'MM') as month_data, to_char(i, 'Month') as month_string, sum(total_amount) as total_amount FROM generate_series(now() - INTERVAL '1 year', now(), '1 month') as i left join orders on (to_char(i, 'YYYY') = to_char(created_date, 'YYYY') and to_char(i, 'MM') = to_char(created_date, 'MM') and vendor_id = ".$vendor_id." and outlet_id = ".$outlet_id." ) GROUP BY 1,2,3 order by year_data desc, month_data desc limit 12";
            $year_transaction = DB::select($query1);

            $language_id = getAdminCurrentLang();
            $store_transaction_query = "SELECT outlet_infos.outlet_name, SUM (orders.total_amount) AS total FROM orders JOIN outlets on outlets.id = orders.outlet_id JOIN outlet_infos on outlets.id = outlet_infos.id where orders.vendor_id=$vendor_id and orders.outlet_id=$outlet_id GROUP BY outlet_infos.outlet_name ORDER BY total DESC";
            $store_transaction_count = DB::select($store_transaction_query);
            $currency_symbol = getCurrency();
            $currency_side   = getCurrencyPosition()->currency_side;

            return view('managers.home')->with('products', $products)->with('order_status_count', $order_status_count)->with('year_transaction', $year_transaction)->with('store_transaction_count', $store_transaction_count)->with('currency_symbol', $currency_symbol)->with('currency_side', $currency_side)->with('orders', $orders)->with('products', $products)->with('ratings',$reviews_avg)->with('order_period_count', $order_period_count)->with('transaction_period_count',$transaction_period_count)->with('outlet_reviews_query', $outlet_reviews_query);
        }
    }
    /*
     * Log out vendor and current sessions
    */
    public function logout() 
    {
        Session::flush();
        return Redirect::to('managers/login')->with('status', 'Your are now logged out!');
    }
    
    /*
     * Render the forgot password view
    */
    public function forgot() 
    {
        return view('managers.email');    
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
            $outlet_managers = DB::table('outlet_managers')
                        ->select('outlet_managers.id','outlet_managers.first_name','outlet_managers.email')
                        ->where('email',$email)
                        ->where('active_status',1)
                        ->where('is_verified',1)
                        ->get();
            if(count($outlet_managers) > 0){
                $outlet_data = $outlet_managers[0];
                //Generate random password string
                $string = str_random(8);
                $pass_string = md5($string);
                //Sending the mail to outlet managers
                $template=DB::table('email_templates')
                        ->select('*')
                        ->where('template_id','=',self::MANAGER_FORGOT_PASSWORD_EMAIL_TEMPLATE)
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
                   $content = array("name" => $outlet_data->first_name,"email"=>$outlet_data->email,"password"=>$string);
                   $email = smtp($from,$from_name,$outlet_data->email,$subject,$content,$template);
                }
                //Update random password to vendors table to coreesponding vendor id
                $res = DB::table('outlet_managers')
                    ->where('id', $outlet_data->id)
                    ->update(['hash_password' => $pass_string]);
                //Show the flash message if successful logged in
                Session::flash('status', trans('messages.Password was sent your email successfully.'));
                return Redirect::to('managers/login');
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
        if (!Session::get('manager_id')) {
            return redirect()->guest('managers/login');
        } else {
            return view('managers.reset');
        }
    }
    
    /*
     * Vendor change password request goes here
    */
    public function change_details(Request $data) 
    {
        if (!Session::get('manager_id')) {
            return redirect()->guest('managers/login');
        } 
        $datas=Input::all();
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $validation = Validator::make($data->all(), array(
            'old_password' => 'required|min:6|max:16',
            'password' => 'required|min:6|max:16|confirmed',
            'password_confirmation' => 'required|min:6|max:16|regex:/(^[A-Za-z0-9 !@#$%]+$)+/'
        ));
        // process the validation
        if ($validation->fails()) {
            //return redirect('create')->withInput($datas)->withErrors($validation);
            return Redirect::back()->withErrors($validation)->withInput();
        } else {
            //Get new password details from posts
            $old_password = Input::get('old_password');
            $string       = Input::get('password');
            $pass_string  = md5($string);
            $oldpass       = md5($old_password);
            $manager_id   = Session::get('manager_id');
            $outlet_managers = DB::table('outlet_managers')
                        ->select('outlet_managers.id','outlet_managers.first_name','outlet_managers.email')
                        ->where('id',$manager_id)
                        ->where('hash_password',$oldpass)
                        ->where('active_status',1)
                        ->get();
            //print_r($outlet_managers);exit;
            if(count($outlet_managers) > 0){
				
                $outlet_data = $outlet_managers[0];
                //Sending the mail to vendors
                $template=DB::table('email_templates')
                        ->select('*')
                        ->where('template_id','=',self::MANAGER_CHANGE_PASSWORD_EMAIL_TEMPLATE)
                        ->get();
                       // print_r($template); exit;
               /* if(count($template)){
                   $from = $template[0]->from_email;
                   $from_name = $template[0]->from;
                   $subject = $template[0]->subject;
                   if(!$template[0]->template_id){
                       $template = 'mail_template';
                       $from = getAppConfigEmail()->contact_email;
                       $subject = getAppConfig()->site_name." New Password Request Updated";
                       $from_name = "";
                   }
                   $content = array("name" => $outlet_data->first_name,"email"=>$outlet_data->email,"password"=>$string);
                   $email = smtp($from,$from_name,$outlet_data->email,$subject,$content,$template);
                } */
                //Update random password to vendors table to coreesponding vendor id
                $res = DB::table('outlet_managers')
                    ->where('id', $outlet_data->id)
                    ->update(['hash_password' => $pass_string]);
                //After updating new password details logout the session and redirects to login page
                Session::flash('message', trans('messages.Your Password Changed Successfully.'));
                return Redirect::to('managers/dashboard');
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
        if (!Session::get('manager_id')){
            return redirect()->guest('managers/login');
        }else{
            //Get session user details.        
            $id = Session::get('manager_id');
            //Get manager details
            $manager = outlet_managers::find($id);
            $vendors_list  = getVendorLists(5);
            $settings = Settings::find(1);
            if(!count($manager)){
                 Session::flash('message', 'Invalid Manager Details'); 
                 return Redirect::to('managers/dashboard');    
            }
            //Get countries data
            $countries = getCountryLists();
            return view('managers.edit_profile')->with('countries', $countries)->with('data',$manager)->with('vendors_list',$vendors_list)->with('settings',$settings);
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
        if (!Session::get('manager_id')) {
            return redirect()->guest('managers/login');
        }
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation
        $validation = Validator::make($data->all(), array(
            'first_name'    => 'required',
            'last_name'     => 'required',
            'email'         => 'required|email|max:255|unique:outlet_managers,email,'.$id,
            //'mobile'        => 'regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
            'mobile'        => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            'date_of_birth' => 'date',
            'gender'        => 'required',
            'gender'        => 'required',
            //'outlet_name' => 'required',
            'postal_code'   => 'required|numeric',
            'address'       => 'required',
        ));
        // process the validation
        if ($validation->fails()) {
            return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store datas in to database
            $managers = Outlet_managers::find($id);
            $managers->first_name    = $_POST['first_name'];
            $managers->last_name     = $_POST['last_name'];
            $managers->email         = $_POST['email'];
            $managers->mobile_number = $_POST['mobile'];
            $managers->gender        = $_POST['gender'];
            if($_POST['date_of_birth']!='') {
                $managers->date_of_birth = $_POST['date_of_birth'];
            }
            if(isset($_POST['country']) && $_POST['country']!='') {
                $managers->country_id = $_POST['country'];
            }
            if(isset($_POST['city']) && $_POST['city']!='') {
                $managers->city_id = $_POST['city'];
            }
            $managers->modified_date = date("Y-m-d H:i:s");
            $managers->postal_code   = $_POST['postal_code'];
            $managers->address       = $_POST['address'];
            $managers->vendor_id     = Session::get('manager_vendor');
            $managers->outlet_id     = Session::get('manager_outlet');
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
            Session::put('manager_name', ucfirst($_POST['first_name']));
            Session::flash('message', trans('messages.Profile has been successfully updated'));
            return Redirect::to('managers/dashboard');
        }
    }    

    /**
     * Display a listing of the products.
     *
     * @return Response
     */
    public function index()
    {        
        if (!Session::get('manager_id')) {
            return redirect()->guest('managers/login');
        } else {
			//echo"ss"; exit;
            return view('managers.products.list');
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
                    ->where('products.vendor_id','=',Session::get('manager_vendor'))
                    ->where('products.outlet_id','=',Session::get('manager_outlet'))
                    ->where('products.outlet_id','=',Session::get('manager_outlet'))
                    ->orderBy('products.created_date', 'desc')
                    ->get();
                     return Datatables::of($cdata)->addColumn('action', function ($cdata) {
                    $html='<div class="btn-group"><a href="'.URL::to("managers/products/edit_product/".$cdata->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                                <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu xs pull-right" role="menu">
                                    <li><a href="'.URL::to("managers/products/product_details/".$cdata->id).'" class="view-'.$cdata->id.'" title="'.trans("messages.View").'"><i class="fa fa-file-text-o"></i>&nbsp;&nbsp;'.@trans("messages.View").'</a></li>
                                    <li><a href="'.URL::to("managers/products/delete_product/".$cdata->id).'" class="delete-'.$cdata->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
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
        if (!Session::get('manager_id')) {
            return redirect()->guest('managers/login');
        } else {
           // $ingredient_type_list = Products::ingredient_type_list();
           // $weight_class         = Products::getWeightClass();
            return view('managers.products.create');
        }
    }

    /**
     * Store a newly created category in storage.
     *
     * @return Response
     */
    public function product_store(Request $data)
    {
		//print_r($data); exit;
		
        if (!Session::get('manager_id')) {
            return redirect()->guest('managers/login');
        }
         $fields['category'] = Input::get('category');
        $fields['sub_category'] = Input::get('sub_category');
        $fields['head_category'] = Input::get('head_category');
        $fields['weight_class'] = Input::get('weight_class');
        $fields['weight_value'] = Input::get('weight_value');
        $fields['original_price'] = Input::get('original_price');
        $fields['discount_price'] = Input::get('discount_price');
        $fields['total_quantity'] = Input::get('total_quantity');
        $fields['product_image'] = Input::file('product_image');
       // $fields['cuisine']  = Input::get('cuisine');
        $rules = array(
            'category'       => 'required', 
            'sub_category'       => 'required', 
            'head_category'       => 'required',
           // 'cuisine'  => 'required',
            'weight_class'   => 'required',
            'weight_value'   => 'required|numeric',
            'total_quantity' => 'required|integer',
            'original_price' => 'required|numeric',
            'discount_price' => 'required|numeric',
            'product_image'  => 'required|mimes:png,jpg,jpeg,bmp|max:2024',
        );
        $product_name = Input::get('product_name');
        foreach ($product_name  as $key => $value)
        {
            $fields['product_name'.$key] = $value;
            $rules['product_name'.'1'] = 'required';
        }
        $description = Input::get('description');
        foreach ($description  as $key => $value)
        {
            $fields['description'.$key] = $value;
            $rules['description'.'1'] = 'required';
        }
        $meta_title = Input::get('meta_title');
        foreach ($meta_title  as $key => $value)
        {
            $fields['meta_title'.$key] = $value;
            $rules['meta_title'.'1'] = 'required';
        }
        $meta_keywords = Input::get('meta_keywords');
        foreach ($meta_keywords  as $key => $value)
        {
            $fields['meta_keywords'.$key] = $value;
            $rules['meta_keywords'.'1'] = 'required';
        }
        $meta_description = Input::get('meta_description');
        foreach ($meta_description  as $key => $value)
        {
            $fields['meta_description'.$key] = $value;
            $rules['meta_description'.'1'] = 'required';
        }
        $validation = Validator::make($fields, $rules);
        if ($validation->fails())
        {
            $errors = '';
            $j = 0;
            $error = array();
            foreach( $validation->errors()->messages() as $key => $value) 
            {
                $error[] = is_array($value)?implode( ',',str_replace("."," ",$value) ):str_replace("."," ",$value);
            }
            $errors = implode( "<br>", $error );
            $result = array("httpCode" => 400, "errors" => $errors);return json_encode($result);exit;
        } 
        else {
            $err_msg = '';
            if($fields['original_price'] < $fields['discount_price'])
            {
                $result = array("httpCode" => 400, "errors" => trans('messages.Discount price should be less than original price.'));return json_encode($result);exit;
            }
            $data_all = $data->all();
            $err_msg  = array();
            
            if(!empty($err_msg))
            {
                $errors = implode( "<br>", $err_msg );
                $result = array("httpCode" => 400, "errors" => $errors);return json_encode($result);exit;
            }
            // store
            $Products = new Products;
            $Products->vendor_id       = Session::get('manager_vendor');
            $Products->outlet_id       = Session::get('manager_outlet');
            $Products->category_id = $_POST['category'];
	    $Products->sub_category_id = $_POST['sub_category'];
	    $Products->vendor_category_id = $_POST['head_category'];
           // $Products->cuisine_ids     = implode(',',$data_all['cuisine']);
            $Products->weight_class_id = $data_all['weight_class'];
            $Products->weight          = $data_all['weight_value'];
            $Products->quantity        = $data_all['total_quantity'];
            $Products->original_price  = $data_all['original_price'];
            $Products->discount_price  = $data_all['discount_price'];
            $Products->active_status   = 1;
            $Products->approval_status = 1;
            $Products->created_date    = date("Y-m-d H:i:s");
            $Products->created_by      = Auth::id();
            $Products->product_url     = str_slug($data_all['product_name'][1]);
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
            // redirect
            $result["status"] = 200;
            Session::flash('message',trans('Product added successfully'));
            $result["errors"] = "";
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
        if(isset($post['product_name']))
        {
            $product_name = $post['product_name'];
            $description  = $post['description'];
            $meta_title   = $post['meta_title'];
            $meta_keywords    = $post['meta_keywords'];
            $meta_description = $post['meta_description'];
            try{
                $affected = DB::table('products_infos')->where('id', '=', $object->id)->delete();
                $languages = DB::table('languages')->where('status', 1)->get();
                foreach($languages as $key => $lang)
                {
                    if(isset($product_name[$lang->id]) && $product_name[$lang->id]!="")
                    {
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
        if (!Session::get('manager_id')) {
            return redirect()->guest('managers/login');
        } else {
            $data = Products::find($id);
            if(!count($data)){
                Session::flash('message', 'Invalid Product Details'); 
                return Redirect::to('managers/products');
            }
            $info = new Products_infos;
            //$ingredient_type_list    = Products::ingredient_type_list();
            //$product_ingre_type_list = Products::product_ingredient_type_list($id);
            //$weight_class = Products::getWeightClass();
            return view('managers.products.edit')->with('infomodel', $info)->with('data', $data);
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
		//echo"ss"; exit;
        if (!Session::get('manager_id')) {
            return redirect()->guest('managers/login');
        }
       $fields['category'] = Input::get('category');
        $fields['sub_category'] = Input::get('sub_category');
        $fields['head_category'] = Input::get('head_category');
       // $fields['cuisine']        = Input::get('cuisine');
        $fields['weight_class']   = Input::get('weight_class');
        $fields['weight_value']   = Input::get('weight_value');
        $fields['original_price'] = Input::get('original_price');
        $fields['discount_price'] = Input::get('discount_price');
        $fields['total_quantity'] = Input::get('total_quantity');
        $fields['product_url']    = Input::get('product_url');
        $fields['product_image']  = Input::file('product_image');
        $rules = array(
           'category'       => 'required', 
            'sub_category'       => 'required', 
            'head_category'       => 'required',
           // 'cuisine' => 'required',
            'weight_class' => 'required',
            'weight_value' => 'required|numeric',
            'total_quantity' => 'required|integer',
            'original_price' => 'required|numeric',
            'discount_price' => 'required|numeric',
            'product_image' => 'mimes:png,jpg,jpeg,bmp|max:2024',
            //'product_url' => 'required|regex:/(^[A-Za-z0-9-]+$)+/',
        );
        $product_name = Input::get('product_name');
        foreach ($product_name  as $key => $value) {
            $fields['product_name'.$key] = $value;
            $rules['product_name'.'1'] = 'required';
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
                $result = array("httpCode" => 400, "errors" => trans('messages.Discount price should be less than original price.'));return json_encode($result);exit;
            }
            $data_all = $data->all();
            $err_msg  = array();
           
            if(!empty($err_msg))
            {
                $errors = implode( "<br>", $err_msg );
                $result = array("httpCode" => 400, "errors" => $errors);return json_encode($result);exit;
            }
            // store datas in to database
            $Products = Products::find($id);
            $Products->vendor_id       = Session::get('manager_vendor');
            $Products->outlet_id       = Session::get('manager_outlet');
           $Products->category_id = $_POST['category'];
			$Products->sub_category_id = $_POST['sub_category'];
			$Products->vendor_category_id = $_POST['head_category'];
           // $Products->cuisine_ids     = implode(',',$_POST['cuisine']);
            $Products->weight_class_id = $_POST['weight_class'];
            $Products->weight          = $_POST['weight_value'];
            $Products->quantity        = $_POST['total_quantity'];
            $Products->original_price  = $_POST['original_price'];
            $Products->discount_price  = $_POST['discount_price'];
            $Products->product_url     = str_slug($_POST['product_name'][1]);
            $Products->modified_date   = date("Y-m-d H:i:s");
            $Products->save();
            if(isset($_FILES['product_image']['name']) && $_FILES['product_image']['name']!=''){ 
                $imageName = $id . '.' . $data->file('product_image')->getClientOriginalExtension();
                $data->file('product_image')->move(
                    base_path() . '/public/assets/admin/base/images/products/', $imageName
                );
                $destinationPath2 = url('/assets/admin/base/images/products/'.$imageName.'');
                $size=getImageResize('PRODUCT');
                Image::make( $destinationPath2 )->fit($size['LIST_WIDTH'], $size['LIST_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/list/'.$imageName)->destroy();
              /*  Image::make( $destinationPath2 )->fit($size['DETAIL_WIDTH'], $size['DETAIL_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/detail/'.$imageName)->destroy();*/
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
        if (!Session::get('manager_id')){
            return redirect()->guest('managers/login');
        } else {
            $admin_language = getAdminCurrentLang();
            $query  = '"products_infos"."lang_id" = (case when (select count(lang_id) as totalcount from products_infos where products_infos.lang_id = '.$admin_language.' and products.id = products_infos.id) > 0 THEN '.$admin_language.' ELSE 1 END)';
            $query1 = '"outlet_infos"."language_id" = (case when (select count(language_id) as totalcount from outlet_infos where outlet_infos.language_id = '.$admin_language.' and products.outlet_id = outlet_infos.id) > 0 THEN '.$admin_language.' ELSE 1 END)';
            $data   = DB::table('products')
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
                        ->select('products.id','products.product_url','products.product_image','products.approval_status','products.active_status','products.weight_class_id','products.weight','products.quantity','products.original_price','products.discount_price','products_infos.product_name','products_infos.description','products_infos.meta_title','products_infos.meta_keywords','products_infos.meta_description','categories_infos.category_name','categories_infos.category_name','vendors_infos.vendor_name','outlet_infos.outlet_name')
                        ->whereRaw($query)
                        ->whereRaw($query1)
                        ->where('products.vendor_id','=',Session::get('manager_vendor'))
                        ->where('products.outlet_id','=',Session::get('manager_outlet'))
                        ->where('products.id',$id)
                        ->orderBy('products.created_date', 'desc')
                        ->first();
            if(!count($data))
            {
                Session::flash('message', 'Invalid Product Details'); 
                return Redirect::to('managers/products');
            }
            $info = new Products_infos;
          // $product_ingre_type_list = products::product_ingredient_type_list($id);
          // print_r($product_ingre_type_list); exit;
            return view('managers.products.show')->with('data', $data)->with('infomodel', $info);
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
        if (!Session::get('manager_id')) {
            return redirect()->guest('managers/login');
        }
        $data = Products::find($id);
        if(!count($data)){
            Session::flash('message', 'Invalid Product Details'); 
            return Redirect::to('managers/products');
        }
        //$data->delete();
        //Update delete status while deleting
        $data->active_status = 2;
        $data->save();
        Session::flash('message', trans('messages.Product has been deleted successfully!'));
        return Redirect::to('managers/products');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function reviews()
    {
        if (!Session::get('manager_id')){
            return redirect()->guest('managers/login');
        } else {
            return view('managers.reviews.list');
        }
    }
    
         /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function anyAjaxreviewlistmanager()
    {
        $query = '"outlet_infos"."language_id" = (case when (select count(language_id) as totalcount from outlet_infos where outlet_infos.language_id = '.getAdminCurrentLang().' and outlets.id = outlet_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $reviews = DB::table('outlet_reviews')
                    ->select('outlet_reviews.id as review_id','outlet_reviews.customer_id as review_customer_id','outlet_reviews.vendor_id as review_vendor_id','outlet_reviews.comments','outlet_reviews.title','outlet_reviews.approval_status','outlet_reviews.ratings','outlet_reviews.created_date as review_posted_date','users.name as user_name','users.email as user_email','users.id as user_id','users.image as user_image','vendors.id as store_id','vendors.first_name as store_first_name','vendors.last_name as store_last_name','vendors.email as store_email','vendors.phone_number as store_phone_number','outlet_infos.outlet_name','outlets.id as outletid')
                    ->leftJoin('users','users.id','=','outlet_reviews.customer_id')
                    ->leftJoin('outlets','outlets.id','=','outlet_reviews.outlet_id')
                    ->leftJoin('outlet_infos','outlets.id','=','outlet_infos.id')
                    ->leftJoin('vendors','vendors.id','=','outlets.vendor_id')
                    ->where("outlet_reviews.vendor_id","=",Session::get('manager_vendor'))
                    ->where("outlet_reviews.outlet_id","=",Session::get('manager_outlet'))
                    ->whereRaw($query)
                    ->orderBy('outlet_reviews.id', 'desc');
            return Datatables::of($reviews)->addColumn('action', function ($reviews) {
                return '<div class="btn-group"><a href="'.URL::to("managers/reviews/view/?review_id=".$reviews->review_id).'" class="btn btn-xs btn-white" title="'.trans("messages.View").'"><i class="fa fa-eye"></i>&nbsp;'.trans("messages.View").'</a>';
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
        if (!Session::get('manager_id'))
        {
            return redirect()->guest('managers/login');
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
                            ->where("outlet_reviews.vendor_id","=",Session::get('manager_vendor'))
                            ->where("outlet_reviews.outlet_id","=",Session::get('manager_outlet'))
                            ->whereRaw($query1)
                            ->get();
            if(!count($reviews))
            {
                Session::flash('message', trans('messages.Invalid Request'));
                return Redirect::to('managers/reviews');
            }
            return view('managers.reviews.show')->with('review', $reviews[0]);
        }
    }
    /*
    * Return orders listing
    */
    public function return_orders_list()
    {
        if (!Session::get('manager_id')) {
            return redirect()->guest('managers/login');
        } else {
            $vendor_id = Session::get('manager_vendor');
            $outlet_id = Session::get('manager_outlet');
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
                        ->where('orders.outlet_id',$outlet_id)
                        ->whereRaw($query)
                        ->whereRaw($query1)
                        ->whereRaw($condition)
                        ->orderBy('return_orders.created_at', 'desc')
                        ->paginate(10);
            if(Input::get('export'))
            {
                $out = '"Order Id","Name","Merchant Name","Restaurant Name","Status","Total Amount","Payment Mode","Order Date"'."\r\n";
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
            return view('managers.return_orders.list')->with('data', $list);
        }
    }
    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxReturnOrders()
    {
        if (!Session::get('manager_id')) {
            return redirect()->guest('managers/login');
        } else {
            $vendor_id = Session::get('manager_vendor');
            $outlet_id = Session::get('manager_outlet');
            $list = DB::table('return_orders')
                    ->select('return_orders.*','return_action.name as return_action_name','return_reason.name as return_reason_name','return_status.name as return_status_name','orders.order_key_formated','users.name as username')
                    ->leftJoin('return_action','return_action.id','=','return_orders.return_action_id')
                    ->leftJoin('return_reason','return_reason.id','=','return_orders.return_reason')
                    ->leftJoin('return_status','return_status.id','=','return_orders.return_status')
                    ->leftJoin('orders','orders.id','=','return_orders.order_id')
                    ->leftJoin('users','users.id','=','orders.customer_id')
                    ->where('orders.vendor_id',$vendor_id)
                    ->where('orders.outlet_id',$outlet_id)
                    ->orderBy('return_orders.created_at', 'desc');
            return Datatables::of($list)->addColumn('action', function ($list) {
                    return '<div class="btn-group"><a href="'.URL::to("managers/return_orders_view/".$list->id).'" class="btn btn-xs btn-white" title="'.trans("messages.View").'"><i class="fa fa-file-text-o"></i>&nbsp;'.trans("messages.View").'</a></div>';
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
        if (!Session::get('manager_id')) {
            return redirect()->guest('managers/login');
        } else {
            $vendor_id = Session::get('manager_vendor');
            $outlet_id = Session::get('manager_outlet');
            
            $query = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = '.getAdminCurrentLang().' and vendors.id = vendors_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $query1 = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.language_id) as totalcount from outlet_infos where outlet_infos.language_id = '.getAdminCurrentLang().' and outlets.id = outlet_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $os_query = '"order_status"."lang_id" = (case when (select count(*) as totalcount from order_status where order_status.lang_id = '.getAdminCurrentLang().' and orders.order_status = order_status.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $pay_query = 'payment_gateways_info.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.getAdminCurrentLang().' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $data = DB::table('return_orders')
                        ->select('return_orders.*','return_action.name as return_action_name','return_reason.name as return_reason_name','return_status.name as return_status_name','orders.order_key_formated','users.name as username','users.email','users.first_name','users.last_name','users.mobile','outlet_infos.outlet_name','vendors_infos.vendor_name','orders.order_key','orders.invoice_id','orders.total_amount','orders.vendor_id','orders.customer_id','orders.outlet_id','orders.delivery_date','orders.delivery_charge','orders.service_tax','orders.coupon_amount','order_status.name as order_status','orders.delivery_instructions','orders.created_date as ordered_date','user_address.address','payment_gateways.id as payment_gateway_id','payment_gateways_info.name','delivery_time_interval.start_time','delivery_time_interval.end_time','outlets.contact_email','transaction.currency_code')
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
                        ->where('orders.outlet_id',$outlet_id)
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
                return Redirect::to('managers/orders/return_orders');
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
            return view('managers.return_orders.show')->with('data', $data)->with('items_data', $items_info)->with('return_reasons', $return_reasons)->with('return_statuses', $return_statuses)->with('return_actions', $return_actions)->with('return_orders_logs', $return_orders_logs);
        }
    }
    /*
    * Update the return_orders & orders table
    */
    public function return_orders_update(Request $data,$id)
    {
        if (!Session::get('manager_id'))
        {
            return redirect()->guest('managers/login');
        } else
        {
            $vendor_id = Session::get('manager_vendor');
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
                return Redirect::to('managers/return_orders');
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
            $cont_replace = "Your return order <b>". $post['invoice_id'] ."</b> status has been updated with store or restaurant.";
            $cont_replace1 = "Your order has been <b>". $return_status->name ."</b> and make it necessary arrangements we are waiting for <b>". $return_action->name."</b>";
            $content = array("name" => $customer->name,"order_key"=>$post['invoice_id'],"return_status"=> $return_status->name ,"return_action"=> $return_action->name);
            $email = smtp($from,$from_name,$customer->email,$subject,$content,$template);
            return;
        }
    }

    public function orders()
    {
		
        if (!Session::get('manager_id'))
        {
            return redirect()->guest('managers/login');
        }
        else {
            $vendor_id = Session::get('manager_vendor');
              $outlet_id = Session::get('manager_outlet');
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
                        ->select('orders.id','orders.total_amount','orders.created_date','orders.modified_date','orders.delivery_date','users.first_name','users.last_name','order_status.name as status_name','order_status.color_code as color_code','users.name as user_name','transaction.currency_code','payment_gateways_info.name as payment_type','outlet_infos.outlet_name','outlet_infos.contact_address','vendors_infos.vendor_name as vendor_name','vendors_infos.id as vendor_id','orders.id', 'outlets.latitude as outlet_latitude', 'outlets.longitude as outlet_longitude', 'outlets.id as outlet_id', 'orders.request_vendor as request_vendor', 'drivers.first_name as driver_name') 
                        ->leftJoin('users','users.id','=','orders.customer_id')
                        ->leftJoin('order_status','order_status.id','=','orders.order_status')
                        ->leftjoin('transaction','transaction.order_id','=','orders.id')
                        ->Join('payment_gateways_info','payment_gateways_info.payment_id','=','orders.payment_gateway_id')
                        ->Join('vendors_infos','vendors_infos.id','=','orders.vendor_id')
                        ->Join('outlet_infos','outlet_infos.id','=','orders.outlet_id')
                        ->Join('outlets', 'outlets.id', '=', 'outlet_infos.id')
                        ->leftJoin('driver_orders', 'driver_orders.order_id', '=', 'orders.id')

                        ->leftJoin('drivers', 'drivers.id', '=', 'driver_orders.driver_id')


                        ->where('orders.vendor_id',$vendor_id)  
                        ->where('orders.outlet_id',$outlet_id)
                        ->whereRaw($query)
                        ->whereRaw($query1)
                        ->whereRaw($query2)
                        ->whereRaw($condition)
                        ->orderBy('orders.id', 'desc')
                        ->paginate(10);
                    //   echo"<pre>"; print_r($orders);exit;
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
                $out = '"Order Id","Name","Merchant Name","Restaurant Name","Status","Total Amount","Payment Mode","Order Date"'."\r\n";
                foreach($orders as $d)
                {
                    $out .= $d->id.',"'.$d->user_name.'","'.$d->vendor_name.'","'.$d->outlet_name.'","'.$d->status_name.'","'.$d->total_amount.$d->currency_code.'","'.$d->payment_type.'","'.date("d F, Y", strtotime($d->created_date)).'"'."\r\n";
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
            return view('managers.orders.list')->with('orders', $orders)->with('order_status', $order_status)->with('payment_seetings', $payment_seetings);
        }
    }
    /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function anyAjaxOrderlist()
    {
        $vendor_id = Session::get('manager_vendor');
        $outlet_id = Session::get('manager_outlet');
        $orders = DB::table('orders')
        ->select('orders.id','orders.total_amount','orders.created_date','orders.delivery_date','users.first_name','users.last_name','order_status.name as status_name','order_status.color_code as color_code','users.name as user_name','transaction.currency_code')
        ->leftJoin('users','users.id','=','orders.customer_id')
        ->leftJoin('order_status','order_status.id','=','orders.order_status')
        ->leftJoin('transaction','transaction.order_id','=','orders.id')
        ->where('orders.vendor_id',$vendor_id)
        ->where('orders.outlet_id',$outlet_id)
        ->orderBy('orders.id', 'desc');
        return Datatables::of($orders)->addColumn('action', function ($orders) {
                $html = '--';
                $html ='<a href="'.URL::to("managers/orders/info/".$orders->id).'" class="btn btn-xs btn-white" title="'.trans("messages.View").'"><i class="fa fa-view"></i>&nbsp;'.trans("messages.View").'</a>';
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
        if (!Session::get('manager_id')) {
            return redirect()->guest('managers/login');
        } else {
            $vendor_id = Session::get('manager_vendor');
            $language_id = getAdminCurrentLang();
            $query3 = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = '.$language_id.' and vendors.id = vendors_infos.id) > 0 THEN '.$language_id.' ELSE 1 END)';
            $query4 = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.$language_id.' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN '.$language_id.' ELSE 1 END)';
            $vendor_info = DB::select('SELECT vendors_infos.vendor_name,vendors.email,o.id as order_id,o.created_date,o.order_status,order_status.name as status_name,order_status.color_code as color_code,o.outlet_id,vendors.id as vendor_id,o.order_key_formated
            FROM orders o
            left join vendors vendors on vendors.id = o.vendor_id
            left join outlets outlets on outlets.vendor_id = vendors.id
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
            $delivery_details = DB::select('SELECT o.id as order_id, o.delivery_instructions,ua.address,pg.id as payment_gateway_id,pgi.name,o.total_amount,o.delivery_charge,o.service_tax,dti.start_time,end_time,o.created_date,o.delivery_date,o.order_type,out.contact_email,o.coupon_amount,users.first_name,users.last_name,users.email,users.mobile,o.order_key_formated,out_inf.outlet_name,trans.currency_code,pgi.name as payment_gateway_name,users.name as user_name 
                        FROM orders o
                        left join users on o.customer_id = users.id
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
            return view('managers.orders.show')->with('order_items', $order_items)->with('delivery_details', $delivery_details)->with('vendor_info', $vendor_info)->with('order_history', $order_history)->with('order_status_list', $order_status_list);
        }
    }
    public function update_status(Request $data)
    {
        if (!Session::get('manager_id')) {
            return redirect()->guest('managers/login');
        }

        $post_data = $data->all();
        $affected = DB::update('update orders set order_status = ?,order_comments = ? where id = ?', array($post_data['order_status_id'],$post_data['comment'],$post_data['order_id']));
        $affected = DB::update('update orders_log set order_status=?, order_comments = ? where id = (select max(id) from orders_log where order_id = '. $post_data['order_id'].')', array($post_data['order_status_id'],$post_data['comment']));
        if(isset($post_data['notify']) && $post_data['notify'] == 1)
        {
            $order_detail = $this->get_order_detail($post_data['order_id']);
            $order_details = $order_detail["order_items"];
            $delivery_details = $order_detail["delivery_details"];
            $vendor_info = $order_detail["vendor_info"];
            $logo = url('/assets/front/'.Session::get("general")->theme.'/images/'.Session::get("general")->site_name.'.png');
     /*       if(file_exists(base_path().'/public/assets/admin/base/images/outlets/list/'.$vendor_info[0]->logo_image)) { 
                $vendor_image ='<img width="100px" height="100px" src="'.URL::to("assets/admin/base/images/outlets/list/".$vendor_info[0]->logo_image).'") >';
            }
            else
            {  
                $vendor_image ='<img width="100px" height="100px" src="'.URL::to("assets/admin/base/images/stores.png").'") >';
            }*/
            $delivery_date = date("d F, l", strtotime($delivery_details[0]->delivery_date)); 
            $delivery_time = date('g:i a', strtotime($delivery_details[0]->start_time)).'-'.date('g:i a', strtotime($delivery_details[0]->end_time));
            $users=Users::find($delivery_details[0]->customer_id); 
            $to=$users->email;
            $subject = 'Your Order with '.getAppConfig()->site_name.' ['.$vendor_info[0]->order_key_formated .'] has been successfully '.$vendor_info[0]->status_name.'!';
            $template=DB::table('email_templates')
            ->select('*')
            ->where('template_id','=',self::ORDER_STATUS_UPDATE_USER)
            ->get();
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
        }
        return 1;
    }
    
    public function load_history($order_id)
    {
        if (!Session::get('manager_id')) {
            return redirect()->guest('managers/login');
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
        if (!Session::get('manager_id')) {
            return redirect()->guest('managers/login');
        }
        $language_id = getCurrentLang();
        $query3 = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = '.$language_id.' and vendors.id = vendors_infos.id) > 0 THEN '.$language_id.' ELSE 1 END)';
        $query4 = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.$language_id.' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN '.$language_id.' ELSE 1 END)';
        $vendor_info = DB::select('SELECT vendors_infos.vendor_name,vendors.email,outlets.logo_image,o.id as order_id,o.created_date,o.order_status,order_status.name as status_name,order_status.color_code as color_code,payment_gateways_info.name as payment_gateway_name,o.outlet_id,vendors.id as vendor_id,o.order_key_formated
        FROM orders o
        left join vendors vendors on vendors.id = o.vendor_id
        left join outlets outlets on outlets.vendor_id = vendors.id
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
        $delivery_details = DB::select('SELECT o.delivery_instructions,ua.address,pg.id as payment_gateway_id,pgi.name,o.total_amount,o.delivery_charge,o.service_tax,dti.start_time,end_time,o.created_date,o.delivery_date,o.order_type,out.contact_email,o.coupon_amount,o.customer_id FROM orders o
                    LEFT JOIN user_address ua ON ua.id = o.delivery_address
                    left join payment_gateways pg on pg.id = o.payment_gateway_id
                    left join payment_gateways_info pgi on pgi.payment_id = pg.id
                    left join delivery_time_slots dts on dts.id=o.delivery_slot
                    left join delivery_time_interval dti on dti.id = dts.time_interval_id
                    left join outlets out on out.id = o.outlet_id
                    where '.$query2.' AND o.id = ?',array($order_id));
        if(count($order_items)>0 && count($delivery_details)>0 && count($vendor_info)>0)
        {
            $result = array("order_items"=>$order_items,"delivery_details"=>$delivery_details,"vendor_info"=>$vendor_info);
        }
        return $result;
    }
    /** Report Orderds List for Managers **/
    public function order()
    {
        if (!Session::get('manager_id')) {
            return redirect()->guest('managers/login');
        } else {
            SEOMeta::setTitle('Orders Reports - '.$this->site_name);
            SEOMeta::setDescription('Orders Reports - '.$this->site_name);
            
            $order_status = DB::table('order_status')->select('id','name')->orderBy('name', 'asc')->get();
            return view('managers.reports.orders.list')->with('order_status',$order_status);
        }
    }
    /** Report Orderds List for Managers **/
    public function anyAjaxReportOrderList(Request $request)
    {
        $vendor_id = Session::get('manager_vendor');
        $outlet_id = Session::get('manager_outlet');
        $post_data = $request->all();
        $orders    = DB::table('orders')
                        ->selectRaw('COUNT(orders.id) AS orders_count, SUM(orders.service_tax) AS tax_total, SUM((SELECT SUM(orders_info.item_unit) FROM orders_info WHERE orders_info.order_id = orders.id GROUP BY orders_info.order_id)) AS quantity_count,  SUM(orders.total_amount) AS total')
                        ->where('orders.vendor_id',$vendor_id)
                        ->where('orders.outlet_id',$outlet_id);
        return Datatables::of($orders)->addColumn('date_start', function ($orders) {
            $data = date("M-d-Y, l h:i:a", strtotime($orders->date_start));
            return $data;
        })
        ->addColumn('date_end', function ($orders) {
            $data = date("M-d-Y, l h:i:a", strtotime($orders->date_end));
            return $data;
        })
        ->addColumn('tax_total', function ($orders) {
            if(getCurrencyPosition()->currency_side == 1)
            {
                return getCurrency().$orders->tax_total;
            }
            else {
                return $orders->tax_total.getCurrency();
            }
        })
        ->addColumn('total', function ($orders) {
            if(getCurrencyPosition()->currency_side == 1)
            {
                return getCurrency().$orders->total;
            }
            else {
                return $orders->total.getCurrency();
            }
        })
        ->filter(function ($query) use ($request){
            $condition = '1=1';
            if ($request->has('from') != '' && $request->has('to') != '')
            {
                $from = date('Y-m-d H:i:s', strtotime($request->get('from')));
                $to   = date('Y-m-d H:i:s', strtotime($request->get('to')));
                $condition1 = $condition." and orders.created_date BETWEEN '".$from."'::timestamp and '".$to."'::timestamp";
                
                $query->whereRaw($condition1);
            }
            if ($request->has('order_status') != '')
            {
                $order_status = Input::get('order_status');
                $condition2   = $condition." and orders.order_status = ".$order_status;
                
                $query->whereRaw($condition2);
            }
            if ($request->has('group_by'))
            {
                $group_by = ($request->get('group_by') != '')?$request->get('group_by'):1;
                if($group_by == 1)
                    $start_date = " date_trunc('day', orders.created_date) AS date_start, ";
                else if($group_by == 2)
                    $start_date = " date_trunc('week', orders.created_date) AS date_start, ";
                else if($group_by == 3)
                    $start_date = " date_trunc('month', orders.created_date) AS date_start, ";
                else if($group_by == 4)
                    $start_date = " date_trunc('year', orders.created_date) AS date_start, ";
                $query->selectRaw($start_date.' MAX(orders.created_date) AS date_end')->groupBy('date_start');
            }
        })
         ->rawColumns(['total','tax_total','date_end','date_start'])

        ->make(true);
    }
    /* Reports for return orders */
    public function returns()
    {
        if (!Session::get('manager_id')) {
            return redirect()->guest('managers/login');
        } else {
            SEOMeta::setTitle('Return Orders Reports - '.$this->site_name);
            SEOMeta::setDescription('Return Orders Reports - '.$this->site_name);
            
            $order_status = DB::table('return_status')->select('id','name')->orderBy('name', 'asc')->get();
            return view('managers.reports.returns.returns')->with('order_status', $order_status);
        }
    }
    /* Return orders for ajax list */
    public function anyAjaxReportReturnOrderList(Request $request)
    {
        $vendor_id = Session::get('manager_vendor');
        $outlet_id = Session::get('manager_outlet');
        
        $post_data = $request->all();
        $orders    = DB::table('return_orders')
                        ->leftJoin('orders','orders.id','=','return_orders.order_id')
                        ->selectRaw('COUNT(return_orders.order_id) AS return_orders_count')
                        ->where('orders.vendor_id',$vendor_id)
                        ->where('orders.outlet_id',$outlet_id);
        return Datatables::of($orders)->addColumn('date_start', function ($orders) {
            $data = date("M-d-Y, l h:i:a", strtotime($orders->date_start));
            return $data;
        })
        ->addColumn('date_end', function ($orders) {
            $data = date("M-d-Y, l h:i:a", strtotime($orders->date_end));
            return $data;
        })
        ->filter(function ($query) use ($request){
            $condition = '1=1';
            if ($request->has('from') && $request->has('to'))
            {
                $from = date('Y-m-d H:i:s', strtotime($request->get('from')));
                $to   = date('Y-m-d H:i:s', strtotime($request->get('to')));
                $condition1 = $condition." and return_orders.created_at BETWEEN '".$from."'::timestamp and '".$to."'::timestamp";
                $query->whereRaw($condition1);
            }
            if ($request->has('order_status') != '')
            {
                $order_status = Input::get('order_status');
                $condition2   = $condition." and return_orders.return_reason = ".$order_status;
                $query->whereRaw($condition2);
            }
            if ($request->has('group_by'))
            {
                $group_by = ($request->get('group_by') != '')?$request->get('group_by'):1;
                if($group_by == 1)
                    $start_date = " date_trunc('day', return_orders.created_at) AS date_start, ";
                else if($group_by == 2)
                    $start_date = " date_trunc('week', return_orders.created_at) AS date_start, ";
                else if($group_by == 3)
                    $start_date = " date_trunc('month', return_orders.created_at) AS date_start, ";
                else if($group_by == 4)
                    $start_date = " date_trunc('year', return_orders.created_at) AS date_start, ";
                $query->selectRaw($start_date.' MAX(return_orders.created_at) AS date_end')->groupBy('date_start');
            }
        })
        ->make(true);
    }
    /* Reports product list */
    public function product()
    {
        if (!Session::get('manager_id')) {
            return redirect()->guest('managers/login');
        } else {
            SEOMeta::setTitle('Product Reports - '.$this->site_name);
            SEOMeta::setDescription('Product Reports - '.$this->site_name);
            
            $order_status = DB::table('order_status')->select('id','name')->orderBy('name', 'asc')->get();
            return view('managers.reports.products.list')->with('order_status', $order_status);
        }
    }
    public function anyAjaxReportProdcutList(Request $request)
    {
        $vendor_id = Session::get('manager_vendor');
        $outlet_id = Session::get('manager_outlet');
        
        $query = '"products_infos"."lang_id" = (case when (select count(products_infos.id) as totalcount from products_infos where products_infos.lang_id = '.getAdminCurrentLang().' and products.id = products_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $query1  = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = '.getAdminCurrentLang().' and vendors.id = vendors_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $post_data = $request->all();
        $products   = DB::table('orders')
                        ->select(DB::raw('COUNT(orders.id) AS orders,SUM((SELECT SUM(orders_info.item_unit) FROM orders_info WHERE orders_info.order_id = orders.id GROUP BY orders_info.order_id)) AS quantity_count,SUM(orders.total_amount) AS total,orders_info.item_id'),'products_infos.product_name','vendors_infos.vendor_name')
                        ->join('orders_info','orders.id','=','orders_info.order_id')
                        ->join('products','products.id','=','orders_info.item_id')
                        ->join('products_infos','products_infos.id','=','products.id')
                        ->join('vendors','vendors.id','=','products.vendor_id')
                        ->join('vendors_infos','vendors_infos.id','=','vendors.id')
                        ->whereRaw($query)
                        ->whereRaw($query1)
                        ->where('orders.vendor_id',$vendor_id)
                        ->where('orders.outlet_id',$outlet_id)
                        ->groupBy('orders_info.item_id','products_infos.product_name','vendors_infos.vendor_name');
        return Datatables::of($products)->addColumn('total', function ($products) {
            if(getCurrencyPosition()->currency_side == 1)
            {
                return getCurrency().$products->total;
            }
            else {
                return $products->total.getCurrency();
            }
        })
        ->addColumn('product_name', function ($products) {
            //$product_details = get_admin_product_details($products->item_id);
            return isset($products->product_name)?ucfirst($products->product_name):'-';
        })
        ->addColumn('vendor_name', function ($products) {
            //$vendor_details = get_admin_vendor_details($products->item_id);
            return isset($products->vendor_name)?ucfirst($products->vendor_name):'-';
        })
        ->filter(function ($query) use ($request){
            $condition = '1=1';
            if ($request->has('from') && $request->has('to'))
            {
                $from = date('Y-m-d H:i:s', strtotime($request->get('from')));
                $to   = date('Y-m-d H:i:s', strtotime($request->get('to')));
                $condition1 = $condition." and orders.created_date BETWEEN '".$from."'::timestamp and '".$to."'::timestamp";
                $query->whereRaw($condition1);
            }
            if ($request->has('order_status') != '')
            {
                $order_status = Input::get('order_status');
                $condition2   = $condition." and orders.order_status = ".$order_status;
                $query->whereRaw($condition2);
            }
        })
                 ->rawColumns(['vendor_name','product_name','total'])

        ->make(true);
    }
}
