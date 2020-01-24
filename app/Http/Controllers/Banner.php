<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Model\users;
use App\Model\banners;
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


class Banner extends Controller
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
            if(!hasTask('admin/banners')){
                return view('errors.404');
            }
            return view('admin.banner.list');
        }
    }
    
         /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function anyAjaxbannerlist()
    {
        $banners = DB::table('banner_settings')->select('banner_setting_id', 'banner_title', 'banner_subtitle', 'banner_link', 'banner_type', 'created_date', 'default_banner', 'status')->orderBy('banner_setting_id', 'desc');
        return Datatables::of($banners)->addColumn('action', function ($banners) {
            if(hasTask('admin/banner/edit'))
            {
                $html ='<div class="btn-group"><a href="'.URL::to("admin/banner/edit/".$banners->banner_setting_id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/banner/delete/".$banners->banner_setting_id).'" class="delete-'.$banners->banner_setting_id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $( document ).ready(function() {
                        $(".delete-'.$banners->banner_setting_id.'").on("click", function(){
                            return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });
                    });
                </script>';
                return $html;
            }
        })
        ->addColumn('status', function ($banners) {
            if($banners->status==0):
                $data = '<span class="label label-danger">'.trans("messages.Inactive").'</span>';
            elseif($banners->status==1):
                $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
            endif;
            return $data;
        })
        ->addColumn('banner_type', function ($banners) {
            $data='--';
            if($banners->banner_type==1):
                $data = 'Common';
            elseif($banners->banner_type==2):
                $data = 'Store';
            endif;
            return $data;
        })
        ->addColumn('default_banner', function ($banners) {
            $checked = '';
            if($banners->default_banner):
                $checked = 'checked=checked';
            elseif($banners->status):
                $checked = '';
            endif;
            if(hasTask('admin/banner/edit'))
            {
                $html = '<span class="label label-success">'.trans("messages.Success").'</span>';
                $data = '<input type="checkbox" value="1" id='.$banners->banner_setting_id.'  '.$checked.' class="default-'.$banners->banner_setting_id.' example"><span class="responce-'.$banners->banner_setting_id.'"> </span>
                    <script type="text/javascript">
                        $( document ).ready(function() {
                            $(".default-'.$banners->banner_setting_id.'").on("click", function(){
                                $(".example").not(this).prop("checked", false);  
                                var value= $( this ).val();
                                var id = $(this).attr("id");
                                var token;
                                var token = $("input[name=_token]").val();
                                var url = "'.URL::to("admin/banner/ajaxupdate/").'";
                                $.ajax({
                                    url: url,
                                    type: "post",
                                    data: {"value":value,"id":id,"_token":token},
                                    dataType:"json",
                                    success: function(d) {
                                        if(d.data){
                                            $(".responce-'.$banners->banner_setting_id.'").html();
                                        }
                                    }
                                });
                            });
                        });
                    </script>';
                return $data;
            }
        })
         ->rawColumns(['default_banner','banner_type','status','action'])

        ->make(true);
    }
    /**
     * Show the form for creating a new blog.
     *
     * @return Response
     */
    public function create()
    { 
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('admin/banner/create')){
                return view('errors.404');
            }
            return view('admin.banner.create');
        }
    }
    /**
     * Store a newly created blog in storage.
     *
     * @return Response
     */
    public function store(Request $data)
    {
        if(!hasTask('createbanner'))
        {
            return view('errors.404');
        }
        $data1=Input::all();
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $validation = Validator::make($data->all(), array(
            'banner_title' => 'required|unique:banner_settings,banner_title',
            'banner_subtitle' => 'required|unique:banner_settings,banner_subtitle',
            'banner_type' => 'required',
            'banner_image'=> 'required|mimes:png,jpg,jpeg|max:2024',
            'language_type' => 'required',
        ));
        // process the validation
        if ($validation->fails()) {
                //return redirect('create')->withInput($data1)->withErrors($validation);
                return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store
            $Banners = new Banners;
            $Banners->banner_title      = $_POST['banner_title'];
            $Banners->banner_subtitle    = $_POST['banner_subtitle'];
            $Banners->banner_type    = $_POST['banner_type'];
            $Banners->banner_link    = $_POST['banner_link'];
            $Banners->language_type    = $_POST['language_type'];
            $Banners->created_date = date("Y-m-d H:i:s");
            $Banners->updated_date = date("Y-m-d H:i:s");
            $Banners->status    = isset($_POST['status']);
            $Banners->save();
            $imageName = $Banners->banner_setting_id . '.' . 
            $data->file('banner_image')->getClientOriginalExtension();
            $data->file('banner_image')->move(
                base_path() . '/public/assets/admin/base/images/banner/', $imageName
            );
            $destinationPath1 = url('/assets/admin/base/images/banner/'.$imageName.'');
            $size=getImageResize('BANNER');
            Image::make( $destinationPath1 )->fit($size['LIST_WIDTH'], $size['LIST_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/banner/thumb/'.$imageName)->destroy();
            $Banners->banner_image=$imageName;
            $Banners->save();
            // redirect
            Session::flash('message', trans('messages.Banner has been created successfully'));
            return Redirect::to('admin/banners');
        }
    }
    
    public function edit($id)
    {
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('admin/banner/edit')){
                return view('errors.404');
            }
            $banners = Banners::find($id);
            return view('admin.banner.edit')->with('data', $banners);
        }
    }
    /**
     * Store a newly created blog in storage.
     *
     * @return Response
     */
    public function update(Request $data,$id)
    {
        if(!hasTask('admin/banner/update')){
            return view('errors.404');
        }
        $data1=Input::all();
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $validation = Validator::make($data->all(), array(
            'banner_title' => 'required|unique:banner_settings,banner_title,'.$id.',banner_setting_id',
            'banner_type' => 'required',
            'banner_subtitle' => 'required|unique:banner_settings,banner_subtitle,'.$id.',banner_setting_id',
            'banner_image'=> 'mimes:png,jpg,jpeg|max:2024',
        ));
        // process the validation
        if ($validation->fails()) {
            //return redirect('create')->withInput($data1)->withErrors($validation);
            return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store
            $Banners = Banners::find($id); 
            $Banners->banner_title      = $_POST['banner_title'];
            $Banners->banner_subtitle    = $_POST['banner_subtitle'];
            $Banners->banner_type    = $_POST['banner_type'];
            $Banners->banner_link    = $_POST['banner_link'];
             $Banners->language_type    = $_POST['language_type'];
            $Banners->updated_date = date("Y-m-d H:i:s");
            $Banners->status    = isset($_POST['status']);
            $Banners->save();
            if(isset($_FILES['banner_image']['name']) && $_FILES['banner_image']['name']!=''){
                $imageName = $Banners->banner_setting_id . '.' . 
                $data->file('banner_image')->getClientOriginalExtension();
                $data->file('banner_image')->move(
                    base_path() . '/public/assets/admin/base/images/banner/', $imageName
                );
                $destinationPath1 = url('/assets/admin/base/images/banner/'.$imageName.'');
                $size=getImageResize('BANNER');
                Image::make( $destinationPath1 )->fit($size['LIST_WIDTH'], $size['LIST_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/banner/thumb/'.$imageName)->destroy();
                $Banners->banner_image=$imageName;
                $Banners->save();
            }
            // redirect
            Session::flash('message', trans('messages.Banner has been updated successfully'));
            return Redirect::to('admin/banners');
        }
    }
    //Get city list for ajax request
    public function ajaxupdate(Request $request)
    {
        if($request->ajax())
        {
            $value = $request->input('value');
            $id = $request->input('id');
            $Banners = Banners::find($id); 
            $Banners->default_banner    = $value;
            $Banners->save();
            DB::table('banner_settings')
            ->where('banner_setting_id','!=',$id)
            ->update(['default_banner' => 0]);
            return response()->json([
                'data' => true,
            ]);
        }
    }


    public function destory($banner_setting_id)
    {
        if(!hasTask('admin/banners/delete')){
            return view('errors.404');
        }
        $data    = Banners::find($banner_setting_id);
        $Banners = DB::select('select banner_setting_id,banner_image from banner_settings where banner_setting_id = '.$banner_setting_id.'');
        if(count($Banners)){
            if(file_exists(base_path().'/public/assets/admin/base/images/banner/'.$Banners[0]->banner_image)) {
                unlink(base_path() .'/public/assets/admin/base/images/banner/'.$Banners[0]->banner_image);
            }
            if(file_exists(base_path() .'/public/assets/admin/base/images/banner/detail/'.$Banners[0]->banner_image)) {
                unlink(base_path() .'/public/assets/admin/base/images/banner/detail/'.$Banners[0]->banner_image);
            }
            if(file_exists(base_path() .'/public/assets/admin/base/images/banner/thumb/'.$Banners[0]->banner_image)) {
                unlink(base_path() .'/public/assets/admin/base/images/banner/thumb/'.$Banners[0]->banner_image);
            }
        }
        $data->delete();
        Session::flash('message', trans('messages.Banner has been deleted successfully!'));
        return Redirect::to('admin/banners');
    }
    
}
