<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Model\blogs;
use App\Model\blog_infos;
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
use App;
use Illuminate\Support\Facades\Input;
use Yajra\Datatables\Datatables;
use URL;

class Blog extends Controller
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
     * Display a listing of the blogs.
     *
     * @return Response
     */
    public function index()
    {
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('admin/blog')){
                return view('errors.404');
            }
            return view('blog.list');
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
            if(!hasTask('admin/blog/create')){
                return view('errors.404');
            }
            MetaTag::set('title', 'Nextbrain - Blog');
            MetaTag::set('description', 'Nextbrain - Blog Blog');
            $type = 3;
            $query = '"categories_infos"."language_id" = (case when (select count(*) as totalcount from categories_infos where categories_infos.language_id = '.getAdminCurrentLang().' and categories.id = categories_infos.category_id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $category = DB::table('categories')
                        ->select('categories.*','categories_infos.*')
                        ->leftJoin('categories_infos','categories_infos.category_id','=','categories.id')
                        ->whereRaw($query)
                        ->where("categories.category_type","=",$type)
                        ->orderBy('categories_infos.category_name', 'asc')
                        ->get();
            return view('blog.create')->with('category', $category);
        }
    }
    
    /**
     * Store a newly created blog in storage.
     *
     * @return Response
     */
    public function store(Request $data)
    {
        if(!hasTask('createblog')){
            return view('errors.404');
        }    
        $fields['category_ids'] = Input::get('category_ids');
        $fields['image'] = Input::file('image');
        $rules = array(
            'category_ids' => 'required',
            'image'       => 'required|mimes:png,jpg,jpeg|max:2024',
        );
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $title = Input::get('title');
        foreach ($title  as $key => $value) {
            $fields['title'.$key] = $value;
            $rules['title'.'1'] = 'required|unique:blog_infos,title';
        }
        $short_notes = Input::get('short_notes');
        foreach ($short_notes  as $key => $value) {
            $fields['short_notes'.$key] = $value;
            $rules['short_notes'.'1'] = 'required';
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
            $blog = new Blogs;
            $blog->url_index =  $_POST['index'] ? str_slug($_POST['index']): str_slug($_POST['title'][1]);
            $blog->created_at = date("Y-m-d H:i:s");
            $blog->category_ids = implode(',',$_POST['category_ids']);
			//$blog->blog_link    = $_POST['blog_link'];
            $blog->created_by = Auth::id();
            $blog->status =  isset($_POST['status']) ? $_POST['status']: 0;
            $blog->save();
            $this->blog_save_after($blog,$_POST);
            $imageName = $blog->id . '.' . 
            $data->file('image')->getClientOriginalExtension();
            $data->file('image')->move(
                base_path() . '/public/assets/admin/base/images/blog/', $imageName
            );
            $destinationPath1 = url('/assets/admin/base/images/blog/'.$imageName.'');
            Image::make( $destinationPath1 )->fit(50, 50)->save(base_path() .'/public/assets/admin/base/images/blog/thumb/'.$imageName)->destroy();
            Image::make( $destinationPath1 )->fit(555, 335)->save(base_path() .'/public/assets/admin/base/images/blog/list/'.$imageName)->destroy();
            Image::make( $destinationPath1 )->fit(1600, 546)->save(base_path() .'/public/assets/admin/base/images/blog/914_649/'.$imageName)->destroy();
            Image::make( $destinationPath1 )->fit(686, 323)->save(base_path() .'/public/assets/admin/base/images/blog/686_323/'.$imageName)->destroy();
            $blog->image = $imageName;
            $blog->save();
            // redirect
            Session::flash('message', trans('messages.Blog has been created successfully'));
            return Redirect::to('admin/blog');
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
            if(!hasTask('admin/blog/view')){
                    return view('errors.404');
            }
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
            if(!hasTask('admin/blog/edit')){
                    return view('errors.404');
            }
            // get the blog
             $blogs = Blogs::find($id);
             if(!count($blogs)){
                 Session::flash('message', 'Invalid blog'); 
                 return Redirect::to('admin/blog');    
             }
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
            return view('blog.edit')->with('data', $blogs)->with('infomodel', $info)->with('category', $category);
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
        if(!hasTask('updateblog')){
                    return view('errors.404');
        }    
        $fields['category_ids'] = Input::get('category_ids');
        $fields['image'] = Input::file('image');
        $rules = array(
            'category_ids' => 'required',
            'image'       => 'mimes:png,jpg,jpeg|max:2024',
        );
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $title = Input::get('title');
        foreach ($title  as $key => $value) {
            $fields['title'.$key] = $value;
            $rules['title'.'1'] = 'required|unique:blog_infos,title,'.$id.',blog_id';
        }
        $short_notes = Input::get('short_notes');
        foreach ($short_notes  as $key => $value) {
            $fields['short_notes'.$key] = $value;
            $rules['short_notes'.'1'] = 'required';
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
            
            // store datas in to database
            $blog = Blogs::find($id);
            $blog->url_index =  $_POST['index'] ? str_slug($_POST['index']): str_slug($_POST['title'][1]);
			//$blog->blog_link    = $_POST['blog_link'];
            $blog->updated_at = date("Y-m-d H:i:s");
            $blog->category_ids = implode(',',$_POST['category_ids']);
            $blog->status =  isset($_POST['status']) ? $_POST['status']: 0;
            $blog->save();
            $this->blog_save_after($blog,$_POST);
            if(isset($_FILES['image']['name']) && $_FILES['image']['name']!=''){ 
                $destinationPath = base_path() .'/public/assets/admin/base/images/blog/'; // upload path
                $imageName = $blog->id . '.' . 
                $data->file('image')->getClientOriginalExtension();
                $data->file('image')->move(base_path() . '/public/assets/admin/base/images/blog/', $imageName);
                //$data->file('image')->move($destinationPath, $imageName);
                $destinationPath1 = url('/assets/admin/base/images/blog/'.$imageName.'');
                Image::make( $destinationPath1 )->fit(50, 50)->save(base_path() .'/public/assets/admin/base/images/blog/thumb/'.$imageName)->destroy();
                Image::make( $destinationPath1 )->fit(555, 335)->save(base_path() .'/public/assets/admin/base/images/blog/list/'.$imageName)->destroy();
                Image::make( $destinationPath1 )->fit(1600, 546)->save(base_path() .'/public/assets/admin/base/images/blog/914_649/'.$imageName)->destroy();
                Image::make( $destinationPath1 )->fit(686, 323)->save(base_path() .'/public/assets/admin/base/images/blog/686_323/'.$imageName)->destroy();
                $blog->image = $imageName;
                $blog->save();
            }
            // redirect            
            Session::flash('message', trans('messages.Blog has been successfully updated'));
            return Redirect::to('admin/blog');
        }
    }
    
                /**
     * add,edit datas  saved in main table 
     * after inserted in sub tabel.
     *
     * @param  int  $id
     * @return Response
     */
   public static function blog_save_after($object,$post)
   {
        $blog = $object;
        $post = $post;
        if(isset($post['title'])){
            $blog_name = $post['title'];
            $content = $post['content'];
            $short_notes = $post['short_notes'];
            try{
                $affected = DB::table('blog_infos')->where('blog_id', '=', $object->id)->delete();
                $languages = DB::table('languages')->where('status', 1)->get();
                foreach($languages as $key => $lang){
                    if(isset($blog_name[$lang->id]) && $blog_name[$lang->id]!=""){
                        $infomodel = new Blog_infos;
                        $infomodel->language_id = $lang->id;
                        $infomodel->blog_id = $blog->id; 
                        $infomodel->title = $blog_name[$lang->id];
                        $infomodel->content = $content[$lang->id];
                        $infomodel->short_notes = $short_notes[$lang->id];
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

        if(!hasTask('admin/blog/delete')){
                    return view('errors.404');
        }
        $data = Blogs::find($id);
        $blogs = DB::select('select id,image from blogs where id = '.$id.'');
        if(count($blogs)){
            if(file_exists(base_path().'/public/assets/admin/base/images/blog/'.$blogs[0]->image)) {
                unlink(base_path() .'/public/assets/admin/base/images/blog/'.$blogs[0]->image);
            }
            if(file_exists(base_path() .'/public/assets/admin/base/images/blog/detail/'.$blogs[0]->image)) {
                unlink(base_path() .'/public/assets/admin/base/images/blog/detail/'.$blogs[0]->image);
            }
            if(file_exists(base_path() .'/public/assets/admin/base/images/blog/list/'.$blogs[0]->image)) {
                unlink(base_path() .'/public/assets/admin/base/images/blog/list/'.$blogs[0]->image);
            }
            if(file_exists(base_path() .'/public/assets/admin/base/images/blog/thumb/'.$blogs[0]->image)) {
                unlink(base_path() .'/public/assets/admin/base/images/blog/thumb/'.$blogs[0]->image);
            }
            if(file_exists(base_path() .'/public/assets/admin/base/images/blog/914_649/'.$blogs[0]->image)) {
                unlink(base_path() .'/public/assets/admin/base/images/blog/914_649/'.$blogs[0]->image);
            }
            if(file_exists(base_path() .'/public/assets/admin/base/images/blog/686_323/'.$blogs[0]->image)) {
                unlink(base_path() .'/public/assets/admin/base/images/blog/686_323/'.$blogs[0]->image);
            }
        }
        $data->delete();
        Session::flash('message', trans('messages.Blog has been deleted successfully!'));
        return Redirect::to('admin/blog');
    }
    
    
            /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxbloglist(Request $request)
    {
        
        $query = '"blog_infos"."language_id" = (case when (select count(*) as totalcount from blog_infos where blog_infos.language_id = '.getAdminCurrentLang().' and blogs.id = blog_infos.blog_id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $blogs=Blogs::Leftjoin('blog_infos','blog_infos.blog_id','=','blogs.id')
             ->select('blogs.*','blog_infos.*')
            ->whereRaw($query)
            ->orderBy('blogs.id', 'desc')
            ->get();
        return Datatables::of($blogs)->addColumn('action', function ($blogs) {
            if(hasTask('admin/blog/create'))
            {
                $html ='<div class="btn-group"><a href="'.URL::to("admin/blog/edit/".$blogs->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/blog/view/".$blogs->id).'" class="view-'.$blogs->id.'" title="'.trans("messages.View").'"><i class="fa fa-eye"></i>&nbsp;&nbsp;'.@trans("messages.View").'</a></li>
                        <li><a href="'.URL::to("admin/blog/delete/".$blogs->id).'" class="delete-'.$blogs->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $( document ).ready(function() {
                        $(".delete-'.$blogs->id.'").on("click", function(){
                            return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });
                    });
                </script>';
                return $html;
            }
        })
        ->addColumn('categories', function ($blogs) {
            $category = '';
            $category_list = explode(',',$blogs->category_ids);
            if(count($category_list) > 0 )
            {
                foreach($category_list as $cat)
                {
                    $category_det = getCategoryListsById($cat);
                    if(count($category_det) > 0)
                        $category .= ucfirst($category_det->category_name).', ';
                }
            }
            else {
                $category = '-';
            }
            $category = wordwrap(rtrim($category,', '),50,'<br>\n');
            return $category;
        })
        ->addColumn('status', function ($blogs) {
            if($blogs->status==0):
                $data = '<span class="label label-danger">'.trans("messages.Inactive").'</span>';
            elseif($blogs->status==1):
                $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
            endif;
            return $data;
        })
        ->editColumn('title', '{!! str_limit($title, 30) !!}')
        ->editColumn('url_index', '{!! str_limit($url_index, 30) !!}')
         ->rawColumns(['status','categories','action'])

        ->make(true);
    }
}
