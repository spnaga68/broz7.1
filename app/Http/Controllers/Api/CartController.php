<?php
namespace App\Http\Controllers\Api;
use App;
use App\Http\Controllers\Controller;
use App\Model\cart_info;
use App\Model\cart_model;
//use PushNotification;
use DB;
use Dingo\Api\Http\Request;
use JWTAuth;
use Session;
use Tymon\JWTAuth\Exceptions\JWTException;
use URL;
use App\Model\stores;
use App\Model\users;

class CartController extends Controller {
	const USER_SIGNUP_EMAIL_TEMPLATE = 1;
	const USERS_WELCOME_EMAIL_TEMPLATE = 3;
	const USERS_FORGOT_PASSWORD_EMAIL_TEMPLATE = 6;
	const USER_CHANGE_PASSWORD_EMAIL_TEMPLATE = 13;
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(Request $data) {
		$post_data = $data->all();
		if (isset($post_data['language']) && $post_data['language'] != '' && $post_data['language'] == 2) {
			App::setLocale('ar');
		} else {
			App::setLocale('en');
		}
	}

	/*
		     * order detail
	*/
	public function update_cart(Request $data) {
		$post_data = $data->all();
		if ($post_data['qty'] > 0) {
			$affected = DB::update('update cart_detail set quantity = ?,updated_at = NOW() where cart_detail_id = ?', array($post_data['qty'], $post_data['cart_detail_id']));
		} else {
			$affected = DB::update('delete from cart_detail where cart_detail_id = ?', array($post_data['cart_detail_id']));
			$cart_count = DB::table('cart_detail')
				->select('cart_detail_id')
				->where('cart_id', '=', $post_data['cart_id'])
				->first();
			if (count($cart_count) == 0) {
				DB::update('delete from cart where cart_id= ?', array($post_data['cart_id']));
				$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Your cart is empty now.")));
			}

		}
		$cart_items = $this->calculate_cart($post_data['language'], $post_data['user_id']);
		$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Cart has been updated successfully!"), "cart_items" => $cart_items['cart_items'], "total" => $cart_items['total'], "sub_total" => $cart_items['sub_total'], "minimum_order_amount" => $cart_items['minimum_order_amount'], "tax" => $cart_items['tax'], "delivery_cost" => (double) $cart_items['delivery_cost'], "tax_amount" => $cart_items['tax_amount']));
		return json_encode($result);
	}

	public function get_cart(Request $data) {
		$post_data = $data->all();
		$language_id = $post_data['language'];
		$cart_items = $this->calculate_cart($post_data['language'], $post_data['user_id']);
		//print_r($cart_items);exit;
		$result = array("response" => array("httpCode" => 200, "Message" => "Cart details", "cart_items" => $cart_items['cart_items'], "total" => $cart_items['total'], "sub_total" => $cart_items['sub_total'], "tax" => $cart_items['tax'], "tax_amount" => $cart_items['tax_amount'], "outlet_id" => $cart_items['outlet_id'], "outlet_name" => $cart_items['outlet_name'], "vendor_image" => $cart_items['vendor_image'], "minimum_order_amount" => $cart_items['minimum_order_amount'], "vendor_id" => $cart_items['vendor_id'], "delivery_cost" => (double) $cart_items['delivery_cost'], "delivery_time" => $cart_items['delivery_time']));
		return json_encode($result);
	}

	public function calculate_cart($language, $user_id,$delivery_address="",$latitude="",$longitude="") {
		$cart_data = cart_model::cart_items($language, $user_id);
		//print_r($cart_data);exit();
		$delivery_latlng = cart_model::delivery_address($delivery_address);

		$delivery_settings = $this->get_delivery_settings();
		$sub_total = $tax = $delivery_cost = 0;
		$vendor_id = $outlet_id = '';
		$minimum_order_amount = 0;
		$outlet_name = '';
		$vendor_image = '';
		$featured_image = '';
		$delivery_time = '';
		//print_r($cart_data);exit();

		foreach ($cart_data as $key => $items) {

		//	print_r($items->product_image);exit;

			$sub_total += $items->quantity * $items->discount_price;
			$tax += $items->service_tax;

			$no_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
			/*if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $items->product_image) && $items->product_image != '') {
				$product_image = url('/assets/admin/base/images/products/list/' . $items->product_image);
			}*/

			$path = url('/assets/admin/base/images/products/admin_products/');
            $productImage=json_decode($items->product_image);
          
            $image1 =$image2=$image3 =array();

            $image1[]= $no_image;

            if($productImage != "")
            {           
                foreach ($productImage as $keys => $value) {
                    if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $value) && $value != '') {
                        $image1[] =$path.'/'.$value;

                    }
                }
            }

            $cart_data[$key]->image_url = $image1;
            $cart_data[$key]->product_image = $image1;
            $cart_data[$key]->sub_category_id = (int)$items->sub_category_id;
           // $cart_data[$key]->product_info_image = $image1;
           // $cart_data[$key]->product_zoom_image = $image1;
           // print_r($cart_data[$key]);exit();
           
			//$cart_data[$key]->image_url = $product_image;
			$vendor_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
			if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $items->logo_image) && $items->logo_image != '') {
				$vendor_image = url('/assets/admin/base/images/vendors/logos/' . $items->logo_image);
			}

			$cart_data[$key]->vendor_image = $vendor_image;

			$featured_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
						if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/list/' . $items->featured_image) && $items->featured_image != '') {
							$featured_image = url('/assets/admin/base/images/vendors/list/' . $items->featured_image);
						}

			$cart_data[$key]->featured_image = $featured_image;



			$category_list = getCategoryListsById($items->sub_category_id);
			$cart_data[$key]->sub_category_name = isset($category_list->category_name) ? $category_list->category_name : '';

			//$category_list = getCategoryListsById($items->sub_category_id);

			$cart_data[$key]->unit = $items->unit;
			$cart_data[$key]->item_limit = isset($items->item_limit)?$items->item_limit:0;
			$cart_data[$key]->title = $items->title;
			$vendor_address = isset($items->contact_address)?$items->contact_address:'';
			$outlet_address = isset($items->outlet_address)?$items->outlet_address:'';
			$cart_data[$key]->discount_price = number_format($items->discount_price,2);
			//$cart_data[$key]->discount_price = number_format($items->quantity * $items->original_price);
			$outlet_name = $items->outlet_name;
			$delivery_time = $items->delivery_time;
			$vendor_id = $items->vendor_id;
			$outlet_id = $items->outlet_id;
			$minimum_order_amount = $items->minimum_order_amount;
			$delivery_settings = $this->get_delivery_settings();
		}

		$tax_amount = $sub_total * $tax / 100;
		//$tax_amount = $tax;
		$total = $sub_total + $tax_amount;
		//print_r($total);exit();

		if ($delivery_settings->on_off_status == 1) {
			if ($delivery_settings->delivery_type == 1) { //charges bydistance

				$del_latitude = isset($delivery_latlng->latitude)?$delivery_latlng->latitude:0;
				$del_longitude = isset($delivery_latlng->longitude)?$delivery_latlng->longitude:0;
				//getDistanceBetweenPoints(10.7882494, 76.618802, 11.01187, 76.8970233)
				$distance = getDistanceBetweenPoints($del_latitude, $del_longitude, $latitude, $longitude);
				//print_r($distance);exit;
				if($distance > $delivery_settings->delivery_km_fixed) {
					$dis = $distance -  $delivery_settings->delivery_km_fixed;
					$delivery_charge = $delivery_settings->delivery_cost_fixed;
					$delivery_charge1 = $dis * $delivery_settings->delivery_cost_variation;
					$tot = $delivery_charge+$delivery_charge1;
					//$total = $total + $tot;
					$delivery_cost = $tot;
				}else{
					//$total = $total + $delivery_settings->delivery_cost_fixed;
					$delivery_cost = $delivery_settings->delivery_cost_fixed;
				}
				
			}
			if ($delivery_settings->delivery_type == 2) {
				//$total = $total + $delivery_settings->flat_delivery_cost;
				$delivery_cost = $delivery_settings->flat_delivery_cost;
			}

		}

		return array("cart_items" => $cart_data, "total" => $total, "sub_total" => $sub_total, "delivery_cost" => $delivery_cost, "tax" => $tax, "vendor_id" => $vendor_id, "outlet_id" => $outlet_id, "minimum_order_amount" => $minimum_order_amount, "tax_amount" => $tax_amount, "outlet_name" => $outlet_name, "vendor_image" => $vendor_image, "featured_image" => $featured_image, "delivery_time" => $delivery_time, "vendor_address" => $vendor_address, "outlet_address" => $outlet_address);
	}

	/*
		     * order detail
	*/
	public function get_delivery_settings() {
		$delivery_settings = DB::table('delivery_settings')->first();
		return $delivery_settings;
	}
	public function add_cart(Request $data) {
		$post_data = $data->all();
		if (isset($post_data['language']) && $post_data['language'] == 2) {
            App::setLocale('ar');
        } else {
            App::setLocale('en');
        }
		$data = array();
		$rules = [
			'user_id' => ['required'],
			'vendors_id' => ['required'],
			'outlet_id' => ['required'],
			'product_id' => ['required'],
			'qty' => ['required'],
			'token' => ['required'],
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
				$check_auth = JWTAuth::toUser($post_data['token']);
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
							$last_quantity = $cdata[0]->quantity;
							$cart = Cart_model::find($cdata[0]->cart_id);
							$cart->updated_at = date("Y-m-d H:i:s");
							$cart->save();
							$cart_info = Cart_info::find($cdata[0]->cart_detail_id);
							$quntiry = $post_data['qty'];
							$cart_info->quantity = $quntiry;
							if ($quntiry == 0) {
								$affected = DB::update('delete from cart_detail where cart_detail_id = ?', array($cdata[0]->cart_detail_id));
							}
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
							if ($last_quantity > $post_data['qty']) {
								$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Cart has been deleted successfully!"), "type" => 2, "cart_count" => $cart_item));
							} else {
								$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.The product has been added to your cart"), "type" => 2, "cart_count" => $cart_item));
							}
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
							$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.The product has been added to your cart"), "type" => 1, "cart_count" => $cart_item));
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
						$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.your cart has items from another branch, please choose the same branch to continue"), "type" => 3, "cart_count" => $cart_item));
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
					$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.The product has been added to your cart"), "type" => 1, "cart_count" => $cart_item));
				}
			} catch (JWTException $e) {
				$result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
			} catch (TokenExpiredException $e) {
				$result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}
	public function delete_cart(Request $data) {
		$post_data = $data->all();
		if ($post_data['qty'] > 0) {
			$affected = DB::update('update cart_detail set quantity = ?,updated_at = NOW() where cart_detail_id = ?', array($post_data['qty'], $post_data['cart_detail_id']));
		} else {
			$affected = DB::update('delete from cart_detail where cart_detail_id = ?', array($post_data['cart_detail_id']));
			$cart_count = DB::table('cart_detail')
				->select('cart_detail_id')
				->where('cart_id', '=', $post_data['cart_id'])
				->first();
			if (count($cart_count) == 0) {
				DB::update('delete from cart where cart_id= ?', array($post_data['cart_id']));
				$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Your cart is empty now.")));
			}

		}
		$cart_items = $this->calculate_cart($post_data['language'], $post_data['user_id']);
		$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Cart has been deleted successfully!"), "cart_items" => $cart_items['cart_items'], "total" => $cart_items['total'], "sub_total" => $cart_items['sub_total'], "minimum_order_amount" => $cart_items['minimum_order_amount'], "tax" => $cart_items['tax'], "delivery_cost" => (double) $cart_items['delivery_cost'], "tax_amount" => $cart_items['tax_amount']));
		return json_encode($result);
	}

//mob apis:

// 	public function mbulkCartInsert(Request $data) {
// 		$post_data = $data->all();

