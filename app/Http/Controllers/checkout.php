<?php

namespace App\Http\Controllers;
use App;
use App\Model\api;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use OpenGraph;
use Paypal;
use PayPal\Api\ItemList;
//use App\Http\Controllers\Api\Api;
use SEOMeta;
use Session;
use Twitter;

class Checkout extends Controller {
	const USERS_SIGNUP_EMAIL_TEMPLATE = 1;
	const USERS_WELCOME_EMAIL_TEMPLATE = 3;
	const COMMON_MAIL_TEMPLATE = 8;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	private $_apiContext;
	public function __construct() {
		$this->user_details = array();
		$this->api = New Api;
		$this->client = new Client([
			// Base URI is used with relative requests
			'base_uri' => url('/'),
    			//'base_uri' => url('http://127.0.0.1/'),

			// You can set any number of default request options.
			'timeout' => 3000.0,
		]);
		$user_details = $this->check_login();
		$this->_apiContext = PayPal::ApiContext(getAppPaymentConfig()->merchant_key, getAppPaymentConfig()->merchant_secret_key);
		//echo '<pre>'; print_r( $this->_apiContext);exit;
		$this->_apiContext->setConfig(
			array(
				'mode' => 'live',
				'log.LogEnabled' => true,
				'log.FileName' => '../PayPal.log',
				'log.LogLevel' => 'FINE', // PLEASE USE `FINE` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
				'cache.enabled' => true,
				// 'http.CURLOPT_CONNECTTIMEOUT' => 30
				// 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
			)
		);
		$this->theme = Session::get("general")->theme;
	}

	public function check_login() {
		$user_id = Session::get('user_id');
		$token = Session::get('token');
		//Session::put('token', $response->response->token);
		if (empty($user_id)) {
			return Redirect::to('/')->send();
		}
		$user_array = array("user_id" => $user_id, "token" => $token);
		$method = "POST";
		$data = array('form_params' => $user_array);
		$response = $this->api->call_api($data, '/api/user_detail', $method);
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

	function GetDrivingDistance($lat1, $long1, $lat2, $long2) {
		$url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=" . $lat1 . "," . $long1 . "&destinations=" . $lat2 . "," . $long2 . "&mode=driving&language=pl-PL";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$response_a = json_decode($response, true);
		$dist = $response_a['rows'][0]['elements'][0]['distance']['text'];
		$time = $response_a['rows'][0]['elements'][0]['duration']['text'];
		$dist = str_replace(',', '.', $dist);
		$distance = array('distance' => $dist, 'distance_km' => ($dist * 1.609344), 'time' => $time);
		return $distance;
	}

	public function index() {
		$user_id = Session::get('user_id');
		$token = Session::get('token');
		$language = getCurrentLang();
		$user_array = array("user_id" => $user_id, "token" => $token, "language" => $language);
		$method = "POST";
		$data = array('form_params' => $user_array);
		/*$geotools = new \League\Geotools\Geotools();
			        $coordA   = new \League\Geotools\Coordinate\Coordinate([12.916943, 77.648621]);
			        $coordB   = new \League\Geotools\Coordinate\Coordinate([12.733630, 77.825089]);
			        $distance = $geotools->distance()->setFrom($coordA)->setTo($coordB);
			        printf("%s\n",$distance->in('km')->haversine()); // 659.02190812846
		*/
		$checkout_details = $this->api->call_api($data, '/api/checkout_detail', $method);
		//echo '<pre>';print_r($checkout_details);exit;
		//echo "<pre>";
		//    print_r($checkout_details->response->outlet_detail);
		///    print_r($checkout_details->response->address_list);
		//    print_r($checkout_details->response->delivery_settings);
		//    echo "</pre>";

		//print_r($driving_distance);exit;
		if ($checkout_details->response->httpCode == 400) {
			Session::flash('message-failure', trans('messages.No cart items found'));
			return Redirect::to('/')->send();
		}
		//$driving_distance = $this->GetDrivingDistance($checkout_details->response->outlet_detail->latitude,$checkout_details->response->outlet_detail->longitude,12.5186110,78.2137360);

		$address_types[""] = trans("messages.Select address type");
		foreach ($checkout_details->response->address_type as $row) {
			$address_types[$row->id] = ucfirst($row->name);
		}
		SEOMeta::setTitle(Session::get('general_site')->site_name);
		SEOMeta::setDescription(Session::get('general_site')->site_name);
		SEOMeta::addKeyword(Session::get('general_site')->site_name);
		OpenGraph::setTitle(Session::get('general_site')->site_name);
		OpenGraph::setDescription(Session::get('general_site')->site_name);
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get('general_site')->site_name);
		Twitter::setSite(Session::get('general_site')->site_name);
		$user_details = $this->check_login();
		return view('front.' . $this->theme . '.checkout')->with("user_details", $this->user_details)->with("checkout_details", $checkout_details->response)->with("address_types", $address_types)->with("language", $language);
	}
	public function converCurrency($from, $to, $amount) {
		$url = "https://finance.google.com/finance/converter?a=$amount&from=$from&to=$to";
		$request = curl_init();
		$timeOut = 0;
		curl_setopt($request, CURLOPT_URL, $url);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($request, CURLOPT_USERAGENT, "Mozilla/4.0(compatible;MSIE8.0;Windows NT6.1)");
		curl_setopt($request, CURLOPT_CONNECTTIMEOUT, $timeOut);
		$response = curl_exec($request);
		curl_close($request);
		return $response;
	}

