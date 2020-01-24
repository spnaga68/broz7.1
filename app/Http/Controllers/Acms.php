<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Model\cms;
use App\Model\cms_infos;
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
use Input;
use App;
use Yajra\Datatables\Datatables;
use URL;

class Acms extends Controller
{

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
     * Display a listing of the cms.
     *
     * @return Response
     */
    public function index()
    {
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('admin/cms')){
                return view('errors.404');
            }
            return view('cms.list');
        }
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
            if(!hasTask('admin/cms/create')){
                return view('errors.404');
            }
            return view('cms.create');
        }
    }
    /**
     * Store a newly created blog in storage.
     *
     * @return Response
     */
    public function store(Request $data)
    {

        if(!hasTask('createcms'))
        {
            return view('errors.404');
        }
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $title = Input::get('title');
        foreach ($title  as $key => $value) {
            $fields['title'.$key] = $value;
            $rules['title'.'1'] = 'required|unique:cms_infos,title';
        }
        $content = Input::get('content');
        foreach ($content  as $key => $value) {
            $fields['content'.$key] = $value;
            $rules['content'.'1'] = 'required';
        }
        $validator = Validator::make($fields, $rules);
        // process the validation
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        } else {
            // store
            $cms = new Cms;
            $cms->url_index =  $_POST['index'] ? str_slug($_POST['index']): str_slug($_POST['title'][1]);
            $cms->created_at = date("Y-m-d H:i:s");
            $cms->created_by = Auth::id();
            $cms->cms_status =  isset($_POST['status']) ? $_POST['status']: 0;
            $cms->sort_order = ($_POST['sort_order'] != '')?($_POST['sort_order']):0;
            $cms->save();
            $this->cms_save_after($cms,$_POST);
            // redirect
            Session::flash('message', trans('messages.Cms page has been created successfully'));
            return Redirect::to('admin/cms');
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
            if(!hasTask('admin/cms/view')){
                return view('errors.404');
            }
            $cms = Cms::find($id);
            $info = new Cms_infos;
            return view('cms.show')->with('data', $cms)->with('infomodel', $info);
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
            if(!hasTask('admin/cms/edit')){
                return view('errors.404');
            }
            // get the blog
             $cms = Cms::find($id);
             if(!count($cms)){
                 Session::flash('message', 'Invalid cms'); 
                 return Redirect::to('admin/cms');    
             }
             $info = new Cms_infos;
            return view('cms.edit')->with('data', $cms)->with('infomodel', $info);
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
        if(!hasTask('updatecms')){
            return view('errors.404');
        }
        $title = Input::get('title');
        foreach ($title  as $key => $value) {
            $fields['title'.$key] = $value;
            $rules['title'.'1'] = 'required|unique:cms_infos,title,'.$id.',cms_id';
        }
        $content = Input::get('content');
        foreach ($content  as $key => $value) {
            $fields['content'.$key] = $value;
            $rules['content'.'1'] = 'required';
        }
        $validation = Validator::make($fields, $rules);
        // process the validation
        if ($validation->fails()) {
              return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store datas in to database
            $cms = Cms::find($id);
            $cms->url_index =  $_POST['index'] ? str_slug($_POST['index']): str_slug($_POST['title'][1]);
            $cms->cms_status =  isset($_POST['status']) ? $_POST['status']: 0;
             $cms->sort_order =($_POST['sort_order'] != '')?($_POST['sort_order']):0;
            $cms->updated_at = date("Y-m-d H:i:s");
            $cms->save();
            $this->cms_save_after($cms,$_POST);
            // redirect
            Session::flash('message',trans('messages.Cms has been successfully updated'));
            return Redirect::to('admin/cms');
        }
    }
    /**
     * add,edit datas  saved in main table 
     * after inserted in sub tabel.
     *
     * @param  int  $id
     * @return Response
     */
   public static function cms_save_after($object,$post)
   {
        $cms = $object;
        $post = $post;
        if(isset($post['title'])){
            $cms_name = $post['title'];
            $content = $post['content'];
            $meta_keywords = $post['meta_keywords'];
            $meta_description = $post['meta_description'];
            try{
                $affected = DB::table('cms_infos')->where('cms_id', '=', $object->id)->delete();
                $languages = DB::table('languages')->where('status', 1)->get();
                foreach($languages as $key => $lang){
                    if(isset($cms_name[$lang->id]) && $cms_name[$lang->id]!=""){
                        $infomodel = new Cms_infos;
                        $infomodel->language_id = $lang->id;
                        $infomodel->cms_id = $cms->id; 
                        $infomodel->title = $cms_name[$lang->id];
                        $infomodel->content = $content[$lang->id];
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
     * Delete the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destory($id)
    {
        if(!hasTask('admin/cms/delete')){
            return view('errors.404');
        }
        $data = Cms::find($id);
        $data->delete();
        Session::flash('message',trans('messages.Cms has been deleted successfully!'));
        return Redirect::to('admin/cms');
    }
    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyCmsAjax(Request $request)
    {
        $query = '"cms_infos"."language_id" = (case when (select count(*) as totalcount from cms_infos where cms_infos.language_id = '.getAdminCurrentLang().' and cms.id = cms_infos.cms_id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $cms = Cms::Leftjoin('cms_infos','cms_infos.cms_id','=','cms.id')
                ->select('cms.*','cms_infos.*')
                ->whereRaw($query)
                ->get();
        return Datatables::of($cms)->addColumn('action', function ($cms) {
            if(hasTask('admin/cms/create'))
            {
                $html ='<div class="btn-group"><a href="'.URL::to("admin/cms/edit/".$cms->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/cms/view/".$cms->id).'" class="view-'.$cms->id.'" title="'.trans("messages.View").'"><i class="fa fa-eye"></i>&nbsp;&nbsp;'.@trans("messages.View").'</a></li>
                        <li><a href="'.URL::to("admin/cms/delete/".$cms->id).'" class="delete-'.$cms->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $( document ).ready(function() {
                        $(".delete-'.$cms->id.'").on("click", function(){
                            return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });
                    });
                </script>';
                return $html;
            }
        })
        ->addColumn('cms_status', function ($cms) {
            if($cms->cms_status==0):
                $data = '<span class="label label-danger">'.trans("messages.Inactive").'</span>';
            elseif($cms->cms_status==1):
                $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
            endif;
            return $data;
        })
        ->editColumn('title', '{!! str_limit($title, 20) !!}')
        ->rawColumns(['cms_status','action'])

        ->make(true);
    }
}