// 		if (isset($post_data['language']) && $post_data['language'] == 2) {
//             App::setLocale('ar');
//         } else {
//             App::setLocale('en');
//         }
// 		$data = array();
// 		$rules = [
// 			'language' => ['required'],
// 			'userId' => ['required'],
// 			'vendorId' => ['required'],
// 			'outletId' => ['required'],
// 			'productDetails' => ['required'],
// 			'deviceToken' => ['required'],
// 		];
// 		$errors = $result = array();
// 		$validator = app('validator')->make($post_data, $rules);
// 		if ($validator->fails()) {
// 			$j = 0;
// 			foreach ($validator->errors()->messages() as $key => $value) {
// 				$errors[] = is_array($value) ? implode(',', $value) : $value;
// 			}
// 			$errors = implode(", \n ", $errors);
// 			$result = array("status" => 0, "message" => $errors);
// 		} else {
// 			try {
// 				// $check_auth = JWTAuth::toUser($post_data['token']);
// 				// print_r($post_data);exit;
// 				$productArr = json_decode($post_data['productDetails']);
// 				// print_r($productArr);exit;
// 				/* $productIdsArr = array();
// 	                foreach ($productArr as $pkey => $pvalue) {
// 	                    $productIdsArr[] = $pvalue->productId;
// */

// 				//find and delete existing
// 				$cdata = DB::table('cart')
// 					->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
// 					->select('cart.cart_id', 'cart_detail.product_id', 'cart_detail.quantity', 'cart_detail.cart_detail_id')
// 					->where("cart.user_id", "=", $post_data['userId'])
// 				//->where("cart.store_id","=",$post_data['vendorId'])
// 				//->where("cart.outlet_id","=",$post_data['outletId'])
// 				//->where("cart_detail.product_id","=",$pvalue->productId)
// 					->get();

// 				if (count($cdata)) {
// 					// print_r($cdata[0]->cart_id);
// 					$affected = DB::update('delete from cart_detail where cart_id = ? ', array($cdata[0]->cart_id));
// 					$affected2 = DB::update('delete from cart where cart_id = ? ', array($cdata[0]->cart_id));
// 				}

// 				$ucdata = DB::table('cart')
// 					->select('cart.cart_id')
// 					->where("cart.user_id", "=", $post_data['userId'])
// 					->get();
// 				if (count($ucdata)) {
// 					$uucdata = DB::table('cart')
// 						->select('cart.cart_id')
// 						->where("cart.user_id", "=", $post_data['userId'])
// 						->where("cart.store_id", "=", $post_data['vendorId'])
// 						->where("cart.outlet_id", "=", $post_data['outletId'])
// 						->get();
// 					if (count($uucdata)) {
// 						//update in cart table
// 						$cart = Cart_model::find($uucdata[0]->cart_id);
// 						$cart->updated_at = date("Y-m-d H:i:s");
// 						$cart->save();

// 						//find and delete existing
// 						$cdata = DB::table('cart')
// 							->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
// 							->select('cart.cart_id', 'cart_detail.product_id', 'cart_detail.quantity', 'cart_detail.cart_detail_id')
// 							->where("cart.user_id", "=", $post_data['userId'])
// 						//->where("cart.store_id","=",$post_data['vendorId'])
// 						//->where("cart.outlet_id","=",$post_data['outletId'])
// 						//->where("cart_detail.product_id","=",$pvalue->productId)
// 							->get();
// 						if (count($cdata)) {
// 							$affected = DB::update('delete from cart_detail where cart_detail_id = ? ', array($cdata[0]->cart_detail_id));

// 						}

// 						if (count($productArr) > 0) {
// 							foreach ($productArr as $pkey => $pvalue) {
// 								// print_r($pvalue);exit();

// 								//insert in cart detail
// 								$cart_info = new Cart_info;
// 								$cart_info->cart_id = $cart->cart_id;
// 								$cart_info->product_id = $pvalue->productId;
// 								$cart_info->quantity = $pvalue->productQty;
// 								$cart_info->created_at = date("Y-m-d H:i:s");
// 								$cart_info->updated_at = date("Y-m-d H:i:s");
// 								$cart_info->save();
// 							}
// 						}

// 						$cartCount = 0;
// 						if ($post_data['userId']) {
// 							$cdata = DB::table('cart')
// 								->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
// 								->select('cart_detail.cart_id', DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
// 								->where("cart.user_id", "=", $post_data['userId'])
// 								->groupby('cart_detail.cart_id')
// 								->get();
// 							if (count($cdata)) {
// 								$cartCount = $cdata[0]->cart_count;
// 							}
// 						}
// 						$cart_items = $this->calculate_cart($post_data['language'], $post_data['userId']);
// 						// print_r($cart_items);exit;

// 						$cartProductRes = array();
// 						if (count($cart_items['cart_items']) > 0) {
// 							foreach ($cart_items['cart_items'] as $key => $pvalue) {
// 								// print_r($key);exit;
// 								$cartProductRes[$key] = new \stdClass();
// 								$cartProductRes[$key]->productId = $pvalue->product_id;
// 								$cartProductRes[$key]->originalPrice = $pvalue->original_price;
// 								$cartProductRes[$key]->discountPrice = $pvalue->discount_price;
// 								$cartProductRes[$key]->productName = $pvalue->product_name;
// 								$cartProductRes[$key]->cartCount = $pvalue->quantity;
// 								$cartProductRes[$key]->unit = $pvalue->unit;
// 								$cartProductRes[$key]->productType = "1";

// 								$cartProductRes[$key]->weight = $pvalue->weight;
// 								$cartProductRes[$key]->description = $pvalue->description;

// 								$cartProductRes[$key]->productImage = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
// 								if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $pvalue->product_image) && $pvalue->product_image != '') {
// 									$cartProductRes[$key]->productImage = url('/assets/admin/base/images/products/list/' . $pvalue->product_image);
// 								}

// 							}
// 						}

// 						// print_r($cartProductRes);exit;

// // $result = array("status" => 1 , "message" => trans("messages.The product has been added to your cart"), "detail" => array("cartCount" => $cartCount, "cart_items"=>$cartProductRes,"total"=>$cart_items['total'],"sub_total"=>$cart_items['sub_total'],"tax"=>$cart_items['tax'],"tax_amount"=>$cart_items['tax_amount'],"outlet_id"=>$cart_items['outlet_id'],"outletName"=>$cart_items['outlet_name'],"vendor_image"=>$cart_items['vendor_image'],"minimum_order_amount"=>$cart_items['minimum_order_amount'],"vendor_id"=>$cart_items['vendor_id'],"delivery_cost"=>(double)$cart_items['delivery_cost'],"delivery_time" =>  $cart_items['delivery_time']));
// 						$billDetails = new \stdClass();
// 						$billDetails->billTitle = "SuperMarket Bill";
// 						$billDetails->subTotal = $cart_items['sub_total'];
// 						$billDetails->itemTotal = $cart_items['total'];
// 						$billDetails->deliveryCharge = (double) $cart_items['delivery_cost'];
// 						$billDetails->totalAmount = $cart_items['total'];
// 						$billDetails->offerApplied = "0%";
// 						$billDetails->offerApplied = 0;
// 						$billDetails->tax = $cart_items['tax'];
// 						$billDetails->tax = $cart_items['tax_amount'];

// 						$result = array("status" => 1, "message" => trans("messages.The product has been added to your cart"), "detail" => array(
// 							"cartCount" => $cartCount,
// 							"cartItems" => $cartProductRes,
// 							"outletName" => $cart_items['outlet_name'],
// 							"outletImage" => $cart_items['vendor_image'],
// 							"address" => "Peelamedu static",
// 							"billDetails" => $billDetails,
// 							"deliveryTime" => $cart_items['delivery_time']));
// 					} else {
// 						$cartCount = 0;
// 						if ($post_data['userId']) {
// 							$cdata = DB::table('cart')
// 								->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
// 								->select('cart_detail.cart_id', DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
// 								->where("cart.user_id", "=", $post_data['userId'])
// 								->groupby('cart_detail.cart_id')
// 								->get();
// 							if (count($cdata)) {
// 								$cartCount = $cdata[0]->cart_count;
// 							}
// 						}
// 						$result = array("status" => 3, "message" => trans("messages.your cart has items from another branch, please choose the same branch to continue"), "detail" => array("cartCount" => $cartCount));
// 					}
// 				} else {
// 					$cart = new Cart_model;
// 					$cart->user_id = $post_data['userId'];
// 					$cart->store_id = $post_data['vendorId'];
// 					$cart->outlet_id = $post_data['outletId'];
// 					$cart->cart_status = 1;
// 					$cart->created_at = date("Y-m-d H:i:s");
// 					$cart->updated_at = date("Y-m-d H:i:s");
// 					$cart->save();

// 					if (count($productArr) > 0) {
// 						foreach ($productArr as $pkey => $pvalue) {
// 							//insert in cart detail
// 							$cart_info = new Cart_info;
// 							$cart_info->cart_id = $cart->cart_id;
// 							$cart_info->product_id = $pvalue->productId;
// 							$cart_info->quantity = $pvalue->productQty;
// 							$cart_info->created_at = date("Y-m-d H:i:s");
// 							$cart_info->updated_at = date("Y-m-d H:i:s");
// 							$cart_info->save();
// 						}
// 					}

// 					$cartCount = 0;
// 					if ($post_data['userId']) {
// 						$cdata = DB::table('cart')
// 							->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
// 							->select('cart_detail.cart_id', DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
// 							->where("cart.user_id", "=", $post_data['userId'])
// 							->groupby('cart_detail.cart_id')
// 							->get();
// 						if (count($cdata)) {
// 							$cartCount = $cdata[0]->cart_count;
// 						}
// 					}

// 					$cart_items = $this->calculate_cart($post_data['language'], $post_data['userId']);

// 					$cartProductRes = array();
// 					if (count($cart_items['cart_items']) > 0) {
// 						foreach ($cart_items['cart_items'] as $key => $pvalue) {
// 							// print_r($key);exit;
// 							$cartProductRes[$key] = new \stdClass();
// 							$cartProductRes[$key]->productId = $pvalue->product_id;
// 							$cartProductRes[$key]->originalPrice = $pvalue->original_price;
// 							$cartProductRes[$key]->discountPrice = $pvalue->discount_price;
// 							$cartProductRes[$key]->productName = $pvalue->product_name;
// 							$cartProductRes[$key]->cartCount = $pvalue->quantity;
// 							$cartProductRes[$key]->unit = $pvalue->unit;
// 							$cartProductRes[$key]->productType = "1";

// 							$cartProductRes[$key]->weight = $pvalue->weight;
// 							$cartProductRes[$key]->description = $pvalue->description;

// 							$cartProductRes[$key]->productImage = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
// 							if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $pvalue->product_image) && $pvalue->product_image != '') {
// 								$cartProductRes[$key]->productImage = url('/assets/admin/base/images/products/list/' . $pvalue->product_image);
// 							}

// 						}
// 					}

// 					// print_r($cartProductRes);exit;

// // $result = array("status" => 1 , "message" => trans("messages.The product has been added to your cart"), "detail" => array("cartCount" => $cartCount, "cart_items"=>$cartProductRes,"total"=>$cart_items['total'],"sub_total"=>$cart_items['sub_total'],"tax"=>$cart_items['tax'],"tax_amount"=>$cart_items['tax_amount'],"outlet_id"=>$cart_items['outlet_id'],"outletName"=>$cart_items['outlet_name'],"vendor_image"=>$cart_items['vendor_image'],"minimum_order_amount"=>$cart_items['minimum_order_amount'],"vendor_id"=>$cart_items['vendor_id'],"delivery_cost"=>(double)$cart_items['delivery_cost'],"delivery_time" =>  $cart_items['delivery_time']));
// 					$billDetails = new \stdClass();
// 					$billDetails->billTitle = "SuperMarket Bill";
// 					$billDetails->subTotal = $cart_items['sub_total'];
// 					$billDetails->itemTotal = $cart_items['total'];
// 					$billDetails->deliveryCharge = (double) $cart_items['delivery_cost'];
// 					$billDetails->totalAmount = $cart_items['total'];
// 					$billDetails->offerApplied = "0%";
// 					$billDetails->offerApplied = 0;
// 					$billDetails->tax = $cart_items['tax'];
// 					$billDetails->tax = $cart_items['tax_amount'];

