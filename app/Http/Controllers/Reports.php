<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Model\users;
use App\Model\users_activity;
use App\Model\Users\groups;
use App\Model\Users\addresstype;
use App\Model\settings;
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
use Illuminate\Support\Facades\Text;
use Hash;
use App\Model\vendors;
use App\Model\vendors_infos;

class Reports extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
     const ORDER_STATUS_UPDATE_USER = 18;
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
    
    public function order()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else{
            if(!hasTask('reports/order'))
            {
                return view('errors.404');
            }
            $order_status = DB::table('order_status')->select('id','name')->orderBy('name', 'asc')->get();
            SEOMeta::setTitle('Orders Reports - '.$this->site_name);
            SEOMeta::setDescription('Orders Reports - '.$this->site_name);
            return view('admin.reports.orders.list')->with('order_status',$order_status);
        }
    }
    public function anyAjaxReportOrderList(Request $request)
    {
    $post_data = $request->all();
    //print_r($post_data);exit();
       $query = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . getCurrentLang() . ' and orders.vendor_id = vendors_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
        $query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' .getCurrentLang() . ' and orders.outlet_id = outlet_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
        $orders    = DB::table('orders')
                        ->selectRaw('COUNT(orders.id) AS orders_count, SUM(orders.service_tax) AS tax_total, SUM((SELECT SUM(orders_info.item_unit) FROM orders_info WHERE orders_info.order_id = orders.id GROUP BY orders_info.order_id)) AS quantity_count,  SUM(orders.total_amount) AS total,vendors_infos.vendor_name,outlet_infos.outlet_name')
                         ->join('vendors_infos','vendors_infos.id','=','orders.vendor_id')
                         ->join('outlet_infos','outlet_infos.id','=','orders.outlet_id')
                         ->whereRaw($query)
                         ->whereRaw($query1)
                        ->groupBy('vendors_infos.vendor_name','outlet_infos.outlet_name','orders.id');

        return Datatables::of($orders)->addColumn('date_start', function ($orders) {
            $data = date("M-d-Y, l h:i:a", strtotime($orders->date_start));
            return $data;
        })
        ->addColumn('orders_count', function ($orders) {
            $data = $orders->orders_count;
            return $data;
        })        
        ->addColumn('date_end', function ($orders) {
            $data = date("M-d-Y, l h:i:a", strtotime($orders->date_end));
            return $data;
        })
           
           ->addColumn('vendor_name', function ($orders) {
            $data = $orders->vendor_name;
            return $data;
        })
             ->addColumn('outlet_name', function ($orders) {
            $data = $orders->outlet_name;
            return $data;
        })
        ->addColumn('tax_total', function ($orders) {
            if(getCurrencyPosition()->currency_side == 1)
            {
                return getCurrency().$orders->tax_total;
            }
            else {
                return $orders->tax_total.getCurrency();
            }
        })
        ->addColumn('total', function ($orders) {
            if(getCurrencyPosition()->currency_side == 1)
            {
                return getCurrency().$orders->total;
            }
            else {
                return $orders->total.getCurrency();
            }
        })
        ->filter(function ($query) use ($request){
            $condition = '1=1';
            if (($request->has('from') && !empty($request->get('from')))&& ($request->has('to')) && !empty($request->get('to')))
            {
                $from = date('Y-m-d H:i:s', strtotime($request->get('from')));
                $to   = date('Y-m-d H:i:s', strtotime($request->get('to')));
                $condition .= " and orders.created_date BETWEEN '".$from."'::timestamp and '".$to."'::timestamp";
            }
            if ($request->has('order_status') != '' && !empty(Input::get('order_status')))
            {
                $order_status = Input::get('order_status');
                $condition   .= " and orders.order_status = ".$order_status;
                $query->whereRaw($condition);
            }
            if ($request->has('vendor') != '' && !empty(Input::get('vendor')))
            {
                $vendor = Input::get('vendor');
                $condition .=" and orders.vendor_id = ".$vendor."";
            }
            if ($request->has('outlet') != '' && !empty(Input::get('outlet')))
            {
                $outlet = Input::get('outlet');
                $condition .=" and orders.outlet_id = ".$outlet."";
            }
            if ($request->has('group_by') && !empty($request->get('group_by')))
            {
                $group_by = ($request->get('group_by') != '')?$request->get('group_by'):1;
                if($group_by == 1)
                    $start_date = " date_trunc('day', orders.created_date) AS date_start, ";
                else if($group_by == 2)
                    $start_date = " date_trunc('week', orders.created_date) AS date_start, ";
                else if($group_by == 3)
                    $start_date = " date_trunc('month', orders.created_date) AS date_start, ";
                else if($group_by == 4)
                    $start_date = " date_trunc('year', orders.created_date) AS date_start, ";
                $query->selectRaw($start_date.' MAX(orders.created_date) AS date_end')->whereRaw($condition)->groupBy('date_start')->orderby('date_start', 'DESC');
            }
        })

        ->rawColumns(['total','tax_total','outlet_name','date_end','orders_count','date_start'])

        ->make(true);
    }
    /* Reports for return orders */
    public function returns()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('reports/returns'))
            {
                return view('errors.404');
            }
            $order_status = DB::table('return_status')->select('id','name')->orderBy('name', 'asc')->get();
            return view('admin.reports.returns.returns')->with('order_status', $order_status);
        }
    }
    /* Return orders for ajax list */

    public function anyAjaxReportReturnOrderList(Request $request)
    {
        $post_data = $request->all();
        $orders    = DB::table('return_orders')->selectRaw('COUNT(return_orders.order_id) AS return_orders_count');
        return Datatables::of($orders)->addColumn('date_start', function ($orders) {
            $data = date("M-d-Y, l h:i:a", strtotime($orders->date_start));
            return $data;
        })
        ->addColumn('date_end', function ($orders) {
            $data = date("M-d-Y, l h:i:a", strtotime($orders->date_end));
            return $data;
        })
        ->filter(function ($query) use ($request){
            $condition = '1=1';
            if (($request->has('from') && !empty($request->get('from')))&& ($request->has('to')) && !empty($request->get('to')))
            {
                $from = date('Y-m-d H:i:s', strtotime($request->get('from')));
                $to   = date('Y-m-d H:i:s', strtotime($request->get('to')));
                $condition .= " and return_orders.created_at BETWEEN '".$from."'::timestamp and '".$to."'::timestamp";
            }
            if ($request->has('order_status') != '' && !empty(Input::get('order_status')))
            {
                $order_status = Input::get('order_status');
                $condition   .= " and return_orders.return_reason = ".$order_status;
                $query->whereRaw($condition);
            }
            if ($request->has('group_by') && !empty($request->get('group_by')))
            {
                $group_by = ($request->get('group_by') != '')?$request->get('group_by'):1;
                if($group_by == 1)
                    $start_date = " date_trunc('day', return_orders.created_at) AS date_start, ";
                else if($group_by == 2)
                    $start_date = " date_trunc('week', return_orders.created_at) AS date_start, ";
                else if($group_by == 3)
                    $start_date = " date_trunc('month', return_orders.created_at) AS date_start, ";
                else if($group_by == 4)
                    $start_date = " date_trunc('year', return_orders.created_at) AS date_start, ";
                $query->selectRaw($start_date.' MAX(return_orders.created_at) AS date_end')->groupBy('date_start')->orderby('date_start', 'DESC');
            }
        })

        ->rawColumns(['date_start','date_end'])

        ->make(true);

    
    }

    /*public function anyAjaxReportReturnOrderList(Request $request)
    {
        $post_data = $request->all();
        $orders    = DB::table('return_orders')->selectRaw('COUNT(return_orders.order_id) AS return_orders_count');
        return Datatables::of($orders)->addColumn('date_start', function ($orders) {
            $data = date("M-d-Y, l h:i:a", strtotime($orders->date_start));
            return $data;
        })
        ->addColumn('date_end', function ($orders) {
            $data = date("M-d-Y, l h:i:a", strtotime($orders->date_end));
            return $data;
        })
        ->filter(function ($query) use ($request){
            $condition = '1=1';
            if ($request->has('from') && $request->has('to'))
            {
                $from = date('Y-m-d H:i:s', strtotime($request->get('from')));
                $to   = date('Y-m-d H:i:s', strtotime($request->get('to')));
                $condition1 = $condition." and return_orders.created_at BETWEEN '".$from."'::timestamp and '".$to."'::timestamp";
                $query->whereRaw($condition1);
            }
            if ($request->has('order_status') != '')
            {
                $order_status = Input::get('order_status');
                $condition2   = $condition." and return_orders.return_reason = ".$order_status;
                $query->whereRaw($condition2);
            }
            if ($request->has('group_by'))
            {
		
                $group_by = ($request->get('group_by') != '')?$request->get('group_by'):1;
                if($group_by == 1)
                    $start_date = " date_trunc('day', return_orders.created_at) AS date_start, ";
                else if($group_by == 2)
                    $start_date = " date_trunc('week', return_orders.created_at) AS date_start, ";
                else if($group_by == 3)
                    $start_date = " date_trunc('month', return_orders.created_at) AS date_start, ";
                else if($group_by == 4)
                    $start_date = " date_trunc('year', return_orders.created_at) AS date_start, ";

                  
                $query->selectRaw($start_date.' MAX(return_orders.created_at) AS date_end')->groupBy('date_start');
            }
        })
        ->rawColumns(['date_end','date_start'])

        ->make(true);
    }*/
    /* Reports for customer order */
    public function user()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else{
            if(!hasTask('reports/user'))
            {
                return view('errors.404');
            }
            $order_status = DB::table('order_status')->select('id','name')->orderBy('name', 'asc')->get();
            return view('admin.reports.users.list')->with('order_status', $order_status);
        }
    }
    public function anyAjaxReportCustomerOrderList(Request $request)
    {
        $post_data = $request->all();
        $orders    = DB::table('users')
                        ->select('users.id',DB::raw('COUNT(orders.id) AS orders_count, SUM((SELECT SUM(orders_info.item_unit) FROM orders_info WHERE orders_info.order_id = orders.id GROUP BY orders_info.order_id)) AS quantity_count,SUM(orders.total_amount) AS total'))
                        ->join('orders','orders.customer_id','=','users.id')
                        ->groupBy('users.id');
        return Datatables::of($orders)->addColumn('total', function ($orders) {
            if(getCurrencyPosition()->currency_side == 1)
            {
                return getCurrency().$orders->total;
            }
            else {
                return $orders->total.getCurrency();
            }
        })
        ->addColumn('name', function ($orders) {
            $user_datail = get_user_details($orders->id);
            return wordwrap(ucfirst($user_datail->name),20,'<br>');
        })
        ->addColumn('email', function ($orders) {
            $user_datail = get_user_details($orders->id);
            return $user_datail->email;
        })
        ->filter(function ($query) use ($request){
            $condition = '1=1';
            if (($request->has('from') && !empty($request->get('from')))&& ($request->has('to')) && !empty($request->get('to')))
            {
                $from = date('Y-m-d H:i:s', strtotime($request->get('from')));
                $to   = date('Y-m-d H:i:s', strtotime($request->get('to')));
                $condition1 = $condition." and orders.created_date BETWEEN '".$from."'::timestamp and '".$to."'::timestamp";
                $query->whereRaw($condition1);
            }
            if ($request->has('order_status') != '' && !empty(Input::get('order_status')))
            {
                $order_status = Input::get('order_status');
                $condition2   = $condition." and orders.order_status = ".$order_status;
                $query->whereRaw($condition2);
            }
        })
        ->make(true);
    }
    /* Reports coupon */
    public function coupons()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else{
            if(!hasTask('reports/coupons'))
            {
                return view('errors.404');
            }
            $order_status = DB::table('order_status')->select('id','name')->orderBy('name', 'asc')->get();
            return view('admin.reports.coupons.list')->with('order_status', $order_status);
        }
    }

    public function anyAjaxReportCouponList(Request $request)
    {
        $post_data = $request->all();
        $coupons = DB::table('coupons')
                    ->select('coupons.start_date','coupons.end_date','coupons.id',DB::RAW('COUNT(orders.id) AS orders_count,SUM(orders.coupon_amount) AS coupon_amount'))
                    ->join('orders','orders.coupon_id','=','coupons.id')
                    ->join('coupons_infos','coupons_infos.id','=','coupons.id')
                   ->groupBy('coupons.id','coupons_infos.coupon_title','coupons.coupon_code');
        return Datatables::of($coupons)->addColumn('date_start', function ($coupons) {
            $data = date("M-d-Y, l h:i:a", strtotime($coupons->date_start));
            return $data;
        }) 
        ->addColumn('date_end', function ($coupons) {
            $data = date("M-d-Y, l h:i:a", strtotime($coupons->date_end));
            return $data;
        })
        ->addColumn('coupon_amount', function ($coupons) {
            if(getCurrencyPosition()->currency_side == 1)
            {
                return getCurrency().$coupons->coupon_amount;
            }
            else {
                return $coupons->coupon_amount.getCurrency();
            }
        })
        ->addColumn('coupon_title', function ($coupons) {
            $coupon_details = get_coupon_details($coupons->id);
            return isset($coupon_details->coupon_title)?$coupon_details->coupon_title:'-';
        })
        ->addColumn('coupon_code', function ($coupons) {
            $coupon_details = get_coupon_details($coupons->id);
            return isset($coupon_details->coupon_code)?$coupon_details->coupon_code:'-';
        })
        ->filter(function ($query) use ($request){
            $condition = '1=1';
            if (($request->has('from') && !empty($request->get('from')))&& ($request->has('to')) && !empty($request->get('to')))
            {
                $from = date('Y-m-d H:i:s', strtotime($request->get('from')));
                $to   = date('Y-m-d H:i:s', strtotime($request->get('to')));
                $condition .= " and orders.created_date BETWEEN '".$from."'::timestamp and '".$to."'::timestamp";
            }
            if ($request->has('order_status') != '' && !empty(Input::get('order_status')))
            {
                $order_status = Input::get('order_status');
                $condition   .= " and orders.order_status = ".$order_status;
                $query->whereRaw($condition);
            }
            
            if ($request->has('group_by') && !empty($request->get('group_by')))
            {
                $group_by = ($request->get('group_by') != '')?$request->get('group_by'):1;
                if($group_by == 1)
                    $start_date = " date_trunc('day', orders.created_date) AS date_start, ";
                else if($group_by == 2)
                    $start_date = " date_trunc('week', orders.created_date) AS date_start, ";
                else if($group_by == 3)
                    $start_date = " date_trunc('month', orders.created_date) AS date_start, ";
                else if($group_by == 4)
                    $start_date = " date_trunc('year', orders.created_date) AS date_start, ";
                $query->selectRaw($start_date.' MAX(orders.created_date) AS date_end')->whereRaw($condition)->groupBy('date_start')->orderby('date_start', 'DESC');
            }
        })

        ->rawColumns(['coupon_code','coupon_title','coupon_amount','date_end','date_start'])

        ->make(true);
    }
    /*public function anyAjaxReportCouponList(Request $request)
    { 
        $post_data = $request->all();
        $coupons = DB::table('coupons')
                    ->select('coupons.start_date','coupons.end_date','coupons.id',DB::RAW('COUNT(orders.id) AS orders_count,SUM(orders.coupon_amount) AS coupon_amount'))
                    ->join('orders','orders.coupon_id','=','coupons.id')
                    ->join('coupons_infos','coupons_infos.id','=','coupons.id')
                   ->groupBy('coupons.id','coupons_infos.coupon_title','coupons.coupon_code')->get();
        return Datatables::of($coupons)->addColumn('date_start', function ($coupons) {

            $data = date("M-d-Y, l h:i:a", strtotime($coupons->date_start));
            return $data;
        })
        ->addColumn('date_end', function ($coupons) {
            $data = date("M-d-Y, l h:i:a", strtotime($coupons->date_end));
            return $data;
        })
        ->addColumn('coupon_amount', function ($coupons) {
            if(getCurrencyPosition()->currency_side == 1)
            {
                return getCurrency().$coupons->coupon_amount;
            }
            else {
                return $coupons->coupon_amount.getCurrency();
            }
        })
        ->addColumn('coupon_title', function ($coupons) {
            $coupon_details = get_coupon_details($coupons->id);
            return isset($coupon_details->coupon_title)?$coupon_details->coupon_title:'-';
        })
        ->addColumn('coupon_code', function ($coupons) {
            $coupon_details = get_coupon_details($coupons->id);
            return isset($coupon_details->coupon_code)?$coupon_details->coupon_code:'-';
        })
        ->filter(function ($query) use ($request){
            $condition = '1=1';
            if (($request->has('from') && !empty($request->get('from'))) && ($request->has('to') && !empty($request->get('to'))))
            {
                $from = date('Y-m-d H:i:s', strtotime($request->get('from')));
                $to   = date('Y-m-d H:i:s', strtotime($request->get('to')));
                $condition1 = $condition." and orders.created_date BETWEEN '".$from."'::timestamp and '".$to."'::timestamp";
                $query->whereRaw($condition1);
            }
            if ($request->has('order_status') != '' && !empty(Input::get('order_status')))
            {   
                $order_status = Input::get('order_status');
                $condition2   = $condition." and orders.order_status = ".$order_status;
                $query->whereRaw($condition2);
            }
            if ($request->has('group_by') && !empty($request->get('group_by')))
            {
                $group_by = ($request->get('group_by') != '')?$request->get('group_by'):1;
                if($group_by == 1)
                    $start_date = " date_trunc('day', orders.created_date) AS date_start, ";
                else if($group_by == 2)
                    $start_date = " date_trunc('week', orders.created_date) AS date_start, ";
                else if($group_by == 3)
                    $start_date = " date_trunc('month', orders.created_date) AS date_start, ";
                else if($group_by == 4)
                    $start_date = " date_trunc('year', orders.created_date) AS date_start, ";
                $query->selectRaw($start_date.' MAX(orders.created_date) AS date_end')->orderBy('date_start','desc')->groupBy('date_start');
            }
        })
        ->rawColumns(['coupon_code','coupon_title','coupon_amount','date_end','date_start'])
        ->make(true);
    }*/
    /* Reports product list */
    public function products()
     { 
    
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else{
            if(!hasTask('reports/products'))
            {
                return view('errors.404');
            }
            $order_status = DB::table('order_status')->select('id','name')->orderBy('name', 'asc')->get();
            return view('admin.reports.products.list')->with('order_status', $order_status);
        }
    }
    public function anyAjaxReportProdcutList(Request $request)
    {
		$language = getAdminCurrentLang() ;
        //print_r("fdf");exit();
      $query = 'products_infos.lang_id = (case when (select count(products_infos.id) as totalcount from products_infos where products_infos.lang_id = '.$language.' and products.id = products_infos.id) > 0 THEN '.$language.' ELSE 1 END)';
		$query1  = 'vendors_infos.lang_id = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = '.$language.' and vendors.id = vendors_infos.id) > 0 THEN '.$language.' ELSE 1 END)';
		$post_data = $request->all();
       $products   = DB::table('orders')
						->select(DB::raw('COUNT(orders.id) AS orders,SUM((SELECT SUM(orders_info.item_unit) FROM orders_info WHERE orders_info.order_id = orders.id GROUP BY orders_info.order_id)) AS quantity_count,SUM(orders.total_amount) AS total,orders_info.item_id'),'products_infos.product_name','vendors_infos.vendor_name')
						->join('orders_info','orders.id','=','orders_info.order_id')
						->join('products','products.id','=','orders_info.item_id')
						->join('products_infos','products_infos.id','=','products.id')
						->join('vendors','vendors.id','=','products.vendor_id')
						->join('vendors_infos','vendors_infos.id','=','vendors.id')
						->whereRaw($query)
						->whereRaw($query1)
						->groupBy('orders_info.item_id','products_infos.product_name','vendors_infos.vendor_name');
                      //  print_r($products);exit();
        return Datatables::of($products)->addColumn('total', function ($products) {
            if(getCurrencyPosition()->currency_side == 1)
            {
                return getCurrency().$products->total;
            }
            else {
                return $products->total.getCurrency();
            }
        })
        ->addColumn('product_name', function ($products) {
			//$product_details = get_admin_product_details($products->item_id);
			return isset($products->product_name)?ucfirst($products->product_name):'-';
		})
		->addColumn('vendor_name', function ($products) {
			//$vendor_details = get_admin_vendor_details($products->item_id);
			return isset($products->vendor_name)?ucfirst($products->vendor_name):'-';
		})
        ->filter(function ($query) use ($request){
            $condition = '1=1';
            if (($request->has('from') && !empty($request->get('from')))&& ($request->has('to')) && !empty($request->get('to')))
            {
                $from = date('Y-m-d H:i:s', strtotime($request->get('from')));
                $to   = date('Y-m-d H:i:s', strtotime($request->get('to')));
                $condition1 = $condition." and orders.created_date BETWEEN '".$from."'::timestamp and '".$to."'::timestamp";
                $query->whereRaw($condition1);
            }
            if ($request->has('order_status') != '' && !empty(Input::get('order_status')))
            {
                $order_status = Input::get('order_status');
                $condition2   = $condition." and orders.order_status = ".$order_status;
                $query->whereRaw($condition2);
            }
        })
        ->make(true);
    }
}
