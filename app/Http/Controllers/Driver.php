<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Model\drivers;
use App\Model\driver_orders;
use App\Model\driver_track_location;
use App\Model\settings;
use Session;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Image;
use MetaTag;
use Mail;
use File;
use SEO;
use SEOMeta;
use OpenGraph;
use Twitter;
use App;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\Input;
use Yajra\Datatables\Datatables;
use URL;
use Illuminate\Support\Facades\Text;
use App\Model\driver_settings;

class Driver extends Controller
{
    const DRIVER_SIGNUP_EMAIL_TEMPLATE  = 9;
    const DRIVER_WELCOME_EMAIL_TEMPLATE = 10;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->site_name = isset(getAppConfig()->site_name)?ucfirst(getAppConfig()->site_name):'';
        $this->middleware('auth');
        SEOMeta::addKeyword($this->site_name);
        OpenGraph::setTitle($this->site_name);
        OpenGraph::setDescription($this->site_name);
        OpenGraph::setUrl($this->site_name);
        Twitter::setTitle($this->site_name);
        Twitter::setSite('@'.$this->site_name);
        App::setLocale('en');
    }
    /**
     * Show the application drivers list.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('admin/drivers')){
                return view('errors.404');
            }
            SEOMeta::setTitle('Manage Drivers - '.$this->site_name);
            SEOMeta::setDescription('Manage Drivers - '.$this->site_name);
            return view('admin.drivers.list');
        }
    }
    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxdriverlist()
    {
        //print_r("hai");exit();
        $drivers = DB::table('drivers')
                    ->select('drivers.id', 'drivers.social_title', 'first_name', 'drivers.last_name', 'drivers.email', 'drivers.active_status', 'drivers.created_date', 'drivers.modified_date', 'drivers.is_verified')
                    ->orderBy('drivers.id', 'desc');
        return Datatables::of($drivers)->addColumn('action', function ($drivers) {
            if(hasTask('admin/drivers/edit'))
            {
                $html='<div class="btn-group">
                    <a href="'.URL::to("admin/drivers/edit/".$drivers->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/drivers/view/".$drivers->id).'" class="view-'.$drivers->id.'" title="'.trans("messages.View").'"><i class="fa fa-file-text-o"></i>&nbsp;&nbsp;'.@trans("messages.View").'</a></li>
                        <li><a href="'.URL::to("admin/drivers/delete/".$drivers->id).'" class="delete-'.$drivers->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $( document ).ready(function() {
                        $(".delete-'.$drivers->id.'").on("click", function(){
                            return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });
                    });
                </script>';
                return $html;
            }
        })
        ->addColumn('active_status', function ($drivers) {
            if($drivers->active_status == 0):
                $data = '<span class="label label-warning">'.trans("messages.Inactive").'</span>';
            elseif($drivers->active_status == 1):
                $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
            else:
                $data = '<span class="label label-danger">'.trans("messages.Delete").'</span>';
            endif;
            return $data;
        })
        ->addColumn('is_verified', function ($drivers) {
            if($drivers->is_verified == 0):
                $data = '<span class="label label-warning">'.trans("messages.Disabled").'</span>';
            elseif($drivers->is_verified == 1):
                $data = '<span class="label label-success">'.trans("messages.Enabled").'</span>';
            endif;
            return $data;
        })
            ->rawColumns(['active_status','is_verified','action'])
        //->editColumn('first_name', '{!! $social_title.ucfirst($first_name)." ".$last_name !!}')
        ->make(true);
    }
    /**
     * Create the specified driver in view.
     *
     * @param  int  $id
     * @return Response
     */
    public function create()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else{
            if(!hasTask('admin/drivers/create')){
                return view('errors.404');
            }
            $settings = Settings::find(1);
            SEOMeta::setTitle('Create Driver - '.$this->site_name);
            SEOMeta::setDescription('Create Driver - '.$this->site_name);
            return view('admin.drivers.create')->with('settings', $settings);
        }
    }
    /**
     * Update the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function store(Request $data)
    {
        if(!hasTask('create_driver'))
        {
            return view('errors.404');
        }
        $validation = Validator::make($data->all(), array(
           // 'social_title'  => 'required|numeric',
            'first_name'    => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:3|max:32',
            'last_name'     => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:3|max:32',
            'email'         => 'required|email|unique:drivers,email',
            'user_password' => 'required|min:5|max:32',
            'gender'        => 'required',
            'address'       => 'required',
            'date_of_birth' => 'date',
            'image'         => 'mimes:png,jpeg,bmp,max:10000',
            //'mobile'        => 'regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
            'mobile'        => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',

        ));
        // process the validation
        if ($validation->fails())
        {
            return Redirect::back()->withErrors($validation)->withInput();
        }
        else {
           // echo"<pre>";print_r($_POST);exit;
            // store datas in to database
            $drivers      = new Drivers;
            $driver_token = sha1(uniqid(Text::random('alnum', 32), TRUE));
            if(!$drivers->driver_token)
            {
                $drivers->driver_token = $driver_token;
            }
           //$drivers->social_title = $_POST['social_title'];
            $drivers->first_name    = $_POST['first_name'];
            $drivers->last_name     = $_POST['last_name'];
            $drivers->email         = $_POST['email'];
            $drivers->hash_password = md5($_POST['user_password']);
            $drivers->mobile_number = $_POST['mobile'];
            if($_POST['date_of_birth'] != '')
                $drivers->date_of_birth = $_POST['date_of_birth'];
            $drivers->gender        = $_POST['gender'];
            $drivers->address       = $_POST['address'];
            $drivers->latitude = isset($_POST['latitude'])?$_POST['latitude']:0;
            $drivers->longitude = isset($_POST['longitude'])?$_POST['longitude']:0;
            if(isset($_POST['country']) && $_POST['country']!='')
            {
                $drivers->country_id = $_POST['country'];
            }
            if(isset($_POST['city']) && $_POST['city']!='')
            {
                $drivers->city_id = $_POST['city'];
            }
            $drivers->active_status     = isset($_POST['active_status']);
            $drivers->is_verified       = isset($_POST['is_verified']);
            //$drivers->ip_address      = Request::ip();
            $drivers->created_date      = date("Y-m-d H:i:s");
            $drivers->modified_date     = date("Y-m-d H:i:s");
            $drivers->driver_created_by = 1;
            $verification_key           = Text::random('alnum',12);
            $drivers->verification_key  = $verification_key;
            $drivers->vendor_driver  = isset($_POST['vendor'])?$_POST['vendor']:0;
            $drivers->save();
            $this->driver_save_after($drivers,$_POST);
            if(isset($_FILES['image']['name']) && $_FILES['image']['name']!='')
            {
                $destinationPath = base_path().'/public/assets/admin/base/images/drivers/'; // upload path
                $imageName = $drivers->id.'.'.$data->file('image')->getClientOriginalExtension();
                $data->file('image')->move($destinationPath, $imageName);
                $destinationPath1 = url('/assets/admin/base/images/drivers/'.$imageName);
                Image::make( $destinationPath1 )->fit(75, 75)->save(base_path().'/public/assets/admin/base/images/drivers/thumb/'.$imageName)->destroy();
                $drivers->profile_image = $imageName;
                $drivers->save();
            }
            // redirect
            Session::flash('message', trans('messages.Driver has been created successfully'));
            return Redirect::to('admin/drivers');
        }
    }
    
    public function driver_save_after($object,$post)
    {
        $customer = $object->getAttributes();
        if($customer['is_verified'])
        {
            $template = DB::table('email_templates')
                        ->select('from_email', 'from', 'subject', 'template_id','content')
                        ->where('template_id','=',self::DRIVER_WELCOME_EMAIL_TEMPLATE)
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
                $customer['name'] = ucfirst($customer['first_name']);
                $customer['password'] = $post['user_password'];
                $customer['mobile'] = $post['mobile'];
                $content = array("customer" => $customer);
                $email   = smtp($from,$from_name,$customer['email'],$subject,$content,$template);
            }
        }
        else {
            $template = DB::table('email_templates')
                        ->select('from_email', 'from', 'subject', 'template_id','content')
                        ->where('template_id','=',self::DRIVER_SIGNUP_EMAIL_TEMPLATE)
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
                $url1 ='<a href="'.url('/').'/drivers/confirmation?key='.$customer['verification_key'].'&email='.$customer['email'].'&password='.$post['user_password'].'"> This Confirmation Link </a>';
                $customer['name'] = ucfirst($customer['first_name']);
                $content = array("customer" => $customer,"confirmation_link" => $url1);
				$email   = smtp($from,$from_name,$customer['email'],$subject,$content,$template);
            }
        }
    }
    /**
     * Edit the specified driver in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('admin/drivers/edit')){
                return view('errors.404');
            }
            //Get driver details
            $drivers = Drivers::find($id);
            if(!count($drivers))
            {
                Session::flash('message', 'Invalid Driver Details'); 
                return Redirect::to('admin/drivers');
            }
            $settings = Settings::find(1);
            SEOMeta::setTitle('Edit Driver - '.$this->site_name);
            SEOMeta::setDescription('Edit Driver - '.$this->site_name);
            return view('admin.drivers.edit')->with('settings', $settings)->with('data', $drivers);
        }
    }
    /**
     * Update the specified driver in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $data, $id)
    {
        if(!hasTask('admin/drivers/update'))
        {
            return view('errors.404');
        }
        $validation = Validator::make($data->all(), array(
            //'social_title'=> 'required',
            'first_name'    => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:3|max:32',
            'last_name'     => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:3|max:32',
            'user_password' => 'min:5|max:32',
            'gender'        => 'required',
            'address'       => 'required',
            'date_of_birth' => 'date',
            'image'         => 'mimes:png,jpeg,bmp,max:10000',
            'mobile'        => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            //'mobile'        => 'regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
        ));
        // process the validation
        if ($validation->fails())
        {
            return Redirect::back()->withErrors($validation)->withInput();
        }
        else { 
            $vendor = !empty($_POST['vendor'])?$_POST['vendor']:0;
       // echo"<pre>";print_r($vendor);exit;
            // store datas in to database
            $drivers      = Drivers::find($id);
            $drivers->first_name    = $_POST['first_name'];
            $drivers->last_name     = $_POST['last_name'];
            $drivers->mobile_number = $_POST['mobile'];
            $drivers->date_of_birth = $_POST['date_of_birth'];
            $drivers->gender        = $_POST['gender'];
            $drivers->address       = $_POST['address'];
            $drivers->latitude = isset($_POST['latitude'])?$_POST['latitude']:0;
            $drivers->longitude = isset($_POST['longitude'])?$_POST['longitude']:0;
            if(isset($_POST['country']) && $_POST['country']!='')
            if(isset($_POST['country']) && $_POST['country']!='')
            {
                $drivers->country_id = $_POST['country'];
            }
            if(isset($_POST['city']) && $_POST['city']!='')
            {
                $drivers->city_id = $_POST['city'];
            }
            if(isset($_POST['user_password']) && $_POST['user_password']!='')
            {
                $drivers->hash_password = md5($_POST['user_password']);
            }
            $drivers->active_status     = isset($_POST['active_status']);
            $drivers->is_verified       = isset($_POST['is_verified']);
            //$drivers->ip_address      = Request::ip();
            $drivers->modified_date     = date("Y-m-d H:i:s");
            $drivers->driver_created_by = 1;
            $drivers->vendor_driver  = isset($vendor)?$vendor:0;

            $drivers->save();

            if(isset($_FILES['image']['name']) && $_FILES['image']['name']!='')
            {
                $destinationPath = base_path().'/public/assets/admin/base/images/drivers/'; // upload path
                $imageName = $drivers->id.'.'.$data->file('image')->getClientOriginalExtension();
                $data->file('image')->move($destinationPath, $imageName);
                $destinationPath1 = url('/assets/admin/base/images/drivers/'.$imageName);
                Image::make( $destinationPath1 )->fit(75, 75)->save(base_path().'/public/assets/admin/base/images/drivers/thumb/'.$imageName)->destroy();
                $drivers->profile_image = $imageName;
                $drivers->save();
            } 
            // redirect
            Session::flash('message', trans('messages.Driver has been updated successfully'));
            return Redirect::to('admin/drivers');
        }
    }
    /**
     * Delete the specified driver in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function delete($id)
    {
        if(!hasTask('admin/drivers/delete'))
        {
            return view('errors.404');
        }
        $data = Drivers::find($id);
		if(!count($data))
		{
				Session::flash('message', 'Invalid Driver Details'); 
				return Redirect::to('admin/drivers');
		}
		$drivers = DB::select('select COUNT(driver_orders.driver_id) from  driver_orders where driver_orders.driver_id = '.$id);
		if($drivers[0]->count > 0){
			Session::flash('message', trans('messages.This driver mapped with driver orders so cannot be delete.'));
			return Redirect::to('vendor/drivers');
		}
		else{
			 $affected = DB::table('driver_track_location')->where('driver_id', '=', $id)->delete();
			if(file_exists(base_path().'/public/assets/admin/base/images/drivers/thumb/'.$data->profile_image) && $data->profile_image != '')
			{
				unlink(base_path().'/public/assets/admin/base/images/drivers/thumb/'.$data->profile_image);
			}
			if(file_exists(base_path().'/public/assets/admin/base/images/drivers/'.$data->profile_image) && $data->profile_image != '')
			{
				unlink(base_path().'/public/assets/admin/base/images/drivers/'.$data->profile_image);
			}
			$data->delete();
			Session::flash('message', trans('messages.Driver has been deleted successfully'));
			return Redirect::to('admin/drivers');
        }
    }
    /**
     * Display the specified driver.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        if(!hasTask('admin/drivers/view')){
            return view('errors.404');
        }
        //Get driver details
        $drivers = Drivers::find($id);
        if(!count($drivers))
        {
            Session::flash('message', 'Invalid Driver Details'); 
            return Redirect::to('admin/drivers');
        }
        SEOMeta::setTitle('View Driver - '.$this->site_name);
        SEOMeta::setDescription('View Driver - '.$this->site_name);
        return view('admin.drivers.show')->with('data', $drivers);
    }
    //Get city list for ajax request
    public function getCityData(Request $request)
    {
        if($request->ajax())
        {
            $country_id = $request->input('cid');
            $city_data  = getCityList($country_id);
            return response()->json([
                'data' => $city_data
            ]);
        }
    }
    /**
     * Show the application drivers location list.
     *
     * @return \Illuminate\Http\Response
     */
    public function driver_location_list()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else 
		{
            if(!hasTask('admin/drivers'))
			{
                return view('errors.404');
            }
            $driver_location_list = DB::select('SELECT DISTINCT ON ("driver_track_location"."driver_id") "driver_id", "driver_track_location"."id", "driver_track_location"."latitude", "driver_track_location"."longitude","drivers"."first_name","drivers"."last_name","drivers"."driver_status" FROM  "driver_track_location" join "drivers" on "drivers"."id"="driver_track_location"."driver_id" order by "driver_track_location"."driver_id","driver_track_location"."id" desc');
			foreach($driver_location_list as $key=>$drivers)
			{
				
				$driver_location_list[$key]->orders = DB::select('SELECT "orders"."id","orders"."total_amount","users"."name" 
																		FROM "driver_orders"
																		join "drivers" on "drivers"."id"="driver_orders"."driver_id" 
																		join "orders" on "orders"."id"="driver_orders"."order_id" 
																		join "users" on "users"."id"="orders"."customer_id" 
																		WHERE "drivers"."id" = '.$drivers->driver_id.'
																		order by "orders"."id" desc limit 2');
			}
            SEOMeta::setTitle('Manage Drivers Location - '.$this->site_name);
            SEOMeta::setDescription('Manage Drivers Location - '.$this->site_name);
            return view('admin.drivers.location_list')->with('driver_location_list',$driver_location_list);
        }
    }

    public function driver_settings()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('admin/driver-settings')){
                return view('errors.404');
            }
            //Get driver details
            $drivers_settings = Driver_settings::find(1);            
            if(!count($drivers_settings))
            {
                Session::flash('message', 'Invalid Driver Details'); 
                return Redirect::to('admin/drivers');
            }
            SEOMeta::setTitle('Edit Driver - '.$this->site_name);
            SEOMeta::setDescription('Edit Driver - '.$this->site_name);
            return view('admin.drivers.settings')->with('drivers_settings', $drivers_settings);
        }
    }

        /**
     * Update the specified driver in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function driver_settings_update(Request $data, $id)
    {
        if(!hasTask('admin/drivers/update'))
        {
            return view('errors.404');
        }
        $validation = Validator::make($data->all(), array(
			'order_accept_time' => 'required',
			'driver_order_total' => 'required',
        ));
        // process the validation
        if ($validation->fails())
        {
            return Redirect::back()->withErrors($validation)->withInput();
        }
        else {
            // store datas in to database
            $driver_settings      = Driver_settings::find(1);
            $driver_settings->order_accept_time    = $_POST['order_accept_time'];
            $driver_settings->driver_order_total    = $_POST['driver_order_total'];
            $driver_settings->save();	
            // redirect
            Session::flash('message', trans('messages.Driver settings has been updated successfully'));
            return Redirect::to('admin/driver-settings');
        }
    }

     /**
     * Show the application driver.
     * @return \Illuminate\Http\Response
     */



    /*public function vendordrivers()
    {
        $id=Session::get('vendor_id');
        if (!$id){
            return redirect()->guest('vendors/login');
        }else{   
            return view('vendors.drivers.list');
        }
    }*/

    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
   /* public function anyAjaxvendordriverlist()
    {
        $id=Session::get('vendor_id');
       // print_r($id);exit;
        $drivers = DB::table('drivers')
                    ->select('drivers.id', 'drivers.social_title', 'first_name', 'drivers.last_name', 'drivers.email', 'drivers.active_status', 'drivers.created_date', 'drivers.modified_date', 'drivers.is_verified', 'drivers.vendor_driver')
                    ->where('drivers.vendor_driver','=',$id)

                    ->orderBy('drivers.id', 'desc');
      // echo"<pre>"; print_r($drivers);exit;

        return Datatables::of($drivers)->addColumn('action', function ($drivers) {
            if(hasTask('vendors/driver_edit'))
            {
                $html='<div class="btn-group">
                    <a href="'.URL::to("vendors/driver_edit/".$drivers->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/drivers/view/".$drivers->id).'" class="view-'.$drivers->id.'" title="'.trans("messages.View").'"><i class="fa fa-file-text-o"></i>&nbsp;&nbsp;'.@trans("messages.View").'</a></li>
                        <li><a href="'.URL::to("admin/drivers/delete/".$drivers->id).'" class="delete-'.$drivers->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $( document ).ready(function() {
                        $(".delete-'.$drivers->id.'").on("click", function(){
                            return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });
                    });
                </script>';
                return $html;
            }
        })
        ->addColumn('active_status', function ($drivers) {
            if($drivers->active_status == 0):
                $data = '<span class="label label-warning">'.trans("messages.Inactive").'</span>';
            elseif($drivers->active_status == 1):
                $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
            else:
                $data = '<span class="label label-danger">'.trans("messages.Delete").'</span>';
            endif;
            return $data;
        })
        ->addColumn('is_verified', function ($drivers) {
            if($drivers->is_verified == 0):
                $data = '<span class="label label-warning">'.trans("messages.Disabled").'</span>';
            elseif($drivers->is_verified == 1):
                $data = '<span class="label label-success">'.trans("messages.Enabled").'</span>';
            endif;
            return $data;
        })
        //->editColumn('first_name', '{!! $social_title.ucfirst($first_name)." ".$last_name !!}')
        ->make(true);
    }*/
   /* public function driver_create()
    {
        $id=Session::get('vendor_id');
        if (!$id){
            return redirect()->guest('vendors/login');
        }else{   
            $settings = Settings::find(1);
            SEOMeta::setTitle('Create Driver - '.$this->site_name);
            SEOMeta::setDescription('Create Driver - '.$this->site_name);
            return view('vendors.drivers.create')->with('settings', $settings);
        }
    }*/
    /*public function vendor_store(Request $data)
    {
        if(!hasTask('create_vendordriver'))
        {
            return view('errors.404');
        }
        $validation = Validator::make($data->all(), array(
           // 'social_title'  => 'required|numeric',
            'first_name'    => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:3|max:32',
            'last_name'     => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:3|max:32',
            'email'         => 'required|email|unique:drivers,email',
            'user_password' => 'required|min:5|max:32',
            'gender'        => 'required',
            'address'       => 'required',
            'date_of_birth' => 'date',
            'image'         => 'mimes:png,jpeg,bmp,max:10000',
            'mobile'        => 'regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
        ));
        // process the validation
        if ($validation->fails())
        {
            return Redirect::back()->withErrors($validation)->withInput();
        }
        else {
            //echo"<pre>";print_r($_POST);exit;
            // store datas in to database
            $drivers      = new Drivers;
            $driver_token = sha1(uniqid(Text::random('alnum', 32), TRUE));
            if(!$drivers->driver_token)
            {
                $drivers->driver_token = $driver_token;
            }
           //$drivers->social_title = $_POST['social_title'];
            $drivers->first_name    = $_POST['first_name'];
            $drivers->last_name     = $_POST['last_name'];
            $drivers->email         = $_POST['email'];
            $drivers->hash_password = md5($_POST['user_password']);
            $drivers->mobile_number = $_POST['mobile'];
            if($_POST['date_of_birth'] != '')
                $drivers->date_of_birth = $_POST['date_of_birth'];
            $drivers->gender        = $_POST['gender'];
            $drivers->address       = $_POST['address'];
            $drivers->latitude = isset($_POST['latitude'])?$_POST['latitude']:0;
            $drivers->longitude = isset($_POST['longitude'])?$_POST['longitude']:0;
            if(isset($_POST['country']) && $_POST['country']!='')
            {
                $drivers->country_id = $_POST['country'];
            }
            if(isset($_POST['city']) && $_POST['city']!='')
            {
                $drivers->city_id = $_POST['city'];
            }
            $drivers->active_status     = isset($_POST['active_status']);
            $drivers->is_verified       = isset($_POST['is_verified']);
            //$drivers->ip_address      = Request::ip();
            $drivers->created_date      = date("Y-m-d H:i:s");
            $drivers->modified_date     = date("Y-m-d H:i:s");
            $drivers->driver_created_by = 1;
            $verification_key           = Text::random('alnum',12);
            $drivers->verification_key  = $verification_key;
            $drivers->vendor_driver  = isset($_POST['vendor'])?$_POST['vendor']:0;
            $drivers->save();
            $this->driver_save_after($drivers,$_POST);
            if(isset($_FILES['image']['name']) && $_FILES['image']['name']!='')
            {
                $destinationPath = base_path().'/public/assets/admin/base/images/drivers/'; // upload path
                $imageName = $drivers->id.'.'.$data->file('image')->getClientOriginalExtension();
                $data->file('image')->move($destinationPath, $imageName);
                $destinationPath1 = url('/assets/admin/base/images/drivers/'.$imageName);
                Image::make( $destinationPath1 )->fit(75, 75)->save(base_path().'/public/assets/admin/base/images/drivers/thumb/'.$imageName)->destroy();
                $drivers->profile_image = $imageName;
                $drivers->save();
            }
            // redirect
            Session::flash('message', trans('messages.Driver has been created successfully'));
            return Redirect::to('vendors/drivers');
        }
    }*/

   /* public function driver_edit($id)
    {
        $vendorid=Session::get('vendor_id');
        if(!$vendorid){
            return redirect()->guest('vendors/login');
        }
        else {
            if(!hasTask('vendors/driver_edit')){
                return view('errors.404');
            }
            //Get driver details
            $drivers = Drivers::find($id);

            if(!count($drivers))
            {
                Session::flash('message', 'Invalid Driver Details'); 
                return Redirect::to('vendors/drivers');
            }
            $settings = Settings::find(1);
            SEOMeta::setTitle('Edit Driver - '.$this->site_name);
            SEOMeta::setDescription('Edit Driver - '.$this->site_name);
            return view('vendors.drivers.edit')->with('settings', $settings)->with('data', $drivers);
        }
    }*/

   /* public function driver_update(Request $data, $id)
    {
       // print_r("sdfgdsf");exit;
        if(!hasTask('vendors/driver_update'))
        {
            return view('errors.404');
        }
        $validation = Validator::make($data->all(), array(
            //'social_title'=> 'required',
            'first_name'    => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:3|max:32',
            'last_name'     => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:3|max:32',
            'user_password' => 'min:5|max:32',
            'gender'        => 'required',
            'address'       => 'required',
            'date_of_birth' => 'date',
            'image'         => 'mimes:png,jpeg,bmp,max:10000',
            'mobile'        => 'regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
        ));
        // process the validation
        if ($validation->fails())
        {
            return Redirect::back()->withErrors($validation)->withInput();
        }
        else {
            // store datas in to database
            $drivers      = Drivers::find($id);
            $drivers->first_name    = $_POST['first_name'];
            $drivers->last_name     = $_POST['last_name'];
            $drivers->mobile_number = $_POST['mobile'];
            $drivers->date_of_birth = $_POST['date_of_birth'];
            $drivers->gender        = $_POST['gender'];
            $drivers->address       = $_POST['address'];
           $drivers->latitude = isset($_POST['latitude'])?$_POST['latitude']:0;
            $drivers->longitude = isset($_POST['longitude'])?$_POST['longitude']:0;
            if(isset($_POST['country']) && $_POST['country']!='')
            if(isset($_POST['country']) && $_POST['country']!='')
            {
                $drivers->country_id = $_POST['country'];
            }
            if(isset($_POST['city']) && $_POST['city']!='')
            {
                $drivers->city_id = $_POST['city'];
            }
            if(isset($_POST['user_password']) && $_POST['user_password']!='')
            {
                $drivers->hash_password = md5($_POST['user_password']);
            }
            $drivers->active_status     = isset($_POST['active_status']);
            $drivers->is_verified       = isset($_POST['is_verified']);
            //$drivers->ip_address      = Request::ip();
            $drivers->modified_date     = date("Y-m-d H:i:s");
            $drivers->driver_created_by = 1;
            $drivers->vendor_driver  = isset($_POST['vendor'])?$_POST['vendor']:0;

            $drivers->save();
            if(isset($_FILES['image']['name']) && $_FILES['image']['name']!='')
            {
                $destinationPath = base_path().'/public/assets/admin/base/images/drivers/'; // upload path
                $imageName = $drivers->id.'.'.$data->file('image')->getClientOriginalExtension();
                $data->file('image')->move($destinationPath, $imageName);
                $destinationPath1 = url('/assets/admin/base/images/drivers/'.$imageName);
                Image::make( $destinationPath1 )->fit(75, 75)->save(base_path().'/public/assets/admin/base/images/drivers/thumb/'.$imageName)->destroy();
                $drivers->profile_image = $imageName;
                $drivers->save();
            } 
            // redirect
            Session::flash('message', trans('messages.Driver has been updated successfully'));
            return Redirect::to('vendors/drivers');
        }
    }*/

	
	/*public function get_driver_orders($driver_id,$order_status)
	{
		
	}*/
}