// 					$result = array("status" => 1, "message" => trans("messages.The product has been added to your cart"), "detail" => array(
// 						"cartCount" => $cartCount,
// 						"cartItems" => $cartProductRes,
// 						"outletName" => $cart_items['outlet_name'],
// 						"outletImage" => $cart_items['vendor_image'],
// 						"address" => "Peelamedu static",
// 						"billDetails" => $billDetails,
// 						"deliveryTime" => $cart_items['delivery_time']));

// 					//print_r($cart_items);exit;
// 					// $result = array("status" => 1 , "message" => trans("messages.The product has been added to your cart"), "detail" => array("cartCount" => $cartCount, "cart_items"=>$cart_items['cart_items'],"total"=>$cart_items['total'],"sub_total"=>$cart_items['sub_total'],"tax"=>$cart_items['tax'],"tax_amount"=>$cart_items['tax_amount'],"outlet_id"=>$cart_items['outlet_id'],"outlet_name"=>$cart_items['outlet_name'],"vendor_image"=>$cart_items['vendor_image'],"minimum_order_amount"=>$cart_items['minimum_order_amount'],"vendor_id"=>$cart_items['vendor_id'],"delivery_cost"=>(double)$cart_items['delivery_cost'],"delivery_time" =>  $cart_items['delivery_time']));
// 				}
// 			} catch (JWTException $e) {
// 				$result = array("status" => 0, "message" => trans("messages.Kindly check the user credentials"));
// 			} catch (TokenExpiredException $e) {
// 				$result = array("status" => 0, "message" => trans("messages.Kindly check the user credentials"));
// 			}
// 		}
// 		return json_encode($result, JSON_UNESCAPED_UNICODE);
// 	}

	public function maddCart(Request $data) {
		$post_data = $data->all();
		if (isset($post_data['language']) && $post_data['language'] == 2) {
            App::setLocale('ar');
        } else {
            App::setLocale('en');
        }
		$data = array();
		$rules = [
			'userId' => ['required'],
			'vendorsId' => ['required'],
			'outletId' => ['required'],
			'productId' => ['required'],
			'qty' => ['required'],
			'token' => ['required'],
		];
		$errors = $result = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("status" => 0, "message" => $errors);
		} else {
			try {
				$check_auth = JWTAuth::toUser($post_data['token']);
				$ucdata = DB::table('cart')
					->select('cart.cart_id')
					->where("cart.user_id", "=", $post_data['userId'])
					->get();
				if (count($ucdata)) {
					$uucdata = DB::table('cart')
						->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
						->select('cart.cart_id', 'cart_detail.product_id', 'cart_detail.quantity', 'cart_detail.cart_detail_id')
						->where("cart.user_id", "=", $post_data['userId'])
						->where("cart.store_id", "=", $post_data['vendorsId'])
						->where("cart.outlet_id", "=", $post_data['outletId'])
						->get();
					if (count($uucdata)) {
						$cdata = DB::table('cart')
							->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
							->select('cart.cart_id', 'cart_detail.product_id', 'cart_detail.quantity', 'cart_detail.cart_detail_id')
							->where("cart.user_id", "=", $post_data['userId'])
							->where("cart.store_id", "=", $post_data['vendorsId'])
							->where("cart.outlet_id", "=", $post_data['outletId'])
							->where("cart_detail.product_id", "=", $post_data['productId'])
							->get();
						if (count($cdata)) {
							$last_quantity = $cdata[0]->quantity;
							$cart = Cart_model::find($cdata[0]->cart_id);
							$cart->updated_at = date("Y-m-d H:i:s");
							$cart->save();
							$cart_info = Cart_info::find($cdata[0]->cart_detail_id);
							$quntiry = $post_data['qty'];
							$cart_info->quantity = $quntiry;
							if ($quntiry == 0) {
								$affected = DB::update('delete from cart_detail where cart_detail_id = ?', array($cdata[0]->cart_detail_id));
							}
							$cart_info->updated_at = date("Y-m-d H:i:s");
							$cart_info->save();
							$cart_item = 0;
							if ($post_data['userId']) {
								$cdata = DB::table('cart')
									->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
									->select('cart_detail.cart_id', DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
									->where("cart.user_id", "=", $post_data['userId'])
									->groupby('cart_detail.cart_id')
									->get();
								if (count($cdata)) {
									$cart_item = $cdata[0]->cart_count;
								}
							}

							$result = array("status" => 2, "message" => trans("messages.Cart has been updated successfully!"), "detail" => array("cartCount" => $cart_item));
							/*if($last_quantity > $post_data['qty'])
								                            {
								                                $result = array("response" => array("httpCode" => 200 , "Message" => trans("messages.Cart has been deleted successfully!"),"type" => 2,"cart_count" => $cart_item));
								                            }
								                            else {
								                                $result = array("response" => array("httpCode" => 200 , "Message" => trans("messages.The product has been added to your cart"),"type" => 2,"cart_count" => $cart_item));
							*/
						} else {
							$ccdata = DB::table('cart')
								->select('cart.cart_id')
								->where("cart.user_id", "=", $post_data['userId'])
								->where("cart.store_id", "=", $post_data['vendorsId'])
								->where("cart.outlet_id", "=", $post_data['outletId'])
								->get();
							if (count($ccdata)) {
								$cart = Cart_model::find($ccdata[0]->cart_id);
								$cart->updated_at = date("Y-m-d H:i:s");
								$cart->save();
							} else {
								$cart = new Cart_model;
								$cart->user_id = $post_data['userId'];
								$cart->store_id = $post_data['vendorsId'];
								$cart->outlet_id = $post_data['outletId'];
								$cart->cart_status = 1;
								$cart->created_at = date("Y-m-d H:i:s");
								$cart->updated_at = date("Y-m-d H:i:s");
								$cart->save();
							}
							$cart_info = new Cart_info;
							$cart_info->cart_id = $cart->cart_id;
							$cart_info->product_id = $post_data['productId'];
							$cart_info->quantity = $post_data['qty'];
							$cart_info->created_at = date("Y-m-d H:i:s");
							$cart_info->updated_at = date("Y-m-d H:i:s");
							$cart_info->save();
							$cart_item = 0;
							if ($post_data['userId']) {
								$cdata = DB::table('cart')
									->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
									->select('cart_detail.cart_id', DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
									->where("cart.user_id", "=", $post_data['userId'])
									->groupby('cart_detail.cart_id')
									->get();
								if (count($cdata)) {
									$cart_item = $cdata[0]->cart_count;
								}
							}
							$result = array("status" => 1, "message" => trans("messages.The product has been added to your cart"), "detail" => array("cartCount" => $cart_item));
						}
					} else {
						$cart_item = 0;
						if ($post_data['userId']) {
							$cdata = DB::table('cart')
								->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
								->select('cart_detail.cart_id', DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
								->where("cart.user_id", "=", $post_data['userId'])
								->groupby('cart_detail.cart_id')
								->get();
							if (count($cdata)) {
								$cart_item = $cdata[0]->cart_count;
							}
						}
						$result = array("status" => 3, "message" => trans("messages.your cart has items from another branch, please choose the same branch to continue"), "detail" => array("cartCount" => $cart_item));
					}
				} else {
					$cart = new Cart_model;
					$cart->user_id = $post_data['userId'];
					$cart->store_id = $post_data['vendorsId'];
					$cart->outlet_id = $post_data['outletId'];
					$cart->cart_status = 1;
					$cart->created_at = date("Y-m-d H:i:s");
					$cart->updated_at = date("Y-m-d H:i:s");
					$cart->save();

					$cart_info = new Cart_info;
					$cart_info->cart_id = $cart->cart_id;
					$cart_info->product_id = $post_data['productId'];
					$cart_info->quantity = $post_data['qty'];
					$cart_info->created_at = date("Y-m-d H:i:s");
					$cart_info->updated_at = date("Y-m-d H:i:s");
					$cart_info->save();
					$cart_item = 0;
					if ($post_data['userId']) {
						$cdata = DB::table('cart')
							->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
							->select('cart_detail.cart_id', DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
							->where("cart.user_id", "=", $post_data['userId'])
							->groupby('cart_detail.cart_id')
							->get();
						if (count($cdata)) {
							$cart_item = $cdata[0]->cart_count;
						}
					}
					$result = array("status" => 1, "message" => trans("messages.The product has been added to your cart"), "detail" => array("cartCount" => $cart_item));
				}
			} catch (JWTException $e) {
				$result = array("status" => 0, "message" => trans("messages.Kindly check the user credentials"));
			} catch (TokenExpiredException $e) {
				$result = array("status" => 0, "message" => trans("messages.Kindly check the user credentials"));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	public function mbulkCartInsert1(Request $data) {
		$post_data = $data->all();
		//print_r($post_data);exit;

				/*if (isset($post_data['language']) && $post_data['language'] == 2) {
		           App::setLocale('ar');
		        } else {
		            App::setLocale('en');
		        }*/
		if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

		$data = array();
		$rules = [
			//'language' => ['required'],
			'userId' => ['required'],
			'vendorId' => ['required'],
			'outletId' => ['required'],
			'productDetails' => ['required'],
			'deviceToken' => ['required'],
			'paymentGatewayId' => ['required'],
		];
		$errors = $result = $coupon_user_limit_details = $coupon_details = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("status" => 0, "message" => $errors);
		} else {
			try
			{
				// $check_auth = JWTAuth::toUser($post_data['token']);
				// print_r($post_data);exit;
				//$productArr = json_decode($post_data['productDetails']);
				// print_r($productArr);exit;
				/* $productIdsArr = array();
	                foreach ($productArr as $pkey => $pvalue) {
					                    $productIdsArr[] = $pvalue->productId;
				*/
				// $outn=json_encode($post_data['productDetails']);
								// print_r($outn);
								// echo($post_data['productDetails']);exit;

				//working for postman
				//$productDetails_arr= json_decode($post_data['productDetails'],true);
				//working for mobile
				$currency =getCurrencyList();
				$currency_code = isset($currency[0]->currency_code)?$currency[0]->currency_code:'AED';
				//print_r($currency[0]->currency_code);exit;

				$address = DB::table('user_address')
					->select('user_address.address', 'user_address.latitude', 'user_address.longitude', 'user_address.address_type', 'user_address.user_id', 'user_address.id as address_id','user_address.land_mark','user_address.home_no','user_address.created_date','user_address.modified_date','user_address.active_status','user_address.type_text','user_address.id'/*,'address_infos.type_text as type_text'*/)
					//->leftJoin('address_infos', 'address_infos.address_id', '=', 'user_address.id')
					->where('user_id', '=', $post_data['userId'])

					->orderBy('user_address.id', 'desc')
					->get();
				//print_r($address[0]->address_id);exit();

				$min_amnt_order = DB::table('delivery_settings')
					->select('minimum_order_amount')
					->get();
				$min_amnt = isset($min_amnt_order[0]->minimum_order_amount)?$min_amnt_order[0]->minimum_order_amount:0;
				//print_r($min_amnt);exit;
				
				$users_detail = DB::table('users')
					->where('id', '=', $post_data['userId'])
					->select('wallet_amount')
					->get();
				$user_wallet  = isset($users_detail[0]->wallet_amount)?$users_detail[0]->wallet_amount:0;
				//print_r($user_wallet);exit;


				/*outletlat lng*/
				$outletdetail = DB::table('outlets')
					->where('id', '=', $post_data['outletId'])
					->select('latitude','longitude')
					->get();
				$outlet_det = $outletdetail[0];
				//print_r($outlet_det);exit();
				/*outletlat lng*/

				$post_data['promoCode'] = isset($post_data['promoCode'])?$post_data['promoCode']:'';
				//$post_data['promoCode'] = 'BR30HFD';
				
				$current_date = strtotime(date('Y-m-d'));
				$coupon_details = DB::table('coupons')
						->select('coupons.id as coupon_id', 'coupon_type', 'offer_amount','offer_type', 'coupon_code', 'start_date', 'end_date', 'minimum_order_amount')
						->leftJoin('coupon_outlet','coupon_outlet.coupon_id','=','coupons.id')
						->where('coupon_code','=',$post_data['promoCode'])
						->where('coupon_outlet.outlet_id','=',$post_data['outletId'])
						->first();
				//print_r($coupon_details);exit;
				/*$coupon_details->start_date = "2019-06-04 12:28:00";
				$coupon_details->end_date = "2019-06-28 12:28:00";*/
				$res =0;
				$msg ='';
				if($post_data['promoCode'] !='')
				{ 
					$res=1;
					if(count($coupon_details) == 0)
					{
						$res =-1;
						$msg = trans("messages.Ivalid promocode");
					}
					else if((strtotime($coupon_details->start_date) <= $current_date) && (strtotime($coupon_details->end_date) >= $current_date)){
						$coupon_user_limit_details = DB::table('user_cart_limit')
						->select('cus_order_count','user_limit','total_order_count','coupon_limit')
						->where('customer_id','=',$post_data['userId'])
						->where('coupon_code','=',$post_data['promoCode'])
						->first();
						if(count($coupon_user_limit_details)>0){   
							if($coupon_user_limit_details->cus_order_count >= $coupon_user_limit_details->user_limit)
							{
								$res =-1;
								$msg = trans("messages.Max user limit has been crossed");


							}
							if($coupon_user_limit_details->total_order_count >= $coupon_user_limit_details->coupon_limit)
							{
								$res =-1;
								$msg = trans("messages.Max coupon limit has been crossed");
							}
						}
					}
				}
				$offer_amount =0.00;
			//	print_r($res);exit;

				$productDetails_arr = $post_data['productDetails'];
				//print_r($productDetails_arr);exit();
				$payment_gateway_details = $this->get_payment_gateway($post_data['paymentGatewayId'], $post_data['language']);
				//print_r($payment_gateway_details);exit();

				if (count($productDetails_arr)) {
					foreach($productDetails_arr as $key => $value) {
						//find and delete existing
						$cdata = DB::table('cart')
							->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
							->select('cart.cart_id', 'cart_detail.product_id', 'cart_detail.quantity', 'cart_detail.cart_detail_id')
							->where("cart.user_id", "=", $post_data['userId'])
						// ->where("cart.store_id","=",$post_data['vendorId'])
						// ->where("cart.outlet_id","=",$post_data['outletId'])
						// ->where("cart_detail.product_id","=",$value['productId'])
							->get();

						if (count($cdata)) {
							// print_r($cdata[0]->cart_id);
							$affected = DB::update('delete from cart_detail where cart_id = ? ', array($cdata[0]->cart_id));
							$affected2 = DB::update('delete from cart where cart_id = ? ', array($cdata[0]->cart_id));
						}
					}

					$ucdata = DB::table('cart')
						->select('cart.cart_id')
						->where("cart.user_id", "=", $post_data['userId'])
						->get();
						//print_r($ucdata);exit;

					if (count($ucdata)) {
						//print_r("expression");exit();
						$uucdata = DB::table('cart')
							->select('cart.cart_id')
							->where("cart.user_id", "=", $post_data['userId'])
						// ->where("cart.store_id","=",$post_data['vendorId'])
						// ->where("cart.outlet_id","=",$post_data['outletId'])
							->get();
						if (count($uucdata)) {
							//update in cart table
							$cart = Cart_model::find($uucdata[0]->cart_id);
							$cart->store_id = $post_data['vendorId'];
							$cart->outlet_id = $post_data['outletId'];
							$cart->updated_at = date("Y-m-d H:i:s");
							$cart->save();

							foreach ($productDetails_arr as $key => $value) {
								//find and delete existing
								$cdata = DB::table('cart')
									->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
									->select('cart.cart_id', 'cart_detail.product_id', 'cart_detail.quantity', 'cart_detail.cart_detail_id')
									->where("cart.user_id", "=", $post_data['userId'])
									->where("cart.store_id", "=", $post_data['vendorId'])
									->where("cart.outlet_id", "=", $post_data['outletId'])
									->where("cart_detail.product_id", "=", $value['productId'])
									->get();
								if (count($cdata)) {
									$affected = DB::update('delete from cart_detail where cart_detail_id = ? ', array($cdata[0]->cart_detail_id));

								}
							}

							if (count($productDetails_arr) > 0) {
								foreach ($productDetails_arr as $pkey => $pvalue) {
									// print_r($pvalue);exit();

									//insert in cart detail
									$cart_info = new Cart_info;
									$cart_info->cart_id = $cart->cart_id;
									$cart_info->product_id = $pvalue['productId'];
									$cart_info->quantity = $pvalue['productQty'];
									$cart_info->created_at = date("Y-m-d H:i:s");
									$cart_info->updated_at = date("Y-m-d H:i:s");
									$cart_info->save();
								}
							}

							$cartCount = 0;
							if ($post_data['userId']) {
								$cdata = DB::table('cart')
									->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
									->select('cart_detail.cart_id', DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
									->where("cart.user_id", "=", $post_data['userId'])
									->groupby('cart_detail.cart_id')
									->get();
								if (count($cdata)) {
									$cartCount = $cdata[0]->cart_count;
								}
							}
							
							$address_new= isset($address[0])?$address[0]:array();
							$address_id= isset($address_new->address_id)?$address_new->address_id:0;
							$cart_items = $this->calculate_cart($post_data['language'], $post_data['userId'],$address_id,$outlet_det->latitude,$outlet_det->longitude);
							//$cart_items = $this->calculate_cart($post_data['language'], $post_data['userId']);
							//print_r($cart_items);exit;

							$cartProductRes = array();
							if (count($cart_items['cart_items']) > 0) {
								foreach ($cart_items['cart_items'] as $key => $pvalue) {
									// print_r($key);exit;
									$cartProductRes[$key] = new \stdClass();
									$cartProductRes[$key]->productId = $pvalue->product_id;
									$cartProductRes[$key]->originalPrice = $pvalue->original_price;
									$cartProductRes[$key]->discountPrice = $pvalue->discount_price;
									$cartProductRes[$key]->productName = $pvalue->product_name;
									$cartProductRes[$key]->cartCount = $pvalue->quantity;
									$cartProductRes[$key]->unit = $pvalue->unit;
									$cartProductRes[$key]->productType = "1";
									$cartProductRes[$key]->weight = $pvalue->weight;
									$cartProductRes[$key]->description = $pvalue->description;
									$cartProductRes[$key]->productImage = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
									/*if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $pvalue->product_image) && $pvalue->product_image != '') {
										$cartProductRes[$key]->productImage = url('/assets/admin/base/images/products/list/' . $pvalue->product_image);
									}*/
								}

								/*$result = array("status" => 1, "message" => trans("messages.The product has been added to your cart"), "detail" => array("cartCount" => $cartCount, "cart_items" => $cart_items['cart_items'], "total" => $cart_items['total'], "sub_total" => $cart_items['sub_total'], "tax" => $cart_items['tax'], "tax_amount" => $cart_items['tax_amount'], "outletId" => $cart_items['outlet_id'], "outletName" => $cart_items['outlet_name'], "vendor_image" => $cart_items['vendor_image'], "minimum_order_amount" => $cart_items['minimum_order_amount'], "vendorId" => $cart_items['vendor_id'], "delivery_cost" => (double) $cart_items['delivery_cost'], "delivery_time" => $cart_items['delivery_time'], "payment_gateway_details" => $payment_gateway_details));*/
								$show_msg =0;
								$total_pay =0;

								$discountMsg ="";
								
								if($cart_items['total'] <= $min_amnt){ // for min amount of delivery check
									$res = -2;
									$p_amnt = abs($cart_items['total'] - $min_amnt );
									$msg = $currency_code." ".$p_amnt." ".trans("messages.to proceed");
									if(count($coupon_details) != 0){
										$discountMsg = "".trans("messages.To apply coupon you need") .$currency_code." ".$p_amnt." ".trans("messages.amount");
									}
								}	

								
								$savedAmount =0;

								if($res == 1 && $coupon_details->minimum_order_amount <= $cart_items['total'])	{ //for promocode
									if($coupon_details->offer_type == 1)
										{ 
											$offer_amount =$coupon_details->offer_amount;
				                        	//$total_pay = $cart_items['total'] - $offer_amount;
				                        	if($cart_items['total'] > $offer_amount )
											{
												$total_pay = $cart_items['total'] - $offer_amount;
												$savedAmount = $total_pay;

											}else
											{
												$total_pay = 0;
												$savedAmount = $cart_items['total'];

											}
											$cart_items['total'] =$total_pay;
											
										}
									else
									{ 
										$offer_amount =$coupon_details->offer_amount;

										$offer_amount = (($cart_items['total']*$offer_amount)/100);
										$coupon_details->offer_amount = $offer_amount;

			                           	$total_pay = ($cart_items['total'] - $offer_amount);

			                           	$cart_items['total'] = $total_pay;
									}
									$show_msg = 1;

								}else if($res == 1)
								{
									$res =-1;
									$msg = trans("messages.minimum order amount should be ").$currency_code." ".$coupon_details->minimum_order_amount .trans("messages.for applying coupon");
								}

								//$cart_items['total'] =15;
								$cart_items['total'] = $cart_items['delivery_cost'] + $cart_items['total']; //delivery charge
								$actual_amount = $cart_items['total'];

								$tot = $user_wallet-$cart_items['total'];
								($user_wallet >$cart_items['total'])?$used_walletamt=$cart_items['total']:$used_walletamt = $user_wallet;
								$cart_items['total'] =  abs($tot);




								/*	$result = array("status" => 1, "message" => trans("messages.Coupon applied Successfully"), "detail" => array("cartCount" => $cartCount, "cart_items" => $cart_items['cart_items'], "total" => $cart_items['total'], "sub_total" => $cart_items['sub_total'], "tax" => $cart_items['tax'], "tax_amount" => $cart_items['tax_amount'], "outletId" => $cart_items['outlet_id'], "outletName" => $cart_items['outlet_name'], "vendor_image" => $cart_items['vendor_image'], "minimum_order_amount" => $cart_items['minimum_order_amount'], "vendorId" => $cart_items['vendor_id'], "delivery_cost" => (double) $cart_items['delivery_cost'], "delivery_time" => $cart_items['delivery_time'], "payment_gateway_details" => $payment_gateway_details,"coupon_details"=>$coupon_details,"coupon_user_limit_details"=>$coupon_user_limit_details,"total_pay"=>$total_pay,"address_details"=>$address,"show_message"=>1));
										}else*/
								//print_r("expression");exit;
								if($res == -1){ //invalid promocode
										$result = array("status" => 3, "message" => $msg, "detail" => array("cartCount" => $cartCount, "cart_items" => $cart_items['cart_items'], "total" => (String)$cart_items['total'], "sub_total" => (String)$cart_items['sub_total'], "tax" => $cart_items['tax'], "tax_amount" =>(String) $cart_items['tax_amount'], "outletId" => $cart_items['outlet_id'], "outletName" => $cart_items['outlet_name'], "vendor_image" => $cart_items['vendor_image'],"featured_image" => $cart_items['featured_image'], "minimum_order_amount" => $cart_items['minimum_order_amount'], "vendorId" => $cart_items['vendor_id'], "delivery_cost" => (String) $cart_items['delivery_cost'], "delivery_time" => $cart_items['delivery_time'], "payment_gateway_details" => $payment_gateway_details,"address_details"=>$address,"used_walletamt"=>(String)$used_walletamt,"actual_amount"=>(String)$actual_amount,"couponDiscountamt"=>(String)$offer_amount,"discountMsg"=>$discountMsg,"vendorAddress"=>$cart_items['vendor_address'],"outletAddress"=>$cart_items['outlet_address']
											));
								}


								elseif($res == -2){ //invalid promocode
										$result = array("status" => 4, "message" => $msg, "detail" => array("cartCount" => $cartCount, "cart_items" => $cart_items['cart_items'], "total" => number_format( (String) $cart_items['total'], 2, '.', ''), "sub_total" => number_format( (String) $cart_items['sub_total'], 2, '.', ''), "tax" => $cart_items['tax'], "tax_amount" => number_format( (String) $cart_items['tax_amount'], 2, '.', ''), "outletId" => $cart_items['outlet_id'], "outletName" => $cart_items['outlet_name'], "vendor_image" => $cart_items['vendor_image'],"featured_image" => $cart_items['featured_image'], "minimum_order_amount" => $cart_items['minimum_order_amount'], "vendorId" => $cart_items['vendor_id'], "delivery_cost" => number_format( (String) $cart_items['delivery_cost'], 2, '.', ''), "delivery_time" => $cart_items['delivery_time'], "payment_gateway_details" => $payment_gateway_details,"address_details"=>$address,"used_walletamt"=>number_format( (String) $used_walletamt, 2, '.', ''),"actual_amount"=>number_format( (String) $actual_amount, 2, '.', ''),"couponDiscountamt"=>number_format( (String) $offer_amount, 2, '.', ''),"discountMsg"=>$discountMsg,"vendorAddress"=>$cart_items['vendor_address']
											,"outletAddress"=>$cart_items['outlet_address']));
								}else { //withour promocode

									$result = array("status" => 1, "message" => trans("messages.The product has been added to your cart"), "detail" => array("cartCount" => $cartCount, "cart_items" => $cart_items['cart_items'], "total" => number_format( (String) $cart_items['total'], 2, '.', ''), "sub_total" => number_format( (String) $cart_items['sub_total'], 2, '.', ''), "tax" => $cart_items['tax'], "tax_amount" => number_format( (String) $cart_items['tax_amount'], 2, '.', ''), "outletId" => $cart_items['outlet_id'], "outletName" => $cart_items['outlet_name'], "vendor_image" => $cart_items['vendor_image'],"featured_image" => $cart_items['featured_image'], "minimum_order_amount" => $cart_items['minimum_order_amount'], "vendorId" => $cart_items['vendor_id'], "delivery_cost" => number_format( (String) $cart_items['delivery_cost'], 2, '.', ''), "delivery_time" => $cart_items['delivery_time'], "payment_gateway_details" => $payment_gateway_details,"coupon_details"=>$coupon_details,"coupon_user_limit_details"=>$coupon_user_limit_details,"address_details"=>$address,"show_message"=>$show_msg,"used_walletamt"=>number_format( (String) $used_walletamt, 2, '.', ''),"actual_amount"=>number_format( (String) $actual_amount, 2, '.', ''),"couponDiscountamt"=>number_format( (String) $offer_amount, 2, '.', ''),"savedAmount"=>number_format( (String) $savedAmount, 2, '.', ''),"vendorAddress"=>$cart_items['vendor_address']
										,"outletAddress"=>$cart_items['outlet_address']));
									}
							} else {
								$result = array("status" => 0, "message" => trans("messages.no products"));
							}

							//print_r($cartProductRes);exit;

						} else {
							$cartCount = 0;
							if ($post_data['userId']) {
								$cdata = DB::table('cart')
									->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
									->select('cart_detail.cart_id', DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
									->where("cart.user_id", "=", $post_data['userId'])
									->groupby('cart_detail.cart_id')
									->get();
								if (count($cdata)) {
									$cartCount = $cdata[0]->cart_count;
								}
							}
							$result = array("status" => 3, "message" => trans("messages.your cart has items from another branch, please choose the same branch to continue"), "detail" => array("cartCount" => $cartCount));
						}
					} else {
						//print_r("dddddd");exit();
						$cart = new Cart_model;
						$cart->user_id = $post_data['userId'];
						$cart->store_id = $post_data['vendorId'];
						$cart->outlet_id = $post_data['outletId'];
						$cart->cart_status = 1;
						$cart->created_at = date("Y-m-d H:i:s");
						$cart->updated_at = date("Y-m-d H:i:s");
						$cart->save();

						if (count($productDetails_arr) > 0) {
							foreach($productDetails_arr as $pkey => $pvalue) {
								//insert in cart detail
								$cart_info = new Cart_info;
								$cart_info->cart_id = $cart->cart_id;
								$cart_info->product_id = $pvalue['productId'];
								$cart_info->quantity = $pvalue['productQty'];
								$cart_info->created_at = date("Y-m-d H:i:s");
								$cart_info->updated_at = date("Y-m-d H:i:s");
								$cart_info->save();
							}
						}

						$cartCount = 0;
						if ($post_data['userId']) {
							$cdata = DB::table('cart')
								->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
								->select('cart_detail.cart_id', DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
								->where("cart.user_id", "=", $post_data['userId'])
								->groupby('cart_detail.cart_id')
								->get();
							if (count($cdata)) {
								$cartCount = $cdata[0]->cart_count;
							}
						}
						$address_new= isset($address[0])?$address[0]:array();
						$address_id= isset($address_new->address_id)?$address_new->address_id:0;

						//echo"<pre>";print_r($address_id);exit;

						$cart_items = $this->calculate_cart($post_data['language'], $post_data['userId'],$address_id,$outlet_det->latitude,$outlet_det->longitude);
						//print_r($cart_items);exit();

						//$cart_items = $this->calculate_cart($post_data['language'], $post_data['userId']);

						

						$cartProductRes = array();
						if (count($cart_items['cart_items']) > 0) {
							foreach ($cart_items['cart_items'] as $key => $pvalue) {
								// print_r($key);exit;
								$cartProductRes[$key] = new \stdClass();
								$cartProductRes[$key]->productId = $pvalue->product_id;
								$cartProductRes[$key]->originalPrice = $pvalue->original_price;
								$cartProductRes[$key]->discountPrice = $pvalue->discount_price;
								$cartProductRes[$key]->productName = $pvalue->product_name;
								$cartProductRes[$key]->cartCount = $pvalue->quantity;
								$cartProductRes[$key]->unit = $pvalue->unit;
								$cartProductRes[$key]->productType = "1";

								$cartProductRes[$key]->weight = $pvalue->weight;
								$cartProductRes[$key]->description = $pvalue->description;

								$cartProductRes[$key]->productImage = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
								/*if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $pvalue->product_image) && $pvalue->product_image != '') {
									$cartProductRes[$key]->productImage = url('/assets/admin/base/images/products/list/' . $pvalue->product_image);
								}*/
							}
							//$res =1;						
							$show_msg =0;
								$total_pay = $wallet = 0;
								$discountMsg ="";

								if($cart_items['total'] <= $min_amnt){ // for min amount of delivery check
									$res = -2;
									$p_amnt = abs($cart_items['total'] - $min_amnt );
									//print_r($p_amnt);exit();
									//print_r($currency);exit;
									$msg = $currency_code." ".number_format( (String) $p_amnt, 2, '.', '')." ".trans("messages.to proceed");

									if(count($coupon_details) != 0){
										$discountMsg = "".trans("messages.To apply coupon you need") .$currency_code." ". number_format( (String) $p_amnt, 2, '.', '')." ".trans("messages.amount");
									}


								}	

								
								$savedAmount =0;
								//$actual_amount =$cart_items['total'];

								 //for promocode
								if($res == 1 && $coupon_details->minimum_order_amount <= $cart_items['total'])	{ //for promocode 

									if($coupon_details->offer_type == 1){ 																

										$offer_amount =$coupon_details->offer_amount;

			                        	//$total_pay = $cart_items['total'] - $offer_amount;
			                        	if($cart_items['total'] > $offer_amount )
										{
											$total_pay = $cart_items['total'] - $offer_amount;
											$savedAmount = $offer_amount;

										}else
										{
											$total_pay = 0;
											$savedAmount = $cart_items['total'];
										}
										$cart_items['total'] = $total_pay;
									}
									else
									{ 																											
								
										$offer_amount =$coupon_details->offer_amount;
										$offer_amount = (($cart_items['total']*$offer_amount)/100);
										//print_r($offer_amount);exit();

										$coupon_details->offer_amount = $offer_amount;
										$savedAmount = $offer_amount;

			                           	$total_pay = ($cart_items['total'] - $offer_amount);
			                           	$cart_items['total'] = $total_pay;

									}
										$show_msg = 1;

								}else if($res == 1)
								{ 							
									$res =-1;
									$msg = trans("messages.minimum order amount should be ").$currency_code." ".$coupon_details->minimum_order_amount .trans("messages.for applying coupon");
								}
								


								$cart_items['total'] = $cart_items['delivery_cost'] + $cart_items['total']; //delivery charge
								//$cart_items['total'] =20;
								//print_r($user_wallet);exit;
								$actual_amount = $cart_items['total'];

								$tot = $user_wallet-$cart_items['total'];
								($user_wallet >$cart_items['total'])?$used_walletamt=$cart_items['total']:$used_walletamt=$user_wallet;
								($tot <0)?$tot=$tot:$tot=0;
								$cart_items['total'] =  abs($tot);
								//$cart_items['total'] =  abs($cart_items['total']);
									//print_r($tot);exit;



							if($res == -1){ //invalid promocode
								$result = array("status" => 3, "message" => $msg, "detail" => array("cartCount" => $cartCount, "cart_items" => $cart_items['cart_items'], "total" => number_format( (String) $cart_items['total'], 2, '.', ''), "sub_total" => number_format( (String) $cart_items['sub_total'], 2, '.', ''), "tax" => $cart_items['tax'], "tax_amount" => number_format( (String) $cart_items['tax_amount'], 2, '.', ''), "outletId" => $cart_items['outlet_id'], "outletName" => $cart_items['outlet_name'], "vendor_image" => $cart_items['vendor_image'],"featured_image" => $cart_items['featured_image'], "minimum_order_amount" => $cart_items['minimum_order_amount'], "vendorId" => $cart_items['vendor_id'], "delivery_cost" => number_format( (String) $cart_items['delivery_cost'], 2, '.', '') , "delivery_time" => $cart_items['delivery_time'], "payment_gateway_details" => $payment_gateway_details,"address_details"=>$address,"used_walletamt"=>number_format( (String) $used_walletamt, 2, '.', ''),"actual_amount"=>number_format( (String) $actual_amount, 2, '.', ''),"couponDiscountamt"=>number_format( (String) $offer_amount, 2, '.', ''),"discountMsg"=>$discountMsg,"vendorAddress"=>$cart_items['vendor_address'],"outletAddress"=>$cart_items['outlet_address']));
							}elseif($res == -2){ //invalid promocode
								$result = array("status" => 4, "message" => $msg, "detail" => array("cartCount" => $cartCount, "cart_items" => $cart_items['cart_items'], "total" =>number_format( (String) $cart_items['total'], 2, '.', ''), "sub_total" => number_format( (String) $cart_items['sub_total'], 2, '.', ''), "tax" => $cart_items['tax'], "tax_amount" => number_format( (String) $cart_items['tax_amount'], 2, '.', ''), "outletId" => $cart_items['outlet_id'], "outletName" => $cart_items['outlet_name'], "vendor_image" => $cart_items['vendor_image'],"featured_image" => $cart_items['featured_image'], "minimum_order_amount" => $cart_items['minimum_order_amount'], "vendorId" => $cart_items['vendor_id'], "delivery_cost" => number_format( (String) $cart_items['delivery_cost'], 2, '.', ''), "delivery_time" => $cart_items['delivery_time'], "payment_gateway_details" => $payment_gateway_details,"address_details"=>$address,"moreToPay"=>number_format( (String) $p_amnt, 2, '.', '')
									/*(String)$p_amnt*/,"used_walletamt"=>number_format( (String) $used_walletamt, 2, '.', '')
									,"actual_amount"=>number_format( (String) $actual_amount, 2, '.', ''),"couponDiscountamt"=>number_format( (String) $offer_amount, 2, '.', ''),"discountMsg"=>$discountMsg,"vendorAddress"=>$cart_items['vendor_address'],"outletAddress"=>$cart_items['outlet_address']));
							}else { //withour promocode
									$result = array("status" => 1, "message" => trans("messages.The product has been added to your cart"), "detail" => array("cartCount" => $cartCount, "cart_items" => $cart_items['cart_items'], "total" => number_format( (String) $cart_items['total'], 2, '.', ''), "sub_total" => number_format( (String) $cart_items['sub_total'], 2, '.', ''), "tax" => $cart_items['tax'], "tax_amount" => number_format( (String) $cart_items['tax_amount'], 2, '.', ''), "outletId" => $cart_items['outlet_id'], "outletName" => $cart_items['outlet_name'], "vendor_image" => $cart_items['vendor_image'],"featured_image" => $cart_items['featured_image'], "minimum_order_amount" => $cart_items['minimum_order_amount'], "vendorId" => $cart_items['vendor_id'], "delivery_cost" => number_format( (String) $cart_items['delivery_cost'], 2, '.', ''), "delivery_time" => $cart_items['delivery_time'], "payment_gateway_details" => $payment_gateway_details,"coupon_details"=>$coupon_details,"coupon_user_limit_details"=>$coupon_user_limit_details,"address_details"=>$address,"show_message"=>$show_msg,"used_walletamt"=>number_format( (String) $used_walletamt, 2, '.', ''),"actual_amount"=>number_format( (String) $actual_amount, 2, '.', ''),"couponDiscountamt"=>number_format( (String) $offer_amount, 2, '.', ''),"discountMsg"=>$discountMsg,"savedAmount"=>$savedAmount,"vendorAddress"=>$cart_items['vendor_address'],"outletAddress"=>$cart_items['outlet_address']));
							}

						} else {
							$result = array("status" => 0, "message" => trans("messages.no products"));
						}

						//echo '<pre>';
						// print_r($cartProductRes);exit;
						// $result = array("status" => 1 , "message" => trans("messages.The product has been added to your cart"), "detail" => array("cartCount" => $cartCount, "cart_items"=>$cart_items['cart_items'],"total"=>$cart_items['total'],"sub_total"=>$cart_items['sub_total'],"tax"=>$cart_items['tax'],"tax_amount"=>$cart_items['tax_amount'],"outlet_id"=>$cart_items['outlet_id'],"outlet_name"=>$cart_items['outlet_name'],"vendor_image"=>$cart_items['vendor_image'],"minimum_order_amount"=>$cart_items['minimum_order_amount'],"vendor_id"=>$cart_items['vendor_id'],"delivery_cost"=>(double)$cart_items['delivery_cost'],"delivery_time" =>  $cart_items['delivery_time']));
					}
				} else {
					$result = array("status" => 0, "message" => trans("messages.no products"));
				}
			} catch (JWTException $e) {
				$result = array("status" => 0, "message" => trans("messages.Kindly check the user credentials"));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}
	// public function mbulkCartInsert1_copy(Request $data) {
	// 	//print_r("expression");exit;
	// 	$post_data = $data->all();

	// 	if (isset($post_data['language']) && $post_data['language'] == 2) {
 //            App::setLocale('ar');
 //        } else {
 //            App::setLocale('en');
 //        }
	// 	$data = array();
	// 	$rules = [
	// 		'language' => ['required'],
	// 		'userId' => ['required'],
	// 		'vendorId' => ['required'],
	// 		'outletId' => ['required'],
	// 		'productDetails' => ['required'],
	// 		'deviceToken' => ['required'],
	// 		'paymentGatewayId' => ['required'],
	// 	];
	// 	$errors = $result = $coupon_user_limit_details = $coupon_details= array();
	// 	$validator = app('validator')->make($post_data, $rules);
	// 	if ($validator->fails()) {
	// 		$j = 0;
	// 		foreach ($validator->errors()->messages() as $key => $value) {
	// 			$errors[] = is_array($value) ? implode(',', $value) : $value;
	// 		}
	// 		$errors = implode(", \n ", $errors);
	// 		$result = array("status" => 0, "message" => $errors);
	// 	} else {
	// 		try
	// 		{
	// 			// $check_auth = JWTAuth::toUser($post_data['token']);
	// 			// print_r($post_data);exit;
	// 			//$productArr = json_decode($post_data['productDetails']);
	// 			// print_r($productArr);exit;
	// 			/* $productIdsArr = array();
	//                 foreach ($productArr as $pkey => $pvalue) {
	// 				                    $productIdsArr[] = $pvalue->productId;
	// 			*/
	// 			// $outn=json_encode($post_data['productDetails']);
	// 							// print_r($outn);
	// 							// echo($post_data['productDetails']);exit;

	// 			//working for postman
	// 			//$productDetails_arr= json_decode($post_data['productDetails'],true);
	// 			//working for mobile
			

	// 			$latitude = isset($post_data['latitude']) ? $post_data['latitude'] : "11.0238";
	// 			$longitude = isset($post_data['longitude']) ? $post_data['longitude'] : "77.0197";
	// 			$language = isset($post_data['language'])?$post_data['language']:1;
	// 			$outlet_id = isset($post_data['outletId'])?$post_data['outletId']:1;
	// 			//print_r("expression");exit;

	// 			$stores_list = Stores::bulk_nearest_outlets($language,$latitude,$longitude,$outlet_id);
	// 			if(count($stores_list) > 0)
	// 			{	                    
	// 				$address = DB::table('user_address')
	// 					->select('user_address.address', 'user_address.latitude', 'user_address.longitude', 'user_address.address_type', 'user_address.user_id', 'user_address.id as address_id','user_address.land_mark','user_address.home_no','user_address.created_date','user_address.modified_date','user_address.active_status','user_address.type_text','user_address.id'/*,'address_infos.type_text as type_text'*/)
	// 					//->leftJoin('address_infos', 'address_infos.address_id', '=', 'user_address.id')
	// 					->where('user_id', '=', $post_data['userId'])

	// 					->orderBy('user_address.id', 'desc')
	// 					->get();


				


	// 				$post_data['promoCode'] = isset($post_data['promoCode'])?$post_data['promoCode']:'';
	// 				//$post_data['promoCode'] = 'BR30HFD';
					
	// 				$current_date = strtotime(date('Y-m-d'));
	// 				$coupon_details = DB::table('coupons')
	// 						->select('coupons.id as coupon_id', 'coupon_type', 'offer_amount','offer_type', 'coupon_code', 'start_date', 'end_date', 'minimum_order_amount')
	// 						->leftJoin('coupon_outlet','coupon_outlet.coupon_id','=','coupons.id')
	// 						->where('coupon_code','=',$post_data['promoCode'])
	// 						->where('coupon_outlet.outlet_id','=',$post_data['outletId'])
	// 						->first();
	// 				//print_r($coupon_details);exit;
	// 				/*$coupon_details->start_date = "2019-06-04 12:28:00";
	// 				$coupon_details->end_date = "2019-06-28 12:28:00";*/
	// 				$res ='';
	// 				$msg ='';
	// 				if($post_data['promoCode'] !='')
	// 				{ 
	// 					$res=1;
	// 					if(count($coupon_details) == 0)
	// 					{
	// 						$res =-1;
	// 						$msg = "Ivalid promocode";
	// 					}
	// 					else if((strtotime($coupon_details->start_date) <= $current_date) && (strtotime($coupon_details->end_date) >= $current_date)){
	// 						$coupon_user_limit_details = DB::table('user_cart_limit')
	// 						->select('cus_order_count','user_limit','total_order_count','coupon_limit')
	// 						->where('customer_id','=',$post_data['userId'])
	// 						->where('coupon_code','=',$post_data['promoCode'])
	// 						->first();
	// 						if(count($coupon_user_limit_details)>0){   
	// 							if($coupon_user_limit_details->cus_order_count >= $coupon_user_limit_details->user_limit)
	// 							{
	// 								$res =-1;
	// 								$msg = "Max user limit has been crossed";


	// 							}
	// 							if($coupon_user_limit_details->total_order_count >= $coupon_user_limit_details->coupon_limit)
	// 							{
	// 								$res =-1;
	// 								$msg = "Max coupon limit has been crossed";
	// 							}
	// 						}
	// 					}
	// 				}
	// 				//	print_r($res);exit;

	// 				$productDetails_arr = $post_data['productDetails'];
	// 				//print_r($productDetails_arr);exit();
	// 				$payment_gateway_details = $this->get_payment_gateway($post_data['paymentGatewayId'], $post_data['language']);
	// 				//print_r($payment_gateway_details);exit();

	// 				if (count($productDetails_arr)) {
	// 					foreach($productDetails_arr as $key => $value) {
	// 						//find and delete existing
	// 						$cdata = DB::table('cart')
	// 							->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
	// 							->select('cart.cart_id', 'cart_detail.product_id', 'cart_detail.quantity', 'cart_detail.cart_detail_id')
	// 							->where("cart.user_id", "=", $post_data['userId'])
	// 						// ->where("cart.store_id","=",$post_data['vendorId'])
	// 						// ->where("cart.outlet_id","=",$post_data['outletId'])
	// 						// ->where("cart_detail.product_id","=",$value['productId'])
	// 							->get();

	// 						if (count($cdata)) {
	// 							// print_r($cdata[0]->cart_id);
	// 							$affected = DB::update('delete from cart_detail where cart_id = ? ', array($cdata[0]->cart_id));
	// 							$affected2 = DB::update('delete from cart where cart_id = ? ', array($cdata[0]->cart_id));
	// 						}
	// 					}

	// 					$ucdata = DB::table('cart')
	// 						->select('cart.cart_id')
	// 						->where("cart.user_id", "=", $post_data['userId'])
	// 						->get();

	// 					if (count($ucdata)) {
	// 						$uucdata = DB::table('cart')
	// 							->select('cart.cart_id')
	// 							->where("cart.user_id", "=", $post_data['userId'])
	// 						// ->where("cart.store_id","=",$post_data['vendorId'])
	// 						// ->where("cart.outlet_id","=",$post_data['outletId'])
	// 							->get();
	// 						if (count($uucdata)) {
	// 							//update in cart table
	// 							$cart = Cart_model::find($uucdata[0]->cart_id);
	// 							$cart->store_id = $post_data['vendorId'];
	// 							$cart->outlet_id = $post_data['outletId'];
	// 							$cart->updated_at = date("Y-m-d H:i:s");
	// 							$cart->save();

	// 							foreach ($productDetails_arr as $key => $value) {
	// 								//find and delete existing
	// 								$cdata = DB::table('cart')
	// 									->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
	// 									->select('cart.cart_id', 'cart_detail.product_id', 'cart_detail.quantity', 'cart_detail.cart_detail_id')
	// 									->where("cart.user_id", "=", $post_data['userId'])
	// 									->where("cart.store_id", "=", $post_data['vendorId'])
	// 									->where("cart.outlet_id", "=", $post_data['outletId'])
	// 									->where("cart_detail.product_id", "=", $value['productId'])
	// 									->get();
	// 								if (count($cdata)) {
	// 									$affected = DB::update('delete from cart_detail where cart_detail_id = ? ', array($cdata[0]->cart_detail_id));

	// 								}
	// 							}

	// 							if (count($productDetails_arr) > 0) {
	// 								foreach ($productDetails_arr as $pkey => $pvalue) {
	// 									// print_r($pvalue);exit();

	// 									//insert in cart detail
	// 									$cart_info = new Cart_info;
	// 									$cart_info->cart_id = $cart->cart_id;
	// 									$cart_info->product_id = $pvalue['productId'];
	// 									$cart_info->quantity = $pvalue['productQty'];
	// 									$cart_info->created_at = date("Y-m-d H:i:s");
	// 									$cart_info->updated_at = date("Y-m-d H:i:s");
	// 									$cart_info->save();
	// 								}
	// 							}

	// 							$cartCount = 0;
	// 							if ($post_data['userId']) {
	// 								$cdata = DB::table('cart')
	// 									->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
	// 									->select('cart_detail.cart_id', DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
	// 									->where("cart.user_id", "=", $post_data['userId'])
	// 									->groupby('cart_detail.cart_id')
	// 									->get();
	// 								if (count($cdata)) {
	// 									$cartCount = $cdata[0]->cart_count;
	// 								}
	// 							}
	// 							$cart_items = $this->calculate_cart($post_data['language'], $post_data['userId']);
	// 							//print_r($cart_items);exit;

	// 							$cartProductRes = array();
	// 							if (count($cart_items['cart_items']) > 0) {
	// 								foreach ($cart_items['cart_items'] as $key => $pvalue) {
	// 									// print_r($key);exit;
	// 									$cartProductRes[$key] = new \stdClass();
	// 									$cartProductRes[$key]->productId = $pvalue->product_id;
	// 									$cartProductRes[$key]->originalPrice = $pvalue->original_price;
	// 									$cartProductRes[$key]->discountPrice = $pvalue->discount_price;
	// 									$cartProductRes[$key]->productName = $pvalue->product_name;
	// 									$cartProductRes[$key]->cartCount = $pvalue->quantity;
	// 									$cartProductRes[$key]->unit = $pvalue->unit;
	// 									$cartProductRes[$key]->productType = "1";
	// 									$cartProductRes[$key]->weight = $pvalue->weight;
	// 									$cartProductRes[$key]->description = $pvalue->description;
	// 									$cartProductRes[$key]->productImage = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
	// 									if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $pvalue->product_image) && $pvalue->product_image != '') {
	// 										$cartProductRes[$key]->productImage = url('/assets/admin/base/images/products/list/' . $pvalue->product_image);
	// 									}
	// 								}

	// 								/*$result = array("status" => 1, "message" => trans("messages.The product has been added to your cart"), "detail" => array("cartCount" => $cartCount, "cart_items" => $cart_items['cart_items'], "total" => $cart_items['total'], "sub_total" => $cart_items['sub_total'], "tax" => $cart_items['tax'], "tax_amount" => $cart_items['tax_amount'], "outletId" => $cart_items['outlet_id'], "outletName" => $cart_items['outlet_name'], "vendor_image" => $cart_items['vendor_image'], "minimum_order_amount" => $cart_items['minimum_order_amount'], "vendorId" => $cart_items['vendor_id'], "delivery_cost" => (double) $cart_items['delivery_cost'], "delivery_time" => $cart_items['delivery_time'], "payment_gateway_details" => $payment_gateway_details));*/
	// 								$show_msg =0;
	// 								$total_pay =0;
	// 								if($res == 1 && $coupon_details->minimum_order_amount <= $cart_items['total'])	{ //for promocode
	// 									if($coupon_details->offer_type == 1)
	// 										{ 
	// 											$offer_amount =$coupon_details->offer_amount;
	// 				                        	//$total_pay = $cart_items['total'] - $offer_amount;
	// 				                        	if($cart_items['total'] > $offer_amount )
	// 											{
	// 												$total_pay = $cart_items['total'] - $offer_amount;

	// 											}else
	// 											{
	// 												$total_pay = 0;
	// 											}
	// 											$cart_items['total'] =$total_pay;
												
	// 										}
	// 									else
	// 									{ 
	// 										$offer_amount = (($cart_items['total']*$offer_amount)/100).toFixed(2);
	// 										$coupon_details->offer_amount = $offer_amount;

	// 			                           	$total_pay = parseFloat($cart_items['total'] - $offer_amount).toFixed(2);
	// 			                           	$cart_items['total'] = $total_pay;
	// 									}
	// 									$show_msg = 1;

	// 								}else if($res == 1)
	// 								{
	// 									$res =-1;
	// 									$msg = "minimum order amount should be ".$coupon_details->minimum_order_amount;
	// 								}

	// 								/*	$result = array("status" => 1, "message" => trans("messages.Coupon applied Successfully"), "detail" => array("cartCount" => $cartCount, "cart_items" => $cart_items['cart_items'], "total" => $cart_items['total'], "sub_total" => $cart_items['sub_total'], "tax" => $cart_items['tax'], "tax_amount" => $cart_items['tax_amount'], "outletId" => $cart_items['outlet_id'], "outletName" => $cart_items['outlet_name'], "vendor_image" => $cart_items['vendor_image'], "minimum_order_amount" => $cart_items['minimum_order_amount'], "vendorId" => $cart_items['vendor_id'], "delivery_cost" => (double) $cart_items['delivery_cost'], "delivery_time" => $cart_items['delivery_time'], "payment_gateway_details" => $payment_gateway_details,"coupon_details"=>$coupon_details,"coupon_user_limit_details"=>$coupon_user_limit_details,"total_pay"=>$total_pay,"address_details"=>$address,"show_message"=>1));
	// 									}else*/

	// 								if($res == -1){ //invalid promocode
	// 										$result = array("status" => 3, "message" => trans("messages.".$msg), "detail" => array("cartCount" => $cartCount, "cart_items" => $cart_items['cart_items'], "total" => $cart_items['total'], "sub_total" => $cart_items['sub_total'], "tax" => $cart_items['tax'], "tax_amount" => $cart_items['tax_amount'], "outletId" => $cart_items['outlet_id'], "outletName" => $cart_items['outlet_name'], "vendor_image" => $cart_items['vendor_image'], "minimum_order_amount" => $cart_items['minimum_order_amount'], "vendorId" => $cart_items['vendor_id'], "delivery_cost" => (double) $cart_items['delivery_cost'], "delivery_time" => $cart_items['delivery_time'], "payment_gateway_details" => $payment_gateway_details,"address_details"=>$address));
	// 								}else { //withour promocode

	// 									$result = array("status" => 1, "message" => trans("messages.The product has been added to your cart"), "detail" => array("cartCount" => $cartCount, "cart_items" => $cart_items['cart_items'], "total" => $cart_items['total'], "sub_total" => $cart_items['sub_total'], "tax" => $cart_items['tax'], "tax_amount" => $cart_items['tax_amount'], "outletId" => $cart_items['outlet_id'], "outletName" => $cart_items['outlet_name'], "vendor_image" => $cart_items['vendor_image'], "minimum_order_amount" => $cart_items['minimum_order_amount'], "vendorId" => $cart_items['vendor_id'], "delivery_cost" => (double) $cart_items['delivery_cost'], "delivery_time" => $cart_items['delivery_time'], "payment_gateway_details" => $payment_gateway_details,"coupon_details"=>$coupon_details,"coupon_user_limit_details"=>$coupon_user_limit_details,"address_details"=>$address,"show_message"=>$show_msg));
	// 									}
	// 							} else {
	// 								$result = array("status" => 0, "message" => trans("messages.no products"));
	// 							}

	// 							//print_r($cartProductRes);exit;

	// 						} else {
	// 							$cartCount = 0;
	// 							if ($post_data['userId']) {
	// 								$cdata = DB::table('cart')
	// 									->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
	// 									->select('cart_detail.cart_id', DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
	// 									->where("cart.user_id", "=", $post_data['userId'])
	// 									->groupby('cart_detail.cart_id')
	// 									->get();
	// 								if (count($cdata)) {
	// 									$cartCount = $cdata[0]->cart_count;
	// 								}
	// 							}
	// 							$result = array("status" => 3, "message" => trans("messages.your cart has items from another branch, please choose the same branch to continue"), "detail" => array("cartCount" => $cartCount));
	// 						}
	// 					} else {
	// 						$cart = new Cart_model;
	// 						$cart->user_id = $post_data['userId'];
	// 						$cart->store_id = $post_data['vendorId'];
	// 						$cart->outlet_id = $post_data['outletId'];
	// 						$cart->cart_status = 1;
	// 						$cart->created_at = date("Y-m-d H:i:s");
	// 						$cart->updated_at = date("Y-m-d H:i:s");
	// 						$cart->save();

	// 						if (count($productDetails_arr) > 0) {
	// 							foreach($productDetails_arr as $pkey => $pvalue) {
	// 								//insert in cart detail
	// 								$cart_info = new Cart_info;
	// 								$cart_info->cart_id = $cart->cart_id;
	// 								$cart_info->product_id = $pvalue['productId'];
	// 								$cart_info->quantity = $pvalue['productQty'];
	// 								$cart_info->created_at = date("Y-m-d H:i:s");
	// 								$cart_info->updated_at = date("Y-m-d H:i:s");
	// 								$cart_info->save();
	// 							}
	// 						}

	// 						$cartCount = 0;
	// 						if ($post_data['userId']) {
	// 							$cdata = DB::table('cart')
	// 								->leftJoin('cart_detail', 'cart_detail.cart_id', '=', 'cart.cart_id')
	// 								->select('cart_detail.cart_id', DB::raw('count(cart_detail.cart_detail_id) as cart_count'))
	// 								->where("cart.user_id", "=", $post_data['userId'])
	// 								->groupby('cart_detail.cart_id')
	// 								->get();
	// 							if (count($cdata)) {
	// 								$cartCount = $cdata[0]->cart_count;
	// 							}
	// 						}

	// 						$cart_items = $this->calculate_cart($post_data['language'], $post_data['userId']);

	// 						// echo '<pre>';
	// 						// print_r($res);
	// 						// print_r($cart_items);
	// 						// exit;

	// 						$cartProductRes = array();
	// 						if (count($cart_items['cart_items']) > 0) {
	// 							foreach ($cart_items['cart_items'] as $key => $pvalue) {
	// 								// print_r($key);exit;
	// 								$cartProductRes[$key] = new \stdClass();
	// 								$cartProductRes[$key]->productId = $pvalue->product_id;
	// 								$cartProductRes[$key]->originalPrice = $pvalue->original_price;
	// 								$cartProductRes[$key]->discountPrice = $pvalue->discount_price;
	// 								$cartProductRes[$key]->productName = $pvalue->product_name;
	// 								$cartProductRes[$key]->cartCount = $pvalue->quantity;
	// 								$cartProductRes[$key]->unit = $pvalue->unit;
	// 								$cartProductRes[$key]->productType = "1";

	// 								$cartProductRes[$key]->weight = $pvalue->weight;
	// 								$cartProductRes[$key]->description = $pvalue->description;

	// 								$cartProductRes[$key]->productImage = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
	// 								if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $pvalue->product_image) && $pvalue->product_image != '') {
	// 									$cartProductRes[$key]->productImage = url('/assets/admin/base/images/products/list/' . $pvalue->product_image);
	// 								}
	// 							}
	// 							//$res =1;						
	// 							$show_msg =0;
	// 								$total_pay =0;
	// 							//print_r($coupon_details->minimum_order_amount);exit;

	// 								//if($res == 1)	{ //for promocode
	// 								if($res == 1 && $coupon_details->minimum_order_amount <= $cart_items['total'])	{ //for promocode 

	// 									if($coupon_details->offer_type == 1)
	// 										{ 																

	// 											$offer_amount =$coupon_details->offer_amount;

	// 				                        	//$total_pay = $cart_items['total'] - $offer_amount;
	// 				                        	if($cart_items['total'] > $offer_amount )
	// 											{
	// 												$total_pay = $cart_items['total'] - $offer_amount;

	// 											}else
	// 											{
	// 												$total_pay = 0;
	// 											}
	// 											$cart_items['total'] = $total_pay;
	// 										}
	// 										else
	// 										{ 
	// 											$offer_amount = (($cart_items['total']*$offer_amount)/100).toFixed(2);
	// 											$coupon_details->offer_amount = $offer_amount;

	// 				                           	$total_pay = parseFloat($cart_items['total'] - $offer_amount).toFixed(2);
	// 				                           	$cart_items['total'] = $total_pay;

	// 										}
	// 										$show_msg = 1;

	// 								}else if($res == 1)
	// 								{ 							
	// 									$res =-1;
	// 									$msg = "minimum order amount should be ".$coupon_details->minimum_order_amount;
	// 								}						
	// 								//print_r($cart_items);echo".....";


	// 								/*$result = array("status" => 1, "message" => trans("messages.Coupon applied Successfully"), "detail" => array("cartCount" => $cartCount, "cart_items" => $cart_items['cart_items'], "total" => $cart_items['total'], "sub_total" => $cart_items['sub_total'], "tax" => $cart_items['tax'], "tax_amount" => $cart_items['tax_amount'], "outletId" => $cart_items['outlet_id'], "outletName" => $cart_items['outlet_name'], "vendor_image" => $cart_items['vendor_image'], "minimum_order_amount" => $cart_items['minimum_order_amount'], "vendorId" => $cart_items['vendor_id'], "delivery_cost" => (double) $cart_items['delivery_cost'], "delivery_time" => $cart_items['delivery_time'], "payment_gateway_details" => $payment_gateway_details,"coupon_details"=>$coupon_details,"coupon_user_limit_details"=>$coupon_user_limit_details,"total_pay"=>$total_pay,"address_details"=>$address,"show_message"=>1));
	// 							}else*/
	// 							if($res == -1){ //invalid promocode
	// 								$result = array("status" => 3, "message" => trans("messages.".$msg), "detail" => array("cartCount" => $cartCount, "cart_items" => $cart_items['cart_items'], "total" => $cart_items['total'], "sub_total" => $cart_items['sub_total'], "tax" => $cart_items['tax'], "tax_amount" => $cart_items['tax_amount'], "outletId" => $cart_items['outlet_id'], "outletName" => $cart_items['outlet_name'], "vendor_image" => $cart_items['vendor_image'], "minimum_order_amount" => $cart_items['minimum_order_amount'], "vendorId" => $cart_items['vendor_id'], "delivery_cost" => (double) $cart_items['delivery_cost'], "delivery_time" => $cart_items['delivery_time'], "payment_gateway_details" => $payment_gateway_details,"address_details"=>$address));
	// 							}else { //withour promocode
	// 								//print_r($cart_items['total']);exit;

	// 									$result = array("status" => 1, "message" => trans("messages.The product has been added to your cart"), "detail" => array("cartCount" => $cartCount, "cart_items" => $cart_items['cart_items'], "total" => $cart_items['total'], "sub_total" => $cart_items['sub_total'], "tax" => $cart_items['tax'], "tax_amount" => $cart_items['tax_amount'], "outletId" => $cart_items['outlet_id'], "outletName" => $cart_items['outlet_name'], "vendor_image" => $cart_items['vendor_image'], "minimum_order_amount" => $cart_items['minimum_order_amount'], "vendorId" => $cart_items['vendor_id'], "delivery_cost" => (double) $cart_items['delivery_cost'], "delivery_time" => $cart_items['delivery_time'], "payment_gateway_details" => $payment_gateway_details,"coupon_details"=>$coupon_details,"coupon_user_limit_details"=>$coupon_user_limit_details,"address_details"=>$address,"show_message"=>$show_msg));
	// 							}

	// 						} else {
	// 							$result = array("status" => 0, "message" => trans("messages.no products"));
	// 						}

	// 						//echo '<pre>';
	// 						// print_r($cartProductRes);exit;
	// 						// $result = array("status" => 1 , "message" => trans("messages.The product has been added to your cart"), "detail" => array("cartCount" => $cartCount, "cart_items"=>$cart_items['cart_items'],"total"=>$cart_items['total'],"sub_total"=>$cart_items['sub_total'],"tax"=>$cart_items['tax'],"tax_amount"=>$cart_items['tax_amount'],"outlet_id"=>$cart_items['outlet_id'],"outlet_name"=>$cart_items['outlet_name'],"vendor_image"=>$cart_items['vendor_image'],"minimum_order_amount"=>$cart_items['minimum_order_amount'],"vendor_id"=>$cart_items['vendor_id'],"delivery_cost"=>(double)$cart_items['delivery_cost'],"delivery_time" =>  $cart_items['delivery_time']));
	// 					}
	// 				} else {
	// 					$result = array("status" => 0, "message" => trans("messages.no products"));
	// 				}
	// 			}else
	// 			{
	// 				$result = array("status" => 0, "message" => trans("messages. vendor not available for your location"));
	// 			}
	// 		} catch (JWTException $e) {
	// 			$result = array("status" => 0, "message" => trans("messages.Kindly check the user credentials"));
	// 		}
	// 	}
	// 	return json_encode($result, JSON_UNESCAPED_UNICODE);
	// }

	public function payment_response(Request $data) {
		$post_data = $data->all();
		//print_r($post_data);exit;
		// $cart_details=json_decode($post_data['cartDetails']);
		$abc = $post_data['cartDetails'];
		// $cart_detailsb=json_encode($abc,JSON_UNESCAPED_UNICODE);

		$cart_detailsb = json_encode($abc);
		$cart_details = json_decode($cart_detailsb);

		$coupon_details = isset($cart_details->coupon_details)?$cart_details->coupon_details:'';
		//echo"<pre>";print_r($coupon_details->coupon_id);exit();
		//echo"<pre>";print_r($cart_details);exit();

		// $cart_details=$post_data['cartDetails']);

		if (isset($post_data['language']) && $post_data['language'] == 2) {
            App::setLocale('ar');
        } else {
            App::setLocale('en');
        }
		$data = array();
		$rules = [
			'language' => ['required'],
			'userId' => ['required'],
			'cartDetails' => ['required'],
			'deviceToken' => ['required'],
		];
		$errors = $result = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("status" => 0, "message" => $errors);
		} else {
			try
			{	

				$users = Users::find($post_data['userId']);
				//print_r($users['wallet_amount']);exit;


				//echo"<pre>";print_r($post_data['cartDetails']['used_walletamt']);exit;
				$wallet_amt= isset($post_data['cartDetails']['used_walletamt'])?$post_data['cartDetails']['used_walletamt']:0; //for wallet amount 
				$wallet_amount = $users['wallet_amount']-$wallet_amt;
				//print_r($wallet_amount);exit;
				($wallet_amount > 0)?$wallet_amount =$wallet_amount :$wallet_amount =0;
				//print_r($post_data['userId']);exit;
				$wallet = DB::update('update users set wallet_amount =?  where id = ?', array($wallet_amount,$post_data['userId']));
				//	print_r("expression");exit;
				$cart_details->delivery_notes = isset($post_data['delivery_instructions']) ? $post_data['delivery_instructions'] : '';
				$delivery_address = (isset($post_data['delivery_address']) && ($post_data['delivery_address'] != "")) ? $post_data['delivery_address'] : 0;
				$cart_details->delivery_address = $delivery_address;
				$cart_details->delivery_slot = (isset($post_data['delivery_slot']) && ($post_data['delivery_slot'] != "")) ? $post_data['delivery_slot'] : 0;
				$cart_details->delivery_date = (isset($post_data['delivery_date']) && ($post_data['delivery_date'] != "")) ? $post_data['delivery_date'] : date("Y-m-d H:i:s");
				//$cart_details->delivery_cost = (isset($post_data['delivery_cost']) && ($post_data['delivery_cost'] != "")) ? $post_data['delivery_cost'] : 0;
				
				$cart_details->delivery_cost = (isset($abc['delivery_cost']) && ($abc['delivery_cost'] != "")) ? $abc['delivery_cost'] : 0;
				$cart_details->order_type = (isset($post_data['order_type']) && ($post_data['order_type'] != "")) ? $post_data['order_type'] : 1;
				//print_r($cart_details);exit;
				if ($cart_details->order_type == 2) {
					$cart_details->delivery_cost = 0;
					$cart_details->delivery_slot = 0;
					$cart_details->delivery_date = "NOW()";
					$cart_details->delivery_address = 0;
				}
			/*	$cart_details->coupon_id = (isset($post_data['coupon_id']) && $post_data['coupon_id'] != "") ? $post_data['coupon_id'] : 0;
				$cart_details->coupon_amount = (isset($post_data['coupon_amount']) && $post_data['coupon_amount'] != "") ? $post_data['coupon_amount'] : 0;
				$cart_details->coupon_type = (isset($post_data['coupon_type']) && $post_data['coupon_type'] != "") ? $post_data['coupon_type'] : 0;*/
				$cart_details->coupon_id = (isset($coupon_details->coupon_id) && $coupon_details->coupon_id != "") ? $coupon_details->coupon_id : 0;
				$cart_details->coupon_amount = (isset($coupon_details->offer_amount) && $coupon_details->offer_amount != "") ? $coupon_details->offer_amount : 0;
				$cart_details->coupon_type = (isset($coupon_details->offer_type) && $coupon_details->offer_type != "") ? $coupon_details->offer_type : 0;
				//print_r($coupon_details);exit;

				if (count($cart_details)) {

					$language = getCurrentLang();
					$payment_array = array();
					$total_amount = ($cart_details->sub_total + $cart_details->tax_amount + $cart_details->delivery_cost);
					//$total_amount = ($cart_details->sub_total + $cart_details->tax_amount + $cart_details->delivery_cost) - $cart_details->coupon_amount;
				//	print_r($total_amount);exit;
					if($total_amount > $cart_details->coupon_amount ) {
						$total_amount = $total_amount - $cart_details->coupon_amount;
					} else {
						$total_amount =0;
					}
										$total_amount =$total_amount - $wallet_amt;

					//print_r($total_amount);exit;
					//$cart_details->payment_gateway_detail->commision."<br/>";
					$admin_commission = (($cart_details->sub_total * $cart_details->payment_gateway_details->commision) / 100);
					$payment_array['admin_commission'] = number_format(($admin_commission + $cart_details->tax + $cart_details->delivery_cost),2, '.', '');
					$vendor_commission = $cart_details->sub_total - $admin_commission;
					$payment_array['vendor_commission'] = number_format($vendor_commission, 2, '.', '') ;
					$payment_array['user_id'] = $cart_details->cart_items[0]->user_id;
					$payment_array['store_id'] = $cart_details->cart_items[0]->store_id;
					$payment_array['outlet_id'] = $cart_details->cart_items[0]->outlet_id;
					$payment_array['vendor_key'] = $cart_details->cart_items[0]->vendor_key;
					$payment_array['vendor_name'] = $cart_details->cart_items[0]->vendor_name;
					$payment_array['total'] = number_format($total_amount, 2, '.', '');
					$payment_array['sub_total'] = number_format($cart_details->sub_total, 2, '.', '');
					$payment_array['service_tax'] = $cart_details->tax;
					$payment_array['tax_amount'] = number_format($cart_details->tax_amount, 2, '.', '');
					$payment_array['order_status'] = 1;
					$payment_array['order_key'] = str_random(32);
					$payment_array['invoice_id'] = str_random(32);
					$payment_array['transaction_id'] = str_random(32);
					$payment_array['transaction_staus'] = 1;
					$payment_array['transaction_amount'] = number_format($total_amount, 2, '.', '') ;
					$payment_array['payer_id'] = str_random(32);
					$payment_array['currency_code'] = getCurrency($language);
					$payment_array['payment_gateway_id'] = $cart_details->payment_gateway_details->id;
					$payment_array['coupon_type'] = 0;
					$payment_array['delivery_charge'] = number_format(0, 2, '.', '');
					$payment_array['payment_status'] = 0;
					$payment_array['payment_gateway_commission'] = number_format($cart_details->payment_gateway_details->commision, 2, '.', '');
					$payment_array['delivery_instructions'] = $cart_details->delivery_notes;
					$payment_array['delivery_address'] = $cart_details->delivery_address;
					$payment_array['delivery_slot'] = $cart_details->delivery_slot;
					$payment_array['delivery_date'] = $cart_details->delivery_date;
					$payment_array['order_type'] = $cart_details->order_type;
					$payment_array['coupon_id'] = $cart_details->coupon_id;
					$payment_array['coupon_amount'] = number_format($cart_details->coupon_amount, 2, '.', '');
					$payment_array['coupon_type'] = $cart_details->coupon_type;
					$payment_array['delivery_cost'] = number_format($cart_details->delivery_cost, 2, '.', '') ;
					$payment_array['wallet_amt'] = $wallet_amt ;
					$payment_array['actual_amount'] = $cart_details->actual_amount ;
					// echo '<pre>';  print_r($payment_array);exit;
					$items = array();
					$i = 0;
					foreach ($cart_details->cart_items as $cartitems) {
						$items[$i]['product_id'] = $cartitems->product_id;
						$items[$i]['quantity'] = $cartitems->quantity;
						$items[$i]['discount_price'] = number_format(  $cartitems->discount_price, 2, '.', '');
						$items[$i]['item_offer'] = 0;
						$i++;
					}
					$payment_array['items'] = $items;




					//$payment_array = json_encode($payment_array);
					//echo $payment_array;
					//exit;
					$result = array("status" => 1, "message" => trans("messages.proceed payment"), "payment_array" => $payment_array);
				} else {
					$result = array("status" => 0, "message" => trans("messages.no products"));
				}
			} catch (JWTException $e) {
				$result = array("status" => 0, "message" => trans("messages.Kindly check the user credentials"));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
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
			//print_r($gateways);exit;
		return $gateways;
	}

}
