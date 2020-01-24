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
use Illuminate\Support\Facades\Text;
use Illuminate\Support\Facades\Input;
use Yajra\Datatables\Datatables;
use DB;
use Session;
use Closure;
use Image;
use MetaTag;
use Mail;
use File;
use SEO;
use SEOMeta;
use OpenGraph;
use Twitter;
use App;
use URL;
use App\Model\notifications;
use App\Model\settings;
use App\Model\drivers;
use App\Model\salesperson;

class VendorsNotification extends Controller
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
        SEOMeta::setTitle($this->site_name);
        SEOMeta::setDescription($this->site_name);
        SEOMeta::addKeyword($this->site_name);
        OpenGraph::setTitle($this->site_name);
        OpenGraph::setDescription($this->site_name);
        OpenGraph::setUrl($this->site_name);
        Twitter::setTitle($this->site_name);
        Twitter::setSite('@'.$this->site_name);
        App::setLocale('en');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Session::get('vendor_id'))
        {
            return redirect()->guest('vendors/login');
        }
        else {
            return view('vendors.notification.list');
        }
    }
    /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function anyAjaxStoreNotificationList()
    {
        $vendor_id    = Session::get('vendor_id');
        $notification = DB::table('notifications')
                            ->select('notifications.id as notification_id','notifications.message','notifications.read_status','notifications.created_date','users.name as user_name')
                            ->join('users','users.id','=','notifications.customer_id')
                            ->orderBy('notifications.created_date', 'desc')
                            ->where('vendor_id','=',$vendor_id);
        return Datatables::of($notification)->addColumn('action', function ($notification) {
            if($notification->read_status==0):
                $data = '<div class="btn-group notify-'.$notification->notification_id.'"><a href="javascript:;" onclick="change_read_status('.$notification->notification_id.')" class="btn btn-xs btn-white" title="'.trans("messages.Change to option read").'"><i class="fa fa-eye"></i>&nbsp;'.trans("messages.Change Status to read").'</a>';
            elseif($notification->read_status==1):
                $data = '-';
            endif;
            return $data;
        })
        ->addColumn('read_status', function ($notification) {
            if($notification->read_status==0):
                $data = '<span  class="label label-danger n_status_'.$notification->notification_id.'">'.trans("messages.Not Read").'</span>';
            elseif($notification->read_status==1):
                $data = '<span  class="label label-success">'.trans("messages.Read").'</span>';
            endif;
            return $data;
        })
        ->addColumn('message', function ($notification) {
            $data = '<strong>'.$notification->user_name.' - '.$notification->message.'</strong>';
            return $data;
        })
        ->rawColumns(['message','read_status','action'])

        ->make(true);
    }
    public function notifications_read(Request $request)
    {
        if($request->ajax())
        {
            $vendor_id = Session::get('vendor_id');
            $c_id      = $request->input('cid');
            $res       = DB::table('notifications')->where('id', $c_id)->update(['read_status' => 1,'modified_date'=>date('Y-m-d H:i:s')]);
            $notifications = DB::table('notifications')
                                ->select('notifications.id','notifications.order_id','notifications.message','notifications.created_date','notifications.read_status','users.name','users.image')
                                ->leftJoin('users','users.id','=','notifications.customer_id')
                                ->where('read_status', 0)
                                ->where('vendor_id', $vendor_id)
                                ->orderBy('created_date', 'desc')->get();
            $count = count($notifications);
            $data  = ($res==true)?1:0;
            return response()->json([
                'data' => $data,'count' => $count,'vid'=>$vendor_id
            ]);
        }
    }

    /* Vendors drivers*/

    public function vendordrivers()
    {

        $id=Session::get('vendor_id');

        //print_r($id);exit();
        if (!$id){
            return redirect()->guest('vendors/login');
        }else{   
            return view('vendors.drivers.list');
        }
    }

    public function anyAjaxvendordriverlist()
    {
        $id=Session::get('vendor_id');
        $drivers = DB::table('drivers')
                    /*->join('salesperson','salesperson.vendor_driver','=','drivers.vendor_driver')*/
                    ->select('drivers.id', 'drivers.social_title', 'first_name', 'drivers.last_name', 'drivers.email', 'drivers.active_status', 'drivers.created_date', 'drivers.modified_date', 'drivers.is_verified', 'drivers.vendor_driver')
                    ->where('drivers.vendor_driver','=',$id)

                    ->orderBy('drivers.id', 'desc');

        return Datatables::of($drivers)->addColumn('action', function ($drivers) {

           // if(!hasTask('vendors/driver_edit'))
            //{               
                //echo "<pre>";print_r($drivers);exit();
                $html='<div class="btn-group">
                    <a href="'.URL::to("vendors/driver_edit/".$drivers->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("vendors/drivers/view/".$drivers->id).'" class="view-'.$drivers->id.'" title="'.trans("messages.View").'"><i class="fa fa-file-text-o"></i>&nbsp;&nbsp;'.@trans("messages.View").'</a></li>
                        <li><a href="'.URL::to("vendors/drivers/delete/".$drivers->id).'" class="delete-'.$drivers->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
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
           // }                                   
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
        ->rawColumns(['active_status','action','is_verified'])

        ->make(true);
    } 

    public function driver_create()
    {
        $id=Session::get('vendor_id');
        //print_r($id);exit();
        if (!$id){
            return redirect()->guest('vendors/login');
        }else{   
            $settings = Settings::find(1);
            SEOMeta::setTitle('Create Driver - '.$this->site_name);
            SEOMeta::setDescription('Create Driver - '.$this->site_name);
            return view('vendors.drivers.create')->with('settings', $settings);
        }
    }

    public function vendor_store(Request $data)
    {
        if(hasTask('create_vendordriver'))
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

                //$saveDriver=isset($_POST['saveDriver'])? $_POST['saveDriver']:0;
                //$saveSalesPerson=isset($_POST['saveSalesPerson'])? $_POST['saveSalesPerson']:0;
                // echo "<pre>";print_r($saveDriver);exit();

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
    }
    
    public function driver_save_after($object,$post)
    {
        $customer = $object->getAttributes();
        //echo"<pre>";print_r($post);exit();
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
                $customer['name'] = ucfirst($post['first_name']);
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

    public function driver_edit($id)
    {
        $vendorid=Session::get('vendor_id');
        if(!$vendorid){
            return redirect()->guest('vendors/login');
        }
        else {
            if(hasTask('vendors/driver_edit')){
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
    }
    public function driver_update(Request $data, $id)
    {
        //print_r("sdfgdsf");exit;
        if(hasTask('vendors/driver_update'))
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
            //'mobile'        => 'regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
            'mobile'        => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
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
    }

    public function delete($id)
    {
        if(hasTask('vendors/drivers/delete'))
        {
            return view('errors.404');
        }
        $data = Drivers::find($id);
        if(!count($data))
        {
                Session::flash('message', 'Invalid Driver Details'); 
                return Redirect::to('vendors/drivers');
        }
        $drivers = DB::select('select COUNT(driver_orders.driver_id) from  driver_orders where driver_orders.driver_id = '.$id);
        if($drivers[0]->count > 0){
            Session::flash('message', trans('messages.This driver mapped with driver orders so cannot be delete.'));
            return Redirect::to('vendors/drivers');
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
            return Redirect::to('vendors/drivers');
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
        if(hasTask('vendors/drivers/view')){
            return view('errors.404');
        }
        //Get driver details
        $drivers = Drivers::find($id);
        if(!count($drivers))
        {
            Session::flash('message', 'Invalid Driver Details'); 
            return Redirect::to('vendors/drivers');
        }

        SEOMeta::setTitle('View Driver - '.$this->site_name);
        SEOMeta::setDescription('View Driver - '.$this->site_name);

        return view('vendors.drivers.show')->with('data', $drivers);
    }



    /* Vendors Salesperson */

    public function anyAjaxvendorsalespersonlist()
    {
        $id=Session::get('vendor_id');
        //print_r($id);exit();
        $salesperson = DB::table('salesperson')
                   
                    ->select('salesperson.id', 'name as first_name', 'salesperson.email', 'salesperson.active_status', 'salesperson.created_date', 'salesperson.modified_date', 'salesperson.is_verified', 'salesperson.vendor_driver')
                    ->where('salesperson.vendor_driver','=',$id)

                    ->orderBy('salesperson.id', 'desc');

        return Datatables::of($salesperson)->addColumn('action', function ($salesperson) {

           // if(!hasTask('vendors/driver_edit'))
            //{               
                //echo "<pre>";print_r($salesperson);exit();
                $html='<div class="btn-group">
                    <a href="'.URL::to("vendors/salesperson_edit/".$salesperson->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("vendors/salesperson/view/".$salesperson->id).'" class="view-'.$salesperson->id.'" title="'.trans("messages.View").'"><i class="fa fa-file-text-o"></i>&nbsp;&nbsp;'.@trans("messages.View").'</a></li>
                        <li><a href="'.URL::to("vendors/salesperson/delete/".$salesperson->id).'" class="delete-'.$salesperson->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $( document ).ready(function() {
                        $(".delete-'.$salesperson->id.'").on("click", function(){
                            return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });
                    });
                </script>';   

                return $html;
           // }                                   
        })
        ->addColumn('active_status', function ($salesperson) {
            if($salesperson->active_status == 0):
                $data = '<span class="label label-warning">'.trans("messages.Inactive").'</span>';
            elseif($salesperson->active_status == 1):
                $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
            else:
                $data = '<span class="label label-danger">'.trans("messages.Delete").'</span>';
            endif;
            return $data;
        })
        ->addColumn('is_verified', function ($salesperson) {
            if($salesperson->is_verified == 0):
                $data = '<span class="label label-warning">'.trans("messages.Disabled").'</span>';
            elseif($salesperson->is_verified == 1):
                $data = '<span class="label label-success">'.trans("messages.Enabled").'</span>';
            endif;
            return $data;
        })
        //->editColumn('first_name', '{!! $social_title.ucfirst($first_name)." ".$last_name !!}')
               ->rawColumns(['active_status','active_status','action'])

        ->make(true);
    }

    public function vendorSalesperson()
    {

        $id=Session::get('vendor_id');

        //print_r($id);exit();
        if (!$id){
            return redirect()->guest('vendors/login');
        }else{   
            return view('vendors.salesperson.list');
        }
    }


    public function salesperson_create()
    {
        $id=Session::get('vendor_id');
        //print_r($id);exit();
        if (!$id){
            return redirect()->guest('vendors/login');
        }else{   

           // print_r("expression");exit();
            $settings = Settings::find(1);
            SEOMeta::setTitle('Create Salesperson - '.$this->site_name);
            SEOMeta::setDescription('Create Salesperson - '.$this->site_name);
            return view('vendors.salesperson.create')->with('settings', $settings);
        }
    }


    public function vendor_salespersonStore(Request $data)
    {
        if(hasTask('create_vendorSalesperson'))
        {
            return view('errors.404');
        }

        $validation = Validator::make($data->all(), array(
           // 'social_title'  => 'required|numeric',
            'first_name'    => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:3|max:32',
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


                $drivers      = new Salesperson;
                $driver_token = sha1(uniqid(Text::random('alnum', 32), TRUE));
                if(!$drivers->driver_token)
                {
                    $drivers->driver_token = $driver_token;
                }
                $drivers->name    = $_POST['first_name'];
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
                // print_r("expression");exit();

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
                Session::flash('message', trans('messages.SalesPerson has been created successfully'));
                return Redirect::to('vendors/salesperson');

                 }
    }

    public function salesperson_edit($id)
    {
        $vendorid=Session::get('vendor_id');
        if(!$vendorid){
            return redirect()->guest('vendors/login');
        }
        else {
            if(hasTask('vendors/salesperson_edit')){
                return view('errors.404');
            }
            //Get driver details
            $salesperson = Salesperson::find($id);

            if(!count($salesperson))
            {
                Session::flash('message', 'Invalid Driver Details'); 
                return Redirect::to('vendors/salesperson');
            }
            $settings = Settings::find(1);
            SEOMeta::setTitle('Edit Salesperson - '.$this->site_name);
            SEOMeta::setDescription('Edit Salesperson - '.$this->site_name);
            return view('vendors.salesperson.edit')->with('settings', $settings)->with('data', $salesperson);
        }
    }
  

    public function salesperson_updates(Request $data, $id)
    {
        // print_r("djhfbvdjb");exit();
        if(hasTask('vendors/salesperson'))
        {
            return view('errors.404');
        }
        $validation = Validator::make($data->all(), array(
            'first_name'    => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:3|max:32',
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
            $drivers      = Salesperson::find($id);
           // print_r($drivers);exit;

            $drivers->name    = $_POST['first_name'];
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
            Session::flash('message', trans('messages.Salesperson has been updated successfully'));
            return Redirect::to('vendors/salesperson');
        }
    }

    public function deleteSalesperson($id)
    {
        if(hasTask('vendors/salesperson/delete'))
        {
            return view('errors.404');
        }
        $data = Salesperson::find($id);
        if(!count($data))
        {
                Session::flash('message', 'Invalid Driver Details'); 
                return Redirect::to('vendors/salesperson');
        }
        $salesperson = DB::select('select COUNT(salesperson_orders.salesperson_id) from  salesperson_orders where salesperson_orders.salesperson_id = '.$id);
        if($salesperson[0]->count > 0){
            Session::flash('message', trans('messages.This salesperson mapped with salesperson orders so cannot be delete.'));
            return Redirect::to('vendors/salesperson');
        }
        else{
             
            if(file_exists(base_path().'/public/assets/admin/base/images/drivers/thumb/'.$data->profile_image) && $data->profile_image != '')
            {
                unlink(base_path().'/public/assets/admin/base/images/drivers/thumb/'.$data->profile_image);
            }
            if(file_exists(base_path().'/public/assets/admin/base/images/drivers/'.$data->profile_image) && $data->profile_image != '')
            {
                unlink(base_path().'/public/assets/admin/base/images/drivers/'.$data->profile_image);
            }
            $data->delete();
            Session::flash('message', trans('messages.Salesperson has been deleted successfully'));
            return Redirect::to('vendors/salesperson');
        }
    }
    /**
     * Display the specified driver.
     *
     * @param  int  $id
     * @return Response
     */
    public function showSalesperson($id)
    {
        if(hasTask('vendors/salesperson/view')){
            return view('errors.404');
        }
        //Get driver details
        $salesperson = Salesperson::find($id);
        if(!count($salesperson))
        {
            Session::flash('message', 'Invalid Driver Details'); 
            return Redirect::to('vendors/salesperson');
        }

        SEOMeta::setTitle('View salesperson - '.$this->site_name);
        SEOMeta::setDescription('View salesperson - '.$this->site_name);

        return view('vendors.salesperson.show')->with('data', $salesperson);
    }


    public function getVendorCityData(Request $request)
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

}
