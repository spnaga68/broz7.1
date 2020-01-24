<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Model\email_templates;
use App\Model\email_subjects;
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


class Template extends Controller
{
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
    public function index()
    {
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('admin/templates/email')){
                return view('errors.404');
            }
            return view('admin.notification.template.list');
        }
    }
    /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function anyAjaxtemplatelist()
    {
        $templates = DB::table('email_templates')->select('*')->orderBy('template_id', 'desc');
        return Datatables::of($templates)->addColumn('action', function ($templates) {
            if(hasTask('admin/templates/edit'))
            {
                $html ='<div class="btn-group"><a href="'.URL::to("admin/templates/edit/".$templates->template_id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/templates/view/".$templates->template_id).'" title="'.trans("messages.Preview Template").'"><i class="fa fa-search"></i>&nbsp;&nbsp;'.@trans("messages.Preview Template").'</a></li>
                        <li><a href="'.URL::to("admin/templates/delete/".$templates->template_id).'" class="delete-'.$templates->template_id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $( document ).ready(function() {
                        $(".delete-'.$templates->template_id.'").on("click", function(){
                            return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });
                    });
                </script>';
                return $html;
            }
        })
        ->addColumn('is_system', function ($templates) {
            if($templates->is_system==0):
                $data = '<span class="label label-danger">'.trans("messages.Custom").'</span>';
            elseif($templates->is_system==1):
                $data = '<span class="label label-success">'.trans("messages.System").'</span>';
            endif;
            return $data;
        })
        ->addColumn('from', function ($templates) {
            $data = '-';
            if($templates->from != ''):
                $data = $templates->from;
            endif;
            return $data;
        })
        ->rawColumns(['from','is_system','action'])

        ->make(true);
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
        else{
            if(!hasTask('admin/templates/create'))
            {
                return view('errors.404');
            }
            return view('admin.notification.template.create');
        }
    }
    /**
     * Store a newly created blog in storage.
     *
     * @return Response
     */
    public function store(Request $data)
    {
        if(!hasTask('createtemplate'))
        {
            return view('errors.404');
        }
        $data1=Input::all();
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $validation = Validator::make($data->all(), array(
            'ref_name' => 'required|unique:email_templates,ref_name',
            'from_email' => 'required',
            'subject'=> 'required',
            'content'=>'required',
        ));
        // process the validation
        if ($validation->fails())
        {
            //return redirect('create')->withInput($data1)->withErrors($validation);
            return Redirect::back()->withErrors($validation)->withInput();
        }
        else {
            // store
            $Templates = new Email_templates;
            $Templates->from      = $_POST['from_name'];
            $Templates->subject    = $_POST['subject'];
            $Templates->content    = $_POST['content'];
            $Templates->from_email    = $_POST['from_email'];
            $Templates->reply_to    = $_POST['reply_to'];
            $Templates->ref_name    = $_POST['ref_name'];
            $Templates->created_date = date("Y-m-d H:i:s");
            $Templates->updated_date = date("Y-m-d H:i:s");
            $Templates->is_system    = 1;
            $Templates->save();
            // redirect
            Session::flash('message', trans('messages.Template has been created successfully'));
            return Redirect::to('admin/templates/email');
        }
    }
    public function edit($id)
    {
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('admin/templates/edit')){
                return view('errors.404');
            }
            $templates = Email_templates::find($id);
            return view('admin.notification.template.edit')->with('data', $templates);
        }
    }
    /**
     * Store a newly created blog in storage.
     *
     * @return Response
     */
    public function update(Request $data,$id)
    {
        if(!hasTask('admin/template/update'))
        {
            return view('errors.404');
        }
        $data1=Input::all();
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $validation = Validator::make($data->all(), array(
            'ref_name' => 'required|unique:email_templates,ref_name,'.$id.',template_id',
            'from_email' => 'required',
            'subject'=> 'required',
            'content'=>'required',
        ));
        // process the validation
        if ($validation->fails())
        {
            //return redirect('create')->withInput($data1)->withErrors($validation);
            return Redirect::back()->withErrors($validation)->withInput();
        }
        else {
            // store
            $Templates = Email_templates::find($id); 
            $Templates->from      = $_POST['from_name'];
            $Templates->subject    = $_POST['subject'];
            $Templates->content    = $_POST['content'];
            $Templates->from_email    = $_POST['from_email'];
            $Templates->reply_to    = $_POST['reply_to'];
            $Templates->ref_name    = $_POST['ref_name'];
            $Templates->updated_date = date("Y-m-d H:i:s");
            $Templates->is_system    = 1;
            $Templates->save();
            // redirect
            Session::flash('message', trans('messages.Template has been updated successfully'));
            return Redirect::to('admin/templates/email');
        }
    }
    /* To remove the template */
    public function destroy($id)
    {
        if(!hasTask('admin/templates/delete')){
            return view('errors.404');
        }
        $groups = DB::select('select template_id from email_templates where template_id = '.$id);
        if(count($groups))
        {
            DB::table('email_templates')->where('template_id', '=', $id)->delete();
            Session::flash('message', trans('messages.Template has been deleted successfully!'));
            return Redirect::to('admin/templates/email');
        }
        else {
            Session::flash('message', trans('messages.No template found'));
            return Redirect::to('admin/templates/email');
        }
    }
    
    public function view($id)
    {
        if(!hasTask('admin/templates/view')){
            return view('errors.404');
        }
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            $templates = Email_templates::find($id);
            return view('admin.notification.template.view')->with('data', $templates);
        }
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function subject_index()
    {    
         if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('admin/template/subjects')){
                return view('errors.404');
            }
            return view('admin.notification.subjects.list');
        }
    }
    
    /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function anyAjaxsubjectlist()
    {
        $email_subjects = DB::table('email_subject')->select('*')->orderBy('id', 'desc');
        return Datatables::of($email_subjects)->addColumn('action', function ($email_subjects) {
            if(hasTask('admin/subjects/edit')){
                $html='<div class="btn-group"><a href="'.URL::to("admin/subjects/edit/".$email_subjects->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                </div>';
                return $html;
            }
        })
        ->addColumn('status', function ($email_subjects) {
            if($email_subjects->status==0):
                $data = '<span class="label label-danger">'.trans("messages.Disabled").'</span>';
            elseif($email_subjects->status==1):
                $data = '<span class="label label-success">'.trans("messages.Enabled").'</span>';
            endif;
            return $data;
        })
                ->rawColumns(['status','action'])

        ->make(true);
    }
    
          /**
     * Show the form for creating a new blog.
     *
     * @return Response
     */
    public function subject_create()
    { 
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('admin/subjects/create')){
                return view('errors.404');
            }
            return view('admin.notification.subjects.create');
        }
    }
    
                /**
     * Store a newly created blog in storage.
     *
     * @return Response
     */
    public function subject_store(Request $data)
    {
        if(!hasTask('createsubject')){
                return view('errors.404');
        }
        $data1=Input::all();
        // validate
        // read more on validation at http://laravel.com/docs/validation        
        $validation = Validator::make($data->all(), array(
            'subject_type' => 'required|unique:email_subject,subject_type',
            'template_id' => 'required',
            'color_code'=> 'required',
        ));
        // process the validation
        if ($validation->fails()) {
                //return redirect('create')->withInput($data1)->withErrors($validation);
                return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store
            $subject_type = explode('|',$_POST['subject_type']);
            $Subjects = new Email_subjects;
            $Subjects->template_id  = $_POST['template_id'];
            $Subjects->subject_type = $subject_type[0];
            $Subjects->subject_index = $subject_type[1];
            $Subjects->color_code = $_POST['color_code'];
            $Subjects->status = $_POST['status'];
            $Subjects->save();
            // redirect
            Session::flash('message', trans('messages.Subjects has been created successfully'));
            return Redirect::to('admin/template/subjects');
        }
    }
    
    public function subject_edit($id)
    {
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('admin/subjects/edit')){
                return view('errors.404');
            }
            $subjects = Email_subjects::find($id);
            return view('admin.notification.subjects.edit')->with('data', $subjects);
        }
    }
    
        /**
     * Store a newly created blog in storage.
     *
     * @return Response
     */
    public function subject_update(Request $data,$id)
    {
        if(!hasTask('admin/subjects/update')){
            return view('errors.404');
        }
        $data1=Input::all();
        // validate
        // read more on validation at http://laravel.com/docs/validation        
        $validation = Validator::make($data->all(), array(
            'subject_type' => 'required|unique:email_subject,subject_type,'.$id.',id',
            'template_id' => 'required',
            'color_code'=> 'required',
        ));
        // process the validation
        if ($validation->fails()) {
            //return redirect('create')->withInput($data1)->withErrors($validation);
            return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store
            $Subjects = Email_subjects::find($id); 
            $subject_type = explode('|',$_POST['subject_type']);
            $Subjects->template_id  = $_POST['template_id'];
            $Subjects->subject_type = $subject_type[0];
            $Subjects->subject_index = $subject_type[1];
            $Subjects->color_code = $_POST['color_code'];
            $Subjects->status = $_POST['status'];
            $Subjects->save();
            // redirect
            Session::flash('message', trans('messages.Subject has been updated successfully'));
            return Redirect::to('admin/template/subjects');
        }
    }
}
