<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Model\categories;
use App\Model\categories_infos;
use App\Http\Requests;
use Session;
use Closure;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon ;
use Image;
use MetaTag;
use SEO;
use SEOMeta;
use OpenGraph;
use Twitter;
use Input;
use App;
use Yajra\Datatables\Datatables;
use URL;


class Category extends Controller
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
        Twitter::setSite($this->site_name);
        App::setLocale('en');
    }
    
    /**
     * Display a listing of the categorys.
     * @return Response
     */
    public function index()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('admin/category'))
            {
                return view('errors.404');
            }
            return view('category.list');
        }
    }
    
    
    /**
     * Show the form for creating a new category.
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
            if(!hasTask('admin/category/create'))
            {
                return view('errors.404');
            }
            // load the create form (resources/views/category/create.blade.php)
            return view('category.create');
        }
    }
    
    /**
     * Store a newly created category in storage.
     *
     * @return Response
     */
    public function store(Request $data)
    {
        if(!hasTask('createcategory'))
        {
            return view('errors.404');
        }
        //$fields['head_category']  = Input::get('head_category');
        $fields['category_type']  = Input::get('category_type');
        $fields['category_image'] = Input::file('category_image');
        $fields['mobile_banner_image'] = Input::file('mobile_banner_image');
        $fields['category_white_image'] = Input::file('category_white_image');
        $fields['sort_order']     = Input::get('sort_order');
        $fields['is_category_type'] = Input::get('is_category_type');
        $fields['main_category']  = Input::get('main_category');
        $fields['head_category']  = Input::get('head_category');
        $rules = array(
            'category_type'  => 'required',
            'category_image' => 'required|mimes:png,jpeg,bmp|max:2024',
            'mobile_banner_image' => 'required|mimes:png,jpeg,bmp|max:2024',
            //'category_white_image' => 'required|mimes:png,jpeg,bmp|max:2024',
           'sort_order'     => 'min:1 | max :100',
        );
        if(Input::get('category_type')==1)
        {
            $rules = array(
                'is_category_type' => 'required',
                'head_category' => 'required',
            );
            if(Input::get('is_category_type')==1)
            {
                $rules = array(
                    'main_category' => 'required',
                    'head_category' => 'required',
                );
            }
        }
        $category_name = Input::get('category_name');
        foreach ($category_name  as $key => $value)
        {
            $fields['category_name'.$key] = $value;
            $rules['category_name'.'1']   = 'required';
        }
        $description = Input::get('description');
        foreach ($description  as $key => $value)
        {
            $fields['description'.$key] = $value;
            $rules['description'.'1']   = 'required';
        }
        $validation = Validator::make($fields, $rules);

        // process the validation
        if ($validation->fails()) 
        {
            //return redirect('categorycreate')->withErrors($validation);
            return Redirect::back()->withErrors($validation)->withInput();
        }
        else 
        {  
            //echo '<pre>'; print_r($_POST);exit;
			if($_POST['category_type'] == 1)
            {  
				if(count($_POST['head_category']) > 0)
				{
					$ii = 0;
					foreach($_POST['head_category'] as $procat)
					{
						$category = new Categories;
                        //echo"<pre>"; print_r($category);exit;
						$category->parent_id  = $procat;
						//$category->category    = $procat->id;
						$category->category_type = $_POST['category_type'];
						if($_POST['sort_order'] != '')
						$category->sort_order = $_POST['sort_order'];
						$category->url_key       = $_POST['url_key'] ? str_slug($_POST['url_key']): str_slug($_POST['category_name'][1]);
						$category->category_status = isset($_POST['status']) ? $_POST['status']: 0;
						$category->created_at = date('Y-m-d H:i:s');
						$category->created_by = Auth::id();
						if($_POST['is_category_type'] == 1)
						{
						$category->category_level = 3;
                            if(isset($_POST['head_category']) && $_POST['head_category']!=''){
                                $category->parent_id  = $procat;
                            }
                            if(isset($_POST['main_category']) && $_POST['main_category']!=''){
                                $category->head_category_ids = $_POST['main_category'];
                            }
						}
						else if($_POST['is_category_type'] == 0)
						{
							$category->category_level =2;
                            if(isset($_POST['head_category']) && $_POST['head_category']!=''){
                                $category->parent_id  = $procat;
                            }
                            if(isset($_POST['main_category']) && $_POST['main_category']!=''){
                                $category->head_category_ids = $procat;
                            }   
					
						}
						$category->save();
						if(isset($_FILES['category_image']['name']) && $_FILES['category_image']['name']!='')
						{
							$imageName = $category->id.'.'.$data->file('category_image')->getClientOriginalExtension();
							if($ii != 0)
							{
								Image::make( $destinationPath )->save(base_path() .'/public/assets/admin/base/images/category/'.$imageName);
							}
							else {
								$destinationPath = base_path().'/public/assets/admin/base/images/category/'.$imageName;
								$data->file('category_image')->move(base_path().'/public/assets/admin/base/images/category/', $imageName);
								Image::make( $destinationPath )->save(base_path() .'/public/assets/admin/base/images/category/'.$imageName);
							}
							$destinationPath = base_path().'/public/assets/admin/base/images/category/'.$imageName; // upload path
							$category->image = $imageName;
							$category->save();
							
						}
						
						if(isset($_FILES['mobile_banner_image']['name']) && $_FILES['mobile_banner_image']['name']!='')
						{
							$mobileimageName = $category->id.'.'.$data->file('mobile_banner_image')->getClientOriginalExtension();
							if($ii != 0)
							{
								Image::make($destinationPath1)->save(base_path() .'/public/assets/admin/base/images/category/mobile_banner/'.$mobileimageName);
							}
							else {
								$data->file('mobile_banner_image')->move(base_path().'/public/assets/admin/base/images/category/mobile_banner/', $mobileimageName);
							}
							$destinationPath1 = base_path().'/public/assets/admin/base/images/category/mobile_banner/'.$mobileimageName; // upload path
							$category->mobile_banner_image = $mobileimageName;
							$category->save();
						}
						$this->category_save_after($category,$_POST);
						$ii++;
			        }
			  }
		}	
			else
			{
				$category = new Categories;
				$category->category_type = $_POST['category_type'];
				if($_POST['sort_order'] != '')
					$category->sort_order = $_POST['sort_order'];
				$category->url_key       = $_POST['url_key'] ? str_slug($_POST['url_key']): str_slug($_POST['category_name'][1]);
				$category->category_status = isset($_POST['status']) ? $_POST['status']: 0;
				$category->created_at = date('Y-m-d H:i:s');
				$category->created_by = Auth::id();
				$category->save();
				
			if(isset($_FILES['category_image']['name']) && $_FILES['category_image']['name']!='')
            { 
               
                $imageName = $category->id . '.' . 
                $data->file('category_image')->getClientOriginalExtension();
                $data->file('category_image')->move(base_path().'/public/assets/admin/base/images/category/', $imageName);
                $destinationPath = url('/assets/admin/base/images/category/', $imageName); // upload path
                Image::make( $destinationPath )->save(base_path() .'/public/assets/admin/base/images/category/'.$imageName);
                $category->image = $imageName;
                $category->save();
            }
				if(isset($_FILES['category_white_image']['name']) && $_FILES['category_white_image']['name']!='')
						{
						    $destinationPath = base_path().'/public/assets/admin/base/images/category/white_category/';
							$whitecategoryName = $category->id.'.'.$data->file('category_white_image')->getClientOriginalExtension();
							$data->file('category_white_image')->move(base_path().'/public/assets/admin/base/images/category/white_category', $imageName);
							$category->category_white_image = $whitecategoryName;
							$category->save();
							
						}
				if(isset($_FILES['mobile_banner_image']['name']) && $_FILES['mobile_banner_image']['name']!='')
				{
					$destinationPath = base_path() .'/public/assets/admin/base/images/category/mobile_banner/'; // upload path
					$mobileimageName = $category->id.'.'.$data->file('mobile_banner_image')->getClientOriginalExtension();
					$data->file('mobile_banner_image')->move(base_path().'/public/assets/admin/base/images/category/mobile_banner/', $mobileimageName);
					$category->mobile_banner_image = $mobileimageName;
					$category->save();
				}
				$this->category_save_after($category,$_POST);
		}
            // redirect
            Session::flash('message', trans('messages.Category has been created successfully'));
            return Redirect::to('admin/category');
        }
    }
    
    /**
     * Display the specified category.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        if(!hasTask('admin/category/edit')){
            return view('errors.404');
        }
        // get the category
        $category = DB::select('select * from  categories where id = "'.$id.'"');
        // show the view and pass the category to it
        return view('category.show')->with('data', $category);
    }
    
    
    /**
     * Show the form for editing the specified category.
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
            if(!hasTask('admin/category/edit'))
            {
                return view('errors.404');
            }
            //$category = Categories::find($id);
			$category = DB::select('SELECT "cat"."id", "categories_infos"."category_name","cat"."head_category_ids", "cat"."url_key", cat_main.id AS main_category_id, cat_head.id AS head_category_id, "cat"."category_type", "cat"."parent_id", "cat"."sort_order", "cat"."image", "cat"."category_white_image","cat"."category_status", "cat"."category_level", "cat"."parent_id", "cat"."mobile_banner_image" FROM categories cat
            LEFT JOIN "categories_infos" ON "categories_infos"."category_id" = "cat"."id"   
            LEFT JOIN categories cat_main ON cat_main.id = cat.parent_id
            LEFT JOIN categories cat_head ON cat_head.id = cat_main.parent_id
            where cat.id = ?',array($id));


            //print_r($category); exit;
            if(!count($category))
            {
                Session::flash('message', 'Invalid cms'); 
                return Redirect::to('admin/cms');
            }
            $info = new Categories_infos;
            return view('category.edit')->with('data', $category[0])->with('infomodel', $info);
        }
    }
    
    
    /**
     * Update the specified category in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $data, $id)
    {
        if(!hasTask('updatecategory'))
        {
            return view('errors.404');
        }
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $fields['head_category']  = Input::get('head_category');
        $fields['category_type']  = Input::get('category_type');
        $fields['category_image'] = Input::file('category_image');
        $fields['mobile_banner_image'] = Input::file('mobile_banner_image');
        $fields['sort_order']     = Input::get('sort_order');
        $fields['is_category_type'] = Input::get('is_category_type');
        $fields['main_category']  = Input::get('main_category');
        $fields['head_category']  = Input::get('head_category');
        $rules = array(
            
            'category_type'  => 'required',
            'category_image' => 'mimes:png,jpeg,bmp|max:2024',
            'mobile_banner_image' => 'mimes:png,jpeg,bmp|max:2024',
            'sort_order'     => 'integer',
        );
        if(Input::get('category_type')==1)
        {
            $rules = array(
                'is_category_type' => 'required',
                'head_category' => 'required',
            );
            if(Input::get('is_category_type')==1)
            {
                $rules = array(
                    'main_category' => 'required',
                    'head_category' => 'required',
                );
            }
        }
        $category_name = Input::get('category_name');
        foreach ($category_name  as $key => $value)
        {
            $fields['category_name'.$key] = $value;
           $rules['category_name'.'1'] = 'required';
        }
        $description = Input::get('description');
        foreach ($description  as $key => $value)
        {
            $fields['description'.$key] = $value;
            $rules['description'.'1'] = 'required';
        }
        $validation = Validator::make($fields, $rules);
        // process the validation
        if ($validation->fails())
        {
            return Redirect::back()->withErrors($validation)->withInput();
        }
        else {
 // echo '<pre>'; print_r($_POST);exit;
			//print_r($_POST); exit;
            // store datas in to database
            $category = Categories::find($id);
            $category->category_type    = $_POST['category_type'];
            if($category->category_type != 1)
            {
                $_POST['head_category'] = 0;
            }
            $category->sort_order    = ($_POST['sort_order'] != '')?$_POST['sort_order']:0;
            $category->url_key =  $_POST['url_key'] ? str_slug($_POST['url_key']): str_slug($_POST['category_name'][1]);
            $category->category_status =  isset($_POST['status']) ? $_POST['status']: 0;
            
            if(isset($_POST['is_category_type']))
            {
                if($_POST['is_category_type'] == 1)
                {
					
                    $category->category_level = 3;
                    if(isset($_POST['head_category']) && $_POST['head_category']!=''){
						$category->parent_id  = $_POST['head_category'];
					}
					if(isset($_POST['main_category']) && $_POST['main_category']!=''){
						$category->head_category_ids = $_POST['main_category'];
					}
                }
                else if($_POST['is_category_type'] == 0)
                {
                    $category->category_level =2;
                    if(isset($_POST['head_category']) && $_POST['head_category']!=''){
						$category->parent_id  = $_POST['head_category'];
					}
                    if(isset($_POST['main_category']) && $_POST['main_category']!=''){
						$category->head_category_ids = $_POST['main_category'];
					}
                }
            }

                
            
            
            //echo $category->parent_id ;exit;
            $category->updated_at = Carbon::now();
            //$current_time = Carbon::now()->toDayDateTimeString();
         //echo '<pre>'; print_r($category);exit;
            $category->save();
            if(isset($_FILES['category_image']['name']) && $_FILES['category_image']['name']!='')
            { 
               
                $imageName = $category->id . '.' . 
                $data->file('category_image')->getClientOriginalExtension();
                $data->file('category_image')->move(base_path().'/public/assets/admin/base/images/category/', $imageName);
                $destinationPath = url('/assets/admin/base/images/category/', $imageName); // upload path
                Image::make( $destinationPath )->save(base_path() .'/public/assets/admin/base/images/category/'.$imageName);
                $category->image = $imageName;
                $category->save();
            }
             
            if(isset($_FILES['category_white_image']['name']) && $_FILES['category_white_image']['name']!='')
						{
						    $destinationPath = base_path().'/public/assets/admin/base/images/category/white_category/';
							$whitecategoryName = $category->id.'.'.
							$data->file('category_white_image')->getClientOriginalExtension();
							$data->file('category_white_image')->move(base_path().'/public/assets/admin/base/images/category/white_category', $whitecategoryName);
							$category->category_white_image = $whitecategoryName;
							$category->save();
							
						}
            if(isset($_FILES['mobile_banner_image']['name']) && $_FILES['mobile_banner_image']['name']!='')
            {
                $destinationPath = base_path() .'/public/assets/admin/base/images/category/mobile_banner/'; // upload path
                $mobileimageName = $category->id.'.'.$data->file('mobile_banner_image')->getClientOriginalExtension();
                $data->file('mobile_banner_image')->move(base_path().'/public/assets/admin/base/images/category/mobile_banner/', $mobileimageName);
                $category->mobile_banner_image = $mobileimageName;
                $category->save();
            }
            $this->category_save_after($category,$_POST);
            // redirect
            Session::flash('message', trans('messages.Category has been successfully updated'));
            return Redirect::to('admin/category');
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
			if(!hasTask('admin/category/delete')){
			return view('errors.404');
			}
		
			
           $category = DB::select('select COUNT(products.category_id) from  products where products.category_id = '.$id);
         
			if($category[0]->count > 0){

				Session::flash('message', trans('messages.This category mapped with products so cannot be delete.'));
				return Redirect::to('admin/category');
			}
			else{
				
					$category = Categories::find($id);
					$category->delete();
					Session::flash('message', trans('messages.Category has been deleted successfully!'));
					return Redirect::to('admin/category');
}
        
    }
    
                /**
     * add,edit datas  saved in main table 
     * after inserted in sub tabel.
     *
     * @param  int  $id
     * @return Response
     */
   public static function category_save_after($object,$post)
   {
        $category = $object;
        $post = $post;
        if(isset($post['category_name'])){
            $category_name = $post['category_name'];
            $description = $post['description'];
            $meta_title = $post['meta_title'];
            $meta_keywords = $post['meta_keywords'];
            $meta_description = $post['meta_description'];
            try{
                $affected = DB::table('categories_infos')->where('category_id', '=', $object->id)->delete();
                $languages = DB::table('languages')->where('status', 1)->get();
                foreach($languages as $key => $lang){
                    if(isset($category_name[$lang->id]) && $category_name[$lang->id]!=""){
                        $infomodel = new Categories_infos;
                        $infomodel->language_id = $lang->id;
                        $infomodel->category_id = $category->id; 
                        $infomodel->category_name = $category_name[$lang->id];
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
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyAjaxCategory(Request $request)
    {
        $query = '"categories_infos"."language_id" = (case when (select count(category_id) as totalcount from categories_infos where categories_infos.language_id = '.getAdminCurrentLang().' and categories.id = categories_infos.category_id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $category = Categories::Leftjoin('categories_infos','categories_infos.category_id','=','categories.id')
                        ->select('categories.id','categories.parent_id','categories.category_type','categories.category_status','categories.url_key','categories.created_at','categories.updated_at','categories_infos.category_name')
                        ->whereRaw($query)
                        ->orderBy('categories.id', 'desc')
                        ->get();
        return Datatables::of($category)->addColumn('action', function ($category) {
            if(hasTask('admin/category/create'))
            {
                $html = '<div class="btn-group">
                    <a href="'.URL::to("admin/category/edit/".$category->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/category/delete/".$category->id).'" class="delete-'.$category->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $( document ).ready(function() {
                        $(".delete-'.$category->id.'").on("click", function(){
                            return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });
                    });
                </script>';
                return $html;
            }
        })
       
        ->addColumn('category_type', function ($category) {
            if($category->category_type==1):
                $data = trans("messages.Product");
            elseif($category->category_type==2):
                $data = trans("messages.Vendor");
            elseif($category->category_type==3):
                $data = trans("messages.Blog");
            elseif($category->category_type==4):
                $data = trans("messages.Faq");
            elseif($category->category_type==5):
                $data = trans("messages.Coupon");
            endif;
            return $data;
        })
        ->addColumn('parent_category', function ($category) {
			
            $query = '"categories_infos"."language_id" = (case when (select count(category_id) as totalcount from categories_infos where categories_infos.language_id = '.getAdminCurrentLang().' and cat.id = categories_infos.category_id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $category = DB::select('SELECT "cat"."id", "categories_infos"."category_name", "cat"."url_key", cat_head.id AS head_category_id, "cat"."category_type", "cat"."parent_id", "cat"."sort_order", "cat"."image", "cat"."category_status", "cat"."category_level", "cat"."parent_id", "cat"."mobile_banner_image" FROM categories cat
            LEFT JOIN "categories_infos" ON "categories_infos"."category_id" = "cat"."id"   
            LEFT JOIN categories cat_head ON cat_head.id = cat.parent_id
            where cat.id = '.$category->parent_id.' AND '.$query);
            $data = "";
            if(count($category)>0){
				$data=$category[0]->category_name;
			}
			else{
				$data ='-';
			}
			
			return $data;
        })
		
        ->addColumn('category_status', function ($category) {
            if($category->category_status == 0):
                $data = '<span class="label label-danger">'.trans("messages.Inactive").'</span>';
            elseif($category->category_status == 1):
                $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
            endif;
            return $data;
        })
        
        ->editColumn('category_name', '{!! str_limit($category_name, 20) !!}')
        ->rawColumns(['category_status','parent_category','category_type','action'])

        ->make(true);
    }

}
