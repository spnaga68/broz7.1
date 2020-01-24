<?php
namespace App\Http\Controllers\Api;
use App;
use App\Http\Controllers\Controller;
use App\Model\cart_info;
use App\Model\cart_model;
use App\Model\order;
use App\Model\orders;
use App\Model\return_orders;
use App\Model\return_orders_log;
use App\Model\return_reasons;
use App\Model\transaction;
use App\Model\users;
use App\Model\Users\addresstype;
use App\Model\vendors;
use DB;
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
//use Services_Twilio;
use Twilio\Rest\Client;

use Session;
use Twilio;
use Tymon\JWTAuth\Exceptions\JWTException;
use URL;

class Ordercheckout extends Controller {
	const USER_SIGNUP_EMAIL_TEMPLATE = 1;
	const USERS_WELCOME_EMAIL_TEMPLATE = 3;
	const USERS_FORGOT_PASSWORD_EMAIL_TEMPLATE = 6;
	const USER_CHANGE_PASSWORD_EMAIL_TEMPLATE = 13;
	const OTP_EMAIL_TEMPLATE = 14;
	const ORDER_MAIL_TEMPLATE = 5;
	const ORDER_MAIL_VENDOR_TEMPLATE = 16;
	const RETURN_STATUS_CUSTOMER_EMAIL_TEMPLATE = 17;
	const ORDER_STATUS_UPDATE_USER = 18;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	private $_apiContext;
	public function __construct(Request $data) {
		$post_data = $data->all();
		if (isset($post_data['language']) && $post_data['language'] != '' && $post_data['language'] == 2) {
			App::setLocale('ar');
		} else {
			App::setLocale('en');
		}
		$this->_apiContext = PayPal::ApiContext(getAppPaymentConfig()->merchant_key, getAppPaymentConfig()->merchant_secret_key);
		$this->_apiContext->setConfig(array(
			'mode' => 'sandbox',
			'service.EndPoint' => 'https://api.sandbox.paypal.com',
			'http.ConnectionTimeOut' => 30,
			'log.LogEnabled' => true,
			'log.FileName' => storage_path('logs/paypal.log'),
			'log.LogLevel' => 'FINE',
		));
	}

	/*
		     * order detail
	*/
	public function index(Request $data) {

		$post_data = $data->all();
		$cart_items = $this->calculate_cart($post_data['language'], $post_data['user_id']);
		$address_list = $this->get_address($post_data['language'], $post_data['user_id']);
		$gateway_list = $this->get_payment_gateways($post_data['language']);
		$delivery_slots = $this->get_delivery_slots();
		$delivery_settings = $this->get_delivery_settings();
		$time_interval = $this->get_delivery_time_interval();
		$avaliable_slot_mob = $this->get_avaliable_slot_mobl();
		$outlet_detail = $this->get_outlet_detail($post_data['language'], $post_data['user_id']);
		$address_type = $this->address_type();
		$date = date('Y-m-d'); //today date
		$weekOfdays = array();
		$weeks = array();
		$uweek = array();
		$deliver_slot_array = array();
		$datetime = new \DateTime();
		//$datetime->modify('+1 day');
		$listItem = array('<li class="active">', '</li>');
		$i = 0;
		$weekarray = array();
		while (true) {
			if ($i === 7) {
				break;
			}

			if ($datetime->format('N') === '7' && $i === 0) {
				$datetime->add(new \DateInterval('P1D'));
				continue;
			}
			$weekarray[] = $datetime->format('N');
			$j = $datetime->format('N');
			$jj = $datetime->format('N');
			$datetime->add(new \DateInterval('P1D'));
			$wk_day = date('N', strtotime($date));
			$weekOfdays[$j] = date('d M', strtotime($date));
			$weekOfdays_mob[] = date('d-m-Y', strtotime($date));
			$weekday = date('l', strtotime($date));
			foreach ($time_interval as $time) {

				$slot_id = $this->check_value_exist($delivery_slots, $time->id, $j, 'time_interval_id', 'day');
				$deliver_slot_array[$weekOfdays[$j]][] = array(
					"time" => date('g:i a', strtotime($time->start_time)) . ' - ' . date('g:i a', strtotime($time->end_time)),
					"slot" => $slot_id,
					"time_interval_id" => $time->id,
					"key" => $weekOfdays[$j],
					"day" => $j,
					"weekday" => $weekday,
					"date" => $date,
				);
			}
			$uj = $j + 1;
			if ($uj == 8) {
				$uweek[1] = date('l', strtotime($date));
			} else {
				$uweek[$uj] = date('l', strtotime($date));
			}

			$weeks[$j] = date('l', strtotime($date));
			$week_mob[] = date('l', strtotime($date));
			$date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
			$i++;
		}

		/** avilable slot  mobile **/
		$date1 = date('Y-m-d');
		$format_slot = array();
		$iii = 0;
		foreach ($uweek as $ukey => $week) {
			foreach ($avaliable_slot_mob as $key => $mobile_slot) {
				if ($mobile_slot->day == $ukey) {

					$mobile_slot->week_mob_time = date('g:i a', strtotime($mobile_slot->start_time)) . ' - ' . date('g:i a', strtotime($mobile_slot->end_time));
					$format_slot[$iii]['week_date'] = date('d-m-Y', strtotime($date1));
					$format_slot[$iii]['day'] = $week;

					/** current day past time slot restriction **/
					$start_time = explode('-', $mobile_slot->week_mob_time);
					$stime = date('H:i:s', strtotime($start_time[0]));
					$etime = date('H:i:s', strtotime($start_time[1]));
					$td = date('d-m-Y', strtotime($date1));
					$today = date('d-m-Y');
					if ((time() >= strtotime($stime)) && (time() >= strtotime($etime)) && ($td == $today)) {
						//$format_slot[$iii]['time'][] = array();
					} else if ((time() > strtotime($stime)) && (time() < strtotime($etime)) && ($td == $today)) {
						$format_slot[$iii]['time'][] = $mobile_slot;
					} else {
						$format_slot[$iii]['time'][] = $mobile_slot;
					}
					/** current day past time slot restriction end **/

					/** current day past time without slot restriction we have to enable this and hide above **/
					//$format_slot[$iii]['time'][] = $mobile_slot;
					/** current day past time without slot restriction we have to enable this and hide above end **/

				} else {
					$format_slot[$iii]['day'] = $week;
					$format_slot[$iii]['week_date'] = date('d-m-Y', strtotime($date1));
				}

			}
			$date1 = date('d-m-Y', strtotime('+1 day', strtotime($date1)));
			$iii++;
		}

		/** avilable slot  mobile **/

		/** avilable slot updated mobile **/
		$avilable_slot_updated = array();
		if (count($format_slot) > 0) {
			foreach ($format_slot as $akey => $avilbale) {

				$avilable_slot_updated[$akey]['day'] = $avilbale['day'];
				$avilable_slot_updated[$akey]['week_date'] = $avilbale['week_date'];

				if (isset($avilbale['time']) && $avilbale['time'] != '') {
					foreach ($avilbale['time'] as $tkey => $utime) {
						$day_of_week = date('N', strtotime($avilbale['day']));
						$slot_class = 1;
						$avilable_slot_updated[$akey]['time'][$tkey]['status'] = $slot_class;
						$avilable_slot_updated[$akey]['time'][$tkey]['week_mob_time'] = $utime->week_mob_time;
						$avilable_slot_updated[$akey]['time'][$tkey]['slot_id'] = $utime->slot_id;
						$avilable_slot_updated[$akey]['time'][$tkey]['time_interval_id'] = $utime->time_interval_id;
					}
				} else {
					$avilable_slot_updated[$akey]['time'] = array();

				}
			}
		}
		/** avilable slot updated mobile end **/
		$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.No cart items found"), "cart_items" => array()));
		if (count($cart_items['cart_items']) > 0) {
			$result = array("response" => array("httpCode" => 200, "Message" => "Cart details", "cart_items" => $cart_items['cart_items'], "total" => $cart_items['total'], "sub_total" => $cart_items['sub_total'], "tax" => $cart_items['tax'], "tax_amount" => $cart_items['tax_amount'], "delivery_cost" => $cart_items['delivery_cost'], "address_list" => $address_list, "gateway_list" => $gateway_list, 'delivery_slots' => $delivery_slots, 'time_interval' => $time_interval, 'delivery_slot_array' => $deliver_slot_array, 'weekOfdays' => $weekOfdays, 'weekOfdays_mob' => $weekOfdays_mob, 'week_mob' => $week_mob, 'week' => $weeks, 'outlet_detail' => $outlet_detail, 'address_type' => $address_type, 'delivery_settings' => $delivery_settings, 'avaliable_slot_mob' => $avilable_slot_updated));
		}
		return json_encode($result);
	}

	public function get_avaliable_slot_mobl() {
		$available_slots = DB::select('SELECT dts.day,dti.start_time,dti.end_time,dts.id AS slot_id,dts.time_interval_id
        FROM delivery_time_slots dts
        LEFT JOIN delivery_time_interval dti ON dti.id = dts.time_interval_id');
		return $available_slots;
	}

	public function get_delivery_settings() {
		$delivery_settings = DB::table('delivery_settings')
			->first();
		return $delivery_settings;
	}
	public function address_type($language_id = '') {
		if ($language_id == '') {
			$language_id = getAdminCurrentLang();
		}

		$query = '"address_infos"."language_id" = (case when (select count(*) as totalcount from address_infos where address_infos.language_id = ' . $language_id . ' and address_type.id = address_infos.address_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$address_type = Addresstype::Leftjoin('address_infos', 'address_infos.address_id', '=', 'address_type.id')
			->select('address_type.*', 'address_infos.*')
			->whereRaw($query)
			->where('active_status', 1)
			->orderBy('id', 'desc')
			->get();

		return $address_type;
	}

	/** public function check_value_exist($delivery_slots,$interval_id,$day,$key1,$key2)
	{
	foreach ($delivery_slots as $slots)
	{
	if (is_array($slots) && check_value_exist($delivery_slots, $interval_id,$day,$key1,$key2)) return $slots->id;
	if (isset($slots->$key1) && $slots->$key1 == $interval_id && isset($slots->$key2) &&$slots->$key2 == $day) return $slots->id;
	}
	return 0;
	}**/

	public function check_value_exist($delivery_slots, $interval_id, $day, $key1, $key2) {
		$di = $day + 1;
		if ($di == 8) {
			$di = 1;
		}
		foreach ($delivery_slots as $slots) {
			if (is_array($slots) && check_value_exist($delivery_slots, $interval_id, $di, $key1, $key2)) {
				return $slots->id;
			}

			if (isset($slots->$key1) && $slots->$key1 == $interval_id && isset($slots->$key2) && $slots->$key2 == $di) {
				return $slots->id;
			}

		}
		return 0;
	}

	public function get_outlet_detail($language, $user_id) {
		$query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';

		$outlet_detail = DB::table('cart')
			->select('cart.outlet_id', 'vendors.id as vendor_id', 'outlet_infos.contact_address', 'outlets.latitude', 'outlets.longitude', 'outlets.delivery_time')
			->join('outlets', 'cart.outlet_id', '=', 'outlets.id')
			->join('outlet_infos', 'outlets.id', '=', 'outlet_infos.id')
			->join('vendors', 'vendors.id', '=', 'outlets.vendor_id')
			->where('cart.user_id', "=", $user_id)
			->whereRaw($query1)
			->first();
		//echo $outlet_detail->outlet_id;
		/*$outlet_slot_detail = DB::table('delivery_timings')
	                            ->select('vendor_id','day_week','day_week','start_time','end_time')
	                             ->where('vendor_id',"=",$outlet_detail->outlet_id)
		*/
		//$outlet_info["address"] = $outlet_detail;
		//$outlet_info["slots"] = $outlet_slot_detail;
		//print_r($outlet_slot_detail);exit;
		return $outlet_detail;
	}
	public function get_delivery_slots() {
		$delivery_slots = DB::table('delivery_time_slots')
			->select('*')
			->get();
		return $delivery_slots;
	}

	public function get_delivery_time_interval() {
		$time_interval = DB::table('delivery_time_interval')
			->select('*')
			->orderBy('start_time', 'asc')
			->get();
		return $time_interval;
	}
	public function calculate_cart($language, $user_id) {
		$cart_data = cart_model::cart_items($language, $user_id);
		$delivery_settings = $this->get_delivery_settings();
		$sub_total = 0;
		$tax = 0;
		$delivery_cost = 0;
		$tax_amount = 0;
		foreach ($cart_data as $key => $items) {
			$sub_total += $items->quantity * $items->discount_price;
			$tax += $items->service_tax;
		/*	$product_image = URL::asset('assets/front/' . Session::get('general')->theme . '/images/no_image.png');
			if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $items->product_image) && $items->product_image != '') {
				$product_image = url('/assets/admin/base/images/products/list/' . $items->product_image);
			}
			$cart_data[$key]->product_image = $product_image;*/

			$no_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
			$path = url('/assets/admin/base/images/products/admin_products/');
            $productImage=json_decode($items->product_image);
          
            $image1 =$image2=$image3 =array();
            $image1[]= $no_image;

            if($productImage != "")
            {           

               foreach ($productImage as $keys => $valuess) {
                	if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $valuess) && $valuess != '') {
                        $image1[] =$path.'/'.$valuess;
                    }
                }
            }
            $cart_data[$key]->product_image = $image1;
		}
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

