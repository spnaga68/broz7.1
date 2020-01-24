<?php

namespace App\Http\Controllers;

use App;
use App\Model\autoassign_order_logs;
use App\Model\drivers;
use App\Model\driver_orders;
use App\Model\driver_settings;
use App\Model\order;
use App\Model\users;
use App\Model\cartcontroller;
use App\Model\vendors;
use App\Model\outlet_reviews;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use OpenGraph;
use SEOMeta;
use Services_Twilio;
use Session;
use Twitter;
use URL;
use Yajra\Datatables\Datatables;

use App\Model\request_admins;


class Orders extends Controller {
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	const ORDER_STATUS_UPDATE_USER = 18;
	const REFUND_APPROVE_EMAIL_TEMPLATE = 20;
	public function __construct() {
		$this->site_name = isset(getAppConfig()->site_name) ? ucfirst(getAppConfig()->site_name) : '';
		$this->middleware('auth');
		SEOMeta::setTitle($this->site_name);
		SEOMeta::setDescription($this->site_name);
		SEOMeta::addKeyword($this->site_name);
		OpenGraph::setTitle($this->site_name);
		OpenGraph::setDescription($this->site_name);
		OpenGraph::setUrl($this->site_name);
		Twitter::setTitle($this->site_name);
		Twitter::setSite('@' . $this->site_name);
		App::setLocale('en');
	}
	public function index() {
		if (Auth::guest()) {
			return redirect()->guest('admin/login');
		} else {
			if (!hasTask('admin/orders/index')) {
				return view('errors.404');
			}
			$condition = "orders.order_type!=0";
			if (Input::get('from') && Input::get('to')) {
				$from = date('Y-m-d H:i:s', strtotime(Input::get('from')));
				$to = date('Y-m-d H:i:s', strtotime(Input::get('to')));
				$condition .= " and orders.created_date BETWEEN '" . $from . "'::timestamp and '" . $to . "'::timestamp";
			}
			if (Input::get('from_amount') && Input::get('to_amount')) {
				$from_amount = preg_replace("/[^0-9,.]/", "", Input::get('from_amount'));
				$to_amount = preg_replace("/[^0-9,.]/", "", Input::get('to_amount'));
				$condition .= " and orders.total_amount BETWEEN '" . $from_amount . "' and '" . $to_amount . "'";
			}
			if (Input::get('order_status')) {
				$order_status = Input::get('order_status');
				$condition .= " and orders.order_status = " . $order_status . "";
			}
			if (Input::get('payment_type')) {
				$payment_type = Input::get('payment_type');
				$condition .= " and orders.payment_gateway_id = " . $payment_type . "";
			}
			if (Input::get('vendor')) {
				$vendor = Input::get('vendor');
				$condition .= " and orders.vendor_id = " . $vendor . "";
			}
			if (Input::get('outlet')) {
				$outlet = Input::get('outlet');
				$condition .= " and orders.outlet_id = " . $outlet . "";
			}
			$language = getCurrentLang();
			$query = '"payment_gateways_info"."language_id" = (case when (select count(payment_gateways_info.payment_id) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language . ' and orders.payment_gateway_id = payment_gateways_info.payment_id) > 0 THEN ' . $language . ' ELSE 1 END)';
			$query1 = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language . ' and orders.outlet_id = outlet_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
			$query2 = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language . ' and orders.vendor_id = vendors_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
			$orders = DB::table('orders')
				->select('orders.id', 'orders.total_amount', 'orders.created_date', 'orders.modified_date', 'orders.delivery_date', 'users.first_name', 'users.last_name', 'order_status.name as status_name', 'order_status.color_code as color_code', 'users.name as user_name', 'transaction.currency_code', 'payment_gateways_info.name as payment_type', 'outlet_infos.outlet_name', 'vendors_infos.vendor_name as vendor_name', 'orders.id', 'outlet_infos.contact_address', 'outlets.latitude as outlet_latitude', 'outlets.longitude as outlet_longitude', 'outlets.id as outlet_id', 'drivers.first_name as driver_name', 'orders.order_status as order_status', 'orders.request_vendor as request_vendor')
				->leftJoin('users', 'users.id', '=', 'orders.customer_id')
				->leftJoin('order_status', 'order_status.id', '=', 'orders.order_status')
				->leftjoin('transaction', 'transaction.order_id', '=', 'orders.id')
				->Join('payment_gateways_info', 'payment_gateways_info.payment_id', '=', 'orders.payment_gateway_id')
				->Join('vendors_infos', 'vendors_infos.id', '=', 'orders.vendor_id')
				->Join('outlet_infos', 'outlet_infos.id', '=', 'orders.outlet_id')
				->Join('outlets', 'outlets.id', '=', 'outlet_infos.id')
				->leftJoin('driver_orders', 'driver_orders.order_id', '=', 'orders.id')
				->leftJoin('drivers', 'drivers.id', '=', 'driver_orders.driver_id')
				->whereRaw($query)->whereRaw($query1)->whereRaw($query2)
				->whereRaw($condition)
				->orderBy('orders.id', 'desc')
				->paginate(10);
			//echo "<pre>";print_r($orders);exit;
			$order_status = DB::table('order_status')->select('id', 'name')->orderBy('name', 'asc')->get();
			$query3 = '"payment_gateways_info"."language_id" = (case when (select count(payment_gateways_info.language_id) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . getCurrentLang() . ' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
			$payment_seetings = DB::table('payment_gateways')
				->select('payment_gateways.id', 'payment_gateways_info.name')
				->leftJoin('payment_gateways_info', 'payment_gateways_info.payment_id', '=', 'payment_gateways.id')
				->whereRaw($query3)
				->orderBy('id', 'asc')
				->get();

