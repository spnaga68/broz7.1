<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
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
use Yajra\Datatables\Datatables;
use URL;
use App;
use App\Model\categories;
use App\Model\categories_infos;
use App\Model\products;
use App\Model\products_infos;
use App\Model\admin_products;
use App\Model\outlet_products;
use Maatwebsite\Excel\Facades\Excel;

class Product extends Controller
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
     * Display a listing of the categorys.
     *
     * @return Response
     */
    public function index()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('admin/products'))
            {
                return view('errors.404');
            }
            return view('admin.products.list');
        }
    }
    
    
    /**
     * Show the form for creating a new product.
     * @return Response
     */
    public function create()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('admin/products/create_product'))
            {
                return view('errors.404');
            }
            // load the create form (resources/views/category/create.blade.php)
            return view('admin.products.create');
        }
    }
    
    /**
     * Store a newly created category in storage.
     *
     * @return Response
     */
    public function store(Request $data)
    {
        if(!hasTask('product_create'))
        {
            return view('errors.404');
        }
        ///echo"<pre>";print_r($_POST);exit;
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation
        $fields['head_category'] = Input::get('head_category');
        $fields['category'] = Input::get('category');
        $fields['sub_category'] = Input::get('sub_category'); 
        $fields['sub_category'] = Input::get('sub_category');
        $fields['weight_class'] = Input::get('weight_class');
        $fields['weight_value'] = Input::get('weight_value');
        $fields['item_limit'] = Input::get('item_limit');
        //$fields['return_time'] = Input::get('return_time');
        $fields['total_quantity'] = Input::get('total_quantity');
        //$fields['product_url'] = Input::get('product_url');
        $fields['publish_status'] = Input::get('publish_status');
        $fields['active_status'] = Input::get('active_status');
        $fields['product_image'] = Input::file('product_image');
         $fields['product_info_image'] = Input::file('product_info_image');
        $rules = array(
           
            'category' => 'required', 
            'head_category' => 'required', 
            'sub_category' => 'required',
            'weight_class' => 'required',
            'weight_value' => 'required|numeric',
            'total_quantity' => 'required|integer',
            'item_limit' => 'required|numeric',
            //'product_image' => 'required|mimes:png,jpg,jpeg,bmp', 
        );
        $product_name = Input::get('product_name');
        foreach ($product_name  as $key => $value) {
            $fields['product_name'.$key] = $value;
            $rules['product_name'.'1'] = 'required';
        }
        $description = Input::get('description');
        foreach ($description  as $key => $value) {
            $fields['description'.$key] = $value;
            $rules['description'.'1'] = 'required';
        }
        $validation = Validator::make($fields, $rules);
        // process the validation
        if ($validation->fails())
        {
            $errors = '';
			$j = 0;
			$error = array();
			foreach( $validation->errors()->messages() as $key => $value) 
			{
				$error[] = is_array($value)?implode( ',',str_replace("."," ",$value) ):str_replace("."," ",$value);
			}
			$errors = implode( "<br>", $error );
			$result = array("httpCode" => 400, "errors" => $errors);return json_encode($result);exit;
        }
        else {
			try{ 
				$languages = DB::table('languages')->where('status', 1)->get();
                foreach($languages as $key => $lang){
                	$product_name = $_POST['product_name'];
                    if(isset($product_name[$lang->id]) && $product_name[$lang->id]!=""){
                        $Products = new Admin_products;
                        $Products->product_name   = str_slug($_POST['product_name'][$lang->id]);
						$Products->product_url   = str_slug($_POST['product_url']);
						$Products->description   = str_slug($_POST['description'][$lang->id]);
						$Products->quantity   = isset($_POST['total_quantity'])?$_POST['total_quantity']:0;
						$Products->category_id   = isset($_POST['head_category'])?$_POST['head_category']:0;
						$Products->sub_category_id   = isset($_POST['category'])?$_POST['category']:0;
						$Products->child_category_id   = isset($_POST['sub_category'])?$_POST['sub_category']:0;
						$Products->barcode   = isset($_POST['barcode'])?$_POST['barcode']:'';
						$Products->weight_class_id   = isset($_POST['weight_class'])?$_POST['weight_class']:0;
						$Products->weight   = isset($_POST['weight_value'])?$_POST['weight_value']:0;
						$Products->item_limit   = isset($_POST['item_limit'])?$_POST['item_limit']:0;
						$Products->adjust_weight   = isset($_POST['adjust_weight'])?$_POST['adjust_weight']:0;
						$Products->status   = isset($_POST['active_status'])?$_POST['active_status']:0;
						$Products->created_date = date("Y-m-d H:i:s");
						$Products->save();
                    }
                }
			
			}
			catch(Exception $e) {
				Log::Instance()->add(Log::ERROR, $e);
			}
			// redirect
			$result["status"] = 200;
			Session::flash('message',trans('Product added successfully'));
			$result["errors"] = "";
			
		}
		return json_encode($result);exit;
	}


	public function store_cpy(Request $data)
    {
        if(!hasTask('product_create'))
        {
            return view('errors.404');
        }
        echo"<pre>";print_r($_POST);exit;
        // validate the post data
        // read more on validation at http://laravel.com/docs/validation
        $fields['vendor'] = Input::get('vendor');
        $fields['outlet'] = Input::get('outlet');
        $fields['head_category'] = Input::get('head_category');
        $fields['vendor_type']  = Input::get('vendor_type');
        $fields['category'] = Input::get('category');
        $fields['sub_category'] = Input::get('sub_category'); 
        $fields['sub_category'] = Input::get('sub_category');
        $fields['weight_class'] = Input::get('weight_class');
        $fields['weight_value'] = Input::get('weight_value');
        $fields['original_price'] = Input::get('original_price');
        $fields['discount_price'] = Input::get('discount_price');
        $fields['item_limit'] = Input::get('item_limit');
        //$fields['return_time'] = Input::get('return_time');
        $fields['total_quantity'] = Input::get('total_quantity');
        //$fields['product_url'] = Input::get('product_url');
        $fields['publish_status'] = Input::get('publish_status');
        $fields['active_status'] = Input::get('active_status');
        $fields['product_image'] = Input::file('product_image');
         $fields['product_info_image'] = Input::file('product_info_image');
        $rules = array(
            //'vendor' => 'required',
            //'outlet' => 'required',
            'category' => 'required', 
            'head_category' => 'required', 
            'vendor_type' => 'required',
            'sub_category' => 'required',
            'weight_class' => 'required',
            'weight_value' => 'required|numeric',
            'total_quantity' => 'required|integer',
           // 'original_price' => 'required|numeric',
            'discount_price' => 'required|numeric',
            'item_limit' => 'required|numeric',
            //'return_time' => 'required|numeric',
            
            'product_image' => 'required|mimes:png,jpg,jpeg,bmp', 
             'product_info_image' => 'required|mimes:png,jpg,jpeg,bmp',
            //'product_url' => 'required|regex:/(^[A-Za-z0-9-]+$)+/',
            //~ 'publish_status' => 'required',
            //~ 'active_status' => 'required',
            'product_image' => 'required|mimes:png,jpeg,bmp|max:2024',
        );
        $product_name = Input::get('product_name');
        foreach ($product_name  as $key => $value) {
            $fields['product_name'.$key] = $value;
            $rules['product_name'.'1'] = 'required';
        }
        $description = Input::get('description');
        foreach ($description  as $key => $value) {
            $fields['description'.$key] = $value;
            $rules['description'.'1'] = 'required';
        }
        $meta_title = Input::get('meta_title');
        foreach ($meta_title  as $key => $value) {
            $fields['meta_title'.$key] = $value;
            $rules['meta_title'.'1'] = 'required';
        }
        $meta_keywords = Input::get('meta_keywords');
        foreach ($meta_keywords  as $key => $value) {
            $fields['meta_keywords'.$key] = $value;
            $rules['meta_keywords'.'1'] = 'required';
        }
        $meta_description = Input::get('meta_description');
        foreach ($meta_description  as $key => $value) {
            $fields['meta_description'.$key] = $value;
            $rules['meta_description'.'1'] = 'required';
        }
        $validation = Validator::make($fields, $rules);
        // process the validation
        if ($validation->fails())
        {
            $errors = '';
			$j = 0;
			$error = array();
			foreach( $validation->errors()->messages() as $key => $value) 
			{
				$error[] = is_array($value)?implode( ',',str_replace("."," ",$value) ):str_replace("."," ",$value);
			}
			$errors = implode( "<br>", $error );
			$result = array("httpCode" => 400, "errors" => $errors);return json_encode($result);exit;
        }
        else {
			//print_r($_POST); exit;
            $err_msg = '';
           if(!empty($_POST['original_price'])){
				if($fields['original_price'] < $fields['discount_price'])
				{
					$result = array("httpCode" => 400, "errors" => trans('messages.Discount price should be less than original price.'));return json_encode($result);exit;
				}
			}
			if($err_msg != '')
			{
				return Redirect::back()->withErrors($validation)->withInput();
			}
				try{ 
					$profile_image_ext = $data->file('product_image')->getClientOriginalExtension();
					$profile_info_image_ext = $data->file('product_info_image')->getClientOriginalExtension();
					if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
					$product_zoom_image_ext = $data->file('product_zoom_image')->getClientOriginalExtension();
				}
					if( $_POST['vendor_type'] ==1){
						$head_categories = $_POST['head_category'];
						$vendor_list = getCategoryVendorLists($head_categories);
						$product_id = 0;
						foreach($vendor_list as $pro){
							$vendor_id = $pro->id;
							$outlet_list = get_outlet_list($vendor_id );
							foreach($outlet_list as $opro){
								$Products = new Products;
								$Products->vendor_id =$pro;
								$Products->outlet_id =$opro;
								$Products->vendor_id    = $pro->id;
								$Products->outlet_id    = $opro->id;
								$Products->vendor_type = $_POST['vendor_type'];
								$Products->category_id = $_POST['category'];
								$Products->sub_category_id = $_POST['sub_category'];
								$Products->vendor_category_id = $_POST['head_category'];
								$Products->weight_class_id = $_POST['weight_class'];
								$Products->weight = $_POST['weight_value'];
								$Products->quantity = $_POST['total_quantity'];
								if(!empty($_POST['original_price']))
								$Products->original_price = isset($_POST['original_price']) ? $_POST['original_price']: 0;
								$Products->discount_price = $_POST['discount_price'];
								$Products->active_status =  isset($_POST['active_status']) ? $_POST['active_status']: 0;
								$Products->approval_status =  isset($_POST['publish_status']) ? $_POST['publish_status']: 0;
								$Products->item_limit =  isset($_POST['item_limit']) ? $_POST['item_limit']: 0;
								$Products->barcode =  isset($_POST['barcode']) ? $_POST['barcode']: '';
								$Products->created_date = date("Y-m-d H:i:s");
								$Products->created_by = Auth::id();
								$Products->product_url =  str_slug($_POST['product_name'][1]);
								$Products->save();
								$imageName = $Products->id.'.'.$profile_image_ext;
								$info_imageName = $Products->id.'.'.$profile_info_image_ext;
								if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
								$zoom_imageName = $Products->id.'.'.$product_zoom_image_ext;
							     }
								if($product_id != 0)
								{
									Image::make( $destinationPath2 )->save(base_path() .'/public/assets/admin/base/images/products/'.$imageName);
									Image::make( $destinationPath1 )->save(base_path() .'/public/assets/admin/base/images/products/detail/'.$info_imageName);
									if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
									Image::make( $destinationPath3 )->save(base_path() .'/public/assets/admin/base/images/products/zoom/'.$zoom_imageName);
								     }
								}
								else {
									$destinationPath2 = url('/assets/admin/base/images/products/'.$imageName);
									$destinationPath1 = url('/assets/admin/base/images/products/detail/'.$info_imageName);
									$destinationPath3 = url('/assets/admin/base/images/products/zoom/'.$zoom_imageName);
									$data->file('product_image')->move(base_path().'/public/assets/admin/base/images/products/', $imageName);
									$data->file('product_info_image')->move(base_path().'/public/assets/admin/base/images/products/detail/', $info_imageName);
									if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
									$data->file('product_zoom_image')->move(base_path().'/public/assets/admin/base/images/products/zoom/', $zoom_imageName);
								     }
								}
								$size = getImageResize('PRODUCT');
								Image::make( $destinationPath2 )->fit($size['LIST_WIDTH'], $size['LIST_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/list/'.$imageName);
								/* Image::make( $destinationPath2 )->fit($size['DETAIL_WIDTH'], $size['DETAIL_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/detail/'.$imageName);*/

								Image::make( $destinationPath2 )->fit($size['THUMB_WIDTH'], $size['THUMB_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/thumb/'.$imageName);
								$Products->product_info_image = $info_imageName;
								$Products->product_zoom_image = $zoom_imageName;
								$Products->product_image = $imageName;
								$Products->save();
								$this->product_save_after($Products,$_POST);
								$product_id = $Products->id;
							}
						}
					}
					else if( $_POST['vendor_type'] ==2)
					{
						$product_id=0;
						if(count($_POST['vendor']) > 0)
						{ 
							foreach($_POST['vendor'] as $pro)
							{
								$outlet_list = get_outlet_list($pro );
								if(count($outlet_list) > 0)
								{  
									foreach($outlet_list as $opro)
									{ 
										$Products = new Products;
										$Products->vendor_id =$pro;
										$Products->outlet_id =$opro->id;
										//$Products->outlet_id    = $opro->id;
										$Products->vendor_type = $_POST['vendor_type'];
										$Products->vendor_category_id = $_POST['head_category'];
										$Products->category_id = $_POST['category'];
										$Products->sub_category_id = $_POST['sub_category'];
										$Products->weight_class_id = $_POST['weight_class'];
										$Products->weight = $_POST['weight_value'];
										$Products->quantity = $_POST['total_quantity'];
										if(!empty($_POST['original_price']))
								        $Products->original_price = isset($_POST['original_price']) ? $_POST['original_price']: 0;
										$Products->discount_price = $_POST['discount_price'];
										$Products->active_status =  isset($_POST['active_status']) ? $_POST['active_status']: 0;
										$Products->approval_status =  isset($_POST['publish_status']) ? $_POST['publish_status']: 0;
										$Products->created_date = date("Y-m-d H:i:s");
										$Products->created_by = Auth::id();
										$Products->product_url =  str_slug($_POST['product_name'][1]);
										
										//print_r($Products);exit;
										$Products->save();
										$imageName = $Products->id.'.'.$profile_image_ext;
										$info_imageName = $Products->id.'.'.$profile_info_image_ext;
										if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
								$zoom_imageName = $Products->id.'.'.$product_zoom_image_ext;
							     }
										if($product_id != 0)
										{
											Image::make( $destinationPath2 )->save(base_path() .'/public/assets/admin/base/images/products/'.$imageName);
											Image::make( $destinationPath1 )->save(base_path() .'/public/assets/admin/base/images/products/detail/'.$info_imageName);
											if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
											Image::make( $destinationPath3 )->save(base_path() .'/public/assets/admin/base/images/products/zoom/'.$zoom_imageName);
										   }
										}
										else {
											$destinationPath2 = url('/assets/admin/base/images/products/'.$imageName);
											$destinationPath1 = url('/assets/admin/base/images/products/detail/'.$info_imageName);
											$destinationPath3 = url('/assets/admin/base/images/products/zoom/'.$info_imageName);
											$data->file('product_image')->move(base_path().'/public/assets/admin/base/images/products/', $imageName);
											$data->file('product_info_image')->move(base_path().'/public/assets/admin/base/images/products/detail/', $info_imageName);
											if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
									        $data->file('product_zoom_image')->move(base_path().'/public/assets/admin/base/images/products/zoom/', $zoom_imageName);
								             }
										}

										$size = getImageResize('PRODUCT');
										Image::make( $destinationPath2 )->fit($size['LIST_WIDTH'], $size['LIST_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/list/'.$imageName)->destroy();
										/*Image::make( $destinationPath2 )->fit($size['DETAIL_WIDTH'], $size['DETAIL_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/detail/'.$imageName)->destroy(); */ 
										Image::make( $destinationPath2 )->fit($size['THUMB_WIDTH'], $size['THUMB_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/thumb/'.$imageName)->destroy();
										Image::make( $destinationPath1 )->save(base_path() .'/public/assets/admin/base/images/products/detail/'.$info_imageName);
										$Products->product_image=$imageName;
									    $Products->product_info_image=$info_imageName;
									    if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
									    $Products->product_zoom_image = $zoom_imageName;
									}
										$Products->save();
										$this->product_save_after($Products,$_POST);
								        $product_id = $Products->id;
								    }
								}
						    }
						}
					}
				}
				catch(Exception $e) {
					Log::Instance()->add(Log::ERROR, $e);
				}
				// redirect
				$result["status"] = 200;
			Session::flash('message',trans('Product added successfully'));
			$result["errors"] = "";
			
		}
		return json_encode($result);exit;
	}
    
    /**
     * Display the specified category.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('admin/products/product_details'))
            {
                return view('errors.404');
            }
            $query  = '"products_infos"."lang_id" = (case when (select count(lang_id) as totalcount from products_infos where products_infos.lang_id = '.getAdminCurrentLang().' and products.id = products_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $query1 = '"outlet_infos"."language_id" = (case when (select count(language_id) as totalcount from outlet_infos where outlet_infos.language_id = '.getAdminCurrentLang().' and products.outlet_id = outlet_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $data   = DB::table('products')
                        ->join('products_infos','products.id','=','products_infos.id')
                        ->join('outlet_infos','products.outlet_id','=','outlet_infos.id')
                        ->join('outlets','products.outlet_id','=','outlets.id')
                        ->join("categories_infos",function($join){
                            $join->on("categories_infos.category_id","=","products.category_id")
                                ->on("categories_infos.language_id","=","products_infos.lang_id");
                        })
                        ->join("vendors_infos",function($join){
                            $join->on("vendors_infos.id","=","products.vendor_id")
                                ->on("vendors_infos.lang_id","=","products_infos.lang_id");
                        })
                        ->select('products.*','products_infos.*','categories_infos.category_name','vendors_infos.vendor_name','outlet_infos.outlet_name','outlets.url_index')
                        ->whereRaw($query)
                        ->whereRaw($query1)
                        ->where('products.id',$id)
                        ->orderBy('products.created_date', 'desc')
                        ->get();
            if(!count($data))
            {
                Session::flash('message', 'Invalid Product Details'); 
                return Redirect::to('admin/products');
            }
            $info = new Products_infos;
            return view('admin.products.show')->with('data', $data)->with('infomodel', $info);
        }
    }
    
    
    /**
     * Show the form for editing the specified category.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('admin/products/edit_product')){
                return view('errors.404');
            }
           // $data = Products::find($id);
            $data = admin_products::find($id);
          	// echo"<pre>"; print_r($data);exit();
			if(!count($data)){
                Session::flash('message', 'Invalid Product Details'); 
                return Redirect::to('admin/products');    
            }
			$language = getAdminCurrentLang();
			
			//echo"<pre>";print_r($data);exit;
            $info = new admin_products;
           // $info = new outlet_products;
           // echo"<pre>";print_r($info);exit;
		   return view('admin.products.edit')->with('data', $data)->with('infomodel', $info);
        }
    }
    
    /**
     * Update the specified category in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $data,$id)
    { 
        if(!hasTask('update_product'))
        {
            return view('errors.404');
        }
      	//echo"<pre>";print_r($_POST);exit;

      	
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $fields['head_category'] = Input::get('head_category'); 
        $fields['category'] = Input::get('category');
        $fields['sub_category'] = Input::get('sub_category');
        $fields['weight_class'] = Input::get('weight_class');
        $fields['weight_value'] = Input::get('weight_value');
        $fields['item_limit'] = Input::get('item_limit');
        $fields['total_quantity'] = Input::get('total_quantity');
        $fields['publish_status'] = Input::get('publish_status');
        $fields['active_status'] = Input::get('active_status');
        $fields['adjust_weight'] = Input::get('adjust_weight');
        $fields['product_image'] = Input::file('product_image');
        //$fields['product_info_image'] = Input::file('product_info_image');
        $rules = array(
            'head_category' => 'required',
            'category' => 'required', 
            'sub_category' => 'required',
            'weight_class' => 'required',
            'weight_value' => 'required|numeric',
            'total_quantity' => 'required|integer',
            'item_limit' => 'required|numeric',
            //'product_image' => 'mimes:png,jpg,jpeg,bmp',
        );
        $product_name = Input::get('product_name');
        foreach ($product_name  as $key => $value) {
            $fields['product_name'.$key] = $value;
            $rules['product_name'.'1'] = 'required';
        }
        $description = Input::get('description');
        foreach ($description  as $key => $value) {
            $fields['description'.$key] = $value;
            $rules['description'.'1'] = 'required';
        }
        $validation = Validator::make($fields, $rules);
        // process the validation
        if ($validation->fails())
        {
           $errors = '';
			$j = 0;
			$error = array();
			foreach( $validation->errors()->messages() as $key => $value) 
			{
				$error[] = is_array($value)?implode( ',',str_replace("."," ",$value) ):str_replace("."," ",$value);
			}
			$errors = implode( "<br>", $error );
			$result = array("httpCode" => 400, "errors" => $errors);return json_encode($result);exit;
        }
        else {
           	
			try{ 
				$Products = Admin_products::find($id);

				if(count($Products) > 0)
				{  
					$languages = DB::table('languages')->where('status', 1)->get();
	                foreach($languages as $key => $lang){
	                    if(isset($product_name[$lang->id]) && $product_name[$lang->id]!=""){
	                      	$Products->product_name   = str_slug($_POST['product_name'][$lang->id]);
							$Products->product_url   = str_slug($_POST['product_url']);
							$Products->description   = str_slug($_POST['description'][$lang->id]);
							$Products->quantity   = isset($_POST['total_quantity'])?$_POST['total_quantity']:0;
							$Products->category_id   = isset($_POST['head_category'])?$_POST['head_category']:0;
							$Products->sub_category_id   = isset($_POST['category'])?$_POST['category']:0;
							$Products->child_category_id   = isset($_POST['sub_category'])?$_POST['sub_category']:0;
							$Products->barcode   = isset($_POST['barcode'])?$_POST['barcode']:'';
							$Products->weight_class_id   = isset($_POST['weight_class'])?$_POST['weight_class']:0;
							$Products->weight   = isset($_POST['weight_value'])?$_POST['weight_value']:0;
							$Products->item_limit   = isset($_POST['item_limit'])?$_POST['item_limit']:0;
							$Products->adjust_weight   = isset($_POST['adjust_weight'])?$_POST['adjust_weight']:0;
							$Products->updated_date = date("Y-m-d H:i:s");
							//$Products->active_status =  isset($_POST['active_status']) ? $_POST['active_status']: 0;
							//$Products->approval_status =  isset($_POST['publish_status']) ? $_POST['publish_status']: 0;
							$Products->save();
	                    }
	                }
					$old_product_image     = $Products->image;
					
				}
				else {
					Session::flash('message', 'Invalid Product Details'); 
					return Redirect::to('admin/products');
				}
			}
			catch(Exception $e) {
				Log::Instance()->add(Log::ERROR, $e);
			}
			$result["status"] = 200;
			Session::flash('message',trans('Product updated successfully'));
			$result["errors"] = "";
        }
		return json_encode($result);exit;
    }  

    public function update_copy(Request $data,$id)
    { 
        if(!hasTask('update_product'))
        {
            return view('errors.404');
        }
      	echo"<pre>";print_r($_POST);exit;
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $fields['vendor'] = Input::get('vendor');
        //$fields['outlet'] = Input::get('outlet');
        $fields['head_category'] = Input::get('head_category'); 
         $fields['category'] = Input::get('category');
        $fields['sub_category'] = Input::get('sub_category');
        $fields['weight_class'] = Input::get('weight_class');
        $fields['weight_value'] = Input::get('weight_value');
        $fields['original_price'] = Input::get('original_price');
        $fields['discount_price'] = Input::get('discount_price');
        $fields['item_limit'] = Input::get('item_limit');
        //$fields['return_time'] = Input::get('return_time');
        $fields['total_quantity'] = Input::get('total_quantity');
        //$fields['product_url'] = Input::get('product_url');
        $fields['publish_status'] = Input::get('publish_status');
        $fields['active_status'] = Input::get('active_status');
        $fields['adjust_weight'] = Input::get('adjust_weight');
        $fields['product_image'] = Input::file('product_image');
        $fields['product_info_image'] = Input::file('product_info_image');
        $rules = array(
            'vendor' => 'required',
            //'outlet' => 'required',
             'head_category' => 'required',
            'category' => 'required', 
            'sub_category' => 'required',
            'weight_class' => 'required',
            'weight_value' => 'required|numeric',
            'total_quantity' => 'required|integer',
            //'original_price' => 'required|numeric',
            'discount_price' => 'required|numeric',
            'item_limit' => 'required|numeric',
            //'return_time' => 'required|numeric',
            'product_image' => 'mimes:png,jpg,jpeg,bmp',
            //'product_url' => 'required|regex:/(^[A-Za-z0-9-]+$)+/',
            //~ 'publish_status' => 'required',
            //~ 'active_status' => 'required',
            'product_info_image' => 'mimes:png,jpeg,bmp|max:2024',
        );
        $product_name = Input::get('product_name');
        foreach ($product_name  as $key => $value) {
            $fields['product_name'.$key] = $value;
            $rules['product_name'.'1'] = 'required';
        }
        $description = Input::get('description');
        foreach ($description  as $key => $value) {
            $fields['description'.$key] = $value;
            $rules['description'.'1'] = 'required';
        }
        $meta_title = Input::get('meta_title');
        foreach ($meta_title  as $key => $value) {
            $fields['meta_title'.$key] = $value;
            $rules['meta_title'.'1'] = 'required';
        }
        $meta_keywords = Input::get('meta_keywords');
        foreach ($meta_keywords  as $key => $value) {
            $fields['meta_keywords'.$key] = $value;
            $rules['meta_keywords'.'1'] = 'required';
        }
        $meta_description = Input::get('meta_description');
        foreach ($meta_description  as $key => $value) {
            $fields['meta_description'.$key] = $value;
            $rules['meta_description'.'1'] = 'required';
        }
        $validation = Validator::make($fields, $rules);
        // process the validation
        if ($validation->fails())
        {
           $errors = '';
			$j = 0;
			$error = array();
			foreach( $validation->errors()->messages() as $key => $value) 
			{
				$error[] = is_array($value)?implode( ',',str_replace("."," ",$value) ):str_replace("."," ",$value);
			}
			$errors = implode( "<br>", $error );
			$result = array("httpCode" => 400, "errors" => $errors);return json_encode($result);exit;
        }
        else {
           $err_msg = '';
           if(!empty($_POST['original_price'])){
				if($fields['original_price'] < $fields['discount_price'])
				{
					$result = array("httpCode" => 400, "errors" => trans('messages.Discount price should be less than original price.'));return json_encode($result);exit;
				}
			}
			if($err_msg != '')
			{
				return Redirect::back()->withErrors($validation)->withInput();
			}
		
				try{ 
					$old_product_detail = Products::find($id);
					if(count($old_product_detail) > 0)
					{  
						$old_product_image     = $old_product_detail->product_image;
						$old_product_image_url = '';
						if(file_exists(base_path().'/public/assets/admin/base/images/products/'.$old_product_image) && $old_product_image != '')
						{
							$old_product_image_url = url('/assets/admin/base/images/products/'.$old_product_image);
						}
						$old_product_info_image     = $old_product_detail->product_info_image;
						$old_product_info_image_url = '';
						if(file_exists(base_path().'/public/assets/admin/base/images/products/detail/'.$old_product_info_image) && $old_product_info_image != '')
						{
							$old_product_info_image_url = url('/assets/admin/base/images/products/detail/'.$old_product_info_image);
						}
						$old_product_zoom_image     = $old_product_detail->product_zoom_image;
						$old_product_zoom_image_url = '';
						if(file_exists(base_path().'/public/assets/admin/base/images/products/zoom/'.$old_product_zoom_image) && $old_product_zoom_image != '')
						{
							$old_product_zoom_image_url = url('/assets/admin/base/images/products/zoom/'.$old_product_zoom_image);
						}
						$old_product_vendor = $old_product_detail->vendor_id;
						$old_product_outlet = $old_product_detail->outlet_id;
						$old_product_h_cate = $old_product_detail->vendor_category_id;
						$old_product_url    = $old_product_detail->product_url;
					}
					else {
						Session::flash('message', 'Invalid Product Details'); 
						return Redirect::to('admin/products');
					}
					$product_id=0;
					if(count($_POST['vendor']) > 0)
					{ 
						foreach($_POST['vendor'] as $pro)
						{
							if($pro == $old_product_vendor)
							{
								$vendor_based_outlets = getOutletList($old_product_vendor);
								$oulet_products = getProductBasedOutlet($old_product_h_cate, $old_product_url, $old_product_vendor);
								$outlet_arr = array();
								if(count($oulet_products) > 0)
								{
									foreach($oulet_products as $old_out)
									{
										$outlet_arr[] = $old_out;
									}
								}
								//echo '<pre>';print_r($vendor_based_outlets);exit;
								if(count($vendor_based_outlets) > 0)
								{
									foreach($vendor_based_outlets as $new_out)
									{
									   if(count($oulet_products) > 0)
										 {
											foreach($oulet_products as $old_out)
											  {
														$Products = Products::find($old_out->id); 
														$Products->vendor_id   = $new_out->vendor_id;
														$Products->outlet_id   = $old_out;
														$Products->outlet_id   = $old_out->outlet_id;
														$Products->vendor_category_id = $_POST['head_category'];
														$Products->category_id = $_POST['category'];
														$Products->sub_category_id = $_POST['sub_category'];
														$Products->weight_class_id = $_POST['weight_class'];
														$Products->weight = $_POST['weight_value'];
														$Products->quantity = $_POST['total_quantity'];
														$Products->original_price = !empty($_POST['original_price']) ? $_POST['original_price']: 0;
														$Products->discount_price = $_POST['discount_price'];
														$Products->active_status =  isset($_POST['active_status']) ? $_POST['active_status']: 0;
														$Products->approval_status =  isset($_POST['publish_status']) ? $_POST['publish_status']: 0;
														$Products->modified_date = date("Y-m-d H:i:s");
														$Products->product_url =  str_slug($_POST['product_name'][1]);
														$Products->adjust_weight =  isset($_POST['adjust_weight']) ? $_POST['adjust_weight'] : 0;;
														$Products->item_limit =  isset($_POST['item_limit']) ? $_POST['item_limit'] : 0;;
														$Products->barcode =  isset($_POST['barcode']) ? $_POST['barcode'] : '';
														//echo '<pre>';print_r($Products);exit;
														$Products->save();
														if(isset($_FILES['product_image']['name']) && $_FILES['product_image']['name']!=''){
															$imageName = $id.'.'.$data->file('product_image')->getClientOriginalExtension();
															if($product_id != 0)
															{
																Image::make( $destinationPath1 )->save(base_path() .'/public/assets/admin/base/images/products/'.$imageName);
															}
															else {
																$destinationPath1 = url('/assets/admin/base/images/products/'.$imageName);
																$data->file('product_image')->move(base_path() . '/public/assets/admin/base/images/products/', $imageName);
															}
															$size=getImageResize('PRODUCT');
															Image::make( $destinationPath1 )->fit($size['LIST_WIDTH'], $size['LIST_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/list/'.$imageName)->destroy();
															Image::make( $destinationPath1 )->fit($size['THUMB_WIDTH'], $size['THUMB_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/thumb/'.$imageName)->destroy();
															$Products->product_image=$imageName;
															$Products->save();
														}
														else {
															if($old_product_image_url != '')
															{
																$size = getImageResize('PRODUCT');
																Image::make($old_product_image_url)->save(base_path() .'/public/assets/admin/base/images/products/'.$old_product_image);
																Image::make($old_product_image_url)->fit($size['LIST_WIDTH'], $size['LIST_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/list/'.$old_product_image);
																Image::make( $old_product_image_url )->fit($size['THUMB_WIDTH'], $size['THUMB_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/thumb/'.$old_product_image);
																$Products->product_image=$old_product_image;
																$Products->save();
															}
														}
														if(isset($_FILES['product_info_image']['name']) && $_FILES['product_info_image']['name']!=''){
															$info_imageName = $id.'.'.$data->file('product_info_image')->getClientOriginalExtension();
															if($product_id != 0)
															{
																Image::make( $destinationPath2 )->save(base_path() .'/public/assets/admin/base/images/products/detail/'.$info_imageName);
															}
															else {
																$destinationPath2 = url('/assets/admin/base/images/products/detail/'.$info_imageName);
																$data->file('product_info_image')->move(base_path() . '/public/assets/admin/base/images/products/detail/', $info_imageName);
															}
															$Products->product_info_image=$info_imageName;
															$Products->save();
														}
														else { 
															if($old_product_info_image_url != '')
															{
																Image::make( $old_product_info_image_url )->save(base_path() .'/public/assets/admin/base/images/products/detail/'.$old_product_info_image);
																$Products->product_info_image=$old_product_info_image;
																$Products->save();
															}
														}
														if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
															$zoom_imageName = $id.'.'.$data->file('product_zoom_image')->getClientOriginalExtension();
															if($product_id != 0)
															{
																Image::make( $destinationPath3 )->save(base_path() .'/public/assets/admin/base/images/products/zoom/'.$zoom_imageName);
															}
															else {
																$destinationPath3 = url('/assets/admin/base/images/products/zoom/'.$zoom_imageName);
																$data->file('product_zoom_image')->move(base_path() . '/public/assets/admin/base/images/products/zoom/', $zoom_imageName);
															}
															$Products->product_zoom_image=$zoom_imageName;
															$Products->save();
														}
														else { 
															if($old_product_zoom_image_url != '')
															{
																Image::make( $old_product_zoom_image_url )->save(base_path() .'/public/assets/admin/base/images/products/zoom/'.$old_product_zoom_image);
																$Products->product_zoom_image=$old_product_zoom_image;
																$Products->save();
															}
														}
														$product_id = $Products->id;
													$this->product_save_after($Products,$_POST);
											}
										}
										
											else {
												
											    $new_products_outlets=getNewProductBasedOutlet($old_product_url, $new_out->id);
											    if(count($new_products_outlets)==0){
													//echo "out";exit;
													$Products = new Products;
													$Products->outlet_id    = $new_out->id;
													$Products->vendor_id    = $old_product_vendor;
													$Products->vendor_category_id = $_POST['head_category'];
													$Products->category_id = $_POST['category'];
													$Products->sub_category_id = $_POST['sub_category'];
													$Products->weight_class_id = $_POST['weight_class'];
													$Products->weight = $_POST['weight_value'];
													$Products->quantity = $_POST['total_quantity'];
					                                $Products->original_price = !empty($_POST['original_price']) ? $_POST['original_price']: 0;
													$Products->discount_price = $_POST['discount_price'];
													$Products->active_status =  isset($_POST['active_status']) ? $_POST['active_status']: 0;
													$Products->approval_status =  isset($_POST['publish_status']) ? $_POST['publish_status']: 0;
													$Products->created_date = date("Y-m-d H:i:s");
													$Products->created_by = Auth::id();
													$Products->product_url =  str_slug($_POST['product_name'][1]);
													$Products->save();
													if(isset($_FILES['product_image']['name']) && $_FILES['product_image']['name']!=''){
														$imageName = $id.'.'.$data->file('product_image')->getClientOriginalExtension();
														if($product_id != 0)
														{
															Image::make( $destinationPath1 )->save(base_path() .'/public/assets/admin/base/images/products/'.$imageName);
														}
														else {
															$destinationPath1 = url('/assets/admin/base/images/products/'.$imageName);
															$data->file('product_image')->move(base_path() . '/public/assets/admin/base/images/products/', $imageName);
														}
														$size=getImageResize('PRODUCT');
														Image::make( $destinationPath1 )->fit($size['LIST_WIDTH'], $size['LIST_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/list/'.$imageName)->destroy();
														Image::make( $destinationPath1 )->fit($size['THUMB_WIDTH'], $size['THUMB_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/thumb/'.$imageName)->destroy();
														$Products->product_image=$imageName;
														$Products->save();
													}
													else {
														if($old_product_image_url != '')
														{
															$size = getImageResize('PRODUCT');
															Image::make($old_product_image_url)->save(base_path() .'/public/assets/admin/base/images/products/'.$old_product_image);
															Image::make($old_product_image_url)->fit($size['LIST_WIDTH'], $size['LIST_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/list/'.$old_product_image);
															Image::make( $old_product_image_url )->fit($size['THUMB_WIDTH'], $size['THUMB_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/thumb/'.$old_product_image);
															$Products->product_image=$old_product_image;
															$Products->save();
														}
													}
													if(isset($_FILES['product_info_image']['name']) && $_FILES['product_info_image']['name']!=''){
														$info_imageName = $id.'.'.$data->file('product_info_image')->getClientOriginalExtension();
														if($product_id != 0)
														{
															Image::make( $destinationPath2 )->save(base_path() .'/public/assets/admin/base/images/products/detail/'.$info_imageName);
														}
														else {
															$destinationPath2 = url('/assets/admin/base/images/products/detail/'.$info_imageName);
															$data->file('product_info_image')->move(base_path() . '/public/assets/admin/base/images/products/detail/', $info_imageName);
														}
														$Products->product_info_image=$info_imageName;
														$Products->save();
													}
													else {
														if($old_product_info_image_url != '')
														{
															Image::make( $old_product_info_image_url )->save(base_path() .'/public/assets/admin/base/images/products/detail/'.$old_product_info_image);
															$Products->product_info_image=$old_product_info_image;
															$Products->save();
														}
													}
													if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
													$zoom_imageName = $id.'.'.$data->file('product_zoom_image')->getClientOriginalExtension();
													if($product_id != 0)
													{
														Image::make( $destinationPath3 )->save(base_path() .'/public/assets/admin/base/images/products/zoom/'.$zoom_imageName);
													}
													else {
														$destinationPath3 = url('/assets/admin/base/images/products/zoom/'.$zoom_imageName);
														$data->file('product_zoom_image')->move(base_path() . '/public/assets/admin/base/images/products/zoom/', $zoom_imageName);
													}
													$Products->product_zoom_image=$zoom_imageName;
													$Products->save();
												}
												else {
													if($old_product_zoom_image_url != '')
													{
														Image::make( $old_product_zoom_image_url )->save(base_path() .'/public/assets/admin/base/images/products/zoom/'.$old_product_zoom_image);
														$Products->product_zoom_image=$old_product_zoom_image;
														$Products->save();
													}
												}
													$product_id = $Products->id;
													$this->product_save_after($Products,$_POST);
												}
										    }
										
									}
								}
							}
							else {
								$outlet_list = get_outlet_list($pro);
								if(count($outlet_list) > 0)
								{  
									foreach($outlet_list as $opro)
									{ 
										$Products = new Products;
										$Products->vendor_id =$pro;
										$Products->outlet_id =$opro->id;
										//$Products->outlet_id    = $opro->id;
										$Products->vendor_category_id = $_POST['head_category'];
										$Products->category_id = $_POST['category'];
										$Products->sub_category_id = $_POST['sub_category'];
										$Products->weight_class_id = $_POST['weight_class'];
										$Products->weight = $_POST['weight_value'];
										$Products->quantity = $_POST['total_quantity'];
					                    $Products->original_price = !empty($_POST['original_price']) ? $_POST['original_price']: 0;
										$Products->discount_price = $_POST['discount_price'];
										$Products->active_status =  isset($_POST['active_status']) ? $_POST['active_status']: 0;
										$Products->approval_status =  isset($_POST['publish_status']) ? $_POST['publish_status']: 0;
										$Products->created_date = date("Y-m-d H:i:s");
										$Products->created_by = Auth::id();
										$Products->product_url =  str_slug($_POST['product_name'][1]);
										//print_r($Products);exit;
										$Products->save();
										if(isset($_FILES['product_image']['name']) && $_FILES['product_image']['name']!=''){
											$imageName = $id.'.'.$data->file('product_image')->getClientOriginalExtension();
											if($product_id != 0)
											{
												Image::make( $destinationPath1 )->save(base_path() .'/public/assets/admin/base/images/products/'.$imageName);
											}
											else {
												$destinationPath1 = url('/assets/admin/base/images/products/'.$imageName);
												$data->file('product_image')->move(base_path() . '/public/assets/admin/base/images/products/', $imageName);
											}
											$size=getImageResize('PRODUCT');
											Image::make( $destinationPath1 )->fit($size['LIST_WIDTH'], $size['LIST_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/list/'.$imageName)->destroy();
											Image::make( $destinationPath1 )->fit($size['THUMB_WIDTH'], $size['THUMB_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/thumb/'.$imageName)->destroy();
											$Products->product_image=$imageName;
											$Products->save();
										}
										else {
											if($old_product_image_url != '')
											{
												$size = getImageResize('PRODUCT');
												Image::make($old_product_image_url)->save(base_path() .'/public/assets/admin/base/images/products/'.$old_product_image);
												Image::make($old_product_image_url)->fit($size['LIST_WIDTH'], $size['LIST_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/list/'.$old_product_image);
												Image::make( $old_product_image_url )->fit($size['THUMB_WIDTH'], $size['THUMB_HEIGHT'])->save(base_path() .'/public/assets/admin/base/images/products/thumb/'.$old_product_image);
												$Products->product_image=$old_product_image;
												$Products->save();
											}
										}
										if(isset($_FILES['product_info_image']['name']) && $_FILES['product_info_image']['name']!=''){
											$info_imageName = $id.'.'.$data->file('product_info_image')->getClientOriginalExtension();
											if($product_id != 0)
											{
												Image::make( $destinationPath2 )->save(base_path() .'/public/assets/admin/base/images/products/detail/'.$info_imageName);
											}
											else {
												$destinationPath2 = url('/assets/admin/base/images/products/detail/'.$info_imageName);
												$data->file('product_info_image')->move(base_path() . '/public/assets/admin/base/images/products/detail/', $info_imageName);
											}
											$Products->product_info_image=$info_imageName;
											$Products->save();
										}
										else {
											if($old_product_info_image_url != '')
											{
												Image::make( $old_product_info_image_url )->save(base_path() .'/public/assets/admin/base/images/products/detail/'.$old_product_info_image);
												$Products->product_info_image=$old_product_info_image;
												$Products->save();
											}
										}
										if(isset($_FILES['product_zoom_image']['name']) && $_FILES['product_zoom_image']['name']!=''){
											$zoom_imageName = $id.'.'.$data->file('product_zoom_image')->getClientOriginalExtension();
											if($product_id != 0)
											{
												Image::make( $destinationPath3 )->save(base_path() .'/public/assets/admin/base/images/products/zoom/'.$zoom_imageName);
											}
											else {
												$destinationPath3 = url('/assets/admin/base/images/products/zoom/'.$zoom_imageName);
												$data->file('product_info_image')->move(base_path() . '/public/assets/admin/base/images/products/zoom/', $zoom_imageName);
											}
											$Products->product_info_image=$info_imageName;
											$Products->save();
										}
										else {
											if($old_product_zoom_image_url != '')
											{
												Image::make( $old_product_zoom_image_url )->save(base_path() .'/public/assets/admin/base/images/products/zoom/'.$old_product_zoom_image);
												$Products->product_zoom_image=$old_product_zoom_image;
												$Products->save();
											}
										}
										$product_id = $Products->id;
										$this->product_save_after($Products,$_POST);
									}
								}
							}
						}
					}
				}
				catch(Exception $e) {
					Log::Instance()->add(Log::ERROR, $e);
				}
              // redirect
		 
				$result["status"] = 200;
			Session::flash('message',trans('Product updated successfully'));
			$result["errors"] = "";
		 
          
        }
		return json_encode($result);exit;
    }

    /**
     * Delete the specified blog in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destory($id)
    {
        if(!hasTask('admin/products/delete_product')){
            return view('errors.404');
        }
        $data = Admin_products::find($id);
        if(!count($data)){
            Session::flash('message', 'Invalid Product Details'); 
            return Redirect::to('admin/products');    
        }
        //$data->delete();
        //Update delete status while deleting
        $data->active_status = 2;
        $data->save();
        Session::flash('message', trans('messages.Product has been deleted successfully!'));
        return Redirect::to('admin/products');
    }
    
    /**
     * add,edit datas  saved in main table 
     * after inserted in sub tabel.
     *
     * @param  int  $id
     * @return Response
     */
   public static function product_save_after($object,$post)
   {
        if(isset($post['product_name'])){
            $product_name = $post['product_name'];
            $description = $post['description'];
            $meta_title = $post['meta_title'];
            $meta_keywords = $post['meta_keywords'];
            $meta_description = $post['meta_description'];
            try{
                $affected = DB::table('products_infos')->where('id', '=', $object->id)->delete();
               // print_r($object->id);exit();
                $languages = DB::table('languages')->where('status', 1)->get();
                foreach($languages as $key => $lang){
                    if(isset($product_name[$lang->id]) && $product_name[$lang->id]!=""){
                        $infomodel = new Products_infos;
                        $infomodel->lang_id = $lang->id;
                        $infomodel->id = $object->id; 
                        $infomodel->product_name = $product_name[$lang->id];
                        $infomodel->description = $description[$lang->id];
                        $infomodel->meta_title = $meta_title[$lang->id];
                        $infomodel->meta_keywords = $meta_keywords[$lang->id];
                        $infomodel->meta_description = $meta_description[$lang->id];
                        //$infomodel->product_url =  $post['product_url'] ? str_slug($post['product_url']): str_slug($post['product_name'][1]);
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
    public function anyAjaxItems(Request $request)
    {   //print_r("expression");exit();
    	$language = getAdminCurrentLang();
    	//print_r($language);exit();
        $cdata = Admin_products::join("categories_infos",function($join){
                    $join->on("categories_infos.category_id","=","admin_products.category_id")
                        ->on("categories_infos.language_id","=","admin_products.lang_id");
                })
				->select('admin_products.id','admin_products.quantity','admin_products.status as active_status','admin_products.approval_status','admin_products.product_name','categories_infos.category_name')
               	->where('admin_products.lang_id',$language)
                ->orderBy('admin_products.created_date', 'desc');
            return Datatables::of($cdata)->addColumn('action', function ($cdata) {
            if(hasTask('admin/products/edit_product'))
            { 
                $html='<div class="btn-group">
                    <a href="'.URL::to("admin/products/edit_product/".$cdata->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button> 
                    <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/products/product_details/".$cdata->id).'" class="view-'.$cdata->id.'" title="'.trans("messages.View").'"><i class="fa fa-file-text-o"></i>&nbsp;&nbsp;'.@trans("messages.View").'</a></li>
                        <li><a href="'.URL::to("admin/products/delete_product/".$cdata->id).'" class="delete-'.$cdata->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                        <li><a href="'.URL::to("product/info/".$cdata->url_index."/".$cdata->product_url).'" class="preview-'.$cdata->id.'" target ="blank" title="'.trans("messages.Preview").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'. @trans("messages.Preview").'</a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $( document ).ready(function() {
                        $(".delete-'.$cdata->id.'").on("click", function(){
                            return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });
                    });
                </script>';
                return $html;
            }
        })
         ->addColumn('id', function ($cdata) {
            if(hasTask('admin/products/edit_product')){
			     $data ="<input type='checkbox'  class='deleteRow' value='".$cdata['id']."'  /> ".$cdata['id'];
			     return $data;
			}else
			{
			    return $cdata['id'];
			}
		})

        ->addColumn('active_status', function ($cdata) {
            if($cdata->active_status==0):
                $data = '<span class="label label-warning">'.trans("messages.Inactive").'</span>';
            elseif($cdata->active_status==1):
                $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
            elseif($cdata->active_status==2):
                $data = '<span class="label label-danger">'.trans("messages.Deleted").'</span>';
            endif;
            return $data;
        })
        ->addColumn('approval_status', function ($cdata) {
            if($cdata->approval_status==0):
                $data = '<span class="label label-danger">'.trans("messages.UnPublished").'</span>';
            elseif($cdata->approval_status==1):
                $data = '<span class="label label-success">'.trans("messages.Published").'</span>';
            endif;
            return $data;
        })     
        ->editColumn('product_name', '{!! wordwrap($product_name, 30) !!}')
        ->setRowId('id')
        ->rawColumns(['approval_status','active_status','id','action'])
        ->make(true);
    }  
    public function anyAjaxItems_copy(Request $request)
    {   //print_r("expression");exit();
    	$language = getAdminCurrentLang();
        $query = '"products_infos"."lang_id" = (case when (select count(products_infos.id) as totalcount from products_infos where products_infos.lang_id = '.$language.' and products.id = products_infos.id) > 0 THEN '.$language.' ELSE 1 END)';
        $query1 = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = '.$language.' and products.outlet_id = outlet_infos.id) > 0 THEN '.$language.' ELSE 1 END)';
        $cdata = Products::join('products_infos','products.id','=','products_infos.id')
                    ->join('outlets','products.outlet_id','=','outlets.id')
                    ->join('outlet_infos','products.outlet_id','=','outlet_infos.id')
                    ->Leftjoin("categories_infos",function($join){
                        $join->on("categories_infos.category_id","=","products.category_id")
                            ->on("categories_infos.language_id","=","products_infos.lang_id");
                    })
                    ->join("vendors_infos",function($join){
                        $join->on("vendors_infos.id","=","products.vendor_id")
                            ->on("vendors_infos.lang_id","=","products_infos.lang_id");
                    })
                    
                    ->select('products.id','products.quantity','products.original_price','products.discount_price','products.product_url','products.active_status','products.approval_status','products_infos.product_name','categories_infos.category_name','vendors_infos.vendor_name','outlet_infos.outlet_name','outlets.url_index')
                    ->whereRaw($query)
                    ->whereRaw($query1)
                    ->orderBy('products.created_date', 'desc');
                    return Datatables::of($cdata)->addColumn('action', function ($cdata) {
            if(hasTask('admin/products/edit_product'))
            { 
                $html='<div class="btn-group">
                    <a href="'.URL::to("admin/products/edit_product/".$cdata->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button> 
                    <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/products/product_details/".$cdata->id).'" class="view-'.$cdata->id.'" title="'.trans("messages.View").'"><i class="fa fa-file-text-o"></i>&nbsp;&nbsp;'.@trans("messages.View").'</a></li>
                        <li><a href="'.URL::to("admin/products/delete_product/".$cdata->id).'" class="delete-'.$cdata->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                        <li><a href="'.URL::to("product/info/".$cdata->url_index."/".$cdata->product_url).'" class="preview-'.$cdata->id.'" target ="blank" title="'.trans("messages.Preview").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'. @trans("messages.Preview").'</a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $( document ).ready(function() {
                        $(".delete-'.$cdata->id.'").on("click", function(){
                            return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });
                    });
                </script>';
                return $html;
            }
        })
        
         ->addColumn('id', function ($cdata) {
            if(hasTask('admin/products/edit_product')){
			     $data ="<input type='checkbox'  class='deleteRow' value='".$cdata['id']."'  /> ".$cdata['id'];
			     return $data;
			}else
			{
			    return $cdata['id'];
			}
		})

        ->addColumn('active_status', function ($cdata) {
            if($cdata->active_status==0):
                $data = '<span class="label label-warning">'.trans("messages.Inactive").'</span>';
            elseif($cdata->active_status==1):
                $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
            elseif($cdata->active_status==2):
                $data = '<span class="label label-danger">'.trans("messages.Deleted").'</span>';
            endif;
            return $data;
        })
        ->addColumn('approval_status', function ($cdata) {
            if($cdata->approval_status==0):
                $data = '<span class="label label-danger">'.trans("messages.UnPublished").'</span>';
            elseif($cdata->approval_status==1):
                $data = '<span class="label label-success">'.trans("messages.Published").'</span>';
            endif;
            return $data;
        })
        ->addColumn('original_price', function ($cdata) {
            
              $currency_side   = getCurrencyPosition()->currency_side;
              $currency_symbol = getCurrency(); 
            if($currency_side == 1)
            {
                $data=$currency_symbol.$cdata->original_price;
            }
            else {
                $data=$cdata->original_price.$currency_symbol;
            }
            return $data;
        })
        ->addColumn('discount_price', function ($cdata) {
            
              $currency_side   = getCurrencyPosition()->currency_side;
              $currency_symbol = getCurrency(); 
            if($currency_side == 1)
                        {
                        $data=$currency_symbol.$cdata->discount_price;
                        }
                        else {
                        $data=$cdata->discount_price.$currency_symbol;
                        }
            return $data;
        })
        ->editColumn('product_name', '{!! wordwrap($product_name, 30) !!}')
        ->setRowId('id')
        ->make(true);
    }
    public function bulkdelete(Request $request)
	{
		if($request->ajax()){
			$data_ids = $request->input('data_ids');
			$data_id_array = explode(",", $data_ids); 
			if(!empty($data_id_array)) {
				foreach($data_id_array as $id) {
					$products = Admin_products::find($id);
					$products->delete();
				}
			}
			return response()->json([
				'data' => true
			]);
		}
	}

	
}
