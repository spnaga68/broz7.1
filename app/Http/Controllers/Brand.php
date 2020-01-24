<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Model\users;
use App\Model\brands;
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


class Brand  extends Controller
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
            if(!hasTask('admin/brands')){
                return view('errors.404');
            }
            return view('admin.brands.list');
        }
    }
    
         /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function anyAjaxbrandlist()
    {
        $brands = DB::table('brands')->select('id', 'brand_title', 'brand_link', 'created_at', 'status')->orderBy('id', 'desc');
        return Datatables::of($brands)->addColumn('action', function ($brands) {
            if(hasTask('admin/brand/edit'))
            {
                $html ='<div class="btn-group"><a href="'.URL::to("admin/brand/edit/".$brands->id).'" class="btn btn-xs btn-white" title="'.trans("messages.Edit").'"><i class="fa fa-edit"></i>&nbsp;'.trans("messages.Edit").'</a>
                    <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu xs pull-right" role="menu">
                        <li><a href="'.URL::to("admin/brand/delete/".$brands->id).'" class="delete-'.$brands->id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li>
                    </ul>
                </div>
                <script type="text/javascript">
                    $( document ).ready(function() {
                        $(".delete-'.$brands->id.'").on("click", function(){
                            return confirm("'.trans("messages.Are you sure want to delete?").'");
                        });
                    });
                </script>';
                return $html;
            }
        })
        ->addColumn('status', function ($brands) {
            if($brands->status==0):
                $data = '<span class="label label-danger">'.trans("messages.Inactive").'</span>';
            elseif($brands->status==1):
                $data = '<span class="label label-success">'.trans("messages.Active").'</span>';
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
    public function create()
    { 
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('admin/brand/create')){
                return view('errors.404');
            }
            return view('admin.brands.create');
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
            'brand_title' => 'required',
            'brand_image'=> 'required|mimes:png,jpg,jpeg|max:2024',
            'brand_link' => 'required|url',
        ));
        // process the validation
        if ($validation->fails()) {
                //return redirect('create')->withInput($data1)->withErrors($validation);
                return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store
            $Brands = new Brands;
            $Brands->brand_title      = $_POST['brand_title'];
            $Brands->brand_link    = $_POST['brand_link'];
            $Brands->created_at = date("Y-m-d H:i:s");
            $Brands->status    = isset($_POST['status']);
			//print_r($Brands);exit;
            $Brands->save();
            $imageName = $Brands->id . '.' . 
            $data->file('brand_image')->getClientOriginalExtension();
            $data->file('brand_image')->move(
                base_path() . '/public/assets/admin/base/images/brand/', $imageName
            );
            $destinationPath1 = url('/assets/admin/base/images/brand/'.$imageName.'');
            
            Image::make( $destinationPath1 )->fit(482, 316)->save(base_path() .'/public/assets/admin/base/images/brand/thumb/'.$imageName)->destroy();
            $Brands->brand_image=$imageName;
            $Brands->save();
            // redirect
            Session::flash('message', trans('messages.Brand has been created successfully'));
            return Redirect::to('admin/brands');
        }
    }
	
	 public function edit($id)
    {
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        }else{
            if(!hasTask('admin/brand/edit')){
                return view('errors.404');
            }
            $brands = Brands::find($id);
			//print_r($brands);exit;
            return view('admin.brands.edit')->with('data', $brands);
        }
    }
    public function update(Request $data,$id)
    {
        if(!hasTask('admin/brand/update')){
            return view('errors.404');
        }
        $data1=Input::all();
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $validation = Validator::make($data->all(), array(
            'brand_title' => 'required|unique:brands,brand_title,'.$id.',id',
            'brand_image'=> 'mimes:png,jpg,jpeg|max:2024',
            'brand_link' => 'required|url',
            //'brand_link'   => 'required|regex:/^((?:https?\:\/\/|www\.)(?:[-a-z0-9]+\.)*[-a-z0-9]+.*)$/',
        ));
        // process the validation
        if ($validation->fails()) {
            //return redirect('create')->withInput($data1)->withErrors($validation);
            return Redirect::back()->withErrors($validation)->withInput();
        } else {
            // store
            $Brands = Brands::find($id); 
            $Brands->brand_title   = $_POST['brand_title'];
            $Brands->brand_link    = $_POST['brand_link'];
            $Brands->updated_at = date("Y-m-d H:i:s");
            $Brands->status    = isset($_POST['status']);
            $Brands->save();
            if(isset($_FILES['brand_image']['name']) && $_FILES['brand_image']['name']!=''){
                $imageName = $Brands->id . '.' . 
                $data->file('brand_image')->getClientOriginalExtension();
                $data->file('brand_image')->move(
                    base_path() . '/public/assets/admin/base/images/brand/', $imageName
                );
                $destinationPath1 = url('/assets/admin/base/images/brand/'.$imageName.'');
                $size=getImageResize('BRAND');
                Image::make( $destinationPath1 )->fit(482, 316)->save(base_path() .'/public/assets/admin/base/images/brand/thumb/'.$imageName)->destroy();
                $Brands->brand_image=$imageName;
                $Brands->save();
            }
            // redirect
            Session::flash('message', trans('messages.Brand has been updated successfully'));
            return Redirect::to('admin/brands');
        }
    }
    public function destory($id)
    {
        if(!hasTask('admin/brand/delete'))
		{
			return view('errors.404');
		}
        $data = Brands::find($id);
        if(!count($data))
        {
            Session::flash('message', 'Invalid Brand Details'); 
            return Redirect::to('admin/coupons');
        }
        if(file_exists(base_path().'/public/assets/admin/base/images/brand/'.$data->product_image) && $data->product_image != '')
        {
            unlink(base_path().'/public/assets/admin/base/images/brand/'.$data->product_image);
        }
        DB::table('brands')->where('id', '=', $id)->delete();
        $data->delete();
        Session::flash('message', trans('messages.Brand has been deleted successfully'));
        return Redirect::to('admin/brands');
    }
    
}
