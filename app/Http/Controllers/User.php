<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Model\users;
use App\Model\users_activity;
use App\Model\Users\groups;
use App\Model\Users\addresstype;
use App\Model\settings;
use App\Model\address_infos;
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
use Hash;

class User extends Controller
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
        $this->site_name = isset(getAppConfig()->site_name)?ucfirst(getAppConfig()->site_name):'';
        $this->middleware('auth');
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
    public function view_profile($id='')
    {
        $users    = Users::find($id);
        $activity = DB::table('user_activity_log')
                    ->where('user_id','=',$id)
                    ->orderBy('activity_id', 'desc')
                    ->paginate(20);
        return view('admin/users/view')->with('users', $users)->with('activities', $activity)->with('totalActivity', count($activity));
    }
    
    public function loadactivityajax()
    { 
        $page = $_POST['page'];
        if(!$page || $page <= 0) {
            $page = 2;
        }
        $offset = ($page - 1) * 20 + 1;
        $actvity =  $this->getActivity($_POST['user_id'],20,$offset);
        
    print_r($actvity); exit;
         
    }
    
    public function getActivity($userid, $limit = 20,$offset = '')
    {
        $this->_userid = $userid;
        $activity = DB::table('user_activity_log')
                    ->where('user_id','=',$this->_userid)
                    ->skip($limit)->take($offset)
                    ->get();
        return $activity;
    }
    
        /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function group_index()
    {    
         if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('admin/users/groups')){
                return view('errors.404');
            }
            return view('admin.users.group.list');
        }
    }
    
     /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function anyAjaxgroupslist()
    {
        $groups = DB::table('users_group')->select('*')->orderBy('group_id', 'desc');
        return Datatables::of($groups)->addColumn('action', function ($groups) {
            if(hasTask('admin/groups/edit'))
            {
                $html ='<div class="btn-group"><a href="'.URL::to("admin/groups/edit/".$groups->group_id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                        <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu xs pull-right" role="menu">
                            <li><a href="'.URL::to("admin/groups/delete/".$groups->group_id).'" class="delete-'.$groups->group_id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                        </ul>
                    </div>
                    <script type="text/javascript">
                        $( document ).ready(function() {
                            $(".delete-'.$groups->group_id.'").on("click", function(){
                                return confirm("'.trans("messages.Are you sure want to delete?").'");
                            });
                        });
                    </script>';
                return $html;
            }
        })
        ->addColumn('group_status', function ($groups) {
            if($groups->group_status==0):
                $data = '<span class="label label-danger">'.trans("messages.Inactive").'</span>';
            elseif($groups->group_status==1):
                $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
            endif;
            return $data;
        })
                        ->rawColumns(['group_status','action'])

        ->make(true);
    }
    
     /**
     * Show the form for creating a new blog.
     *
     * @return Response
     */
    public function group_create()
    { 
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('admin/groups/create')){
                return view('errors.404');
            }
            return view('admin.users.group.create');
        }
    }
    
            /**
     * Store a newly created blog in storage.
     *
     * @return Response
     */
    public function group_store(Request $data)
    {
        if(!hasTask('admin/groups/create')){
            return view('errors.404');
        }
        $data1=Input::all();
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $validation = Validator::make($data->all(), array(
            'group_name' => 'required|unique:users_group,group_name',
            'group_info' => 'required|max:150',
        ));
        // process the validation
        if ($validation->fails()) {
            //return redirect('create')->withInput($data1)->withErrors($validation);
            return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store
            $Groups = new Groups;
            $Groups->group_name      = ucfirst($_POST['group_name']);
            $Groups->group_info    = $_POST['group_info'];
            $Groups->created_date = date("Y-m-d H:i:s");
            $Groups->updated_date = date("Y-m-d H:i:s");
            $Groups->group_status    = isset($_POST['status']);
            $Groups->save();
            // redirect
            Session::flash('message', trans('messages.Group has been created successfully'));
            return Redirect::to('admin/users/groups');
        }
    }
    
    public function group_edit($id)
    {
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('admin/groups/edit')){
                return view('errors.404');
            }
            $groups = Groups::find($id);
            return view('admin.users.group.edit')->with('data', $groups);
        }
    }
    /**
     * Store a newly created blog in storage.
     *
     * @return Response
     */
    public function group_update(Request $data,$id)
    {
        if(!hasTask('admin/groups/edit')){
            return view('errors.404');
        }
        $data1=Input::all();
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $validation = Validator::make($data->all(), array(
            'group_name' => 'required|unique:users_group,group_name,'.$id.',group_id',
            'group_info' => 'required|max:150',
        ));
        
        // process the validation
        if ($validation->fails()) {
                //return redirect('create')->withInput($data1)->withErrors($validation);
                return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store
            $Groups = Groups::find($id);
            $Groups->group_name      = ucfirst($_POST['group_name']);
            $Groups->group_info    = $_POST['group_info'];
            $Groups->updated_date = date("Y-m-d H:i:s");
            $Groups->group_status    = isset($_POST['status']);
            $Groups->save();
            // redirect
            Session::flash('message', trans('messages.Group has been updated successfully'));
            return Redirect::to('admin/users/groups');
        }
    }
    public function group_delete($id)
    {
        if(!hasTask('admin/groups/delete')){
            return view('errors.404');
        }
        $groups = DB::select('select group_id from users_group where group_id = '.$id);
        if(count($groups))
        {
            DB::table('users_group')->where('group_id', '=', $id)->delete();
            Session::flash('message', trans('messages.Group has been deleted successfully!'));
            return Redirect::to('admin/users/groups');
        }
        else {
            Session::flash('message', trans('messages.No group found'));
            return Redirect::to('admin/users/groups');
        }
    }
            /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function address_index()
    {    
         if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('admin/users/addresstype')){
                return view('errors.404');
            }
            return view('admin.users.address.list');
        }
    }
    /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function anyAjaxaddresstype()
    {   
		$language_id = getAdminCurrentLang();
	    $query = '"address_infos"."language_id" = (case when (select count(*) as totalcount from address_infos where address_infos.language_id = '.$language_id.' and address_type.id = address_infos.address_id) > 0 THEN '.$language_id.' ELSE 1 END)';
        $adddress_type=Addresstype::Leftjoin('address_infos','address_infos.address_id','=','address_type.id')
                         ->select('address_type.*','address_infos.*') 
                         ->whereRaw($query)
                         ->orderBy('id', 'desc')
                         ->get();
        return Datatables::of($adddress_type)->addColumn('action', function ($adddress_type) {
            if(hasTask('admin/addresstype/edit'))
            {
                $html ='<div class="btn-group"><a href="'.URL::to("admin/addresstype/edit/".$adddress_type->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                        <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu xs pull-right" role="menu">
                            <li><a href="'.URL::to("admin/addresstype/delete/".$adddress_type->id).'" class="delete-'.$adddress_type->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                        </ul>
                    </div>
                    <script type="text/javascript">
                        $( document ).ready(function() {
                            $(".delete-'.$adddress_type->id.'").on("click", function(){
                                return confirm("'.trans("messages.Are you sure want to delete?").'");
                            });
                        });
                    </script>';
                return $html;
            }
        })
        ->addColumn('active_status', function ($adddress_type) {
            if($adddress_type->active_status==0):
                $data = '<span class="label label-danger">'.trans("messages.Inactive").'</span>';
            elseif($adddress_type->active_status==1):
                $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
            endif;
            return $data;
        })
                                ->rawColumns(['active_status','action'])

        ->make(true);
    }
    /**
     * Show the form for creating a new blog.
     *
     * @return Response
     */
    public function address_create()
    { 
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('admin/addresstype/create')){
                return view('errors.404');
            }
            return view('admin.users.address.create');
        }
    }
    
    /**
     * Store a newly created blog in storage.
     *
     * @return Response
     */
    public function address_store(Request $data)
    {
        if(!hasTask('admin/addresstype/create'))
        {
            return view('errors.404');
        }
        $address_type = Input::get('address_type');
        foreach ($address_type  as $key => $value) {
            $fields['address_type'.$key] = $value;
            $rules['address_type'.'1'] = 'required|unique:address_infos,name';
        }
        
         $validator = Validator::make($fields, $rules);
        // process the validation
        if ($validator->fails()) {
			return Redirect::back()->withErrors($validator)->withInput();
        } else {
            $Addresstype = new Addresstype;
            $Addresstype->created_date = date("Y-m-d H:i:s");
            $Addresstype->active_status    = isset($_POST['status']);
            $Addresstype->save();
            $this->Addresstype_save_after($Addresstype,$_POST);
            // redirect
            Session::flash('message', trans('messages.Address type has been created successfully'));
            return Redirect::to('admin/users/addresstype');
          }
    }
    
    
    public function address_edit($id)
    {
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('admin/addresstype/edit')){
                return view('errors.404');
            }
            $address_type = Addresstype::find($id);
            if(!count($address_type)){
                 Session::flash('message', 'Invalid address'); 
                 return Redirect::to('admin/addresstype');    
             }
             $info = new address_infos;
            return view('admin.users.address.edit')->with('data', $address_type)->with('infomodel', $info);
        }
    }
    public function address_delete($id)
    {
        if(!hasTask('admin/addresstype/delete'))
        {
            return view('errors.404');
        }
        $address = Addresstype::find($id);
        if(count($address))
        {
            $address = Addresstype::find($id);
            $address->delete();
            Session::flash('message', trans('messages.Address type has been deleted successfully!'));
            return Redirect::to('admin/users/addresstype');
        }
        else {
            Session::flash('message', trans('messages.No address type found'));
            return Redirect::to('admin/users/addresstype');
        }
    }
    /**
     * Store a newly created blog in storage.
     *
     * @return Response
     */
    public function address_update(Request $data,$id)
    {
        if(!hasTask('admin/addresstype/edit'))
        {
            return view('errors.404');
        }
      
        $address_type = Input::get('address_type');
        foreach ($address_type  as $key => $value) {
            $fields['address_type'.$key] = $value;
            $rules['address_type'.'1'] = 'required|unique:address_infos,name,'.$id.',address_id';
        }
        $validator = Validator::make($fields, $rules);
        // process the validation
        if ($validator->fails()) {
			return Redirect::back()->withErrors($validator)->withInput();
        } else {
            $Addresstype = Addresstype::find($id);
            $Addresstype->active_status    = isset($_POST['status']);
            $Addresstype->save();
             $this->Addresstype_save_after($Addresstype,$_POST);
            Session::flash('message', trans('messages.Address type has been updated successfully'));
            return Redirect::to('admin/users/addresstype');
		}
        
    }
     public static function Addresstype_save_after($object,$post)
    {   
        $Addresstype = $object;
        $post = $post;
        if(isset($post['address_type'])){
            $address_type = $post['address_type'];
            try{
                $affected = DB::table('address_infos')->where('address_id', '=', $object->id)->delete();
                $languages = DB::table('languages')->where('status', 1)->get();
                foreach($languages as $key => $lang){
					 if(isset($address_type[$lang->id]) && $address_type[$lang->id]!=""){
                        $infomodel = new Address_infos;
                        $infomodel->name = $address_type[$lang->id]; 
                        $infomodel->language_id = $lang->id;
                        $infomodel->address_id = $object->id; 
                        $infomodel->save();
					}
                      
                   
                }
                }catch(Exception $e) {
                    
                    Log::Instance()->add(Log::ERROR, $e);
                }
        }
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function user_index()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else{
            if(!hasTask('admin/users/index')){
                return view('errors.404');
            }
                

            return view('admin.users.list');
        }
    }
    /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
     public function anyAjaxuserlist()
    {
        $users = DB::table('users')
                    ->leftJoin('users_group','users_group.group_id','=','users.user_group')
                    //->leftJoin('roles_users','roles_users.user_id','=','users.id')
                    ->select('users.id','users.status','users.login_type','users.user_type','users_group.group_name','users.created_date','users.is_verified','users.social_title','users.name','users.email')
                    ->where('user_type',"!=",1)
                    ->orderBy('id', 'desc');
        return Datatables::of($users)->addColumn('action', function ($users) {
            if(hasTask('admin/users/create')){
                $html ='<div class="btn-group"><a href="'.URL::to("admin/users/edit/".$users->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/user/viewprofile/".$users->id).'" title="'.trans("messages.Details").'"><i class="fa fa-search"></i>&nbsp;&nbsp;'.@trans("messages.Details").'</a></li>
                        <li><a href="'.URL::to("admin/users/delete/".$users->id).'" class="delete-'.$users->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $( document ).ready(function() {
                        $(".delete-'.$users->id.'").on("click", function(){
                            return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });
                    });
                </script>';
                return $html;
            }
        })
        ->addColumn('status', function ($users) {
            if($users->status==0):
                $data = '<span class="label label-danger">'.trans("messages.Inactive").'</span>';
            elseif($users->status==1):
                $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
            endif;
            return $data;
        })
        ->addColumn('login_type', function ($users) {
            $data = '-';
            if($users->login_type == 1):
                $data = trans('messages.Web user');
            elseif($users->login_type == 2):
                $data = trans('messages.Android user');
            elseif($users->login_type == 3):
                $data = trans('messages.iOS user');
            endif;
            return $data;
        })
        ->addColumn('group_name', function ($users) {
                $data = '-';
                if($users->group_name != ''):
                $data = $users->group_name;
                endif;
                return $data;
                })
        ->addColumn('created_date', function ($users) {
                $data = '-';
                if($users->created_date != ''):
                $data = $users->created_date;
                endif;
                return $data;
                })
        ->addColumn('is_verified', function ($users) {
            if($users->user_type == 3):
                if($users->is_verified==0):
                    $data = '<span class="label label-danger">'.trans("messages.Disabled").'</span>';
                elseif($users->is_verified==1):
                    $data = '<span class="label label-success">'.trans("messages.Enabled").'</span>';
                endif;
                return $data;
            else:
                return '-';
            endif;
        })

        ->rawColumns(['is_verified','created_date','group_name','login_type','status','action'])

        ->make(true);
    }
    
    
      /**
     * Show the form for creating a new blog.
     *
     * @return Response
     */
    public function user_create()
    { 
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('admin/users/create')){
                return view('errors.404');
            }    
            $id=1;
            $settings = Settings::find($id);
            $groups=DB::table('users_group')
             ->select('users_group.*')
             ->where('group_status','=',1)
            ->orderBy('group_id', 'asc')
            ->get();
            return view('admin.users.create')->with('settings', $settings)->with('groups', $groups);
        }
    }
    /**
     * Update the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function user_store(Request $data)
    {
        if(!hasTask('createuser'))
        {
            return view('errors.404');
        }
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $validation = Validator::make($data->all(), array(
            //'title' => 'required',
            'name' => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:3',
            'email' => 'required|email|unique:users,email',
            'user_password' => 'required|min:5|max:32',
            'gender' => 'required',
            'group' => 'required',
            'user_type' => 'required',
            'image'       => 'mimes:png,jpeg,bmp|max:2024',
            'mobile' => 'regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
            'mobile' => 'required',
            //mimes:jpeg,bmp,png and for max size max:10000
        ));
        // process the validation
        if ($validation->fails()) {
               return Redirect::back()->withErrors($validation)->withInput();
        } else {
            
            // store datas in to database
            $users = new Users;
            $usertoken = sha1(uniqid(Text::random('alnum', 32), TRUE));
            if(!$users->user_token){
                $users->user_token    = $usertoken;
            }
            $users->name   = $_POST['name'];
            $users->email  = $_POST['email'];
            $users->mobile = $_POST['mobile'];
            if($_POST['user_type']==2)
            {
                $users->password = Hash::make($_POST['user_password']);
            }
            else {
                $users->password    = md5($_POST['user_password']);
                $users->is_verified = isset($_POST['is_verified']);
            }
            if($_POST['date_of_birth'] != '')
                $users->date_of_birth      = $_POST['date_of_birth'];
            //$users->social_title      = $_POST['social_title'];
            $users->gender      = $_POST['gender'];
            $users->user_group     = $_POST['group'];
            $users->user_type      = $_POST['user_type'];
            $users->login_type      = 1;
            if(isset($_POST['country']) && $_POST['country']!=''){
                $users->country_id      = $_POST['country'];
            }
             if(isset($_POST['city']) && $_POST['city']!=''){
                $users->city_id      = $_POST['city'];
            }
            $users->status      = isset($_POST['status']);
            //$users->ip_address      = Request::ip();
            $users->created_date = date("Y-m-d H:i:s");
            $users->updated_date = date("Y-m-d H:i:s");
            $users->user_created_by      = Auth::id();
            $verification_key = Text::random('alnum',12);
            $users->verification_key = $verification_key;
            $refferral_key = Text::random('alnum',6);
            $users->referral_code = $refferral_key;

            $users->save();
            $this->user_save_after($users,$_POST);
           
            if(isset($_FILES['image']['name']) && $_FILES['image']['name']!=''){ 
                $destinationPath = base_path() .'/public/assets/admin/base/images/admin/profile/'; // upload path
                $imageName = $users->id . '.' .
                $data->file('image')->getClientOriginalExtension();
                $data->file('image')->move($destinationPath, $imageName);
                $destinationPath1 = url('/assets/admin/base/images/admin/profile/'.$imageName.'');
                Image::make( $destinationPath1 )->fit(75, 75)->save(base_path() .'/public/assets/admin/base/images/admin/profile/thumb/'.$imageName)->destroy();
                $users->image = $imageName;
                $users->save();
            } 
            
            $to = $_POST['email'];
            $template = DB::table('email_templates')->select('*')->where('template_id', '=',1)->get();
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
                $name = $_POST['name'];
                $phoneno = $_POST['mobile'];
            
                $content = array("logo_image"=>$logo_image,"image1"=>$image1,"image2"=>$image2,"image3"=>$image3,"name"=>$name,"phoneno"=>$phoneno);
                $attachment = "";
                $email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
                //print_r($email);exit;
            }
            // redirect
            Session::flash('message', trans('messages.User has been created successfully'));
            return Redirect::to('admin/users/index');
        }
    }
    
    public function user_save_after($object,$post)
    {
        //print_r($post); exit;
        //$model->toArray();
        //$model->getAttributes();
        $user=$object->getAttributes();
        if((isset($user['is_verified']) && $user['is_verified']) || $user['user_type'] == 2)
        {
            $template = DB::table('email_templates')
                            ->select('*')
                            ->where('template_id','=',self::USERS_WELCOME_EMAIL_TEMPLATE)
                            ->get();
            if(count($template)){
                $from = $template[0]->from_email;
                $from_name=$template[0]->from;
                $subject = $template[0]->subject;
                if(!$template[0]->template_id)
                {
                    $template = 'mail_template';
                    $from=getAppConfigEmail()->contact_email;
                    $subject = "Welcome to ".getAppConfig()->site_name;
                    $from_name="";
                }
                $user['password'] = $post['user_password'];
                $content =array("customer" => $user,'u_password' => $post['user_password']);
                $email=smtp($from,$from_name,$user['email'],$subject,$content,$template);
            }
        }
        else {
            $template = DB::table('email_templates')
                            ->select('*')
                            ->where('template_id','=',self::USERS_SIGNUP_EMAIL_TEMPLATE)
                            ->get();
             if(count($template))
             {
                $from = $template[0]->from_email;
                $from_name=$template[0]->from;
                $subject = $template[0]->subject;
                if(!$template[0]->template_id)
                {
                    $template = 'mail_template';
                    $from=getAppConfigEmail()->contact_email;
                    $subject = "Welcome to ".getAppConfig()->site_name;
                    $from_name="";
                }
                $url1 ='<a href="'.url('/').'/signup/confirmation?key='.$user['verification_key'].'&email='.$user['email'].'&u_password='.$user['password'].'"> This Confirmation Link </a>';
                $content = array("customer" => $user,"first_name" => $user['name'], "confirmation_link" => $url1);        
                $email=smtp($from,$from_name,$user['email'],$subject,$content,$template);
            }
        }
    }
    
    
    public function user_edit($id)
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else{
            if(!hasTask('admin/users/edit')){
                return view('errors.404');
            }
            $userscount=DB::table('users')
             ->select('users.id')
             ->where('user_type','!=',1)
             ->where('id','=',$id)
             ->get();
            if(!count($userscount)){
                Session::flash('message', trans('messages.Invalid User'));
                return Redirect::to('admin/users/index');
            }
            $users = Users::find($id);
            //$sid=Auth::id();
            $settings = Settings::find(1);
            $groups=DB::table('users_group')
             ->select('users_group.*')
             ->where('group_status','=',1)
            ->orderBy('group_id', 'asc')
            ->get();
            return view('admin.users.edit')->with('data', $users)->with('settings', $settings)->with('groups', $groups);
        }
    }
    
                /**
     * Update the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function user_update(Request $data , $id)
    {
        if(!hasTask('update_users')){
            return view('errors.404');
        }
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $validation = Validator::make($data->all(), array(
            //'title'=> 'required',
            'name'   => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:3',
            'email'  => 'required|email|unique:users,email,'.$id,
            'gender' => 'required',
            'group'  => 'required',
            'image'  => 'mimes:png,jpeg,bmp|max:2024',
            //~ 'mobile' => 'regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/',
            'mobile' => 'required',
            //mimes:jpeg,bmp,png and for max size max:10000
        ));
        // process the validation
        if ($validation->fails())
        {
            return Redirect::back()->withErrors($validation)->withInput();
        }
        else {
            // store datas in to database
            $users     = Users::find($id);
            $usertoken = sha1(uniqid(Text::random('alnum', 32), TRUE));
            if(!$users->user_token)
            {
                $users->user_token = $usertoken;
            }
            $users->name   = $_POST['name'];
            $users->email  = $_POST['email'];
             $users->mobile = $_POST['mobile'];
            $user_type     = $users->user_type;
        
            if($_POST['user_password'] != "")
            {
                if($user_type == 2)
                    $users->password = Hash::make($_POST['user_password']);
                else 
                    $users->password = md5($_POST['user_password']);
            }
            $users->date_of_birth = $_POST['date_of_birth'];
            //$users->social_title  = $_POST['social_title'];
            $users->gender        = $_POST['gender'];
            $users->user_group    = $_POST['group'];
            if(isset($_POST['country']) && $_POST['country']!='')
            {
                $users->country_id      = $_POST['country'];
            }
            if(isset($_POST['city']) && $_POST['city']!='')
            {
                $users->city_id      = $_POST['city'];
            }
            $users->status       = isset($_POST['status']);
            $users->is_verified  = isset($_POST['is_verified']);
            //$users->ip_address = Request::ip();
            $users->updated_date = date("Y-m-d H:i:s");
            $users->user_created_by = Auth::id();
            $users->save();
            //$this->user_save_after($users,$_POST);
            if(isset($_FILES['image']['name']) && $_FILES['image']['name']!='')
            { 
                $destinationPath = base_path() .'/public/assets/admin/base/images/admin/profile/'; // upload path
                $imageName = $users->id . '.' .
                $data->file('image')->getClientOriginalExtension();
                $data->file('image')->move($destinationPath, $imageName);
                $destinationPath1 = url('/assets/admin/base/images/admin/profile/'.$imageName.'');
                Image::make( $destinationPath1 )->fit(75, 75)->save(base_path() .'/public/assets/admin/base/images/admin/profile/thumb/'.$imageName)->destroy();
                $users->image = $imageName;
                $users->save();
            } 
            // redirect
            Session::flash('message', trans('messages.User has been updated successfully'));
            return Redirect::to('admin/users/index');
        }
    }
    /* To remove the user */
    public function user_delete($id)
    {
        if(!hasTask('admin/users/delete')){
            return view('errors.404');
        }
        $user = Users::find($id);
        if(count($user))
        {
            if(file_exists(base_path().'/public/assets/admin/base/images/admin/profile/'.$user->image) && $user->image != '')
            {
                unlink(base_path() .'/public/assets/admin/base/images/admin/profile/'.$user->image);
            }
            if(file_exists(base_path().'/public/assets/admin/base/images/admin/profile/thumb/'.$user->image) && $user->image != '')
            {
                unlink(base_path() .'/public/assets/admin/base/images/admin/profile/thumb/'.$user->image);
            }
            $user = Users::find($id);
            $user->delete();
            Session::flash('message', trans('messages.User has been deleted successfully!'));
            return Redirect::to('admin/users/index');
        }
        else {
            Session::flash('message', trans('messages.No user found'));
            return Redirect::to('admin/users/index');
        }
    }

}
