<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
use URL;
use Session;
use App\Model\order;
use App\Model\orders;
use App;
use App\Http\Controllers\Controller;
use App\Model\cart_info;
use App\Model\cart_model;
use App\Model\users;
use App\Model\Users\addresstype;
use App\Model\vendors;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use FCM;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Text;
use JWTAuth;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use Paypal;
use PayPal\Api\Amount;
use PayPal\Api\Capture;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payment;
use PayPal\Api\Refund;
use Services_Twilio;
use Twilio;
use Tymon\JWTAuth\Exceptions\JWTException;


class order extends Model
{
	public $timestamps  = false;
	protected $table = 'orders';
    const USER_SIGNUP_EMAIL_TEMPLATE = 1;
	const USERS_WELCOME_EMAIL_TEMPLATE = 3;
	const USERS_FORGOT_PASSWORD_EMAIL_TEMPLATE = 6;
	const USER_CHANGE_PASSWORD_EMAIL_TEMPLATE = 13;
	const OTP_EMAIL_TEMPLATE = 14;
	const ORDER_MAIL_TEMPLATE = 5;
	const ORDER_MAIL_VENDOR_TEMPLATE = 16;
	const RETURN_STATUS_CUSTOMER_EMAIL_TEMPLATE = 17;
	const ORDER_STATUS_UPDATE_USER = 18;