		return array("cart_items" => $cart_data, "total" => $total, "sub_total" => $sub_total, "tax" => $tax, "delivery_cost" => $delivery_cost, "tax_amount" => $tax_amount);
	}

	public function get_address($language_id, $user_id) {

		$query = '"address_infos"."language_id" = (case when (select count(*) as totalcount from address_infos where address_infos.language_id = ' . $language_id . ' and address_type.id = address_infos.address_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$address = DB::table('user_address')
			->select('*', 'user_address.id as address_id', 'address_infos.name as address_type')
			->where('user_id', '=', $user_id)
			->whereRaw($query)
			->leftJoin('address_type', 'address_type.id', '=', 'user_address.address_type')
			->leftJoin('address_infos', 'address_infos.address_id', '=', 'address_type.id')
			->orderBy('user_address.id', 'desc')
			->get();
		if (count($address) > 0) {
			foreach ($address as $key => $val) {
				$address[$key]->city_id = ($val->city_id != '') ? $val->city_id : '';
				$address[$key]->country_id = ($val->country_id != '') ? $val->country_id : '';
				$address[$key]->postal_code = ($val->postal_code != '') ? $val->postal_code : '';
			}
		}
		return $address;
	}

	public function get_payment_gateways($language_id) {
		//echo $language_id;
		$query = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$gateways = DB::table('payment_gateways')
			->select('*', 'payment_gateways.id as payment_gateway_id')
			->leftJoin('payment_gateways_info', 'payment_gateways_info.payment_id', '=', 'payment_gateways.id')
			->orderBy('payment_gateways.id', 'desc')
			->where('active_status', "=", 1)
			->whereRaw($query)
			->get();
		//print_r($gateways);exit;
		return $gateways;
	}

	public function get_payment_gateway($payment_gateway_id, $language_id) {
		$query = '"payment_gateways_info"."language_id" = (case when (select count(payment_gateways_info.language_id) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$gateways = DB::table('payment_gateways')
			->select('payment_gateways.id', 'payment_gateways.payment_type', 'payment_gateways.merchant_key', 'payment_gateways.account_id', 'payment_gateways.payment_mode', 'payment_gateways.commision', 'payment_gateways_info.name', 'payment_gateways.id as payment_gateway_id', 'currencies.currency_code')
			->leftJoin('payment_gateways_info', 'payment_gateways_info.payment_id', '=', 'payment_gateways.id')
			->leftJoin('currencies', 'currencies.id', '=', 'payment_gateways.currency_id')
			->orderBy('payment_gateways.id', 'desc')
			->where('payment_gateways.active_status', "=", 1)
			->where('payment_gateways.id', "=", $payment_gateway_id)
			->whereRaw($query)
			->first();
		return $gateways;
	}

	public function get_payment_details(Request $data) {
		$post_data = $data->all();

		if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }	

        $cart_items = $this->calculate_cart($post_data['language'], $post_data['user_id']);
		$payment_gateway_detail = $this->get_payment_gateway($post_data['payment_gateway_id'], $post_data['language']);
		$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.No cart items found"), "cart_items" => array()));
		if (count($cart_items) > 0) {
			$result = array("response" => array("httpCode" => 200, "Message" => "Cart details", "cart_items" => $cart_items['cart_items'], "total" => $cart_items['total'], "sub_total" => $cart_items['sub_total'], "tax" => $cart_items['tax'], "tax_amount" => $cart_items['tax_amount'], "delivery_cost" => $cart_items['delivery_cost'], "payment_gateway_detail" => $payment_gateway_detail));
		}
		return json_encode($result);

	}

	public function offline_payment(Request $data) {

		$post_data = $data->all();
		if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }	
		$current_date = strtotime(date('Y-m-d'));
		$payment_array = json_decode($post_data['payment_array']);
		$payment_arrays = json_decode($post_data['payment_array'], true);
		//print_r( $payment_array);exit;
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
					'coupon_id' => $payment_array->coupon_id,
					'coupon_amount' => $payment_array->coupon_amount,
					'coupon_type' => $payment_array->coupon_type,
					'service_tax' => $payment_array->tax_amount,
					'payment_status' => $payment_array->payment_status,
					//'invoice_id' => str_random(32),
					'payment_gateway_commission' => $payment_array->payment_gateway_commission,
					'outlet_id' => $payment_array->outlet_id,
					'delivery_instructions' => $payment_array->delivery_instructions,
					'delivery_address' => isset($payment_array->delivery_address) ? $payment_array->delivery_address : '',
					'payment_gateway_id' => $payment_array->payment_gateway_id,
					'delivery_slot' => isset($payment_array->delivery_slot) ? $payment_array->delivery_slot : '',
					'delivery_date' => $payment_array->delivery_date,
					'delivery_charge' => isset($payment_array->delivery_cost) ? $payment_array->delivery_cost : '',
					'admin_commission' => $payment_array->admin_commission,
					'vendor_commission' => $payment_array->vendor_commission,
					'order_type' => $payment_array->order_type,
					//'vendor_key' => $payment_array->vendor_key
				]
			);
			$update_orders = Orders::find($order_id);
			$update_orders->invoice_id = 'INV' . str_pad($order_id, 8, "0", STR_PAD_LEFT) . time();
			$update_orders->save();
			$order_key_formatted = "#OR" . $payment_array->vendor_key . $order_id;
			DB::update('update orders set order_key_formated = ? where id = ?', array($order_key_formatted, $order_id));
			DB::update('update users set current_balance = current_balance+? where id = ?', array($payment_array->admin_commission, 1));
			DB::update('update vendors set current_balance = current_balance+? where id = ?', array($payment_array->vendor_commission, $payment_array->store_id));
			$items = $payment_array->items;
			foreach ($items as $item) {
				$values = array('item_id' => $item->product_id, 'item_cost' => $item->discount_price, 'item_unit' => $item->quantity, 'item_offer' => $item->item_offer, 'order_id' => $order_id);
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
			DB::update('delete from cart where user_id = ?', array($payment_array->user_id));
			$result = array("response" => array("httpCode" => 400, "Message" => "Something went wrong"));
			if ($values) {
				$result = array("response" => array("httpCode" => 200, "Message" => "Order initated success", "order_id" => $order_id));
				//Email notification to customer
				$this->send_order_email($order_id, $payment_array->user_id, $post_data['language']);
				//Email notification to admin & vendor
				$this->send_order_email_admin_vendors($order_id, $payment_array->user_id, $post_data['language']);
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
						->setClickAction('
    		com.app.jeebelycustomerapp.Activites.NotificationsActivity')
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
				/*$twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
				$twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
				$from_number = "+14783471785";
				$client = new Services_Twilio($twilo_sid, $twilio_token);*/
				$twilo_sid = TWILIO_ACCOUNTSID;
                $twilio_token = TWILIO_AUTHTOKEN;
                $from_number = TWILIO_NUMBER;
                $client = new Client($twilo_sid, $twilio_token);
				// print_r ($client);exit;
				// Create an authenticated client for the Twilio API
				try {
					/*$m = $client->account->messages->sendMessage(
						$from_number, // the text will be sent from your Twilio number
						$users->mobile, // the phone number the text will be sent to
						$message // the body of the text message
					);*/
					                $m =  $client->messages->create($number, array('from' => $from_number, 'body' => $message));


					//echo "<pre>";print_r($m);exit;
				} catch (Exception $e) {
					$result11 = array("response" => array("httpCode" => 400, "Message" => $e->getMessage()));

				} catch (\Services_Twilio_RestException $e) {
					$result1 = array("response" => array("httpCode" => 400, "Message" => $e->getMessage()));

				}

				/* //Mobile Android & iOS Push notifications
					                $order_title = "Order initated success";
					                $notification_message = PushNotification::Message('Your order has been placed',array(
					                    'badge' => 1,
					                    'sound' => 'example.aiff',
					                    'actionLocKey' => 'New Order Placed!',
					                    //'launchImage' => base_path().'/assets/admin/base/images/offers/'.$offer_image,
					                    'id' => $order_id,
					                    'title' => $order_title,
					                    'custom' => array('id' => $order_id,'title' =>$order_title)
					                ));
					                //Get user details if user using mobile devices or not
					                $user_data = Users::find($payment_array->user_id);
					                //Send notifications to Android User
					                if(!empty($user_data->login_type) && $user_data->login_type==2){
					                    $android_device_arr[] = PushNotification::Device($user_data->android_device_token);
					                    $android_devices = PushNotification::DeviceCollection($android_device_arr);
					                    //echo '<pre>';print_r($android_devices);print_r($ios_devices);exit;
					                    $collection = PushNotification::app('TijikAndroid')
					                    ->to($android_devices);
					                    //it was need to set 'sslverifypeer' parameter to false
					                    $collection->adapter->setAdapterParameters(['sslverifypeer' => false]);
					                    $collection->send($notification_message);
					                    // get response for each device push
					                    /*foreach ($collection->pushManager as $push) {
					                        $response = $push->getAdapter()->getResponse();

					                       dd($response);
					                    }/
					                }
					                //Send notifications to iOS User
					                if(!empty($user_data->login_type) && $user_data->login_type==3){
					                    $ios_device_arr[] = PushNotification::Device($user_data->ios_device_token);
					                    $ios_devices = PushNotification::DeviceCollection($ios_device_arr);

					                    $ios_collection = PushNotification::app('TijikIOS')
					                        ->to($ios_devices)
					                        ->send($notification_message);
					                    // get response for each device push
					                    /*foreach ($ios_collection->pushManager as $push) {
					                        $response = $push->getAdapter()->getResponse();

					                       dd($response);
					                    }/
				*/
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

	public function online_payment(Request $data) {
		$post_data = $data->all();
		if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }	
		$payment_array = json_decode($post_data['payment_array']);
		$payment_params = json_decode($post_data['payment_params']);
		$total_amt = $payment_array->total;
		//print_r($payment_params);die;
		//echo $total_amt;exit;
		$order_id = DB::table('orders')->insertGetId(
			[
				'order_key' => str_random(32),
				'customer_id' => $payment_array->user_id,
				'vendor_id' => $payment_array->store_id,
				'total_amount' => $total_amt,
				'created_date' => date("Y-m-d H:i:s"),
				'order_status' => $payment_array->order_status,
				'coupon_id' => $payment_array->coupon_id,
				'coupon_amount' => $payment_array->coupon_amount,
				'coupon_type' => $payment_array->coupon_type,
				'service_tax' => $payment_array->tax_amount,
				'payment_status' => $payment_array->payment_status,
				//'invoice_id' => $payment_array->invoice_id,
				'payment_gateway_commission' => $payment_array->payment_gateway_commission,
				'outlet_id' => $payment_array->outlet_id,
				'delivery_instructions' => $payment_array->delivery_instructions,
				'delivery_address' => ($payment_array->delivery_address != '') ? $payment_array->delivery_address : 0,
				'payment_gateway_id' => $payment_array->payment_gateway_id,
				'delivery_slot' => $payment_array->delivery_slot,
				'delivery_date' => $payment_array->delivery_date,
				'delivery_charge' => $payment_array->delivery_cost,
				'admin_commission' => $payment_array->admin_commission,
				'vendor_commission' => $payment_array->vendor_commission,
				'order_type' => $payment_array->order_type,

			]
		);
		$update_orders = Orders::find($order_id);
		$update_orders->invoice_id = 'INV' . str_pad($order_id, 8, "0", STR_PAD_LEFT) . time();
		$update_orders->save();
		$payment_array->invoice_id = $update_orders->invoice_id;
		$order_key_formatted = "#OR" . $payment_array->vendor_key . $order_id;
		DB::update('update orders set order_key_formated = ? where id = ?', array($order_key_formatted, $order_id));
		DB::update('update users set current_balance = current_balance+? where id = ?', array($payment_array->admin_commission, 1));
		DB::update('update vendors set current_balance = current_balance+? where id = ?', array($payment_array->vendor_commission, $payment_array->store_id));
		$items = $payment_array->items;
		foreach ($items as $item) {
			$values = array('item_id' => $item->product_id, 'item_cost' => $item->discount_price, 'item_unit' => $item->quantity, 'item_offer' => $item->item_offer, 'order_id' => $order_id);
			DB::table('orders_info')->insert($values);
		}
		$values = array('order_id' => $order_id,
			'customer_id' => $payment_array->user_id,
			'vendor_id' => $payment_array->store_id,

			'outlet_id' => $payment_array->outlet_id,
			'payment_status' => "SUCCESS",
			'payment_type' => $payment_params->payment_method,
			'payer_id' => $payment_params->payer_id,
			'transaction_id' => $payment_params->payment_id,
			'country_code' => $payment_params->country_code,
			'cart_id' => $payment_params->cart_id,
			'created_date' => date("Y-m-d H:i:s"),
			'currency_code' => $payment_array->currency_code);
		//print_r($values);exit;
		DB::table('transaction')->insert($values);
		DB::update('delete from cart where user_id = ?', array($payment_array->user_id));
		$result = array("response" => array("httpCode" => 400, "Message" => "Something went wrong"));
		if ($values) {
			$result = array("response" => array("httpCode" => 200, "Message" => "Order initated success", "order_id" => $order_id));
			//Email notification to customer
			$this->send_order_email($order_id, $payment_array->user_id, $post_data['language']);
			//Email notification to admin & vendor
			$this->send_order_email_admin_vendors($order_id, $payment_array->user_id, $post_data['language']);
			$users = Users::find($payment_array->user_id);

			$order_title = 'Your order ' . $order_key_formatted . '  has been placed';
			$subject = 'Your order ' . $order_key_formatted . '  has been placed';
			$vendors = Vendors::find($payment_array->store_id);
			/* To send the push notification for Vendor start*/

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
					->setClickAction('com.app.jeebelycustomerapp.Activites.NotificationsActivity')
					->setBadge(1);
				$dataBuilder = new PayloadDataBuilder();
				$dataBuilder->addData(['additional_params' => $order_title, "message" => $new_content, "title" => $order_title]);
				$option = $optionBuiler->build();
				$notification = $notificationBuilder->build();
				$data = $dataBuilder->build();
				$token = $users->android_device_token;
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

			//Internal Admin Notifications Storing with notifications
			$mess = "New Order Was Placed at " . $payment_array->vendor_key;
			$values = array('order_id' => $order_id,
				'customer_id' => $payment_array->user_id,
				'vendor_id' => $payment_array->store_id,
				'outlet_id' => $payment_array->outlet_id,
				'message' => $mess,
				'read_status' => 0,
				'created_date' => date('Y-m-d H:i:s'));
			DB::table('notifications')->insert($values);
		}

		return json_encode($result);
	}

	public function moffline_payment(Request $data) {

		$post_data = $data->all();
		$current_date = strtotime(date('Y-m-d'));
		//naga
		$abc = $post_data['payment_array'];
		$payment_arrayb = json_encode($abc);

		$payment_array = json_decode($payment_arrayb);

		$payment_arrays = json_decode($payment_arrayb, true);
		//print_r( $payment_array);exit;
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
				//print_r("expression");exit();
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


			//echo"<pre>";print_r(gettype($payment_array->order_type));exit(); 
			$delivery_cost = isset($payment_array->delivery_cost)?$payment_array->delivery_cost:0;
			$wallet_amt = isset($payment_array->wallet_amt)?$payment_array->wallet_amt:0;
			//echo 'update users set current_balance = '.$payment_array->admin_commission.' where id = 1';exit;
			$order_id = DB::table('orders')->insertGetId(
				[
					'order_key' => str_random(32),
					'customer_id' => $payment_array->user_id,
					'vendor_id' => $payment_array->store_id,
					'total_amount' => (double)$total_amt,
					'created_date' => date("Y-m-d H:i:s"),
					'order_status' => $payment_array->order_status,
					'coupon_id' => $payment_array->coupon_id,
					'coupon_amount' => (double)$payment_array->coupon_amount,
					'coupon_type' => (string)$payment_array->coupon_type,
					'service_tax' => (double)$payment_array->tax_amount,
					'payment_status' => (string)$payment_array->payment_status,
					//'invoice_id' => str_random(32),
					'payment_gateway_commission' => (double)$payment_array->payment_gateway_commission,
					'outlet_id' => $payment_array->outlet_id,

					'delivery_instructions' => $payment_array->delivery_instructions,
					'delivery_address' => (int)isset($payment_array->delivery_address) ? $payment_array->delivery_address : '',
					'payment_gateway_id' => $payment_array->payment_gateway_id,
					'delivery_slot' =>(int) isset($payment_array->delivery_slot) ? $payment_array->delivery_slot : 0,
					'delivery_date' => $payment_array->delivery_date,
					'delivery_charge' => (double)$delivery_cost,
					'admin_commission' => (double)$payment_array->admin_commission,
					'vendor_commission' => (double)$payment_array->vendor_commission,
					'order_type' => $payment_array->order_type,
					'used_wallet_amount' => (double)$wallet_amt,
					'actual_total_amount' => (double)$payment_array->actual_amount,
					//'vendor_key' => $payment_array->vendor_key
				]
			);
			//print_r("expression");exit;


			$update_orders = Orders::find($order_id);
			$update_orders->invoice_id = 'INV' . str_pad($order_id, 8, "0", STR_PAD_LEFT) . time();
			$update_orders->save();
			$order_key_formatted = "#OR" . $payment_array->vendor_key . $order_id;
			DB::update('update orders set order_key_formated = ? where id = ?', array($order_key_formatted, $order_id));
			DB::update('update users set current_balance = current_balance+? where id = ?', array($payment_array->admin_commission, 1));
			DB::update('update vendors set current_balance = current_balance+? where id = ?', array($payment_array->vendor_commission, $payment_array->store_id));
			$items = $payment_array->items;
			foreach ($items as $item) {
				$values = array('item_id' => $item->product_id, 'item_cost' => $item->discount_price, 'item_unit' => $item->quantity, 'item_offer' => $item->item_offer, 'order_id' => $order_id);
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
			DB::update('delete from cart where user_id = ?', array($payment_array->user_id));
			$result = array("response" => array("httpCode" => 400, "Message" => "Something went wrong"));
			if ($values) {
				$result = array("response" => array("httpCode" => 200, "Message" => "Order initated success", "order_id" => $order_id));
				//Email notification to customer
				//  $this->send_order_email($order_id,$payment_array->user_id,$post_data['language']);
				//Email notification to admin & vendor
				//   $this->send_order_email_admin_vendors($order_id,$payment_array->user_id,$post_data['language']);
				$users = Users::find($payment_array->user_id);

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
					/* ->setClickAction('    com.app.jeebelycustomerapp.Activites.NotificationsActivity')*/
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

				/* //Mobile Android & iOS Push notifications
					                $order_title = "Order initated success";
					                $notification_message = PushNotification::Message('Your order has been placed',array(
					                    'badge' => 1,
					                    'sound' => 'example.aiff',
					                    'actionLocKey' => 'New Order Placed!',
					                    //'launchImage' => base_path().'/assets/admin/base/images/offers/'.$offer_image,
					                    'id' => $order_id,
					                    'title' => $order_title,
					                    'custom' => array('id' => $order_id,'title' =>$order_title)
					                ));
					                //Get user details if user using mobile devices or not
					                $user_data = Users::find($payment_array->user_id);
					                //Send notifications to Android User
					                if(!empty($user_data->login_type) && $user_data->login_type==2){
					                    $android_device_arr[] = PushNotification::Device($user_data->android_device_token);
					                    $android_devices = PushNotification::DeviceCollection($android_device_arr);
					                    //echo '<pre>';print_r($android_devices);print_r($ios_devices);exit;
					                    $collection = PushNotification::app('TijikAndroid')
					                    ->to($android_devices);
					                    //it was need to set 'sslverifypeer' parameter to false
					                    $collection->adapter->setAdapterParameters(['sslverifypeer' => false]);
					                    $collection->send($notification_message);
					                    // get response for each device push
					                    /*foreach ($collection->pushManager as $push) {
					                        $response = $push->getAdapter()->getResponse();

					                       dd($response);
					                    }/
					                }
					                //Send notifications to iOS User
					                if(!empty($user_data->login_type) && $user_data->login_type==3){
					                    $ios_device_arr[] = PushNotification::Device($user_data->ios_device_token);
					                    $ios_devices = PushNotification::DeviceCollection($ios_device_arr);

					                    $ios_collection = PushNotification::app('TijikIOS')
					                        ->to($ios_devices)
					                        ->send($notification_message);
					                    // get response for each device push
					                    /*foreach ($ios_collection->pushManager as $push) {
					                        $response = $push->getAdapter()->getResponse();

					                       dd($response);
					                    }/
				*/
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



	           $orderId=$order_id;
	           $users = Users::find($payment_array->user_id);
	           $delivery_address = DB::table('user_address')->select('address')->where('id', '=' ,$payment_array->delivery_address)->get();
	          	// print_r($delivery_address[0]->address);exit;
                   $to = $users->email;
                   $template = DB::table('email_templates')->select('*')->where('template_id', '=', self::ORDER_STATUS_UPDATE_USER)->get();
                   if (count($template)) {
                       $from = $template[0]->from_email;
                       $from_name = $template[0]->from;
                       if (!$template[0]->template_id) {
                           $template = 'mail_template';
                           $from = getAppConfigEmail()->contact_mail;
                       }
                       	$subject = "Your order with ". getAppConfig()->site_name . "[". $order_key_formatted."] has been successfully Placed!";
                     	//$orderId = encrypt($orderId);
						$currency =getCurrencyList();
						$currency_code = isset($currency[0]->currency_code)?$currency[0]->currency_code:'AED';
                       	$logo_image = url('/assets/admin/email_temp/images/1570903488.jpg');
						$gif = url('/assets/admin/email_temp/images/source.gif');
						$image1 = url('/assets/admin/email_temp/images/1.jpg');
						$image2 = url('/assets/admin/email_temp/images/2.jpg');
						$image3 = url('/assets/admin/email_temp/images/3.jpg');
						$order_id = (string)$orderId;
						$created_date = date("Y-m-d H:i:s");
						$shipping_address = isset($delivery_address[0]->address) ? $delivery_address[0]->address : '';
						$service_tax = isset($payment_array->tax_amount)?$payment_array->tax_amount:0;
						$delivery_charge = isset($payment_array->delivery_cost) ? $payment_array->delivery_cost : 0;
						$total_item = count($payment_array->items);
						$sub_total = $total_amt - $service_tax - $delivery_charge;
						$currency_code = $currency_code;
						$total = $total_amt;


						$content = array("logo_image"=>$logo_image,"order_id"=>$order_id,"created_date"=>$created_date,"shipping_address"=>$shipping_address,"total_item"=>(string)$total_item,"sub_total"=>(string)$sub_total,"service_tax"=>(string)$service_tax,"currency_code"=>$currency_code,"delivery_charge"=>(string)$delivery_charge,"total"=>(string)$total,"gif"=>$gif,"image1"=>$image1,"image2"=>$image2,"image3"=>$image3);	
						//print_r($content);exit;			    	



                     /*  $reviwe_id = base64_encode('123abc');
                       $orders_link = '<a href="' . URL::to("order-info/" . $orderId) . '" title="' . trans("messages.View") . '">' . trans("messages.View") . '</a>';
                       $review_link = '<a href="' . URL::to("order-info/" . $orderId . '?r=' . $reviwe_id) . '" title="' . trans("messages.View") . '">' . trans("messages.View") . '</a>';
                       $content = array('name' => "" . $users->name,'order_key' => "" . $order_key_formatted,'status_name' => "Initiated" ,'orders_link' => "" . $orders_link,"review_link" => $review_link);*/
                       $attachment = "";
                       $email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
                   }
	               /*delivery mail for user end*/

			}
		}
		return json_encode($result);
	}

	public function send_order_email($id, $uid, $language) {
		$order_id = $id;
		$user_id = $uid;
		$language = $language;
		$user_array = array("user_id" => $user_id, "language" => $language, "order_id" => $order_id);
		$response = $this->get_order_detail($user_array);
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
		/*$logo = url('/assets/front/'.Session::get("general")->theme.'/images/'.Session::get("general")->theme.'.png');
			        if(file_exists(base_path().'/public/assets/admin/base/images/vendors/list/'.$vendor_info[0]->logo_image)) {
			            $vendor_image ='<img width="100px" height="100px" src="'.URL::to("assets/admin/base/images/vendors/list/".$vendor_info[0]->logo_image).'") >';
			        } else{
			            $vendor_image ='<img width="100px" height="100px" src="'.URL::to("assets/front/'.Session::get('general')->theme.'/images/blog_no_images.png").'") >';
			        }
			        $delivery_date = date("d F, l", strtotime($delivery_details[0]->delivery_date));
			        $delivery_time = date('g:i a', strtotime($delivery_details[0]->start_time)).'-'.date('g:i a', strtotime($delivery_details[0]->end_time));
			        $sub_total = 0;
			        $item = '';
			        foreach($order_detail as $items)
			        {
			            $item .='<tr>
			            <td style="width: 200px; padding: 15px 15px;" valign="middle"><a style="text-decoration: none; font-size: 16px; color: #333; font-family: arial;" title="" href="#"><img width="50px" height="50px" style="vertical-align: middle;" src='.url('/assets/admin/base/images/products/thumb/'.$items->product_image).' alt="del" /></a>
			            <p style="margin: 10px 0 0 0;">'.str_limit(ucfirst(strtolower($items->product_name)),30).'</p>
			            </td>
			            <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 0 15px;" width="100">'.$items->item_cost.getCurrency().'</td>
			            <td style="font-size: 16px; color: #333; font-family: arial; text-align: right;" width="100">'.$items->item_unit.'</td>
			            <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 0 15px;" width="100">'.($items->item_cost*$items->item_unit).getCurrency().'</td>
			            </tr>
			            <tr>
			            <td colspan="5" width="100%">
			            <table border="0" width="100%" cellspacing="0" cellpadding="0">
			            <tbody>
			            <tr>
			            <td style="width: 100%; border-bottom: 1px solid #ccc;">&nbsp;</td>
			            </tr>
			            </tbody>
			            </table>
			            </td>
			            </tr>';
			            $sub_total += $items->item_cost*$items->item_unit;
			        }
			        $delivery_charge ='';
			        if($delivery_details[0]->order_type == 1){
			            $delivery_address ='<tr>
			            <td style="font-size: 16px; color: #333; font-family: arial; text-align: left; padding: 15px 13px;">'.trans('messages.Delivery address').'</td>
			            <td>:</td>
			            <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 15px 13px;">'.$delivery_details[0]->address.'</td>
			            </tr><tr>
			        <td style="font-size: 16px; color: #333; font-family: arial; text-align: left; padding: 15px 13px;">'.trans('messages.Delivery slot').'</td>
			        <td>:</td>
			        <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 15px 13px;">'.$delivery_date ." : ". $delivery_time.'</td>
			        </tr><tr><td style="font-size: 16px; color: #333; font-family: arial; text-align: left; padding: 15px 13px;">'.trans('messages.Delivery mode').'</td>
			            <td>:</td>
			            <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 15px 13px;">'.trans('messages.Delivery to your address').'</td></tr>';

			            $delivery_charge = '<tr>

			        <td style="padding-bottom: 15px;" width="300">&nbsp;</td>

			        <td style="font-size: 16px; color: #e91e63; font-family: arial; text-align: right; padding-bottom: 15px;">'.trans('messages.Delivery fee').'</td>
			        <td style="font-size: 20px; color: #e91e63; font-family: arial; text-align: right; padding: 0 15px 15px;">'.$delivery_details[0]->delivery_charge.getCurrency().'</td>
			        </tr>';
			        }else {
			            $delivery_address ='<tr>
			            <td style="font-size: 16px; color: #333; font-family: arial; text-align: left; padding: 15px 13px;">'.trans('messages.Pickup address').'</td>
			            <td>:</td>
			            <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 15px 13px;">'.$delivery_details[0]->contact_address.'</td>
			            </tr><tr><td style="font-size: 16px; color: #333; font-family: arial; text-align: left; padding: 15px 13px;">'.trans('messages.Delivery mode').'</td>
			            <td>:</td>
			            <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 15px 13px;">'.trans('messages.Pickup directly in store').'</td></tr>';
			            $delivery_charge = '';
			        }
			        $html ='<table style="border: 1px solid #ccc;" border="0" width="450" align="left" cellspacing="0" cellpadding="0" bgcolor="#fff">
			        <tbody>
			        <tr>
			        <td style="border-bottom: 1px solid #ccc; padding: 15px 15px;">
			        <table border="0" cellspacing="0" cellpadding="0">
			        <tbody>
			        <tr>
			        <td><a title="" href="#"><img src='.$logo.' alt="'.Session::get("general")->theme.'" /></a></td>
			        </tr>
			        </tbody>
			        </table>
			        </td>
			        </tr>
			        <tr>
			        <td style="border-bottom: 1px solid #ccc; padding: 15px 15px;">
			        <table border="0" cellspacing="0" cellpadding="0">
			        <tbody>
			        <tr>
			        <td style="font-size: 20px; font-weight: normal; color: #333; font-family: arial;">Order Summary</td>
			        </tr>
			        </tbody>
			        </table>
			        </td>
			        </tr>
			        <tr>
			        <td>
			        <table border="0" cellspacing="0" cellpadding="0">
			        <tbody>
			        <tr>
			        <td style="width: 250px; padding: 15px 15px;">'.$vendor_image.'
			        <h3 style="font-size: 20px; font-family: arial; color: #888; font-weight: normal;">'.$vendor_info[0]->vendor_name.'</h3>
			        </td>
			        <td style="width: 350px;">
			        <table border="0" cellspacing="0" cellpadding="0">
			        <tbody>
			        <tr>
			        <td style="width: 50%; font-size: 20px; font-family: arial; color: #333; font-weight: normal; text-align: right; padding-bottom: 15px;">'.trans('messages.Order Id').'</td>
			        <td width="150">&nbsp;</td>
			        <td style="width: 50%; font-size: 20px; font-family: arial; color: #888; font-weight: normal; text-align: right; padding: 0 15px 15px;">'.$vendor_info[0]->order_key_formated.'</td>
			        </tr>
			        <tr>
			        <td style="width: 50%; font-size: 20px; font-family: arial; color: #333; font-weight: normal; text-align: right; padding-bottom: 15px;">'.trans('messages.Date').'</td>
			        <td width="150">&nbsp;</td>
			        <td style="width: 50%; font-size: 20px; font-family: arial; color: #888; font-weight: normal; text-align: right; padding: 0 15px 15px;">'.date('d M, Y', strtotime($vendor_info[0]->created_date)).'</td>
			        </tr>
			        <tr>
			        <td style="width: 50%; font-size: 20px; font-family: arial; color: #333; font-weight: normal; text-align: right;">'.trans('messages.Status').'</td>
			        <td width="150">&nbsp;</td>
			        <td style="width: 50%; font-size: 20px; font-family: arial; color: #888; font-weight: normal; text-align: right; padding: 0 15px;">'.$vendor_info[0]->name.'</td>
			        </tr>
			        </tbody>
			        </table>
			        </td>
			        </tr>
			        </tbody>
			        </table>
			        </td>
			        </tr>
			        <tr>
			        <td style="border-bottom: 1px solid #ccc; padding: 15px 15px;">
			        <table border="0" cellspacing="0" cellpadding="0">
			        <tbody>
			        <tr>
			        <td style="font-size: 20px; font-weight: normal; color: #333; font-family: arial;">'.trans('messages.Bill details').'</td>
			        </tr>
			        </tbody>
			        </table>
			        </td>
			        </tr>
			        <tr>
			        <td>
			        <table border="0" cellspacing="0" cellpadding="0">
			        <tbody>'.$item.'</tbody>
			        </table>
			        </td>
			        </tr>
			        <tr>
			        <td>
			        <table style="width: 100%;" border="0" cellspacing="0" cellpadding="0">
			        <tbody>
			        <tr>

			        <td style="padding-bottom: 15px; padding-top: 15px;" width="300">&nbsp;</td>

			        <td style="font-size: 16px; color: #333; padding-bottom: 15px; padding-top: 15px; font-family: arial; text-align: right;">'.trans('messages.Subtotal').'</td>
			        <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 15px 15px 15px;">'.$sub_total.getCurrency().'</td>
			        </tr>'.$delivery_charge.'<tr>
			        <td style="padding-bottom: 15px;" width="300">&nbsp;</td>
			        <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding-bottom: 15px;">'.trans('messages.Tax').'</td>
			        <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 0 15px 15px;">'.$delivery_details[0]->service_tax.getCurrency().'</td>
			        </tr>
			        </tbody>
			        </table>
			        </td>
			        </tr>
			        <tr>
			        <td style="border-top: 1px solid #ccc; border-bottom: 1px solid #ccc; padding: 15px 0;">
			        <table style="width: 100%;" border="0" cellspacing="0" cellpadding="0">
			        <tbody>
			        <tr>
			        <td style="font-size: 16px; color: #333; font-family: arial; text-align: right;" width="450">'.trans('messages.Total').'</td>
			        <td style="font-size: 20px; color: #e91e63; font-family: arial; text-align: right; padding: 0 15px;">'.$delivery_details[0]->total_amount.getCurrency().'</td>
			        </tr>
			        </tbody>
			        </table>
			        </td>
			        </tr>
			        <tr>
			        <td>
			        <table style="width: 100%;" border="0" cellspacing="0" cellpadding="0">
			        <tbody>'.$delivery_address.'<tr>
			        <td style="font-size: 16px; color: #333; font-family: arial; text-align: left; padding: 15px 13px;">'.trans('messages.Payment mode').'</td>
			        <td>:</td>
			        <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 15px 13px;">'.$vendor_info[0]->payment_gateway_name.'</td>
			        </tr>
			        </tbody>
			        </table>
			        </td>
			        </tr>
			        </tbody>
		*/
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
	public function send_order_email_admin_vendors($id, $uid, $language) {
		$order_id = $id;
		$user_id = $uid;
		$language = $language;
		$user_array = array("user_id" => $user_id, "language" => $language, "order_id" => $order_id);
		$response = $this->get_order_detail($user_array);
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
		/*$logo = url('/assets/front/'.Session::get("general")->theme.'/images/'.Session::get("general")->theme.'.png');
			        if(file_exists(base_path().'/public/assets/admin/base/images/vendors/list/'.$vendor_info[0]->logo_image)) {
			            $vendor_image ='<img width="100px" height="100px" src="'.URL::to("assets/admin/base/images/vendors/list/".$vendor_info[0]->logo_image).'") >';
			        } else{
			            $vendor_image ='<img width="100px" height="100px" src="'.URL::to("assets/front/'.Session::get("general")->theme.'/images/blog_no_images.png").'") >';
			        }
			        $delivery_date = date("d F, l", strtotime($delivery_details[0]->delivery_date));
			        $delivery_time = date('g:i a', strtotime($delivery_details[0]->start_time)).'-'.date('g:i a', strtotime($delivery_details[0]->end_time));
			        $sub_total = 0;
			        $item = '';
			        foreach($order_detail as $items){
			            $item .='<tr>
			            <td style="width: 200px; padding: 15px 15px;" valign="middle"><a style="text-decoration: none; font-size: 16px; color: #333; font-family: arial;" title="" href="#"><img width="50px" height="50px" style="vertical-align: middle;" src='.url('/assets/admin/base/images/products/thumb/'.$items->product_image).' alt="del" /></a>
			            <p style="margin: 10px 0 0 0;">'.str_limit(ucfirst(strtolower($items->product_name)),30).'</p>
			            </td>
			            <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 0 15px;" width="100">'.$items->item_cost.getCurrency().'</td>
			            <td style="font-size: 16px; color: #333; font-family: arial; text-align: right;" width="100">'.$items->item_unit.'</td>
			            <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 0 15px;" width="100">'.($items->item_cost*$items->item_unit).getCurrency().'</td>
			            </tr>
			            <tr>
			            <td colspan="5" width="100%">
			            <table border="0" width="100%" cellspacing="0" cellpadding="0">
			            <tbody>
			            <tr>
			            <td style="width: 100%; border-bottom: 1px solid #ccc;">&nbsp;</td>
			            </tr>
			            </tbody>
			            </table>
			            </td>
			            </tr>';
			            $sub_total += $items->item_cost*$items->item_unit;
			        }
			        if($delivery_details[0]->order_type == 1){
			            $delivery_address ='<tr>
			            <td style="font-size: 16px; color: #333; font-family: arial; text-align: left; padding: 15px 13px;">'.trans('messages.Delivery address').'</td>
			            <td>:</td>
			            <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 15px 13px;">'.$delivery_details[0]->address.'</td>
			            </tr>';
			        }else {
			            $delivery_address ='<tr>
			            <td style="font-size: 16px; color: #333; font-family: arial; text-align: left; padding: 15px 13px;">'.trans('messages.Pickup address').'</td>
			            <td>:</td>
			            <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 15px 13px;">'.$delivery_details[0]->address.'</td>
			            </tr>';
			        }
			        $html ='<table style="border: 1px solid #ccc;" border="0" width="450" align="left" cellspacing="0" cellpadding="0" bgcolor="#fff">
			        <tbody>
			        <tr>
			        <td style="border-bottom: 1px solid #ccc; padding: 15px 15px;">
			        <table border="0" cellspacing="0" cellpadding="0">
			        <tbody>
			        <tr>
			        <td><a title="" href="#"><img src='.$logo.' alt="'.Session::get("general")->theme.'" /></a></td>
			        </tr>
			        </tbody>
			        </table>
			        </td>
			        </tr>
			        <tr>
			        <td style="border-bottom: 1px solid #ccc; padding: 15px 15px;">
			        <table border="0" cellspacing="0" cellpadding="0">
			        <tbody>
			        <tr>
			        <td style="font-size: 20px; font-weight: normal; color: #333; font-family: arial;">Order Summary</td>
			        </tr>
			        </tbody>
			        </table>
			        </td>
			        </tr>
			        <tr>
			        <td>
			        <table border="0" cellspacing="0" cellpadding="0">
			        <tbody>
			        <tr>
			        <td style="width: 250px; padding: 15px 15px;">'.$vendor_image.'
			        <h3 style="font-size: 20px; font-family: arial; color: #888; font-weight: normal;">'.$vendor_info[0]->vendor_name.'</h3>
			        </td>
			        <td style="width: 350px;">
			        <table border="0" cellspacing="0" cellpadding="0">
			        <tbody>
			        <tr>
			        <td style="width: 50%; font-size: 20px; font-family: arial; color: #333; font-weight: normal; text-align: right; padding-bottom: 15px;">'.trans('messages.Order Id').'</td>
			        <td width="150">&nbsp;</td>
			        <td style="width: 50%; font-size: 20px; font-family: arial; color: #888; font-weight: normal; text-align: right; padding: 0 15px 15px;">'.$vendor_info[0]->order_key_formated.'</td>
			        </tr>
			        <tr>
			        <td style="width: 50%; font-size: 20px; font-family: arial; color: #333; font-weight: normal; text-align: right; padding-bottom: 15px;">'.trans('messages.Date').'</td>
			        <td width="150">&nbsp;</td>
			        <td style="width: 50%; font-size: 20px; font-family: arial; color: #888; font-weight: normal; text-align: right; padding: 0 15px 15px;">'.date('d M, Y', strtotime($vendor_info[0]->created_date)).'</td>
			        </tr>
			        <tr>
			        <td style="width: 50%; font-size: 20px; font-family: arial; color: #333; font-weight: normal; text-align: right;">'.trans('messages.Status').'</td>
			        <td width="150">&nbsp;</td>
			        <td style="width: 50%; font-size: 20px; font-family: arial; color: #888; font-weight: normal; text-align: right; padding: 0 15px;">'.$vendor_info[0]->name.'</td>
			        </tr>
			        </tbody>
			        </table>
			        </td>
			        </tr>
			        </tbody>
			        </table>
			        </td>
			        </tr>
			        <tr>
			        <td style="border-bottom: 1px solid #ccc; padding: 15px 15px;">
			        <table border="0" cellspacing="0" cellpadding="0">
			        <tbody>
			        <tr>
			        <td style="font-size: 20px; font-weight: normal; color: #333; font-family: arial;">'.trans('messages.Bill details').'</td>
			        </tr>
			        </tbody>
			        </table>
			        </td>
			        </tr>
			        <tr>
			        <td>
			        <table border="0" cellspacing="0" cellpadding="0">
			        <tbody>'.$item.'</tbody>
			        </table>
			        </td>
			        </tr>
			        <tr>
			        <td>
			        <table style="width: 100%;" border="0" cellspacing="0" cellpadding="0">
			        <tbody>
			        <tr>

			        <td style="padding-bottom: 15px; padding-top: 15px;" width="300">&nbsp;</td>

			        <td style="font-size: 16px; color: #333; padding-bottom: 15px; padding-top: 15px; font-family: arial; text-align: right;">'.trans('messages.Subtotal').'</td>
			        <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 15px 15px 15px;">'.$sub_total.getCurrency().'</td>
			        </tr>
			        <tr>

			        <td style="padding-bottom: 15px;" width="300">&nbsp;</td>

			        <td style="font-size: 16px; color: #e91e63; font-family: arial; text-align: right; padding-bottom: 15px;">'.trans('messages.Delivery fee').'</td>
			        <td style="font-size: 20px; color: #e91e63; font-family: arial; text-align: right; padding: 0 15px 15px;">'.$delivery_details[0]->delivery_charge.getCurrency().'</td>
			        </tr>
			        <tr>
			        <td style="padding-bottom: 15px;" width="300">&nbsp;</td>
			        <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding-bottom: 15px;">'.trans('messages.Tax').'</td>
			        <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 0 15px 15px;">'.$delivery_details[0]->service_tax.getCurrency().'</td>
			        </tr>
			        </tbody>
			        </table>
			        </td>
			        </tr>
			        <tr>
			        <td style="border-top: 1px solid #ccc; border-bottom: 1px solid #ccc; padding: 15px 0;">
			        <table style="width: 100%;" border="0" cellspacing="0" cellpadding="0">
			        <tbody>
			        <tr>
			        <td style="font-size: 16px; color: #333; font-family: arial; text-align: right;" width="450">'.trans('messages.Total').'</td>
			        <td style="font-size: 20px; color: #e91e63; font-family: arial; text-align: right; padding: 0 15px;">'.$delivery_details[0]->total_amount.getCurrency().'</td>
			        </tr>
			        </tbody>
			        </table>
			        </td>
			        </tr>
			        <tr>
			        <td>
			        <table style="width: 100%;" border="0" cellspacing="0" cellpadding="0">
			        <tbody>'.$delivery_address.'<tr>
			        <td style="font-size: 16px; color: #333; font-family: arial; text-align: left; padding: 15px 13px;">'.trans('messages.Delivery slot').'</td>
			        <td>:</td>
			        <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 15px 13px;">'.$delivery_date ." : ". $delivery_time.'</td>
			        </tr>
			        <tr>
			        <td style="font-size: 16px; color: #333; font-family: arial; text-align: left; padding: 15px 13px;">'.trans('messages.Payment mode').'</td>
			        <td>:</td>
			        <td style="font-size: 16px; color: #333; font-family: arial; text-align: right; padding: 15px 13px;">'.$vendor_info[0]->payment_gateway_name.'</td>
			        </tr>
			        </tbody>
			        </table>
			        </td>
			        </tr>
			        </tbody>
		*/
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

	public function order_detail(Request $data) {
		$post_data = $data->all();
		//print_r($post_data);exit;
		// App::setLocale('en');
		// if ($post_data['language'] == 2) {
		// 	App::setLocale('ar');
		// }
		if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }	
		$language_id = $post_data['language'];
		$query3 = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$query4 = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$vendor_info = DB::select('SELECT distinct(o.id) as order_id,vendors_infos.vendor_name, vendors.logo_image, vendors.contact_address, vendors.contact_email, o.created_date,o.order_status,order_status.name,payment_gateways_info.name as payment_gateway_name,o.outlet_id,vendors.id as vendor_id,o.order_key_formated,o.invoice_id, delivery_time_interval.start_time,delivery_time_interval.end_time,o.invoice_id
        FROM orders o
        left join vendors vendors on vendors.id = o.vendor_id
        left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
        left join outlets out on out.vendor_id = vendors.id
        left join order_status order_status on order_status.id = o.order_status
        left join payment_gateways payment_gateways on payment_gateways.id = o.payment_gateway_id
        left join payment_gateways_info payment_gateways_info on payment_gateways_info.payment_id = payment_gateways.id
        left join delivery_time_slots on delivery_time_slots.id =o.delivery_slot
        left join delivery_time_interval on delivery_time_interval.id = delivery_time_slots.time_interval_id
        where ' . $query3 . ' AND ' . $query4 . ' AND o.id = ? AND o.customer_id= ? ORDER BY o.id ', array($post_data['order_id'], $post_data['user_id']));
		foreach ($vendor_info as $k => $v) {
			$logo_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
			if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $v->logo_image) && $v->logo_image != '') {
				$logo_image = url('/assets/admin/base/images/vendors/logos/' . $v->logo_image);
			}
			$vendor_info[$k]->logo_image = $logo_image;
			$vendor_info[$k]->start_time = ($v->start_time != '') ? $v->start_time : '';
			$vendor_info[$k]->end_time = ($v->end_time != '') ? $v->end_time : '';
		}

		$query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and p.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

		$wquery = '"weight_classes_infos"."lang_id" = (case when (select count(weight_classes_infos.lang_id) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and weight_classes.id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$oquery = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$order_items = DB::select('SELECT p.product_image, pi.description,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.id as order_id,o.invoice_id,pi.product_name,pi.description,o.coupon_amount,weight_classes_infos.title,weight_classes_infos.unit as unit_code,o.order_key_formated,p.weight
        FROM orders o
        LEFT JOIN orders_info oi ON oi.order_id = o.id
        LEFT JOIN products p ON p.id = oi.item_id
        LEFT JOIN products_infos pi ON pi.id = p.id
        LEFT JOIN weight_classes ON weight_classes.id = p.weight_class_id
        LEFT JOIN weight_classes_infos ON weight_classes_infos.id = weight_classes.id
        where ' . $query . ' AND ' . $wquery . ' AND o.id = ? AND o.customer_id= ? ORDER BY oi.id', array($post_data['order_id'], $post_data['user_id']));

		//print_r($order_items);exit;
		foreach ($order_items as $key => $items) {
			$product_image = URL::asset('assets/front/' . Session::get('general')->theme . '/images/no_image.png');
			if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $items->product_image) && $items->product_image != '') {
				$product_image = url('/assets/admin/base/images/products/list/' . $items->product_image);
			}
			$invoic_pdf = url('/assets/front/' . Session::get('general')->theme . '/images/invoice/' . $items->invoice_id . '.pdf');
			$order_items[$key]->product_image = $product_image;
			$order_items[$key]->invoic_pdf = $invoic_pdf;
		}

		$reviews = DB::table('outlet_reviews')
			->selectRaw('count(outlet_reviews.order_id) as review_status')
		//->where("outlet_reviews.outlet_id","=",$reviews->outlet_id)
			->where("outlet_reviews.order_id", "=", $post_data['order_id'])
			->where('outlet_reviews.customer_id', '=', $post_data['user_id'])
			->first();

		$query2 = 'pgi.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and pg.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$oquery = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$delivery_details = DB::select('SELECT o.delivery_instructions,ua.address as user_contact_address,pg.id as payment_gateway_id,pgi.name,o.total_amount,o.delivery_charge,o.service_tax,dti.start_time,end_time,o.created_date,o.delivery_date,o.order_type,out_infos.contact_address,o.coupon_amount, u.email FROM orders o LEFT JOIN user_address ua ON ua.id = o.delivery_address LEFT JOIN users u ON u.id = ua.user_id left join payment_gateways pg on pg.id = o.payment_gateway_id left join payment_gateways_info pgi on pgi.payment_id = pg.id left join delivery_time_slots dts on dts.id=o.delivery_slot left join delivery_time_interval dti on dti.id = dts.time_interval_id left join outlets out on out.id = o.outlet_id left join outlet_infos out_infos on out_infos.id = out.id where ' . $query2 . ' AND ' . $oquery . ' AND o.id = ? AND o.customer_id= ?', array($post_data['order_id'], $post_data['user_id']));
		foreach ($delivery_details as $k => $v) {
			$delivery_details[$k]->start_time = ($v->start_time != '') ? $v->start_time : '';
			$delivery_details[$k]->end_time = ($v->end_time != '') ? $v->end_time : '';
			$delivery_details[$k]->user_contact_address = ($v->user_contact_address != '') ? $v->user_contact_address : '';
			$delivery_details[$k]->contact_address = ($v->contact_address != '') ? $v->contact_address : '';
			$delivery_details[$k]->email = ($v->email != '') ? $v->email : '';
			$sub_total = ($v->total_amount) - ($v->delivery_charge + $v->service_tax) + ($v->coupon_amount);
			$delivery_details[$k]->sub_total = $sub_total;
			$tax_amount = $sub_total * $v->service_tax / 100;
			$delivery_details[$k]->tax_amount = $tax_amount;
		}
		//print_r($delivery_details);exit;
		$tracking_orders = array(1 => "Initiated", 10 => "Processed", 18 => "Packed", 19 => "Dispatched", 12 => "Delivered");
		$tracking_result = $mob_tracking_result = array();
		$t = 0;
		$last_state = $mob_last_state = "";
		foreach ($tracking_orders as $key => $track) {
			$tracking_result[$key]['text'] = $track;
			$mob_tracking_result[$t]['text'] = $track;
			$tracking_result[$key]['process'] = "0";
			$mob_tracking_result[$t]['process'] = "0";
			$tracking_result[$key]['order_comments'] = "";
			$mob_tracking_result[$t]['order_comments'] = "";
			$tracking_result[$key]['date'] = "";
			$mob_tracking_result[$t]['date'] = "";
			$check_status = DB::table('orders_log')
				->select('order_id', 'log_time', 'order_comments')
				->where('order_id', '=', $post_data['order_id'])
				->where('order_status', '=', $key)
				->first();
			if (count($check_status) > 0) {
				$last_state = $key;
				$tracking_result[$key]['process'] = "1";
				$tracking_result[$key]['order_comments'] = ($check_status->order_comments != '') ? $check_status->order_comments : '';
				$tracking_result[$key]['date'] = date('M j Y g:i A', strtotime($check_status->log_time));
				$mob_last_state = $t;
				$mob_tracking_result[$t]['process'] = "1";
				$mob_tracking_result[$t]['order_comments'] = $check_status->order_comments;
				$mob_tracking_result[$t]['date'] = date('M j Y g:i A', strtotime($check_status->log_time));
			}
			$t++;
		}

		$return_reasons = $this->return_reason($language_id);
		$mob_return_reasons = $this->mob_return_reason($language_id);
		$result = array("response" => array("httpCode" => 400, "Message" => "no items found", "order_items" => array(), "delivery_details" => array(), "return_reasons" => $return_reasons, "tracking_result" => $tracking_result, "last_state" => $last_state, "reviews" => $reviews));
		if (count($order_items) > 0 && count($delivery_details) > 0 && count($vendor_info) > 0) {
			$result = array("response" => array("httpCode" => 200, "Message" => "order items", "order_items" => $order_items, "delivery_details" => $delivery_details, "vendor_info" => $vendor_info, "return_reasons" => $return_reasons, "tracking_result" => $tracking_result, "last_state" => $last_state, "mob_tracking_result" => $mob_tracking_result, "mob_return_reasons" => $mob_return_reasons, "reviews" => $reviews, "order_id_encrypted" => encrypt($post_data['order_id']))); //, "mob_delivery_details" => $delivery
		}
		return json_encode($result);
	}

	public function get_order_detail($data) {
		$post_data = $data;
		//print_r($data);exit;
		if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }	
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

	public function return_reason($language) {
		$return_reasons = array();
		$result = array("response" => array("httpCode" => 400, "status" => false, "return_reasons" => $return_reasons));
		$query = '"return_reason"."lang_id" = (case when (select count(*) as totalcount from return_reason where return_reason.lang_id = ' . $language . ') > 0 THEN ' . $language . ' ELSE 1 END)';
		$return_reasons = DB::table('return_reason')
			->whereRaw($query)
			->orderBy('id', 'asc')
			->get();
		$reasons_array = array();
		foreach ($return_reasons as $reasons) {
			$reasons_array[$reasons->id] = ucfirst(strtolower($reasons->name));
		}
		return $reasons_array;
	}
	/* Mobile api return reason */
	public function mob_return_reason($language) {
		//print_r("expression");exit;
		$return_reasons = array();
		$result = array("response" => array("httpCode" => 400, "status" => false, "return_reasons" => $return_reasons));
		$query = '"return_reason"."lang_id" = (case when (select count(return_reason.lang_id) as totalcount from return_reason where return_reason.lang_id = ' . $language . ') > 0 THEN ' . $language . ' ELSE 1 END)';
		$return_reasons = DB::table('return_reason')
			->whereRaw($query)
			->orderBy('id', 'asc')
			->get();
		$reasons_array = array();
		$r = 0;
		foreach ($return_reasons as $reasons) {
			$reasons_array[$r]['id'] = $reasons->id;
			$reasons_array[$r]['name'] = $reasons->name;
			$r++;
		}
		return $reasons_array;
	}

	public function update_promocode(Request $data) {

		// echo("nagaaa");exit();
		$current_date = strtotime(date('Y-m-d'));
		$post_data = $data->all();
		$coupon_details = DB::table('coupons')
			->select('coupons.id as coupon_id', 'coupon_type', 'offer_amount', 'offer_type', 'coupon_code', 'start_date', 'end_date')
			->leftJoin('coupon_outlet', 'coupon_outlet.coupon_id', '=', 'coupons.id')
			->where('coupon_code', '=', $post_data['promo_code'])
			->where('coupon_outlet.outlet_id', '=', $post_data['outlet_id'])
			->first();

		//print_r($coupon_details);exit;
		if (count($coupon_details) == 0) {
			$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.This coupon is not applicable for the current store.")));
			return json_encode($result);
		} else if ((strtotime($coupon_details->start_date) <= $current_date) && (strtotime($coupon_details->end_date) >= $current_date)) {
			$coupon_user_limit_details = DB::table('user_cart_limit')
				->select('cus_order_count', 'user_limit', 'total_order_count', 'coupon_limit')
				->where('customer_id', '=', $post_data['user_id'])
				->where('coupon_code', '=', $post_data['promo_code'])
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
			$result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.Coupon applied Successfully"), "coupon_details" => $coupon_details, "coupon_user_limit_details" => $coupon_user_limit_details));
			return json_encode($result);
		} else {
			$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Invalid promocode.")));
			return json_encode($result);
		}
	}

	public function send_otp(Request $data) {
		// if (isset($data['language']) && $data['language'] == 2) {
		// 	App::setLocale('ar');
		// } else {
		// 	App::setLocale('en');
		// }
		if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }	

		$post_data = $data->all();
		$user_id = $post_data['user_id'];
		$otp_option = $post_data['otp_option'];
		$otp = rand(100000, 999999);
		$users = Users::find($user_id);
		if ($otp_option == 2 || $otp_option == 3) {
			$number = $users->mobile;
			$message = 'You have received OTP password for ' . getAppConfig()->site_name . '. Your OTP code is ' . $otp . '. This code can be used only once and dont share this code with anyone.';
			/*$twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
			$twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
			$from_number = "+14783471785";
			$client = new Services_Twilio($twilo_sid, $twilio_token);*/
			$twilo_sid = TWILIO_ACCOUNTSID;
                $twilio_token = TWILIO_AUTHTOKEN;
                $from_number = TWILIO_NUMBER;
                $client = new Client($twilo_sid, $twilio_token);
			//print_r ($client);exit;
			// Create an authenticated client for the Twilio API
			try {
			/*	$m = $client->account->messages->sendMessage(
					$from_number, // the text will be sent from your Twilio number
					$number, // the phone number the text will be sent to
					$message // the body of the text message
				);*/
				                $m =  $client->messages->create($number, array('from' => $from_number, 'body' => $message));

				//echo $m;exit;
				$users->otp = $otp;
				$users->updated_date = date("Y-m-d H:i:s");
				$users->save();
				if ($otp_option == 2) {
					$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.we have sent verification code to your mobile.")));
					return json_encode($result);
				}

			} catch (Exception $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => $e->getMessage()));
				return json_encode($result);
			} catch (\Services_Twilio_RestException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Invalid Phone number.")));
				return json_encode($result);
			}
		}
		if ($otp_option == 1 || $otp_option == 3) {
			$template = DB::table('email_templates')
				->select('from_email', 'from', 'subject', 'template_id', 'content')
				->where('template_id', '=', self::OTP_EMAIL_TEMPLATE)
				->get();
			if (count($template)) {
				$from = $template[0]->from_email;
				$from_name = $template[0]->from;
				$subject = $template[0]->subject;
				if (!$template[0]->template_id) {
					$template = 'mail_template';
					$from = getAppConfigEmail()->contact_email;
					$subject = getAppConfig()->site_name . " OTP Password";
					$from_name = "";
				}
				$content = array("name" => ucfirst($users->name), "otp_password" => "" . $otp);
				$email = smtp($from, $from_name, $users->email, $subject, $content, $template);
				$users = Users::find($user_id);
				$users->otp = $otp;
				$users->updated_date = date("Y-m-d H:i:s");
				$users->save();
				if ($otp_option == 3) {
					$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.we have sent verification code to your mobile and to your email.")));
					return json_encode($result);
				} else {

					$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.we have sent verification code to your email.")));
				}
			}
		}
		return json_encode($result);
	}

	public function check_otp(Request $data) {
		$post_data = $data->all();
		$coupon_details = DB::table('users')
			->select('id')
			->where('id', '=', $post_data['user_id'])
			->where('otp', '=', $post_data['otp'])
			->first();
		$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Order verification code is wrong"), "order_items" => array()));
		if (count($coupon_details) > 0) {
			$result = array("response" => array("httpCode" => 200, "Message" => "Otp verified success"));
		}
		return json_encode($result);
	}

	public function re_order(Request $data) {
		$post_data = $data->all();
		$query = 'SELECT o.vendor_id,o.outlet_id,oi.item_id AS product_id,oi.item_unit
        FROM orders o
        LEFT JOIN orders_info oi ON oi.order_id = o.id
        where o.id = ? AND o.customer_id= ? ORDER BY oi.id';
		//echo $post_data['user_id'].",".$post_data['order_id']."<br/>";
		$order_items = DB::select($query, array($post_data['order_id'], $post_data['user_id']));
		foreach ($order_items as $order) {
			$re_order_data = array();
			$re_order_data['user_id'] = $post_data['user_id'];
			$re_order_data['vendors_id'] = $order->vendor_id;
			$re_order_data['outlet_id'] = $order->outlet_id;
			$re_order_data['product_id'] = $order->product_id;
			$re_order_data['qty'] = $order->item_unit;
			$cart_data = $this->reorder_add_cart($re_order_data);
		}
		$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Order has been added to your cart.")));
		return json_encode($result);
	}

	public function reorder_add_cart($data) {
		$post_data = $data;
		$ucdata = DB::table('cart')
			->select('cart.cart_id')
			->where("cart.user_id", "=", $post_data['user_id'])
			->get();
		if (count($ucdata)) {
			$uucdata = DB::table('cart')
				->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
				->select('cart.cart_id', 'cart_detail.product_id', 'cart_detail.quantity', 'cart_detail.cart_detail_id')
				->where("cart.user_id", "=", $post_data['user_id'])
				->where("cart.store_id", "=", $post_data['vendors_id'])
				->where("cart.outlet_id", "=", $post_data['outlet_id'])
				->get();
			if (count($uucdata)) {
				$cdata = DB::table('cart')
					->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
					->select('cart.cart_id', 'cart_detail.product_id', 'cart_detail.quantity', 'cart_detail.cart_detail_id')
					->where("cart.user_id", "=", $post_data['user_id'])
					->where("cart.store_id", "=", $post_data['vendors_id'])
					->where("cart.outlet_id", "=", $post_data['outlet_id'])
					->where("cart_detail.product_id", "=", $post_data['product_id'])
					->get();
				if (count($cdata)) {
					$cart = Cart_model::find($cdata[0]->cart_id);
					$cart->updated_at = date("Y-m-d H:i:s");
					$cart->save();
					$cart_info = Cart_info::find($cdata[0]->cart_detail_id);
					$quntiry = $cart_info->quantity + $post_data['qty'];
					$cart_info->quantity = $quntiry;
					$cart_info->updated_at = date("Y-m-d H:i:s");
					$cart_info->save();
					$cart_item = 0;
					if ($post_data['user_id']) {
						$cdata = DB::table('cart')
							->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
							->select('cart_detail.cart_id', DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
							->where("cart.user_id", "=", $post_data['user_id'])
							->groupby('cart_detail.cart_id')
							->get();
						if (count($cdata)) {
							$cart_item = $cdata[0]->cart_count;
						}
					}
					return true;
				} else {
					$ccdata = DB::table('cart')
						->select('cart.cart_id')
						->where("cart.user_id", "=", $post_data['user_id'])
						->where("cart.store_id", "=", $post_data['vendors_id'])
						->where("cart.outlet_id", "=", $post_data['outlet_id'])
						->get();
					if (count($ccdata)) {
						$cart = Cart_model::find($ccdata[0]->cart_id);
						$cart->updated_at = date("Y-m-d H:i:s");
						$cart->save();
					} else {
						$cart = new Cart_model;
						$cart->user_id = $post_data['user_id'];
						$cart->store_id = $post_data['vendors_id'];
						$cart->outlet_id = $post_data['outlet_id'];
						$cart->cart_status = 1;
						$cart->created_at = date("Y-m-d H:i:s");
						$cart->updated_at = date("Y-m-d H:i:s");
						$cart->save();
					}
					$cart_info = new Cart_info;
					$cart_info->cart_id = $cart->cart_id;
					$cart_info->product_id = $post_data['product_id'];
					$cart_info->quantity = $post_data['qty'];
					$cart_info->created_at = date("Y-m-d H:i:s");
					$cart_info->updated_at = date("Y-m-d H:i:s");
					$cart_info->save();
					$cart_item = 0;
					if ($post_data['user_id']) {
						$cdata = DB::table('cart')
							->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
							->select('cart_detail.cart_id', DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
							->where("cart.user_id", "=", $post_data['user_id'])
							->groupby('cart_detail.cart_id')
							->get();
						if (count($cdata)) {
							$cart_item = $cdata[0]->cart_count;
						}
					}
					//$result = array("response" => array("httpCode" => 200 , "Message" => "Cart has been added successfully!","type" => 1,"cart_count" => $cart_item));
					return true;
				}
			} else {
				$cart_item = 0;
				if ($post_data['user_id']) {
					$cdata = DB::table('cart')
						->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
						->select('cart_detail.cart_id', DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
						->where("cart.user_id", "=", $post_data['user_id'])
						->groupby('cart_detail.cart_id')
						->get();
					if (count($cdata)) {
						$cart_item = $cdata[0]->cart_count;
					}
				}
				return false;
			}
		} else {
			$cart = new Cart_model;
			$cart->user_id = $post_data['user_id'];
			$cart->store_id = $post_data['vendors_id'];
			$cart->outlet_id = $post_data['outlet_id'];
			$cart->cart_status = 1;
			$cart->created_at = date("Y-m-d H:i:s");
			$cart->updated_at = date("Y-m-d H:i:s");
			$cart->save();

			$cart_info = new Cart_info;
			$cart_info->cart_id = $cart->cart_id;
			$cart_info->product_id = $post_data['product_id'];
			$cart_info->quantity = $post_data['qty'];
			$cart_info->created_at = date("Y-m-d H:i:s");
			$cart_info->updated_at = date("Y-m-d H:i:s");
			$cart_info->save();
			$cart_item = 0;
			if ($post_data['user_id']) {
				$cdata = DB::table('cart')
					->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
					->select('cart_detail.cart_id', DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
					->where("cart.user_id", "=", $post_data['user_id'])
					->groupby('cart_detail.cart_id')
					->get();
				if (count($cdata)) {
					$cart_item = $cdata[0]->cart_count;
				}
			}
			return true;
		}
		return false;
	}

/*	public function return_order(Request $data) {
		$post_data = $data->all();
		$return_orders = new return_orders;
		$return_orders->order_id = $post_data['order_id'];
		$return_orders->return_reason = $post_data['return_reason'];
		$return_orders->return_comments = trim($post_data['comments']);
		$return_orders->return_action_id = 1;
		$return_orders->return_status = 1;
		$return_orders->created_by = $post_data['user_id'];
		$return_orders->created_at = date("Y-m-d H:i:s");
		$return_orders->modified_at = date("Y-m-d H:i:s");
		$return_orders->modified_by = $post_data['user_id'];
		$return_orders->save();

		//Get the order related data here and updated with log table
		$order_data = orders::find($post_data['order_id']);
		$Orders = new return_orders_log;
		$Orders->vendor_id = $order_data->vendor_id;
		$Orders->outlet_id = $order_data->outlet_id;
		$Orders->order_id = $post_data['order_id'];
		$Orders->return_orders_id = $return_orders->id;
		$Orders->customer_id = $order_data->customer_id;
		$Orders->return_status = 1;
		$Orders->return_reason = $post_data['return_reason'];
		$Orders->return_action = 1;
		$Orders->order_status = 17;
		$Orders->modified_date = date("Y-m-d H:i:s");
		$Orders->created_date = date("Y-m-d H:i:s");
		$Orders->customer_notified = 1;
		$Orders->modified_by = $post_data['user_id'];
		$Orders->save();

		//Update return order status with orders table
		DB::update('update orders set order_status = ? where id = ?', array(17, $post_data['order_id']));
		$result = array("response" => array("httpCode" => 200, "Message" => "Order return initiated successfully"));

		/*Internal Admin Notifications Storing with notifications table
			        * When order is returned by customer insert the notification logs for Admin & Vendor
		/
		$mess = "Your Order " . $order_data->order_key_formated . " has been returned at " . $post_data['vendor_name'];
		$values = array('order_id' => $post_data['order_id'],
			'customer_id' => $order_data->customer_id,
			'vendor_id' => $order_data->vendor_id,
			'outlet_id' => $order_data->outlet_id,
			'message' => $mess,
			'read_status' => 0,
			'created_date' => date('Y-m-d H:i:s'));
		DB::table('notifications')->insert($values);

		//Send mail to admin regarding when order returned by customer here
		$template = DB::table('email_templates')
			->select('*')
			->where('template_id', '=', self::RETURN_STATUS_CUSTOMER_EMAIL_TEMPLATE)
			->get();
		if (count($template)) {
			$from = $template[0]->from_email;
			$from_name = $template[0]->from;
			$subject = $template[0]->subject;
			if (!$template[0]->template_id) {
				$template = 'mail_template';
				$from = getAppConfigEmail()->contact_email;
				$subject = getAppConfig()->site_name . " Return Order Status Information";
				$from_name = "";
			}
			$users = Users::find(1);
			$customers = Users::find($post_data['user_id']);
			$return_reason = return_reasons::find($post_data['return_reason']);
			$cont_replace = "Following user <b>" . $customers->name . "</b> was returned order <b>" . $order_data->order_key_formated . "</b> with following store or outlet <b>" . $post_data['vendor_name'] . "</b>";
			$cont_replace1 = "Kindly find the reason for returning the order <b>" . $return_reason->name . "</b> and make it necessary arrangements for the order.Kindly find the below comments from a customer: <br/><b>" . $post_data['comments'] . "</b>";
			$content = array("name" => $users->name, "email" => $users->email, "replacement" => $cont_replace, "replacement1" => $cont_replace1);
			$email = smtp($from, $from_name, $users->email, $subject, $content, $template);
		}
		return json_encode($result);
	}*/

	public function return_order(Request $data) {
		$post_data = $data->all();
		$return_orders = DB::table('return_orders')
					->select('return_orders.id')
					->where('order_id',$post_data['order_id'])
					->count();
		if($return_orders == 0)
		{
			DB::update('update orders set order_status = ? where id = ?', array(17, (int)$post_data['order_id']));
			DB::update('update orders_log set order_status=? where id = (select max(id) from orders_log where order_id = ' . (int)$post_data['order_id'] . ')', array(17));

			$return_orders = new return_orders;
			$return_orders->order_id = $post_data['order_id'];
			$return_orders->return_reason = $post_data['return_reason'];
			$return_orders->return_comments = trim($post_data['comments']);
			$return_orders->return_action_id = 1;
			$return_orders->return_status = 1;
			$return_orders->created_by = $post_data['user_id'];
			$return_orders->created_at = date("Y-m-d H:i:s");
			$return_orders->modified_at = date("Y-m-d H:i:s");
			$return_orders->modified_by = $post_data['user_id'];
			$return_orders->save();

			//Get the order related data here and updated with log table
			$order_data = orders::find($post_data['order_id']);
			$Orders = new return_orders_log;
			$Orders->vendor_id = $order_data->vendor_id;
			$Orders->outlet_id = $order_data->outlet_id;
			$Orders->order_id = $post_data['order_id'];
			$Orders->return_orders_id = $return_orders->id;
			$Orders->customer_id = $order_data->customer_id;
			$Orders->return_status = 1;
			$Orders->return_reason = $post_data['return_reason'];
			$Orders->return_action = 1;
			$Orders->order_status = 17;
			$Orders->modified_date = date("Y-m-d H:i:s");
			$Orders->created_date = date("Y-m-d H:i:s");
			$Orders->customer_notified = 1;
			$Orders->modified_by = $post_data['user_id'];
			$Orders->save();

			//Update return order status with orders table
			

			$result = array("status"=>1,"httpCode" => 200, "Message" => "Order return initiated successfully");

			/*Internal Admin Notifications Storing with notifications table
				        * When order is returned by customer insert the notification logs for Admin & Vendor
			*/
			$mess = "Your Order " . $order_data->order_key_formated . " has been returned at " . $post_data['vendor_name'];
			$values = array('order_id' => $post_data['order_id'],
				'customer_id' => $order_data->customer_id,
				'vendor_id' => $order_data->vendor_id,
				'outlet_id' => $order_data->outlet_id,
				'message' => $mess,
				'read_status' => 0,
				'created_date' => date('Y-m-d H:i:s'));
			DB::table('notifications')->insert($values);

			//Send mail to admin regarding when order returned by customer here
			/*$template = DB::table('email_templates')
				->select('*')
				->where('template_id', '=', self::RETURN_STATUS_CUSTOMER_EMAIL_TEMPLATE)
				->get();
			if (count($template)) {
				$from = $template[0]->from_email;
				$from_name = $template[0]->from;
				$subject = $template[0]->subject;
				if (!$template[0]->template_id) {
					$template = 'mail_template';
					$from = getAppConfigEmail()->contact_email;
					$subject = getAppConfig()->site_name . " Return Order Status Information";
					$from_name = "";
				}
				$users = Users::find(1);
				$customers = Users::find($post_data['user_id']);
										//print_r($customers);exit;

				$return_reason = return_reasons::find($post_data['return_reason']);

				$cont_replace = "Following user <b>" . $customers->name . "</b> was returned order <b>" . $order_data->order_key_formated . "</b> with following store or outlet <b>" . $post_data['vendor_name'] . "</b>";
				$cont_replace1 = "Kindly find the reason for returning the order <b>" . $return_reason->name . "</b> and make it necessary arrangements for the order.Kindly find the below comments from a customer: <br/><b>" . $post_data['comments'] . "</b>";
				$content = array("name" => $users->name, "email" => $users->email, "replacement" => $cont_replace, "replacement1" => $cont_replace1);
				$email = smtp($from, $from_name, $users->email, $subject, $content, $template);
			}*/
		}else
		{
			$result = array("status"=>2,"httpCode" => 400, "Message" => "Order return request already placed");

		}
		return json_encode($result);
	}


	public function returnreason(Request $data)
	{
		$post_data = $data->all();
		$language = isset($post_data['language'])?$post_data['language']:1;
		$return_reasons = array();
		$query = '"return_reason"."lang_id" = (case when (select count(*) as totalcount from return_reason where return_reason.lang_id = ' . $language . ') > 0 THEN ' . $language . ' ELSE 1 END)';
		$return_reasons = DB::table('return_reason')
			->whereRaw($query)
			->orderBy('id', 'asc')
			->get();
		$result = array("httpCode" => 200, "status" => 1, "return_reasons" => $return_reasons);
		return json_encode($result);

			//print_r($return_reasons);exit;
	}

	public function paypal_payment(Request $data) {

		$post_data = $data->all();
		$payment_array = json_decode($post_data['payment_array']);
		$payment_arrays = json_decode($post_data['payment_array'], true);
		//print_r($payment_array);exit;
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
					->select('*', 'coupons.id as coupons_id')
					->leftJoin('coupon_outlet', 'coupon_outlet.coupon_id', '=', 'coupons.id')
					->where('coupons.id', '=', $payment_array->coupon_id)
					->first();
				if (count($coupon_details) == 0) {
					$result = array("response" => array("httpCode" => 400, "Message" => "No coupons found"));
					return json_encode($result);
				}
				$coupon_user_limit_details = DB::table('user_cart_limit')
					->select('*')
					->where('user_id', '=', $post_data['user_id'])
					->where('coupon_id', '=', $payment_array->coupon_id)
					->first();
				if (count($coupon_user_limit_details) > 0) {
					if ($coupon_user_limit_details->cus_order_count >= $coupon_user_limit_details->user_limit) {
						$result = array("response" => array("httpCode" => 400, "Message" => "Max order limit has been crossed", "order_items" => $order_items, "delivery_details" => $delivery_details));
						return json_encode($result);
					}
					if ($coupon_user_limit_details->total_order_count >= $coupon_user_limit_details->coupon_limit) {
						$result = array("response" => array("httpCode" => 400, "Message" => "Max order limit has been crossed", "order_items" => $order_items, "delivery_details" => $delivery_details));
						return json_encode($result);
					}
				}
			}
		}

		$paypal = Paypal::getAll(array('count' => 1, 'start_index' => 0), $this->_apiContext);
		// process the validation
		//Save Details
		try {

			$payer = PayPal::Payer();
			$payer->setPaymentMethod('paypal');
			//$details = new Details();
			//$details->setShipping($shipping)->setTax($_POST['service_tax']);
			$amount = PayPal::Amount();
			$amount->setCurrency('USD');
			$amount->setTotal($total_amt); //->setDetails($details);
			//$item1 = new Item();
			//$item1->setName($data['doctor_first_name'])->setCurrency('USD')->setQuantity(1)->setPrice($pay);

			//print_r($payment_array->items);exit;
			$items = array();
			$index = 0;
			foreach ($payment_array as $_item) {
				$index++;
				$items[$index] = new Item();
				$items[$index]->setName($_item['name_key'])
					->setCurrency($_item['currency_key'])
					->setQuantity($_item['quantity_key'])
					->setPrice($_item['price_key']);
			}

			$itemList = new ItemList();
			$itemList->setItems(array($item1));
			$transaction = PayPal::Transaction();
			$transaction->setAmount($amount);
			$info = 'Book Appointment - ' . $data['doctor_first_name'] . ' Pay on $ ' . $pay;
			$transaction->setDescription($info)->setItemList($itemList);
			if (isset($_POST['location_visit_status']) && $_POST['location_visit_status'] != '') {
				//$transaction->setDescription('Visit Patient Location - '.$_POST['consulting_fees']);
			}
			$redirectUrls = PayPal::RedirectUrls();
			$redirectUrls->setReturnUrl(route('getDone'));
			$redirectUrls->setCancelUrl(route('getCancel'));
			$payment = PayPal::Payment();
			$payment->setIntent('sale');
			$payment->setPayer($payer);
			$payment->setRedirectUrls($redirectUrls);
			$payment->setTransactions(array($transaction));
			$response = $payment->create($this->_apiContext);

			$redirectUrl = $response->links[1]->href;
			return redirect()->to($redirectUrl);
		} catch (Exception $ex) {
			ResultPrinter::printError("Created Payment Using PayPal. Please visit the URL to Approve.", "Payment", null, $request, $ex);exit(1);
		}
		Session::flash('message', 'Error: Oops. Something went wrong. Please try again later.');
		return Redirect::to('/');
	}
	public function convert_currency(Request $data) {
		$post = $data->all();
		$from_currency = $data->from_currency;
		$to_currency = getCurrencycode();
		$amount = urlencode($total_amount);
		$from_currency = urlencode($from_currency);
		$to_currency = urlencode($to_currency);
		$get = file_get_contents("https://finance.google.com/finance/converter?a=$amount&from=$from_currency&to=$to_currency");
		$get = explode("<span class=bld>", $get);
		$converted_amount = $total_amount;
		if (isset($get[1])) {
			$get = explode("</span>", $get[1]);
			if (isset($get[0])) {
				$converted_amount = preg_replace("/[^0-9\.]/", null, $get[0]);
			}
		}
	}

	public function cancel_order(Request $data) {
		$post_data = $data->all();
		$orders = DB::table('transaction')
			->select('*')
			->where('order_id', '=', $post_data['order_id'])
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
			$affected = DB::update('update orders set order_status = ? where id = ? AND order_status = ?', array(11, $post_data['order_id'], 1));
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
			$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Order has been cancelled")));
		}
		return json_encode($result);
	}

	public function get_order_details($order_id) {
		$language_id = getCurrentLang();
		$query3 = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$query4 = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$vendor_info = DB::select('SELECT vendors_infos.vendor_name,vendors.email,vendors.logo_image,o.id as order_id,o.created_date,o.order_status,order_status.name as status_name,order_status.color_code as color_code,payment_gateways_info.name as payment_gateway_name,o.outlet_id,vendors.id as vendor_id,o.order_key_formated
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
        LEFT JOIN products p ON p.id = oi.item_id
        LEFT JOIN products_infos pi ON pi.id = p.id
        where ' . $query . ' AND o.id = ? ORDER BY oi.id', array($order_id));
		$query5 = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$query2 = 'pgi.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and pg.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$delivery_details = DB::select('SELECT o.delivery_instructions,ua.address,pg.id as payment_gateway_id,pgi.name,o.total_amount,o.delivery_charge,o.service_tax,dti.start_time,end_time,o.created_date,o.delivery_date,o.order_type,out_infos.contact_address,o.coupon_amount,o.customer_id FROM orders o
                    LEFT JOIN user_address ua ON ua.id = o.delivery_address
                    left join payment_gateways pg on pg.id = o.payment_gateway_id
                    left join payment_gateways_info pgi on pgi.payment_id = pg.id
                    left join delivery_time_slots dts on dts.id=o.delivery_slot
                    left join delivery_time_interval dti on dti.id = dts.time_interval_id
                    left join outlets out on out.id = o.outlet_id
                   left join outlet_infos out_infos on out_infos.id = out.id
                    where ' . $query2 . ' AND ' . $query5 . 'AND o.id = ?', array($order_id));
		if (count($order_items) > 0 && count($delivery_details) > 0 && count($vendor_info) > 0) {
			$result = array("order_items" => $order_items, "delivery_details" => $delivery_details, "vendor_info" => $vendor_info);
		}
		return $result;
	}
	public function delete_sms(Request $data) {
		/*$twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
		$twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
		$from_number = "+14783471785";
		$client = new Services_Twilio($twilo_sid, $twilio_token);*/
		$twilo_sid = TWILIO_ACCOUNTSID;
        $twilio_token = TWILIO_AUTHTOKEN;
        $from_number = TWILIO_NUMBER;
        $client = new Client($twilo_sid, $twilio_token);
		$call = $client->account->messages->getIterator(0, 50, array('DateCreated>' => '2017-01-10 08:00:00', 'DateCreated<' => '2016-12-01'));
		foreach ($call as $c) {
			//~ $client->messages($c)->delete();
			//~ echo '<pre>';
			print_r($c);die;
		}echo 2;die;
	}

	public function order_driver_location(Request $data) {
		$post_data = $data->all();
		$rules = [
			'user_id' => ['required'],
			'token' => ['required'],
			'language' => ['required'],
			'order_id' => ['required'],
		];
		$errors = $result = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "status" => false, "Message" => $errors));
		} else {
			try {
				// if ($post_data['language'] == 2) {
				// 	App::setLocale('ar');
				// } else {
				// 	App::setLocale('en');
				// }

				if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            	App::setLocale($post_data['language']);
        		} else {
            	App::setLocale('en');
       			 }	
				$check_auth = JWTAuth::toUser($post_data['token']);
				$language = $post_data['language'];
				$driver_details = Order::get_driver_current_location($post_data['order_id']);
				if (count($driver_details) > 0) {

					$imageName = url('/assets/admin/base/images/default_avatar_male.jpg');
					if (file_exists(base_path() . '/public/assets/admin/base/images/drivers/' . $driver_details->driver_profile_image) && $driver_details->driver_profile_image != '') {
						$imageName = URL::to("/assets/admin/base/images/drivers/" . $driver_details->driver_profile_image . '?' . time());
					}
					$driver_details->driver_profile_image = $imageName;
					$driver_details->delivery_time = $driver_details->start_time . '-' . $driver_details->end_time;
					$language_id = $post_data['language'];
					$query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and p.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
					$order_items = DB::select('SELECT p.product_image,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.invoice_id,pi.product_name,pi.description,o.coupon_amount
					FROM orders o
					LEFT JOIN orders_info oi ON oi.order_id = o.id
					LEFT JOIN products p ON p.id = oi.item_id
					LEFT JOIN products_infos pi ON pi.id = p.id
					where ' . $query . ' AND o.id = ? ORDER BY oi.id', array($driver_details->order_id));
					$driver_details->order_items = count($order_items);
					$result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.Driver details"), "driver_details" => $driver_details));
				} else {
					$result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.No driver assigned to this order")));
				}
			} catch (JWTException $e) {
				$result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
			} catch (TokenExpiredException $e) {
				$result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
			}
		}
		return json_encode($result);
	}

	//mob apis:
	public function morder_detail_copy(Request $data) {
		$post_data = $data->all();
		//print_r($post_data);exit;
		App::setLocale('en');
		/*	if ($post_data['lang'] == 2) {
				App::setLocale('ar');
			}*/
			// $language_id = $post_data['language'];
			$language_id = 1;
		/*	if ($post_data['lang'] == "ar") {
				$language_id = 2;
			}*/
		$getAppConfig=getAppConfig();

		$query3 = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$query4 = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$vendor_info = DB::select('SELECT distinct(o.id) as order_id,vendors_infos.vendor_name, vendors.logo_image, vendors.contact_address, vendors.contact_email, o.created_date,o.order_status,order_status.name,payment_gateways_info.name as payment_gateway_name,o.outlet_id,vendors.id as vendor_id,o.order_key_formated,o.invoice_id, delivery_time_interval.start_time,delivery_time_interval.end_time,o.invoice_id
        FROM orders o
        left join vendors vendors on vendors.id = o.vendor_id
        left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
        left join outlets out on out.vendor_id = vendors.id
        left join order_status order_status on order_status.id = o.order_status
        left join payment_gateways payment_gateways on payment_gateways.id = o.payment_gateway_id
        left join payment_gateways_info payment_gateways_info on payment_gateways_info.payment_id = payment_gateways.id
        left join delivery_time_slots on delivery_time_slots.id =o.delivery_slot
        left join delivery_time_interval on delivery_time_interval.id = delivery_time_slots.time_interval_id
        where ' . $query3 . ' AND ' . $query4 . ' AND o.id = ? AND o.customer_id= ? ORDER BY o.id ', array($post_data['orderId'], $post_data['userId']));
		foreach ($vendor_info as $k => $v) {
			$logo_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
			if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $v->logo_image) && $v->logo_image != '') {
				$logo_image = url('/assets/admin/base/images/vendors/logos/' . $v->logo_image);
			}
			$vendor_info[$k]->logo_image = $logo_image;
			$vendor_info[$k]->start_time = ($v->start_time != '') ? $v->start_time : '';
			$vendor_info[$k]->end_time = ($v->end_time != '') ? $v->end_time : '';
		}
		//print_r($vendor_info);exit();

		$query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and p.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

		$wquery = '"weight_classes_infos"."lang_id" = (case when (select count(weight_classes_infos.lang_id) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and weight_classes.id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$oquery = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

		//$qry = 'oi.replacement_product_id = 0 or oi.replacement_product_id = null';
		//print_r($qry);exit;
		$order_items = DB::select('SELECT p.product_image, pi.description,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.id as order_id,o.invoice_id,pi.product_name,pi.description,o.coupon_amount,weight_classes_infos.title,weight_classes_infos.unit as unit_code,o.order_key_formated,p.weight,oi.replacement_product_id,oi.id,oi.additional_comments,oi.adjust_weight_qty,oi.pack_status,p.adjust_weight
        FROM orders o
        LEFT JOIN orders_info oi ON oi.order_id = o.id
        LEFT JOIN products p ON p.id = oi.item_id
        LEFT JOIN products_infos pi ON pi.id = p.id
        LEFT JOIN weight_classes ON weight_classes.id = p.weight_class_id
        LEFT JOIN weight_classes_infos ON weight_classes_infos.id = weight_classes.id
        where ' . $query . ' AND ' . $wquery . ' AND o.id = ? AND o.customer_id= ? ORDER BY oi.id', array($post_data['orderId'], $post_data['userId']));
		//        where ' . $query . ' AND ' . $wquery . ' AND o.id = ? AND o.customer_id= ? ORDER BY oi.id', array($post_data['orderId'], $post_data['userId']));

		//echo"<pre>";print_r($order_items);exit;
		foreach ($order_items as $key => $items) {
			$product_image = URL::asset('assets/front/' . Session::get('general')->theme . '/images/no_image.png');
			if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $items->product_image) && $items->product_image != '') {
				$product_image = url('/assets/admin/base/images/products/list/' . $items->product_image);
			}
			$invoic_pdf = url('/assets/front/' . Session::get('general')->theme . '/images/invoice/' . $items->invoice_id . '.pdf');
			$order_items[$key]->product_image = $product_image;
			$order_items[$key]->invoic_pdf = $invoic_pdf;
		}

		$reviews = DB::table('outlet_reviews')
			->selectRaw('count(outlet_reviews.order_id) as reviewStatus')
		//->where("outlet_reviews.outlet_id","=",$reviews->outlet_id)
			->where("outlet_reviews.order_id", "=", $post_data['orderId'])
			->where('outlet_reviews.customer_id', '=', $post_data['userId'])
			->first();

		$query2 = 'pgi.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and pg.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$oquery = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$delivery_details = DB::select('SELECT o.delivery_instructions,ua.address as user_contact_address,o.customer_id as user_id,ua.latitude as user_latitude,ua.longitude as user_longitude,pg.id as payment_gateway_id,pgi.name,o.total_amount,o.delivery_charge,o.service_tax,dti.start_time,end_time,o.created_date,o.delivery_date,o.order_type,out_infos.contact_address,out.latitude as outlet_latitude,out.longitude as outlet_longitude,o.coupon_amount, u.email,o.driver_ids,dr.ratings,tr.ratings as rating,dri.first_name as driverName,o.order_comments,o.salesperson_id,sals.name as salespersonName,o.replace,o.used_wallet_amount
	    FROM orders o LEFT JOIN user_address ua ON ua.id = o.delivery_address
	     LEFT JOIN users u ON u.id = ua.user_id
	    left join driver_reviews dr on dr.customer_id = o.customer_id
	    left join drivers dri on dri.id = o.driver_ids
	    left join salesperson sals on sals.id = o.salesperson_id
	    left join outlet_reviews tr on tr.customer_id = o.customer_id
	    left join payment_gateways pg on pg.id = o.payment_gateway_id
	    left join payment_gateways_info pgi on pgi.payment_id = pg.id
	    left join delivery_time_slots dts on dts.id=o.delivery_slot
	     left join delivery_time_interval dti on dti.id = dts.time_interval_id
	      left join outlets out on out.id = o.outlet_id
	      left join outlet_infos out_infos on out_infos.id = out.id where
       ' . $query2 . ' AND ' . $oquery . ' AND o.id = ? AND o.customer_id= ?', array($post_data['orderId'], $post_data['userId']));
		//print_r($delivery_details);exit;
		// $delivery_details = DB::select('SELECT o.delivery_instructions as deliveryInstructions,ua.address ,pg.id as paymentGatewayId,pgi.name,o.total_amount as totalAmount,o.delivery_charge as deliverCharge,o.service_tax as serviceTax,dti.start_time ,end_time,o.created_date as createdDate,o.delivery_date as deliveryDate,o.order_type as orderType,out_infos.contact_address as contactAddress,o.coupon_amount as couponAmount, u.email FROM orders o LEFT JOIN user_address ua ON ua.id = o.delivery_address  LEFT JOIN users u ON u.id = ua.user_id  left join payment_gateways pg on pg.id = o.payment_gateway_id left join payment_gateways_info pgi on pgi.payment_id = pg.id left join delivery_time_slots dts on dts.id=o.delivery_slot left join delivery_time_interval dti on dti.id = dts.time_interval_id left join outlets out on out.id = o.outlet_id left join outlet_infos out_infos on out_infos.id = out.id where '.$query2.' AND '.$oquery.' AND o.id = ? AND o.customer_id= ?',array($post_data['orderId'],$post_data['userId']));
		foreach ($delivery_details as $k => $v) {
			// print_r($v);exit;
			$delivery_details[$k]->start_time = ($v->start_time != '') ? $v->start_time : '';
			$delivery_details[$k]->end_time = ($v->end_time != '') ? $v->end_time : '';
			$delivery_details[$k]->user_contact_address = ($v->user_contact_address != '') ? $v->user_contact_address : '';
			$delivery_details[$k]->contact_address = ($v->contact_address != '') ? $v->contact_address : '';
			$delivery_details[$k]->email = ($v->email != '') ? $v->email : '';
			/*$sub_total = ($v->total_amount) - ($v->delivery_charge + $v->service_tax) + ($v->coupon_amount);
			$wallet_amt =isset($v->used_wallet_amount)?$v->used_wallet_amount:0;
			$sub_total = $sub_total + $wallet_amt;*/
			 $wallet_amt =isset($v->used_wallet_amount)?$v->used_wallet_amount:0;

            $sub_total = $v->total_amount - ($v->delivery_charge + $v->service_tax) + ($v->coupon_amount) +$wallet_amt;

			//print_r($sub_total);exit();
			$delivery_details[$k]->sub_total = $sub_total;
			$tax_amount = $sub_total * $v->service_tax / 100;
			$delivery_details[$k]->tax_amount = $tax_amount;
			$delivery_details[$k]->userId = $v->user_id;
			$delivery_details[$k]->driverId = isset($v->driver_ids) ? $v->driver_ids:"" ;
			//$delivery_details[$k]->used_wallet_amount = isset($v->used_wallet_amount) ? $v->used_wallet_amount:0 ;

			
		}
		//print_r($delivery_details);exit;
		$tracking_orders = array(1 => "Initiated",34 => "Sales Person Assigned",10 => "Processed", 18 => "Packed",31=>"Accepted",32=>"Arrived", 19 => "Dispatched", 12 => "Delivered", 11 => "Cancelled");
		$tracking_result = $mob_tracking_result = array();
		$t =$y= 0;
		$last_state = $mob_last_state = "";

		foreach ($tracking_orders as $key => $track) {
				
			/*$mob_tracking_result[$t]['text'] = $track;
			/*$mob_tracking_result[$t]['text'] = $track;
			$mob_tracking_result[$t]['process'] = "0";
			$mob_tracking_result[$t]['order_comments'] = "";
			$mob_tracking_result[$t]['date'] = "";
			*/
			$tracking_result[$key]['text'] = $track;
			$tracking_result[$key]['process'] = "0";
			$tracking_result[$key]['order_comments'] = "";
			$tracking_result[$key]['date'] = "";
			//print_r($key);echo"....";
			//echo"key";		print_r($key);

			//print_r( $post_data['orderId']);

			$check_status = DB::table('orders_log')
				->select('order_id', 'log_time', 'order_comments')
				->where('order_id', '=', $post_data['orderId'])
				->where('order_status', '=', $key)
				->first();

			//print_r($check_status);echo"......";
			
			if (count($check_status) > 0) {
				$last_state = $key;
				$tracking_result[$key]['process'] = "1";
				$tracking_result[$key]['orderComments'] = ($check_status->order_comments != '') ? $check_status->order_comments : '';
				$tracking_result[$key]['date'] = date('M j Y g:i A', strtotime($check_status->log_time));
				$mob_last_state = $t;
				
				//print_r($y);echo"......";
				$mob_tracking_result[$y]['text'] = $track;
				$mob_tracking_result[$y]['process'] = "1";
				$mob_tracking_result[$y]['orderComments'] = $check_status->order_comments;
				$mob_tracking_result[$y]['date'] = date('M j Y g:i A', strtotime($check_status->log_time));
				$mob_tracking_result[$y]['order_comments'] = "";

				$y++;
			}
			$t++;
		}//print_r($mob_tracking_result);exit;
		//exit;
		//prasanth edit
		$deliverynew = new \stdClass();

		//print_r($delivery_details);exit();
		$deliverynew->driverId = isset($delivery_details[0]->driverId) ? $delivery_details[0]->driverId:"" ;
		$deliverynew->driverName = isset($delivery_details[0]->drivername) ? $delivery_details[0]->drivername:"" ;
		$deliverynew->deliveryInstructions = $delivery_details[0]->delivery_instructions;
		$deliverynew->userContactAddress = $delivery_details[0]->user_contact_address;
		$deliverynew->paymentGatewayId = $delivery_details[0]->payment_gateway_id;
		$deliverynew->name = $delivery_details[0]->name;
		$deliverynew->totalAmount = $delivery_details[0]->total_amount;
		$deliverynew->deliveryCharge = $delivery_details[0]->delivery_charge;
		$deliverynew->serviceTax = $delivery_details[0]->service_tax;
		$deliverynew->startTime = $delivery_details[0]->start_time;
		$deliverynew->endTime = $delivery_details[0]->end_time;
		$deliverynew->createdDate = $delivery_details[0]->created_date;
		$deliverynew->deliveryDate = $delivery_details[0]->delivery_date;
		$deliverynew->orderType = $delivery_details[0]->order_type;
		$deliverynew->contactAddress = $delivery_details[0]->contact_address;
		$deliverynew->couponAmount = $delivery_details[0]->coupon_amount;
		$deliverynew->email = $delivery_details[0]->email;
		$deliverynew->subTotal = $delivery_details[0]->sub_total;
		$deliverynew->taxAmount = $delivery_details[0]->tax_amount;
		$deliverynew->userLatitude = $delivery_details[0]->user_latitude;
		$deliverynew->userLongitude = $delivery_details[0]->user_longitude;
		$deliverynew->outletLatitude = $delivery_details[0]->outlet_latitude;
		$deliverynew->outletLongitude = $delivery_details[0]->outlet_longitude;
		$deliverynew->userId = $delivery_details[0]->userId;
		$deliverynew->driverRating = isset($delivery_details[0]->ratings) ? $delivery_details[0]->ratings:"" ;
		$deliverynew->orderRating = isset($delivery_details[0]->rating) ? $delivery_details[0]->rating:"" ;
		$deliverynew->replace = isset($delivery_details[0]->replace) ? $delivery_details[0]->replace:"" ;
		$deliverynew->walletAmountUsed = isset($delivery_details[0]->used_wallet_amount) ? $delivery_details[0]->used_wallet_amount:0 ;
		// isset($user_data->last_name) ? $user_data->last_name : "",
		$produceInfo = array();
		$k =0;
		//print_r($order_items);exit();
		foreach ($order_items as $ke => $data) {
			/*if ($data->replacement_product_id == 0 || $data->replacement_product_id == null) {    

				//echo"<pre>";print_r($data);exit();
				$produceInfo[$k]['productImage'] = $data->product_image;
				$produceInfo[$k]['description'] = $data->description;
				$produceInfo[$k]['productId'] = $data->product_id;
				$produceInfo[$k]['discountPrice'] = $data->item_cost;
				$produceInfo[$k]['orderUnit'] = $data->item_unit;
				$produceInfo[$k]['itemOffer'] = $data->item_offer;

				$amount = 0;
				$amount += $data->item_cost ;
				$amount *= $data->item_unit ;


				$produceInfo[$k]['totalAmount'] = $amount;
				$produceInfo[$k]['deliveryCharge'] = $data->delivery_charge;
				$produceInfo[$k]['serviceTax'] = $data->service_tax;
				$produceInfo[$k]['orderId'] = $data->order_id;
				$produceInfo[$k]['invoiceId'] = $data->invoice_id;
				$produceInfo[$k]['productName'] = $data->product_name;
				$produceInfo[$k]['couponAmount'] = $data->coupon_amount;
				$produceInfo[$k]['title'] = $data->title;
				$produceInfo[$k]['unitCode'] = $data->unit_code;
				$produceInfo[$k]['orderKeyFormated'] = $data->order_key_formated;
				$produceInfo[$k]['weight'] = $data->weight;
				$produceInfo[$k]['invoicePdf'] = $data->invoic_pdf;
				$produceInfo[$k]['replacement'] = "";
				
				$weight = isset($data->weight)?$data->weight:$data->weight;
                $produceInfo[$k]['weight'] =$weight;
                $adjust_weight_qty= isset($data->adjust_weight_qty)?$data->adjust_weight_qty:"";
                $weight_last = !empty($data->adjust_weight_qty)?$data->adjust_weight_qty:$data->weight;
                $weight_last = !empty($data->adjust_weight_qty)?$data->adjust_weight_qty:$data->weight;
				if ($data->adjust_weight == 1) {
                    $qntyweight = $weight * $data->item_unit ;
                    $weight_last = $adjust_weight_qty;
                } else {
                    $weight_last =$weight_last *$data->item_unit;
                }
                $itemprice =  $data->item_cost / $data->weight;
                $amount =$weight_last * $itemprice;
                if($deliverynew->replace = 2)
                {
                  $amount =  $amount;
                }

                if($amount !=0){$amounts = $amount;}else{$amounts= $amount;}
                print_r($amounts);exit();
                $produceInfo[$k]['totalAmount'] = $amounts;
                $produceInfo[$k]['adjustmentWeight'] = $adjust_weight_qty;
                $produceInfo[$k]['adjust'] =0 ;
                if ($data->adjust_weight_qty !=0 || $data->adjust_weight_qty !=null) {
                    $produceInfo[$k]['adjust'] = 1;
                }
				/*if($data->replacement_product_id != 0  || $data->replacement_product_id != null) {

					$order_list = DB::table('orders_info')
						->select('products.product_url')
						->Join('products', 'products.id', '=', 'orders_info.item_id')
						->where('orders_info.id', '=', $data->replacement_product_id)
						->get();
					$produceInfo[$k]['replacement'] =$order_list[0]->product_url;

				}/
			$k++;


			}*/
			    if ($data->replacement_product_id == 0 || $data->replacement_product_id == null) {    

                $produceInfo[$k]['id'] = $data->id;
                $produceInfo[$k]['productImage'] = $data->product_image;
                $produceInfo[$k]['description'] = $data->description;
                $produceInfo[$k]['productId'] = $data->product_id;
                $produceInfo[$k]['discountPrice'] = $data->item_cost;
                $produceInfo[$k]['itemOffer'] = $data->item_offer;
                $produceInfo[$k]['deliveryCharge'] = $data->delivery_charge;
                $produceInfo[$k]['serviceTax'] = $data->service_tax;
                $produceInfo[$k]['orderId'] = $data->order_id;
                $produceInfo[$k]['replacement'] =  isset($data->additional_comments)?$data->additional_comments:"";
                $produceInfo[$k]['packedStage'] =  isset($data->pack_status)?$data->pack_status:0;
                $produceInfo[$k]['adjust_show'] =  isset($data->adjust_weight)?$data->adjust_weight:0;
                //  $produceInfo[$k]['replacement_id'] = $data->replacement_product_id;
            
         
                $order_info=DB::select("select SUM(item_unit) as item_unit from orders_info where order_id = $data->order_id and item_id=$data->product_id");
                if (count($order_info)>0) {
                    $orderInfoArray=array();

                    foreach ($order_info as $keys => $values) {
                        $orderInfoArray[$keys]['itemCount']= $values->item_unit;
                    }
                }
                        
  
                //$produceInfo[$k]['orderUnit'] = $values->item_unit;
                $produceInfo[$k]['orderUnit'] = $data->item_unit;

               // print_r($data->item_cost * $data->item_unit);echo"<br>";


                $sum= DB::select("select   (item_cost * item_unit) as total  from orders_info where order_id = $data->order_id and item_id=$data->product_id");
                // print_r($sum);echo"<br>";//exit;

                if (count($sum)>0) {
                    $sumArray=array();

                    foreach ($sum as $ke => $valu) {
                        $sumArray[$ke]['total']= $valu->total;
                    }
                }

                $valu->total = $data->item_cost * $data->item_unit;

                $produceInfo[$k]['totalAmount'] = $valu->total;
                $produceInfo[$k]['invoiceId'] = $data->invoice_id;
                $produceInfo[$k]['productName'] = $data->product_name;
                $produceInfo[$k]['couponAmount'] = $data->coupon_amount;
                $produceInfo[$k]['title'] = $data->title;
                $produceInfo[$k]['unitCode'] = $data->unit_code;
                $produceInfo[$k]['orderKeyFormated'] = $data->order_key_formated;
                $produceInfo[$k]['weight'] = $data->weight;
                $produceInfo[$k]['invoicePdf'] = $data->invoic_pdf;
         
                $weight = isset($data->weight)?$data->weight:$data->weight;
                $produceInfo[$k]['weight'] =$weight;
                $adjust_weight_qty= isset($data->adjust_weight_qty)?$data->adjust_weight_qty:"";
                $weight_last = !empty($data->adjust_weight_qty)?$data->adjust_weight_qty:$data->weight;
                $weight_last = !empty($data->adjust_weight_qty)?$data->adjust_weight_qty:$data->weight;
                if ($data->adjust_weight == 1) {
                   /* $qntyweight = $weight * $values->item_unit ;
                    $produceInfo[$k]['weight'] = $qntyweight;
                    $weight_last = $qntyweight+$adjust_weight_qty;*/
                    $qntyweight = $weight * $values->item_unit ;
                    //$produceInfo[$k]['weight'] = $adjust_weight_qty;
                    $weight_last = $adjust_weight_qty;
                } else {
                    $weight_last =$weight_last *$values->item_unit;
                }
                $itemprice =  $data->item_cost / $data->weight;
                $amount =$weight_last * $itemprice;
                               // print_r($amount);exit;

                

                if($amount !=0){$amounts = $amount;}else{$amounts= $valu->total;}
                $produceInfo[$k]['totalAmount'] = $amounts;
                $produceInfo[$k]['adjustmentWeight'] = $adjust_weight_qty;
                $produceInfo[$k]['adjust'] =0 ;
                if ($data->adjust_weight_qty !=0 || $data->adjust_weight_qty !=null) {
                    $produceInfo[$k]['adjust'] = 1;
                }
            $k++;
            }
		}

		$orderData = new \stdClass();
		$orderData->orderId = $vendor_info[0]->order_id;
		$orderData->outletName = $vendor_info[0]->vendor_name;

		$orderId=$post_data['orderId'];
		$sum= DB::select("select   sum(item_unit) as total  from orders_info where order_id =$orderId  ");
		//print_r($sum);exit();

        if (count($sum)>0) {

            $sumArray=array();

        foreach ($sum as $ke => $valu) {

                $sumArray[$ke]['total']= $valu->total;

                }

         }

		
		$orderData->orderQuantity = $valu->total;
		$orderData->orderComments = isset($delivery_details[0]->order_comments)?$delivery_details[0]->order_comments:"";
		$orderData->salesFleetId = isset($delivery_details[0]->salesperson_id) ? $delivery_details[0]->salesperson_id:"" ;
		$orderData->salesFleetName = isset($delivery_details[0]->salespersonname) ? $delivery_details[0]->salespersonname:"";
		$orderData->vendorLogo = $vendor_info[0]->logo_image;
		$orderData->outletAddress = $vendor_info[0]->contact_address;
		$orderData->contactEmail = $vendor_info[0]->contact_email;
		$orderData->createdDate = $vendor_info[0]->created_date;
		$orderData->orderStatus = $vendor_info[0]->order_status;
		$orderData->name = $vendor_info[0]->name;
		$orderData->paymentGatewayName = $vendor_info[0]->payment_gateway_name;
		$orderData->outletId = $vendor_info[0]->outlet_id;
		$orderData->vendorId = $vendor_info[0]->vendor_id;

		$orderData->orderKeyFormated = $vendor_info[0]->order_key_formated;
		$orderData->invoiceId = $vendor_info[0]->invoice_id;
		$orderData->startTime = $vendor_info[0]->start_time;
		$orderData->endTime = $vendor_info[0]->end_time;

		$orderData->deliveryAddress = $delivery_details[0]->user_contact_address;

		$return_reasons = $this->return_reason($language_id);
		$mob_return_reasons = $this->mob_return_reason($language_id);
		$result = array("response" => array("status" => 2, "message" => "no items found", "order_items" => array(), "deliveryDetails" => array(), "return_reasons" => $return_reasons, "tracking_result" => $tracking_result, "lastState" => $last_state, "reviews" => $reviews));
		if (count($order_items) > 0 && count($delivery_details) > 0 && count($vendor_info) > 0) {
			$result = array("status" => 1, "message" => "order items", "orderProductList" => $produceInfo, "deliveryDetails" => $deliverynew, "orderData" => $orderData, "return_reasons" => $return_reasons, "tracking_result" => $tracking_result, "lastState" => $last_state, "trackData" => $mob_tracking_result, "mob_return_reasons" => $mob_return_reasons, "reviews" => $reviews, "order_id_encrypted" => encrypt($post_data['orderId']),'adminPhone'=>$getAppConfig->telephone); //, "mob_delivery_details" => $delivery
		}
		return json_encode($result);
	}

	public function morder_detail(Request $data) {
		$post_data = $data->all();
		App::setLocale('en');
		$language_id = 1;
        $rules = array(
            'orderId' => 'required',
            'userId' => 'required',
        );
        if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 0, "message" => trans('messages.Error List'), "detail" => $errors);
        } else {
            $details = orderdetails($post_data['orderId'],'','',$language_id,$post_data['userId'],1);
            //print_r($details);exit();
			$return_reasons = $this->return_reason($language_id);
			$mob_return_reasons = $this->mob_return_reason($language_id);
			$result = array("response" => array("status" => 2, "message" => "no items found", "order_items" => array(), "deliveryDetails" => array(), "return_reasons" => $return_reasons, "tracking_result" => $details['tracking_result'], "lastState" => $details['last_state'], "reviews" => $details['reviews']));
            if (count($details['produceInfo']) > 0 && count($details['deliverynew']) > 0) {
				$result = array("status" => 1, "message" => "order items", "orderProductList" => $details['produceInfo'], "deliveryDetails" => $details['deliverynew'], "orderData" => $details['orderData'], "return_reasons" => $return_reasons, "tracking_result" => $details['tracking_result'], "lastState" => $details['last_state'], "trackData" => $details['mob_tracking_result'], "mob_return_reasons" => $mob_return_reasons, "reviews" => $details['reviews'], "order_id_encrypted" => encrypt($post_data['orderId'])); //, "mob_delivery_details" => $delivery
			}
		}
		return json_encode($result);
	}


	



	public function morderDetail(Request $data) {
		$post_data = $data->all();
		//print_r($post_data);exit;
		App::setLocale('en');
		/*	if ($post_data['lang'] == 2) {
				App::setLocale('ar');
			}*/
			// $language_id = $post_data['language'];
			$language_id = 1;
		/*	if ($post_data['lang'] == "ar") {
				$language_id = 2;
			}*/


		// 		$outletId=$post_data['outletId'];
		// 		$vendorId=$post_data['vendorId'];
		// 		$orderId=$post_data['orderId'];
		// 		$userId=$post_data['userId'];

		// if (($outletId =="") AND ($vendorId =="") AND ($orderId !=="") AND ($userId!=="") ){

		  //               $raw  = 'orders.id = '.$orderId ;
		  //               $raw  = 'orders.customer_id = '.$userId ;
		  //               $raw  = 'ORDER BY o.id' ;


		  //        }
				// elseif(){

				// }

		$query3 = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$query4 = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$vendor_info = DB::select('SELECT distinct(o.id) as order_id,vendors_infos.vendor_name, vendors.logo_image, vendors.contact_address, vendors.contact_email, o.created_date,o.order_status,order_status.name,payment_gateways_info.name as payment_gateway_name,o.outlet_id,vendors.id as vendor_id,o.order_key_formated,o.invoice_id, delivery_time_interval.start_time,delivery_time_interval.end_time,o.invoice_id
        FROM orders o
        left join vendors vendors on vendors.id = o.vendor_id
        left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
        left join outlets out on out.vendor_id = vendors.id
        left join order_status order_status on order_status.id = o.order_status
        left join payment_gateways payment_gateways on payment_gateways.id = o.payment_gateway_id
        left join payment_gateways_info payment_gateways_info on payment_gateways_info.payment_id = payment_gateways.id
        left join delivery_time_slots on delivery_time_slots.id =o.delivery_slot
        left join delivery_time_interval on delivery_time_interval.id = delivery_time_slots.time_interval_id
        where ' . $query3 . ' AND ' . $query4 .' AND o.id = ? AND o.outlet_id = ? AND o.vendor_id = ? ORDER BY o.id ', array($post_data['orderId'], $post_data['outletId'], $post_data['vendorId']));

        /*where ' . $query3 . ' AND ' . $query4 . ' AND o.id = ? AND o.customer_id= ? ORDER BY o.id ', array($post_data['orderId'], $post_data['userId']));*/

      //  print_r($vendor_info);exit();

		foreach ($vendor_info as $k => $v) {
			$logo_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
			if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $v->logo_image) && $v->logo_image != '') {
				$logo_image = url('/assets/admin/base/images/vendors/logos/' . $v->logo_image);
			}
			$vendor_info[$k]->logo_image = $logo_image;
			$vendor_info[$k]->start_time = ($v->start_time != '') ? $v->start_time : '';
			$vendor_info[$k]->end_time = ($v->end_time != '') ? $v->end_time : '';
		}

		/*$query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and p.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

		$wquery = '"weight_classes_infos"."lang_id" = (case when (select count(weight_classes_infos.lang_id) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and weight_classes.id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$oquery = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$order_items = DB::select('SELECT p.product_image, pi.description,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.id as order_id,o.invoice_id,pi.product_name,pi.description,o.coupon_amount,weight_classes_infos.title,weight_classes_infos.unit as unit_code,o.order_key_formated,p.weight
        FROM orders o
        LEFT JOIN orders_info oi ON oi.order_id = o.id
        LEFT JOIN products p ON p.id = oi.item_id
        LEFT JOIN products_infos pi ON pi.id = p.id
        LEFT JOIN weight_classes ON weight_classes.id = p.weight_class_id
        LEFT JOIN weight_classes_infos ON weight_classes_infos.id = weight_classes.id
        where ' . $query . ' AND ' . $wquery . ' AND o.id = ? AND o.customer_id= ? ORDER BY oi.id', array($post_data['orderId'], $post_data['userId']));*/



		$query = 'p.lang_id = (case when (select count(*) as totalcount from admin_products where admin_products.lang_id = '.$language_id.' and op.product_id = admin_products.id) > 0 THEN '.$language_id.' ELSE 1 END)';

		$wquery = '"weight_classes_infos"."lang_id" = (case when (select count(weight_classes_infos.lang_id) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and weight_classes.id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$oquery = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

		$order_items =DB::select('SELECT o.total_amount,
										o.delivery_charge,
										o.service_tax,
										o.id as order_id,
										o.invoice_id,
										o.coupon_amount,
										o.order_key_formated,
										oi.item_cost,
										oi.item_unit,
										oi.item_offer,
										oi.replacement_product_id,
										oi.id,
										oi.additional_comments,
										oi.adjust_weight_qty,
										oi.pack_status,
										p.description,
										p.product_name,
										p.image	 AS product_image,
										p.weight_class_id,
										p.weight,
										p.adjust_weight,
						    			p.id AS product_id,
										weight_classes_infos.title,
										weight_classes_infos.unit as unit_code
								  FROM orders o
								  LEFT JOIN orders_info oi ON oi.order_id = o.id 
								  LEFT JOIN admin_products p ON p.id = oi.item_id
								  LEFT JOIN outlet_products op ON op.product_id = oi.item_id

								  LEFT JOIN weight_classes ON weight_classes.id = p.weight_class_id
								  LEFT JOIN weight_classes_infos ON weight_classes_infos.id = weight_classes.id
        						  where ' . $query . ' AND ' . $wquery . ' AND o.id = ? AND o.customer_id= ? ORDER BY oi.id', array($post_data['orderId'], $post_data['userId']));


		//print_r($order_items);exit;
		foreach ($order_items as $key => $items) {
			/*$product_image = URL::asset('assets/front/' . Session::get('general')->theme . '/images/no_image.png');
			if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $items->product_image) && $items->product_image != '') {
				$product_image = url('/assets/admin/base/images/products/list/' . $items->product_image);
			}
			$invoic_pdf = url('/assets/front/' . Session::get('general')->theme . '/images/invoice/' . $items->invoice_id . '.pdf');
			$order_items[$key]->product_image = $product_image;*/

			$no_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
			$path = url('/assets/admin/base/images/products/admin_products/');
            $productImage=json_decode($items->product_image);
            $image1 =array();
            $image1[]= $no_image;
            if($productImage != "")
            {           

                foreach ($productImage as $keys => $valuess) {
                	if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $valuess) && $valuess!= '') {
                        $image1[] =$path.'/'.$valuess;
                    }
                }
            }

			$order_items[$key]->product_image = $image1;

			$order_items[$key]->invoic_pdf = $invoic_pdf;
		}

		$reviews = DB::table('outlet_reviews')
			->selectRaw('count(outlet_reviews.order_id) as reviewStatus')
		//->where("outlet_reviews.outlet_id","=",$reviews->outlet_id)
			->where("outlet_reviews.order_id", "=", $post_data['orderId'])
			->where('outlet_reviews.customer_id', '=', $post_data['userId'])
			->first();

		$query2 = 'pgi.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and pg.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$oquery = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$delivery_details = DB::select('SELECT o.delivery_instructions,ua.address as user_contact_address,o.customer_id as user_id,ua.latitude as user_latitude,ua.longitude as user_longitude,pg.id as payment_gateway_id,pgi.name,o.total_amount,o.delivery_charge,o.service_tax,dti.start_time,end_time,o.created_date,o.delivery_date,o.order_type,out_infos.contact_address,out.latitude as outlet_latitude,out.longitude as outlet_longitude,o.coupon_amount, u.email,o.driver_ids,dr.ratings,tr.ratings as rating
	    FROM orders o LEFT JOIN user_address ua ON ua.id = o.delivery_address
	     LEFT JOIN users u ON u.id = ua.user_id
	    left join driver_reviews dr on dr.customer_id = o.customer_id
	    left join outlet_reviews tr on tr.customer_id = o.customer_id
	    left join payment_gateways pg on pg.id = o.payment_gateway_id
	    left join payment_gateways_info pgi on pgi.payment_id = pg.id
	    left join delivery_time_slots dts on dts.id=o.delivery_slot
	     left join delivery_time_interval dti on dti.id = dts.time_interval_id
	      left join outlets out on out.id = o.outlet_id
	      left join outlet_infos out_infos on out_infos.id = out.id where
	       ' . $query2 . ' AND ' . $oquery . ' AND o.id = ? AND o.customer_id= ?', array($post_data['orderId'], $post_data['userId']));
			//print_r($delivery_details);exit;
			// $delivery_details = DB::select('SELECT o.delivery_instructions as deliveryInstructions,ua.address ,pg.id as paymentGatewayId,pgi.name,o.total_amount as totalAmount,o.delivery_charge as deliverCharge,o.service_tax as serviceTax,dti.start_time ,end_time,o.created_date as createdDate,o.delivery_date as deliveryDate,o.order_type as orderType,out_infos.contact_address as contactAddress,o.coupon_amount as couponAmount, u.email FROM orders o LEFT JOIN user_address ua ON ua.id = o.delivery_address  LEFT JOIN users u ON u.id = ua.user_id  left join payment_gateways pg on pg.id = o.payment_gateway_id left join payment_gateways_info pgi on pgi.payment_id = pg.id left join delivery_time_slots dts on dts.id=o.delivery_slot left join delivery_time_interval dti on dti.id = dts.time_interval_id left join outlets out on out.id = o.outlet_id left join outlet_infos out_infos on out_infos.id = out.id where '.$query2.' AND '.$oquery.' AND o.id = ? AND o.customer_id= ?',array($post_data['orderId'],$post_data['userId']));
			foreach ($delivery_details as $k => $v) {
				// print_r($v);exit;
				$delivery_details[$k]->start_time = ($v->start_time != '') ? $v->start_time : '';
				$delivery_details[$k]->end_time = ($v->end_time != '') ? $v->end_time : '';
				$delivery_details[$k]->user_contact_address = ($v->user_contact_address != '') ? $v->user_contact_address : '';
				$delivery_details[$k]->contact_address = ($v->contact_address != '') ? $v->contact_address : '';
				$delivery_details[$k]->email = ($v->email != '') ? $v->email : '';
				$sub_total = ($v->total_amount) - ($v->delivery_charge + $v->service_tax) - ($v->coupon_amount);
				//print_r($sub_total);exit();
			$delivery_details[$k]->sub_total = $sub_total;
			$tax_amount = $sub_total * $v->service_tax / 100;
			$delivery_details[$k]->tax_amount = $tax_amount;
			$delivery_details[$k]->userId = $v->user_id;
			$delivery_details[$k]->driverId = $deliverynew->driverId = isset($v->driver_ids) ? $v->driver_ids:"" ;
			
		}
		//print_r($delivery_details);exit;
		$tracking_orders = array(1 => "Initiated", 10 => "Processed", 18 => "Packed", 19 => "Dispatched", 12 => "Delivered");
		$tracking_result = $mob_tracking_result = array();
		$t =$y= 0;
		$last_state = $mob_last_state = "";
		foreach ($tracking_orders as $key => $track) {
				
		/*	$mob_tracking_result[$t]['text'] = $track;
			$mob_tracking_result[$t]['process'] = "0";
			$mob_tracking_result[$t]['order_comments'] = "";
			$mob_tracking_result[$t]['date'] = "";
			*/
			$tracking_result[$key]['text'] = $track;
			$tracking_result[$key]['process'] = "0";
			$tracking_result[$key]['order_comments'] = "";
			$tracking_result[$key]['date'] = "";
			//print_r($key);echo"....";
			$check_status = DB::table('orders_log')
				->select('order_id', 'log_time', 'order_comments')
				->where('order_id', '=', $post_data['orderId'])
				->where('order_status', '=', $key)
				->first();

			//print_r($key);echo"......";
			
			if (count($check_status) > 0) {
				$last_state = $key;
				$tracking_result[$key]['process'] = "1";
				$tracking_result[$key]['orderComments'] = ($check_status->order_comments != '') ? $check_status->order_comments : '';
				$tracking_result[$key]['date'] = date('M j Y g:i A', strtotime($check_status->log_time));
				$mob_last_state = $t;
				
				//print_r($y);echo"......";
				$mob_tracking_result[$y]['text'] = $track;
				$mob_tracking_result[$y]['process'] = "1";
				$mob_tracking_result[$y]['orderComments'] = $check_status->order_comments;
				$mob_tracking_result[$y]['date'] = date('M j Y g:i A', strtotime($check_status->log_time));
				$mob_tracking_result[$y]['order_comments'] = "";

				$y++;
			}
			$t++;
		}//print_r($mob_tracking_result);exit;
		//exit;
		//prasanth edit
		$deliverynew = new \stdClass();
		$deliverynew->driverId = isset($delivery_details[0]->driverId) ? $delivery_details[0]->driverId:"" ;
		$deliverynew->deliveryInstructions = $delivery_details[0]->delivery_instructions;
		$deliverynew->userContactAddress = $delivery_details[0]->user_contact_address;
		$deliverynew->paymentGatewayId = $delivery_details[0]->payment_gateway_id;
		$deliverynew->name = $delivery_details[0]->name;
		$deliverynew->totalAmount = $delivery_details[0]->total_amount;
		$deliverynew->deliveryCharge = $delivery_details[0]->delivery_charge;
		$deliverynew->serviceTax = $delivery_details[0]->service_tax;
		$deliverynew->startTime = $delivery_details[0]->start_time;
		$deliverynew->endTime = $delivery_details[0]->end_time;
		$deliverynew->createdDate = $delivery_details[0]->created_date;
		$deliverynew->deliveryDate = $delivery_details[0]->delivery_date;
		$deliverynew->orderType = $delivery_details[0]->order_type;
		$deliverynew->contactAddress = $delivery_details[0]->contact_address;
		$deliverynew->couponAmount = $delivery_details[0]->coupon_amount;
		$deliverynew->email = $delivery_details[0]->email;
		$deliverynew->subTotal = $delivery_details[0]->sub_total;
		$deliverynew->taxAmount = $delivery_details[0]->tax_amount;
		$deliverynew->userLatitude = $delivery_details[0]->user_latitude;
		$deliverynew->userLongitude = $delivery_details[0]->user_longitude;
		$deliverynew->outletLatitude = $delivery_details[0]->outlet_latitude;
		$deliverynew->outletLongitude = $delivery_details[0]->outlet_longitude;
		$deliverynew->userId = $delivery_details[0]->userId;
		$deliverynew->driverRating = isset($delivery_details[0]->ratings) ? $delivery_details[0]->ratings:"" ;
		$deliverynew->orderRating = isset($delivery_details[0]->rating) ? $delivery_details[0]->rating:"" ;
		// isset($user_data->last_name) ? $user_data->last_name : "",
		$produceInfo = array();
		foreach ($order_items as $k => $data) {
			$produceInfo[$k]['productImage'] = $data->product_image;
			$produceInfo[$k]['description'] = $data->description;
			$produceInfo[$k]['productId'] = $data->product_id;
			$produceInfo[$k]['discountPrice'] = $data->item_cost;
			$produceInfo[$k]['orderUnit'] = $data->item_unit;
			$produceInfo[$k]['itemOffer'] = $data->item_offer;
			$produceInfo[$k]['totalAmount'] = $data->total_amount;
			$produceInfo[$k]['deliveryCharge'] = $data->delivery_charge;
			$produceInfo[$k]['serviceTax'] = $data->service_tax;
			$produceInfo[$k]['orderId'] = $data->order_id;
			$produceInfo[$k]['invoiceId'] = $data->invoice_id;
			$produceInfo[$k]['productName'] = $data->product_name;
			$produceInfo[$k]['couponAmount'] = $data->coupon_amount;
			$produceInfo[$k]['title'] = $data->title;
			$produceInfo[$k]['unitCode'] = $data->unit_code;
			$produceInfo[$k]['orderKeyFormated'] = $data->order_key_formated;
			$produceInfo[$k]['weight'] = $data->weight;
			$produceInfo[$k]['invoicePdf'] = $data->invoic_pdf;

		}

		$orderData = new \stdClass();
		$orderData->orderId = $vendor_info[0]->order_id;
		$orderData->outletName = $vendor_info[0]->vendor_name;
		$orderData->vendorLogo = $vendor_info[0]->logo_image;
		$orderData->outletAddress = $vendor_info[0]->contact_address;
		$orderData->contactEmail = $vendor_info[0]->contact_email;
		$orderData->createdDate = $vendor_info[0]->created_date;
		$orderData->orderStatus = $vendor_info[0]->order_status;
		$orderData->name = $vendor_info[0]->name;
		$orderData->paymentGatewayName = $vendor_info[0]->payment_gateway_name;
		$orderData->outletId = $vendor_info[0]->outlet_id;
		$orderData->vendorId = $vendor_info[0]->vendor_id;

		$orderData->orderKeyFormated = $vendor_info[0]->order_key_formated;
		$orderData->invoiceId = $vendor_info[0]->invoice_id;
		$orderData->startTime = $vendor_info[0]->start_time;
		$orderData->endTime = $vendor_info[0]->end_time;

		$orderData->deliveryAddress = $delivery_details[0]->user_contact_address;

		$return_reasons = $this->return_reason($language_id);
		$mob_return_reasons = $this->mob_return_reason($language_id);
		$result = array("response" => array("status" => 2, "message" => "no items found", "order_items" => array(), "deliveryDetails" => array(), "return_reasons" => $return_reasons, "tracking_result" => $tracking_result, "lastState" => $last_state, "reviews" => $reviews));
		if (count($order_items) > 0 && count($delivery_details) > 0 && count($vendor_info) > 0) {
			$result = array("status" => 1, "message" => "order items", "orderProductList" => $produceInfo, "deliveryDetails" => $deliverynew, "orderData" => $orderData, "return_reasons" => $return_reasons, "tracking_result" => $tracking_result, "lastState" => $last_state, "trackData" => $mob_tracking_result, "mob_return_reasons" => $mob_return_reasons, "reviews" => $reviews, "order_id_encrypted" => encrypt($post_data['orderId'])); //, "mob_delivery_details" => $delivery
		}
		return json_encode($result);
	}



	//Ram: 03/10/2019
	public function mcashIn(Request $data)
	{

		$post_data = $data->all();
		if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }	
		$rules = [
			
			'language' => ['required'],
			'outletId' => ['required'],
			'orderId' => ['required'],
		];
		$errors = $result = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array( "status" => 0,  "message" => trans("messages.Error List"), "detail" => $errors);
		} else {

            $outletId=$post_data['outletId'];

            $orderId=$post_data['orderId'];

            $updateStaus=DB::table('orders')
                    			->where('orders.id',$orderId) 
                    			->update(['order_status'=> 33]) ; 

			

            	$result = array("status" => 1, "message" => trans("messages.Order Status updated successfully"));


		}
		        return json_encode($result, JSON_UNESCAPED_UNICODE);

	}


    /*public function orderReturn(Request $data)
    {
    	$post_data = $data->all();
    //	print_r($post_data);exit;
		
		$rules = [
			
			'language' => ['required'],
			'outletId' => ['required'],
			'orderId' => ['required'],
			'reason' => ['required'],
		];
		$errors = $result = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array( "status" => 0,  "message" => trans("messages.Error List"), "detail" => $errors);
		} else {
			if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
	            App::setLocale($post_data['language']);
	        } else {
	            App::setLocale('en');
	        }	

			//print_r($post_data);exit;
		}

        
    }*/

    /*public function offlinepayment(Request $data)
	{

		$post_data = $data->all();
		//print_r($post_data);exit;
		if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }	
		$current_date = strtotime(date('Y-m-d'));
	
		$abc = $post_data['cartDetails'];
		$cart_detailsb = json_encode($abc);
		$cart_details = json_decode($cart_detailsb);

		//print_r( $cart_details);exit;
		$rules = array();
	
		$validation = app('validator')->make($post_data, $rules);
		// process the validation
		if ($validation->fails()) {
			foreach ($validation->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "status" => false, "Message" => $errors, "Error" => trans("messages.Error List")));
		} else {
			$total_amt = $cart_details->total;
			
			$coupon_details = isset($cart_details->coupon_details)?$cart_details->coupon_details:'';

			$coupon_id = (isset($coupon_details->coupon_id) && $coupon_details->coupon_id != "") ? $coupon_details->coupon_id : 0;
			$coupon_amount = (isset($coupon_details->offer_amount) && $coupon_details->offer_amount != "") ? $coupon_details->offer_amount : 0;
			$coupon_type = (isset($coupon_details->offer_type) && $coupon_details->offer_type != "") ? $coupon_details->offer_type : 0;

			if ($coupon_id != 0) {
				$coupon_details = DB::table('coupons')
					->select('coupons.id as coupon_id', 'coupon_type', 'offer_amount', 'coupon_code', 'start_date', 'end_date')
					->leftJoin('coupon_outlet', 'coupon_outlet.coupon_id', '=', 'coupons.id')
					->where('coupons.id', '=', $payment_array->coupon_id)
					->where('coupon_outlet.outlet_id', '=', $payment_array->outlet_id)
					->first();
				if (count($coupon_details) == 0) {
					$result = array("httpCode" => 400, "Message" => "No coupons found");
					return json_encode($result);
				} else if ((strtotime($coupon_details->start_date) <= $current_date) && (strtotime($coupon_details->end_date) >= $current_date)) {
					$coupon_user_limit_details = DB::table('user_cart_limit')
						->select('cus_order_count', 'user_limit', 'total_order_count', 'coupon_limit')
						->where('customer_id', '=', $post_data['user_id'])
						->where('coupon_id', '=', $payment_array->coupon_id)
						->first();
					if (count($coupon_user_limit_details) > 0) {
						if ($coupon_user_limit_details->cus_order_count >= $coupon_user_limit_details->user_limit) {
							$result = array("httpCode" => 400, "Message" => "Max user limit has been crossed");
							return json_encode($result);
						}
						if ($coupon_user_limit_details->total_order_count >= $coupon_user_limit_details->coupon_limit) {
							$result = array("httpCode" => 400, "Message" => "Max coupon limit has been crossed");
							return json_encode($result);
						}
					}
				} else {
					$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.No coupons found")));
					return json_encode($result);
				}
				//$total_amt = $payment_array->total - $payment_array->coupon_amount;
			}
			$admin_commission = (($cart_details->sub_total * $cart_details->payment_gateway_details->commision) / 100);
			$vendor_commission = $cart_details->sub_total - $admin_commission;
			$vendor_commission = number_format($vendor_commission, 2, '.', '') ;
			$delivery_slot =isset($cart_details->delivery_slot) ? (int)$cart_details->delivery_slot : '';
			$delivery_cost= isset($cart_details->delivery_cost) ? $cart_details->delivery_cost : '';

			

			$order_id = DB::table('orders')->insertGetId([
				'customer_id' =>(int) isset($cart_details->userId)?$cart_details->userId:0,
				'vendor_id' => (int)$cart_details->vendorId,
				'total_amount' => (double)$total_amt,
				'created_date' => date("Y-m-d H:i:s"),
				'order_status' => 1,
				'coupon_id' => (int)$coupon_id,
				'coupon_amount' => (double)$coupon_amount,
				'coupon_type' => (string)$coupon_type,
				'service_tax' => (double)$cart_details->tax,
				'payment_status' => 0,
				'payment_gateway_commission' =>(double) number_format($cart_details->payment_gateway_details->commision, 2, '.', ''),
				'outlet_id' => (int)$cart_details->outletId,
				'delivery_instructions' => isset($post_data['delivery_instructions']) ? $post_data['delivery_instructions'] : '',
				'delivery_address' => isset($post_data['delivery_address']) ? $post_data['delivery_address'] : '',
				'payment_gateway_id' => (int)$cart_details->payment_gateway_details->id,
				'delivery_slot' => (int)$delivery_slot,
				'delivery_date' => isset($post_data['delivery_date']) ? $post_data['delivery_date'] : '',
				'delivery_charge' =>(double)$delivery_cost,
				'admin_commission' => (double)$admin_commission,
				'vendor_commission' => (double)$vendor_commission,
				'order_type' => (isset($post_data['order_type']) && ($post_data['order_type'] != "")) ? $post_data['order_type'] : 1
			]);


			$i=0;
			foreach ($cart_details->cart_items as $cartitems) {
				$items[$i]['product_id'] = $cartitems->product_id;
				$items[$i]['quantity'] = $cartitems->quantity;
				$items[$i]['discount_price'] = number_format(  $cartitems->discount_price, 2, '.', '');
				$items[$i]['item_offer'] = 0;
				$i++;
			}
			$update_orders = Orders::find($order_id);

			$update_orders->invoice_id = 'INV' . str_pad($order_id, 8, "0", STR_PAD_LEFT) . time();


			$update_orders->save();
			$order_key_formatted = "#OR" . $cart_details->cart_items[0]->vendor_key . $order_id;
			DB::update('update orders set order_key_formated = ? where id = ?', array($order_key_formatted, $order_id));

			DB::update('update users set current_balance = current_balance+? where id = ?', array($admin_commission, 1));

			DB::update('update vendors set current_balance = current_balance+? where id = ?', array($vendor_commission, $cart_details->vendorId));
			$items = $items;
			foreach ($items as $item) {
				$values = array('item_id' => $item['product_id'], 'item_cost' => $item['discount_price'], 'item_unit' => $item['quantity'], 'item_offer' => $item['item_offer'], 'order_id' => $order_id);
				DB::table('orders_info')->insert($values);
			}
			$language=isset($post_data['language'])?$post_data['language']:1;
			//$cc = getCurrency($language));
			$values = array('order_id' => $order_id,
				'customer_id' => $post_data['userId'],
				'vendor_id' => $cart_details->vendorId,
				'outlet_id' => $cart_details->outletId,
				'payment_status' => "SUCCESS",
				'payment_type' => "COD",
				'created_date' => date("Y-m-d H:i:s"),
				//'currency_code' => getCurrency($language);
				'currency_code' => "");

			DB::table('transaction')->insert($values);
			DB::update('delete from cart where user_id = ?', array($post_data['userId']));
			$result =array("httpCode" => 400, "Message" => "Something went wrong");

			if ($values) {
				$result = array("httpCode" => 200, "Message" => "Order initated success", "order_id" => $order_id);
				//Email notification to customer
				$this->send_order_email($order_id, $cart_details->userId, $post_data['language']);
					//Email notification to admin & vendor
				$this->send_order_email_admin_vendors($order_id, $cart_details->userId, $post_data['language']);
				//	print_r($post_data['userId']);exit();
				$users = Users::find($post_data['userId']);
				$order_title = 'Your order ' . $order_key_formatted . '  has been placed';
				$subject = 'Your order ' . $order_key_formatted . '  has been placed';

				$vendors = Vendors::find($cart_details->vendorId);

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
						->setClickAction('
    				com.app.jeebelycustomerapp.Activites.NotificationsActivity')
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
				$vendor_name= isset($cart_details->cart_items[0]->vendor_name)?$cart_details->cart_items[0]->vendor_name:'';

				//Internal Admin Notifications Storing with notifications
				$mess = "New Order Was Placed at " . $vendor_name;
				$values = array('order_id' => $order_id,
					'customer_id' => $post_data['userId'],
					'vendor_id' => $cart_details->vendorId,
					'outlet_id' => $cart_details->outletId,
					'message' => $mess,
					'read_status' => 0,
					'created_date' => date('Y-m-d H:i:s'));
				DB::table('notifications')->insert($values);
			}

		}
		return json_encode($result);

	}
*/


}
