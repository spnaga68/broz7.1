<?php

namespace App\Http\Controllers;
use App;
use App\Model\api;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use OpenGraph;
use SEOMeta;
use Session;
use Twitter;
use DB;
//use App\Http\Controllers\Api\Api;

class Usercart extends Controller {
	const USERS_SIGNUP_EMAIL_TEMPLATE = 1;
	const USERS_WELCOME_EMAIL_TEMPLATE = 3;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->user_details = array();
		$this->api = New Api;
		$this->client = new Client([
			// Base URI is used with relative requests
			'base_uri' => url('/'),
            // 'base_uri' => url('http://127.0.0.1/'),
			// You can set any number of default request options.
			'timeout' => 3000.0,
		]);
		$user_details = $this->check_login();
		$this->theme = Session::get("general")->theme;
	}

	public function check_login() {
		$user_id = Session::get('user_id');
		$token = Session::get('token');
		if (empty($user_id)) {
			return Redirect::to('/')->send();
		}
		$user_array = array("user_id" => $user_id, "token" => $token);
		$method = "POST";
		$data = array('form_params' => $user_array);
		$response = $this->api->call_api($data, 'api/user_detail', $method);
		if ($response->response->httpCode == 400) {
			return Redirect::to('/')->send();
		} else {
			$this->user_details = $response->response->user_data[0];
			if ($this->user_details->email == "") {
				Session::flash('message-failure', trans("messages.Please fill your personal details"));
				return Redirect::to('/profile')->send();
			}
			return $this->user_details;
		}
	}

	public function index() {
		//get_cart
		$user_id = Session::get('user_id');
		$token = Session::get('token');
		$language = getCurrentLang();
		$user_array = array("user_id" => $user_id, "token" => $token, "language" => $language);
		$method = "POST";
		$data = array('form_params' => $user_array);
		$response = $this->api->call_api($data, 'api/get_cart', $method);

		if ($response->response->httpCode == 400) {
			$cart_items = array();
		} else {
			$cart_items = $response->response->cart_items;
			$total = $response->response->total;
			$sub_total = $response->response->sub_total;
			$tax = $response->response->tax;
			$delivery_cost = $response->response->delivery_cost;
			$tax_amount = $sub_total * $tax / 100;
		}
		//print_r($response);
		SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'Cart');
		SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'Cart');
		SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'Cart');
		OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'Cart');
		OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'Cart');
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'Cart');
		Twitter::setSite(Session::get("general_site")->site_name);
		return view('front.' . $this->theme . '.cart')->with("user_details", $this->user_details)->with("cart_items", $cart_items)->with("total", $total)->with("sub_total", $sub_total)->with("tax_amount", $tax_amount)->with("delivery_cost", $delivery_cost)->with("language", $language);

