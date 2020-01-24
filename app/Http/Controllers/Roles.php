<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Model\role;
use App\Model\api_account;
use App\Model\roles_users;
use App\Model\api_resources;
use App\Model\role_tasks;
use App\Http\Requests;
use Session;
use Closure;
use Illuminate\Support\Facades\Auth;
use Image;
use MetaTag;
use Mail;
use File;
use SEO;
use SEOMeta;
use OpenGraph;
use Twitter;
use Illuminate\Support\Facades\Input;
use Yajra\Datatables\Datatables;
use URL;
use App;

class Roles extends Controller
{

    protected $_taskslist = array();
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
     * Display a listing of the blogs.
     *
     * @return Response
     */
    public function index()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else{
            if(!hasTask('system/permission')){
                return view('errors.404');
            }
            return view('admin.roles.list');
        }
    }
    /**
     * Display a listing of the blogs.
     *
     * @return Response
     */
    public function users()
    {
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('permission/users')){
                return view('errors.404');
            }
            // load the view and list the blogs
            return view('admin.roles.users.list');
        }
    }
    
    
    /**
     * Show the form for creating a new blog.
     *
     * @return Response
     */
    public function create()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('system/permission/create'))
            {
                return view('errors.404');
            }
            $taskresource = $this->getHtmlTree($this->optionTasks($tasks = null,$path = '', $level=0,$id=''),$id='');
            $tasks = $this->getTaskList();
            return view('admin.roles.create')->with('taskresource', $taskresource);
        }
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

            if(!hasTask('permission/usercreate')){
                return view('errors.404');
            }
            $roles=DB::table('user_roles')
             ->select('user_roles.*')
            ->orderBy('user_roles.role_name', 'asc')
            ->get();
            $users=DB::table('users')
             ->select('users.email','users.id')
            ->orderBy('users.email', 'asc')
            ->where('users.user_type',"=",2)
            ->where('users.status',"=",1)
            ->where('users.active_status',"=",'A')
            ->where('users.is_verified',"=",1)
            ->get();
            return view('admin.roles.users.create')->with('roles', $roles)->with('users', $users);
        }
    }
    
    /**
     * Store a newly created blog in storage.
     *
     * @return Response
     */
    public function store(Request $data)
    {
        if(!hasTask('system/rolecreate')){
            return view('errors.404');
        }
        $taskresource=$this->getHtmlTree($this->optionTasks($tasks = null,$path = '', $level=0,$id=''),$id='');
        $tasks=$this->getTaskList();
        $fields['role_name'] = strtolower(Input::get('role_name'));
        $fields['app_key'] = Input::get('app_key');
        $account_id = Input::get('account_id');
        $resources = Input::get('nodes');
        $resdata = Input::get('apires');
        $rules = array(
            'role_name' => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:3|max:32|unique:user_roles,role_name',
            'app_key' => 'required|unique:api_account,app_key',
        );
        $validator = Validator::make($fields, $rules);
        // process the validation
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            // store
            $role = new Role;
            $role->role_name =  strtolower($_POST['role_name']);
            $role->tag_bg_color =  $_POST['tag_bg_color'];
            $role->tag_text_color =  $_POST['tag_text_color'];
            $role->rolefor =  'A';
            $role->created_date = date("Y-m-d H:i:s");
            $role->active_status =  1;
            $role->save();
            $account_id=$this->role_save_after($role,$_POST);
            $set = array();
            if($role->id && count($resources)) {
                //$roleobject->setData($this->getRequest()->post())->save();
                foreach($resources as $res) {
                    $rs = explode("/",$res);
                    $s = '';
                    foreach($rs as $r) {
                        $s .= $r;
                        if(in_array($s,$set)) {
                            $s .= "/";
                            continue;
                        }
                        $set[] = $s;  
                        $s .= "/";
                    }
                }
            }
            /**  task info save **/
            //$affected = DB::table('role_tasks')->where('role_id', '=', $id)->delete();
            if(count($set)){
                foreach($set as $path) {
                    $roletask = new Role_tasks;
                    $roletask->role_id = $role->id;
                    $roletask->task_definition =$path;
                    $roletask->permissions =true;
                    if(isset($tasks[$path])) {
                        $roletask->task_index=$tasks[$path];
                    }
                    $roletask->save();
                }
            }
            
            /**  task info save end **/
            /**  api resorece save **/
            if($account_id){
                if(isset($resdata)) {
                    //$affected = DB::table('api_resources')->where('account_id', '=', $account_id)->delete();
                    if(isset($resdata['resource']) && $resdata['resource']!=''){          
                        foreach($resdata['resource'] as $key =>  $res) {
                            foreach($res as $method => $resource) {
                                $model = new Api_resources;
                                $model->resources = $resdata['resource_index'][$key];
                                $model->method = $method;
                                $model->account_id = $account_id;
                                $model->save();
                            }
                        }
                    }
                }
            }
            // redirect
            Session::flash('message', trans('messages.Role has been created successfully'));
            return Redirect::to('system/permission');
        }
    }

    /**
     * Store a newly created blog in storage.
     *
     * @return Response
     */
    public function user_store(Request $data)
    {
        if(!hasTask('permission/userstore')){
            return view('errors.404');
        }
        $fields['role_id'] = Input::get('role_id');
        $fields['user_id'] = Input::get('user_id');
        $rules = array(
            'role_id' => 'required',
            'user_id' => 'required',
        );
        $validator = Validator::make($fields, $rules);
        // process the validation
        if ($validator->fails()) {
                return Redirect::back()->withErrors($validator)->withInput();
        } else {
            $roles=DB::table('roles_users')
             ->select('roles_users.role_id','roles_users.user_id')
             ->where('roles_users.role_id',"=",$fields['role_id'])
             ->where('roles_users.user_id',"=",$fields['user_id'])
            ->get();
            if(count($roles)){
                $validator->errors()->add('role_id', trans('messages.This role already assigned to this uesr'));
                return Redirect::back()->withErrors($validator)->withInput();
            }
            // store
            $roleusers = new Roles_users;
            $roleusers->role_id =  $_POST['role_id'];
            $roleusers->user_id =  $_POST['user_id'];
            $roleusers->created_date = date("Y-m-d H:i:s");
            $roleusers->save();
            // redirect
            Session::flash('message', trans('messages.Role has been assgined to user successfully'));
            return Redirect::to('permission/users');
        }
    }
    
    /**
     * Display the specified blog.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            // get the blog
            $blog = Blogs::find($id);
            $info = new blog_infos;
            $type = 3;
            $query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = '.getAdminCurrentLang().' and categories.id = categories_infos.category_id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
                $category=DB::table('categories')
                 ->select('categories.*','categories_infos.*')
                ->leftJoin('categories_infos','categories_infos.category_id','=','categories.id')
                ->whereRaw($query)
                ->where("categories.category_type","=",$type)
                ->orderBy('categories_infos.category_name', 'asc')
                ->get();
            // show the view and pass the blog to it
            return view('blog.show')->with('data', $blog)->with('category', $category)->with('infomodel', $info);
        }
    }
    
        /**
     * Show the form for editing the specified blog.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('system/permission/edit')){
                return view('errors.404');
            }
            $taskresource=$this->getHtmlTree($this->optionTasks($tasks = null,$path = '', $level=0,$id),$id);
            // get the blog
            $roles = Role::find($id);
            if(!count($roles)){
                Session::flash('message', 'Invalid Role');
                return Redirect::to('system/permission');
             }
            $roles=DB::table('user_roles')
             ->select('user_roles.*','api_account.*')
            ->leftJoin('api_account','api_account.role_id','=','user_roles.id')
            ->where("user_roles.id","=",$id)
            ->get();
            return view('admin.roles.edit')->with('data', $roles[0])->with('taskresource', $taskresource);
        }
    }

        /**
     * Show the form for editing the specified blog.
     *
     * @param  int  $id
     * @return Response
     */
    public function user_edit($id)
    {

        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('permission/users/edit')){
                return view('errors.404');
            }
            // get the blog
            $roles_users = Roles_users::find($id);
            if(!count($roles_users))
            {
                Session::flash('message', 'Invalid Role');
                return Redirect::to('permission/users');
            }
            $roles = DB::table('user_roles')
                    ->select('user_roles.*')
                    ->orderBy('user_roles.role_name', 'asc')
                    ->get();
            $users = DB::table('users')
                    ->select('users.email','users.id')
                    ->orderBy('users.email', 'asc')
                    ->where('users.user_type',"=",2)
                    ->where('users.status',"=",1)
                    ->where('users.active_status',"=",'A')
                    ->where('users.is_verified',"=",1)
                    ->get();
            $roleusers=DB::table('roles_users')
                ->select('roles_users.ruid','roles_users.user_id','user_roles.role_name','users.email','users.id','roles_users.role_id')
                ->leftJoin("user_roles",'user_roles.id','=','roles_users.role_id')
                ->leftJoin('users','users.id','=','roles_users.user_id')
                ->orderBy('roles_users.ruid', 'desc')
                ->where("roles_users.ruid","=",$id)
                ->get();
            return view('admin.roles.users.edit')->with('data', $roleusers[0])->with('roles', $roles)->with('users', $users);
        }
    }
    
    
    /**
     * Update the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $data, $id)
    {

        if(!hasTask('update_role'))
        {
            return view('errors.404');
        }
        $taskresource=$this->getHtmlTree($this->optionTasks($tasks = null,$path = '', $level=0,$id),$id);
        $tasks=$this->getTaskList();
        $fields['role_name'] = strtolower(Input::get('role_name'));
        $fields['app_key'] = Input::get('app_key');
        $account_id = Input::get('account_id');
        $resources = Input::get('nodes');
        $resdata = Input::get('apires');
        $rules = array(
            'role_name' => 'required|regex:/(^[A-Za-z0-9 ]+$)+/|min:3|max:32|unique:user_roles,role_name,'.$id,
            'app_key' => 'required',
        );    
        $validator = Validator::make($fields, $rules);
        // process the validation
        if ($validator->fails()) {
                return Redirect::back()->withErrors($validator)->withInput();
        } else {
            // store
            $role = Role::find($id);
            $role->role_name = strtolower($_POST['role_name']);
            $role->tag_bg_color =  $_POST['tag_bg_color'];
            $role->tag_text_color =  $_POST['tag_text_color'];
            $role->rolefor =  'A';
            $role->updated_date = date("Y-m-d H:i:s");
            $role->save();
            $account_id=$this->role_save_after($role,$_POST);

            $set = array();
            if($id && count($resources)) {
                
                //$roleobject->setData($this->getRequest()->post())->save();
                foreach($resources as $res) {
                    $rs = explode("/",$res);
                    $s = '';
                    foreach($rs as $r) {
                        $s .= $r;
                        if(in_array($s,$set)) {
                            $s .= "/";
                            continue;
                        }
                        $set[] = $s;  
    
                        $s .= "/";
                    }
    
                }
            }

            /**  task info save **/
            $affected = DB::table('role_tasks')->where('role_id', '=', $id)->delete();
            if(count($set)){
                foreach($set as $path) {
                    $roletask = new Role_tasks;
                    $roletask->role_id = $id;
                    $roletask->task_definition =$path;
                    $roletask->permissions =true;
                    if(isset($tasks[$path])) {
                        $roletask->task_index=$tasks[$path];
                    }
                    $roletask->save();
                }
            }
            /**  task info save end **/

            /**  api resorece save **/
            if($account_id){     
                if(isset($resdata)) {
                    $affected = DB::table('api_resources')->where('account_id', '=', $account_id)->delete();
                    if(isset($resdata['resource']) && $resdata['resource']!=''){          
                        foreach($resdata['resource'] as $key =>  $res) {
                            foreach($res as $method => $resource) {
                                $model = new Api_resources;
                                $model->resources = $resdata['resource_index'][$key];
                                $model->method = $method;
                                $model->account_id = $account_id;
                                $model->save();
                            }
                        }
                    }
                }
            }
            /**  api resorece save end **/
            // redirect
            Session::flash('message', trans('messages.Role has been updated successfully'));
            return Redirect::to('system/permission');
        }

    }

    /**
     * Update the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function user_update(Request $data, $id)
    {
        if(!hasTask('usersupdate')){
                return view('errors.404');
        }
        $fields['role_id'] = Input::get('role_id');
        $fields['user_id'] = Input::get('user_id');
        $rules = array(
            'role_id' => 'required',
            'user_id' => 'required',
        );    
        $validator = Validator::make($fields, $rules);
        // process the validation
        if ($validator->fails()) {
                return Redirect::back()->withErrors($validator)->withInput();
        } else {

            $roles=DB::table('roles_users')
             ->select('roles_users.role_id','roles_users.user_id')
             ->where('roles_users.role_id',"=",$fields['role_id'])
             ->where('roles_users.user_id',"=",$fields['user_id'])
             ->where('roles_users.ruid',"!=",$id)
            ->get();
            
            if(count($roles)){
                $validator->errors()->add('role_id', trans('messages.This role already assigned to this uesr'));
                return Redirect::back()->withErrors($validator)->withInput();
            }
            // store
            $roles_users = Roles_users::find($id);
            $roles_users->role_id =  $_POST['role_id'];
            $roles_users->user_id =  $_POST['user_id'];
            $roles_users->updated_date = date("Y-m-d H:i:s");
            $roles_users->save();
            // redirect
            Session::flash('message', trans('messages.Role users has been updated successfully'));
            return Redirect::to('permission/users');
        }

    }
    
                /**
     * add,edit datas  saved in main table 
     * after inserted in sub tabel.
     *
     * @param  int  $id
     * @return Response
     */
   public static function role_save_after($object,$post)
   {
        $role = $object;
        $post = $post;
        if(isset($post['app_key'])){
            $app_key = $post['app_key'];
            try{
                    $roles = DB::select('select account_id from api_account where role_id = '.$role->id);
                    if(count($roles)){
                        $api_model = Api_account::find($post['account_id']);
                    }else { 
                        $api_model = new Api_account;
                    }
                    $api_model->role_id = $role->id;
                    $api_model->created_date = date("Y-m-d H:i:s");
                    $api_model->app_key = $post['app_key'];
                    $api_model->save();
                    return $api_model->account_id;
                }catch(Exception $e) {
                    Log::Instance()->add(Log::ERROR, $e);
                }
        }
   }

    /**
     * Delete the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destory($id)
    {
        if(!hasTask('system/permission/delete')){
            return view('errors.404');
        }
        $data = Role::find($id);
        $data->delete();
        Session::flash('message', trans('messages.Role has been deleted successfully!'));
        return Redirect::to('system/permission');
    }

    /**
     * Delete the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function users_destory($id)
    {
        if(!hasTask('permission/users/delete'))
        {
            return view('errors.404');
        }
        $data = Roles_users::find($id);
        $data->delete();
        Session::flash('message', trans('messages.Role uesr has been deleted successfully!'));
        return Redirect::to('permission/users');
    }
    
    
            /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxrolelist(Request $request)
    {
        $roles = DB::table('user_roles')
                    ->select('user_roles.*')
                    ->orderBy('user_roles.id', 'desc');
        return Datatables::of($roles)->addColumn('action', function ($roles) {
            if(hasTask('system/permission/edit'))
            {
                $html ='<div class="btn-group"><a href="'.URL::to("system/permission/edit/".$roles->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("system/permission/delete/".$roles->id).'" class="delete-'.$roles->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $( document ).ready(function() {
                        $(".delete-'.$roles->id.'").on("click", function(){
                            return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });
                    });
                </script>';
                return $html;
            }
        })
        ->addColumn('active_status', function ($roles) {
            if($roles->active_status==0):
                $data = '<span class="label label-danger">'.trans("messages.Inactive").'</span>';
            elseif($roles->active_status==1):
                $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
            endif;
            return $data;
        })
        ->addColumn('role_name', function ($roles) {
            return ucfirst($roles->role_name);
        })
        ->rawColumns(['role_name','active_status','action'])

        ->make(true);
    }

    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxroleuesrlist(Request $request)
    {
        $roleusers=DB::table('roles_users')
         ->select('roles_users.ruid','roles_users.user_id','user_roles.role_name','users.email','users.id')
         ->leftJoin("user_roles",'user_roles.id','=','roles_users.role_id')
         ->leftJoin('users','users.id','=','roles_users.user_id')
        ->orderBy('roles_users.ruid', 'desc');
        return Datatables::of($roleusers)->addColumn('action', function ($roleusers) {
            if(hasTask('permission/users/edit'))
            {
                $html ='<div class="btn-group"><a href="'.URL::to("permission/users/edit/".$roleusers->ruid).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("permission/users/delete/".$roleusers->ruid).'" class="delete-'.$roleusers->ruid.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $( document ).ready(function() {
                        $(".delete-'.$roleusers->ruid.'").on("click", function(){
                            return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });
                    });
                </script>'; 
                return $html;
            }
        })
        ->rawColumns(['action'])

        ->make(true);
    }

    public function tasks()
    {
        //return Config('tasks');
        return tasks();
    }

    public function optionTasks($tasks = null,$path = '', $level=0,$id='')
    {
        if(is_null($tasks))
        $tasks = $this->tasks();
        $arr   = array();
        $sortOrder = 0;
        foreach ($tasks as $taskname =>  $task)
        {
            $options = array();
            $options['id'] = $path.$taskname ;
            if(in_array($options['id'],$this->getRoleTasks($id)))
            {
                $options['checked'] = true;
            }
            $options['level'] = $level;
            $options['text']  = isset($task['title']) ? $task['title'] : '';
            $options['task_note']    =  isset($task['task_note']) ? $task['task_note'] : false;
            $options['sort_order']   = isset($task['sort']) ? (int)$task['sort'] : $sortOrder;
            $options['apiresources'] = isset($task['apiresources']) ? $task['apiresources'] : array();
            $options['index']        = false;
            if(isset($task['task_index']))
            {
                $this->_taskslist[$options['id']] = $task['task_index'];
                $options['index'] = $task['task_index'];
            }
            if(isset($task['children']))
            {
                $options['children'] = $this->optionTasks($task['children'], $path.$taskname."/",$level+1);
            }
            $arr[] = $options;
        }
        //print_r($arr); 
        //uasort($arr, array($this, '_sortMenu'));
        //print_r($_taskslist); exit;
        while (list($key, $value) = each($arr))
        {
            $last = $key;
        }
        if (isset($last))
        {
            $arr[$last]['last'] = true;
        }
        return $arr;
    }

    public function getRoleTasks($id='')
    {
        if($id){
            $roletasks=DB::table('role_tasks')
            ->select('role_tasks.task_definition')
            ->where('role_tasks.role_id', $id)
            ->get();
            $tasks_def=array();
            if(count($roletasks)){
                foreach($roletasks as $key =>$value){
                    $tasks_def[]=$value->task_definition;
                }
            }
            return $tasks_def;
        }else { 
            return array();
        }
    }

    public function getApiResources($id='')
    {
        if($id){
            $resource_def=array();
            $apiaccount=DB::table('api_account')
            ->select('api_account.account_id')
            ->where('api_account.role_id', $id)
            ->get();
            if(count($apiaccount)){
                $apiresources=DB::table('api_resources')
                ->select('api_resources.method','api_resources.resources')
                ->where('api_resources.account_id', $apiaccount[0]->account_id)
                ->get();
                if(count($apiresources)){
                    foreach($apiresources as $key =>$value){
                        $resource_def[$value->resources][]=$value->method;
                    }
                }
            }
            return $resource_def;
        }else { 
            return array();
        }
    }

    public function getHtmlTree($nodes,$id='')
    {
        $html = '';
        $db = $this->getApiResources($id);
        
        //$db=array();
        $task_list=array();
        foreach($nodes as $node) {
            if(isset($node['children']) && !empty($node['children'])) { 
                $html .= '<h5 class="lg-title mb5">'.$node['text'].'</h5>';
                if(isset($node['task_note']) && $node['task_note']) {
                    $html .='<p class="mb10">'.$node['task_note'].'</p>';
                } 
                $html .= '<table class="table table-striped table-bordered dataTable roles" id="'.str_replace("/","_",$node['id']).'">';
                $html .= '<col width="40%" />
                            <col width="15%" />
                            <col width="40%" />
                            <thead>
                                <tr>
                                    <th>'.trans('messages.Task Description').'</th>
                                    <th>'.trans('messages.Website Access').'&nbsp;<input type="checkbox" class="parentcheckbox" value="'.str_replace("/","_",$node['id']).'" /></th>
                                    <th>'.trans('messages.Api Resources').'</th>
                                </tr>
                            </thead>
                            <tbody>';
                $html.= $this->getHtmlTree($node['children'],$id);
                $html.="</tbody></table>";
            } else {

                $html.="<tr>";
                $selected = '';
                if(in_array($node['id'],$this->getRoleTasks($id))) {
                    $selected = "checked"; 
                }
                $html.= '<td><div><label for="'.$node['id'].'">'.$node['text'];
                if(isset($node['task_note']) && $node['task_note']) {
                    $html .='<p>'.$node['task_note'].'</p>';
                }
                $html .='</label></div></td>';
                $html .='<td><input type="checkbox" class="toggle" name="nodes[]" '.$selected.' data-size="small" data-on-text='.trans('messages.Yes').' data-off-text='. trans('messages.No').' data-off-color="danger" data-on-color="success" style="visibility:hidden;" value="'.$node['id'].'" id="'.$node['id'].'" /></td>';
                if(isset($node['apiresources']) && count($node['apiresources'])) { 
                    $html .='<td><table border="0" width="100%"><tr>';
                    $html .= '<td></td>'; 
                    $resources = array('GET','POST','PUT','DELETE');
                    foreach($resources as $methods) { 
                           $html .= '<td>'.$methods.'</td>'; 
                    }
                    $html .='</tr>';
                    
                    foreach($node['apiresources'] as $indexx => $res) {
                        $index = array_get($res,'index','');
                        
                        //$dbresources = array_get($db,$index,array());
                        $dbresources = $db;
                        $resource =  array_get($res,'resource',array());
                        $title =  array_get($res,'title',array());
                        $html .= '<tr>';
                        $html .= '<td>'.$title.'</td>';
                        
                        foreach($resources as $methods) {
                            if(in_array($methods,$resource)){
                                
                                $selected = '';
                                 
                                if(count($dbresources)){
                                    foreach ($dbresources as $keys => $item) {
                                        if($keys==$indexx){
                                            if(in_array($methods,$dbresources[$keys])) {
                                                $selected = "checked='checked'";
                                            }
                                        }
                                    }
                                }
                                 $html .= '<td><input type="checkbox" name="apires[resource]['.$index.']['.$methods.']"  '.$selected.' value="1"/></td>';
                                 $html .='<input type="hidden" name="apires[resource_index][]" value='.$indexx.'>';
                            } else {
                                $html .= '<td><input type="checkbox" name="apires[resource]['.$index.']['.$methods.']" disabled="disabled" value="1" /></td>';
                                $html .='<input type="hidden" name="apires[resource_index][]" value='.$indexx.'>';
                            }
                        } 
                        $html .='</tr>';
                    } 
                    $html .='</table></td>';
                } else {
                      $html .='<td>-</td>';
                }
                $html.="</tr>";
            }

        }
        return $html;
    }

    public function getTaskList()
    {
        return $this->_taskslist;
    }

}