	public function proceed_checkout(Request $data) {
		$post_data = $data->all();
		//echo '<pre>';
		//print_r($post_data);exit;
		$user_id = Session::get('user_id');
		$token = Session::get('token');
		$language = getCurrentLang();
		$payment_gateway_id = $post_data['payment_gateway_id'];
		$user_array = array("user_id" => $user_id, "token" => $token, "language" => $language, "payment_gateway_id" => $payment_gateway_id);
		$method = "POST";
		$data = array('form_params' => $user_array);
		$response = $this->api->call_api($data, '/api/get_payment_details', $method);
		$cart_detail = $response->response;
		//print_r($user_array);exit;
		$cart_detail->delivery_notes = $post_data['delivery_instructions'];
		$delivery_address = (isset($post_data['delivery_address']) && ($post_data['delivery_address'] != "")) ? $post_data['delivery_address'] : 0;
		$cart_detail->delivery_address = $delivery_address;

		$cart_detail->delivery_slot = ($post_data['delivery_slot'] != "") ? $post_data['delivery_slot'] : 0;
		$cart_detail->delivery_date = ($post_data['delivery_date'] != "") ? $post_data['delivery_date'] : 0;
		$cart_detail->delivery_cost = ($post_data['delivery_cost'] != "") ? $post_data['delivery_cost'] : 0;
		$cart_detail->order_type = $post_data['order_type'];
		if ($cart_detail->order_type == 2) {
			$cart_detail->delivery_cost = 0;
			$cart_detail->delivery_slot = 0;
			$cart_detail->delivery_date = "NOW()";
			$cart_detail->delivery_address = 0;
		}
		$cart_detail->coupon_id = ($post_data['coupon_id'] != "") ? $post_data['coupon_id'] : 0;
		$cart_detail->coupon_amount = ($post_data['coupon_amount'] != "") ? $post_data['coupon_amount'] : 0;
		$cart_detail->coupon_type = ($post_data['coupon_type'] != "") ? $post_data['coupon_type'] : 0;
		//echo"<pre>";print_r($cart_detail);exit;
		//$cart_detail->vendor_name = $cart_detail->cart_items[0]->vendor_name;
		if ($cart_detail->payment_gateway_detail->payment_type == 0) {
			$this->offline_payment($cart_detail);
		} else if ($cart_detail->payment_gateway_detail->payment_type == 1) {
			//Paypal
			$cart_details = $cart_detail;
			// print_r($cart_details);exit;
			$payment_array = array();
			$total_amount = ($cart_details->sub_total + $cart_details->tax_amount + $cart_details->delivery_cost) - $cart_details->coupon_amount;
			$admin_commission = (($cart_details->sub_total * $cart_details->payment_gateway_detail->commision) / 100);
			$vendor_commission = $cart_details->sub_total - $admin_commission;
			$payment_array['admin_commission'] = ($admin_commission + $cart_details->tax + $cart_details->delivery_cost);
			$payment_array['vendor_commission'] = $vendor_commission;
			$payment_array['user_id'] = $cart_details->cart_items[0]->user_id;
			$payment_array['store_id'] = $cart_details->cart_items[0]->store_id;
			$payment_array['outlet_id'] = $cart_details->cart_items[0]->outlet_id;
			$payment_array['vendor_name'] = $cart_details->cart_items[0]->vendor_name;
			$payment_array['vendor_key'] = $cart_details->cart_items[0]->vendor_key;
			$payment_array['total'] = $total_amount;
			$payment_array['sub_total'] = $cart_details->sub_total;
			$payment_array['service_tax'] = $cart_details->tax;
			$payment_array['tax_amount'] = $cart_details->tax_amount;
			$payment_array['order_status'] = 1;
			$payment_array['order_key'] = str_random(32);
			//$payment_array['invoice_id']       = 'INV'.str_random(8).time();
			$payment_array['transaction_id'] = str_random(32);
			$payment_array['transaction_staus'] = 1;
			$payment_array['transaction_amount'] = $total_amount;
			$payment_array['payer_id'] = str_random(32);
			$payment_array['currency_code'] = getCurrency($language);
			$payment_array['currency_side'] = getCurrencyPosition()->currency_side;
			$payment_array['payment_gateway_id'] = $cart_details->payment_gateway_detail->id;
			$payment_array['coupon_type'] = 0;
			$payment_array['delivery_charge'] = 0;
			$payment_array['payment_status'] = 0;
			$payment_array['payment_gateway_commission'] = 0;
			$payment_array['delivery_instructions'] = $cart_details->delivery_notes;
			$payment_array['delivery_address'] = $cart_details->delivery_address;
			$payment_array['delivery_slot'] = $cart_details->delivery_slot;
			$payment_array['delivery_date'] = $cart_details->delivery_date;
			$payment_array['order_type'] = $cart_details->order_type;
			$payment_array['coupon_id'] = $cart_details->coupon_id;
			$payment_array['coupon_amount'] = $cart_details->coupon_amount;
			$payment_array['coupon_type'] = $cart_details->coupon_type;
			$payment_array['delivery_cost'] = $cart_details->delivery_cost;
			$items = array();
			$i = 0;
			$to_currency = "USD";
			$from_currency = getCurrencycode();

			$amount = urlencode($total_amount);
			$from_currency = urlencode($from_currency);
			$to_currency = urlencode($to_currency);
			//print_r( $from_currency);exit;
			/*  $get           = file_get_contents("https://finance.google.com/finance/converter?a=$amount&from=$from_currency&to=$to_currency");
	            $get = explode("<span class=bld>",$get);
	            $converted_amount = $total_amount;
	            if(isset($get[1]))
	            {
	                $get = explode("</span>",$get[1]);
	                if(isset($get[0]))
	                {
	                    $converted_amount = preg_replace("/[^0-9\.]/", null, $get[0]);
	                }
*/

			/*$request ='https://free.currencyconverterapi.com/api/v5/convert?q='.$from_currency.'_'.$to_currency.'&compact=y&callback=jQuery203017817078931262786_1521607731806&_=1521607731810';
				               $file_contents = file_get_contents($request);

				                   if(!empty($file_contents))
				                   {
				                       $file_contents = explode(':',$file_contents);
				                       $result = substr($file_contents[2], 0, 5);
				                       if(!empty($result))
				                       {
				                           $converted_amount = $result*$amount;

				                       }

				                   }
				            //$this->currency_converter($post);
			*/
			foreach ($cart_details->cart_items as $cartitems) {
				$items[$i]['product_id'] = $cartitems->product_id;
				$items[$i]['quantity'] = $cartitems->quantity;
				$items[$i]['discount_price'] = $cartitems->discount_price;
				$items[$i]['item_offer'] = 0;
				$i++;
			}
			$payment_array['items'] = $items;
			Session::put('checkout_info', $payment_array);
			$paypal = Paypal::getAll(array('count' => 1, 'start_index' => 0), $this->_apiContext);
			try {
				$payer = PayPal::Payer();
				$payer->setPaymentMethod('paypal');
				$amount = PayPal::Amount();
				$amount->setCurrency('INR');
				$amount->setTotal($total_amount); //->setDetails($details);
				$item = $items = array();
				$index = 0;
				$itemList = new ItemList();
				/*foreach ($cart_details->cart_items as $_item)
					                {
					                    $index++;
					                    $item[$index] = new Item();
					                    $item[$index]->setName($_item->product_name)
					                                    ->setCurrency('USD')
					                                    ->setQuantity($_item->quantity)
					                                    ->setPrice($_item->discount_price);
					                    $itemList->setItems($item);
					                    break;
				*/
				//print_r( $itemList);exit;
				$transaction = PayPal::Transaction();
				$transaction->setAmount($amount);
				$info = 'Place order - Pay on $ ' . $total_amount;
				//print_r($itemList);exit;
				$transaction->setDescription($info)->setItemList($itemList);
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
		} else if ($cart_detail->payment_gateway_detail->payment_type == 2) {
			//PayFort
			$user_id = Session::get('user_id');
			$user_email = Session::get('email');
			$merchant_reference = str_random(16);
			$lang = App::getLocale(); //getCurrentLang();
			$return_url = url("/") . "/checkout/thankyou";
			$cart_details = $cart_detail;
			//print_r($cart_details);exit;
			$payment_array = array();
			$total_amount = ($cart_details->sub_total + $cart_details->tax + $cart_details->delivery_cost) - $cart_details->coupon_amount;
			$admin_commission = (($cart_details->sub_total * $cart_details->payment_gateway_detail->commision) / 100);
			$vendor_commission = $cart_details->sub_total - $admin_commission;
			$payment_array['admin_commission'] = ($admin_commission + $cart_details->tax + $cart_details->delivery_cost);
			$payment_array['vendor_commission'] = $vendor_commission;
			$payment_array['user_id'] = $cart_details->cart_items[0]->user_id;
			$payment_array['store_id'] = $cart_details->cart_items[0]->store_id;
			$payment_array['outlet_id'] = $cart_details->cart_items[0]->outlet_id;
			$payment_array['vendor_name'] = $cart_details->cart_items[0]->vendor_name;
			$payment_array['total'] = $total_amount;
			$payment_array['sub_total'] = $cart_details->sub_total;
			$payment_array['service_tax'] = $cart_details->tax;
			$payment_array['tax_amount'] = $cart_details->tax_amount;
			$payment_array['order_status'] = 1;
			$payment_array['order_key'] = str_random(32);
			//$payment_array['invoice_id']       = 'INV'.str_random(8).time();
			$payment_array['transaction_id'] = str_random(32);
			$payment_array['transaction_staus'] = 1;
			$payment_array['transaction_amount'] = $total_amount;
			$payment_array['payer_id'] = str_random(32);
			$payment_array['currency_code'] = getCurrency($language);
			$payment_array['currency_side'] = getCurrencyPosition()->currency_side;
			$payment_array['payment_gateway_id'] = $cart_details->payment_gateway_detail->id;
			$payment_array['coupon_type'] = 0;
			$payment_array['delivery_charge'] = 0;
			$payment_array['payment_status'] = 0;
			$payment_array['payment_gateway_commission'] = 0;
			$payment_array['delivery_instructions'] = $cart_details->delivery_notes;
			$payment_array['delivery_address'] = $cart_details->delivery_address;
			$payment_array['delivery_slot'] = $cart_details->delivery_slot;
			$payment_array['delivery_date'] = $cart_details->delivery_date;
			$payment_array['order_type'] = $cart_details->order_type;
			$payment_array['coupon_id'] = $cart_details->coupon_id;
			$payment_array['coupon_amount'] = $cart_details->coupon_amount;
			$payment_array['coupon_type'] = $cart_details->coupon_type;
			$payment_array['delivery_cost'] = $cart_details->delivery_cost;
			$items = array();
			$i = 0;
			/*$from_currency = "USD";
				            $to_currency   = getCurrencycode();
				            $amount        = urlencode($total_amount);
				            $from_currency = urlencode($from_currency);
				            $to_currency   = urlencode($to_currency);
				            $get           = file_get_contents("https://www.google.com/finance/converter?a=$amount&from=$from_currency&to=$to_currency");
				            $get = explode("<span class=bld>",$get);
				            $converted_amount = $total_amount;
				            if(isset($get[1]))
				            {
				                $get = explode("</span>",$get[1]);
				                if(isset($get[0]))
				                {
				                    $converted_amount = preg_replace("/[^0-9\.]/", null, $get[0]);
				                }
				            }
				            //$this->currency_converter($post);
			*/
			foreach ($cart_details->cart_items as $cartitems) {
				$items[$i]['product_id'] = $cartitems->product_id;
				$items[$i]['quantity'] = $cartitems->quantity;
				$items[$i]['discount_price'] = $cartitems->discount_price;
				$items[$i]['item_offer'] = 0;
				$i++;
			}
			$payment_array['items'] = $items;
			Session::put('checkout_info', $payment_array);
			//print_r($payment_array);exit;
			$total_new_amount = $total_amount * 100;
			$str = "adserefdseraccess_code=" . $cart_detail->payment_gateway_detail->merchant_key . "amount=" . $total_new_amount . "command=PURCHASEcurrency=" . $cart_detail->payment_gateway_detail->currency_code . "customer_email=" . $user_email . "language=" . $lang . "merchant_identifier=" . $cart_detail->payment_gateway_detail->account_id . "merchant_reference=" . $merchant_reference . "payment_option=MASTERCARDadserefdser";
			$signature = hash('sha256', $str);
			$signature = hash('sha256', $str);
			$requestParams = array(
				'access_code' => $cart_detail->payment_gateway_detail->merchant_key,
				'amount' => $total_new_amount,
				'currency' => $cart_detail->payment_gateway_detail->currency_code,
				'customer_email' => $user_email,
				'merchant_reference' => $merchant_reference,
				'language' => $lang,
				'merchant_identifier' => $cart_detail->payment_gateway_detail->account_id,
				'signature' => $signature,
				'command' => 'PURCHASE',
				'payment_option' => 'MASTERCARD',
			);
			if ($cart_detail->payment_gateway_detail->payment_mode == 1) {
				$redirectUrl = 'https://sbcheckout.payfort.com/FortAPI/paymentPage';
			} else {
				$redirectUrl = 'https://checkout.payfort.com/FortAPI/paymentPage';
			}
			echo "<html xmlns='http://www.w3.org/1999/xhtml'>\n<head></head>\n<body>\n";
			echo "<form action='$redirectUrl' method='post' name='frm'>\n";
			foreach ($requestParams as $a => $b) {
				echo "\t<input type='hidden' name='" . htmlentities($a) . "' value='" . htmlentities($b) . "'>\n";
			}
			echo "\t<script type='text/javascript'>\n";
			echo "\t\tdocument.frm.submit();\n";
			echo "\t</script>\n";
			echo "</form>\n</body>\n</html>";

			/*$total_new_amount = $total_amount*100;
				            $str = "qwertwqertaccess_code=".$cart_detail->payment_gateway_detail->merchant_key."amount=".$total_amount."command=PURCHASEcurrency=".$cart_detail->payment_gateway_detail->currency_code."customer_email=".$user_email."language=".$lang."merchant_identifier=".$cart_detail->payment_gateway_detail->account_id."merchant_reference=".$merchant_reference."payment_option=MASTERCARDqwertwqert";
							$signature = hash('sha256', $str);
							//echo $str."**".$signature.'<br/>';
							//$str = "qwertwqertaccess_code=d2s1As1SP9A4zelSA2OUamount=1000command=PURCHASEcurrency=SARcustomer_email=chandru@mailinator.comlanguage=enmerchant_identifier=bdMZsfAOmerchant_reference=BV0IPmPy5jp1vAz8Kpg7payment_option=MASTERCARDqwertwqert";
							//$signature = Sha1($str);
							$signature = hash('sha256', $str);
							//echo $str1."**".$signature1;
							//exit;
							$requestParams = array(
							'access_code' => $cart_detail->payment_gateway_detail->merchant_key,
							'amount' => $total_new_amount,
							'currency' => $cart_detail->payment_gateway_detail->currency_code,
							'customer_email' => $user_email,
							'merchant_reference' => $merchant_reference,
							'language' => $lang,
							'merchant_identifier' => $cart_detail->payment_gateway_detail->account_id,
							'signature' => $signature,
							'command' => 'PURCHASE',
							'payment_option' => 'MASTERCARD',
							);
							//print_r($requestParams);exit;
							if($cart_detail->payment_gateway_detail->payment_mode==1){
								$redirectUrl = 'https://sbcheckout.payfort.com/FortAPI/paymentPage';
							} else {
								$redirectUrl = 'https://checkout.payfort.com/FortAPI/paymentPage';
							}
							echo "<html xmlns='http://www.w3.org/1999/xhtml'>\n<head></head>\n<body>\n";
							echo "<form action='$redirectUrl' method='post' name='frm'>\n";
							foreach ($requestParams as $a => $b) {
								echo "\t<input type='hidden' name='".htmlentities($a)."' value='".htmlentities($b)."'>\n";
							}
							echo "\t<script type='text/javascript'>\n";
							echo "\t\tdocument.frm.submit();\n";
							echo "\t</script>\n";
			*/

			/*return view('front.'.$this->theme.'.checkout_cards')->with("total_amount",$total_amount)->with("payment_details",$payment_array);*/
		}
	}
	/*public function paypal_payment($cart_details)
		    {
		        $payment_array =array();
		        $total_amount = ($cart_details->sub_total+$cart_details->tax+$cart_details->delivery_cost)-$cart_details->coupon_amount;
		        $payment_array['user_id'] = $cart_details->cart_items[0]->user_id;
		        $payment_array['store_id'] = $cart_details->cart_items[0]->store_id;
		        $payment_array['outlet_id'] = $cart_details->cart_items[0]->outlet_id;
		        $payment_array['vendor_key'] = $cart_details->cart_items[0]->vendor_key;
		        $payment_array['total'] = $total_amount;
		        $payment_array['sub_total'] = $cart_details->sub_total;
		        $payment_array['service_tax'] = $cart_details->tax;
		        $payment_array['order_status'] = 1;
		        $payment_array['order_key'] = str_random(32);
		        $payment_array['invoice_id'] = str_random(32);
		        $payment_array['transaction_id'] = str_random(32);
		        $payment_array['transaction_staus'] = 1;
		        $payment_array['transaction_amount'] = $total_amount;
		        $payment_array['payer_id'] = str_random(32);
		        $payment_array['currency_code'] = getCurrency();
		        $payment_array['payment_gateway_id'] = $cart_details->payment_gateway_detail->id;
		        $payment_array['coupon_type'] = 0;
		        $payment_array['delivery_charge'] = 0;
		        $payment_array['payment_status'] = 0;
		        $payment_array['vendor_commission'] = 0;
		        $payment_array['payment_gateway_commission'] = 0;
		        $payment_array['delivery_instructions'] = $cart_details->delivery_notes;
		        $payment_array['delivery_address'] = $cart_details->delivery_address;
		        $payment_array['delivery_slot'] = $cart_details->delivery_slot;
		        $payment_array['delivery_date'] = $cart_details->delivery_date;
		        $payment_array['order_type'] = $cart_details->order_type;
		        $payment_array['coupon_id'] = $cart_details->coupon_id;
		        $payment_array['coupon_amount'] = $cart_details->coupon_amount;
		        $payment_array['delivery_cost'] = $cart_details->delivery_cost;
		        $items = array();
		        $i = 0;
		        foreach($cart_details->cart_items as $cartitems)
		        {
		            $items[$i]['product_id'] = $cartitems->product_id;
		            $items[$i]['quantity'] = $cartitems->quantity;
		            $items[$i]['discount_price'] = $cartitems->discount_price;
		            $items[$i]['item_offer'] = 0;
		            $i++;
		        }
		        $payment_array['items'] = $items;
		        print_r($payment_array);exit;
		        Session::put('checkout_info',$payment_array);
		        $paypal=Paypal::getAll(array('count' => 1, 'start_index' => 0), $this->_apiContext);
		        // process the validation
		             //Save Details
		            try    {
		                //echo $total_amount;exit;
		                $payer = PayPal    ::Payer();
		                $payer->setPaymentMethod('paypal');
		                $amount = PayPal:: Amount();
		                $amount->setCurrency('USD');
		                $amount->setTotal($total_amount);//->setDetails($details);

		                /*$item1 = new Item();
		                $item1->setName("dark choclate")->setCurrency('USD')->setQuantity(1)->setPrice(1235);
	*/

	/*$itemList = new ItemList();
		                $itemList->setItems(array($item1));

		                $item  = array();
		                $items = array();
		                $index = 0;
		                foreach ($cart_details->cart_items as $_item)
		                {
		                    $index++;
		                    $item[$index] = new Item();
		                    $item[$index]->setName($_item->product_name)
		                                 ->setCurrency('USD')
		                                 ->setQuantity($_item->quantity)
		                                 ->setPrice($_item->discount_price);
		                }
		                $itemList = new ItemList(); $itemList->setItems($items);
		                $transaction = PayPal::Transaction();
		                $transaction->setAmount($amount);
		                $info ='Place order - Pay on $ '.$total_amount;
		                $transaction->setDescription($info)->setItemList($itemList);
		                $redirectUrls = PayPal:: RedirectUrls();
		                $redirectUrls->setReturnUrl(route('getDone'));
		                $redirectUrls->setCancelUrl(route('getCancel'));
		                $payment = PayPal::Payment();
		                $payment->setIntent('sale');
		                $payment->setPayer($payer);
		                $payment->setRedirectUrls($redirectUrls);
		                $payment->setTransactions(array($transaction));
		                $response = $payment->create($this->_apiContext);
		                $redirectUrl = $response->links[1]->href;
		                header("Location: ".$redirectUrl);
		            }
		            catch(Exception $ex)
		            {
		                 ResultPrinter::printError("Created Payment Using PayPal. Please visit the URL to Approve.", "Payment", null, $request, $ex); exit(1);
		            }
		            Session::flash('message', 'Error: Oops. Something went wrong. Please try again later.');
		            return Redirect::to('/');
	*/

	public function getDone(Request $request) {
		//print_r( $request);exit;
		$checkout_info = Session::get('checkout_info');
		if ($checkout_info == '') {
			Session::flash('message', 'Error: Oops. Something went wrong. Please try again later.');
			return Redirect::to('/');
		}
		$id = $request->get('paymentId');
		$token = $request->get('token');
		$payer_id = $request->get('PayerID');
		$all_info = Paypal::getAll(array('count' => 1, 'start_index' => 0), $this->_apiContext);
//print_r( $all_info);exit;
		/** get payment request responce  **/
		$payment = PayPal::getById($id, $this->_apiContext);
		$paymentExecution = PayPal::PaymentExecution();
		$paymentExecution->setPayerId($payer_id);
		$executePayment = $payment->execute($paymentExecution, $this->_apiContext);
		/** get payment request responce  **/
		//Session::put('checkout_info','');
		if (isset($executePayment->state) && $executePayment->state == "approved") {
			$Id = $executePayment->getId();
			$Intent = $executePayment->getIntent();
			$Payer = $executePayment->getPayer();
			$Payee = $executePayment->getPayee();
			$Cart = $executePayment->getCart();
			$payment_method = $executePayment->payer->payment_method;
			$paypal_email = $executePayment->payer->payer_info->email;
			$country_code = $executePayment->payer->payer_info->country_code;
			$Transactions = $executePayment->getTransactions();
			$PaymentInstruction = $executePayment->getPaymentInstruction();
			$State = $executePayment->getState();
			$ExperienceProfileId = $executePayment->getExperienceProfileId();
			$CreateTime = $executePayment->getCreateTime();
			$UpdateTime = $executePayment->getUpdateTime();
			$ApprovalLink = $executePayment->getApprovalLink();
			$all_data = $executePayment->get($id, $this->_apiContext);
			$payment_params = '';
			if (isset($Transactions['0']) && $Transactions['0'] != '') {
				$related_resource = $Transactions['0']->related_resources;
				//print_r($related_resource);exit;
				foreach ($related_resource as $key => $value) {
					/** get payment transaction responce  **/
					$payment_id = $value->sale->id;
					$payment_state = $value->sale->state;
					$payment_amount = $value->sale->amount;
					$payment_mode = $value->sale->payment_mode;
					//$reason_code=$value->state->reason_code;
					$protection_eligibility = $value->sale->protection_eligibility;
					$protection_eligibility = $value->sale->protection_eligibility_type;
					$parent_payment = $value->sale->parent_payment;
					$create_time = $value->sale->create_time;
					$update_time = $value->sale->update_time;
					$links = $value->sale->links;
					/** get payment transaction responce  end **/
					/**calculate admin and vendor commision ammount*/
					/*$admin_commision_per=getAppPaymentConfig()->commision;
						                        $admin_commision=round($payment_amount->total)/100*$admin_commision_per;
					*/
					/**calculate admin and vendor commision ammount end*/

					/** set payment_params  */
					$payment_params = array("parent_payment_id" => $Id, "token" => $token, "payer_id" => $payer_id, "Intent" => $Intent, "Payee" => $Payee, "cart_id" => $Cart, "payment_id" => $payment_id, "payment_state" => $State, "payment_amount" => $payment_amount, "payment_mode" => $payment_mode, "create_time" => $create_time, "update_time" => $update_time, "links" => $links, 'payment_method' => $payment_method, 'paypal_email' => $paypal_email, 'country_code' => $country_code);
					//$payment_params = array("parent_payment_id"=>$Id,"token"=>$token,"payer_id"=>$payer_id,"Intent"=>$Intent,"Payee"=>$Payee,"cart_id"=>$Cart,"payment_id"=>$payment_id,"payment_state"=>$State,"payment_amount"=>$payment_amount,"payment_mode"=>$payment_mode,"reason_code"=>$reason_code,"valid_until"=>$valid_until,"create_time"=>$create_time,"update_time"=>$update_time,"links"=>$links,'payment_method'=>$payment_method,'paypal_email'=>$paypal_email,'country_code'=>$country_code,'admin_commision'=>$admin_commision,'vendor_commision'=>$vendor_commision);
					/** set payment_params  end*/
					//$appoinment_id=$this->Appointmentbook($checkout_info,$payment_params);
					$user_id = Session::get('user_id');
					$token = Session::get('token');
					$language = getCurrentLang();
					$checkout_info = json_encode($checkout_info);
					$payment_params = json_encode($payment_params);
					$user_array = array("user_id" => $user_id, "token" => $token, "language" => $language, "payment_array" => $checkout_info, "payment_params" => $payment_params);
					//print_r($user_array);exit;
					$method = "POST";
					$data = array('form_params' => $user_array);
					$response = $this->api->call_api($data, '/api/online_payment', $method);
					if ($response->response->httpCode == 200) {
						Session::flash('message-success', trans('messages.Order placed successfully'));
						return Redirect::to('/thankyou/' . encrypt($response->response->order_id))->send();
					} else {
						Session::flash('message-failure', $response->response->Message);
						return Redirect::to('/checkout')->send();
					}
				}
			} else {
				Session::flash('message', 'Error:PaymentSuccess Oops. Something went wrong. Please try again later.');
				return Redirect::to('/');
			}
		}
	}

	public function getCancel() {
		//echo "asdfasdf";exit;
		Session::flash('message', 'Error:Proccess has been cancelled by user.');
		return Redirect::to('/checkout');
	}
	public function getDonePayFort(Request $request) {
		$checkout_info = Session::get('checkout_info');
		if ($checkout_info == '') {
			Session::flash('message', 'Error: Oops. Something went wrong. Please try again later.');
			return Redirect::to('/');
		}
		$payfort_response = $request;
		/** get payment request responce  **/
		//Session::put('checkout_info','');
		if ((isset($payfort_response->response_code) && $payfort_response->response_code == 14000) && (isset($payfort_response->response_message) && $payfort_response->response_message == 'Success') && (isset($payfort_response->status) && $payfort_response->status == 14)) {
			/** set payment_params  */
			$payment_params = array("amount" => $payfort_response->get("amount"),
				"response_code" => $payfort_response->get("response_code"),
				"card_number" => $payfort_response->get("card_number"),
				"signature" => $payfort_response->get("signature"),
				"merchant_identifier" => $payfort_response->get("merchant_identifier"),
				"expiry_date" => $payfort_response->get("expiry_date"),
				"access_code" => $payfort_response->get("access_code"),
				"payment_option" => $payfort_response->get("payment_option"),
				"customer_ip" => $payfort_response->get("customer_ip"),
				"language" => $payfort_response->get("language"),
				"eci" => $payfort_response->get("eci"),
				"fort_id" => $payfort_response->get("fort_id"),
				"command" => $payfort_response->get("command"),
				"payment_method" => 'credit card',
				"response_message" => $payfort_response->get("response_message"),
				'authorization_code' => $payfort_response->get("authorization_code"),
				'customer_email' => $payfort_response->get("customer_email"),
				'merchant_reference' => $payfort_response->get("merchant_reference"),
				'token_name' => $payfort_response->get("token_name"),
				'currency' => $payfort_response->get("currency"),
				'status' => $payfort_response->get("status"),
				'sdk_token' => '');

			$user_id = Session::get('user_id');
			$token = Session::get('token');
			$language = getCurrentLang();
			$checkout_info = json_encode($checkout_info);
			$payment_params = json_encode($payment_params);
			$user_array = array("user_id" => $user_id, "token" => $token, "language" => $language, "payment_array" => $checkout_info, "payment_params" => $payment_params);
			//print_r($user_array);exit;
			$method = "POST";
			$data = array('form_params' => $user_array);
			//~ echo '<pre>';print_r($user_array);die;
			$response = $this->api->call_api($data, 'api/online_payment', $method);
			//echo '<pre>';print_r($response);die;
			if ($response->response->httpCode == 200) {
				Session::flash('message-success', trans('messages.Your order has been placed successfully'));
				return Redirect::to('/thankyou/' . encrypt($response->response->order_id))->send();
			} else {
				Session::flash('message-failure', $response->response->Message);
				return Redirect::to('/checkout')->send();
			}
		} else {
			Session::flash('message', 'Oops. Something went wrong. Please try again later.');
			return Redirect::to('/');
		}
	}

	public function offline_payment($cart_details) {
		// echo "<pre>";
		 //print_r($cart_details);exit;
		//    print_r($cart_details->payment_gateway_detail->commision);exit;
		$language = getCurrentLang();
		$payment_array = array();
		$total_amount = ($cart_details->sub_total + $cart_details->tax_amount + $cart_details->delivery_cost) - $cart_details->coupon_amount;
		//$cart_details->payment_gateway_detail->commision."<br/>";
		$admin_commission = (($cart_details->sub_total * $cart_details->payment_gateway_detail->commision) / 100);
		$payment_array['admin_commission'] = ($admin_commission + $cart_details->tax + $cart_details->delivery_cost);
		$vendor_commission = $cart_details->sub_total - $admin_commission;
		$payment_array['vendor_commission'] = $vendor_commission;
		$payment_array['user_id'] = $cart_details->cart_items[0]->user_id;
		$payment_array['store_id'] = $cart_details->cart_items[0]->store_id;
		$payment_array['outlet_id'] = $cart_details->cart_items[0]->outlet_id;
		$payment_array['vendor_key'] = $cart_details->cart_items[0]->vendor_key;
		$payment_array['vendor_name'] = $cart_details->cart_items[0]->vendor_name;
		$payment_array['total'] = $total_amount;
		$payment_array['sub_total'] = $cart_details->sub_total;
		$payment_array['service_tax'] = $cart_details->tax;
		$payment_array['tax_amount'] = $cart_details->tax_amount;
		$payment_array['order_status'] = 1;
		$payment_array['order_key'] = str_random(32);
		$payment_array['invoice_id'] = str_random(32);
		$payment_array['transaction_id'] = str_random(32);
		$payment_array['transaction_staus'] = 1;
		$payment_array['transaction_amount'] = $total_amount;
		$payment_array['payer_id'] = str_random(32);
		$payment_array['currency_code'] = getCurrency($language);
		$payment_array['payment_gateway_id'] = $cart_details->payment_gateway_detail->id;
		$payment_array['coupon_type'] = 0;
		$payment_array['delivery_charge'] = 0;
		$payment_array['payment_status'] = 0;
		$payment_array['payment_gateway_commission'] = $cart_details->payment_gateway_detail->commision;
		$payment_array['delivery_instructions'] = $cart_details->delivery_notes;
		$payment_array['delivery_address'] = $cart_details->delivery_address;
		$payment_array['delivery_slot'] = $cart_details->delivery_slot;
		$payment_array['delivery_date'] = $cart_details->delivery_date;
		$payment_array['order_type'] = $cart_details->order_type;
		$payment_array['coupon_id'] = $cart_details->coupon_id;
		$payment_array['coupon_amount'] = $cart_details->coupon_amount;
		$payment_array['coupon_type'] = $cart_details->coupon_type;
		$payment_array['delivery_cost'] = $cart_details->delivery_cost;
		// echo '<pre>';  print_r($payment_array);exit;
		$items = array();
		$i = 0;
		foreach ($cart_details->cart_items as $cartitems) {
			$items[$i]['product_id'] = $cartitems->product_id;
			$items[$i]['quantity'] = $cartitems->quantity;
			$items[$i]['discount_price'] = $cartitems->discount_price;
			$items[$i]['item_offer'] = 0;
			$i++;
		}
		$payment_array['items'] = $items;
		//echo"<pre>";print_r($payment_array);exit;
		$payment_array = json_encode($payment_array);

		$user_id = Session::get('user_id');
		//print_r($payment_array);exit;
		$token = Session::get('token');

		$user_array = array("user_id" => $user_id, "token" => $token, "language" => $language, "payment_array" => $payment_array);
		$method = "POST";
		$data = array('form_params' => $user_array);
		//echo "<pre>";print_r($data);exit;
		$response = $this->api->call_api($data, '/api/offline_payment', $method);

		if (isset($response->response->httpCode) && $response->response->httpCode == 200) {
			Session::flash('message-success', trans('messages.Order placed successfully'));
			return Redirect::to('/thankyou/' . encrypt($response->response->order_id))->send();
		} else {
			Session::flash('message-failure', $response->response->Message);
			return Redirect::to('/checkout')->send();
		}
	}

	public function update_promocode(Request $data) {
		$post_data = $data->all();
		$post_data['user_id'] = Session::get('user_id');
		$post_data['language'] = getCurrentLang();
		$post_data['token'] = Session::get('token');
		$method = "POST";
		$data = array('form_params' => $post_data); //echo '<pre>';print_r($post_data);exit;
		$checkout_details = $this->api->call_api($data, '/api/update_promocode', $method);
		//echo '<pre>';print_r($checkout_details);exit;
		return response()->json($checkout_details->response);
	}

	public function thankyou($id) {
		$order_id = decrypt($id);
		$user_id = Session::get('user_id');
		$token = Session::get('token');
		$language = getCurrentLang();
		$user_array = array("user_id" => $user_id, "token" => $token, "language" => $language, "order_id" => $order_id);
		$method = "POST";
		$data = array('form_params' => $user_array);
		$response = $this->api->call_api($data, '/api/order_detail', $method);

		SEOMeta::setTitle(Session::get('general_site')->site_name);
		SEOMeta::setDescription(Session::get('general_site')->site_name);
		SEOMeta::addKeyword(Session::get('general_site')->site_name);
		OpenGraph::setTitle(Session::get('general_site')->site_name);
		OpenGraph::setDescription(Session::get('general_site')->site_name);
		OpenGraph::setUrl(URL::to('/'));
		Twitter::setTitle(Session::get('general_site')->site_name);
		Twitter::setSite(Session::get('general_site')->site_name);
		$user_details = $this->check_login();
		return view('front.' . $this->theme . '.order_confirmation')->with("user_details", $this->user_details)->with("order_items", $response->response->order_items)->with("delivery_details", $response->response->delivery_details);
	}

	public function send_otp(Request $data) {
		$post_data = $data->all();
		$post_data['user_id'] = Session::get('user_id');
		$post_data['language'] = getCurrentLang();
		$post_data['token'] = Session::get('token');
		$method = "POST";
		$data = array('form_params' => $post_data); //print_r($checkout_details);exit;
		$checkout_details = $this->api->call_api($data, '/api/send_otp', $method);
		//print_r($checkout_details);exit;
		return response()->json($checkout_details->response);
	}
	public function check_otp(Request $data) {
		$post_data = $data->all();
		$post_data['user_id'] = Session::get('user_id');
		$post_data['language'] = getCurrentLang();
		$post_data['token'] = Session::get('token');
		$method = "POST";
		$data = array('form_params' => $post_data);
		$checkout_details = $this->api->call_api($data, '/api/check_otp', $method);
		return response()->json($checkout_details->response);
	}

	public function re_order($order_id) {
		$order_id = decrypt($order_id);
		$post_data['user_id'] = Session::get('user_id');
		$post_data['language'] = getCurrentLang();
		$post_data['token'] = Session::get('token');
		$post_data['order_id'] = $order_id;
		$method = "POST";
		$data = array('form_params' => $post_data);
		$checkout_details = $this->api->call_api($data, '/api/re_order', $method);
		return Redirect::to('/checkout')->send();
	}

	public function cancel_order($order_id) {
		$order_id = decrypt($order_id);
		$post_data['user_id'] = Session::get('user_id');
		$post_data['language'] = getCurrentLang();
		$post_data['token'] = Session::get('token');
		$post_data['order_id'] = $order_id;
		$method = "POST";
		$data = array('form_params' => $post_data);
		$checkout_details = $this->api->call_api($data, '/api/cancel_order', $method);
		if ($checkout_details->response->httpCode == 200) {
			Session::flash('message-success', trans('messages.Order cancelled successfully'));
		} else {
			Session::flash('message-failure', trans('messages.Order not found'));
		}
		return Redirect::to('/orders')->send();
	}

}
