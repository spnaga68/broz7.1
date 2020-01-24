<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Text;

use Image;
use MetaTag;
use Mail;
use File;
use SEO;
use SEOMeta;
use OpenGraph;
use Twitter;
use App;
use URL;
use DB;
use Session;
use Closure;

use Yajra\Datatables\Datatables;
use App\Model\return_orders;
use App\Model\return_reasons;
use App\Model\return_actions;
use App\Model\return_status;
use App\Model\return_orders_log;
use App\Model\users;

class Returnorders extends Controller
{
	const RETURN_STATUS_CUSTOMER_EMAIL_TEMPLATE=17;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
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
	/*
	* Return orders listing
	*/
	public function index()
	{
		if (Auth::guest())
		{
			return redirect()->guest('admin/login');
		}
		else{
			//print_r(Input::get());exit;
			
			if(!hasTask('orders/return_orders'))
			{
				return view('errors.404');
			}
			$condition = '1=1';
			if(Input::get('from') && Input::get('to'))
			{
				$from = date('Y-m-d H:i:s', strtotime(Input::get('from')));
				$to = date('Y-m-d H:i:s', strtotime(Input::get('to')));
				$condition .=" and return_orders.created_at BETWEEN '".$from."'::timestamp and '".$to."'::timestamp";
			}
			
				//echo Input::get('vendor');exit;
			   if(Input::get('vendor') != '')
			{      $vendor = Input::get('vendor');
				$condition .=" and orders.vendor_id = ".$vendor."";
			}

			  if(Input::get('outlet')!='')
			{
				$outlet = Input::get('outlet');
				$condition .=" and orders.outlet_id = ".$outlet."";
			}

			//print_r($condition);die;
			$query  = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = '.getCurrentLang().' and orders.outlet_id = outlet_infos.id) > 0 THEN '.getCurrentLang().' ELSE 1 END)';
			$query1 = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = '.getCurrentLang().' and orders.vendor_id = vendors_infos.id) > 0 THEN '.getCurrentLang().' ELSE 1 END)';
			$list   = DB::table('return_orders')
						->select('return_orders.id','return_orders.return_comments','return_orders.created_at','return_orders.modified_at','return_orders.return_action_id','return_action.name as return_action_name','return_reason.name as return_reason_name','return_status.name as return_status_name','users.name as username','outlet_infos.outlet_name','vendors_infos.vendor_name','orders.id as order_id','orders.customer_id as customer_id','return_orders.refund_status')
						->leftJoin('return_action','return_action.id','=','return_orders.return_action_id')
						->leftJoin('return_reason','return_reason.id','=','return_orders.return_reason')
						->leftJoin('return_status','return_status.id','=','return_orders.return_status')
						->leftJoin('orders','orders.id','=','return_orders.order_id')
						->leftJoin('users','users.id','=','orders.customer_id')
						->leftJoin('vendors_infos','vendors_infos.id','=','orders.vendor_id')
						->leftJoin('outlet_infos','outlet_infos.id','=','orders.outlet_id')
						->whereRaw($query)
						//->whereRaw($query1)
						->whereRaw($condition)
						->orderBy('return_orders.created_at', 'desc')
						->paginate(10);
						//~ ->toSql();
			
			if(Input::get('export'))
			{


					$out = '"Order Id","Name","Vendor Name","Outlet Name","Return Reason"'."\r\n";
				foreach($list as $d)
				{
					$out .= $d->order_id.',"'.$d->username.'","'.$d->vendor_name.'","'.$d->outlet_name.'","'.$d->return_reason_name.'"'."\r\n";
				}
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename=returnOrders.csv');
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				echo "\xEF\xBB\xBF"; // UTF-8 BOM
				echo $out;
				exit;
			}

			//echo"<pre>";print_r($list);die;
			return view('admin.return_orders.list')->with('data', $list);
		}
	}
	/**
     * Display the specified return order details here.
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
		else{
			if(!hasTask('orders/return_orders_view'))
			{
				return view('errors.404');
			}
			$query = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = '.getAdminCurrentLang().' and vendors.id = vendors_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
			$query1 = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.language_id) as totalcount from outlet_infos where outlet_infos.language_id = '.getAdminCurrentLang().' and outlets.id = outlet_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
			$os_query = '"order_status"."lang_id" = (case when (select count(*) as totalcount from order_status where order_status.lang_id = '.getAdminCurrentLang().' and orders.order_status = order_status.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
			$pay_query = 'payment_gateways_info.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.getAdminCurrentLang().' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
			$data = DB::table('return_orders')
					->select('return_orders.*','return_action.name as return_action_name','return_reason.name as return_reason_name','return_status.name as return_status_name','orders.order_key_formated','users.name as username','users.email','users.first_name','users.last_name','users.mobile','outlet_infos.outlet_name','vendors_infos.vendor_name','orders.order_key','orders.invoice_id','orders.total_amount','orders.vendor_id','orders.customer_id','orders.outlet_id','orders.delivery_date','orders.delivery_charge','orders.service_tax','orders.coupon_amount','order_status.name as order_status','orders.delivery_instructions','orders.created_date as ordered_date','user_address.address','payment_gateways.id as payment_gateway_id','payment_gateways_info.name','delivery_time_interval.start_time','delivery_time_interval.end_time','outlet_infos.contact_address','transaction.currency_code')
					->leftJoin('return_action','return_action.id','=','return_orders.return_action_id')
					->leftJoin('return_reason','return_reason.id','=','return_orders.return_reason')
					->leftJoin('return_status','return_status.id','=','return_orders.return_status')
					->leftJoin('orders','orders.id','=','return_orders.order_id')
					->leftJoin('users','users.id','=','orders.customer_id')
					->leftJoin('vendors','vendors.id','=','orders.vendor_id')
					->Join('transaction','transaction.order_id','=','return_orders.order_id')
					->leftJoin("vendors_infos",function($join){
						$join->on("vendors_infos.id","=","orders.vendor_id")
							->on("vendors_infos.id","=","vendors.id");
					})
					->leftJoin('outlets','outlets.id','=','orders.outlet_id')
					->leftJoin('outlet_infos','outlet_infos.id','=','outlets.id')
					->leftJoin('order_status','order_status.id','=','orders.order_status')
					->leftJoin('user_address','user_address.id', '=', 'orders.delivery_address')
					->leftJoin('payment_gateways','payment_gateways.id', '=', 'orders.payment_gateway_id')
					->leftJoin('payment_gateways_info','payment_gateways_info.payment_id', '=', 'payment_gateways.id')
					->leftJoin('delivery_time_slots','delivery_time_slots.id', '=', 'orders.delivery_slot')
					->leftJoin('delivery_time_interval','delivery_time_interval.id', '=', 'delivery_time_slots.time_interval_id')
					->whereRaw($query)
					->whereRaw($query1)
					->whereRaw($os_query)
					->whereRaw($pay_query)
					->where('return_orders.id',$id)
					->orderBy('return_orders.created_at', 'desc')
					->get();

				$pquery = 'products_infos.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = '.getAdminCurrentLang().' and products.id = products_infos.id) > 0 THEN '.getAdminCurrentLang().' ELSE 1 END)';
				$items_info = DB::table('orders')
								->select('products.product_image','products.id AS product_id','orders_info.item_cost','orders_info.item_unit','orders_info.item_offer','products_infos.product_name','products_infos.description')
								->leftJoin('orders_info', 'orders_info.order_id', '=','orders.id')
								->leftJoin('products', 'products.id', '=','orders_info.item_id')
								->leftJoin('products_infos', 'products_infos.id', '=','products.id')
								->whereRaw($pquery)
								->where('orders.id',$data[0]->order_id)
								->orderBy('products.id', 'desc')
								->get();
				//echo '<pre>';print_r($items_info);print_r($data);exit;
			if(!count($data))
			{
				Session::flash('message', 'Invalid Order Details'); 
				return Redirect::to('orders/return_orders');
			}
			$return_reasons = return_reasons::all();
			$return_statuses = return_status::all();
			$return_actions = return_actions::all();
			$return_orders_logs = DB::table('return_orders_log')
					->select('return_status.name AS return_status_name','return_action.name AS return_actions_name','return_orders_log.created_date as date_added','return_orders_log.modified_date as date_changed','return_orders_log.customer_notified')
					//->leftJoin('return_orders', 'return_orders.id', '=','return_orders_log.order_id')
					->leftJoin('return_status', 'return_status.id', '=','return_orders_log.return_status')
					->leftJoin('return_action', 'return_action.id', '=','return_orders_log.return_action')
					->where('return_orders_id',$id)
					->get();
			return view('admin.return_orders.show')->with('data', $data)->with('items_data', $items_info)->with('return_reasons', $return_reasons)->with('return_statuses', $return_statuses)->with('return_actions', $return_actions)->with('return_orders_logs', $return_orders_logs);
		}
    }
    /*
    * Update the return_orders & orders table
    */
    public function update(Request $data,$id)
    {
    	if(!hasTask('update_return_order')){
			return view('errors.404');
		}

	

    	// validate
        // read more on validation at http://laravel.com/docs/validation
        $fields['return_order_id'] = $id;
		$fields['order_id'] = Input::get('order_id');
		$fields['vendor_id'] = Input::get('vendor_id');
		$fields['customer_id'] = Input::get('customer_id');
		$fields['return_action'] = Input::get('return_action');
		$fields['return_reason'] = Input::get('return_reason');
		$fields['return_status'] = Input::get('return_status');
		$fields['outlet_id'] = Input::get('outlet_id');
		$rules = array(
			'return_action' => 'required',
			'return_reason' => 'required',
			'return_status' => 'required',
		);
		$validation = Validator::make($fields, $rules);
        // process the validation
        if ($validation->fails()) {
               return Redirect::back()->withErrors($validation)->withInput();
        } else { 
            // store datas in to database
            $Return_orders = return_orders::find($id);

           	//echo "<pre>"; print_r($Return_orders->return_status);exit;
            $Return_orders->return_status = $_POST['return_status'];
			$Return_orders->return_reason = $_POST['return_reason'];
			$Return_orders->return_action_id = $_POST['return_action'];
			$Return_orders->save();

			$Orders = new return_orders_log;
            $Orders->vendor_id    = $_POST['vendor_id'];
            $Orders->outlet_id    = $_POST['outlet_id'];
            $Orders->order_id    = $_POST['order_id'];
			$Orders->return_orders_id = $id;
			$Orders->customer_id = $_POST['customer_id'];
			$Orders->return_status = $_POST['return_status'];
			$Orders->return_reason = $_POST['return_reason'];
			$Orders->return_action = $_POST['return_action'];
            $Orders->modified_date = date("Y-m-d H:i:s");
            $Orders->created_date = date("Y-m-d H:i:s");
            $Orders->customer_notified = 1;
            $Orders->modified_by = Auth::id();
            $Orders->save();

		   /* if($_POST['return_status'] == 20 && $_POST['return_action'] == 19 ) {
            	$customers = Users::find($_POST['customer_id']);
            	$wallet_amount =isset($customers->wallet_amount)?$customers->wallet_amount:0;
            	$order = DB::table('orders')
						->select('total_amount')
						->where('id', '=', $_POST['order_id'])
						->get();
				$total =isset($order[0]->total_amount)?$order[0]->total_amount:0;
				$wallet =$wallet_amount + $total;
				$res = DB::table('users')->where('id', $_POST['customer_id'])->update(['wallet_amount' => $wallet]);

            }*/

            $this->return_orders_save_after($Orders,$_POST);
            // redirect
            Session::flash('message', trans('messages.Return Status has been successfully updated'));
            return Redirect::to('orders/return_orders');
        }
    }
    //Send mail to customer regarding the return order status was updated
    public static function return_orders_save_after($data,$post)
    {
    	//Sending the mail to vendors
		$template=DB::table('email_templates')
				->select('*')
				->where('template_id','=',self::RETURN_STATUS_CUSTOMER_EMAIL_TEMPLATE)
				->get();
		if(count($template)){
		   $from = $template[0]->from_email;
		   $from_name = $template[0]->from;
		   $subject = $template[0]->subject;
		   if(!$template[0]->template_id){
			   $template = 'mail_template';
			   $from = getAppConfigEmail()->contact_email;
			   $subject = getAppConfig()->site_name." Return Order Status Information";
			   $from_name = "";
		   }
		   $customer = Users::find($post['customer_id']);
		   $return_status = return_status::find($post['return_status']);
		   $return_action = return_actions::find($post['return_action']);
		   $cont_replace = "Your return order <b>". $post['order_key'] ."</b> status has been updated with store or outlet.";
		   $cont_replace1 = "Your order has been <b>". $return_status->name ."</b> and make it necessary arrangements we are waiting for <b>". $return_action->name."</b>";
		   $content = array("name" => $customer->name,"order_key"=>$post['invoice_id'],"return_status"=> $return_status->name ,"return_action"=> $return_action->name);
		   $email = smtp($from,$from_name,$customer->email,$subject,$content,$template);
		   return;
	    }
    }



    	/** store register **/
	public function refund_customer(Request $data) {

		$post_data = $data->all();
		$rules = [
			'customer_id' => ['required'],
			'order_id' => ['required'],
			'refund_amount' => ['required', 'numeric'],
		];
		$error = $result = array();
		$validation = app('validator')->make($post_data, $rules);

		if ($validation->fails()) {
			$errors = '';
			$j = 0;
			foreach ($validation->errors()->messages() as $key => $value) {
				$error[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $error);
			$result = array("response" => array("httpCode" => 400, "Status" => "Failure", "Error" => trans("messages.Error List"), "Message" => $errors, "Error" => $validation->errors()->messages()));
		} else {
			$users = Users::find($post_data['customer_id']);
			$wallet_amount =isset($users->wallet_amount)?$users->wallet_amount:0;
			$wallet =$wallet_amount+$post_data['refund_amount'];
			//DB::update('update users set wallet_amount = ? where id = ?', array($wallet, $post_data['customer_id']));
			//$status_change = DB::update('update return_orders set refund_status = 1  where order_id = ' . $post_data['order_id'] . '');

		//	print_r($users);exit();
			$to = $users->email;
			$to = 'athhiraraveendran5@gmail.com';
			$template = DB::table('email_templates')->select('*')->where('template_id', '=', self::RETURN_STATUS_CUSTOMER_EMAIL_TEMPLATE)->get();
			if (count($template)) {
				$from = $template[0]->from_email;
				$from_name = $template[0]->from;
				if (!$template[0]->template_id) {
					$template = 'mail_template';
					$from = getAppConfigEmail()->contact_mail;
				}
				$currency =getCurrencyList();
				$currency_code = isset($currency[0]->currency_code)?$currency[0]->currency_code:'AED';
				
				$subject = getAppConfig()->site_name . " Return Order Status Information";
				$log_image = url('/assets/admin/email_temp/images/1570903488.jpg');
				$offer_image = url('/assets/admin/email_temp/images/1571292850.jpg');
				$order_id = (string)$post_data['order_id'];
				$currency_code = $currency_code;
				$refund_amount = (string)$post_data['refund_amount'];
				$content = array("log_image"=>$log_image,"offer_image"=>$offer_image,"order_id"=>$order_id,"currency_code"=>$currency_code,"refund_amount"=>$refund_amount);				    	
				$attachment = "";
				$email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
			}


			$result = array("response" => array("httpCode" => 200, "status" => "Success", "Message" => trans("messages.Driver responded to the order assign process succesfully")));




		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}
}