		//echo"<pre>"; print_r($orders);exit;
			if (Input::get('export')) {
				$out = '"Order Id","User Name","Vendor Name","Outlet Name","Payment Type","Status","Total Amount","Order Date"' . "\r\n";
				foreach ($orders as $d) {
					$out .= $d->id . ',"' . $d->user_name . '","' . $d->vendor_name . '","' . $d->outlet_name . '","' . $d->payment_type . '","' . $d->status_name . '","' . $d->total_amount . $d->currency_code . '","' . date("d F, Y", strtotime($d->created_date)) . '"' . "\r\n";
				}
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename=Orders.csv');
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				echo "\xEF\xBB\xBF"; // UTF-8 BOM
				echo $out;
				exit;
			}
			return view('admin.orders.list')->with('orders', $orders)->with('order_status', $order_status)->with('payment_seetings', $payment_seetings);
		}
	}

	


	public function order_info($order_id) {
		if (!hasTask('admin/orders/info')) {
			return view('errors.404');
		}
		$language_id = getCurrentLang();
		$query3 = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$query4 = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$vendor_info = DB::select('SELECT vendors_infos.vendor_name,vendors.email,vendors.logo_image,o.id as order_id,o.created_date,o.order_status,order_status.name as status_name,order_status.color_code as color_code,o.outlet_id,vendors.id as vendor_id,o.order_key_formated
        FROM orders o
        left join vendors vendors on vendors.id = o.vendor_id
        left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
        left join order_status order_status on order_status.id = o.order_status
        left join payment_gateways payment_gateways on payment_gateways.id = o.payment_gateway_id
        left join payment_gateways_info payment_gateways_info on payment_gateways_info.payment_id = payment_gateways.id
        where ' . $query3 . ' AND ' . $query4 . ' AND o.id = ? ORDER BY o.id', array($order_id));

		$query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and p.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$order_items = DB::select('SELECT p.product_image,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.invoice_id,pi.product_name,pi.description,o.coupon_amount
        FROM orders o
        LEFT JOIN orders_info oi ON oi.order_id = o.id
        JOIN products p ON p.id = oi.item_id
        JOIN products_infos pi ON pi.id = p.id
        where ' . $query . ' AND o.id = ? ORDER BY oi.id', array($order_id));

		$query1 = '"out_inf"."language_id" = (case when (select count(outlet_infos.language_id) as totalcount from outlet_infos where outlet_infos.language_id = ' . getCurrentLang() . ' and o.outlet_id = outlet_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
		$query2 = 'pgi.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and pg.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$delivery_details = DB::select('SELECT o.id as order_id,o.order_status as order_status, o.delivery_instructions,ua.address,pg.id as payment_gateway_id,pgi.name,o.total_amount,o.delivery_charge,o.service_tax,dti.start_time,end_time,o.created_date,o.delivery_date,o.order_type,out_inf.contact_address,o.coupon_amount,o.customer_id,users.first_name,users.last_name,users.email,users.mobile,o.order_key_formated,outlet_infos.outlet_name,trans.currency_code,pgi.name as payment_gateway_name,users.name as user_name, o.invoice_id FROM orders o
                    left join users on o.customer_id = users.id
                    left join outlet_infos on o.outlet_id = outlet_infos.id
                    LEFT JOIN user_address ua ON ua.id = o.delivery_address
                    left join payment_gateways pg on pg.id = o.payment_gateway_id
                    left join payment_gateways_info pgi on pgi.payment_id = pg.id
                    left join delivery_time_slots dts on dts.id=o.delivery_slot
                    left join delivery_time_interval dti on dti.id = dts.time_interval_id
                    left join outlets out on out.id = o.outlet_id
                    left join outlet_infos out_inf on out.id = o.outlet_id
                    left join transaction trans on trans.order_id = o.id
                    where ' . $query1 . ' AND ' . $query2 . ' AND o.id = ?', array($order_id));

		$order_history = DB::select('SELECT ol.order_comments,ol.order_status,log_time,order_status.name as status_name,order_status.color_code as color_code
        FROM orders_log ol
        left join order_status order_status on order_status.id = ol.order_status
        where ol.order_id = ? ORDER BY ol.id', array($order_id));
		$order_history1 = DB::select('SELECT ol.id,ol.digital_signature,ol.order_attachment FROM orders_log ol where ol.order_id = ? and ol.order_status = 12', array($order_id));
		$order_status_list = DB::select('SELECT * FROM order_status WHERE id in(1,10,18,19,14,12,11) ORDER BY order_status.id');



	/*	$user_id = $delivery_details[0]->customer_id;
		$language = getCurrentLang();
		$user_array = array("user_id" => $user_id,"language" => $language);
		
		$response = order::get_cart($user_array);
		$response= json_decode($response);
		//echo"<pre>";print_r($response->response->httpCode);exit;
		if ($response->response->httpCode == 400) {
			$cart_items = array();
		} else {
			//print_r("expression");exit;
			$cart_items = $response->response->cart_items;
			$total = $response->response->total;
			$sub_total = $response->response->sub_total;
			$tax = $response->response->tax;
			$delivery_cost = $response->response->delivery_cost;
			$tax_amount = $sub_total * $tax / 100;
		}
		*/
		if (count($order_items) > 0) {
			return view('admin.orders.view')->with('order_items', $order_items)->with('delivery_details', $delivery_details)->with('vendor_info', $vendor_info)->with('order_history', $order_history)->with('order_status_list', $order_status_list)->with('order_history1', $order_history1);/*->with("language", $language);->with("cart_items", $cart_items)->with("total", $total)->with("sub_total", $sub_total)->with("tax_amount", $tax_amount)->with("delivery_cost", $delivery_cost)*/
		} else {

			Session::flash('message', 'Invalid order Details');
			return Redirect::to('admin/orders/index');
		}

	}

	public function update_status(Request $data) {
		if (!hasTask('admin/orders/update-status')) {
			return view('errors.404');
		}
		$post_data = $data->all();

		$driver_id=DB::table('orders')
                    ->select('driver_ids','order_status','customer_id','created_date','total_amount','delivery_address','salesperson_id')
                    ->where('id',$post_data['order_id'])
                    ->get();

        $driverid =isset($driver_id[0]->driver_ids)?$driver_id[0]->driver_ids:0;
        $salesperson =isset($driver_id[0]->salesperson_id)?$driver_id[0]->salesperson_id:0;

        $order_status =isset($driver_id[0]->order_status)?$driver_id[0]->order_status:0;
        $customer_id =isset($driver_id[0]->customer_id)?$driver_id[0]->customer_id:0;
        $created_date =isset($driver_id[0]->created_date)?$driver_id[0]->created_date:'';
        $total_amount	 =isset($driver_id[0]->total_amount	)?$driver_id[0]->total_amount	:'';
        $delivery_address	 =isset($driver_id[0]->delivery_address	)?$driver_id[0]->delivery_address	:'';

        if($order_status !=12 && $order_status !=11){ 
	        if($post_data['order_status_id'] == 12)
	        {
	            
	            $data['driverId'] = $driverid;
	            $data['orderId'] =$post_data['order_id'];
	            $delivery = commonDelivery($data); //common fun for delivery

	        }else{
	              $affected = DB::update('update orders set order_status = ?,order_comments = ? where id = ?', array($post_data['order_status_id'],$post_data['comment'],$post_data['order_id']));
	            $affected = DB::update('update orders_log set order_status=?, order_comments = ? where id = (select max(id) from orders_log where order_id = '. $post_data['order_id'].')', array($post_data['order_status_id'],$post_data['comment']));

	            /**cancel mail for customer**/
	            if($post_data['order_status_id'] == 11)
	            {

	            	$affected = DB::update('update drivers set driver_status = 1 where id = ?', array($driverid));

	            	if($order_status==34){

	            	$notify = DB::table('salesperson')
				            ->select('salesperson.name as salesPersonName','salesperson.android_device_token','salesperson.ios_device_token')
				            ->where('salesperson.id', '=', (int)$salesperson)
				            ->get();  

	            	}
	            	if($order_status==31 || $order_status==32 || $order_status==19){
	            	$notify= DB::table('drivers')
	            			->select('drivers.id','drivers.first_name','drivers.android_device_token','drivers.ios_device_token','drivers.login_type')
	            			->where('drivers.id',$driverid)
        					->get();
        			}
        			if ($notify) {
        				$notifys = $notify[0];
		            	if($notifys->login_type == 2){
			                $token = $notifys->android_device_token;
			            }else if($notifys->login_type == 3)
			            {
			                $token = $notifys->ios_device_token;
			            }
			            $token =isset($token)?$token:'';
			            $order_title = '' . 'Order Cancelled';
                		$description = 'Order Id #' .$post_data['order_id']. ' Is cancelled  by Admin';
			            //$token = "e4do9Z4X1K4:APA91bEiNKEemXf0Wf0xYg9wJXKKnm4Lq8DLFNe6z0E0PEi562gb_whLgU_Zwb_Zcx0iwoMlEbwLQKmkH_JvVrIATBCj7rN-rr2txn_cBBEMloniAlyyUweVcQcDHWE-lmGoiqjQnmfu";
			            $data = array
			                (
			                'status' => 1,
			                'message' => $order_title,
			                'detail' =>array(
			                'description'=>$description,    
			                'orderId' => $post_data['order_id'],
			                'driverId' => $driverid,
			                'orderStatus' => 11,
			                'type' => 2,
			                'title' => $order_title,
			                'request_type' => 1,
			                'notification_dialog' => "1",
			            ));

			            $fields = array
			                (
			                'registration_ids' => array($token),
			                'data' => array('title' => $order_title, 'body' =>  $data ,'sound'=>'Default','image'=>'Notification Image')
			            	);
			            $headers = array
			                (
			                'Authorization: key='.FCM_SERVER_KEY,
			               
			                'Content-Type: application/json'
			                );

			            $ch = curl_init();
			            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
			            curl_setopt($ch, CURLOPT_POST, true);
			            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
			            $result = curl_exec($ch);
			            //print_r($result);exit;
			            curl_close($ch);
			        }

	           		$users = Users::find($customer_id);
					$to = $users->email;
					/*$template = DB::table('email_templates')->select('*')->where('template_id', '=', 29)->get();
					if (count($template)) {
						$from = $template[0]->from_email;
						$from_name = $template[0]->from;
						if (!$template[0]->template_id) {
							$template = 'mail_template';
							$from = getAppConfigEmail()->contact_mail;
						}
						$subject = 'Your Order has been cancelled';
						$content = array('name' => "" . $users->name);
						$attachment = "";
						$email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
					}*/
	           		$delivery_address = DB::table('user_address')->select('address')->where('id', '=' ,$delivery_address)->get();
					$currency =getCurrencyList();
					$currency_code = isset($currency[0]->currency_code)?$currency[0]->currency_code:'AED';
					$template = DB::table('email_templates')->select('*')->where('template_id', '=',29)->get();
					if (count($template)) {
						$from = $template[0]->from_email;
						$from_name = $template[0]->from;
						if (!$template[0]->template_id) {
							$template = 'mail_template';
							$from = getAppConfigEmail()->contact_mail;
						}
						$subject = 'Your Order has been cancelled';
						$log_image = url('/assets/admin/email_temp/images/1570903488.jpg');
						$cancel_image = url('/assets/admin/email_temp/images/c.jpg');
						$order_id = (string)$post_data['order_id'];
						$created_date = $created_date;
						$shipping_address =isset($delivery_address[0]->address) ? $delivery_address[0]->address : '';
						$currency_code = $currency_code;
						$total = $total_amount;

						$content = array("log_image"=>$log_image,"cancel_image"=>$cancel_image,"order_id"=>$order_id,"created_date"=>$created_date,"shipping_address"=>$shipping_address,"currency_code"=>$currency_code,"total"=>$total);				    	
						$attachment = "";
						$email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
					}

	            }
	        }
	        //$post_data['notify'] = 1;
	        $affected = DB::update('update orders set request_vendor = 0 where id = ?', array($post_data['order_id']));
	        $notify=push_notification($post_data['order_id'],$post_data['order_status_id'],1);

			$result = array("status" => 1, "message" => trans("messages.Order Status updated successfully"));
		}else
		{

			if($order_status == 12){
				$result = array("status" => 0,  "message" => trans("messages.Order is Already completed"));

			}else
			{
				$result = array("status" => 0,  "message" => trans("messages.Order is Already cancelled"));

			}
		}

		return json_encode($result, JSON_UNESCAPED_UNICODE);

		/*
		$affected = DB::update('update orders set order_status = ?,order_comments = ? where id = ?', array($post_data['order_status_id'], $post_data['comment'], $post_data['order_id']));
		$affected = DB::update('update orders_log set order_status=?, order_comments = ? where id = (select max(id) from orders_log where order_id = ' . $post_data['order_id'] . ')', array($post_data['order_status_id'], $post_data['comment']));

		$order_detail = $this->get_order_detail($post_data['order_id']);
		$order_details = $order_detail["order_items"];
		$delivery_details = $order_detail["delivery_details"];
		$vendor_info = $order_detail["vendor_info"];


		//Ram::29/08/19  push notification:

		 $rules = [
		// 	//'userId' => ['required'],
		// 	'orderId' => ['required'],
		 ];
		$errors = $result = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			$errors = '';
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$error[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $error);
			$result = array("status" => 2, "httpCode" => 400, "Error" => trans("messages.Error List"), "message" => $errors);
		} else {
			try {
				//$check_auth = JWTAuth::toUser($post_data['token']);
				//$driverId = $post_data['userId'];
				$orderId = $post_data['order_id'];
				$comment = isset($post_data['comment']) ? $post_data['comment'] : '';
				$date = date("Y-m-d H:i:s");

			
				$affected = DB::update('update orders set request_vendor = 0 where id = ?', array($post_data['order_id']));

				
				

				$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.Order Status updated successfully"));
			
			}catch (JWTException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
			} catch (TokenExpiredException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);

		*/
	}

	/*public function update_status(Request $data) {
		if (!hasTask('admin/orders/update-status')) {
			return view('errors.404');
		}
		$post_data = $data->all();


		$affected = DB::update('update orders set order_status = ?,order_comments = ? where id = ?', array($post_data['order_status_id'], $post_data['comment'], $post_data['order_id']));
		$affected = DB::update('update orders_log set order_status=?, order_comments = ? where id = (select max(id) from orders_log where order_id = ' . $post_data['order_id'] . ')', array($post_data['order_status_id'], $post_data['comment']));

		$order_detail = $this->get_order_detail($post_data['order_id']);
		$order_details = $order_detail["order_items"];
		$delivery_details = $order_detail["delivery_details"];
		$vendor_info = $order_detail["vendor_info"];


		//Ram::29/08/19  push notification:

		 $rules = [
		// 	//'userId' => ['required'],
		// 	'orderId' => ['required'],
		 ];
		$errors = $result = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			$errors = '';
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$error[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $error);
			$result = array("status" => 2, "httpCode" => 400, "Error" => trans("messages.Error List"), "message" => $errors);
		} else {
			try {
				//$check_auth = JWTAuth::toUser($post_data['token']);
				//$driverId = $post_data['userId'];
				$orderId = $post_data['order_id'];
				$comment = isset($post_data['comment']) ? $post_data['comment'] : '';
				$date = date("Y-m-d H:i:s");

				//driver accept order state =>31
				

				//$status_change = DB::update('update orders set order_status = 18 , where id = ' . $orderId . '');

					//$status_change = DB::update('update orders set order_status=18  where id = $orderId ');
				

				//print_r("expression");exit();

				//$affected = DB::update('update orders_log set order_status=?, order_comments = ? where id = (select max(id) from orders_log where order_id = ' . $orderId . ')', array(18, $comment));

				//driver status changed to busy
				//$driver_status_change = DB::update('update drivers set driver_status = 2 where id = ?', array((int) $driverId));

				$affected = DB::update('update orders set request_vendor = 0 where id = ?', array($post_data['order_id']));

				//driver order details save
				// $driver_orders = new Driver_orders;
				// $driver_orders->order_id = $orderId;
				// $driver_orders->driver_id = $driverId;
				// $driver_orders->assigned_time = date("H:i:s");
				// $driver_orders->created_at = date("Y-m-d H:i:s");
				// $driver_orders->updated_at = date("Y-m-d H:i:s");
				// $driver_orders->save();
				/*FCM push notification/
				$notify = DB::table('orders')
					->select('orders.assigned_time', 'users.android_device_token', 'users.ios_device_token','users.id as customerId ','users.login_type', 'users.first_name', 'vendors_infos.vendor_name','vendors.id as vendorId','orders.total_amount','outlets.id as outletId','outlet_infos.outlet_name','orders.driver_ids')
					->Join('users', 'users.id', '=', 'orders.customer_id')
					->Join('vendors_infos', 'vendors_infos.id', '=', 'orders.vendor_id')
					->Join('vendors', 'vendors.id', '=', 'orders.vendor_id')
					->Join('outlets', 'outlets.id', '=', 'orders.outlet_id')
					->Join('outlet_infos','outlet_infos.id', '=', 'orders.outlet_id')
					->where('orders.id', '=', (int) $orderId)
					->get();
				//print_r($notify);exit;

				if (count($notify) > 0 && $notify[0]->login_type != 1 ) {
					$notifys = $notify[0];


						if($post_data['order_status_id']==1){
							$order_title = '' . 'order placed';
							$description = '' . 'your placed order successfully';
							$orderStatus =1;
						}


						if($post_data['order_status_id']==10){
							$order_title = '' . 'processed';
							$description = '' . 'your placed order is processed successfully';
							$orderStatus =10;
						}


						if($post_data['order_status_id']==18){
							$order_title = '' . 'packed';
							$description = '' . 'your placed order is packed successfully';
							$orderStatus =18;
						}

						if($post_data['order_status_id']==11){
							$order_title = '' . 'cancelled';
							$description = '' . 'your placed order is cancelled successfully';
							$orderStatus =11;
						}

						if($post_data['order_status_id']==12){
							$order_title = '' . 'delivered';
							$description = '' . 'your placed order is delivered successfully';
							$orderStatus =12;

								

							$reviews = new Outlet_reviews;
							$reviews->customer_id = $notifys->customerId ;
							$reviews->vendor_id = $notifys->vendorId ;
							$reviews->outlet_id = $notifys->customerId ;
							//$reviews->comments = $post_data['comments'];
							//~ $reviews->title        = $post_data['title'];
							$reviews->ratings = "-2";
							$reviews->created_date = date("Y-m-d H:i:s");
							$reviews->order_id = $post_data['order_id'];
							$reviews->save();





						}

						if($post_data['order_status_id']==14){
							$order_title = '' . 'shipped';
							$description = '' . 'your placed order is shipped successfully';
							$orderStatus =14;
						}

						if($post_data['order_status_id']==19){
							$order_title = '' . 'dispatched';
							$description = '' . 'your placed order is dispatched successfully';
							$orderStatus =19;
						}



					if($notifys->login_type == 2)// android device
						{
							$token = $notifys->android_device_token;
						}else if($notifys->login_type == 3)
						{
							$token = $notifys->ios_device_token;
						}

							// echo("logintype ".$notifys->login_type);
							// echo("logintype #".$token);
					$token =isset($token)?$token:'';
					$data = array
						(
						'status' => 1,
						'message' => $order_title,
						'detail' =>array(
						'description'=>$description,	
				
						'customerId' => isset($notifys->customerId) ? $notifys->customerId : '',
						'orderId' => $orderId,
						'driverId' => isset($notifys->driver_ids) ? $notifys->driver_ids : '',
						'orderStatus' => $orderStatus,
						'type' => 2,
						'title' => $order_title,
						'totalamount' => isset($notifys->total_amount) ? $notifys->total_amount : 0,
						'vendorName' => isset($notifys->vendor_name) ? $notifys->vendor_name : '',
						'vendorId' => isset($notifys->vendorId) ? $notifys->vendorId : '',
						'outletId' => isset($notifys->outletId) ? $notifys->outletId : '',
						'outlet_name' => isset($notifys->outlet_name) ? $notifys->outlet_name : '',
						'request_type' => 1,
						"order_assigned_time" => isset($notifys->assigned_time) ? $notifys->assigned_time : '',
						'notification_dialog' => "1",
					));

					$fields = array
						(
						'registration_ids' => array($token),
						//'data' => $data,
						"content_available" => true,
						'notification' => array('title' => $order_title, 'body' =>  $description ,'sound'=>'Default','image'=>'Notification Image'),
						'data' =>  $data,
						'priority'=>'high'


					);


					/*	$headers = array
						(
						'Authorization: key=AAAAI_fAV4w:APA91bFSR1TLAn1Vh134nzXLznsUVYiGnR4KiUYdAa3u0OccC5S-DyDdQRdnR0XugSRArsJGXC8AHE342eNhBbnK8np10KuyuWwiJxtndV75O4DyT3QCGXKFu_fwUTNPdB51Cno6Rewc',
						'Content-Type: application/json',
					);/
					 $headers = array
			            (
			           'Authorization: key='.FCM_SERVER_KEY,
			           // 'Authorization: key=AAAAzIl1AiA:APA91bGu-ThfOepY30smrHk4Vm-ZkoaF7RI4MeMlQXYBGEn9-QYe_VM4MjuZziLhKewS6L6QdZjMHpZOS6T-wco644NgtsF9DRsptg8BFcPafThGNmZDPg4uMYrvM3LWkZq0YuY2mrJt',
			            'Content-Type: application/json'
			            );

					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
					$result = curl_exec($ch);
					//print_r(json_encode($fields));exit;
					curl_close($ch);
				}

				/*FCM push notification/

				$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.Order Status updated successfully"));
			
			}catch (JWTException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
			} catch (TokenExpiredException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);





		// this is order autoassign code:

				// $temp_stop_auto = 1; // temp stoping auto assign
				// if ($post_data['order_status_id'] == 18 && $temp_stop_auto = 0) {
				// 	//print_r("expression");exit;

				// 	$date1 = date("Y-m-d H:i:s");
				// 	$time1 = strtotime($date1);
				// 	$time = $time1 - (1 * 30);
				// 	$date = date("Y-m-d H:i:s", $time);

				// 	$vendor_info = $order_detail["vendor_info"];

				// 	$drivers_details = $vendor_info[0]->driver_ids;

				// 	$driver_conditions = '';

				// 	if ($drivers_details != '') {

				// 		$driver_conditions = " and drivers.id NOT IN (" . rtrim($drivers_details, ",") . ") ";
				// 	}

				// 	$drivers = DB::select("select driver_id,distance ,first_name,last_name,android_device_token from (select DISTINCT ON (driver_track_location.driver_id) driver_id, drivers.first_name, drivers.last_name, drivers.android_device_token,earth_distance(ll_to_earth(" . $vendor_info[0]->outlet_latitude . "," . $vendor_info[0]->outlet_longitude . "), ll_to_earth(driver_track_location.latitude, driver_track_location.longitude)) as distance from drivers left join driver_track_location on driver_track_location.driver_id = drivers.id where earth_box(ll_to_earth(" . $vendor_info[0]->outlet_latitude . "," . $vendor_info[0]->outlet_longitude . "), 30000)  @> ll_to_earth(driver_track_location.latitude, driver_track_location.longitude) and drivers.active_status=1 and drivers.android_device_token != '' and  drivers.is_verified=1 and drivers.driver_status = 1 " . $driver_conditions . "  order by driver_track_location.driver_id,distance asc) as temp_table");

				// 	if (count($drivers) > 0) {
				// 		$assigned_drivers = 0;
				// 		foreach ($drivers as $od => $odvalue) {

				// 			if ($assigned_drivers == 1) {

				// 				break;
				// 			}
				// 			$get_autoassign_order_logs12 = DB::table('autoassign_order_logs')
				// 				->select('*')
				// 				->where('autoassign_order_logs.order_id', $post_data['order_id'])
				// 				->where('autoassign_order_logs.order_delivery_status', '=', 0)
				// 				->where('autoassign_order_logs.auto_order_rejected', '=', 1)
				// 				->where('autoassign_order_logs.driver_response', '=', 0)
				// 				->orderby('autoassign_order_logs.id', 'desc')
				// 				->first();

				// 			$driver_settings = Driver_settings::find(1);
				// 			$created_date = date("Y-m-d H:i:s");
				// 			$get_autoassign_order_logs12 = 1;

				// 			if (count($get_autoassign_order_logs12) >= 0) {

				// 				if ($odvalue->android_device_token != '') {

				// 					$orders = DB::table('orders')
				// 						->select('driver_ids')
				// 						->where('orders.id', $post_data['order_id'])
				// 						->first();

				// 					$driver_ids = $orders->driver_ids;
				// 					$new_orders = Order::find($post_data['order_id']);
				// 					$new_orders->driver_ids = $driver_ids . $odvalue->driver_id . ',';

				// 					$assigned_time = strtotime("+ " . $driver_settings->order_accept_time . " minutes", strtotime(date('Y-m-d H:i:s')));

				// 					$update_assign_time = date("Y-m-d H:i:s", $assigned_time);

				// 					$new_orders->assigned_time = $update_assign_time;
				// 					echo "<pre>";
				// 					//print_r($new_orders);exit;

				// 					$new_orders->save();

				// 					$assigned_drivers = 1;
				// 					$order_title = '' . ucfirst($vendor_info[0]->vendor_name) . ' , A new order delivery has been sent';
				// 					$order_title1 = '' . ucfirst($vendor_info[0]->vendor_name) . ' , ØªÙ… Ø§Ø±Ø³Ø§Ù„ Ø·Ù„Ø¨ ØªÙˆØµÙŠÙ„ Ø¬Ø¯ÙŠØ¯';
				// 					$order_logs = new Autoassign_order_logs;
				// 					$order_logs->driver_id = $odvalue->driver_id;
				// 					$order_logs->order_id = $post_data['order_id'];
				// 					$order_logs->driver_response = 0;
				// 					$order_logs->driver_token = $odvalue->android_device_token;
				// 					$order_logs->order_delivery_status = 0;
				// 					$order_logs->order_subject = $order_title;
				// 					// $order_logs->order_subject_arabic = $order_title1;
				// 					$order_logs->order_message = $order_title;
				// 					$order_logs->assign_date = date("Y-m-d H:i:s");
				// 					$order_logs->created_date = date("Y-m-d H:i:s");

				// 					$order_logs->save();

				// 					$affected = DB::update('update drivers set driver_status = 2 where id = ?', array($odvalue->driver_id));

				// 					$data = array
				// 						(
				// 						'id' => $post_data['order_id'],
				// 						'type' => 2,
				// 						'title' => $order_title,
				// 						'message' => $order_title,
				// 						'log_id' => $order_logs->id,
				// 						'order_key_formated' => $vendor_info[0]->order_key_formated,
				// 						'request_type' => 1,
				// 						"order_accept_time" => $driver_settings->order_accept_time,
				// 						'notification_dialog' => "1",
				// 					);

				// 					$fields = array
				// 						(
				// 						'registration_ids' => array($odvalue->android_device_token),
				// 						'data' => $data,
				// 					);

				// 					$headers = array
				// 						(
				// 						'Authorization: key=AAAAI_fAV4w:APA91bFSR1TLAn1Vh134nzXLznsUVYiGnR4KiUYdAa3u0OccC5S-DyDdQRdnR0XugSRArsJGXC8AHE342eNhBbnK8np10KuyuWwiJxtndV75O4DyT3QCGXKFu_fwUTNPdB51Cno6Rewc',
				// 						'Content-Type: application/json',
				// 					);

				// 					//  print_r($fields);exit;

				// 					$ch = curl_init();
				// 					curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
				// 					curl_setopt($ch, CURLOPT_POST, true);
				// 					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				// 					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				// 					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				// 					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
				// 					$result = curl_exec($ch);
				// 					curl_close($ch);
				// 				}
				// 			}
				// 		}
				// 	}
				// }

				// if (isset($post_data['notify']) && $post_data['notify'] == 1) {
				// 	$order = DB::select('SELECT o.vendor_id,o.outlet_id,o.delivery_date, o.customer_id,dti.start_time,end_time FROM orders o
		  //           left join delivery_time_slots dts on dts.id=o.delivery_slot
		  //           left join delivery_time_interval dti on dti.id = dts.time_interval_id
		  //           where o.id = ?', array($post_data['order_id']));
				// 	$customer_id = isset($delivery_details[0]->customer_id) ? $delivery_details[0]->customer_id : $order[0]->customer_id;
				// 	$vendor_id = isset($delivery_details[0]->vendor_id) ? $delivery_details[0]->vendor_id : $order[0]->vendor_id;
				// 	$outlet_id = isset($delivery_details[0]->outlet_id) ? $delivery_details[0]->outlet_id : $order[0]->outlet_id;
				// 	$users = Users::find($delivery_details[0]->customer_id);
				// 	if ($post_data['order_status_id'] == 12) {

				// 		$message = 'Your order has been Delivered in  ' . getAppConfig()->site_name . ' Order reference:     ' . $vendor_info[0]->order_key_formated;
				// 		$twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
				// 		$twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
				// 		$from_number = "+14783471785";
				// 		$client = new Services_Twilio($twilo_sid, $twilio_token);
				// 		//print_r ($client);exit;
				// 		// Create an authenticated client for the Twilio API
				// 		try {
				// 			$m = $client->account->messages->sendMessage(
				// 				$from_number, // the text will be sent from your Twilio number
				// 				$users->mobile, // the phone number the text will be sent to
				// 				$message // the body of the text message
				// 			);

				// 		} catch (Exception $e) {
				// 			$result11 = array("response" => array("httpCode" => 400, "Message" => $e->getMessage()));

				// 		} catch (\Services_Twilio_RestException $e) {
				// 			$result1 = array("response" => array("httpCode" => 400, "Message" => $e->getMessage()));

				// 		}

				// 	}

				// 	$logo = url('/assets/front/' . Session::get("general")->theme . '/images/' . Session::get("general")->theme . '.png');
				// 	if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/list/' . $vendor_info[0]->logo_image)) {
				// 		$vendor_image = '<img width="100px" height="100px" src="' . URL::to("assets/admin/base/images/vendors/list/" . $vendor_info[0]->logo_image) . '") >';
				// 	} else {
				// 		$vendor_image = '<img width="100px" height="100px" src="' . URL::to("assets/front/base/images/blog_no_images.png") . '") >';
				// 	}
				// 	$delivery_date = date("d F, l", strtotime($delivery_details[0]->delivery_date));
				// 	$delivery_time = date('g:i a', strtotime($delivery_details[0]->start_time)) . '-' . date('g:i a', strtotime($delivery_details[0]->end_time));
				// 	$users = Users::find($delivery_details[0]->customer_id);

				// 	$to = $users->email;
				// 	$subject = 'Your Order with ' . getAppConfig()->site_name . ' [' . $vendor_info[0]->order_key_formated . '] has been successfully ' . $vendor_info[0]->status_name . '!';
				// 	$template = DB::table('email_templates')->select('*')->where('template_id', '=', self::ORDER_STATUS_UPDATE_USER)->get();
				// 	if (count($template)) {
				// 		$from = $template[0]->from_email;
				// 		$from_name = $template[0]->from;
				// 		if (!$template[0]->template_id) {
				// 			$template = 'mail_template';
				// 			$from = getAppConfigEmail()->contact_mail;
				// 		}
				// 		$orders_link = '<a href="' . URL::to("orders") . '" title="' . trans("messages.View") . '">' . trans("messages.View") . '</a>';
				// 		$content = array('name' => "" . $users->name, 'order_key' => "" . $vendor_info[0]->order_key_formated, 'status_name' => "" . $vendor_info[0]->status_name, 'orders_link' => "" . $orders_link);
				// 		$attachment = "";
				// 		$email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
				// 	}
				// 	$order_title = 'Your order ' . $vendor_info[0]->order_key_formated . '  has been ' . $vendor_info[0]->status_name;
				// 	/* To send the push notification for customer start*/
				// 	if (!empty($users->android_device_token)) {
				// 		$optionBuiler = new OptionsBuilder();
				// 		$optionBuiler->setTimeToLive(60 * 20);
				// 		$notificationBuilder = new PayloadNotificationBuilder($subject);
				// 		$notificationBuilder->setBody($subject)->setSound('default')->setBadge(1);
				// 		$dataBuilder = new PayloadDataBuilder();
				// 		$dataBuilder->addData(['order_id' => $post_data['order_id'], "message" => $subject]);
				// 		$option = $optionBuiler->build();
				// 		$notification = $notificationBuilder->build();
				// 		$data = $dataBuilder->build();
				// 		$token = $users->android_device_token;
				// 		$downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
				// 		$downstreamResponse->numberSuccess();
				// 		if ($downstreamResponse->numberSuccess() && $downstreamResponse->numberSuccess() == 1) {
				// 			//Notification success
				// 		}
				// 		$downstreamResponse->numberFailure();
				// 		$downstreamResponse->numberModification();
				// 		$downstreamResponse->tokensToDelete();
				// 		$downstreamResponse->tokensToModify();
				// 		$downstreamResponse->tokensToRetry();
				// 	}
				// 	if (!empty($users->ios_device_token)) {
				// 		$optionBuiler = new OptionsBuilder();
				// 		$optionBuiler->setTimeToLive(60 * 20);
				// 		$notificationBuilder = new PayloadNotificationBuilder($subject);
				// 		$notificationBuilder->setBody($subject)->setSound('default')->setBadge(1);
				// 		$dataBuilder = new PayloadDataBuilder();
				// 		$dataBuilder->addData(['order_id' => $post_data['order_id'], "message" => $subject]);
				// 		$option = $optionBuiler->build();
				// 		$notification = $notificationBuilder->build();
				// 		$data = $dataBuilder->build();
				// 		$token = $users->ios_device_token;
				// 		$downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
				// 		$downstreamResponse->numberSuccess();
				// 		/*if($downstreamResponse->numberSuccess() && $downstreamResponse->numberSuccess()==1){
				// 			                        //Notification success
				// 		*/
				// 		$downstreamResponse->numberFailure();
				// 		$downstreamResponse->numberModification();
				// 		$downstreamResponse->tokensToDelete();
				// 		$downstreamResponse->tokensToModify();
				// 		$downstreamResponse->tokensToRetry();
				// 	}
				// 	$values = array('order_id' => $post_data['order_id'],
				// 		'customer_id' => $customer_id,
				// 		'vendor_id' => $vendor_id,
				// 		'outlet_id' => $outlet_id,
				// 		'message' => $subject,
				// 		'read_status' => 0,
				// 		'created_date' => date('Y-m-d H:i:s'));
				// 	DB::table('notifications')->insert($values);

				// }
				// return 1;
//	}*/

	public function load_history($order_id) {
		if (!hasTask('admin/orders/load_history')) {
			return view('errors.404');
		}
		$order_history = DB::select('SELECT ol.order_comments,ol.order_status,log_time,order_status.name as status_name,order_status.color_code as color_code
        FROM orders_log ol
        left join order_status order_status on order_status.id = ol.order_status
        where ol.order_id = ? ORDER BY ol.id', array($order_id));


		$data_tab = '<table class="table table-bordered"><thead><tr>
                    <td class="text-left">' . trans("messages.Date") . '</td>
                    <td class="text-right">' . trans("messages.Comment") . '</td>
                    <td class="text-right">' . trans("messages.Status") . '</td>
                    </tr></thead><tbody>';
		$subtotal = "";
		foreach ($order_history as $history) {
			$data_tab .= '<tr><td class="text-left">' . date('M j Y g:i A', strtotime($history->log_time)) . '</td>';
			$data_tab .= '<td class="text-right">' . $history->order_comments . '</td>';
			$data_tab .= '<td class="text-right">' . $history->status_name . '</td></tr>';
		}
		$data_tab .= '</tbody>';
		$data_tab .= '</table>';
		echo $data_tab;exit;
	}

	public function get_order_detail($order_id) {
		$language_id = getCurrentLang();
		$query3 = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$query4 = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$vendor_info = DB::select('SELECT vendors_infos.vendor_name,vendors.email, outlets.latitude as outlet_latitude,outlets.longitude as outlet_longitude,o.driver_ids,vendors.logo_image,o.id as order_id,o.created_date,o.order_status,o.order_key_formated,order_status.name as status_name,order_status.color_code as color_code,payment_gateways_info.name as payment_gateway_name,o.outlet_id,vendors.id as vendor_id,o.order_key_formated
        FROM orders o
        left join vendors vendors on vendors.id = o.vendor_id
           left join outlets outlets on outlets.id = o.outlet_id
        left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
        left join order_status order_status on order_status.id = o.order_status
        left join payment_gateways payment_gateways on payment_gateways.id = o.payment_gateway_id
        left join payment_gateways_info payment_gateways_info on payment_gateways_info.payment_id = payment_gateways.id
        where ' . $query3 . ' AND ' . $query4 . ' AND o.id = ? ORDER BY o.id', array($order_id));
		$query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and p.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$order_items = DB::select('SELECT p.product_image,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.invoice_id,pi.product_name,pi.description,o.coupon_amount
        FROM orders o
        LEFT JOIN orders_info oi ON oi.order_id = o.id
        LEFT JOIN products p ON p.id = oi.item_id
        LEFT JOIN products_infos pi ON pi.id = p.id
        where ' . $query . ' AND o.id = ? ORDER BY oi.id', array($order_id));

		$query2 = 'pgi.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and pg.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$delivery_details = DB::select('SELECT o.delivery_instructions,ua.address,pg.id as payment_gateway_id,pgi.name,o.total_amount,o.delivery_charge,o.service_tax,dti.start_time,end_time,o.created_date,o.delivery_date,o.order_type,out_inf.contact_address,o.coupon_amount,o.customer_id FROM orders o
                    LEFT JOIN user_address ua ON ua.id = o.delivery_address
                    left join payment_gateways pg on pg.id = o.payment_gateway_id
                    left join payment_gateways_info pgi on pgi.payment_id = pg.id
                    left join delivery_time_slots dts on dts.id=o.delivery_slot
                    left join delivery_time_interval dti on dti.id = dts.time_interval_id
                    left join outlets out on out.id = o.outlet_id
                    left join outlet_infos out_inf on out.id = o.outlet_id
                    where ' . $query2 . ' AND o.id = ?', array($order_id));
		if (count($order_items) > 0 && count($delivery_details) > 0 && count($vendor_info) > 0) {
			$result = array("order_items" => $order_items, "delivery_details" => $delivery_details, "vendor_info" => $vendor_info);
		}
		return $result;
	}
	/*
		    * Vendor Fund Request List Amount
	*/
	public function fund_requests_list() {
		if (Auth::guest()) {
			return redirect()->guest('admin/login');
		} else {
			if (!hasTask('orders/fund_requests')) {
				return view('errors.404');
			}
			$condition = "1=1";
			if (Input::get('from') && Input::get('to')) {
				$from = date('Y-m-d H:i:s', strtotime(Input::get('from')));
				$to = date('Y-m-d H:i:s', strtotime(Input::get('to')));
				$condition .= " and payment_request_vendors.created_date BETWEEN '" . $from . "'::timestamp and '" . $to . "'::timestamp";
			}
			$query = '"vendors_infos"."lang_id" = (case when (select count(id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . getCurrentLang() . ' and payment_request_vendors.vendor_id = vendors_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
			$list_data = DB::table('payment_request_vendors')
				->join('vendors_infos', 'vendors_infos.id', '=', 'payment_request_vendors.vendor_id')
			//->join('transaction','transaction.vendor_id','=','payment_request_vendors.vendor_id')
				->select('payment_request_vendors.id', 'payment_request_vendors.approve_status', 'payment_request_vendors.vendor_id', 'payment_request_vendors.created_date', 'payment_request_vendors.modified_date', 'payment_request_vendors.current_balance', 'payment_request_vendors.request_amount', 'payment_request_vendors.unique_id', 'vendors_infos.vendor_name')
				->whereRaw($query)
				->whereRaw($condition)
				->orderBy('payment_request_vendors.created_date', 'desc')
				->get();
			//print_r($list_data);
			return view('admin.request_amount.list')->with('return_orders', $list_data);
		}
	}
	/*
		    * Ajax request payment request for vendors
	*/
	public function anyAjaxRequestPayments() {
		$currency_side = getCurrencyPosition()->currency_side;
		$language = getCurrentLang();
		$currency_symbol = getCurrency($language);
		$query = '"vendors_infos"."lang_id" = (case when (select count(id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . getCurrentLang() . ' and payment_request_vendors.vendor_id = vendors_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
		$list_data = DB::table('payment_request_vendors')
			->join('vendors_infos', 'vendors_infos.id', '=', 'payment_request_vendors.vendor_id')
		//->join('transaction','transaction.vendor_id','=','payment_request_vendors.vendor_id')
			->select('payment_request_vendors.*', 'vendors_infos.vendor_name')
			->whereRaw($query)
			->orderBy('payment_request_vendors.created_date', 'desc');
		return Datatables::of($list_data)->addColumn('action', function ($list_data) {
			if (hasTask('orders/approve_fund_status')) {
				$html = '--';
				if ($list_data->approve_status == 0):
					$html = '<div class="request_amount_' . $list_data->id . '">            <select name="status" id="fund_status_' . $list_data->id . '" class="form-control" onchange="approve_fund_status(' . $list_data->id . ',' . $list_data->vendor_id . ')">																																																																		                            <option ' . (($list_data->approve_status == 0) ? "selected='selected'" : "") . ' value="0">' . trans("messages.Pending") . '</option>																																																													                            <option ' . (($list_data->approve_status == 1) ? "selected='selected'" : "") . ' value="1">' . trans("messages.Completed") . '</option>																																																																																		                            <option ' . (($list_data->approve_status == 2) ? "selected='selected'" : "") . ' value="2">' . trans("messages.Cancelled") . '</option>																		                        </select>																																					                    </div>';
				endif;
				return $html;
			}
		})
			->addColumn('approve_status', function ($list_data) {
				if ($list_data->approve_status == 0):
					$data = '<span class="label label-warning" id="approve_status_' . $list_data->id . '">' . trans("messages.Pending") . '</span>';
				elseif ($list_data->approve_status == 1):
					$data = '<span class="label label-success">' . trans("messages.Completed") . '</span>';
				elseif ($list_data->approve_status == 2):
					$data = '<span class="label label-danger">' . trans("messages.Cancelled") . '</span>';
				endif;
				return $data;
			})
			->addColumn('modified_date', function ($list_data) {
				$data = '-';
				if (!empty($list_data->modified_date)):
					$data = trim($list_data->modified_date);
				endif;
				return $data;
			})
			->addColumn('current_balance', function ($list_data) {
				if ($currency_side == 1) {
					$data = $currency_symbol . $list_data->current_balance;
				} else {
					$data = $list_data->current_balance . $currency_symbol;
				}
				return $data;
			})
			->addColumn('request_amount', function ($list_data) {
				if ($currency_side == 1) {
					$data = $currency_symbol . $list_data->request_amount;
				} else {
					$data = $list_data->request_amount . $currency_symbol;
				}
				return $data;
			})
			->rawColumns(['request_amount','current_balance','modified_date','approve_status','action'])

			->make(true);
	}
	//Get city list for ajax request
	public function update_fund_request(Request $request) {
		if (!hasTask('orders/approve_fund_status')) {
			return view('errors.404');
		}
		if ($request->ajax()) {
			$_id = $request->input('cid');
			$v_id = $request->input('vid');
			$status = $request->input('status');
			$query = '"vendors_infos"."lang_id" = (case when (select count(id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . getCurrentLang() . ' and vendors.id = vendors_infos.id) > 0 THEN ' . getCurrentLang() . ' ELSE 1 END)';
			$funds = DB::table('payment_request_vendors')
				->leftJoin('vendors', 'vendors.id', '=', 'payment_request_vendors.vendor_id')
				->leftJoin("vendors_infos", function ($join) {
					$join->on("vendors_infos.id", "=", "payment_request_vendors.vendor_id")
						->on("vendors_infos.id", "=", "vendors.id");
				})
				->select('payment_request_vendors.*', 'vendors.email', 'vendors_infos.vendor_name')
				->whereRaw($query)
				->where('payment_request_vendors.id', $_id)
				->orderBy('payment_request_vendors.created_date', 'desc')->get();
			//Update return approve status with payment request vendors table
			$admin_id = Auth::id();
			$date = date('Y-m-d H:i:s');
			if ($status == 1) {
				$approve_status = 'Completed';
				$result = DB::update('update payment_request_vendors set approve_status = ?,modified_by = ?,modified_date = ? where id = ?', array($status, $admin_id, $date, $_id));
			} else if ($status == 2) {
				$approve_status = 'Cancelled';
				$result = DB::update('update payment_request_vendors set approve_status = ?,modified_by = ?,modified_date = ? where id = ?', array($status, $admin_id, $date, $_id));
				//Return back amount to vendor
				$result1 = DB::update('update vendors set current_balance = current_balance + ? where id = ?', array($funds[0]->request_amount, $v_id));
			}
			//Send mail to vendor regarding when admin changed approval status
			$template = DB::table('email_templates')
				->select('*')
				->where('template_id', '=', self::REFUND_APPROVE_EMAIL_TEMPLATE)
				->get();
			if (count($template)) {
				$from = $template[0]->from_email;
				$from_name = $template[0]->from;
				$subject = $template[0]->subject;
				if (!$template[0]->template_id) {
					$template = 'mail_template';
					$from = getAppConfigEmail()->contact_email;
					$subject = getAppConfig()->site_name . " Refund Request Information";
					$from_name = "";
				}
				$cont_replace = "Following Fund Request ID: <b>" . $funds[0]->unique_id . "</b> Administrator was updated following status <b>" . $approve_status . "</b>.";
				$cont_replace1 = "We wish you will get more customers and earnings and make benefit of everyone.";
				$content = array("name" => $funds[0]->vendor_name, "email" => $funds[0]->email, "replacement" => $cont_replace, "replacement1" => $cont_replace1);
				$email = smtp($from, $from_name, $funds[0]->email, $subject, $content, $template);
			}
			$res = ($result) ? 1 : 0;
			return response()->json([
				'data' => $res,
			]);
		}
	}
	public function order_destory($id) {
		if (!hasTask('admin/orders/delete')) {
			return view('errors.404');
		}
		$order = Order::find($id);
		$order->delete();
		Session::flash('message', trans('messages.Order has been deleted successfully!'));
		return Redirect::to('admin/orders/index');
	}
	public function driver_orders() {
		if (!hasTask('admin/orders/delete')) {
			return view('errors.404');
		}
		$order = Order::find($id);
		$order->delete();
		Session::flash('message', trans('messages.Order has been deleted successfully!'));
		return Redirect::to('admin/orders/index');
	}
	/* To assign driver for orders */
	public function assign_driver_orders(Request $data) {

		//print_r("expression");exit();

		if (!hasTask('admin/orders/index')) {
			return view('errors.404');
		}
		$data_all = $data->all();
		$validation = Validator::make($data_all, array(
			'order_id' => 'required',
			'driver' => 'required',
		));
		if ($validation->fails()) {
			$errors = '';
			$j = 0;
			foreach ($validation->errors()->messages() as $key => $value) {
				$error[] = is_array($value) ? implode(',', str_replace(".", " ", $value)) : str_replace(".", " ", $value);
			}
			$errors = implode("<br>", $error);
			$result = array("response" => array("httpCode" => 400, "Error" => trans("messages.Error List"), "Message" => $errors));
		} else {
			
			$new_orders = Order::find($data_all['order_id']);
			if($new_orders->driver_ids == ''){
				$driver_settings = Driver_settings::find(1);

				$orders = DB::table('orders')
					->select('driver_ids')
					->where('orders.id', $data_all['order_id'])
					->first();
				$driver_ids = $orders->driver_ids;

				// $new_orders->driver_ids = $driver_ids.$data_all['driver'].',';
				$new_orders->driver_ids = /*$driver_ids . */$data_all['driver'];
				$assigned_time = strtotime("+ " . $driver_settings->order_accept_time . " minutes", strtotime(date('Y-m-d H:i:s')));
				$update_assign_time = date("Y-m-d H:i:s", $assigned_time);
				$new_orders->assigned_time = $update_assign_time;
				$new_orders->save();

				$order_title = 'order assigned to you';
				$driver_detail = Drivers::find($data_all['driver']);
				/*$order_logs = new Autoassign_order_logs;
					$order_logs->driver_id = $data_all['driver'];
					$order_logs->order_id = $data_all['order_id'];
					$order_logs->driver_response = 0;
					$order_logs->driver_token = $driver_detail->android_device_token;
					$order_logs->order_delivery_status = 0;
					$order_logs->assign_date = date("Y-m-d H:i:s");
					$order_logs->created_date = date("Y-m-d H:i:s");
					$order_logs->order_subject = $order_title;
					// $order_logs->order_subject_arabic = $order_title1;
					$order_logs->order_message = $order_title;


				*/

					/*$driverDetail=DB::table('drivers')
								->select('drivers.first_name')
								->where('drivers.id', '=', (int) $data_all['driver'])
								->get();
				$driver_name= isset($driverDetail[0]->first_name)?$driverDetail[0]->first_name:'';
				$accept="Request to ".$driver_name;

				

				$affected=DB::table('orders_log')
							->where('orders_log.order_id','=',$data_all['order_id'])
							->where('orders_log.order_status','=',18)
							->update(['order_comments'=>$accept]);*/

				$affected = DB::update('update drivers set driver_status = 4 where id = ?', array($data_all['driver']));
				driver_assignlog($data_all['order_id'],$data_all['driver']);


				if ($driver_detail->android_device_token != '') {

					$orders = Order::find($data_all['order_id']);

					$data = array
						(
						'id' => $data_all['order_id'],
						'type' => 2,
						'title' => $order_title,
						'message' => $order_title,
						//	'log_id' => $order_logs->id,
						'order_key_formated' => $orders->order_key_formated,
						'request_type' => 2,
						"order_accept_time" => $driver_settings->order_accept_time,
						'notification_dialog' => "1",
					);

					$fields = array
						(
						'registration_ids' => array($driver_detail->android_device_token),
						'data' => $data,
					);

					$headers = array
						(
						'Authorization: key=AAAAI_fAV4w:APA91bFSR1TLAn1Vh134nzXLznsUVYiGnR4KiUYdAa3u0OccC5S-DyDdQRdnR0XugSRArsJGXC8AHE342eNhBbnK8np10KuyuWwiJxtndV75O4DyT3QCGXKFu_fwUTNPdB51Cno6Rewc',
						'Content-Type: application/json',
					);

					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
					$result = curl_exec($ch);
					curl_close($ch);
					//echo $result;

				}

				Session::flash('message', trans('messages.Driver assigned successfully'));
				$result = array("response" => array("httpCode" => 200, "Message" => trans('messages.Driver assigned successfully')));
			}else
			{
				Session::flash('message-failure', trans('messages.Driver Already assigned'));
				$result = array("response" => array("httpCode" => 400, "Message" => trans('messages.Driver Already assigned')));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	public function order_cancel($id) {

		$orders = DB::table('transaction')
			->select('*')
			->where('order_id', '=', $id)
			->where('payment_status', '=', 'SUCCESS')
			->get();
		if (!count($orders)) {
			$result = array("response" => array("httpCode" => 400, "Message" => "No orders found", "order_items" => array()));
			return json_encode($result);
		}
		if ($orders[0]->payment_type == "paypal") {
			$transaction_id = $orders[0]->transaction_id;
			$capture_get = Capture::get($transaction_id, $this->_apiContext);

			//$capture_get = Capture::get('8GK68812KY030334A', $this->_apiContext);
			if ($capture_get->getState() == "completed") {
				$refund = new Refund();
				$amt = new Amount();
				$amt->setCurrency("USD")
					->setTotal($capture_get->getAmount()->getTotal());
				$refund->setAmount($amt);
				$refundstatus = false;

				//$refund->setId($transaction_id);
				try {
					//print_r($refund);exit;
					$captureRefund = $capture_get->refund($refund, $this->_apiContext);

					$Refundget = Refund::get($captureRefund->getId(), $this->_apiContext);
					//print_r($Refundget->getId());exit;
					$refund_id = $Refundget->getId();
					$refund_status = $Refundget->getState();
					if ($Refundget->getId()) {
						$transaction = Transaction::find($orders[0]->id);
						//print_r($transaction);exit;
						$transaction->refund_id = $refund_id;
						$transaction->refund_status = $refund_status;
						$transaction->captured = 4;
						$transaction->refund_updated_date = date("Y-m-d H:i:s");
						$transaction->save();
						$refundstatus = true;
					}
				} catch (PayPal\Exception\PayPalConnectionException $ex) {
					echo $ex->getCode(); // Prints the Error Code
					echo $ex->getData(); // Prints the detailed error message
					//die($ex);
					exit;
				} catch (Exception $ex) {
					echo $ex->getMessage();
					die($ex);
					echo "Problem in order refund process";exit;
				}

				if ($refundstatus) {
					$affected = DB::update('update orders set order_status = ? where id = ? AND order_status = ?', array(11, $post_data['order_id'], 1));
					$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Order has been cancelled ")));
					$order_detail = $this->get_order_details($post_data['order_id']);
					$order_details = $order_detail["order_items"];
					$delivery_details = $order_detail["delivery_details"];
					$vendor_info = $order_detail["vendor_info"];
					$logo = url('/assets/front/' . Session::get("general")->theme . '/images/' . Session::get("general")->theme . '.png');
					if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/list/' . $vendor_info[0]->logo_image)) {
						$vendor_image = '<img width="100px" height="100px" src="' . URL::to("assets/admin/base/images/vendors/list/" . $vendor_info[0]->logo_image) . '") >';
					} else {
						$vendor_image = '<img width="100px" height="100px" src="' . URL::to("assets/front/" . Session::get('general')->theme . "/images/blog_no_images.png") . '") >';
					}
					$delivery_date = date("d F, l", strtotime($delivery_details[0]->delivery_date));
					$delivery_time = date('g:i a', strtotime($delivery_details[0]->start_time)) . '-' . date('g:i a', strtotime($delivery_details[0]->end_time));
					$users = Users::find($delivery_details[0]->customer_id);
					$to = $users->email;
					$subject = 'Your Order with ' . getAppConfig()->site_name . ' [' . $vendor_info[0]->order_key_formated . '] has been successfully ' . $vendor_info[0]->status_name . '!';
					$template = DB::table('email_templates')
						->select('*')
						->where('template_id', '=', self::ORDER_STATUS_UPDATE_USER)
						->get();
					if (count($template)) {
						$from = $template[0]->from_email;
						$from_name = $template[0]->from;
						if (!$template[0]->template_id) {
							$template = 'mail_template';
							$from = getAppConfigEmail()->contact_mail;
						}
						$orders_link = '<a href="' . URL::to("orders") . '" title="' . trans("messages.View") . '">' . trans("messages.View") . '</a>';
						$content = array('name' => "" . $users->name, 'order_key' => "" . $vendor_info[0]->order_key_formated, 'status_name' => "" . $vendor_info[0]->status_name, 'orders_link' => "" . $orders_link);
						$attachment = "";
						$email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
					}
				} else {
					$result = array("response" => array("httpCode" => 400, "Message" => "No orders found", "order_items" => array()));
				}
			} else {
				$result = array("response" => array("httpCode" => 400, "Message" => "No orders found", "order_items" => array()));
			}
		} else {
			$affected = DB::update('update orders set order_status = ? where id = ? AND order_status = ?', array(11, $id, 1));
		}
		Session::flash('message', trans('messages.Order has been cancelled successfully!'));
		return Redirect::to('admin/orders/index');
	}

	public function orderedit($id)
	{
		$customer_id = DB::table('orders')
				->select('customer_id')
				->where('id','=',$id)
				->first();
		$user_id = $customer_id;
		$language = getCurrentLang();
		$user_array = array("user_id" => $user_id,"language" => $language,"order_id" => $id);
		//print_r($user_array);exit;
		$response = order::get_cart($user_array);
		//echo"<pre>";print_r($response);exit;
		$response= json_decode($response);
		//echo"<pre>";print_r($response->response);exit;
		if ($response->response->httpCode == 400) {
			$cart_items = array();
		} else {
			//print_r("expression");exit;
			$cart_items = $response->response->cart_items;
			$outlet_id = $response->response->outlet_id;
			$customer_id = $response->response->cart_items[0]->customer_id;
			$coupon_code = $response->response->cart_items[0]->coupon_code;
			$coupon_id = $response->response->cart_items[0]->coupon_id;
			$coupon_type = $response->response->cart_items[0]->coupon_type;
			$coupon_amount = $response->response->cart_items[0]->coupon_amount;
			$total = $response->response->total;
			$sub_total = $response->response->sub_total;
			$tax = $response->response->tax;
			$delivery_cost = $response->response->delivery_cost;
			$tax_amount = $sub_total * $tax / 100;
		}
		//echo"<pre>";print_r($customer_id);exit;
		return view('admin.orders.edit')->with("cart_items", $cart_items)->with("total", $total)->with("sub_total", $sub_total)->with("tax_amount", $tax_amount)->with("delivery_cost", $delivery_cost)->with("language", $language)->with("order_id", $id)->with("tax", $tax)->with("outlet_id", $outlet_id)->with("customer_id", $customer_id)->with("coupon_amount", $coupon_amount)->with("coupon_type", $coupon_type)->with("coupon_id", $coupon_id)->with("coupon_code", $coupon_code);
	}
	public function update_cart(Request $request)
	{
		$post_data =$request->all();
		//echo"<pre>";print_r($post_data);echo"<br>";exit();

		$data = DB::table('orders')
			->select('*')
			->where('orders.id', $post_data['order_id'])
			->get();
		//echo"<pre>";print_r($data);exit;

        $language = getCurrentLang();
		$payment_array = array();

		$payment_array['admin_commission'] = 0;
		if($post_data['coupon_id'] != '')
		{

			if($post_data['total'] > $post_data['coupon_amount'] )
			{
				$total_amount =  $post_data['total'] -  $post_data['coupon_amount'];

			}else
			{
				$total_amount = 0;
			}

		}else{
			$total_amount = $post_data['total'];

		}
		//print_r($total_amount);exit;
		
		//$total_amount = ($post_data['sub_total'] + $post_data['tax'] + $post_data['delivery_cost']) - $data[0]->coupon_amount;
		$admin_commission = $data[0]->admin_commission;
		$payment_array['admin_commission'] = ($admin_commission + $post_data['tax'] + $post_data['delivery_cost']);
		//echo"<pre>";print_r($total_amount);echo"<br>";
		//echo"<pre>";print_r($admin_commission);echo"<br>";
		
	    //print_r($payment_array);exit;
		//exit;
		$vendor_commission = $post_data['sub_total'] - $admin_commission;
		$payment_array['vendor_commission'] = $vendor_commission;
		$payment_array['user_id'] = $data[0]->customer_id;
		$payment_array['store_id'] =$data[0]->vendor_id;
		$payment_array['outlet_id'] = $data[0]->outlet_id;
		$payment_array['total'] = $total_amount;
		$payment_array['sub_total'] = $post_data['sub_total'];
		$payment_array['service_tax'] = $post_data['tax'];
		$payment_array['tax_amount'] = $post_data['tax'];
		$payment_array['order_status'] = 1;
		$payment_array['order_key'] = str_random(32);
		$payment_array['invoice_id'] = str_random(32);
		$payment_array['transaction_id'] = str_random(32);
		$payment_array['transaction_staus'] = 1;
		$payment_array['transaction_amount'] = $total_amount;
		$payment_array['payer_id'] = str_random(32);
		$payment_array['currency_code'] = getCurrency($language);
		$payment_array['payment_gateway_id'] =$data[0]->payment_gateway_id;
		$payment_array['coupon_type'] = $post_data['coupon_type'];
		$payment_array['delivery_charge'] = 0;
		$payment_array['payment_status'] = 0;
		$payment_array['payment_gateway_commission'] = "";
		$payment_array['delivery_instructions'] = $data[0]->delivery_instructions;
		$payment_array['delivery_address'] = $data[0]->delivery_address;
		$payment_array['delivery_slot'] =$data[0]->delivery_slot;
		$payment_array['delivery_date'] =$data[0]->delivery_date;
		$payment_array['order_type'] = $data[0]->order_type;
		$payment_array['coupon_id'] =  $post_data['coupon_id'];
		$payment_array['coupon_amount'] =  $post_data['coupon_amount'];
		$payment_array['coupon_type'] = $post_data['coupon_type'];
		$payment_array['delivery_cost'] =$post_data['delivery_cost'];
		$payment_array['current_order_id'] =  $post_data['order_id'];
		$payment_array['vendor_key'] =    $post_data['vendor_key'];
		$payment_array['vendor_name'] =  $post_data['vendor_name'];
		//echo '<pre>';  print_r($payment_array);exit;
		$items = array();
		$i = 0;
        if(count($post_data['products_name']) != 0 )
        {
			foreach ($post_data['products_name'] as $key => $value) {
				//echo"<pre>";	print_r($post_data);exit;
				$items[$i]['product_id'] = $value;
				$items[$i]['quantity'] = $post_data['quantity'][$key];
				$items[$i]['discount_price'] = $post_data['item_price'][$key];
				$items[$i]['item_offer'] = 0;
				//echo"<pre>";print_r($post_data);exit;
				$items[$i]['adjust_weight_qty'] =  isset($post_data['adjust_weight_qty'][$key])?$post_data['adjust_weight_qty'][$key]:0;
				
				//for adjustment qty

				/*$weight = $post_data['weight'][$key];
				$adjst = isset($post_data['adjust_weight_qty'][$key])?$post_data['adjust_weight_qty'][$key]:0;
				$quantity = isset($post_data['quantity'][$key])?$post_data['quantity'][$key]:0;
				$item_total = isset($post_data['item_total'][$key])?$post_data['item_total'][$key]:0;
				$item_price = $item_total/$quantity;
				$adjt_tot = $item_price/$weight;
				$adjt_tot =$adjt_tot*$quantity;
				$payment_array['total'] = $total_amount+$adjt_tot;*/
				//for adjustment qty
				$i++;
			}
		}
		$payment_array['items'] = $items;
		echo"<pre>";print_r($payment_array);exit;

		$payment_array = json_encode($payment_array);
		//echo"<pre>";print_r($payment_array);exit;

		$user_id = $data[0]->customer_id;

		$user_array = array("user_id" => $user_id, "language" => $language, "payment_array" => $payment_array);
		//print_r($user_array);exit();

		$response = order::offline_payment($user_array);
		$response=json_decode($response);
		//echo "<pre>";print_r($response->response);exit;
		//{"response":{"httpCode":200,"Message":"Order initated success","order_id":2653}}
		//$response = $this->api->call_api($data, '/api/offline_payment', $method);

		if (isset($response->response->httpCode) && $response->response->httpCode == 200) {
			
			Session::flash('message', trans('messages.Order placed successfully'));
            return Redirect::to('admin/orders/index');
		} else {
			Session::flash('message-failure', $response->response->Message);
			return Redirect::to('admin/orders/index');
		}
	}

	public function vendor_request_admin(Request $data) {
		if (!hasTask('vendors/orders/request_admin')) {
			return view('errors.404');
		}
		$data_all = $data->all();

		$validation = Validator::make($data_all, array(
			'id' => 'required',
			'vendor_id' => 'required',
			'order_id' => 'required',
		));
		if ($validation->fails()) {
			$errors = '';
			$j = 0;
			foreach ($validation->errors()->messages() as $key => $value) {
				$error[] = is_array($value) ? implode(',', str_replace(".", " ", $value)) : str_replace(".", " ", $value);
			}
			$errors = implode("<br>", $error);
			$result = array("response" => array("httpCode" => 400, "Error" => trans("messages.Error List"), "Message" => $errors));
		} else {
			

			//$vendors = vendors::find($data_all['vendor_id']);

			/*admin notification*/
			$vendors = DB::table('vendors_infos')
				->select('vendor_name')
				->where('vendors_infos.id', $data_all['vendor_id'])
				->first();

			$subject = 'Request form vendor '.$vendors->vendor_name;
			$values = array('order_id' => $data_all['order_id'],
					'customer_id' => 0,
					'vendor_id' => $data_all['vendor_id'],
					'outlet_id' => 0,
					'message' => $subject,
					'read_status' => 0,
					'created_date' => date('Y-m-d H:i:s'));
			DB::table('notifications')->insert($values);

			/*admin notification*/
			
			/*request admin log*/
			$request_admin      = new request_admins;
			$request_admin->vendor_id = $data_all['vendor_id'];
			$request_admin->created_at = date('Y-m-d H:i:s');
			$request_admin->updated_at = date('Y-m-d H:i:s');
			$request_admin->order_id = $data_all['order_id'];
			$request_admin->save();
			/*request admin log*/


			

			$affected = DB::update('update orders set request_vendor = 1 where id = ?', array($data_all['id']));
			
			Session::flash('message', trans('messages.Request send to admin successfully'));
			$result = array("response" => array("httpCode" => 200, "Message" => trans('messages.Request send to admin successfully')));
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}




}