	public static function outlet_details_by_order($order_id, $language = '')
	{
		if(empty($language))
			$language = getCurrentLang();
		$query = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.language_id) as totalcount from outlet_infos where outlet_infos.language_id = '.$language.' and outlets.id = outlet_infos.id) > 0 THEN '.$language.' ELSE 1 END)';
		$outlet_data = DB::table('orders')
						->select('outlets.id as outlet_id','outlet_infos.outlet_name','orders.order_key_formated')
						->join('outlets','outlets.id','=','orders.outlet_id')
						->join('outlet_infos','outlet_infos.id','=','outlets.id')
						->whereRaw($query)
						->where('orders.id',$order_id)
						->first();
		//print_r($user_data);exit;
		return $outlet_data;
	}
	
	public static function get_driver_current_location($order_id)
	{
		$driver_current_location = DB::table('orders')
									->select('driver_orders.driver_id','driver_track_location.latitude as driver_latitude','driver_track_location.longitude as driver_longitude','drivers.first_name as driver_first_name','drivers.last_name as driver_last_name','drivers.mobile_number as driver_mobile_number','user_address.latitude as user_latitude','user_address.longtitude as user_longtitude','drivers.profile_image as driver_profile_image','orders.total_amount','orders.delivery_instructions','orders.delivery_date','delivery_time_interval.start_time','delivery_time_interval.end_time','outlets.delivery_time as outlet_delivery_minites','orders.id as order_id')
									->join('driver_orders','driver_orders.order_id','=','orders.id')
									->join('drivers','drivers.id','=','driver_orders.driver_id')
									->join('driver_track_location','driver_track_location.driver_id','=','driver_orders.driver_id')
									->join('delivery_time_slots','delivery_time_slots.id','=','orders.delivery_slot')
									->join('delivery_time_interval','delivery_time_interval.id','=','delivery_time_slots.time_interval_id')
									->join('outlets','outlets.id','=','orders.outlet_id')
									->leftjoin('user_address','user_address.id','=','orders.delivery_address')
									->where('driver_orders.order_id',$order_id)
									->orderby('driver_track_location.id','desc')->first();
		return $driver_current_location;
	}

	public static function get_cart($value='')
	{
		//print_r($value);exit;
		$post_data = $value;
		$language_id = $post_data['language'];
		$cart_items = order::calculate_cart($post_data['language'], $post_data['user_id'],$post_data['order_id']);
		//echo"<pre>";	print_r($cart_items);exit;
		$result = array("response" => array("httpCode" => 200, "Message" => "Cart details", "cart_items" => $cart_items['cart_items'], "total" => $cart_items['total'], "sub_total" => $cart_items['sub_total'], "tax" => $cart_items['tax'], "tax_amount" => $cart_items['tax_amount'], "outlet_id" => $cart_items['outlet_id'], "outlet_name" => $cart_items['outlet_name'], "vendor_image" => $cart_items['vendor_image'], "minimum_order_amount" => $cart_items['minimum_order_amount'], "vendor_id" => $cart_items['vendor_id'], "delivery_cost" => (double) $cart_items['delivery_cost'], "delivery_time" => $cart_items['delivery_time']));
		return json_encode($result);
	}
	
	public static function calculate_cart($language, $user_id,$order_id) {

		$query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = ' . $language . ' and p.id = products_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
		$cart_data = DB::select('SELECT p.product_image,p.discount_price,p.sub_category_id,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.invoice_id,pi.product_name,pi.id,pi.description,o.coupon_amount,vi.vendor_name,ou.service_tax,p.weight,p.weight_class_id,p.adjust_weight	,wci.title,oi.adjust_weight_qty	,v.vendor_key,o.outlet_id,o.customer_id,o.coupon_id,o.coupon_amount,o.coupon_type,cp.coupon_code	
		        FROM orders o
		        LEFT JOIN orders_info oi ON oi.order_id = o.id
		        LEFT JOIN products p ON p.id = oi.item_id
		        LEFT JOIN products_infos pi ON pi.id = p.id
		        LEFT JOIN vendors_infos vi ON vi.id = o.vendor_id
		        LEFT JOIN outlets ou ON ou.id = o.outlet_id
		        LEFT JOIN weight_classes_infos wci ON wci.id = p.weight_class_id
		        LEFT JOIN vendors v ON v.id = o.vendor_id
		        LEFT JOIN coupons cp ON cp.id = o.coupon_id
		        where ' . $query . ' AND o.id = ? ORDER BY oi.id', array($order_id));

		//echo"<pre>";print_r($cart_data);exit;

		//$cart_data = cart_model::cart_items($language, $user_id);
		//print_r($language);exit;

		$delivery_settings = order::get_delivery_settings();
		$sub_total = $tax = $delivery_cost = 0;
		$vendor_id = $outlet_id = '';
		$minimum_order_amount = 0;
		$outlet_name = '';
		$vendor_image = '';
		$featured_image = '';
		$delivery_time = '';
		foreach ($cart_data as $key => $items) {
			$quantity =$items->item_unit;
			$outlet_id =$items->outlet_id;
			$customerid =$items->customer_id;
			$coupon_code =$items->coupon_code;

			$sub_total += $items->item_unit * $items->discount_price;
			//print_r($sub_total);echo "<br>";

			$adjust_weight_qty = $items->adjust_weight_qty;
			$item_cost = $items->item_cost;
			$weight = $items->weight;
			if($adjust_weight_qty && $adjust_weight =1){
				$adj_tot = $adjust_weight_qty *($item_cost/$weight);
				$sub_tot =$adj_tot *$quantity;
				$sub_total =$sub_total + $sub_tot;
			}
			$tax += $items->service_tax;

			$product_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
			if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $items->product_image) && $items->product_image != '') {
				$product_image = url('/assets/admin/base/images/products/list/' . $items->product_image);
			}
			$cart_data[$key]->image_url = $product_image;
			$vendor_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
			/*if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/list/' . $items->featured_image) && $items->featured_image != '') {
				$vendor_image = url('/assets/admin/base/images/vendors/list/' . $items->featured_image);
			}*/

			$cart_data[$key]->vendor_image = $vendor_image;
			$cart_data[$key]->quantity = $quantity;

			$category_list = getCategoryListsById($items->sub_category_id);
			$cart_data[$key]->sub_category_name = isset($category_list->category_name) ? $category_list->category_name : '';
		}//exit();
		//print_r($sub_total);exit;

		$tax_amount = $sub_total * $tax / 100;
		$total = $sub_total + $tax_amount;
		if ($delivery_settings->on_off_status == 1) {
			if ($delivery_settings->delivery_type == 1) {
				$total = $total + $delivery_settings->delivery_cost_fixed;
				$delivery_cost = $delivery_settings->delivery_cost_fixed;
			}
			if ($delivery_settings->delivery_type == 2) {
				$total = $total + $delivery_settings->flat_delivery_cost;
				$delivery_cost = $delivery_settings->flat_delivery_cost;
			}

		}
		//echo"<pre>";print_r($coupon_code);exit;

		return array("cart_items" => $cart_data, "total" => $total, "sub_total" => $sub_total, "delivery_cost" => $delivery_cost, "tax" => $tax, "vendor_id" => $vendor_id, "outlet_id" => $outlet_id, "minimum_order_amount" => $minimum_order_amount, "tax_amount" => $tax_amount, "outlet_name" => $outlet_name, "vendor_image" => $vendor_image, "delivery_time" => $delivery_time ,"outlet_id" => $outlet_id);
	}
	public static function get_delivery_settings() {
		$delivery_settings = DB::table('delivery_settings')->first();
		return $delivery_settings;
	}

	public static function offline_payment($data) {
		
		$post_data = $data;
		$current_date = strtotime(date('Y-m-d'));
		$payment_array = json_decode($post_data['payment_array']);
		$payment_arrays = json_decode($post_data['payment_array'], true);
		//echo"<pre>";print_r($payment_arrays);exit;

		$rules = array();
		if ($payment_array->order_type == 1) {

			$rules['delivery_address'] = 'required';
		}
		$validation = app('validator')->make($payment_arrays, $rules);
		// process the validation
		if ($validation->fails()) {		
			foreach ($validation->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "status" => false, "Message" => $errors, "Error" => trans("messages.Error List")));
		} else {

			$total_amt = $payment_array->total;

			if ($payment_array->coupon_id != 0) {
				$coupon_details = DB::table('coupons')
					->select('coupons.id as coupon_id', 'coupon_type', 'offer_amount', 'coupon_code', 'start_date', 'end_date')
					->leftJoin('coupon_outlet', 'coupon_outlet.coupon_id', '=', 'coupons.id')
					->where('coupons.id', '=', $payment_array->coupon_id)
					->where('coupon_outlet.outlet_id', '=', $payment_array->outlet_id)
					->first();
				if (count($coupon_details) == 0) {
					$result = array("response" => array("httpCode" => 400, "Message" => "No coupons found"));
					return json_encode($result);
				} else if ((strtotime($coupon_details->start_date) <= $current_date) && (strtotime($coupon_details->end_date) >= $current_date)) {
					$coupon_user_limit_details = DB::table('user_cart_limit')
						->select('cus_order_count', 'user_limit', 'total_order_count', 'coupon_limit')
						->where('customer_id', '=', $post_data['user_id'])
						->where('coupon_id', '=', $payment_array->coupon_id)
						->first();
					if (count($coupon_user_limit_details) > 0) {
						if ($coupon_user_limit_details->cus_order_count >= $coupon_user_limit_details->user_limit) {
							$result = array("response" => array("httpCode" => 400, "Message" => "Max user limit has been crossed"));
							return json_encode($result);
						}
						if ($coupon_user_limit_details->total_order_count >= $coupon_user_limit_details->coupon_limit) {
							$result = array("response" => array("httpCode" => 400, "Message" => "Max coupon limit has been crossed"));
							return json_encode($result);
						}
					}
				} else {
					$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.No coupons found")));
					return json_encode($result);
				}
				//$total_amt = $payment_array->total - $payment_array->coupon_amount;
			}
			//echo"<pre>";print_r($payment_array);exit;
			//echo 'update users set current_balance = '.$payment_array->admin_commission.' where id = 1';exit;
			$order_id = DB::table('orders')->insertGetId(
				[
					'order_key' => str_random(32),
					'customer_id' => $payment_array->user_id,
					'vendor_id' => $payment_array->store_id,
					//'vendor_name' => $payment_array->vendor_name,
					'total_amount' => $total_amt,
					'created_date' => date("Y-m-d H:i:s"),
					'order_status' => $payment_array->order_status,
					'coupon_id' => (integer)$payment_array->coupon_id,
					'coupon_amount' => (double)$payment_array->coupon_amount,
					'coupon_type' => $payment_array->coupon_type,
					'service_tax' => (double)$payment_array->tax_amount,
					'payment_status' => $payment_array->payment_status,
					//'invoice_id' => str_random(32),
					'payment_gateway_commission' =>  (double)$payment_array->payment_gateway_commission,
					'outlet_id' => $payment_array->outlet_id,
					'delivery_instructions' => $payment_array->delivery_instructions,
					'delivery_address' => isset($payment_array->delivery_address) ? $payment_array->delivery_address : '',
					'payment_gateway_id' => $payment_array->payment_gateway_id,
					'delivery_slot' => isset($payment_array->delivery_slot) ? $payment_array->delivery_slot : '',
					'delivery_date' => $payment_array->delivery_date,
					'delivery_charge' => isset($payment_array->delivery_cost) ? (double)$payment_array->delivery_cost : '',
					'admin_commission' => $payment_array->admin_commission,
					'vendor_commission' => $payment_array->vendor_commission,
					'order_type' => $payment_array->order_type,
					//'vendor_key' => $payment_array->vendor_key
				]
			);
			//print("arg");exit;
			$update_orders = Orders::find($order_id);
			$update_orders->invoice_id = 'INV' . str_pad($order_id, 8, "0", STR_PAD_LEFT) . time();
			$update_orders->save();

			$order_key_formatted = "#OR".$payment_array->vendor_key.$order_id;

			DB::update('update orders set order_key_formated = ? where id = ?', array($order_key_formatted, $order_id));
			DB::update('update users set current_balance = current_balance+? where id = ?', array($payment_array->admin_commission, 1));
			DB::update('update vendors set current_balance = current_balance+? where id = ?', array($payment_array->vendor_commission, $payment_array->store_id));

			$items = $payment_array->items;
			foreach ($items as $item) {
				$values = array('item_id' => $item->product_id, 'item_cost' => $item->discount_price, 'item_unit' => $item->quantity, 'item_offer' => $item->item_offer, 'order_id' => $order_id, 'adjust_weight_qty' => $item->adjust_weight_qty);
				DB::table('orders_info')->insert($values);
			}		

			$values = array('order_id' => $order_id,
				'customer_id' => $payment_array->user_id,
				'vendor_id' => $payment_array->store_id,
				'outlet_id' => $payment_array->outlet_id,
				'payment_status' => "SUCCESS",
				'payment_type' => "COD",
				'created_date' => date("Y-m-d H:i:s"),
				'currency_code' => $payment_array->currency_code);
			DB::table('transaction')->insert($values);
			// for delete old order details
			DB::update('delete from orders where id = ?', array($payment_array->current_order_id));
			DB::update('delete from orders_info where order_id = ?', array($payment_array->current_order_id));
			DB::update('delete from transaction where order_id = ?', array($payment_array->current_order_id));

			// for delete old order details

			$result = array("response" => array("httpCode" => 400, "Message" => "Something went wrong"));
			if ($values) {
				$result = array("response" => array("httpCode" => 200, "Message" => "Order initated success", "order_id" => $order_id));
				//Email notification to customer
				order::send_order_email($order_id, $payment_array->user_id, $post_data['language']);
				//Email notification to admin & vendor
				order::send_order_email_admin_vendors($order_id, $payment_array->user_id, $post_data['language']);
				$users = Users::find($payment_array->user_id);
				//echo "<pre>";print_r($users->mobile);exit;
				$order_title = 'Your order ' . $order_key_formatted . '  has been placed';
				$subject = 'Your order ' . $order_key_formatted . '  has been placed';
				$vendors = Vendors::find($payment_array->store_id);
				if (!empty($vendors->android_device_token)) {

					$optionBuiler = new OptionsBuilder();
					$optionBuiler->setTimeToLive(60 * 20);
					$notificationBuilder = new PayloadNotificationBuilder($subject);
					$notificationBuilder->setBody($subject)->setSound('default')->setBadge(1)->setClickAction('com.app.JeebelyVendor.Notifications');
					$dataBuilder = new PayloadDataBuilder();
					$dataBuilder->addData(['order_id' => $order_id, "message" => $subject]);
					$option = $optionBuiler->build();
					$notification = $notificationBuilder->build();
					$data = $dataBuilder->build();
					$token = $vendors->android_device_token;
					$downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
					$downstreamResponse->numberSuccess();
					$downstreamResponse->numberFailure();
					$downstreamResponse->numberModification();
					$downstreamResponse->tokensToDelete();
					$downstreamResponse->tokensToModify();
					$downstreamResponse->tokensToRetry();
				}
				if ($users->android_device_token != '') {

					$new_content = strip_tags($order_title);
					$optionBuiler = new OptionsBuilder();
					$optionBuiler->setTimeToLive(60 * 20);
					$notificationBuilder = new PayloadNotificationBuilder($order_title);
					$notificationBuilder->setBody($order_title)
						->setSound('default')
					/* ->setClickAction('
   		 			com.app.jeebelycustomerapp.Activites.NotificationsActivity')*/
						->setBadge(1);
					$dataBuilder = new PayloadDataBuilder();
					$dataBuilder->addData(['additional_params' => $order_title, "message" => $new_content, "title" => $order_title]);
					$option = $optionBuiler->build();
					$notification = $notificationBuilder->build();
					$data = $dataBuilder->build();
					$token = $users->android_device_token;
					$downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
					$downstreamResponse->numberSuccess(); //print_r($downstreamResponse);exit;
					if ($downstreamResponse->numberSuccess() && $downstreamResponse->numberSuccess() == 1) {
					}

					$downstreamResponse->numberFailure();
					$downstreamResponse->numberModification();
					$downstreamResponse->tokensToDelete();
					$downstreamResponse->tokensToModify();
					$downstreamResponse->tokensToRetry();
				}

				if ($users->ios_device_token != '') {

					$new_content = strip_tags($order_title);
					$optionBuiler = new OptionsBuilder();
					$optionBuiler->setTimeToLive(60 * 20);
					$notificationBuilder = new PayloadNotificationBuilder($order_title);
					$notificationBuilder->setBody($order_title)
						->setSound('default')
						->setClickAction('com.app.jeebelycustomerapp.Activites.NotificationsActivity')
						->setBadge(1);
					$dataBuilder = new PayloadDataBuilder();
					$dataBuilder->addData(['additional_params' => $order_title, "message" => $new_content, "title" => $order_title]);
					$option = $optionBuiler->build();
					$notification = $notificationBuilder->build();
					$data = $dataBuilder->build();
					$token = $users->ios_device_token;
					$downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
					$downstreamResponse->numberSuccess();
					if ($downstreamResponse->numberSuccess() && $downstreamResponse->numberSuccess() == 1) {
					}
					$downstreamResponse->numberFailure();
					$downstreamResponse->numberModification();
					$downstreamResponse->tokensToDelete();
					$downstreamResponse->tokensToModify();
					$downstreamResponse->tokensToRetry();
				}

				$message = 'Your order has been placed in  ' . getAppConfig()->site_name . ' Order reference:     ' . $order_key_formatted;
				$twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
				$twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
				$from_number = "+14783471785";
				$client = new Services_Twilio($twilo_sid, $twilio_token);
				// print_r ($client);exit;
				// Create an authenticated client for the Twilio API
				try {
					$m = $client->account->messages->sendMessage(
						$from_number, // the text will be sent from your Twilio number
						$users->mobile, // the phone number the text will be sent to
						$message // the body of the text message
					);

					//echo "<pre>";print_r($m);exit;
				} catch (Exception $e) {
					$result11 = array("response" => array("httpCode" => 400, "Message" => $e->getMessage()));

				} catch (\Services_Twilio_RestException $e) {
					$result1 = array("response" => array("httpCode" => 400, "Message" => $e->getMessage()));

				}

				//Internal Admin Notifications Storing with notifications
				$mess = "New Order Was Placed at " . $payment_array->vendor_name;
				$values = array('order_id' => $order_id,
					'customer_id' => $payment_array->user_id,
					'vendor_id' => $payment_array->store_id,
					'outlet_id' => $payment_array->outlet_id,
					'message' => $mess,
					'read_status' => 0,
					'created_date' => date('Y-m-d H:i:s'));
				DB::table('notifications')->insert($values);
			}
		}
		return json_encode($result);
	}
	public static function send_order_email($id, $uid, $language) {
		$order_id = $id;
		$user_id = $uid;
		$language = $language;
		$user_array = array("user_id" => $user_id, "language" => $language, "order_id" => $order_id);
		$response = order::get_order_detail($user_array);
		$order_detail = $response["order_items"];
		$delivery_details = $response["delivery_details"];
		$vendor_info = $response["vendor_info"];
		$logo = url('/assets/front/' . Session::get("general")->theme . '/images/' . Session::get("general")->theme . '.png');
		$delivery_date = date("d F, l", strtotime($delivery_details[0]->delivery_date));
		$delivery_time = date('g:i a', strtotime($delivery_details[0]->start_time)) . '-' . date('g:i a', strtotime($delivery_details[0]->end_time));
		$sub_total = 0;
		$item = '';
		$site_name = Session::get("general")->site_name;
		$currency_side = getCurrencyPosition()->currency_side;
		$currency_symbol = getCurrency($language);
		foreach ($order_detail as $items) {
			if ($currency_side == 1) {
				$item_cost = $currency_symbol . $items->item_cost;
				$unit_cost = $currency_symbol . ($items->item_cost * $items->item_unit);
			} else {
				$item_cost = $items->item_cost . $currency_symbol;
				$unit_cost = ($items->item_cost * $items->item_unit) . $currency_symbol;
			}
			$item .= '<tr><td align="center" style="font-size:15px;padding:10px 0; font-family:dejavu sans,arial; font-weight:normal; border-bottom:1px solid #ccc;">' . wordwrap(ucfirst(strtolower($items->product_name)), 40, "<br>\n") . '</td><td align="center" style="font-size:15px;padding:10px 0;border-bottom:1px solid #ccc; font-family:dejavu sans,arial; font-weight:normal;">' . wordwrap(ucfirst(strtolower($items->description)), 40, "<br>\n") . '</td><td align="center" style="font-size:15px;padding:10px 0;border-bottom:1px solid #ccc; font-family:dejavu sans,arial; font-weight:normal;">' . $items->item_unit . '</td><td align="center" style="font-size:15px;padding:10px 0;border-bottom:1px solid #ccc; font-family:dejavu sans,arial; font-weight:normal;">' . $item_cost . '</td><td align="center" style="font-size:15px;padding:10px 0;border-bottom:1px solid #ccc; font-family:dejavu sans,arial; font-weight:normal;">' . $unit_cost . '</td></tr>';
			$sub_total += $items->item_cost * $items->item_unit;
		}
		if ($currency_side == 1) {
			$delivery_charge = $currency_symbol . '0';
		} else {
			$delivery_charge = '0' . $currency_symbol;
		}
		if ($delivery_details[0]->order_type == 1) {

			if ($currency_side == 1) {
				$delivery_charge = $currency_symbol . $delivery_details[0]->delivery_charge;
			} else {
				$delivery_charge = $delivery_details[0]->delivery_charge . $currency_symbol;
			}
		}
		if ($currency_side == 1) {
			$total_amount = $currency_symbol . $delivery_details[0]->total_amount;
			$sub_total = $currency_symbol . $sub_total;
			$service_tax = $currency_symbol . $delivery_details[0]->service_tax;
		} else {
			$total_amount = $delivery_details[0]->total_amount . $currency_symbol;
			$sub_total = $sub_total . $currency_symbol;
			$service_tax = $delivery_details[0]->service_tax . $currency_symbol;
		}
		$delivery_email = $delivery_details[0]->email;
		$delivery_address = ($delivery_details[0]->contact_address != '') ? ucfirst($delivery_details[0]->contact_address) : '-';
		if ($delivery_details[0]->order_type == 1) {
			$delivery_type = 'DELIVERY ADDRESS :';
			$delivery_address = ($delivery_details[0]->user_contact_address != '') ? ucfirst($delivery_details[0]->user_contact_address) : '-';
		} else {
			$delivery_type = 'PICKUP ADDRESS :';
			$delivery_address = ($delivery_details[0]->contact_address != '') ? ucfirst($delivery_details[0]->contact_address) : '-';
		}
		$site_name = Session::get("general")->site_name;
		$html = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><table width="700px" cellspacing="0" cellpadding="0" bgcolor="#fff" style="border:1px solid #ccc;">
        <tbody>
        <tr>
        <td style="border-bottom:1px solid #ccc;">
        <table style="padding-top: 25px; padding-bottom: 25px;" width="700px" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td width="20">&nbsp;</td>
        <td>
        <table>
        <tr>
        <td style="font-size:16px; font-weight:bold; font-family:Verdana; color:#000; padding-bottom:10px;">BILL FORM :</td>
        </tr>
        <tr>
        <td style="font-size:16px; font-weight:500; font-family:dejavu sans,arial; color:#666; line-height:28px;">' . ucfirst($vendor_info[0]->vendor_name) . ',' . wordwrap(ucfirst($vendor_info[0]->contact_address), 70, "<br>\n") . '<br/>' . ucfirst($vendor_info[0]->contact_email) . '</td>
        </tr>
        </table>
        </td>
        <td align="right"><a title="' . $site_name . '" href="' . url('/') . '"><img src="' . $logo . '" alt="' . $site_name . '" /></a></td>
        <td width="20">&nbsp;</td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        <!-- end 1 tr -->
        <tr>
        <td>
        <table style="padding-top: 25px; padding-bottom: 25px;" width="700px" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td width="20">&nbsp;</td>
        <td colspan="4">
        <table>
        <tr>
        <td style="font-size:16px; font-weight:bold; font-family:Verdana; color:#000; padding-bottom:10px;">' . $delivery_type . '</td>
        </tr>
        <tr>
        <td style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;">' . wordwrap($delivery_address, 70, "<br>\n") . '
        <br/>' . $delivery_email . '</td>
        </tr>
        </table>
        </td>
        <td align="right">
        <table cellpadding="0" cellspacing="0">
        <tr>
        <td style="font-size:15px; font-weight:bold; font-family:Verdana; color:#000; line-height:28px;">Invoice</td>
        <td></td>
        <td align="left" style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;">' . $vendor_info[0]->invoice_id . '</td>
        </tr>
        <tr>
        <td style="font-size:15px; font-weight:bold; font-family:Verdana; color:#000; line-height:28px;">Delivery date</td>
        <td></td>
        <td align="left" style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;">' . date('F d, Y', strtotime($delivery_details[0]->delivery_date)) . '</td>
        </tr>
        <tr>
        <td style="font-size:15px; font-weight:bold; font-family:Verdana; color:#000; line-height:28px;">Invoice date</td>
        <td></td>
        <td align="left" style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;">' . date('F d, Y', strtotime($vendor_info[0]->created_date)) . '</td>
        </tr>
        <tr>
        <td style="font-size:11px; font-weight:bold; font-family:Verdana; color:#000; line-height:28px; background:#d1d5d4; padding:0 9px;">AMOUNT DUE</td>
        <td></td>
        <td align="left" style="font-size:16px; font-weight:500; font-family:dejavu sans,arial;  color:#666; line-height:28px;background:#d1d5d4;padding:0 9px;">' . $total_amount . '</td>
        </tr>
        </table>
        </td>
        <td width="20">&nbsp;</td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        <!-- end 2 tr -->
        <tr>
        <td>
        <table cellpadding="0" cellspacing="0" width="100%">
        <tr style="background:#d1d5d4;padding:0 9px;">
        <td align="center" style=" padding:7px 0; font-size:17px; font-family:Verdana; font-weight:bold;">Item</th>
        <td align="center" style=" padding:7px 0;font-size:17px; font-family:Verdana; font-weight:bold;">Description</th>
        <td align="center" style=" padding:7px 0;font-size:17px; font-family:Verdana; font-weight:bold;">Quantity</th>
        <td align="center" style=" padding:7px 0;font-size:17px; font-family:Verdana; font-weight:bold;">Unit cost</th>
        <td align="center" style=" padding:7px 0;font-size:17px; font-family:Verdana; font-weight:bold;">Line total</th>
        </tr>' . $item . '
        </table>
        </td>
        </tr>
        <!-- end 3 tr -->
        <tr>
        <td>
        <table style="padding-top: 25px; padding-bottom: 25px;" width="787" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td width="20">&nbsp;</td>
        <td>
        <table>
        <tbody><tr>
        <td style="font-size:16px; font-weight:bold; font-family:Verdana; color:#000; padding-bottom:10px;">NOTES / MEMO :</td>
        </tr>
        <tr>
        <td style="font-size:16px; font-weight:500; font-family:dejavu sans,arial; color:#666; line-height:28px;">Free shipping with 30-day money-back guarntee </td>
        </tr>
        </tbody></table>
        </td>
        <td align="right">
        <table cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td style="font-size:15px; font-weight:bold; font-family:dejavu sans,arial; color:#000; line-height:28px;">SUBTOTAL</td>
        <td width="10"></td>
        <td style="font-size:16px; font-weight:500; font-family:dejavu sans,arial; color:#666; line-height:28px;" align="right">' . $sub_total . '</td>
        </tr>
        <tr>
        <td style="font-size:15px; font-weight:bold; font-family:dejavu sans,arial; color:#000; line-height:28px;">Delivery fee</td>
        <td width="10"></td>
        <td style="font-size:16px; font-weight:500; font-family:dejavu sans,arial; color:#666; line-height:28px;" align="right">' . $delivery_charge . '</td>
        </tr>
       <tr>
		<td style="font-size:15px; font-weight:bold; font-family:dejavu sans,arial; color:#000; line-height:28px;">Tax </td>
		<td width="10"></td>
		<td style="font-size:16px; font-weight:500; font-family:dejavu sans,arial; color:#666; line-height:28px;" align="right">' . $service_tax . '</td>
		</tr>
        <tr>
        <td style="font-size:15px; font-weight:bold; font-family:dejavu sans,arial; color:#000; line-height:28px; background:#d1d5d4; padding:0 9px;">TOTAL</td>
        <td style="background:#d1d5d4;padding:0 9px;" width="10"></td>
        <td style="font-size:16px; font-weight:500; font-family:dejavu sans,arial; color:#666; line-height:28px;background:#d1d5d4;padding:0 9px;" align="right">' . $total_amount . '</td>
        </tr>
        </tbody></table>
        </td>
        <td width="20">&nbsp;</td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        <tr>
        <td>
        <table>
        <tr>
        <td width="20">&nbsp;</td>
        <td style="font-size:12px; font-family:dejavu sans,arial; color:#666;padding:10px 10px 0 0;direction:rtl; text-alignment:right;"><b style="font-family: dejavu sans,arial; font-weight: bold;">' . trans('messages.Returns Policy: ') . '</b>' . trans('messages.At Oddappz we try to deliver perfectly each and every time. But in the off-chance that you need to return the item, please do so with the') . '<b style="font-family: dejavu sans,arial; font-weight: bold;">' . trans('messages.original Brand') . trans('messages.box/price tag, original packing and invoice') . '</b> ' . trans('messages.without which it will be really difficult for us to act on your request. Please help us in helping you. Terms and conditions apply') . '</td>
        <td width="20">&nbsp;</td>
        </tr>
        </tbody>
        </table>';
	
		$pdf = App::make('dompdf.wrapper');
		$pdf->loadHTML($html)->save(base_path() . '/public/assets/front/' . Session::get("general")->theme . '/images/invoice/' . $vendor_info[0]->invoice_id . '.pdf');
		$attachment[] = base_path() . '/public/assets/front/' . Session::get("general")->theme . '/images/invoice/' . $vendor_info[0]->invoice_id . '.pdf';
		/** Send Email To patient **/
		$users = Users::find($user_id);
		$to = $users->email;
		$subject = 'Order Confirmation - Your Order with ' . getAppConfig()->site_name . ' [' . $vendor_info[0]->invoice_id . '] has been successfully placed!';
		$template = DB::table('email_templates')
			->select('*')
			->where('template_id', '=', self::ORDER_MAIL_TEMPLATE)
			->get();
		if (count($template)) {
			$from = $template[0]->from_email;
			$from_name = $template[0]->from;
			//$subject = $template[0]->subject;
			if (!$template[0]->template_id) {
				$template = 'mail_template';
				$from = getAppConfigEmail()->contact_mail;
				$subject = "Welcome to " . getAppConfig()->site_name;
				$from_name = "";
			}
			$content = array("order" => array('name' => $users->name));
			$email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
			/** SMS **/
			/** $number  = '+918867953038';
			$message = 'Order Confirmation - Your Order with '.getAppConfig()->site_name.' ['.$vendor_info[0]->order_key_formated .'] has been successfully placed!';
			$twilo_sid    = "ACa0946c498b75106632928c87c2a1e70f";
			$twilio_token = "4a43fcf31ea349c14ce6e6255a096ae4";
			$from_number  = "+19729927910";
			$client = new Services_Twilio($twilo_sid, $twilio_token);
			// Create an authenticated client for the Twilio API
			try {
			$m = $client->account->messages->sendMessage(
			$from_number, // the text will be sent from your Twilio number
			$number, // the phone number the text will be sent to
			$message // the body of the text message
			);
			//echo '<pre>';print_r($client);die;
			$users->otp = $otp;
			$users->updated_date = date("Y-m-d H:i:s");
			$users->save();
			$result = array("response" => array("httpCode" => 200,"Message" => "Otp sent"));
			}
			catch(Services_Twilio_RestException $e) {
			$result = array("response" => array("httpCode" => 400,"Message" => "Something went wrong"));
			};**/
			/** SMS **/
			return true;
		}

	}
	public static function send_order_email_admin_vendors($id, $uid, $language) {
		$order_id = $id;
		$user_id = $uid;
		$language = $language;
		$user_array = array("user_id" => $user_id, "language" => $language, "order_id" => $order_id);
		$response = order::get_order_detail($user_array);
		$order_detail = $response["order_items"];
		$delivery_details = $response["delivery_details"];
		$vendor_info = $response["vendor_info"];
		$logo = url('/assets/front/' . Session::get("general")->theme . '/images/' . Session::get("general")->theme . '.png');
		$delivery_date = date("d F, l", strtotime($delivery_details[0]->delivery_date));
		$delivery_time = date('g:i a', strtotime($delivery_details[0]->start_time)) . '-' . date('g:i a', strtotime($delivery_details[0]->end_time));
		$sub_total = 0;
		$item = '';
		$currency_side = getCurrencyPosition()->currency_side;
		$currency_symbol = getCurrency($language);
		foreach ($order_detail as $items) {
			if ($currency_side == 1) {
				$item_cost = $currency_symbol . $items->item_cost;
				$unit_cost = $currency_symbol . ($items->item_cost * $items->item_unit);
			} else {
				$item_cost = $items->item_cost . $currency_symbol;
				$unit_cost = ($items->item_cost * $items->item_unit) . $currency_symbol;
			}
			$item .= '<tr><td align="center" style="font-size:15px;padding:10px 0; font-family:arial; font-weight:normal; border-bottom:1px solid #ccc;">' . wordwrap(ucfirst(strtolower($items->product_name)), 40, "<br>\n") . '</td><td align="center" style="font-size:15px;padding:10px 0;border-bottom:1px solid #ccc; font-family:arial; font-weight:normal;">' . wordwrap(ucfirst(strtolower($items->description)), 40, "<br>\n") . '</td><td align="center" style="font-size:15px;padding:10px 0;border-bottom:1px solid #ccc; font-family:arial; font-weight:normal;">' . $items->item_unit . '</td><td align="center" style="font-size:15px;padding:10px 0;border-bottom:1px solid #ccc; font-family:arial; font-weight:normal;">' . $item_cost . '</td><td align="center" style="font-size:15px;padding:10px 0;border-bottom:1px solid #ccc; font-family:arial; font-weight:normal;">' . $unit_cost . '</td></tr>';
			$sub_total += $items->item_cost * $items->item_unit;
		}
		if ($currency_side == 1) {
			$delivery_charge = $currency_symbol . '0';
			$total_amount = $currency_symbol . $delivery_details[0]->total_amount;
			$sub_total = $currency_symbol . $sub_total;
			$service_tax = $currency_symbol . $delivery_details[0]->service_tax;
		} else {
			$delivery_charge = '0' . $currency_symbol;
			$total_amount = $delivery_details[0]->total_amount . $currency_symbol;
			$sub_total = $sub_total . $currency_symbol;
			$service_tax = $delivery_details[0]->service_tax . $currency_symbol;
		}
		if ($delivery_details[0]->order_type == 1) {
			if ($currency_side == 1) {
				$delivery_charge = $currency_symbol . $delivery_details[0]->delivery_charge;
			} else {
				$delivery_charge = $delivery_details[0]->delivery_charge . $currency_symbol;
			}
		}
		$delivery_email = $delivery_details[0]->email;
		$delivery_address = ($delivery_details[0]->contact_address != '') ? ucfirst($delivery_details[0]->contact_address) : '-';
		if ($delivery_details[0]->order_type == 1) {
			$delivery_type = 'DELIVERY ADDRESS :';
			$delivery_address = ($delivery_details[0]->user_contact_address != '') ? ucfirst($delivery_details[0]->user_contact_address) : '-';
		} else {
			$delivery_type = 'PICKUP ADDRESS :';
			$delivery_address = ($delivery_details[0]->contact_address != '') ? ucfirst($delivery_details[0]->contact_address) : '-';
		}
		$site_name = Session::get("general")->site_name;
		$html = '<table width="700px" cellspacing="0" cellpadding="0" bgcolor="#fff" style="border:1px solid #ccc;">
        <tbody>
        <tr>
        <td style="border-bottom:1px solid #ccc;">
        <table style="padding-top: 25px; padding-bottom: 25px;" width="700px" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td width="20">&nbsp;</td>
        <td>
        <table>
        <tr>
        <td style="font-size:16px; font-weight:bold; font-family:Verdana; color:#000; padding-bottom:10px;">BILL FORM :</td>
        </tr>
        <tr>
        <td style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;">' . ucfirst($vendor_info[0]->vendor_name) . ',' . wordwrap(ucfirst($vendor_info[0]->contact_address), 70, "<br>\n") . '<br/>' . ucfirst($vendor_info[0]->contact_email) . '</td>
        </tr>
        </table>
        </td>
        <td align="right"><a title="' . $site_name . '" href="' . url('/') . '"><img src="' . $logo . '" alt="' . $site_name . '" /></a></td>
        <td width="20">&nbsp;</td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        <!-- end 1 tr -->
        <tr>
        <td>
        <table style="padding-top: 25px; padding-bottom: 25px;" width="700px" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td width="20">&nbsp;</td>
        <td colspan="4">
        <table>
        <tr>
        <td style="font-size:16px; font-weight:bold; font-family:Verdana; color:#000; padding-bottom:10px;">' . $delivery_type . '</td>
        </tr>
        <tr>
        <td style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;">' . wordwrap($delivery_address, 70, "<br>\n") . '
        <br/>' . $delivery_email . '</td>
        </tr>
        </table>
        </td>
        <td align="right">
        <table cellpadding="0" cellspacing="0">
        <tr>
        <td style="font-size:15px; font-weight:bold; font-family:Verdana; color:#000; line-height:28px;">Invoice</td>
        <td></td>
        <td align="left" style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;">' . $vendor_info[0]->invoice_id . '</td>
        </tr>
        <tr>
        <td style="font-size:15px; font-weight:bold; font-family:Verdana; color:#000; line-height:28px;">Delivery date</td>
        <td></td>
        <td align="left" style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;">' . date('F d, Y', strtotime($delivery_details[0]->delivery_date)) . '</td>
        </tr>
        <tr>
        <td style="font-size:15px; font-weight:bold; font-family:Verdana; color:#000; line-height:28px;">Invoice date</td>
        <td></td>
        <td align="left" style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;">' . date('F d, Y', strtotime($vendor_info[0]->created_date)) . '</td>
        </tr>
        <tr>
        <td style="font-size:11px; font-weight:bold; font-family:Verdana; color:#000; line-height:28px; background:#d1d5d4; padding:0 9px;">AMOUNT DUE</td>
        <td></td>
        <td align="left" style="font-size:16px; font-weight:500; font-family:dejavu sans,arial;  color:#666; line-height:28px;background:#d1d5d4;padding:0 9px;">' . $total_amount . '</td>
        </tr>
        </table>
        </td>
        <td width="20">&nbsp;</td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        <!-- end 2 tr -->
        <tr>
        <td>
        <table cellpadding="0" cellspacing="0" width="100%">
        <tr style="background:#d1d5d4;padding:0 9px;">
        <td align="center" style=" padding:7px 0; font-size:17px; font-family:Verdana; font-weight:bold;">Item</th>
        <td align="center" style=" padding:7px 0;font-size:17px; font-family:Verdana; font-weight:bold;">Description</th>
        <td align="center" style=" padding:7px 0;font-size:17px; font-family:Verdana; font-weight:bold;">Quantity</th>
        <td align="center" style=" padding:7px 0;font-size:17px; font-family:Verdana; font-weight:bold;">Unit cost</th>
        <td align="center" style=" padding:7px 0;font-size:17px; font-family:Verdana; font-weight:bold;">Line total</th>
        </tr>' . $item . '
        </table>
        </td>
        </tr>
        <!-- end 3 tr -->
        <tr>
        <td>
        <table style="padding-top: 25px; padding-bottom: 25px;" width="787" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td width="20">&nbsp;</td>
        <td>
        <table>
        <tbody><tr>
        <td style="font-size:16px; font-weight:bold; font-family:Verdana; color:#000; padding-bottom:10px;">NOTES / MEMO :</td>
        </tr>
        <tr>
        <td style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;">Free shipping with 30-day money-back guarntee </td>
        </tr>
        </tbody></table>
        </td>
        <td align="right">
        <table cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td style="font-size:15px; font-weight:bold; font-family:arial; color:#000; line-height:28px;">SUBTOTAL</td>
        <td width="10"></td>
        <td style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;" align="right">' . $sub_total . '</td>
        </tr>
        <tr>
        <td style="font-size:15px; font-weight:bold; font-family:arial; color:#000; line-height:28px;">Delivery fee</td>
        <td width="10"></td>
        <td style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;" align="right">' . $delivery_charge . '</td>
        </tr>
        <tr>
        <td style="font-size:15px; font-weight:bold; font-family:arial; color:#000; line-height:28px;">Tax</td>
        <td width="10"></td>
        <td style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;" align="right">' . $service_tax . '</td>
        </tr>
        <tr>
        <td style="font-size:15px; font-weight:bold; font-family:arial; color:#000; line-height:28px; background:#d1d5d4; padding:0 9px;">TOTAL</td>
        <td style="background:#d1d5d4;padding:0 9px;" width="10"></td>
        <td style="font-size:16px; font-weight:500; font-family:arial; color:#666; line-height:28px;background:#d1d5d4;padding:0 9px;" align="right">' . $total_amount . '</td>
        </tr>
        </tbody></table>
        </td>
        <td width="20">&nbsp;</td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        <tr>
        <td>
        <table>
        <tr>
        <td width="20">&nbsp;</td>
        <td style="font-size:12px; font-family:arial; color:#666;padding:10px 10px 0 0"><b style="font-family: arial; font-weight: bold;">' . trans('messages.Returns Policy: ') . '</b>' . trans('messages.At Tijik we try to deliver perfectly each and every time. But in the off-chance that you need to return the item, please do so with the ') . '<b style="font-family: arial; font-weight: bold;">' . trans('messages.original Brand') . '' . trans('messages.box/price tag, original packing and invoice') . '</b> ' . trans('messages.without which it will be really difficult for us to act on your request. Please help us in helping you. Terms and conditions apply') . '</td>
        <td width="20">&nbsp;</td>
        </tr>
        </tbody>
        </table>';
		
		/** Send Email To vendor here **/
		$vendor_mail = $vendor_info[0]->email;
		$subject = getAppConfig()->site_name . ' New Order Placed & Confirmation - [' . $vendor_info[0]->invoice_id . ']';
		$template = DB::table('email_templates')
			->select('*')
			->where('template_id', '=', self::ORDER_MAIL_VENDOR_TEMPLATE)
			->get();
		if (count($template)) {
			$from = $template[0]->from_email;
			$from_name = $template[0]->from;
			//$subject = $template[0]->subject;
			if (!$template[0]->template_id) {
				$template = 'mail_template';
				$from = getAppConfigEmail()->contact_mail;
				$subject = getAppConfig()->site_name . ' New Order Placed & Confirmation - [' . $vendor_info[0]->invoice_id . ']';
				$from_name = "";
			}
			$content = array("order" => array('name' => $vendor_info[0]->vendor_name, 'data' => $html));
			$email = smtp($from, $from_name, $vendor_mail, $subject, $content, $template);
			$users = Users::find(1);
			$admin_mail = $users->email;
			$content = array("order" => array('name' => $users->name, 'data' => $html));
			$mail = smtp($from, $from_name, $admin_mail, $subject, $content, $template);
			return true;
		}
	}

	public static function get_order_detail($data) {
		$post_data = $data;
		//print_r($data);exit;
		$language_id = $post_data['language'];
		$query3 = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$query4 = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

		$vendor_info = DB::select('SELECT vendors_infos.vendor_name, vendors.logo_image, vendors.contact_address, vendors.contact_email, o.id as order_id,o.created_date,o.order_status,order_status.name,payment_gateways_info.name as payment_gateway_name,o.outlet_id,vendors.id as vendor_id,o.order_key_formated,o.invoice_id, vendors.email FROM orders o
        left join vendors vendors on vendors.id = o.vendor_id
        left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
        left join order_status order_status on order_status.id = o.order_status
        left join payment_gateways payment_gateways on payment_gateways.id = o.payment_gateway_id
        left join payment_gateways_info payment_gateways_info on payment_gateways_info.payment_id = payment_gateways.id
        where ' . $query3 . ' AND ' . $query4 . ' AND o.id = ? AND o.customer_id= ? ORDER BY o.id', array($post_data['order_id'], $post_data['user_id']));

		$query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and p.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$order_items = DB::select('SELECT p.product_image,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.invoice_id,pi.product_name,pi.description,o.coupon_amount
        FROM orders o
        LEFT JOIN orders_info oi ON oi.order_id = o.id
        LEFT JOIN products p ON p.id = oi.item_id
        LEFT JOIN products_infos pi ON pi.id = p.id
        where ' . $query . ' AND o.id = ? AND o.customer_id= ? ORDER BY oi.id', array($post_data['order_id'], $post_data['user_id']));

		$query2 = 'pgi.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and pg.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$query5 = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$delivery_details = DB::select('SELECT o.delivery_instructions,ua.address,pg.id as payment_gateway_id,pgi.name,o.total_amount,o.delivery_charge,o.service_tax,dti.start_time,end_time,o.created_date,o.delivery_date,o.order_type,out_infos.contact_address,o.coupon_amount,ua.address as user_contact_address, u.email FROM orders o
                    LEFT JOIN user_address ua ON ua.id = o.delivery_address
                    LEFT JOIN users u ON u.id = ua.user_id
                    left join payment_gateways pg on pg.id = o.payment_gateway_id
                    left join payment_gateways_info pgi on pgi.payment_id = pg.id
                    left join delivery_time_slots dts on dts.id=o.delivery_slot
                    left join delivery_time_interval dti on dti.id = dts.time_interval_id
                    left join outlets out on out.id = o.outlet_id
                    left join outlet_infos out_infos on out_infos.id = out.id
                    where ' . $query2 . ' AND ' . $query5 . 'AND o.id = ? AND o.customer_id= ?', array($post_data['order_id'], $post_data['user_id']));
		$result = array("order_items" => array(), "delivery_details" => array(), "vendor_info" => array());
		if (count($order_items) > 0 && count($delivery_details) > 0 && count($vendor_info) > 0) {
			$result = array("order_items" => $order_items, "delivery_details" => $delivery_details, "vendor_info" => $vendor_info);
		}
		return $result;
	}


}