// dynamic table create:
		return view('front.' . $this->theme . '.table')->with("user_details", $this->user_details)->with("cart_items", $cart_items)->with("total", $total)->with("sub_total", $sub_total)->with("tax_amount", $tax_amount)->with("delivery_cost", $delivery_cost)->with("language", $language);
	}
	public function indexCopy() {
		//get_cart
		$user_id = Session::get('user_id');
		$token = Session::get('token');
		$language = getCurrentLang();
		$user_array = array("user_id" => $user_id, "token" => $token, "language" => $language);
		$method = "POST";
		$data = array('form_params' => $user_array);
		$response = $this->api->call_api($data, 'api/get_cart', $method);

		if ($response->response->httpCode == 400) {
			$cart_items = array();
		} else {
			$cart_items = $response->response->cart_items;
			$total = $response->response->total;
			$sub_total = $response->response->sub_total;
			$tax = $response->response->tax;
			$delivery_cost = $response->response->delivery_cost;
			$tax_amount = $sub_total * $tax / 100;
		}
		//print_r($response);
		// SEOMeta::setTitle(Session::get("general_site")->site_name . ' - ' . 'Cart');
		// SEOMeta::setDescription(Session::get("general_site")->site_name . ' - ' . 'Cart');
		// SEOMeta::addKeyword(Session::get("general_site")->site_name . ' - ' . 'Cart');
		// OpenGraph::setTitle(Session::get("general_site")->site_name . ' - ' . 'Cart');
		// OpenGraph::setDescription(Session::get("general_site")->site_name . ' - ' . 'Cart');
		// OpenGraph::setUrl(URL::to('/'));
		// Twitter::setTitle(Session::get("general_site")->site_name . ' - ' . 'Cart');
		// Twitter::setSite(Session::get("general_site")->site_name);
		// return view('front.' . $this->theme . '.cart')->with("user_details", $this->user_details)->with("cart_items", $cart_items)->with("total", $total)->with("sub_total", $sub_total)->with("tax_amount", $tax_amount)->with("delivery_cost", $delivery_cost)->with("language", $language);

// dynamic table create:
		return view('front.' . $this->theme . '.table')->with("user_details", $this->user_details)->with("cart_items", $cart_items)->with("total", $total)->with("sub_total", $sub_total)->with("tax_amount", $tax_amount)->with("delivery_cost", $delivery_cost)->with("language", $language);
	}

	public function update_cart(Request $data) {
		$post_data = $data->all();

		$cart_id = $post_data['cart_id'];
		$cart_detail_id = $post_data['cart_detail_id'];
		$qty = $post_data['qty'];
		$user_id = Session::get('user_id');
		$token = Session::get('token');
		$language = getCurrentLang();
		$user_array = array("user_id" => $user_id, "token" => $token, "language" => $language, "cart_id" => $cart_id, "cart_detail_id" => $cart_detail_id, "qty" => $qty ,"coupon_details"=> $coupon_details);
		$method = "POST";
		$data = array('form_params' => $user_array);
		$response = $this->api->call_api($data, 'api/update_cart', $method);
		//echo"<pre>";print_r($response);exit;

		return response()->json($response->response);
	}

	public function add_to_cart() {
		return view('front.' . $this->theme . '.cart')->with("user_details", $this->user_details);
	}

	public function add_cart_info(Request $data) {
		$post_data = $data->all();
		$qty = $post_data['quantity'];
		$total_amount = $post_data['total_amount'];
		$product_id = $post_data['product_id'];
		$outlet_id = $post_data['outlet_id'];
		$vendors_id = $post_data['vendors_id'];
		$user_id = Session::get('user_id');
		$token = Session::get('token');
		$language = getCurrentLang();
		$cart_array = array("user_id" => $user_id, "token" => $token, "language" => $language, "qty" => $qty, "total_amount" => $total_amount, "product_id" => $product_id, "outlet_id" => $outlet_id, "vendors_id" => $vendors_id);
		$method = "POST";
		$data = array('form_params' => $cart_array);
		$response = $this->api->call_api($data, 'api/add_to_cart', $method);
		return response()->json($response->response);
	}

	public function check_promocode(Request $data)
	{
		$current_date = strtotime(date('Y-m-d'));
		$post_data = $data->all();
		//print_r($post_data);exit();
		$coupon_details = DB::table('coupons')
			->select('coupons.id as coupon_id', 'coupon_type', 'offer_amount', 'offer_type', 'coupon_code', 'start_date', 'end_date')
			->leftJoin('coupon_outlet', 'coupon_outlet.coupon_id', '=', 'coupons.id')
			->where('coupon_code', '=', $post_data['promo_code'])
			->where('coupon_outlet.outlet_id', '=', $post_data['outlet_id'])
			->first();

		//print_r($coupon_details);exit;
		if (count($coupon_details) == 0) {
			$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.This coupon is not applicable for the current store.")));
			
			return response()->json($result['response']);
			//return json_encode($result);
		} else if ((strtotime($coupon_details->start_date) <= $current_date) && (strtotime($coupon_details->end_date) >= $current_date)) {
			$coupon_user_limit_details = DB::table('user_cart_limit')
				->select('cus_order_count', 'user_limit', 'total_order_count', 'coupon_limit')
				->where('customer_id', '=', $post_data['customer_id'])
				->where('coupon_code', '=', $post_data['promo_code'])
				->first();
			if (count($coupon_user_limit_details) > 0) {
				if ($coupon_user_limit_details->cus_order_count >= $coupon_user_limit_details->user_limit) {
					$result = array("response" => array("httpCode" => 400, "Message" => "Max user limit has been crossed"));
					return response()->json($result['response']);
					//return json_encode($result);
				}
				if ($coupon_user_limit_details->total_order_count >= $coupon_user_limit_details->coupon_limit) {
					$result = array("response" => array("httpCode" => 400, "Message" => "Max coupon limit has been crossed"));
					//return json_encode($result);
					return response()->json($result['response']);

				}
			}
			$result = array("response" => array("httpCode" => 200, "status" => true, "Message" => trans("messages.Coupon applied Successfully"), "coupon_details" => $coupon_details, "coupon_user_limit_details" => $coupon_user_limit_details));
			//echo"<pre>";print_r(response()->json($result['response']));exit;
						//response()->json($checkout_details->response)

			return response()->json($result['response']);
			//return json_encode($result);
		} else {
			$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Invalid promocode.")));
			//return json_encode($result);
			return response()->json($result['response']);

		}
	}
}
