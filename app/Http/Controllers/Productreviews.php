<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Model\products;

use App\Model\product_reviews;
use App\Model\vendors;
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
use Illuminate\Support\Facades\Text;
use App\Model\Payment\gateways;
use Illuminate\Support\Facades\Input;
use Yajra\Datatables\Datatables;
use URL;


class Productreviews extends Controller
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
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('admin/product-reviews')){
                return view('errors.404');
            }
            return view('admin.product_reviews.list');
        }
    }
    
   
	public function anyAjaxproductreviewlist()
    {  
        $query = '"products_infos"."lang_id" = (case when (select count(products_infos.id) as totalcount from products_infos where products_infos.lang_id = '.getAdminCurrentLang().' and product_reviews.product_id = products_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $product_reviews = DB::table('product_reviews')
                    ->select('product_reviews.id as review_id','product_reviews.customer_id as review_customer_id','product_reviews.vendor_id as review_vendor_id','product_reviews.comments','product_reviews.title','product_reviews.approval_status','product_reviews.ratings','product_reviews.created_date as review_posted_date','users.name as user_name','users.email as user_email','users.id as user_id','users.image as user_image','products_infos.product_name')
                    ->leftJoin('users','users.id','=','product_reviews.customer_id')
                   // ->leftJoin('products','products.id','=','product_reviews.id')
                    ->leftJoin('products_infos','product_reviews.product_id','=','products_infos.id')
                    //->leftJoin('vendors','vendors.id','=','product_reviews.vendor_id')
                    ->whereRaw($query)
                    ->orderBy('product_reviews.id', 'desc');
					
        return Datatables::of($product_reviews)->addColumn('action', function ($product_reviews) {
            
            if(hasTask('admin/product_reviews/view'))
            {
                $review_status_opt = '';
                if($product_reviews->approval_status == 0):
                    $review_status_opt = '<li><a href="'.URL::to("admin/product-reviews/approve/".$product_reviews->review_id.'?status=1').'" class="block-'.$product_reviews->review_id.'"  title="'.trans("messages.UnBlock").'"> <i class="fa fa-lock"></i>&nbsp;&nbsp;UnBlock</a></li>
                    <script type="text/javascript">
                        $( document ).ready(function() {
                            $(".block-'.$product_reviews->review_id.'").on("click", function(){
                                return confirm("'.trans("messages.Are you sure want to approve ?").'");
                            });
                        });
                    </script>';
                endif;
                return '<div class="btn-group"><a href="'.URL::to("admin/product-reviews/view/?review_id=".$product_reviews->review_id).'" class="btn btn-xs btn-white" title="'.trans("messages.View").'"><i class="fa fa-eye"></i>&nbsp;'.trans("messages.View").'</a>
                        <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu xs pull-right" role="menu">'.$review_status_opt.'
                            <li><a href="'.URL::to("admin/product-reviews/delete/".$product_reviews->review_id).'" class="delete-'.$product_reviews->review_id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li></ul>
                        </ul>
                    </div>
                    <script type="text/javascript">
                        $( document ).ready(function() {
                            $(".delete-'.$product_reviews->review_id.'").on("click", function(){
                                return confirm("'.trans("messages.Are you sure want to delete?").'");
                            });
                        });
                    </script>';
            }
        })
        ->addColumn('approval_status', function ($product_reviews) {
            if($product_reviews->approval_status==0):
                $data = '<span  class="label label-danger">'.trans("messages.Pending").'</span>';
            elseif($product_reviews->approval_status==1):
                $data = '<span  class="label label-success">'.trans("messages.Approved").'</span>';
            endif;
            return $data;print_r( $product_reviews);exit;
        })
        ->addColumn('transaction_date', function ($product_reviews) {
                $data = '<span> '.date('d - M - Y h:i A' , strtotime($product_reviews->review_posted_date)).'</span>';
            return $data;
        })
        ->addColumn('product_name', function ($product_reviews) {
            return ucfirst($product_reviews->product_name);
        })
        ->editColumn('comments', '{!! str_limit($comments,30) !!}')
        ->rawColumns(['product_name','transaction_date','approval_status','action'])

        ->make(true);
    }
	 public function destory($id)
    {
        if(!hasTask('admin/product-reviews/delete'))
        {
            return view('errors.404');
        }
       
        $reviews = product_reviews::find($id);
        $reviews->delete();
        Session::flash('message', trans('messages.Review has been deleted successfully!'));
        return Redirect::to('admin/Productreviews');
    }
	 public function approve($id)
    {
        if(!hasTask('admin/product-reviews/approve'))
        {
            return view('errors.404');
        }
        $reviews = product_reviews::find($id);
        if(!count($reviews)){
            Session::flash('message', trans('messages.Invalid data'));
            return Redirect::to('admin/Productreviews');
        }
        $reviews->approval_status    = 1;
        $reviews->save();

        /**  vendor review average calculating and updated here **/ 
        $reviews_average=DB::table('product_reviews')
                ->selectRaw('SUM(ratings) as total_rating,count(product_reviews.product_id) as tcount')
                ->where("product_reviews.product_id","=",$reviews->product_reviews)
                ->where("product_reviews.approval_status","=",1)
                ->first();
       
        
        /**  vendor review average calculating and updated here **/ 
       
        Session::flash('message', trans('messages.Review has been approved successfully!'));
        return Redirect::to('admin/product-reviews');
    }
	public function view()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else{
            if(!hasTask('admin/product-reviews/view'))
            {
                return view('errors.404');
            }
            $query  = '"products_infos"."lang_id" = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = '.getAdminCurrentLang().' and product_reviews.product_id = products_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
          
            $review_id=Input::get('review_id');
            $reviews=DB::table('product_reviews')
                ->select('product_reviews.id as review_id','product_reviews.customer_id as review_customer_id','product_reviews.vendor_id as review_vendor_id','product_reviews.comments','product_reviews.title','product_reviews.approval_status','product_reviews.ratings','product_reviews.created_date as review_posted_date','users.name as user_name','users.email as user_email','users.id as user_id','users.image as user_image','products_infos.product_name','outlet_infos.outlet_name')
                ->leftJoin('users','users.id','=','product_reviews.customer_id')
                ->leftJoin('outlet_infos','outlet_infos.id','=','product_reviews.product_id')
                ->leftJoin('products_infos','products_infos.id','=','product_reviews.product_id')
                ->where("product_reviews.id","=",$review_id)
                ->whereRaw($query)
                ->get();
            if(!count($reviews)){
                Session::flash('message', trans('messages.Invalid Request'));
                return Redirect::to('admin/reviews');
            }
            return view('admin.product_reviews.show')->with('review', $reviews[0]);
        }
    }
}
