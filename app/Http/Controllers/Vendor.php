<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
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
use App\Model\vendors;
use App\Model\vendors_infos;
use App\Model\users;
use App\Model\outlets;
use App\Model\outlet_infos;
use App\Model\delivery_timings;
use App\Model\opening_timings;
use App\Model\settings;
use App\Model\outlet_managers;
use Illuminate\Support\Facades\Text;
use Maatwebsite\Excel\Facades\Excel;

class Vendor extends Controller
{
    const VENDORS_REGISTER_EMAIL_TEMPLATE = 4;
    const MANAGER_WELCOME_EMAIL_TEMPLATE = 11;
    const MANAGER_SIGNUP_EMAIL_TEMPLATE = 12;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
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
     * @return \Illuminate\Http\Response
     */
    public function vendors()
    {
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('vendors/vendors')){
                return view('errors.404');
            }
            return view('admin.vendors.list');
        }
    }
    /**
     * Create the specified vendor in view.
     * @param  int  $id
     * @return Response
     */
    
    public function vendor_create()
    {
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('vendors/create_vendor')){
                return view('errors.404');
            }
            //Get countries data
            $countries = getCountryLists();
            //Get the categories data with type vendor
            $categories= getCategoryLists(2);
            return view('admin.vendors.create')->with('countries', $countries)->with('categories', $categories);
        }
    }

    public function vendor_edit($id)
    {
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{

            if(!hasTask('vendors/edit_vendor')){
                return view('errors.404');
            }
            //Get vendor details
            $vendors = Vendors::find($id);
            //echo"<pre>"; print_r($vendors);exit;
            if(!count($vendors)){
                 Session::flash('message', 'Invalid Vendor Details'); 
                 return Redirect::to('vendors/vendors');
            }
            //Get the vendors information
            $info = new Vendors_infos;
            //Get countries data
            $countries = getCountryLists();
            //Get the categories data with type vendor
            $categories= getCategoryLists(2);
            
            return view('admin.vendors.edit')->with('countries', $countries)->with('categories', $categories)->with('data', $vendors)->with('infomodel', $info);
        }
    }
    /**
     * Add the specified vendor in storage.
     * @param  int  $id
     * @return Response
     */
    public function vendor_store(Request $data)
    {
        if(!hasTask('vendor_create')){
                return view('errors.404');
        }
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation
        $fields['first_name'] = Input::get('first_name');
        $fields['last_name'] = Input::get('last_name');
        $fields['email'] = Input::get('email');
        $fields['password'] = Input::get('password');
        $fields['password_confirmation'] = Input::get('password_confirmation');
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
            'email' => 'required|email|max:255|unique:vendors,email',
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
            //'service_tax' => 'required|numeric|min:0.1|max:99.9',
            'contact_email' => 'required|email',
            'contact_address' => 'required',
            //'featured_vendor' => 'required',
            //'active_status' => 'required',
            'logo' => 'required|mimes:png,jpg,jpeg,bmp',
            'featured_image' => 'required|mimes:png,jpg,jpeg,bmp|max:2024'
        );
        $vendor_name = Input::get('vendor_name');
        foreach ($vendor_name  as $key => $value) {
            $fields['vendor_name'.$key] = $value;
            $rules['vendor_name'.'1'] = 'required|unique:vendors_infos,vendor_name';
        }
        $vendor_description = Input::get('vendor_description');
        foreach ($vendor_description  as $key => $value) {
            $fields['vendor_description'.$key] = $value;
            $rules['vendor_description'.'1'] = 'required';
        }
        $validation = Validator::make($fields, $rules);    
        // process the validation
        if ($validation->fails())
        { 
            return Redirect::back()->withErrors($validation)->withInput();
        } else {
            //Store the data here with database
            try{
                $vendor_name = $_POST['vendor_name'];
                $Vendors = new Vendors;
                $Vendors->first_name = $_POST['first_name'];
                $Vendors->last_name = $_POST['last_name'];
                $Vendors->email = $_POST['email'];
                $Vendors->hash_password = md5($_POST['password']);
                $Vendors->phone_number = $_POST['phone_number'];
                $Vendors->mobile_number = $_POST['mobile_number'];
                $Vendors->country_id = $_POST['country'];
                $Vendors->city_id = $_POST['city'];
                $Vendors->delivery_time = $_POST['delivery_time'];
                $Vendors->pickup_time = $_POST['pickup_time'];
                $Vendors->category_ids = implode(',',$_POST['category']);
                $Vendors->created_date = date('Y-m-d H:i:s');
                $Vendors->created_by = Auth::user()->id;
                $Vendors->contact_email = $_POST['contact_email'];
                $Vendors->contact_address = $_POST['contact_address'];
                $Vendors->active_status = isset($_POST['active_status'])?$_POST['active_status']:0;
                $Vendors->featured_vendor = isset($_POST['featured_vendor'])?$_POST['featured_vendor']:0;
                $Vendors->cancel_time = $_POST['cancel_time'];
                $Vendors->return_time = $_POST['return_time'];
                $Vendors->delivery_charges_fixed = $_POST['delivery_charges_fixed'];
                $Vendors->delivery_cost_variation = $_POST['delivery_cost_variation'];
                 $Vendors->service_tax = !empty($_POST['service_tax'])?$_POST['service_tax']:0;
                $Vendors->latitude = $_POST['latitude'];
                $Vendors->longitude = $_POST['longitude'];
                $Vendors->vendor_key =  strtoupper(substr($vendor_name[1],0,3));
                $Vendors->original_password = $_POST['password'];
                $Vendors->save();
                //get last insert id
                $imageName = $Vendors->id . '.' . $data->file('logo')->getClientOriginalExtension();
                $data->file('logo')->move(
                    base_path() . '/public/assets/admin/base/images/vendors/logos/', $imageName
                );
                $destinationPath1 = url('/assets/admin/base/images/vendors/logos/'.$imageName.'');
                Image::make( $destinationPath1 )->fit(203, 103)->save(base_path() .'/public/assets/admin/base/images/vendors/logos/'.$imageName)->destroy();
                $Vendors->logo_image=$imageName;
                $Vendors->save();
                $imageName = $Vendors->id . '.' . $data->file('featured_image')->getClientOriginalExtension();
                $data->file('featured_image')->move(
                    base_path() . '/public/assets/admin/base/images/vendors/', $imageName
                );
                $destinationPath2 = url('/assets/admin/base/images/vendors/'.$imageName.'');
                $size=getImageResize('VENDOR');
                Image::make( $destinationPath2 )->fit($size['LIST_WIDTH'], $size['LIST_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/vendors/list/'.$imageName)->destroy();
                
                Image::make( $destinationPath2 )->fit($size['DETAIL_WIDTH'], $size['DETAIL_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/vendors/detail/'.$imageName)->destroy();
                
                Image::make( $destinationPath2 )->fit($size['THUMB_WIDTH'], $size['THUMB_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/vendors/thumb/'.$imageName)->destroy();
                
                Image::make( $destinationPath2 )->fit($size['DETAIL_WIDTH'], $size['DETAIL_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/vendors/thumb/detail/'.$imageName)->destroy();
                $Vendors->featured_image=$imageName;
                $Vendors->save();
                $this->vendor_save_after($Vendors,$_POST,1);
                Session::flash('message', trans('messages.Vendor has been added successfully'));
            }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('vendors/vendors');
        }
    }
    
    /**
     * Update the specified vendor in storage.
     * @param  int  $id
     * @return Response
     */
    public function vendor_update(Request $data, $id)
    {
        if(!hasTask('update_vendor'))
        {
            return view('errors.404');
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
            //'mobile_number' => 'required|regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
            'mobile_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            //'phone_number' => 'required|regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
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
            //'service_tax' => 'required|numeric|min:0.1|max:99.9',
            'contact_email' => 'required|email',
            'contact_address' => 'required',
            //'featured_vendor' => 'required',
            //'active_status' => 'required',
            'logo' => 'mimes:png,jpg,jpeg,bmp|max:2024',
            'featured_image' => 'mimes:png,jpg,jpeg,bmp|max:2024'
        );
        $vendor_name = Input::get('vendor_name');
        foreach ($vendor_name  as $key => $value) {
            $fields['vendor_name'.$key] = $value;
            $rules['vendor_name'.'1'] = 'required|unique:vendors_infos,vendor_name,'.$id;
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
                $vendor_name = $_POST['vendor_name'];
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
                $Vendors->contact_email = $_POST['contact_email'];
                $Vendors->contact_address = $_POST['contact_address'];
                $Vendors->active_status = isset($_POST['active_status'])?$_POST['active_status']:0;
                $Vendors->featured_vendor = isset($_POST['featured_vendor'])?$_POST['featured_vendor']:0;
                $Vendors->cancel_time = $_POST['cancel_time'];
                $Vendors->return_time = $_POST['return_time'];
                $Vendors->delivery_charges_fixed = $_POST['delivery_charges_fixed'];
                $Vendors->delivery_cost_variation = $_POST['delivery_cost_variation'];
               
                $Vendors->service_tax = !empty($_POST['service_tax'])?$_POST['service_tax']:0;
                $Vendors->latitude = $_POST['latitude'];
                $Vendors->longitude = $_POST['longitude'];
                $Vendors->vendor_key =  strtoupper(substr($vendor_name[1],0,3));
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
                    Image::make( $destinationPath2 )->fit($size['DETAIL_WIDTH'], $size['DETAIL_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/vendors/thumb/detail/'.$imageName)->destroy();
                    $Vendors->featured_image=$imageName;
                    $Vendors->save();
                }
                $this->vendor_save_after($Vendors,$_POST);
                Session::flash('message', trans('messages.Vendor has been updated successfully'));
            }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('vendors/vendors');
        }
    }
    
    /**
     * add,edit datas  saved in main table 
     * after inserted in sub tabel.
     * @param  int  $id
     * @return Response
     */
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
                if($method==1){
                    $vendor=$object->getAttributes();
                    $password = $post['password'];
                    $template=DB::table('email_templates')
                        ->select('*')
                        ->where('template_id','=',self::VENDORS_REGISTER_EMAIL_TEMPLATE)
                        ->get();
                    if(count($template)){
                       $from = $template[0]->from_email;
                       $from_name = $template[0]->from;
                       $subject = $template[0]->subject;
                       if(!$template[0]->template_id){
                           $template = 'mail_template';
                           $from = getAppConfigEmail()->contact_email;
                           $subject = "Welcome to ".getAppConfig()->site_name;
                           $from_name = "";
                       }
                       $content = array("vendor_name" => $vendor_name[getAdminCurrentLang()],"email"=>$vendor['email'],"password"=>$password);
                       $email = smtp($from,$from_name,$vendor['email'],$subject,$content,$template);
                   }
                }
            }catch(Exception $e) {
                
                Log::Instance()->add(Log::ERROR, $e);
            }
        }
    }
   /**
     * Display the specified vendor.
     * @param  int  $id
     * @return Response
     */
    public function vendor_show($id)
    {
        if(!hasTask('vendors/vendor_details')){
                return view('errors.404');
        }
        //Get vendor details
        $vendors = Vendors::find($id);
        if(!count($vendors)){
            Session::flash('message', 'Invalid Vendor Details'); 
            return Redirect::to('vendors/vendors');    
        }
        //Get the vendors information
        $info = new Vendors_infos;
        //Get countries data
        $countries = getCountryLists();
        //Get the categories data with type vendor
        $categories= getCategoryLists(2);
        
        return view('admin.vendors.show')->with('countries', $countries)->with('categories', $categories)->with('data', $vendors)->with('infomodel', $info);
    }
   
       /**
     * Delete the specified vendor in storage.
     * @param  int  $id
     * @return Response
     */
    public function vendor_destory($id)
    {
        if(!hasTask('vendors/delete_vendor')){
                return view('errors.404');
        }
        $data = Vendors::find($id);
        if(!count($data)){
            Session::flash('message', 'Invalid Vendor Details'); 
            return Redirect::to('vendors/vendors');    
        }
        //$data->delete();
        //Update delete status while deleting
        $data->active_status = 2;
        $data->save();
        Session::flash('message', trans('messages.Vendor has been deleted successfully!'));
        return Redirect::to('vendors/vendors');
    }

    /**
     * Process datatables ajax request.
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxVendor()
    {
        $query = '"vendors_infos"."lang_id" = (case when (select count(id) as totalcount from vendors_infos where vendors_infos.lang_id = '.getAdminCurrentLang().' and vendors.id = vendors_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $vendors = Vendors::Leftjoin('vendors_infos','vendors_infos.id','=','vendors.id')
                    ->select('vendors.id', 'vendors_infos.vendor_name', 'vendors.first_name', 'vendors.email', 'vendors.phone_number', 'vendors.created_date', 'vendors.modified_date', 'vendors.active_status')
                    ->whereRaw($query)
                    ->orderBy('created_date', 'desc')
                    ->get();
        return Datatables::of($vendors)->addColumn('action', function ($vendors) {
            if(hasTask('vendors/edit_vendor'))
            {
                $html='<div class="btn-group">
                    <a href="'.URL::to("vendors/edit_vendor/".$vendors->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("vendors/vendor_details/".$vendors->id).'" class="view-'.$vendors->id.'" title="'.trans("messages.View").'"><i class="fa fa-file-text-o"></i>&nbsp;&nbsp;'.@trans("messages.View").'</a></li>
                        <li><a href="'.URL::to("vendors/delete_vendor/".$vendors->id).'" class="delete-'.$vendors->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $( document ).ready(function() {
                        $(".delete-'.$vendors->id.'").on("click", function(){
                            return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });
                    });
                </script>';
                return $html;
            }
        })
        ->addColumn('modified_date', function ($vendors) {
            $data = '-';
            if(!empty(trim($vendors->modified_date))):
                $data = trim($vendors->modified_date);
            endif;
            return $data;
        })
        ->addColumn('vendor_name', function ($vendors) {
            return wordwrap(trim(ucfirst($vendors->vendor_name)),20,'<br \>');
        })
        ->addColumn('first_name', function ($vendors) {
            return wordwrap(trim(ucfirst($vendors->first_name)),20,'<br \>');
        })
        ->addColumn('id', function ($vendors) {
			$data ="<input type='checkbox'  class='deleteRow' value='".$vendors['id']."'  /> ".$vendors['id'];
			return $data;
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
       ->rawColumns(['active_status','id','first_name','vendor_name','modified_date','action'])

        ->make(true);
    }
    /**
     * Show the application outlets.
     * @return \Illuminate\Http\Response
     */
    public function branches()
    {
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('vendors/outlets')){
                return view('errors.404');
            }
            return view('admin.outlets.list');
        }
    }
    /**
     * Create the specified outlet in view.
     * @param  int  $id
     * @return Response
     */
    
    public function branch_create()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('vendors/create_outlet'))
            {
                return view('errors.404');
            }
            //Get countries data
            $countries = getCountryLists();
             $categories= getCategoryLists(2);
            return view('admin.outlets.create')->with('countries', $countries)->with('categories', $categories);
        }
    }
    /**
     * Create the specified outlet in view.
     * @param  int  $id
     * @return Response
     */
    public function branch_edit($id)
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('vendors/edit_outlet'))
            {
                return view('errors.404');
            }
            //Get vendor details
            $data = Outlets::find($id);
            if(!count($data))
            {
                Session::flash('message', 'Invalid Outlet Details'); 
                return Redirect::to('vendors/outlets');
            }
            //Get countries data
            $countries = getCountryLists();
            $info      = new Outlet_infos;
             $categories= getCategoryLists(2);
            return view('admin.outlets.edit')->with('countries', $countries)->with('data', $data)->with('infomodel', $info)->with('categories', $categories);
        }
    }
    /**
     * Add the specified outlet in storage.
     * @param  int  $id
     * @return Response
     */
    public function branch_store(Request $data)
    {
        if(!hasTask('outlet_create'))
        {
            return view('errors.404');
        }
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation
        $fields['vendor'] = Input::get('vendor');
        $fields['category'] = Input::get('category');
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
            'vendor' => 'required',
            'contact_phone_number' => 'required|regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
            'country' => 'required',
            'city' => 'required',
            'location' => 'required',
           //'category' => 'required',
            'delivery_areas' => 'required',
            'delivery_time' => 'required|numeric|min:0',
            'pickup_time' => 'required|numeric|min:0',
            'cancel_time' => 'required|numeric|min:0',
            'return_time' => 'required|numeric|min:0',
            'delivery_charges_fixed' => 'required|numeric|min:0',
            'delivery_cost_variation' => 'required|numeric|min:0',
           'service_tax' => 'required|numeric|min:0.1|max:99.9',
            'minimum_order_amount' => 'required|numeric|min:0',
            'contact_email' => 'required|email',
            //'contact_address' => 'required',
            //'active_status' => 'required',
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
        }
        else {
            //Store the data here with database
            try{
                $Outlets = new Outlets;
                $Outlets->contact_phone = $_POST['contact_phone_number'];
                $Outlets->url_index =  str_slug($_POST['outlet_name'][1]);
                $Outlets->country_id = $_POST['country'];
                $Outlets->city_id = $_POST['city'];
                $Outlets->location_id = $_POST['location'];
                $Outlets->vendor_id = $_POST['vendor'];
               // $Outlets->category_ids = implode(',',$_POST['category']);
                $Outlets->delivery_time = $_POST['delivery_time'];
                $Outlets->pickup_time = $_POST['pickup_time'];
                $Outlets->delivery_areas = implode(',',$_POST['delivery_areas']);
                $Outlets->created_date = date('Y-m-d H:i:s');
                $Outlets->created_by = Auth::user()->id;
                $Outlets->active_status = isset($_POST['active_status'])?$_POST['active_status']:'0';
                $Outlets->contact_email = $_POST['contact_email'];
               // $Outlets->contact_address = $_POST['contact_address'];
                $Outlets->latitude = isset($_POST['latitude'])?$_POST['latitude']:0;
                $Outlets->longitude = isset($_POST['longitude'])?$_POST['longitude']:0;
                $Outlets->cancel_time = $_POST['cancel_time'];
                $Outlets->return_time = $_POST['return_time'];
                $Outlets->delivery_charges_fixed = $_POST['delivery_charges_fixed'];
                $Outlets->delivery_charges_variation = $_POST['delivery_cost_variation'];
                if(!empty($_POST['service_tax']))
					$Outlets->service_tax = $_POST['service_tax'];
			
               // $Outlets->service_tax = isset($_POST['service_tax'])?$_POST['service_tax']:'0';
                $Outlets->minimum_order_amount = $_POST['minimum_order_amount'];
                $Outlets->save();
                $last_insert_id = $Outlets->id;
                //Store the opening timing schedules here
                $opening_time = $_POST['opening_timing'];
                 $checked_day = "";
                                if ($_POST['opening_timing']) {
                                  $checked_day = "checked";
                                  // May need to be "checked='checked'" for xhtml
                                }
                        
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
            }
            catch(Exception $e) {
                Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('vendors/outlets');
        }
    }
    
    /**
     * Update the specified outlet in storage.
     * @param  int  $id
     * @return Response
     */
    public function branch_update(Request $data, $id)
    {
        if(!hasTask('update_outlet'))
        {
            return view('errors.404');
        }
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation
        $fields['vendor'] = Input::get('vendor'); 
        $fields['category'] = Input::get('category');
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
        $fields['contact_address'] = Input::get('contact_address');
        $fields['latitude'] = Input::get('latitude');
        $fields['longitude'] = Input::get('longitude');
        $fields['active_status'] = Input::get('active_status');
        $rules = array(
            'vendor' => 'required',
            // 'category' => 'required',
            'contact_phone_number' => 'required|regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
            'country' => 'required',
            'city' => 'required',
            'location' => 'required',
            'delivery_areas' => 'required',
            'delivery_time' => 'required|numeric|min:0',
            'pickup_time' => 'required|numeric|min:0',
            'cancel_time' => 'required|numeric|min:0',
            'return_time' => 'required|numeric|min:0',
            'delivery_charges_fixed' => 'required|numeric|min:0',
            'delivery_cost_variation' => 'required|numeric|min:0',
            'service_tax' => 'required|numeric|min:0.1|max:99.9',
            'minimum_order_amount' => 'required|numeric|min:0',
            'contact_email' => 'required|email',
            //'contact_address' => 'required',
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
        }
        else {
            try{
                $Outlets = Outlets::find($id); 
                $Outlets->contact_phone = $_POST['contact_phone_number'];
                $Outlets->url_index =  str_slug($_POST['outlet_name'][1]);
                $Outlets->country_id = $_POST['country'];
                $Outlets->city_id = $_POST['city'];
                $Outlets->location_id = $_POST['location'];
                $Outlets->vendor_id = $_POST['vendor'];
               // $Outlets->category_ids = implode(',',$_POST['category']);
                $Outlets->delivery_time = $_POST['delivery_time'];
                $Outlets->pickup_time = $_POST['pickup_time'];
                $Outlets->delivery_areas = implode(',',$_POST['delivery_areas']);
                $Outlets->modified_date = date('Y-m-d H:i:s');
                $Outlets->created_by = Auth::user()->id;
                $Outlets->active_status = isset($_POST['active_status'])?$_POST['active_status']:0;
                $Outlets->contact_email = $_POST['contact_email'];
                //$Outlets->contact_address = $_POST['contact_address'];
				if(!empty($_POST['latitude']))
					$Outlets->latitude = $_POST['latitude'];
				if(!empty($_POST['longitude']))
					$Outlets->longitude = $_POST['longitude'];
                $Outlets->cancel_time = $_POST['cancel_time'];
                $Outlets->return_time = $_POST['return_time'];
                $Outlets->delivery_charges_fixed = $_POST['delivery_charges_fixed'];
                $Outlets->delivery_charges_variation = $_POST['delivery_cost_variation'];
               if(!empty($_POST['service_tax']))
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
            return Redirect::to('vendors/outlets');
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
     * Display the specified vendor.
     *
     * @param  int  $id
     * @return Response
     */
    public function branch_show($id)
    {
        if(!hasTask('vendors/outlet_details'))
        {
            return view('errors.404');
        }
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
            return Redirect::to('vendors/outlets');    
        }
        //Get countries data
        $countries = getCountryLists();
        return view('admin.outlets.show')->with('countries', $countries)->with('data', $data);
    }
   
       /**
     * Delete the specified vendor in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function branch_destory($id)
    {
        if(!hasTask('vendors/delete_outlet')){
            return view('errors.404');
        }
        $data = Outlets::find($id);
        if(!count($data)){
            Session::flash('message', 'Invalid Outlet Details'); 
            return Redirect::to('vendors/vendors');    
        }
        //$data->delete();
        //Update delete status while deleting
        $data->active_status = 2;
        $data->save();
        Session::flash('message', trans('messages.Outlet has been deleted successfully!'));
        return Redirect::to('vendors/outlets');
    }
    
    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxBranch()
    {
        $query = '"vendors_infos"."lang_id" = (case when (select count(lang_id) as totalcount from vendors_infos where vendors_infos.lang_id = '.getAdminCurrentLang().' and vendors.id = vendors_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $query1 = '"outlet_infos"."language_id" = (case when (select count(id) as totalcount from outlet_infos where outlet_infos.language_id = '.getAdminCurrentLang().' and outlets.id = outlet_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $vendors = Outlets::Leftjoin('vendors','vendors.id','=','outlets.vendor_id')
                    ->Leftjoin('vendors_infos','vendors_infos.id','=','vendors.id')
                    ->Leftjoin('outlet_infos','outlet_infos.id','=','outlets.id')
                    ->select('vendors_infos.vendor_name','outlets.id','outlets.active_status','outlets.modified_date','outlets.contact_email','outlets.contact_phone','outlet_infos.contact_address','outlets.created_date','outlets.modified_date','outlet_infos.outlet_name')
                    ->whereRaw($query)
                    ->whereRaw($query1)
                    ->orderby('outlets.id','desc')
                    ->get();
        return Datatables::of($vendors)->addColumn('action', function ($vendors) {
            if(hasTask('vendors/edit_outlet'))
            {
                $html='<div class="btn-group"><a href="'.URL::to("vendors/edit_outlet/".$vendors->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("vendors/outlet_details/".$vendors->id).'" class="view-'.$vendors->id.'" title="'.trans("messages.View").'"><i class="fa fa-file-text-o"></i>&nbsp;&nbsp;'.@trans("messages.View").'</a></li>
                        <li><a href="'.URL::to("vendors/delete_outlet/".$vendors->id).'" class="delete-'.$vendors->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $( document ).ready(function() {
                        $(".delete-'.$vendors->id.'").on("click", function(){
                            return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });
                    });
                </script>';
                return $html;
            }
        })
        ->addColumn('id', function ($vendors) {
			$data ="<input type='checkbox'  class='deleteRow' value='".$vendors['id']."'  /> ".$vendors['id'];
			return $data;
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
        ->rawColumns(['modified_date','active_status','id','action'])

        ->make(true);
    }

    /**
     * Show the application outlets.
     * @return \Illuminate\Http\Response
     */
    public function outlet_managers()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('vendors/outlet_managers'))
            {
                return view('errors.404');
            }
            return view('admin.outlets.managers.list');
        }
    }

    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxBranchmanager()
    {
        $query = '"vendors_infos"."lang_id" = (case when (select count(id) as totalcount from vendors_infos where vendors_infos.lang_id = '.getAdminCurrentLang().' and outlet_managers.vendor_id = vendors_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $query1 = '"outlet_infos"."language_id" = (case when (select count(id) as totalcount from outlet_infos where outlet_infos.language_id = '.getAdminCurrentLang().' and outlets.id = outlet_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $outlet_managers = DB::table('outlet_managers')
                            ->select('outlet_managers.first_name','outlet_managers.email','outlet_managers.mobile_number','outlet_managers.email','outlet_managers.active_status','outlet_managers.created_date','outlet_managers.id','vendors_infos.vendor_name','outlet_infos.outlet_name')
                            ->leftJoin('vendors_infos','vendors_infos.id','=','outlet_managers.vendor_id')
                            ->leftJoin('outlets','outlets.id','=','outlet_managers.outlet_id')
                            ->leftJoin('outlet_infos','outlet_infos.id','=','outlets.id')
                            ->whereRaw($query)
                            ->whereRaw($query1)
                            ->orderBy('outlet_managers.id', 'desc');
                            //~ ->toSql();
        //~ print_r($outlet_managers);die;
        return Datatables::of($outlet_managers)->addColumn('action', function ($outlet_managers) {
            if(hasTask('vendors/create_outlet_managers'))
            {
                $html='<div class="btn-group"><a href="'.URL::to("vendors/edit_outlet_manager/".$outlet_managers->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("vendors/delete_outlet_managers/".$outlet_managers->id).'" class="delete-'.$outlet_managers->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $( document ).ready(function() {
                        $(".delete-'.$outlet_managers->id.'").on("click", function(){
                            return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });
                    });
                </script>';
                return $html;
            }
        })
        ->addColumn('active_status', function ($outlet_managers) {
            if($outlet_managers->active_status==0):
                $data = '<span class="label label-warning">'.trans("messages.Inactive").'</span>';
            elseif($outlet_managers->active_status==1):
                $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
            elseif($outlet_managers->active_status==2):
                $data = '<span class="label label-danger">'.trans("messages.Delete").'</span>';
            endif;
            return $data;
        })
        ->rawColumns(['active_status','action'])

        ->make(true);
    }

    /**
     * Create the specified outlet manager in view.
     * @param  int  $id
     * @return Response
     */
    
    public function outlet_managers_create()
    {
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('vendors/create_outlet_managers')){
                return view('errors.404');
            }
            $id = 1;
            $settings = Settings::find($id);
            //Get countries data
            $countries = getCountryLists();
            $vendors_list  = getVendorLists(5);
            return view('admin.outlets.managers.create')->with('countries', $countries)->with('settings', $settings)->with('vendors_list', $vendors_list);
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
        if(!hasTask('create_manager'))
        {
            return view('errors.404');
        }
        $validation = Validator::make($data->all(), array(
            //~ 'social_title'  => 'required',
            'first_name'    => 'required|regex:/(^[A-Za-z0-9 ]+$)+/',
            'last_name'     => 'required|regex:/(^[A-Za-z0-9 ]+$)+/',
            'email'         => 'required|email|unique:outlet_managers,email',
            'user_password' => 'required|min:5|max:32',
            'gender'        => 'required',
            'date_of_birth' => 'date',
            'gender'        => 'required',
            //'mobile' => 'required|regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
            'mobile'        => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            'vendor_name'   => 'required',
            'outlet_name'=>'required',
            'postal_code'=> 'required|numeric',
            'address'=>'required',
            'image'       => 'mimes:png,jpeg,bmp|max:2024',
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
            $managers->first_name    = ucfirst($_POST['first_name']);
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
            $managers->active_status     = isset($_POST['active_status'])?$_POST['active_status']:0;
            $managers->is_verified       = isset($_POST['is_verified'])?$_POST['is_verified']:0;
            //$drivers->ip_address      = Request::ip();
            $managers->created_date      = date("Y-m-d H:i:s");
            $managers->modified_date     = date("Y-m-d H:i:s");
            $managers->created_by = Auth::id();
            $verification_key           = Text::random('alnum',12);
            $managers->verification_key  = $verification_key;
            $managers->postal_code        = $_POST['postal_code'];
            $managers->address        = $_POST['address'];
            $managers->vendor_id        = $_POST['vendor_name'];
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
            return Redirect::to('vendors/outlet_managers');
        }
    }

    

    /**
     * Edit the specified driver in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function outlet_managers_edit($id)
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('vendors/edit_outlet_manager')){
                return view('errors.404');
            }
            //Get driver details
            $managers = Outlet_managers::find($id);
            if(!count($managers))
            {
                Session::flash('message', 'Invalid manager Details'); 
                return Redirect::to('vendors/outlet_managers');
            }
            $settings = Settings::find(1);
            $countries = getCountryLists();
            $vendors_list  = getVendorLists(5);
            SEOMeta::setTitle('Edit Manager - '.$this->site_name);
            SEOMeta::setDescription('Edit Manager - '.$this->site_name);
            return view('admin.outlets.managers.edit')->with('settings', $settings)->with('countries', $countries)->with('data', $managers)->with('vendors_list', $vendors_list);
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
        if(!hasTask('admin/managers/update'))
        {
            return view('errors.404');
        }
        $validation = Validator::make($data->all(), array(
            //~ 'social_title'  => 'required',
            'first_name'    => 'required|regex:/(^[A-Za-z0-9 ]+$)+/',
            'last_name'     => 'required|regex:/(^[A-Za-z0-9 ]+$)+/',
            'email' => 'required|email|max:255|unique:outlet_managers,email,'.$id,
            //'user_password' => 'required|min:5|max:32',
            'gender'        => 'required',
            'date_of_birth' => 'date',
            //'mobile' => 'required|regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
            'mobile'        => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            'gender'        => 'required',
            'vendor_name'   => 'required',
            'outlet_name'=>'required',
            'postal_code'=> 'required|numeric',
            'address'=>'required',
            'image'       => 'mimes:png,jpeg,bmp|max:2024',
            'country_code'  => 'required',
        ));
        // process the validation
        if ($validation->fails())
        {
            return Redirect::back()->withErrors($validation)->withInput();
        }
        else {
            
            // store datas in to database
            $managers = Outlet_managers::find($id);
            /** $manager_token = sha1(uniqid(Text::random('alnum', 32), TRUE));
            if(!$managers->manager_token)
            {
                $managers->manager_token = $manager_token;
            }**/
            //~ $managers->social_title  = $_POST['social_title'];
             $managers->first_name    = ucfirst($_POST['first_name']);
            $managers->last_name     = $_POST['last_name'];
            $managers->email         = $_POST['email'];
            $managers->hash_password = md5($_POST['user_password']);
            $managers->mobile_number = $_POST['mobile'];
             $managers->date_of_birth = $_POST['date_of_birth'];
            $managers->gender        = $_POST['gender'];
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
            //$managers->created_by = Auth::id();
            $verification_key           = Text::random('alnum',12);
            $managers->verification_key  = $verification_key;
            $managers->postal_code        = $_POST['postal_code'];
            $managers->address        = $_POST['address'];
            $managers->vendor_id        = $_POST['vendor_name'];
            $managers->outlet_id        = $_POST['outlet_name'];
            $managers->country_code     = $_POST['country_code'];
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
            return Redirect::to('vendors/outlet_managers');
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

        /**
     * Delete the specified vendor in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function outlet_managers_destory($id)
    {
        if(!hasTask('vendors/delete_outlet_managers'))
        {
            return view('errors.404');
        }
        $data = Outlet_managers::find($id);
        if(!count($data)){
            Session::flash('message', 'Invalid Outlet manager details'); 
            return Redirect::to('vendors/outlet_managers');    
        }
        $data->delete();
        Session::flash('message', trans('messages.Outlet manager has been deleted successfully!'));
        return Redirect::to('vendors/outlet_managers');
    }

    public function bulkdeletevendor(Request $request)
	{
		
		if($request->ajax()){
			$data_ids = $request->input('data_ids');
			$data_id_array = explode(",", $data_ids); 
			if(!empty($data_id_array)) {
				foreach($data_id_array as $id) {
                    $Vendors = Vendors::find($id);
                    $outlets = DB::select('select * from outlets where vendor_id = '.$id.'');
                    if(count($outlets)){
                     foreach($outlets as $out){
                       
                         $affected = DB::table('outlet_infos')->where('id', '=', $out->id)->delete();
                         $affected = DB::table('outlets')->where('id', '=', $out->id)->delete();
         
                     }
                 }

                 $data = DB::select('select id,logo_image,featured_image from vendors where id = '.$id.'');
                 if(count($data)){
                     
                     if(file_exists(base_path().'/public/assets/admin/base/images/vendors/logos/'.$data[0]->logo_image) && ($data[0]->logo_image != '')) {
                     
                         unlink(base_path() .'/public/assets/admin/base/images/vendors/logos/'.$data[0]->logo_image);
                     }
                   
                     if(file_exists(base_path() .'/public/assets/admin/base/images/vendors/'.$data[0]->featured_image)&& ($data[0]->featured_image != '')) {
                         unlink(base_path() .'/public/assets/admin/base/images/vendors/'.$data[0]->featured_image);
                     }
                     if(file_exists(base_path() .'/public/assets/admin/base/images/vendors/list/'.$data[0]->featured_image) && ($data[0]->featured_image != '')) {
                         unlink(base_path() .'/public/assets/admin/base/images/vendors/list/'.$data[0]->featured_image);
                     }
                     if(file_exists(base_path() .'/public/assets/admin/base/images/vendors/detail/'.$data[0]->featured_image)&& ($data[0]->featured_image != '')) {
                         unlink(base_path() .'/public/assets/admin/base/images/vendors/detail/'.$data[0]->featured_image);
                     }
                     if(file_exists(base_path() .'/public/assets/admin/base/images/vendors/thumb/'.$data[0]->featured_image)&& ($data[0]->featured_image != '')) {
                         unlink(base_path() .'/public/assets/admin/base/images/vendors/thumb/'.$data[0]->featured_image);
                     }
                     if(file_exists(base_path() .'/public/assets/admin/base/images/vendors/thumb/detail/'.$data[0]->featured_image)&& ($data[0]->featured_image != '')) {
                         unlink(base_path() .'/public/assets/admin/base/images/vendors/thumb/detail/'.$data[0]->featured_image);
                     }
                 }
                    $affected = DB::table('vendors_infos')->where('id', '=', $id)->delete();
					$Vendors->delete();
				}
			}
			return response()->json([
				'data' => true
			]);
		}
    }
    public function bulkdeleteoutlet(Request $request)
	{
		
		if($request->ajax()){
			$data_ids = $request->input('data_ids');
			$data_id_array = explode(",", $data_ids); 
			if(!empty($data_id_array)) {
				foreach($data_id_array as $id) {
                    $Outlets = Outlets::find($id);
                    $affected = DB::table('outlet_infos')->where('id', '=', $id)->delete();
					$Outlets->delete();
				}
			}
			return response()->json([
				'data' => true
			]);
		}
	}

     public function bulkimport()
    {
        return view('admin.products.bulkimport');
    }
    public function bulk_import(Request $data)
    {
        $this->validate($data, [
          'select_file'  => 'required|mimes:xls,xlsx'
        ]);

        $path = $data->file('select_file')->getRealPath();
        $product_not_exist =$recorde_exist =$uploaded =  array();
        $data = Excel::load($path)->get();
        if($data->count() > 0)
         {
          foreach($data->toArray() as $key => $value)
          {
          
            $product_exist_check = DB::table('admin_products')
                ->select('admin_products.id')
                ->where('admin_products.id', $value['product_id'])
                ->count();



            if($product_exist_check != 0){

                $data = DB::table('outlet_products')
                    ->select('outlet_products.id')
                    ->where('outlet_products.outlet_id', $value['outlet_id'])
                    ->where('outlet_products.vendor_id', $value['vendor_id'])
                    ->where('outlet_products.product_id', $value['product_id'])
                    ->count();
                    //print_r($data);exit();
                    if($data ==0){
                        $insert_data[] = array(
                         'outlet_id'  => $value['outlet_id'],
                         'vendor_id'   => $value['vendor_id'],
                         'original_price'   => $value['original_price'],
                         'discount_price'    => $value['discount_price'],
                         'tax'  => $value['tax'],
                         'stock_status'   => $value['stock_status'],
                         'admin_status'   => $value['admin_status'],
                         'created_date'   => $value['created_date'],
                         'updated_date'   => $value['updated_date'],
                         'product_id'   => $value['product_id']
                        );
                        $uploaded[] =$value['sno'];

                    }else{
                        $recorde_exist[] = $value['sno'];

                    }
            }else{
                 $product_not_exist[] = $value['sno'];

            }
          }
        /*  if(!empty($insert_data))
          {
           DB::table('outlet_products')->insert($insert_data);
          }*/
         }
         $data=array("prdouctNotexist"=>$product_not_exist,
                        "recorde_exist"=>$recorde_exist,
                        "uploaded"=>$uploaded,
                        );
        return view('admin.products.import_fields')->with('success', 'Excel Data Imported successfully.')->with('data',$data);
        //return Redirect::to('admin/products/bulkimport')->with('success', 'Excel Data Imported successfully.')->with('data',$data);

        //return back()->with('success', 'Excel Data Imported successfully.')->with('data',$data);
    }
}
