<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Model\outlets;

use App\Model\outlet_reviews;
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


class Reviews extends Controller
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
            if(!hasTask('admin/reviews')){
                return view('errors.404');
            }
            return view('admin.reviews.list');
        }
    }
    
         /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function anyAjaxreviewlist()
    {
        $query = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = '.getAdminCurrentLang().' and outlet_reviews.outlet_id = outlet_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $reviews = DB::table('outlet_reviews')
                    ->select('outlet_reviews.id as review_id','outlet_reviews.customer_id as review_customer_id','outlet_reviews.vendor_id as review_vendor_id','outlet_reviews.comments','outlet_reviews.title','outlet_reviews.approval_status','outlet_reviews.ratings','outlet_reviews.created_date as review_posted_date','users.name as user_name','users.email as user_email','users.id as user_id','users.image as user_image','vendors.id as store_id','vendors.first_name as store_first_name','vendors.last_name as store_last_name','vendors.email as store_email','vendors.phone_number as store_phone_number','outlet_infos.outlet_name','outlets.id as outletid')
                    ->leftJoin('users','users.id','=','outlet_reviews.customer_id')
                    ->leftJoin('outlets','outlets.id','=','outlet_reviews.outlet_id')
                    ->leftJoin('outlet_infos','outlets.id','=','outlet_infos.id')
                    ->leftJoin('vendors','vendors.id','=','outlets.vendor_id')
                    ->whereRaw($query)
                    ->orderBy('outlet_reviews.id', 'desc');
        //echo"<pre>";print_r($reviews);exit;
        return Datatables::of($reviews)->addColumn('action', function ($reviews) {
            
            if(hasTask('admin/reviews/view'))
            {
                $review_status_opt = '';
                if($reviews->approval_status == 0):
                    $review_status_opt = '<li><a href="'.URL::to("admin/reviews/approve/".$reviews->review_id.'?status=1').'" class="block-'.$reviews->review_id.'"  title="'.trans("messages.UnBlock").'"> <i class="fa fa-lock"></i>&nbsp;&nbsp;UnBlock</a></li>
                    <script type="text/javascript">
                        $( document ).ready(function() {
                            $(".block-'.$reviews->review_id.'").on("click", function(){
                                return confirm("'.trans("messages.Are you sure want to approve ?").'");
                            });
                        });
                    </script>';
                endif;
                return '<div class="btn-group"><a href="'.URL::to("admin/reviews/view/?review_id=".$reviews->review_id).'" class="btn btn-xs btn-white" title="'.trans("messages.View").'"><i class="fa fa-eye"></i>&nbsp;'.trans("messages.View").'</a>
                        <button type="button" class="btn btn-xs btn-white dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu xs pull-right" role="menu">'.$review_status_opt.'
                            <li><a href="'.URL::to("admin/reviews/delete/".$reviews->review_id).'" class="delete-'.$reviews->review_id.'" title="'.trans("messages.Delete").'"><i class="fa fa-trash-o"></i>&nbsp;&nbsp;'.@trans("messages.Delete").'</a></li></ul>
                        </ul>
                    </div>
                    <script type="text/javascript">
                        $( document ).ready(function() {
                            $(".delete-'.$reviews->review_id.'").on("click", function(){
                                return confirm("'.trans("messages.Are you sure want to delete?").'");
                            });
                        });
                    </script>';
            }
        })


           
        ->addColumn('review_id', function ($reviews) {
           if($reviews->approval_status==0):
                $data ="<input type='checkbox'  class='deleteRow' value='".$reviews->review_id."'  /> ".$reviews->review_id;

            else :
                return $reviews->review_id; 
            endif;
           return $data;
           
        })
        ->addColumn('approval_status', function ($reviews) {
            if($reviews->approval_status==0):
                $data = '<span  class="label label-danger">'.trans("messages.Pending").'</span>';
            elseif($reviews->approval_status==1):
                $data = '<span  class="label label-success">'.trans("messages.Approved").'</span>';
            endif;
            return $data;
        })
        ->addColumn('transaction_date', function ($reviews) {
                $data = '<span> '.date('d - M - Y h:i A' , strtotime($reviews->review_posted_date)).'</span>';
            return $data;
        })
        ->addColumn('outlet_name', function ($reviews) {
            return ucfirst($reviews->outlet_name);
        })
        ->editColumn('comments', '{!! str_limit($comments,30) !!}')

        ->rawColumns(['outlet_name','transaction_date','approval_status','review_id','action'])

        ->make(true);
    }

    public function approve($id)
    {
        if(!hasTask('admin/reviews/approve'))
        {
            return view('errors.404');
        }
        $reviews = outlet_reviews::find($id);
        if(!count($reviews)){
            Session::flash('message', trans('messages.Invalid data'));
            return Redirect::to('admin/reviews');
        }
        $reviews->approval_status    = 1;
        $reviews->save();

        /**  vendor review average calculating and updated here **/ 
        $reviews_average=DB::table('outlet_reviews')
                ->selectRaw('SUM(ratings) as total_rating,count(outlet_reviews.outlet_id) as tcount')
                ->where("outlet_reviews.outlet_id","=",$reviews->outlet_id)
                ->where("outlet_reviews.approval_status","=",1)
                ->get();
        //echo"<pre>";print_r($reviews);exit;
        if(count($reviews_average)){
            $total_rating = $reviews_average[0]->total_rating;
            $average_rating=$total_rating/$reviews_average[0]->tcount;
            $outlets = outlets::find($reviews->outlet_id);
            $outlets->average_rating    = round($average_rating);
            $outlets->save();
        }
        
        /**  vendor review average calculating and updated here **/ 
        $outlets_reviews_average=DB::table('outlets')
                ->selectRaw('SUM(average_rating) as total_outlet_rating,count(outlets.id) as tcount')
                ->where("outlets.vendor_id","=",$reviews->vendor_id)
                ->get();
        if(count($outlets_reviews_average)){
            $ototal_rating = $outlets_reviews_average[0]->total_outlet_rating;
            $oaverage_rating=$ototal_rating/$outlets_reviews_average[0]->tcount;
            $vendors = Vendors::find($reviews->vendor_id);
            $vendors->average_rating    = round($oaverage_rating);
            $vendors->save();
        }
        
        Session::flash('message', trans('messages.Review has been approved successfully!'));
        return Redirect::to('admin/reviews');
    }
    
    /**
     * Display the specified review.
     *
     * @param  int  $id
     * @return Response
     */
    public function view()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else{
            if(!hasTask('admin/reviews/view'))
            {
                return view('errors.404');
            }
            $query  = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = '.getAdminCurrentLang().' and vendors.id = vendors_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $query1 = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.language_id) as totalcount from outlet_infos where outlet_infos.language_id = '.getAdminCurrentLang().' and outlet_reviews.outlet_id = outlet_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
            $review_id=Input::get('review_id');
            $reviews=DB::table('outlet_reviews')
                ->select('outlet_reviews.id as review_id','outlet_reviews.customer_id as review_customer_id','outlet_reviews.vendor_id as review_vendor_id','outlet_reviews.comments','outlet_reviews.title','outlet_reviews.approval_status','outlet_reviews.ratings','outlet_reviews.created_date as review_posted_date','users.name as user_name','users.email as user_email','users.id as user_id','users.image as user_image','vendors.id as store_id','vendors.email as store_email','vendors.phone_number as store_phone_number','outlet_infos.outlet_name','outlets.id as outletid','vendors_infos.vendor_name as store_name')
                ->leftJoin('users','users.id','=','outlet_reviews.customer_id')
                ->leftJoin('outlets','outlets.id','=','outlet_reviews.outlet_id')
                ->leftJoin('outlet_infos','outlet_infos.id','=','outlets.id')
                ->leftJoin('vendors','vendors.id','=','outlets.vendor_id')
                ->leftJoin('vendors_infos','vendors_infos.id','=','vendors.id')
                ->where("outlet_reviews.id","=",$review_id)
                ->whereRaw($query)
                ->whereRaw($query1)
                ->get();
            if(!count($reviews)){
                Session::flash('message', trans('messages.Invalid Request'));
                return Redirect::to('admin/reviews');
            }
            return view('admin.reviews.show')->with('review', $reviews[0]);
        }
    }
    public function destory($id)
    {
        if(!hasTask('admin/reviews/delete'))
        {
            return view('errors.404');
        }
        //print_r($id);exit;
        $reviews = outlet_reviews::find($id);
        $reviews->delete();
        Session::flash('message', trans('messages.Review has been deleted successfully!'));
        return Redirect::to('admin/reviews');
    }
	public function product_reviews()
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
        $query = '"products_infos"."lang_id" = (case when (select count(products_infos.id) as totalcount from products_infos where products_infos.lang_id = '.getAdminCurrentLang().' and product_reviews.id = products_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
        $product_reviews = DB::table('product_reviews')
                    ->select('product_reviews.id as review_id','product_reviews.customer_id as review_customer_id','product_reviews.vendor_id as review_vendor_id','product_reviews.comments','product_reviews.title','product_reviews.approval_status','product_reviews.ratings','product_reviews.created_date as review_posted_date','users.name as user_name','users.email as user_email','users.id as user_id','users.image as user_image','vendors.id as store_id','vendors.first_name as store_first_name','vendors.last_name as store_last_name','vendors.email as store_email','vendors.phone_number as store_phone_number','products_infos.product_name')
                    ->leftJoin('users','users.id','=','product_reviews.customer_id')
                   // ->leftJoin('products','products.id','=','product_reviews.id')
                    ->leftJoin('products_infos','product_reviews.id','=','products_infos.id')
                    ->leftJoin('vendors','vendors.id','=','product_reviews.vendor_id')
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
    public function bulkapprove(Request $request)
    {

        if($request->ajax()){
            $data_ids = $request->input('data_ids');
            //print_r($data_ids);exit();

            $data_id_array = explode(",", $data_ids); 
            if(!empty($data_id_array)) {
                foreach($data_id_array as $id) {
                    $reviews = outlet_reviews::find($id);
                    $reviews->approval_status    = 1;
                    $reviews->save();
                }
            }
            return response()->json([
                'data' => true
            ]);
        }
    }

    public function telr_gateway()
    {
        $params = array(
          'ivp_store'      => 'Your Store ID',
          'ivp_authkey'  => 'Your Authentication Key',
          'ivp_trantype'   => 'sale',
          'ivp_tranclass'  => 'cont',
          'ivp_desc'       => 'Product Description',
          'ivp_cart'       => 'Your Cart ID',
          'ivp_currency'   => 'AED',
          'ivp_amount'     => '100.00',
          'tran_ref'       => '12 digit reference of intial ecom/moto transaction',
          'ivp_test'       => '1'
        );

        ///auth_status=E&auth_code=04&auth_message=Invalid%20store%20ID&auth_tranref=000000000000&auth_cvv=X&auth_avs=X&auth_trace=4000%2f7479%2f5d2da68f&payment_code=&payment_desc=
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://secure.telr.com/gateway/remote.html");
        curl_setopt($ch, CURLOPT_POST, count($params));
        curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        $results = curl_exec($ch);
        curl_close($ch);
               echo"<pre>"; print_r($results);exit;

    }
}
