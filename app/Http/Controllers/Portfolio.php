<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Model\portfolios;
use App\Model\weight_classes;
use App\Http\Requests;
use Session;
use Closure;
use Illuminate\Support\Facades\Auth;
use Image;
use MetaTag;
use SEO;
use SEOMeta;
use OpenGraph;
use Twitter;

class Portfolio extends Controller
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
		
	}
		
    /**
     * Display a listing of the portfolios.
     *
     * @return Response
     */
    public function index()
    {
		if (Auth::guest()){
			return redirect()->guest('admin/login');
		}
		else{
			// Section description
			// get all the portfolios
			$portfolios = DB::table('portfolios')->orderBy('id', 'desc')->paginate(10);
			// load the view and list the portfolios
			$type = 2;
			// load the list view (resources/views/portfolio/create.blade.php)
			$category = DB::select('select 	id,category_name from  categories where category_type = '.$type.' and category_status = 1 order by category_name asc limit 3');
			return view('portfolio.list')->with('portfolio', $portfolios)->with('category', $category);
	    }
    }
    
	
	/**
     * Show the form for creating a new portfolio.
     *
     * @return Response
     */
    public function create()
    {

		if (Auth::guest()){
			return redirect()->guest('admin/login');
		}
		else{
			$type = 2;
			// load the create form (resources/views/portfolio/create.blade.php)
			$category = DB::select('select 	id,category_name from  categories where category_type = '.$type.' and category_status = 1 order by category_name asc');
			return view('portfolio.create')->with('category', $category);
        }
    }
	
	/**
     * Store a newly created portfolio in storage.
     *
     * @return Response
     */
    public function store(Request $data)
    {
		
        // validate
        // read more on validation at http://laravel.com/docs/validation        
        $validation = Validator::make($data->all(), array(
			'title' => 'required|unique:portfolios,title',
			'customer' => 'required',
			'technology' => 'required',
			'short_notes' => 'required',
			'short_description' => 'required',
			'long_description' => 'required',
			'category_ids' => 'required',
			'image'       => 'required|mimes:png,jpg,jpeg',
			'thumb_image'       => 'required|mimes:png,jpg,jpeg',
			//'web_link' => 'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/',
			//'iphone_link' => 'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/',
			//'android_link' => 'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/',
			
		));

        // process the validation
        if ($validation->fails()) {
				//return redirect('portfoliocreate')->withErrors($validation);
				return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store
            $portfolio = new Portfolios;
            $portfolio->title      = $_POST['title'];
            $portfolio->short_notes      = $_POST['short_notes'];
            $portfolio->portfolio_index =  $_POST['portfolio_index'] ? str_slug($_POST['portfolio_index']): str_slug($_POST['title']);
            $portfolio->customer    = $_POST['customer'];
            $portfolio->technology    = $_POST['technology'];
            $portfolio->long_description    = $_POST['long_description'];
            $portfolio->short_description    = $_POST['short_description'];
            $portfolio->web_link    = $_POST['web_link'];
            $portfolio->iphone_link    = $_POST['iphone_link'];
            $portfolio->android_link    = $_POST['android_link'];
            $portfolio->category_ids = implode(',',$_POST['category_ids']);
            $portfolio->created_at = date("Y-m-d H:i:s");
            $portfolio->created_by = Auth::id();
            $portfolio->save();
			$imageName = $portfolio->id . '.' . 
			$data->file('image')->getClientOriginalExtension();
			$data->file('image')->move(
				base_path() . '/public/assets/admin/base/images/portfolio/', $imageName
			);
			$destinationPath1 = url('/assets/admin/base/images/portfolio/'.$imageName.'');
			Image::make( $destinationPath1 )->fit(50, 50)->save(base_path() .'/public/assets/admin/base/images/portfolio/thumb/'.$imageName)->destroy();
			Image::make( $destinationPath1 )->fit(300, 300)->save(base_path() .'/public/assets/admin/base/images/portfolio/list/'.$imageName)->destroy();
			Image::make( $destinationPath1 )->fit(631, 417)->save(base_path() .'/public/assets/admin/base/images/portfolio/detail/'.$imageName)->destroy();
			$thumbimageName = $portfolio->id . '.' . 
			$data->file('thumb_image')->getClientOriginalExtension();
			$data->file('thumb_image')->move(
				base_path() . '/public/assets/admin/base/images/portfolio/thumbimage/', $thumbimageName
			);
			$portfolio->thumb_image = $thumbimageName;
			$portfolio->image = $imageName;
			$portfolio->save();
            // redirect
            Session::flash('message', trans('messages.Portfolio has been created successfully'));
            //return Redirect::to('blog')->with('updatemsg', 'Blog has been successfully create');
            return Redirect::to('admin/portfolio');
        }
    }
	
	/**
     * Display the specified portfolio.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
		if (Auth::guest()){
			return redirect()->guest('admin/login');
		}
		else{
			// get the nerd
			$portfolio = Portfolios::find($id);
			// show the view and pass the portfolio to it
			$type = 2;
			// load the create form (resources/views/portfolio/create.blade.php)
			$category = DB::select('select 	id,category_name from  categories where category_type = '.$type.' and category_status = 1 order by category_name asc');
			return view('portfolio.show')->with('data', $portfolio)->with('category', $category);
		}
    }
	
	
	/**
     * Show the form for editing the specified portfolio.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
		if (Auth::guest()){
			return redirect()->guest('admin/login');
		}
		else{
			// get the portfolio
			$portfolios = DB::select('select id from portfolios where id = '.$id.'');
			if(!count($portfolios)){
				Session::flash('message', 'Invalid Portfolio'); 
				Session::flash('alert-class', 'alert-danger'); 
				return Redirect::to('admin/portfolio');	
			}
			$portfolio = Portfolios::find($id);
			$type = 2;
			// load the create form (resources/views/portfolio/edit.blade.php)
			$category = DB::select('select 	id,category_name from  categories where category_type = '.$type.' and category_status = 1 order by category_name asc');
			// show the edit form and pass the portfolio
			return view('portfolio.edit')->with('data', $portfolio)->with('category', $category);
		}
    }
	
	
	/**
     * Update the specified portfolio in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $data, $id)
    {
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $validation = Validator::make($data->all(), array(
			'title' => 'required|unique:portfolios,title,'.$id,
			'short_notes' => 'required',
			'customer' => 'required',
			'technology' => 'required',
			'short_description' => 'required',
			'long_description' => 'required',
			'category_ids' => 'required',
			//'web_link' => 'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/',
			//'iphone_link' => 'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/',
			//'android_link' => 'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/',
			//'image'       => 'required|mimes:png,jpeg,bmp',
			//mimes:jpeg,bmp,png and for max size max:10000
		));
        // process the validation
        if ($validation->fails()) {
               return redirect('admin/portfolio/edit/'.$id)->withErrors($validation);
        } else {
            // store datas in to database
            $portfolio = Portfolios::find($id);
            $portfolio->title      = $_POST['title'];
            $portfolio->short_notes      = $_POST['short_notes'];
            $portfolio->portfolio_index =  $_POST['portfolio_index'] ? str_slug($_POST['portfolio_index']): str_slug($_POST['title']);
            $portfolio->customer    = $_POST['customer'];
            $portfolio->technology    = $_POST['technology'];
            $portfolio->long_description    = $_POST['long_description'];
            $portfolio->short_description    = $_POST['short_description'];
            $portfolio->web_link    = $_POST['web_link'];
            $portfolio->iphone_link    = $_POST['iphone_link'];
            $portfolio->android_link    = $_POST['android_link'];
            $portfolio->category_ids = implode(',',$_POST['category_ids']);
            $portfolio->updated_at = date("Y-m-d H:i:s");
            $portfolio->save();
            if(isset($_FILES['image']['name']) && $_FILES['image']['name']!=''){ 
				$destinationPath = base_path() .'/public/assets/admin/base/images/portfolio/'; // upload path
				$imageName = $portfolio->id . '.' . 
				$data->file('image')->getClientOriginalExtension();
				//Image::make($data->file('image'))->resize(200, 200);
				$data->file('image')->move($destinationPath, $imageName);
				$destinationPath1 = url('/assets/admin/base/images/portfolio/'.$imageName.'');
				Image::make( $destinationPath1 )->fit(50, 50)->save(base_path() .'/public/assets/admin/base/images/portfolio/thumb/'.$imageName)->destroy();
				Image::make( $destinationPath1 )->fit(300, 300)->save(base_path() .'/public/assets/admin/base/images/portfolio/list/'.$imageName)->destroy();
				Image::make( $destinationPath1 )->fit(631, 417)->save(base_path() .'/public/assets/admin/base/images/portfolio/detail/'.$imageName)->destroy();
				$thumbimageName = $portfolio->id . '.' . 
				$data->file('thumb_image')->getClientOriginalExtension();
				$data->file('thumb_image')->move(
					base_path() . '/public/assets/admin/base/images/portfolio/thumbimage/', $thumbimageName
				);
				$portfolio->thumb_image = $thumbimageName;
				$portfolio->image = $imageName;
				$portfolio->save();
			}
            // redirect
            Session::flash('message', trans('messages.Portfolio has been successfully updated'));
            return Redirect::to('admin/portfolio');
        }
    }

	/**
     * Delete the specified portfolio in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destory($id)
    {
		$data = Portfolios::find($id);
		$portfolio = DB::select('select id,image from portfolios where id = '.$id.'');
        if(count($portfolio)){
			if(file_exists(base_path() .'/public/assets/admin/base/images/portfolio/'.$portfolio[0]->image)) {
				unlink(base_path() .'/public/assets/admin/base/images/portfolio/'.$portfolio[0]->image);
			}
			if(file_exists(base_path() .'/public/assets/admin/base/images/portfolio/detail/'.$portfolio[0]->image)) {
				unlink(base_path() .'/public/assets/admin/base/images/portfolio/detail/'.$portfolio[0]->image);
			}
			if(file_exists(base_path() .'/public/assets/admin/base/images/portfolio/list/'.$portfolio[0]->image)) {
				unlink(base_path() .'/public/assets/admin/base/images/portfolio/list/'.$portfolio[0]->image);
			}
			if(file_exists(base_path() .'/public/assets/admin/base/images/portfolio/thumb/'.$portfolio[0]->image)) {
				unlink(base_path() .'/public/assets/admin/base/images/portfolio/thumb/'.$portfolio[0]->image);
			}

		}
		$data->delete();
		Session::flash('message', trans('messages.Portfolio has been deleted successfully!'));
        return Redirect::to('admin/portfolio');
	}

	/**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function weight_classes()
    {
		if (Auth::guest()){
			return redirect()->guest('admin/login');
		}
		else{
			// Section description
			// get all the weight classes
			$query ='select c.*, ci.* FROM "weight_classes" AS "c" LEFT JOIN "weight_classes_info" AS "ci" ON ("ci"."id" = "c"."id" AND "ci"."lang_id" = (case when (select count(*) as totalcount from weight_classes_info as cinfo where cinfo.lang_id = '.getCurrentLang().' and id = ci.id) > 0 THEN '.getCurrentLang().' ELSE 1 END)) order by title asc';
			$weight_classes = DB::select(DB::raw($query));
			print_r($weight_classes);exit;
			//$weight_classes = DB::table('weight_classes')->orderBy('id', 'desc')->paginate(10);
			// load the list view (resources/views/admin/weight_classes/create.blade.php)
			return view('admin.weight_classes.list')->with('weight_classes', $weight_classes);
	    }
    }

    /**
     * Show the form for creating a new weight classes.
     *
     * @return Response
     */
    public function weight_class_create()
    {
		if (Auth::guest()) {
			return redirect()->guest('admin/login');
		} else {
			// load the create form (resources/views/admin/weight_classes/create.blade.php)
			return view('admin.weight_classes.create');
        }
    }
	
	/**
     * Store a newly created weight classes in storage.
     *
     * @return Response
     */
    public function weight_class_store(Request $data)
    {
        // validate
        // read more on validation at http://laravel.com/docs/validation        
        $validation = Validator::make($data->all(), array(
			'weight_title' => 'required|unique:weight_classes_info,title',
			'weight_unit' => 'required',
			'weight_value' => 'required',
		));
		echo 'ergo';print_r($_POST);exit;
        // process the validation
        if ($validation->fails()) {
				return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store the data here
            $weight_classes = new weight_classes;
            
            $weight_classes->weight_value =  $_POST['weight_value'];
            $weight_classes->created_date = date("Y-m-d H:i:s");
            $weight_classes->active_status = Auth::id();
            $weight_classes->save();

            $weight_classes_info = new weight_classes_info;
            $weight_classes_info->title      = $_POST['weight_title'];
            $weight_classes_info->unit      = $_POST['weight_unit'];
            $weight_classes_info->lang_id = 1;
            $weight_classes_info->id = 1;
            $weight_classes_info->save();
            // redirect
            Session::flash('message', trans('messages.Portfolio has been created successfully'));
            //return Redirect::to('blog')->with('updatemsg', 'Blog has been successfully create');
            return Redirect::to('admin/portfolio');
        }
    }

}
