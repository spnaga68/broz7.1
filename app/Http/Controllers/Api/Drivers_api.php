<?php
namespace App\Http\Controllers\Api;
use App;
use App\Http\Controllers\Controller;
use App\Model\autoassign_order_logs;
use App\Model\drivers;
use App\Model\driver_orders;
use App\Model\driver_settings;
use App\Model\driver_track_location;
use App\Model\order;
use App\Model\orders;
use App\Model\users;
use App\Model\vendors;
use DB;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use FCM;
DB::enableQueryLog();
use Illuminate\Support\Facades\Text;
use JWTAuth;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use Services_Twilio;
use Session;
use Tymon\JWTAuth\Exceptions\JWTException;
use URL;

class Drivers_api extends Controller {
	const USERS_FORGOT_PASSWORD_EMAIL_TEMPLATE = 6;
	const USER_CHANGE_PASSWORD_EMAIL_TEMPLATE = 13;
	const DRIVER_SIGNUP_EMAIL_TEMPLATE = 9;
	const DRIVER_WELCOME_EMAIL_TEMPLATE = 10;
	const DRIVER_ORDER_RESPONSE_TEMPLATE = 25;
	const ORDER_STATUS_UPDATE_USER = 18;
	const DRIVER_ORDER__DELIVERED_RESPONSE_ADMIN_TEMPLATE = 27;
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
	/** driver login*/
	public function login(Request $data) {
		$post_data = $data->all();
		$rules = [
			'email' => 'required|email',
			'password' => 'required',
			'login_type' => ['required'],
			'latitude' => ['required'],
			'longitude' => ['required'],
			'device_id' => ['required_if:login_type,2,3'],
			'device_token' => ['required_if:login_type,2,3'],
		];
		$errors = $result = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "Message" => $errors));
		} else {
			$email = $post_data['email'];
			$password = md5($post_data['password']);
			$driver_data = DB::table('drivers')
				->select('drivers.id', 'drivers.social_title', 'first_name', 'drivers.last_name', 'drivers.email', 'drivers.active_status', 'drivers.created_date', 'drivers.modified_date', 'drivers.is_verified', 'drivers.profile_image', 'drivers.driver_status')
				->where('drivers.email', $email)
				->where('drivers.hash_password', $password)
				->where('drivers.active_status', 1)
				->first();
			if (count($driver_data) > 0) {
				if ($driver_data->is_verified == 0) {
					$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Please confirm you mail to activation.")));
				} else if ($driver_data->active_status == 0) {
					$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Your registration has blocked pls contact Your Admin.")));
				} else {
					if ($post_data['login_type'] == 2) {
						$res = DB::table('drivers')->where('id', $driver_data->id)->update(['android_device_token' => $post_data['device_token'], 'android_device_id' => $post_data['device_id'], 'login_type' => $post_data['login_type']]);
					}
					if ($post_data['login_type'] == 3) {
						$res = DB::table('drivers')->where('id', $driver_data->id)->update(['ios_device_token' => $post_data['device_token'], 'ios_device_id' => $post_data['device_id'], 'login_type' => $post_data['login_type']]);
					}
					$driver_image = url('/assets/admin/base/images/default_avatar_male.jpg?' . time());
					if (file_exists(base_path() . '/public/assets/admin/base/images/drivers/' . $driver_data->profile_image) && $driver_data->profile_image != '') {
						$driver_image = URL::to("assets/admin/base/images/drivers/" . $driver_data->profile_image . '?' . time());
					}

					$android_device_id = isset($post_data['device_id']) ? $post_data['device_id'] : '';
					$android_device_token = isset($post_data['device_token']) ? $post_data['device_token'] : '';

					$ios_device_id = isset($post_data['device_id']) ? $post_data['device_id'] : '';
					$ios_device_token = isset($post_data['device_token']) ? $post_data['device_token'] : '';

					$driver_track_location = DB::table('driver_track_location')
						->where('driver_id', $driver_data->id)
						->get();
					if (count($driver_track_location) > 0) {
						$d_t_location = DB::table('driver_track_location')
							->where('driver_id', $driver_data->id)
							->update(['created_date' => date("Y-m-d H:i:s"), 'latitude' => $post_data['latitude'], 'longitude' => $post_data['longitude'], 'android_device_id' => $android_device_id, 'android_device_token' => $android_device_token, 'ios_device_id' => $ios_device_id, 'ios_device_token' => $ios_device_token]);

					} else {

						$d_t_location = new Driver_track_location;
						$d_t_location->driver_id = $driver_data->id;
						$d_t_location->today_date = date('Y-m-d');
						$d_t_location->latitude = $post_data['latitude'];
						$d_t_location->longitude = $post_data['longitude'];
						$d_t_location->login_type = $post_data['login_type'];
						$d_t_location->created_date = date("Y-m-d H:i:s");
						if (isset($post_data['login_type']) && !empty($post_data['login_type'])) {
							if ($post_data['login_type'] == 2) {
								$d_t_location->android_device_id = isset($post_data['device_id']) ? $post_data['device_id'] : '';
								$d_t_location->android_device_token = isset($post_data['device_token']) ? $post_data['device_token'] : '';
							}
							if ($post_data['login_type'] == 3) {
								$d_t_location->ios_device_id = isset($post_data['device_id']) ? $post_data['device_id'] : '';
								$d_t_location->ios_device_token = isset($post_data['device_token']) ? $post_data['device_token'] : '';
							}
						}
						$d_t_location->save();
					}

					/*  $d_t_location = new Driver_track_location;
						                    $d_t_location->driver_id    = $driver_data->id;
						                    $d_t_location->today_date   = date('Y-m-d');
						                    $d_t_location->latitude     = $post_data['latitude'];
						                    $d_t_location->longitude    = $post_data['longitude'];
						                    $d_t_location->login_type   = $post_data['login_type'];
						                    $d_t_location->created_date = date("Y-m-d H:i:s");
						                    if(isset($post_data['login_type']) && !empty($post_data['login_type'])){
						                        if($post_data['login_type'] == 2)
						                        {
						                            $d_t_location->android_device_id = isset($post_data['device_id'])?$post_data['device_id']:'';
						                            $d_t_location->android_device_token = isset($post_data['device_token'])?$post_data['device_token']:'';
						                        }
						                        if($post_data['login_type'] == 3)
						                        {
						                            $d_t_location->ios_device_id = isset($post_data['device_id'])?$post_data['device_id']:'';
						                            $d_t_location->ios_device_token = isset($post_data['device_token'])?$post_data['device_token']:'';
						                        }
						                    }
					*/
					$token = JWTAuth::fromUser($driver_data, array('exp' => 200000000000));
					$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Driver Logged-in Successfully"), "driver_id" => $driver_data->id, "token" => $token, "email" => $driver_data->email, "first_name" => isset($driver_data->first_name) ? $driver_data->first_name : "", "last_name" => isset($driver_data->last_name) ? $driver_data->last_name : "", "image" => $driver_image, "driver_status" => $driver_data->driver_status));
				}
			} else {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Invalid email address or password")));
			}
		}
		return $result;
	}
	/* driver details */
	public function driver_detail(Request $data) {
		$post_data = $data->all();
		$rules = [
			'driver_id' => ['required', 'integer'],
			'token' => ['required'],
		];
		$error = $result = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			$errors = '';
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$error[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $error);
			$result = array("response" => array("httpCode" => 400, "Error" => trans("messages.Error List"), "Message" => $errors));
		} else {
			try {
				$check_auth = JWTAuth::toUser($post_data['token']);
				$driver_data = DB::table('drivers')
					->select('drivers.id', 'first_name', 'drivers.last_name', 'drivers.email', 'drivers.profile_image', 'drivers.address', 'drivers.latitude', 'drivers.latitude', 'drivers.longitude', 'drivers.mobile_number', 'drivers.driver_status', 'drivers.gender', 'drivers.driver_status')
					->where('drivers.id', $post_data['driver_id'])
					->where('drivers.active_status', 1)
					->first();
				if (count($driver_data) > 0) {
					$driver_data->first_name = ($driver_data->first_name != '') ? $driver_data->first_name : '';
					$driver_data->last_name = ($driver_data->last_name != '') ? $driver_data->last_name : '';
					$imageName = url('/assets/admin/base/images/default_avatar_male.jpg');
					if (file_exists(base_path() . '/public/assets/admin/base/images/drivers/' . $driver_data->profile_image) && $driver_data->profile_image != '') {
						$imageName = URL::to("/assets/admin/base/images/drivers/" . $driver_data->profile_image . '?' . time());
					}
					$driver_data->profile_image = $imageName;
					$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Driver details"), 'driver_details' => array($driver_data)));
				} else {
					$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.No driver found")));
				}
			} catch (JWTException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			} catch (TokenExpiredException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			}
		}
		return json_encode($result);exit;
	}
	/* To update drive details*/
	public function update_profile(Request $data) {
		$post_data = $data->all();
		$rules = [
			'driver_id' => ['required'],
			'first_name' => ['required', 'max:56'],
			'last_name' => ['required', 'max:56'],
			'token' => ['required'],
			'mobile' => ['required', 'max:50', 'regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/'],
			'gender' => ['required'],
			'driver_status' => ['required'],
		];
		$error = $result = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			$errors = '';
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$error[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $error);
			$result = array("response" => array("httpCode" => 400, "Error" => trans("messages.Error List"), "Message" => $errors));
		} else {
			try {
				$id = $post_data['driver_id'];
				$drivers = Drivers::find($id);
				$drivers->first_name = $post_data['first_name'];
				$drivers->last_name = $post_data['last_name'];
				$drivers->mobile_number = $_POST['mobile'];
				$drivers->gender = $post_data['gender'];
				$drivers->driver_status = $post_data['driver_status'];
				$drivers->modified_date = date("Y-m-d H:i:s");
				$drivers->save();
				$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Driver information has been updated successfully")));
			} catch (JWTException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			} catch (TokenExpiredException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}
	public function forgot_password(Request $data) {
		$post_data = $data->all();
		$rules = [
			'email' => ['required', 'email'],
		];
		$errors = $result = array();

		$validator = app('validator')->make($post_data, $rules);

		if ($validator->fails()) {
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Error List"), "Error" => $errors));
		} else {
			$email = strtolower($post_data['email']);
			$drivers = DB::table('drivers')
				->select('drivers.id', 'drivers.first_name', 'drivers.hash_password', 'drivers.last_name', 'drivers.email')
				->where('email', $email)
				->where('active_status', 1)
				->get();
			if (count($drivers) > 0) {
				$driver_data = $drivers[0];
				//Generate random password string
				$string = str_random(8);
				$pass_string = md5($string);
				$template = DB::table('email_templates')
					->select('from_email', 'from', 'subject', 'template_id', 'content')
					->where('template_id', '=', self::USERS_FORGOT_PASSWORD_EMAIL_TEMPLATE)
					->get();
				if (count($template)) {
					$from = $template[0]->from_email;
					$from_name = $template[0]->from;
					$subject = $template[0]->subject;
					if (!$template[0]->template_id) {
						$template = 'mail_template';
						$from = getAppConfigEmail()->contact_email;
						$subject = getAppConfig()->site_name . " Password Request Details";
						$from_name = "";
					}
					$content = array("name" => ucfirst($driver_data->first_name), "email" => $driver_data->email, "password" => $string);
					$email = smtp($from, $from_name, $driver_data->email, $subject, $content, $template);
				}
				//Update random password to universities table to coreesponding university id
				$res = DB::table('drivers')
					->where('id', $driver_data->id)
					->update(['hash_password' => $pass_string]);
				$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Password was sent your email successfully")));
			} else {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.These email do not match our records")));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}
	public function change_password(Request $data) {
		$data_all = $data->all();
		$rules = [
			'driver_id' => ['required'],
			'language' => ['required'],
			'token' => ['required'],
			'old_password' => ['required', 'min:5', 'max:16', 'regex:/(^[A-Za-z0-9 !@#$%]+$)+/'],
			'password' => ['required', 'min:5', 'max:16', 'confirmed', 'regex:/(^[A-Za-z0-9 !@#$%]+$)+/'],
			'password_confirmation' => ['required', 'min:5', 'max:16', 'regex:/(^[A-Za-z0-9 !@#$%]+$)+/'],
		];
		$errors = $result = array();

		$validator = app('validator')->make($data->all(), $rules);

		if ($validator->fails()) {
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "Message" => $errors, "Error" => trans("messages.Error List")));
		} else {
			try {
				$check_auth = JWTAuth::toUser($data_all['token']);
				//Get new password details from posts
				$old_password = $data_all['old_password'];
				$string = $data_all['password'];
				$pass_string = md5($string);
				$session_driverid = $data_all['driver_id'];
				$driver_data = DB::table('drivers')
					->select('drivers.id', 'drivers.first_name', 'drivers.last_name', 'drivers.email', 'drivers.hash_password')
					->where('drivers.id', $session_driverid)
					->where('drivers.active_status', 1)
					->first();
				if (count($driver_data) > 0) {
					if ($driver_data->hash_password == md5($old_password)) {
						$template = DB::table('email_templates')
							->select('from_email', 'from', 'subject', 'template_id', 'content')
							->where('template_id', '=', self::USER_CHANGE_PASSWORD_EMAIL_TEMPLATE)
							->get();
						if (count($template)) {
							$from = $template[0]->from_email;
							$from_name = $template[0]->from;
							$subject = $template[0]->subject;
							if (!$template[0]->template_id) {
								$template = 'mail_template';
								$from = getAppConfigEmail()->contact_email;
								$subject = getAppConfig()->site_name . " New Password Request Updated";
								$from_name = "";
							}
							$content = array("name" => ucfirst($driver_data->first_name), "email" => $driver_data->email, "password" => $string);
							$email = smtp($from, $from_name, $driver_data->email, $subject, $content, $template);
						}
						//Update random password to vendors table to coreesponding vendor id
						$res = DB::table('drivers')->where('id', $driver_data->id)->update(['hash_password' => $pass_string]);
						//After updating new password details logout the session and redirects to login page
						$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Your Password Changed Successfully")));
					} else {
						$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Old password is incorrect")));
					}
				} else {
					$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Invalid user")));
				}
			} catch (JWTException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			} catch (TokenExpiredException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}
	/* Orders list */
	public function driver_orders(Request $data) {
		$data_all = $data->all();
		$rules = [
			'driver_id' => ['required'],
			'language' => ['required'],
			'token' => ['required'],
		];
		$errors = $result = array();

		$validator = app('validator')->make($data->all(), $rules);

		if ($validator->fails()) {
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "Message" => $errors, "Error" => trans("messages.Error List")));
		} else {
			try {
				$check_auth = JWTAuth::toUser($data_all['token']);
				$language_id = $data_all["language"];
				$query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
				$order_list = DB::table('orders')
					->select('orders.id as orders_id', 'vendors.featured_image', 'users.image as user_image', 'orders.total_amount', 'orders.created_date', 'orders.order_key_formated', 'outlet_infos.contact_address as outlet_address', 'user_address.address as user_address', 'orders.order_status')
					->Join('vendors', 'vendors.id', '=', 'orders.vendor_id')
					->Join('driver_orders', 'driver_orders.order_id', '=', 'orders.id')
					->Join('outlets', 'outlets.id', '=', 'orders.outlet_id')
					->Join('users', 'users.id', '=', 'orders.customer_id')
					->join('outlet_infos', 'outlets.id', '=', 'outlet_infos.id')
					->leftJoin('user_address', 'orders.delivery_address', '=', 'user_address.id')
					->Join('drivers', 'drivers.id', '=', 'driver_orders.driver_id')
					->whereRaw($query1)
					->where('drivers.id', '=', $data_all['driver_id'])
					->orderBy('orders.id', 'desc')
					->get();
				$orders = array();
				if (count($order_list) > 0) {
					$o = 0;
					foreach ($order_list as $ord) {
						$orders[$o]['orders_id'] = $ord->orders_id;
						$orders[$o]['total_amount'] = $ord->total_amount;
						$orders[$o]['created_date'] = date("D M j,g:i a", strtotime($ord->created_date));
						$orders[$o]['outlet_address'] = $ord->outlet_address;
						$orders[$o]['user_address'] = ($ord->user_address != '') ? $ord->user_address : '';
						$orders[$o]['order_status'] = $ord->order_status;
						$user_image = $featured_image = URL::asset('assets/front/' . Session::get('general')->theme . '/images/no_image.png?' . time());
						$user_image = $featured_image = URL::asset('assets/front/' . Session::get('general')->theme . '/images/no_image.png?' . time());
						if (file_exists(base_path() . '/public/assets/admin/base/images/users/' . $ord->user_image) && $ord->user_image != '') {
							$user_image = url('/assets/admin/base/images/users/' . $ord->user_image . '?' . time());
						}
						if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/list/' . $ord->featured_image) && $ord->featured_image != '') {
							$featured_image = url('/assets/admin/base/images/vendors/list/' . $ord->featured_image . '?' . time());
						}
						$orders[$o]['user_image'] = $user_image;
						$orders[$o]['featured_image'] = $featured_image;
						$o++;
					}
				}
				$order_status = DB::table('order_status')
					->select('*')
					->orderBy('order_status.id', 'desc')
					->get();
				$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Orders list"), 'order_list' => $orders, 'order_status' => $order_status));
			} catch (JWTException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			} catch (TokenExpiredException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}
	/* driver order detail */
	public function driver_order_detail(Request $data) {

		$data_all = $data->all();
		$rules = [
			'driver_id' => ['required'],
			'order_id' => ['required'],
			'language' => ['required'],
			'token' => ['required'],
		];
		$errors = $result = array();

		$validator = app('validator')->make($data->all(), $rules);

		if ($validator->fails()) {
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "Message" => $errors, "Error" => trans("messages.Error List")));
		} else {
			try {

				$check_auth = JWTAuth::toUser($data_all['token']);
				$language_id = $data_all["language"];
				$query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
				$query2 = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and orders.vendor_id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

				$order_list = DB::table('orders')
					->select('orders.id as orders_id', 'vendors.featured_image', 'users.image as user_image', 'users.first_name as user_first_name', 'users.last_name as user_last_name', 'users.mobile as user_mobile', 'drivers.first_name as driver_first_name', 'drivers.last_name as driver_last_name', 'orders.total_amount', 'orders.order_status', 'orders.modified_date', 'orders.created_date', 'orders.order_key_formated', 'outlet_infos.contact_address as outlet_address', 'user_address.address as user_address', 'user_address.latitude as user_latitude', 'user_address.longtitude as user_longitude', 'outlets.latitude as outlet_latitude', 'outlets.longitude as outlet_longtitude', 'vendors_infos.vendor_name', 'outlet_infos.outlet_name', 'orders.digital_signature', 'orders.order_attachment')
					->Join('vendors', 'vendors.id', '=', 'orders.vendor_id')
					->Join('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
					->Join('driver_orders', 'driver_orders.order_id', '=', 'orders.id')
					->Join('outlets', 'outlets.id', '=', 'orders.outlet_id')
					->join('outlet_infos', 'outlets.id', '=', 'outlet_infos.id')
					->Join('users', 'users.id', '=', 'orders.customer_id')
					->leftJoin('user_address', 'orders.delivery_address', '=', 'user_address.id')
					->Join('drivers', 'drivers.id', '=', 'driver_orders.driver_id')
					->where('drivers.id', '=', $data_all['driver_id'])
					->where('orders.id', '=', $data_all['order_id'])
					->whereRaw($query1)
					->whereRaw($query2)
					->orderBy('orders.id', 'desc')
					->get();
				$orders = array();
				if (count($order_list) > 0) {
					$o = 0;
					foreach ($order_list as $ord) {
						$orders[$o]['order_id'] = $ord->orders_id;
						$orders[$o]['total_amount'] = $ord->total_amount;
						$orders[$o]['created_date'] = date("D M j,g:i a", strtotime($ord->created_date));
						$orders[$o]['outlet_address'] = $ord->outlet_address;
						$orders[$o]['outlet_name'] = $ord->outlet_name;
						$orders[$o]['vendor_name'] = $ord->vendor_name;
						$orders[$o]['user_address'] = ($ord->user_address != '') ? $ord->user_address : '';
						$orders[$o]['driver_first_name'] = ($ord->driver_first_name != '') ? $ord->driver_first_name : '';
						$orders[$o]['driver_last_name'] = ($ord->driver_last_name != '') ? $ord->driver_last_name : '';
						$orders[$o]['user_first_name'] = ($ord->user_first_name != '') ? $ord->user_first_name : '';
						$orders[$o]['user_last_name'] = ($ord->user_last_name != '') ? $ord->user_last_name : '';
						$orders[$o]['user_mobile'] = ($ord->user_mobile != '') ? $ord->user_mobile : '';
						$orders[$o]['user_latitude'] = ($ord->user_latitude != '') ? $ord->user_latitude : '';
						$orders[$o]['user_longitude'] = ($ord->user_longitude != '') ? $ord->user_longitude : '';
						$orders[$o]['outlet_latitude'] = ($ord->outlet_latitude != '') ? $ord->outlet_latitude : '';
						$orders[$o]['outlet_longtitude'] = ($ord->outlet_longtitude != '') ? $ord->outlet_longtitude : '';

						$user_image = $featured_image = URL::asset('assets/front/' . Session::get('general')->theme . '/images/no_image.png?' . time());
						if (file_exists(base_path() . '/public/assets/admin/base/images/users/' . $ord->user_image) && $ord->user_image != '') {
							$user_image = url('/assets/admin/base/images/users/' . $ord->user_image . '?' . time());
						}
						if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/list/' . $ord->featured_image) && $ord->featured_image != '') {
							$featured_image = url('/assets/admin/base/images/vendors/list/' . $ord->featured_image . '?' . time());
						}
						$orders[$o]['user_image'] = $user_image;
						$orders[$o]['featured_image'] = $featured_image;

						$digital_signature = $order_attachment = '';
						if (file_exists(base_path() . '/public/assets/front/' . Session::get('general')->theme . '/images/digital_signature/' . $ord->digital_signature) && $ord->digital_signature != '') {
							$digital_signature = url('/assets/front/' . Session::get('general')->theme . '/images/digital_signature/' . $ord->digital_signature . '?' . time());
						}
						if (file_exists(base_path() . '/public/assets/front/' . Session::get('general')->theme . '/images/order_attachment/' . $ord->order_attachment) && $ord->order_attachment != '') {
							$order_attachment = url('/assets/front/' . Session::get('general')->theme . '/images/order_attachment/' . $ord->order_attachment . '?' . time());
						}
						$orders[$o]['digital_signature'] = $digital_signature;
						$orders[$o]['order_attachment'] = $order_attachment;

						$query = 'pi.lang_id = (case when (select count(products_infos.lang_id) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and p.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
						$query1 = 'weight_classes_infos.lang_id = (case when (select count(weight_classes_infos.lang_id) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and weight_classes.id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
						$order_items = DB::select('SELECT p.product_image, pi.description,p.id AS product_id, oi.item_cost, oi.item_unit, oi.item_offer, o.total_amount, o.delivery_charge, o.service_tax, o.id as order_id, o.invoice_id, pi.product_name, pi.description, o.coupon_amount, weight_classes_infos.title, weight_classes_infos.unit as unit_code, o.order_key_formated, p.weight FROM orders o
                        LEFT JOIN orders_info oi ON oi.order_id = o.id
                        LEFT JOIN products p ON p.id = oi.item_id
                        LEFT JOIN products_infos pi ON pi.id = p.id
                        LEFT JOIN weight_classes ON weight_classes.id = p.weight_class_id
                        LEFT JOIN weight_classes_infos ON weight_classes_infos.id = weight_classes.id
                          where ' . $query . ' AND ' . $query1 . ' AND o.id = ? ORDER BY oi.id', array($data_all['order_id']));
						foreach ($order_items as $key => $items) {
							$invoic_pdf = url('/assets/front/' . Session::get('general')->theme . '/images/invoice/' . $items->invoice_id . '.pdf?' . time());
							$order_items[$key]->invoic_pdf = $invoic_pdf;
							$product_image = URL::asset('assets/front/' . Session::get('general')->theme . '/images/no_image.png');
							if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $items->product_image) && $items->product_image != '') {
								$product_image = url('/assets/admin/base/images/products/list/' . $items->product_image);
							}
							$order_items[$key]->product_image = $product_image;
						}
						$orders[$o]['orders'] = $order_items;
						$orders[$o]['order_status'] = $ord->order_status;
						$orders[$o]['modified_date'] = $ord->modified_date;
						$tracking_orders = array(1 => "Initiated", 10 => "Processed", 18 => "Packed", 19 => "Dispatched", 12 => "Delivered");
						$tracking_result = $mob_tracking_result = array();
						$t = 0;
						$last_state = $mob_last_state = "";
						foreach ($tracking_orders as $key => $track) {
							$mob_tracking_result[$t]['text'] = $track;
							$mob_tracking_result[$t]['process'] = "0";
							$mob_tracking_result[$t]['order_comments'] = $mob_tracking_result[$t]['date'] = "";
							$check_status = DB::table('orders_log')
								->select('order_id', 'log_time', 'order_comments')
								->where('order_id', '=', $data_all['order_id'])
								->where('order_status', '=', $key)
								->first();
							if (count($check_status) > 0) {
								$mob_last_state = $t;
								$mob_tracking_result[$t]['process'] = "1";
								$mob_tracking_result[$t]['order_comments'] = $check_status->order_comments;
								$mob_tracking_result[$t]['date'] = date('M j Y g:i A', strtotime($check_status->log_time));
							}
							$t++;
						}
						$orders[$o]['tracking_result'] = $mob_tracking_result;
						$o++;
					}
				}

				$order_status = DB::table('order_status')
					->select('*')
					->orderBy('order_status.id', 'desc')
					->get();
				$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Orders list"), 'order_list' => $orders, 'order_status' => $order_status));
			} catch (JWTException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			} catch (TokenExpiredException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}
	/* To check the driver availability and update the driver location */
	public function driver_update_location(Request $data) {

		$data_all = $data->all();
		$rules = [
			'language' => ['required'],
			'driver_id' => ['required'],
			'token' => ['required'],
			'device_id' => ['required'],
			'device_token' => ['required'],
			'latitude' => ['required'],
			'longitude' => ['required'],
			'login_type' => ['required'],
		];
		$errors = $result = array();

		$validator = app('validator')->make($data->all(), $rules);

		if ($validator->fails()) {
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "Message" => $errors, "Error" => trans("messages.Error List")));
		} else {
			try {
				// $check_auth = JWTAuth::toUser($data_all['token']);
				$language_id = $data_all["language"];
				$drivers = Drivers::find($data_all["driver_id"]);
				if (count($drivers) == 1) {
					if ($drivers->driver_status != 3) {

						$android_device_id = isset($data_all['device_id']) ? $data_all['device_id'] : '';
						$android_device_token = isset($data_all['device_token']) ? $data_all['device_token'] : '';

						$ios_device_id = isset($data_all['device_id']) ? $data_all['device_id'] : '';
						$ios_device_token = isset($data_all['device_token']) ? $data_all['device_token'] : '';

						$driver_settings = Driver_settings::find(1);
						$driver_track_location = DB::table('driver_track_location')
							->where('driver_id', $data_all["driver_id"])
							->get();
						if (count($driver_track_location) > 0) {
							$d_t_location = DB::table('driver_track_location')
								->where('driver_id', $data_all["driver_id"])
								->update(['created_date' => date("Y-m-d H:i:s"), 'latitude' => $data_all['latitude'], 'longitude' => $data_all['longitude'], 'android_device_id' => $android_device_id, 'android_device_token' => $android_device_token, 'ios_device_id' => $ios_device_id, 'ios_device_token' => $ios_device_token]);
						} else {

							$d_t_location = new Driver_track_location;
							$d_t_location->driver_id = $data_all['driver_id'];
							$d_t_location->today_date = date('Y-m-d');
							$d_t_location->latitude = $data_all['latitude'];
							$d_t_location->longitude = $data_all['longitude'];
							$d_t_location->login_type = $data_all['login_type'];
							$d_t_location->created_date = date("Y-m-d H:i:s");
							if (isset($data_all['login_type']) && !empty($data_all['login_type'])) {
								if ($data_all['login_type'] == 2) {
									$d_t_location->android_device_id = isset($data_all['device_id']) ? $data_all['device_id'] : '';
									$d_t_location->android_device_token = isset($data_all['device_token']) ? $data_all['device_token'] : '';
								}
								if ($data_all['login_type'] == 3) {
									$d_t_location->ios_device_id = isset($data_all['device_id']) ? $data_all['device_id'] : '';
									$d_t_location->ios_device_token = isset($data_all['device_token']) ? $data_all['device_token'] : '';
								}
							}
							$d_t_location->save();
						}

						$get_autoassign_order_logs = DB::table('autoassign_order_logs')
							->select('*')
							->where('autoassign_order_logs.driver_id', $data_all["driver_id"])
							->where('autoassign_order_logs.order_delivery_status', '=', 0)
							->where('autoassign_order_logs.auto_order_rejected', '=', 0)
							->where('autoassign_order_logs.driver_response', '=', 0)
							->orderby('autoassign_order_logs.id', 'desc')->first();
						// echo(count($get_autoassign_order_logs));
						if (count($get_autoassign_order_logs) > 0) {

							$orderassign_log_auto_id = $get_autoassign_order_logs->id;
							$assigned_date = $get_autoassign_order_logs->created_date;
							$assigned_time = strtotime("+ " . $driver_settings->order_accept_time . " minutes", strtotime(date('Y-m-d H:i:s')));
							$update_assign_time = date("Y-m-d H:i:s", $assigned_time);
// echo("___". ($assigned_date > $update_assign_time)."**");
							if ($assigned_date > $update_assign_time) {
								// echo("came inside");
								$order_logs_rejected = Autoassign_order_logs::find($orderassign_log_auto_id);
								$order_logs_rejected->auto_order_rejected = 1;
								$order_logs_rejected->updated_date = date("Y-m-d H:i:s");
								$order_logs_rejected->save();
								$affected = DB::update('update drivers set driver_status = 1 where id = ?', array($data_all["driver_id"]));

							}

						}
						$order_data = array();
// echo("sending fcms");
						if ($drivers->driver_status == 1) {

							$language = getCurrentLang();
							$condition = "orders.order_type!=0";
							$date = date("Y-m-d H:i:s");
							$time1 = strtotime($date);
							$time = $time1 - (60 * 60);
							$time2 = $time1 + (60 * 60);

							$driver_conditions = " and orders.driver_ids NOT IN ('" . $data_all['driver_id'] . "') ";
							$time_conditions = " and orders.modified_date > NOW() - INTERVAL '1 HOURS' ";

							$date = date("Y-m-d H:i:s", $time);
							$date2 = date("Y-m-d H:i:s", $time2);
							$query1 = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
							$query2 = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language . ' and orders.vendor_id = vendors_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
							$orders = DB::select('select order_id,order_key_formated,total_amount,created_date ,modified_date,delivery_date,first_name,last_name,status_name,color_code,user_name,outlet_name,vendor_name,distance from (select distinct on (orders.id) orders.id as order_id,orders.total_amount,orders.created_date,orders.modified_date,orders.order_key_formated,orders.delivery_date,users.first_name,users.last_name,order_status.name as status_name,order_status.color_code as color_code,users.name as user_name,outlet_infos.outlet_name,vendors_infos.vendor_name as vendor_name,orders.id,outlets.latitude as outlet_latitude,outlets.longitude as outlet_longitude,outlets.id as outlet_id,user_address.address as user_address,earth_distance(ll_to_earth(outlets.latitude,outlets.longitude), ll_to_earth(' . $data_all['latitude'] . ',' . $data_all['longitude'] . ')) as distance from "orders" left join "users" on "users"."id" = "orders"."customer_id" left join "order_status" on "order_status"."id" = "orders"."order_status" left join "user_address" on "orders"."delivery_address" = "user_address"."id" inner join "driver_orders" on "driver_orders"."order_id" = "orders"."id" inner join "vendors_infos" on "vendors_infos"."id" = "orders"."vendor_id" inner join "outlets" on "outlets"."id" = "orders"."outlet_id" inner join "outlet_infos" on "outlet_infos"."id" = "outlets"."id" where ' . $query1 . '  and ' . $query2 . '  ' . $time_conditions . ' and orders.order_type !=0 and earth_box(ll_to_earth(outlets.latitude,outlets.longitude), 5000) @>  ll_to_earth(' . $data_all['latitude'] . ',' . $data_all['longitude'] . ') and  "orders"."order_status" = 50 ' . $driver_conditions . ') as temp_table where distance < 30000  order by distance asc limit 1');
// echo("sending fcm".count($orders));
							if (count($orders) > 0) {

								foreach ($orders as $or) {

									$orders = DB::table('orders')
										->select('driver_ids')
										->where('orders.id', $data_all['order_id'])
										->first();

									$driver_ids = $orders->driver_ids;
									$new_orders = Order::find($data_all['order_id']);
									$new_orders->driver_ids = $driver_ids . $data_all['driver'] . ',';
									$assigned_time = strtotime("+ " . $driver_settings->order_accept_time . " minutes", strtotime(date('Y-m-d H:i:s')));
									$update_assign_time = date("Y-m-d H:i:s", $assigned_time);
									$new_orders->assigned_time = $update_assign_time;
									$new_orders->save();

									$order_title = '' . ucfirst($or->outlet_name) . ' , A new order delivery has been sent';
									$order_title1 = '' . ucfirst($or->outlet_name) . ' , تم ارسال طلب توصيل جديد';
									$order_logs = new Autoassign_order_logs;
									$order_logs->driver_id = $data_all['driver_id'];
									$order_logs->order_id = $or->order_id;
									$order_logs->driver_response = 0;
									$order_logs->driver_token = $data_all['device_token'];
									$order_logs->order_delivery_status = 0;
									$order_logs->assign_date = date("Y-m-d H:i:s");
									$order_logs->created_date = date("Y-m-d H:i:s");
									$order_logs->order_subject = $order_title;
									// $order_logs->order_subject_arabic = $order_title1;
									$order_logs->order_message = $order_title;
									$order_logs->save();
									$affected = DB::update('update drivers set driver_status = 2 where id = ?', array($data_all['driver_id']));
									$data = array
										(
										'id' => $or->order_id,
										'type' => 2,
										'title' => $order_title,
										'message' => $order_title,
										'subject' => $order_title,
										'log_id' => $order_logs->id,
										'order_key_formated' => $or->order_key_formated,
										'request_type' => 1,
										"order_accept_time" => $driver_settings->order_accept_time,
										'notification_dialog' => "1",
									);
									$order_data = array('id' => $or->order_id,
										'type' => 2,
										'title' => $order_title,
										'message' => $order_title,
										'subject' => $order_title,
										'log_id' => $order_logs->id,
										'order_key_formated' => $or->order_key_formated,
										'request_type' => 1,
										"order_accept_time" => $driver_settings->order_accept_time,
										'notification_dialog' => "1",
									);
									$fields = array
										(
										'registration_ids' => array($data_all['device_token']),
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
								}

							}

						}

						$result = array("response" => array("httpCode" => 200, "datas" => $order_data, "Message" => trans("messages.Driver location has been updated successfully")));
					} else {
						$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Driver is an offline")));
					}
				} else {
					$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Invalid driver credentials")));
				}
			} catch (JWTException $e) {
				//$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
				$result = array("response" => array("httpCode" => 400, "Message" => "haiii"));
			} catch (TokenExpiredException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => "haiii2"));
				//$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}
	/* To update the order status */
	public function change_order_status(Request $data) {
		$data_all = $data->all();
		$rules = array(
			'language' => 'required',
			'driver_id' => 'required',
			'token' => 'required',
			'order_status' => 'required',
			'order_id' => 'required',
			//'digital_signature' => 'mimes:png,jpeg,jpg',
			//'order_attachment' => 'required|mimes:png,jpeg,jpg'
		);
		$errors = $result = array();

		$validator = app('validator')->make($data->all(), $rules);

		if ($validator->fails()) {
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "Message" => $errors, "Error" => trans("messages.Error List")));
		} else {
			try {
				// $check_auth  = JWTAuth::toUser($data_all['token']);
				$language_id = $data_all['language'];
				$digital_signature = '';
				if ($data->file('digital_signature')) {
					$destinationPath = base_path() . '/public/assets/front/' . Session::get('general')->theme . '/images/digital_signature/'; // upload path
					$digital_signature = $data_all["order_id"] . '.' . $data->file('digital_signature')->getClientOriginalExtension();
					$data->file('digital_signature')->move(base_path() . '/public/assets/front/' . Session::get('general')->theme . '/images/digital_signature/', $digital_signature);
				}
				$order_attachment = '';
				if ($data->file('order_attachment')) {
					$destinationPath = base_path() . '/public/assets/front/' . Session::get('general')->theme . '/images/order_attachment/'; // upload path
					$order_attachment = $data_all["order_id"] . '.' . $data->file('order_attachment')->getClientOriginalExtension();
					$data->file('order_attachment')->move(base_path() . '/public/assets/front/' . Session::get('general')->theme . '/images/order_attachment/', $order_attachment);
				}
				$orders = Order::find($data_all['order_id']);

				if ($orders->order_status == 11) {

					$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.This order has been cancelled")));
					return json_encode($result, JSON_UNESCAPED_UNICODE);

				} else {

					if ($data_all['order_status'] == 12) {
						$affected = DB::update('update orders set order_status = ?,digital_signature = ?,order_attachment = ? where id = ?', array($data_all['order_status'], $digital_signature, $order_attachment, $data_all['order_id']));

						$affected = DB::update('update orders_log set order_status=?,digital_signature=?,order_attachment=?,log_time = ? where id = (select max(id) from orders_log where order_id = ' . $data_all['order_id'] . ')', array($data_all['order_status'], $digital_signature, $order_attachment, date("Y-m-d H:i:s")));

						/** auto order assign status update while complete customer delivery **/
						$affected = DB::update('update autoassign_order_logs set order_delivery_status = ? where driver_id = ? and order_id = ?', array(1, $data_all['driver_id'], $data_all['order_id']));
						$order_list = DB::table('orders')
							->select('orders.id as orders_id', 'orders.customer_id', 'orders.order_key_formated', 'orders.order_status', 'order_status.name as status_name')
							->leftJoin('order_status', 'orders.order_status', '=', 'order_status.id')
							->where('orders.id', '=', $data_all['order_id'])
							->orderBy('orders.id', 'desc')
							->first();
						$users = Users::find($order_list->customer_id);
						$orders = Orders::find($data_all['order_id']);
						$subject = 'Your Order with ' . getAppConfig()->site_name . ' [' . $orders->order_key_formated . '] has been successfully Delivered!';
						$values = array('order_id' => $data_all['order_id'],
							'customer_id' => $orders->customer_id,
							'vendor_id' => $orders->vendor_id,
							'outlet_id' => $orders->outlet_id,
							'message' => $subject,
							'read_status' => 0,
							'created_date' => date('Y-m-d H:i:s'));
						DB::table('notifications')->insert($values);
						$to = $users->email;
						$template = DB::table('email_templates')->select('*')->where('template_id', '=', self::ORDER_STATUS_UPDATE_USER)->get();
						if (count($template)) {
							$from = $template[0]->from_email;
							$from_name = $template[0]->from;
							if (!$template[0]->template_id) {
								$template = 'mail_template';
								$from = getAppConfigEmail()->contact_mail;
							}
							$subject = 'Your Order with ' . getAppConfig()->site_name . ' [' . $orders->order_key_formated . '] has been successfully Delivered!';
							$order_id = encrypt($data_all['order_id']);
							$reviwe_id = base64_encode('123abc');
							$orders_link = '<a href="' . URL::to("order-info/" . $order_id) . '" title="' . trans("messages.View") . '">' . trans("messages.View") . '</a>';
							$review_link = '<a href="' . URL::to("order-info/" . $order_id . '?r=' . $reviwe_id) . '" title="' . trans("messages.View") . '">' . trans("messages.View") . '</a>';
							$content = array('name' => "" . $users->name, 'order_key' => "" . $order_list->order_key_formated, 'status_name' => "" . $order_list->status_name, 'orders_link' => "" . $orders_link, "review_link" => $review_link);

							$attachment = "";
							$email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
						}

						$template = DB::table('email_templates')
							->select('*')
							->where('template_id', '=', self::DRIVER_ORDER__DELIVERED_RESPONSE_ADMIN_TEMPLATE)
							->get();
						if (count($template)) {
							$from = $template[0]->from_email;
							$from_name = $template[0]->from;
							//$subject = $template[0]->subject;
							$drivers = Drivers::find($data_all['driver_id']);
							if (!$template[0]->template_id) {
								$template = 'mail_template';
								$from = getAppConfigEmail()->contact_mail;
								$adminsubject = getAppConfig()->site_name . 'Order Delivered Successfully by the driver  - [' . $drivers->first_name . '-' . $drivers->last_name . ']';
								$from_name = "";
							}
							$adminsubject = getAppConfig()->site_name . 'Order Delivered Successfully by the driver  - [' . $drivers->first_name . '-' . $drivers->last_name . ']';

							$admin = Users::find(1);
							$admin_mail = $admin->email;
							$driver_name = $drivers->first_name . '-' . $drivers->last_name;
							$content = array('name' => "" . $admin->name, 'order_key' => "" . $order_list->order_key_formated, 'status_name' => "" . $order_list->status_name, 'driver_name' => "" . $drivers->first_name);
							$mail = smtp($from, $from_name, $admin_mail, $adminsubject, $content, $template);
						}

						/* Vendor email */

						/*  $vendors = Vendors::find($orders->vendor_id);
							                        $template_vendor = DB::table('email_templates')
							                        ->select('*')
							                        ->where('template_id','=',self::DRIVER_ORDER_DELIVERED_RESPONSE_VENDOR_TEMPLATE)
							                        ->get();
							                        if(count($template_vendor))
							                        {
							                        $from = $template_vendor[0]->from_email;
							                        $from_name=$template_vendor[0]->from;
							                        //$subject = $template[0]->subject;
							                        $drivers = Drivers::find($data_all['driver_id']);
							                        if(!$template_vendor[0]->template_id)
							                        {
							                        $template = 'mail_template';
							                        $from = getAppConfigEmail()->contact_mail;
							                        $adminsubject = getAppConfig()->site_name.'Order Delivered Successfully by the driver - ['.$drivers->first_name.'-'.$drivers->last_name.']';
							                        $from_name = "";
							                        }
							                        $adminsubject = getAppConfig()->site_name.'Order Delivered Successfully by the driver - ['.$drivers->first_name.'-'.$drivers->last_name.']';

							                        $vendor_email = $vendors->email;
							                        $vendor_name =$vendors->first_name.' '.$vendors->last_name;
							                        $driver_name = $drivers->first_name.'-'.$drivers->last_name;
							                        $content =array('name' =>"".$vendor_name,'order_key'=>"".$order_list->order_key_formated,'status_name'=>"".$order_list->status_name,'driver_name'=>"".$drivers->first_name);
							                        $mail = smtp($from,$from_name,$vendor_email,$adminsubject,$content,$template_vendor);
						*/

						$users = Users::find($orders->customer_id);
						if (!empty($users->android_device_token)) {
							$optionBuiler = new OptionsBuilder();
							$optionBuiler->setTimeToLive(60 * 20);
							$notificationBuilder = new PayloadNotificationBuilder($subject);
							$notificationBuilder->setBody($subject)->setSound('default')->setBadge(1);
							$dataBuilder = new PayloadDataBuilder();
							$dataBuilder->addData(['order_id' => $data_all['order_id'], "message" => $subject]);
							$option = $optionBuiler->build();
							$notification = $notificationBuilder->build();
							$data = $dataBuilder->build();
							$token = $users->android_device_token;
							$downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
							$downstreamResponse->numberSuccess();
							if ($downstreamResponse->numberSuccess() && $downstreamResponse->numberSuccess() == 1) {
								//Notification success
							}
							$downstreamResponse->numberFailure();
							$downstreamResponse->numberModification();
							$downstreamResponse->tokensToDelete();
							$downstreamResponse->tokensToModify();
							$downstreamResponse->tokensToRetry();
						}
						if (!empty($users->ios_device_token)) {
							$optionBuiler = new OptionsBuilder();
							$optionBuiler->setTimeToLive(60 * 20);
							$notificationBuilder = new PayloadNotificationBuilder($subject);
							$notificationBuilder->setBody($subject)->setSound('default')->setBadge(1);
							$dataBuilder = new PayloadDataBuilder();
							$dataBuilder->addData(['order_id' => $data_all['order_id'], "message" => $subject]);
							$option = $optionBuiler->build();
							$notification = $notificationBuilder->build();
							$data = $dataBuilder->build();
							$token = $users->ios_device_token;
							$downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
							$downstreamResponse->numberSuccess();
							if ($downstreamResponse->numberSuccess() && $downstreamResponse->numberSuccess() == 1) {
								//Notification success
							}
							$downstreamResponse->numberFailure();
							$downstreamResponse->numberModification();
							$downstreamResponse->tokensToDelete();
							$downstreamResponse->tokensToModify();
							$downstreamResponse->tokensToRetry();
						}

					} else {

						DB::update('delete from orders_log where order_status=? AND order_id = ?', array(19, $data_all['order_id']));
					}

				}

				/** auto order assign status update while complete customer delivery end **/

				if ($digital_signature != '' && $order_attachment != '') {
					$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Order status has been updated successfully")));
				} else {
					if ($digital_signature = '') {
						$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Dear user, please attach your signature")));
					} else {
						$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Please attach a photo of the invoice")));
					}

				}
			} catch (JWTException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			} catch (TokenExpiredException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	/* To update the order status */
	public function update_order_attachments(Request $data) {
		$data_all = $data->all();
		$rules = array(
			'language' => 'required',
			'driver_id' => 'required',
			'token' => 'required',
			'order_status' => 'required',
			'order_id' => 'required',
			'digital_signature' => 'mimes:png,jpeg,jpg|max:2024',
			'order_attachment' => 'mimes:png,jpeg,jpg|max:2024',
		);

		if (isset($post_data['language']) && $post_data['language'] == 2) {
			App::setLocale('ar');
		} else {
			App::setLocale('en');
		}
		$errors = $result = array();

		$validator = app('validator')->make($data->all(), $rules);

		if ($validator->fails()) {
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "Message" => $errors, "Error" => trans("messages.Error List")));
		} else {
			try {
				$check_auth = JWTAuth::toUser($data_all['token']);
				$language_id = $data_all['language'];
				$orders = Orders::find($data_all['order_id']);
				$digital_signature = '';
				if ($data->file('digital_signature')) {

					if (file_exists(base_path() . '/public/assets/front/' . Session::get('general')->theme . '/images/digital_signature/' . $orders->digital_signature) && $orders->digital_signature != '') {
						unlink(base_path() . '/public/assets/front/' . Session::get('general')->theme . '/images/digital_signature/' . $orders->digital_signature);
					}
					$destinationPath = base_path() . '/public/assets/front/' . Session::get('general')->theme . '/images/digital_signature/'; // upload path
					$digital_signature = $data_all["order_id"] . '.' . $data->file('digital_signature')->getClientOriginalExtension();
					$data->file('digital_signature')->move(base_path() . '/public/assets/front/' . Session::get('general')->theme . '/images/digital_signature/', $digital_signature);

					$affected = DB::update('update orders set order_status = ?,digital_signature = ? where id = ?', array($data_all['order_status'], $digital_signature, $data_all['order_id']));

					$affected = DB::update('update orders_log set order_status=?,digital_signature=? where id = (select max(id) from orders_log where order_id = ' . $data_all['order_id'] . ')', array($data_all['order_status'], $digital_signature));

					$status_message = trans("messages.Digital signature has been updated successfully");

				}
				$order_attachment = '';
				if ($data->file('order_attachment')) {
					if (file_exists(base_path() . '/public/assets/front/' . Session::get('general')->theme . '/images/order_attachment/' . $orders->order_attachment) && $orders->order_attachment != '') {
						unlink(base_path() . '/public/assets/front/' . Session::get('general')->theme . '/images/order_attachment/' . $orders->order_attachment);
					}
					$destinationPath = base_path() . '/public/assets/front/' . Session::get('general')->theme . '/images/order_attachment/'; // upload path
					$order_attachment = $data_all["order_id"] . '.' . $data->file('order_attachment')->getClientOriginalExtension();
					$data->file('order_attachment')->move(base_path() . '/public/assets/front/' . Session::get('general')->theme . '/images/order_attachment/', $order_attachment);

					$affected = DB::update('update orders set order_status = ?,order_attachment = ? where id = ?', array($data_all['order_status'], $order_attachment, $data_all['order_id']));

					$affected = DB::update('update orders_log set order_status=?,order_attachment=? where id = (select max(id) from orders_log where order_id = ' . $data_all['order_id'] . ')', array($data_all['order_status'], $order_attachment));

					$status_message = trans("messages.Image has been updated successfully");

				}

				/** auto order assign status update while complete customer delivery **/
				/** $affected  = DB::update('update autoassign_order_logs set order_delivery_status = ? where driver_id = ? and order_id = ?', array(1,$data_all['driver_id'],$data_all['order_id']));
				 ***/

				/** auto order assign status update while complete customer delivery end **/

				$result = array("response" => array("httpCode" => 200, "Message" => $status_message));
			} catch (JWTException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			} catch (TokenExpiredException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	/* To driver based report list using date */
	public function report_chart(Request $data) {
		$data_all = $data->all();
		$rules = array(
			'language' => 'required',
			'driver_id' => 'required',
			'token' => 'required',
			'search_date' => 'required',
		);
		$errors = $result = array();

		$validator = app('validator')->make($data->all(), $rules);

		if ($validator->fails()) {
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "Message" => $errors, "Error" => trans("messages.Error List")));
		} else {
			try {
				$check_auth = JWTAuth::toUser($data_all['token']);
				$language_id = $data_all['language'];
				$search_date = $data_all['search_date'];
				/* count of orders for delivered status*/
				$delivered_status = DB::table('orders')
					->select('orders.id')
					->Join('driver_orders', 'driver_orders.order_id', '=', 'orders.id')
					->where('driver_orders.driver_id', '=', $data_all['driver_id'])
					->where('orders.order_status', '=', 12)
					->where('orders.created_date', '>', $search_date . ' 00:00:00')
					->where('orders.created_date', '<', $search_date . ' 23:59:59')

					->count();
				// ->orderBy('orders.id', 'desc')
				// ->groupBy('orders.id')
				$dispatched_status = DB::table('orders')
					->select('orders.id')
					->Join('driver_orders', 'driver_orders.order_id', '=', 'orders.id')
					->where('driver_orders.driver_id', '=', $data_all['driver_id'])
					->where('orders.order_status', '=', 19)
					->where('orders.created_date', '>', $search_date . ' 00:00:00')
					->where('orders.created_date', '<', $search_date . ' 23:59:59')

					->count();

				$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Driver report"), 'delivered_status_count' => $delivered_status, 'dispatched_status_count' => $dispatched_status));
			} catch (JWTException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			} catch (TokenExpiredException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	/* To update drive details*/
	public function driver_signup(Request $data) {
		$post_data = $data->all();
		$rules = [

			'first_name' => ['required', 'max:56'],
			'last_name' => ['required', 'max:56'],
			'email' => ['required', 'email', 'max:250', 'unique:drivers,email'],
			'password' => ['required', 'min:5', 'max:32', 'regex:/(^[A-Za-z0-9 !@#$%]+$)+/', 'confirmed'],
			'password_confirmation' => ['required', 'min:5', 'max:32', 'regex:/(^[A-Za-z0-9 !@#$%]+$)+/'],
			'mobile' => ['required', 'max:50', 'regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/'],
			'country' => ['required'],
			'city' => ['required'],
			'contact_address' => ['required'],
			'driving_licence' => ['required'],
			'driver_licence' => ['required'],
			'identification_card' => ['required'],
			'profile_image' => ['required'],
			'terms_and_codintion' => ['required'],
			'latitude' => ['required'],
			'longitude' => ['required'],
		];
		$error = $result = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			$errors = '';
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$error[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $error);
			$result = array("response" => array("httpCode" => 400, "Error" => trans("messages.Error List"), "Message" => $errors));
		} else {
			try {

				$drivers = new Drivers;
				$drivers->first_name = $post_data['first_name'];
				$drivers->last_name = $post_data['last_name'];
				$drivers->mobile_number = $_POST['mobile'];
				$drivers->email = $_POST['email'];
				$drivers->address = $_POST['contact_address'];
				$drivers->hash_password = md5($_POST['password']);
				if (isset($_POST['country']) && $_POST['country'] != '') {
					$drivers->country_id = $_POST['country'];
				}
				if (isset($_POST['city']) && $_POST['city'] != '') {
					$drivers->city_id = $_POST['city'];
				}

				//$drivers->driver_status  = 1;
				$drivers->active_status = isset($_POST['active_status']);
				$drivers->latitude = isset($_POST['latitude']);
				$drivers->longitude = isset($_POST['longitude']);
				$drivers->is_verified = isset($_POST['is_verified']);
				$drivers->created_date = date("Y-m-d H:i:s");
				$drivers->modified_date = date("Y-m-d H:i:s");
				$verification_key = Text::random('alnum', 12);
				$drivers->verification_key = $verification_key;
				$drivers->terms_and_codintion = 1;
				$drivers->save();

				if (isset($post_data['driving_licence']) && $post_data['driving_licence'] != '') {

					$destinationPath = base_path() . '/public/assets/admin/base/images/drivers/driving_licence/'; // upload path
					$imageName = $drivers->id . '.' . $post_data['driving_licence']->getClientOriginalExtension();
					$data->file('driving_licence')->move($destinationPath, $imageName);
					$drivers->driving_licence = $imageName;
					$drivers->save();
				}

				if (isset($post_data['driver_licence']) && $post_data['driver_licence'] != '') {

					$destinationPath = base_path() . '/public/assets/admin/base/images/drivers/driver_licence/'; // upload path
					$imageName1 = $drivers->id . '.' . $post_data['driver_licence']->getClientOriginalExtension();
					$data->file('driver_licence')->move($destinationPath, $imageName1);
					$drivers->driver_licence = $imageName1;
					$drivers->save();
				}

				if (isset($post_data['identification_card']) && $post_data['identification_card'] != '') {

					$destinationPath = base_path() . '/public/assets/admin/base/images/drivers/identification_card/'; // upload path
					$imageName2 = $drivers->id . '.' . $post_data['identification_card']->getClientOriginalExtension();
					$data->file('identification_card')->move($destinationPath, $imageName2);
					$drivers->identification_card = $imageName2;
					$drivers->save();
				}

				if (isset($post_data['profile_image']) && $post_data['profile_image'] != '') {

					$destinationPath = base_path() . '/public/assets/admin/base/images/drivers/drivers/'; // upload path
					$imageName3 = $drivers->id . '.' . $post_data['profile_image']->getClientOriginalExtension();
					$data->file('profile_image')->move($destinationPath, $imageName2);
					$drivers->profile_image = $imageName2;
					$drivers->save();
				}

				//$this->driver_save_after($drivers,$post_data);
				$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Thank you for registering with us, our staff will contact you as soon as possible")));
			} catch (JWTException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			} catch (TokenExpiredException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	public function driver_save_after($object, $post) {
		$customer = $object->getAttributes();
		if ($customer['is_verified']) {
			$template = DB::table('email_templates')
				->select('from_email', 'from', 'subject', 'template_id', 'content')
				->where('template_id', '=', self::DRIVER_WELCOME_EMAIL_TEMPLATE)
				->get();
			if (count($template)) {
				$from = $template[0]->from_email;
				$from_name = $template[0]->from;
				$subject = $template[0]->subject;
				if (!$template[0]->template_id) {
					$template = 'mail_template';
					$from = getAppConfigEmail()->contact_email;
					$subject = "Welcome to " . getAppConfig()->site_name;
					$from_name = "";
				}
				$customer['name'] = ucfirst($customer['first_name']);
				$customer['password'] = $post['password'];
				$content = array("customer" => $customer);
				$email = smtp($from, $from_name, $customer['email'], $subject, $content, $template);
			}
		} else {
			$template = DB::table('email_templates')
				->select('from_email', 'from', 'subject', 'template_id', 'content')
				->where('template_id', '=', self::DRIVER_SIGNUP_EMAIL_TEMPLATE)
				->get();
			if (count($template)) {
				$from = $template[0]->from_email;
				$from_name = $template[0]->from;
				$subject = $template[0]->subject;
				if (!$template[0]->template_id) {
					$template = 'mail_template';
					$from = getAppConfigEmail()->contact_email;
					$subject = "Welcome to " . getAppConfig()->site_name;
					$from_name = "";
				}
				$url1 = '<a href="' . url('/') . '/drivers/confirmation?key=' . $customer['verification_key'] . '&email=' . $customer['email'] . '&password=' . $post['password'] . '"> This Confirmation Link </a>';
				$customer['name'] = ucfirst($customer['first_name']);
				$content = array("customer" => $customer, "confirmation_link" => $url1);
				$email = smtp($from, $from_name, $customer['email'], $subject, $content, $template);
			}
		}
	}

	/** store register **/
	public function assign_driver_orders(Request $data) {
		//print_r("expression");exit;
		$rules = [
			'order_id' => ['required', 'numeric'],
			'driver_id' => ['required', 'numeric'],
			'driver_responce' => ['required', 'numeric'],
			'autoassign_order_log_id' => ['required', 'numeric'],
			'request_type' => ['required'],
		];
		$post_data = $data->all();
		if (isset($post_data['language']) && $post_data['language'] == 2) {
			App::setLocale('ta');
		} else {
			App::setLocale('en');
		}

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

			$affected = DB::update('update drivers set driver_status = 1 where id = ?', array($post_data['driver_id']));
			
			//$affected = DB::update('update orders set request_vendor = 0 where id = ?', array($post_data['order_id']));

			/** Get Driver Info **/
			$driver_id = $post_data['driver_id'];
			$drivers = Drivers::find($driver_id);
			/** Get Driver Info End **/

			/** Get Order Info **/
			$order_id = $post_data['order_id'];
			$orders = Orders::find($order_id);

			/** Get Order Info End **/

			// $max_drivers =DB::select('select id,driver_id  from driver_orders  where order_id = '.$post_data['order_id'].' order by id desc limit 1');
			//print_r( $post_data['driver_responce'] );exit;

			$max_drivers = DB::table('autoassign_order_logs')
				->select('id', 'driver_id')
				->where('order_id', '=', $post_data['order_id'])
				->where('driver_response', '=', 1)
				->orderBy('id', '=', 'desc')
				->first();
			if (count($max_drivers) > 0) {
				if ($max_drivers->driver_id != $post_data['driver_id']) {

					$order_logs = Autoassign_order_logs::find($post_data['autoassign_order_log_id']);
					$order_logs->driver_response = 0;
					$order_logs->auto_order_rejected = 1;
					$order_logs->updated_date = date("Y-m-d H:i:s");
					$order_logs->save();

					$result = array("response" => array("httpCode" => 200, "status" => "Success", "Message" => trans("messages.This order already assigned to another driver")));
					return json_encode($result, JSON_UNESCAPED_UNICODE);

				}
			}

			/** Approved **/
			if ($post_data['driver_responce'] == 1) {

				$driver_order_info = DB::select('select id from driver_orders  where order_id = ? and driver_id = ?', array($post_data['order_id'], $post_data['driver_id']));

				if (count($driver_order_info) == 0) {

					$driver_order_assign_response = 'Order delivery has been accepted';
					$order_logs = Autoassign_order_logs::find($post_data['autoassign_order_log_id']);
					$order_logs->driver_response = $post_data['driver_responce'];
					$order_logs->notification_read = 1;
					$order_logs->updated_date = date("Y-m-d H:i:s");
					$order_logs->save();
					$driver_orders = new Driver_orders;
					$driver_orders->order_id = $post_data['order_id'];
					$driver_orders->driver_id = $post_data['driver_id'];
					$driver_orders->assigned_time = date("H:i:s");
					$driver_orders->created_at = date("Y-m-d H:i:s");
					$driver_orders->updated_at = date("Y-m-d H:i:s");
					$driver_orders->save();

					$current_date = date("Y-m-d H:i:s");
					$affected = DB::update('update orders set order_status = ?,modified_date = ? where id = ?', array(19, $current_date, $post_data['order_id']));
					$affected = DB::update('update orders_log set order_status=?,  log_time = ? where id = (select max(id) from orders_log where order_id = ' . $post_data['order_id'] . ')', array(19, $current_date));

					$outlet_details = Order::outlet_details_by_order($post_data['order_id']);

					$order_title = '' . ucfirst($outlet_details->outlet_name) . ' - ' . $outlet_details->order_key_formated . ' A new order has been assigned and confirmed by you';

					//$affected  = DB::update('update autoassign_order_logs set order_status = 19 where id = ?', array($post_data['order_id']));

					if (count($drivers)) {
						$subject = getAppConfig()->site_name . ' Order delivery accepted by the driver  - [' . $drivers->first_name . '-' . $drivers->last_name . ']';
						$template = DB::table('email_templates')
							->select('*')
							->where('template_id', '=', self::DRIVER_ORDER_RESPONSE_TEMPLATE)
							->get();
						if (count($template)) {
							$from = $template[0]->from_email;
							$from_name = $template[0]->from;
							//$subject = $template[0]->subject;
							if (!$template[0]->template_id) {
								$template = 'mail_template';
								$from = getAppConfigEmail()->contact_mail;
								$subject = getAppConfig()->site_name . 'Order delivery accepted by the driver  - [' . $drivers->first_name . '-' . $drivers->last_name . ']';
								$from_name = "";
							}

							$admin = Users::find(1);
							$admin_mail = $admin->email;
							$vendors = Vendors::find($orders->vendor_id);
							$vendor_mail = $vendors->email;
							$content = array("order" => array('name' => $admin->name, 'order_key' => $orders->order_key_formated, 'status' => $driver_order_assign_response));
							$content1 = array("order" => array('name' => $vendors->first_name, 'order_key' => $orders->order_key_formated, 'status' => $driver_order_assign_response));
							$mail = smtp($from, $from_name, $admin_mail, $subject, $content, $template);
							$mail = smtp($from, $from_name, $vendor_mail, $subject, $content1, $template);
						}

						$users = Users::find($orders->customer_id);
						if (!empty($users->android_device_token)) {
							$optionBuiler = new OptionsBuilder();
							$optionBuiler->setTimeToLive(60 * 20);
							$notificationBuilder = new PayloadNotificationBuilder($subject);
							$notificationBuilder->setBody($subject)->setSound('default')->setClickAction('com.app.Spizer_customer.Activites.NotificationsActivity')->setBadge(1);
							$dataBuilder = new PayloadDataBuilder();
							$dataBuilder->addData(['order_id' => $post_data['order_id'], "message" => $subject]);
							$option = $optionBuiler->build();
							$notification = $notificationBuilder->build();
							$data = $dataBuilder->build();
							$token = $users->android_device_token;
							$downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
							$downstreamResponse->numberSuccess();
							if ($downstreamResponse->numberSuccess() && $downstreamResponse->numberSuccess() == 1) {
								//Notification success
							}
							$downstreamResponse->numberFailure();
							$downstreamResponse->numberModification();
							$downstreamResponse->tokensToDelete();
							$downstreamResponse->tokensToModify();
							$downstreamResponse->tokensToRetry();

						}
						if (!empty($users->ios_device_token)) {
							$optionBuiler = new OptionsBuilder();
							$optionBuiler->setTimeToLive(60 * 20);
							$notificationBuilder = new PayloadNotificationBuilder($subject);
							$notificationBuilder->setBody($subject)->setSound('default')->setClickAction('com.app.Spizer_customer.Activites.NotificationsActivity')->setBadge(1);
							$dataBuilder = new PayloadDataBuilder();
							$dataBuilder->addData(['order_id' => $post_data['order_id'], "message" => $subject]);
							$option = $optionBuiler->build();
							$notification = $notificationBuilder->build();
							$data = $dataBuilder->build();
							$token = $users->ios_device_token;
							$downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
							$downstreamResponse->numberSuccess();
							/*if($downstreamResponse->numberSuccess() && $downstreamResponse->numberSuccess()==1){
								                            //Notification success
							*/
							$downstreamResponse->numberFailure();
							$downstreamResponse->numberModification();
							$downstreamResponse->tokensToDelete();
							$downstreamResponse->tokensToModify();
							$downstreamResponse->tokensToRetry();
						}
						$values = array('order_id' => $post_data['order_id'],
							'customer_id' => $orders->customer_id,
							'vendor_id' => $orders->vendor_id,
							'outlet_id' => $orders->outlet_id,
							'message' => $subject,
							'read_status' => 0,
							'created_date' => date('Y-m-d H:i:s'));
						DB::table('notifications')->insert($values);
					}

					/** Order Assign To The Driver End**/
				} else {
					$result = array("response" => array("httpCode" => 200, "status" => "Success", "Message" => trans("messages.This order already assigned to this driver")));
					return json_encode($result, JSON_UNESCAPED_UNICODE);
				}

			}

			/** Declined **/
			if (($post_data['driver_responce'] == 2) && ($post_data['request_type'] == 1)) {

				$driver_order_assign_response = 'Order delivery has been declined';
				$order_logs = Autoassign_order_logs::find($post_data['autoassign_order_log_id']);
				$order_logs->driver_response = $post_data['driver_responce'];
				$order_logs->notification_read = 1;
				$order_logs->updated_date = date("Y-m-d H:i:s");
				$order_logs->save();

				$subject = getAppConfig()->site_name . ' Order delivery declined by the driver  - [' . $drivers->first_name . '-' . $drivers->last_name . ']';
				$template = DB::table('email_templates')
					->select('*')
					->where('template_id', '=', self::DRIVER_ORDER_RESPONSE_TEMPLATE)
					->get();
				$users = Users::find(1);
				//$vendors = Vendors::find($orders->vendor_id);
				//$vendor_mail = $vendors->email;
				$admin_mail = $users->email;
				//$content = array("order" => array('name' =>$users->name,'order_key'=>$orders->order_key_formated,'status'=>$driver_order_assign_response));
				// $content1 = array("order" => array('name' =>$vendors->first_name,'order_key'=>$orders->order_key_formated,'status'=>$driver_order_assign_response));

				$order_detail = $this->get_order_detail($post_data['order_id']);

				$vendor_info = $order_detail["vendor_info"];

				$drivers_details = $vendor_info[0]->driver_ids;

				$driver_conditions = '';

				if ($drivers_details != '') {

					$driver_conditions = " and drivers.id NOT IN (" . rtrim($drivers_details, ",") . ") ";
				}

//print_r($driver_conditions);exit;

				$drivers = DB::select("select driver_id,distance ,first_name,last_name,android_device_token from (select DISTINCT ON (driver_track_location.driver_id) driver_id, drivers.first_name, drivers.last_name, driver_track_location.android_device_token,earth_distance(ll_to_earth(" . $vendor_info[0]->outlet_latitude . "," . $vendor_info[0]->outlet_longitude . "), ll_to_earth(driver_track_location.latitude, driver_track_location.longitude)) as distance from drivers left join driver_track_location on driver_track_location.driver_id = drivers.id where earth_box(ll_to_earth(" . $vendor_info[0]->outlet_latitude . "," . $vendor_info[0]->outlet_longitude . "), 300000)  @> ll_to_earth(driver_track_location.latitude, driver_track_location.longitude) and drivers.active_status=1 and drivers.is_verified=1 and drivers.driver_status = 1  and drivers.android_device_token != '' " . $driver_conditions . " order by driver_track_location.driver_id,distance asc) as temp_table limit 1");

				// print_r( $drivers);exit;

				if ((count($drivers) > 0)) {
					$assigned_drivers = 0;

					foreach ($drivers as $od => $odvalue) {

						if ($assigned_drivers == 1) {

							break;
						}

						$driver_settings = Driver_settings::find(1);
						$orders = $get_autoassign_order_logs12 = DB::table('orders')
							->select('driver_ids')
							->where('orders.id', $post_data['order_id'])
							->first();

						$driver_ids = $orders->driver_ids;
						$new_orders = Order::find($post_data['order_id']);
						$new_orders->driver_ids = $driver_ids . $odvalue->driver_id . ',';
						$assigned_time = strtotime("+ " . $driver_settings->order_accept_time . " minutes", strtotime(date('Y-m-d H:i:s')));
						$update_assign_time = date("Y-m-d H:i:s", $assigned_time);
						$new_orders->assigned_time = $update_assign_time;
						$new_orders->save();

						$created_date = date("Y-m-d H:i:s");
						$assigned_drivers = 1;

						$order_title = '' . ucfirst($vendor_info[0]->vendor_name) . ' , A new order delivery has been sent';
						$order_title1 = '' . ucfirst($vendor_info[0]->vendor_name) . ' , تم ارسال طلب توصيل جديد';
						$order_logs = new Autoassign_order_logs;
						$order_logs->driver_id = $odvalue->driver_id;
						$order_logs->order_id = $post_data['order_id'];
						$order_logs->driver_response = 0;
						$order_logs->driver_token = $odvalue->android_device_token;
						$order_logs->order_delivery_status = 0;
						$order_logs->order_subject = $order_title;
						// $order_logs->order_subject_arabic = $order_title1;
						$order_logs->order_message = $order_title;
						$order_logs->assign_date = date("Y-m-d H:i:s");
						$order_logs->created_date = date("Y-m-d H:i:s");

						$order_logs->save();

						$affected = DB::update('update drivers set driver_status = 2 where id = ?', array($odvalue->driver_id));

						$data = array
							(

							'id' => $post_data['order_id'],
							'type' => 2,
							'title' => $order_title,
							'message' => $order_title,
							'log_id' => $order_logs->id,
							'order_key_formated' => $vendor_info[0]->order_key_formated,
							'request_type' => 1,
							"order_accept_time" => $driver_settings->order_accept_time,
							'notification_dialog' => "1",
						);

						$fields = array
							(
							'registration_ids' => array($odvalue->android_device_token),
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
						//  print_r($result);exit;
						curl_close($ch);
					}
				}

			}
			$result = array("response" => array("httpCode" => 200, "status" => "Success", "Message" => trans("messages.Driver responded to the order assign process succesfully")));
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	/* Notification list */
	public function order_notification_list(Request $data) {

		$post_data = $data->all();
		if (isset($post_data['language']) && $post_data['language'] == 2) {
			App::setLocale('ar');
		} else {
			App::setLocale('en');
		}
		$rules = [
			'driver_id' => ['required'],
			//'token'   => ['required'],
		];
		$errors = $result = $notification_list = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "Status" => "Failure", "Error" => trans("messages.Error List"), "Message" => $errors, "Error" => $validator->errors()->messages()));
		} else {
			try {
				//$check_auth = JWTAuth::toUser($post_data['token']);
				$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.No notifications found"), 'data' => $notification_list));

				/** $notifications = DB::table('autoassign_order_logs')
				->select('autoassign_order_logs.id','autoassign_order_logs.order_id','autoassign_order_logs.driver_id','autoassign_order_logs.assign_date','autoassign_order_logs.created_date','autoassign_order_logs.notification_read','autoassign_order_logs.order_delivery_status','autoassign_order_logs.order_subject','autoassign_order_logs.driver_response','autoassign_order_logs.auto_order_rejected','autoassign_order_logs.order_message')
				->where('driver_id',$post_data['driver_id'])
				->orderBy('autoassign_order_logs.id','desc')
				->get();
				 **/

				$language_id = $post_data["language"];
				$query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
				$order_list = DB::table('orders')
					->select('orders.id as orders_id', 'vendors.featured_image', 'users.image as user_image', 'users.first_name as first_name', 'users.last_name as last_name', 'users.email as email', 'orders.total_amount', 'orders.order_key_formated', 'outlet_infos.contact_address as outlet_address', 'user_address.address as user_address', 'orders.order_status', 'autoassign_order_logs.id', 'autoassign_order_logs.order_id', 'autoassign_order_logs.driver_id', 'autoassign_order_logs.assign_date', 'autoassign_order_logs.created_date', 'autoassign_order_logs.notification_read', 'autoassign_order_logs.order_delivery_status', 'autoassign_order_logs.order_subject', 'autoassign_order_logs.driver_response', 'autoassign_order_logs.auto_order_rejected', 'autoassign_order_logs.order_message')
					->Join('vendors', 'vendors.id', '=', 'orders.vendor_id')
					->Join('autoassign_order_logs', 'autoassign_order_logs.order_id', '=', 'orders.id')
					->Join('outlets', 'outlets.id', '=', 'orders.outlet_id')
					->Join('users', 'users.id', '=', 'orders.customer_id')
					->join('outlet_infos', 'outlets.id', '=', 'outlet_infos.id')
					->leftJoin('user_address', 'orders.delivery_address', '=', 'user_address.id')
					->whereRaw($query1)
					->where('driver_id', '=', $post_data['driver_id'])
					->orderBy('autoassign_order_logs.id', 'desc')
					->get();

				//print_r($order_list); exit;
				$orders = array();
				if (count($order_list) > 0) {
					$o = 0;
					foreach ($order_list as $ord) {
						$orders[$o]['orders_id'] = $ord->orders_id;
						$orders[$o]['order_id_formated'] = $ord->order_key_formated;
						$orders[$o]['total_amount'] = $ord->total_amount;
						$orders[$o]['created_date'] = date("D M j,g:i a", strtotime($ord->created_date));
						$orders[$o]['outlet_address'] = $ord->outlet_address;
						$orders[$o]['user_name'] = $ord->first_name . ' ' . $ord->last_name;
						$orders[$o]['user_email'] = $ord->email;
						$orders[$o]['user_address'] = ($ord->user_address != '') ? $ord->user_address : '';
						$orders[$o]['order_status'] = $ord->order_status;
						$user_image = $featured_image = URL::asset('assets/front/' . Session::get('general')->theme . '/images/no_image.png?' . time());
						$user_image = $featured_image = URL::asset('assets/front/' . Session::get('general')->theme . '/images/no_image.png?' . time());
						if (file_exists(base_path() . '/public/assets/admin/base/images/users/' . $ord->user_image) && $ord->user_image != '') {
							$user_image = url('/assets/admin/base/images/users/' . $ord->user_image . '?' . time());
						}
						if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/list/' . $ord->featured_image) && $ord->featured_image != '') {
							$featured_image = url('/assets/admin/base/images/vendors/list/' . $ord->featured_image . '?' . time());
						}
						$orders[$o]['user_image'] = $user_image;
						$orders[$o]['featured_image'] = $featured_image;
						$orders[$o]['id'] = $ord->id;
						$orders[$o]['driver_id'] = $ord->driver_id;
						$orders[$o]['assign_date'] = $ord->assign_date;
						$orders[$o]['created_date'] = $ord->created_date;
						$orders[$o]['notification_read'] = $ord->notification_read;
						$orders[$o]['order_delivery_status'] = $ord->order_delivery_status;
						$orders[$o]['order_subject'] = $ord->order_subject;
						$orders[$o]['driver_response'] = $ord->driver_response;
						$orders[$o]['auto_order_rejected'] = $ord->auto_order_rejected;
						$orders[$o]['order_message'] = $ord->order_message;
						$orders[$o]['created_date_formated'] = timeAgo($ord->created_date);
						$o++;
					}
				}
				if (count($orders)) {
					$result = array("response" => array("httpCode" => 200, "status" => true, 'data' => $orders, 'Message' => trans('messages.Notification list')));
				}
			} catch (JWTException $e) {
				$result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
			} catch (TokenExpiredException $e) {
				$result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	/* Delete Notification list */
	public function delete_notification(Request $data) {
		$post_data = $data->all();
		if (isset($post_data['language']) && $post_data['language'] == 2) {
			App::setLocale('ar');
		} else {
			App::setLocale('en');
		}
		$rules = [
			'notification_id' => ['required'],
			'driver_id' => ['required'],
		];
		$errors = $result = $notification_list = array();
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

				$result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Invalid notification id")));
				$notifications = DB::table('autoassign_order_logs')->where('id', '=', $post_data['notification_id'])->where('driver_id', '=', $post_data['driver_id'])->where('order_id', '=', $post_data['order_id'])->delete();
				if ($notifications) {
					$result = array("response" => array("httpCode" => 200, "status" => true, 'Message' => trans('messages.Notification has been deleted successfully.')));
				}
			} catch (JWTException $e) {
				$result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
			} catch (TokenExpiredException $e) {
				$result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Kindly check the user credentials")));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}
	/* To update drive details*/
	public function update_reject_status(Request $data) {
		$post_data = $data->all();
		$rules = [
			'driver_id' => ['required'],
			'autoassign_order_log_id' => ['required', 'numeric'],
			'order_id' => ['required'],
			'request_type' => ['required'],
		];
		$error = $result = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			$errors = '';
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$error[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $error);
			$result = array("response" => array("httpCode" => 400, "Error" => trans("messages.Error List"), "Message" => $errors));
		} else {
			try {

				$orderassign_log_auto_id = $post_data['autoassign_order_log_id'];
				$order_logs_rejected = Autoassign_order_logs::find($orderassign_log_auto_id);
				$order_logs_rejected->auto_order_rejected = 1;
				$order_logs_rejected->updated_date = date("Y-m-d H:i:s");
				$order_logs_rejected->save();

				$affected = DB::update('update drivers set driver_status = 1 where id = ?', array($post_data['driver_id']));
				$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Driver information has been updated successfully")));
				$order_detail = $this->get_order_detail($post_data['order_id']);

				$vendor_info = $order_detail["vendor_info"];

				$drivers_details = $vendor_info[0]->driver_ids;

				$driver_conditions = '';

				if ($drivers_details != '') {

					$driver_conditions = " and drivers.id NOT IN (" . rtrim($drivers_details, ",") . ") ";
				}

				$time_conditions = " and orders.modified_date > NOW() - INTERVAL '1 HOURS' ";

				$drivers = DB::select("select driver_id,distance ,first_name,last_name,android_device_token from (select DISTINCT ON (driver_track_location.driver_id) driver_id, drivers.first_name, drivers.last_name, driver_track_location.android_device_token,earth_distance(ll_to_earth(" . $vendor_info[0]->outlet_latitude . "," . $vendor_info[0]->outlet_longitude . "), ll_to_earth(driver_track_location.latitude, driver_track_location.longitude)) as distance from drivers left join driver_track_location on driver_track_location.driver_id = drivers.id where earth_box(ll_to_earth(" . $vendor_info[0]->outlet_latitude . "," . $vendor_info[0]->outlet_longitude . "), 30000)  @> ll_to_earth(driver_track_location.latitude, driver_track_location.longitude) and drivers.active_status=1 and drivers.is_verified=1 and drivers.driver_status = 1  and drivers.android_device_token != '' " . $driver_conditions . " order by driver_track_location.driver_id,distance asc) as temp_table limit 1");
//print_r(  $drivers);exit;
				if ((count($drivers) > 0) && ($post_data['request_type'] == 1)) {
					$assigned_drivers = 0;

					foreach ($drivers as $od => $odvalue) {
						if ($assigned_drivers == 1) {

							break;
						}

						$driver_settings = Driver_settings::find(1);

						$orders = DB::table('orders')
							->select('driver_ids')
							->where('orders.id', $post_data['order_id'])
							->first();
						$driver_ids = $orders->driver_ids;
						$new_orders = Order::find($post_data['order_id']);
						$new_orders->driver_ids = $driver_ids . $odvalue->driver_id . ',';
						$assigned_time = strtotime("+ " . $driver_settings->order_accept_time . " minutes", strtotime(date('Y-m-d H:i:s')));
						$update_assign_time = date("Y-m-d H:i:s", $assigned_time);
						$new_orders->assigned_time = $update_assign_time;
						$new_orders->save();

						$created_date = date("Y-m-d H:i:s");
						$order_title = '' . ucfirst($vendor_info[0]->vendor_name) . ' , A new order delivery has been sent';
						// $order_title1 = ''.ucfirst($vendor_info[0]->vendor_name).' , تم ارسال طلب توصيل جديد';
						$order_logs = new Autoassign_order_logs;
						$order_logs->driver_id = $odvalue->driver_id;
						$order_logs->order_id = $post_data['order_id'];
						$order_logs->driver_response = 0;
						$order_logs->driver_token = $odvalue->android_device_token;
						$order_logs->order_delivery_status = 0;
						$order_logs->order_subject = $order_title;
						// $order_logs->order_subject_arabic = $order_title1;
						$order_logs->order_message = $order_title;
						$order_logs->assign_date = date("Y-m-d H:i:s");
						$order_logs->created_date = date("Y-m-d H:i:s");

						$order_logs->save();
						$assigned_drivers == 1;

						$affected = DB::update('update drivers set driver_status = 2 where id = ?', array($odvalue->driver_id));

						$data = array
							(
							'id' => $post_data['order_id'],
							'type' => 2,
							'title' => $order_title,
							'message' => $order_title,
							'log_id' => $order_logs->id,
							'order_key_formated' => $vendor_info[0]->order_key_formated,
							'request_type' => 1,
							"order_accept_time" => $driver_settings->order_accept_time,
							'notification_dialog' => "1",
						);

						$fields = array
							(
							'registration_ids' => array($odvalue->android_device_token),
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
						// print_r( $result);exit;
						curl_close($ch);
					}
				}
			} catch (JWTException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			} catch (TokenExpiredException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}
	public function update_driver_order_status(Request $data) {
		$post_data = $data->all();
		$rules = [
			'driver_id' => ['required'],
			'autoassign_order_log_id' => ['required', 'numeric'],
		];
		$error = $result = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			$errors = '';
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$error[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $error);
			$result = array("response" => array("httpCode" => 400, "Error" => trans("messages.Error List"), "Message" => $errors));
		} else {
			try {

				$id = $post_data['driver_id'];
				$orderassign_log_auto_id = $post_data['autoassign_order_log_id'];
				$order_logs_rejected = Autoassign_order_logs::find($orderassign_log_auto_id);
				$order_logs_rejected->auto_order_rejected = 1;
				$order_logs_rejected->updated_date = date("Y-m-d H:i:s");
				$order_logs_rejected->save();

				$affected = DB::update('update drivers set driver_status = 1 where id = ' . $post_data['driver_id'] . '');
				$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Driver information has been updated successfully")));
			} catch (JWTException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			} catch (TokenExpiredException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	public function driver_logout(Request $data) {
		$post_data = $data->all();
		if ($post_data['language'] == 2) {
			App::setLocale('ta');
		} else {
			App::setLocale('en');
		}
		$rules = [
			'driver_id' => ['required'],
			'language' => ['required'],
		];
		$errors = $result = array();

		$validator = app('validator')->make($post_data, $rules);

		if ($validator->fails()) {
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "status" => false, "Message" => trans("messages.Error List"), "Error" => $errors));
		} else {
			$user_det = Drivers::find($post_data['driver_id']);
			$user_det['android_device_id'] = '';
			$user_det['android_device_token'] = '';
			$user_det['ios_device_id'] = '';
			$user_det['ios_device_token'] = '';
			$user_det->save();
			$result = array("response" => array("httpCode" => 200, "status" => true, "user_id" => $user_det->id, "Message" => trans("messages.Logged out successfully")));
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	public function get_order_detail($order_id) {
		$language_id = getCurrentLang();
		$query3 = 'vendors_infos.lang_id = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and vendors.id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$query4 = 'payment_gateways_info.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
		$vendor_info = DB::select('SELECT vendors_infos.vendor_name,vendors.email,outlets.latitude as outlet_latitude,outlets.longitude as outlet_longitude,o.id as order_id,o.created_date,o.driver_ids,o.order_status,order_status.name as status_name,order_status.color_code as color_code,payment_gateways_info.name as payment_gateway_name,o.outlet_id,vendors.id as vendor_id,o.order_key_formated
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
		$delivery_details = DB::select('SELECT o.delivery_instructions,ua.address,pg.id as payment_gateway_id,pgi.name,o.total_amount,o.delivery_charge,o.service_tax,dti.start_time,end_time,o.created_date,o.delivery_date,o.order_type,o.coupon_amount,o.customer_id FROM orders o
                    LEFT JOIN user_address ua ON ua.id = o.delivery_address
                    left join payment_gateways pg on pg.id = o.payment_gateway_id
                    left join payment_gateways_info pgi on pgi.payment_id = pg.id
                    left join delivery_time_slots dts on dts.id=o.delivery_slot
                    left join delivery_time_interval dti on dti.id = dts.time_interval_id
                    left join outlets out on out.id = o.outlet_id
                    where ' . $query2 . ' AND o.id = ?', array($order_id));
		$result = array("order_items" => $order_items, "delivery_details" => $delivery_details, "vendor_info" => $vendor_info);
		return $result;
	}

	//mob apis:

	public function mdriver_order_detail(Request $data) {

		$data_all = $data->all();
		$rules = [
			'driver_id' => ['required'],
			'order_id' => ['required'],
			'language' => ['required'],
			'token' => ['required'],
		];
		$errors = $result = array();

		$validator = app('validator')->make($data->all(), $rules);

		if ($validator->fails()) {
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "Message" => $errors, "Error" => trans("messages.Error List")));
		} else {
			try {

				$check_auth = JWTAuth::toUser($data_all['token']);
				$language_id = $data_all["language"];
				$query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
				$query2 = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and orders.vendor_id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

				$order_list = DB::table('orders')
					->select('orders.id as orders_id', 'vendors.featured_image', 'users.image as user_image', 'users.first_name as user_first_name', 'users.last_name as user_last_name', 'users.mobile as user_mobile', 'drivers.first_name as driver_first_name', 'drivers.last_name as driver_last_name', 'orders.total_amount', 'orders.order_status', 'orders.modified_date', 'orders.created_date', 'orders.order_key_formated', 'outlet_infos.contact_address as outlet_address', 'user_address.address as user_address', 'user_address.latitude as user_latitude', 'user_address.longitude as user_longitude', 'outlets.latitude as outlet_latitude', 'outlets.longitude as outlet_longtitude', 'vendors_infos.vendor_name', 'outlet_infos.outlet_name', 'orders.digital_signature', 'orders.order_attachment')
					->Join('vendors', 'vendors.id', '=', 'orders.vendor_id')
					->Join('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
					->Join('driver_orders', 'driver_orders.order_id', '=', 'orders.id')
					->Join('outlets', 'outlets.id', '=', 'orders.outlet_id')
					->join('outlet_infos', 'outlets.id', '=', 'outlet_infos.id')
					->Join('users', 'users.id', '=', 'orders.customer_id')
					->leftJoin('user_address', 'orders.delivery_address', '=', 'user_address.id')
					->Join('drivers', 'drivers.id', '=', 'driver_orders.driver_id')
					->where('drivers.id', '=', $data_all['driver_id'])
					->where('orders.id', '=', $data_all['order_id'])
					->whereRaw($query1)
					->whereRaw($query2)
					->orderBy('orders.id', 'desc')
					->get();
				$orders = array();
				if (count($order_list) > 0) {
					$o = 0;
					foreach ($order_list as $ord) {
						$orders[$o]['order_id'] = $ord->orders_id;
						$orders[$o]['total_amount'] = $ord->total_amount;
						$orders[$o]['created_date'] = date("D M j,g:i a", strtotime($ord->created_date));
						$orders[$o]['outlet_address'] = $ord->outlet_address;
						$orders[$o]['outlet_name'] = $ord->outlet_name;
						$orders[$o]['vendor_name'] = $ord->vendor_name;
						$orders[$o]['user_address'] = ($ord->user_address != '') ? $ord->user_address : '';
						$orders[$o]['driver_first_name'] = ($ord->driver_first_name != '') ? $ord->driver_first_name : '';
						$orders[$o]['driver_last_name'] = ($ord->driver_last_name != '') ? $ord->driver_last_name : '';
						$orders[$o]['user_first_name'] = ($ord->user_first_name != '') ? $ord->user_first_name : '';
						$orders[$o]['user_last_name'] = ($ord->user_last_name != '') ? $ord->user_last_name : '';
						$orders[$o]['user_mobile'] = ($ord->user_mobile != '') ? $ord->user_mobile : '';
						$orders[$o]['user_latitude'] = ($ord->user_latitude != '') ? $ord->user_latitude : '';
						$orders[$o]['user_longitude'] = ($ord->user_longitude != '') ? $ord->user_longitude : '';
						$orders[$o]['outlet_latitude'] = ($ord->outlet_latitude != '') ? $ord->outlet_latitude : '';
						$orders[$o]['outlet_longtitude'] = ($ord->outlet_longtitude != '') ? $ord->outlet_longtitude : '';

						$user_image = $featured_image = URL::asset('assets/front/' . Session::get('general')->theme . '/images/no_image.png?' . time());
						if (file_exists(base_path() . '/public/assets/admin/base/images/users/' . $ord->user_image) && $ord->user_image != '') {
							$user_image = url('/assets/admin/base/images/users/' . $ord->user_image . '?' . time());
						}
						if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/list/' . $ord->featured_image) && $ord->featured_image != '') {
							$featured_image = url('/assets/admin/base/images/vendors/list/' . $ord->featured_image . '?' . time());
						}
						$orders[$o]['user_image'] = $user_image;
						$orders[$o]['featured_image'] = $featured_image;

						$digital_signature = $order_attachment = '';
						if (file_exists(base_path() . '/public/assets/front/' . Session::get('general')->theme . '/images/digital_signature/' . $ord->digital_signature) && $ord->digital_signature != '') {
							$digital_signature = url('/assets/front/' . Session::get('general')->theme . '/images/digital_signature/' . $ord->digital_signature . '?' . time());
						}
						if (file_exists(base_path() . '/public/assets/front/' . Session::get('general')->theme . '/images/order_attachment/' . $ord->order_attachment) && $ord->order_attachment != '') {
							$order_attachment = url('/assets/front/' . Session::get('general')->theme . '/images/order_attachment/' . $ord->order_attachment . '?' . time());
						}
						$orders[$o]['digital_signature'] = $digital_signature;
						$orders[$o]['order_attachment'] = $order_attachment;

						$query = 'pi.lang_id = (case when (select count(products_infos.lang_id) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and p.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
						$query1 = 'weight_classes_infos.lang_id = (case when (select count(weight_classes_infos.lang_id) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and weight_classes.id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
						$order_items = DB::select('SELECT p.product_image, pi.description,p.id AS product_id, oi.item_cost, oi.item_unit, oi.item_offer, o.total_amount, o.delivery_charge, o.service_tax, o.id as order_id, o.invoice_id, pi.product_name, pi.description, o.coupon_amount, weight_classes_infos.title, weight_classes_infos.unit as unit_code, o.order_key_formated, p.weight FROM orders o
                        LEFT JOIN orders_info oi ON oi.order_id = o.id
                        LEFT JOIN products p ON p.id = oi.item_id
                        LEFT JOIN products_infos pi ON pi.id = p.id
                        LEFT JOIN weight_classes ON weight_classes.id = p.weight_class_id
                        LEFT JOIN weight_classes_infos ON weight_classes_infos.id = weight_classes.id
                          where ' . $query . ' AND ' . $query1 . ' AND o.id = ? ORDER BY oi.id', array($data_all['order_id']));
						foreach ($order_items as $key => $items) {
							$invoic_pdf = url('/assets/front/' . Session::get('general')->theme . '/images/invoice/' . $items->invoice_id . '.pdf?' . time());
							$order_items[$key]->invoic_pdf = $invoic_pdf;
							$product_image = URL::asset('assets/front/' . Session::get('general')->theme . '/images/no_image.png');
							if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $items->product_image) && $items->product_image != '') {
								$product_image = url('/assets/admin/base/images/products/list/' . $items->product_image);
							}
							$order_items[$key]->product_image = $product_image;
						}
						$orders[$o]['orders'] = $order_items;
						$orders[$o]['order_status'] = $ord->order_status;
						$orders[$o]['modified_date'] = $ord->modified_date;
						$tracking_orders = array(1 => "Initiated", 10 => "Processed", 18 => "Packed", 19 => "Dispatched", 12 => "Delivered");
						$tracking_result = $mob_tracking_result = array();
						$t = 0;
						$last_state = $mob_last_state = "";
						foreach ($tracking_orders as $key => $track) {
							$mob_tracking_result[$t]['text'] = $track;
							$mob_tracking_result[$t]['process'] = "0";
							$mob_tracking_result[$t]['order_comments'] = $mob_tracking_result[$t]['date'] = "";
							$check_status = DB::table('orders_log')
								->select('order_id', 'log_time', 'order_comments')
								->where('order_id', '=', $data_all['order_id'])
								->where('order_status', '=', $key)
								->first();
							if (count($check_status) > 0) {
								$mob_last_state = $t;
								$mob_tracking_result[$t]['process'] = "1";
								$mob_tracking_result[$t]['order_comments'] = $check_status->order_comments;
								$mob_tracking_result[$t]['date'] = date('M j Y g:i A', strtotime($check_status->log_time));
							}
							$t++;
						}
						$orders[$o]['tracking_result'] = $mob_tracking_result;
						$o++;
					}
				}

				$order_status = DB::table('order_status')
					->select('*')
					->orderBy('order_status.id', 'desc')
					->get();
				$result = array("response" => array("httpCode" => 200, "Message" => trans("messages.Orders list"), 'order_list' => $orders, 'order_status' => $order_status));
			} catch (JWTException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			} catch (TokenExpiredException $e) {
				$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials")));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

//Ram :

	public function plogin(Request $data) {
		$post_data = $data->all();

		if (isset($post_data['language']) && $post_data['language'] == 2) {
			App::setLocale('ar');
		} else {
			App::setLocale('en');
		}
		$rules = [
			'password' => 'required',
			'login_type' => ['required'],
			'phone' => ['required'],
			// 'longitude' => ['required'],
			'device_id' => ['required_if:login_type,2,3'],
			'device_token' => ['required_if:login_type,2,3'],
		];
		$errors = $result = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "message" => $errors));
		} else {
			$phone = $post_data['phone'];
			//$email = $post_data['email'];
			$password = md5($post_data['password']);
			$driver_data = DB::table('drivers')
				->select('drivers.id', 'drivers.social_title', 'first_name', 'drivers.last_name', 'drivers.email', 'drivers.active_status', 'drivers.created_date', 'drivers.modified_date', 'drivers.is_verified', 'drivers.profile_image', 'drivers.driver_status', 'drivers.mobile_number', 'drivers.country_code')
			//->where('drivers.email', $email)
				->where('drivers.mobile_number', $phone)
				->where('drivers.hash_password', $password)
				->where('drivers.active_status', 1)
				->first();
			if (count($driver_data) > 0) {

				if ($driver_data->is_verified == 0) {
					$result = array("status" => 3, "httpCode" => 400, "message" => trans('messages.Please confirm you mail to activation.'));
				} else if ($driver_data->active_status == 0) {
					$result = array("status" => 4, "httpCode" => 400, "message" => trans('messages.Your registration has blocked pls contact Your Admin.'));
				} else {
					if ($post_data['login_type'] == 2) {
						$res = DB::table('drivers')->where('id', $driver_data->id)->update(['android_device_token' => $post_data['device_token'], 'android_device_id' => $post_data['device_id'], 'login_type' => $post_data['login_type']]);
					}
					if ($post_data['login_type'] == 3) {
						$res = DB::table('drivers')->where('id', $driver_data->id)->update(['ios_device_token' => $post_data['device_token'], 'ios_device_id' => $post_data['device_id'], 'login_type' => $post_data['login_type']]);
					}
					$driver_image = url('/assets/admin/base/images/default_avatar_male.jpg?' . time());
					if (file_exists(base_path() . '/public/assets/admin/base/images/drivers/' . $driver_data->profile_image) && $driver_data->profile_image != '') {
						$driver_image = URL::to("assets/admin/base/images/drivers/" . $driver_data->profile_image . '?' . time());
					}

					$android_device_id = isset($post_data['device_id']) ? $post_data['device_id'] : '';
					$android_device_token = isset($post_data['device_token']) ? $post_data['device_token'] : '';

					$ios_device_id = isset($post_data['device_id']) ? $post_data['device_id'] : '';
					$ios_device_token = isset($post_data['device_token']) ? $post_data['device_token'] : '';

					$driver_track_location = DB::table('driver_track_location')
						->where('driver_id', $driver_data->id)
						->get();
					if (count($driver_track_location) > 0) {
						$d_t_location = DB::table('driver_track_location')
							->where('driver_id', $driver_data->id)
							->update(['created_date' => date("Y-m-d H:i:s"), 'android_device_id' => $android_device_id, 'ios_device_id' => $ios_device_id]);

					} else {

						$d_t_location = new Driver_track_location;
						$d_t_location->driver_id = $driver_data->id;
						$d_t_location->today_date = date('Y-m-d');
						//$d_t_location->latitude = $post_data['latitude'];
						//$d_t_location->longitude = $post_data['longitude'];
						$d_t_location->login_type = $post_data['login_type'];
						$d_t_location->created_date = date("Y-m-d H:i:s");
						if (isset($post_data['login_type']) && !empty($post_data['login_type'])) {
							if ($post_data['login_type'] == 2) {
								$d_t_location->android_device_id = isset($post_data['device_id']) ? $post_data['device_id'] : '';
								$d_t_location->android_device_token = isset($post_data['device_token']) ? $post_data['device_token'] : '';
							}
							if ($post_data['login_type'] == 3) {
								$d_t_location->ios_device_id = isset($post_data['device_id']) ? $post_data['device_id'] : '';
								$d_t_location->ios_device_token = isset($post_data['device_token']) ? $post_data['device_token'] : '';
							}
						}
						$d_t_location->save();
					}
					//$driver_data->id = base64_encode('id');
					$token = JWTAuth::fromUser($driver_data, array('exp' => 200000000000));
					$result = array("status" => 200, "message" => trans('messages.Successfully loggedIn'), "httpcode" => 200, "detail" => array("userName" => isset($driver_data->first_name) ? $driver_data->first_name : "", "imageUrl" => $driver_image, "userEmail" => $driver_data->email, "countryCode" => $driver_data->country_code, "phoneNum" => $driver_data->mobile_number, "id" => $driver_data->id, "token" => $token));
				}
			} else {
				$result = array("status" => 2, "httpCode" => 400, 'message' => trans('messages.Invalid phoneNumber or password'));
			}
		}
		return $result;
	}

	public function pUpdateProfile(Request $data) {
		$post_data = $data->all();
		$rules = [
			'driverId' => ['required'],
			'firstName' => ['required', 'max:56'],
			'lastName' => ['required', 'max:56'],
			'token' => ['required'],
			'mobile' => ['required', 'max:50', 'regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/'],
			'gender' => ['required'],
			'driverStatus' => ['required'],
		];
		$error = $result = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			$errors = '';
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$error[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $error);
			$result = array("response" => array("httpCode" => 400, "Error" => trans('messages.Error List'), "message" => $errors));
		} else {
			try {
				$id = $post_data['driverId'];
				$drivers = Drivers::find($id);
				$drivers->first_name = $post_data['firstName'];
				$drivers->last_name = $post_data['lastName'];
				$drivers->mobile_number = $_POST['mobile'];
				$drivers->gender = $post_data['gender'];
				$drivers->driver_status = $post_data['driverStatus'];
				$drivers->modified_date = date("Y-m-d H:i:s");
				$drivers->save();
				$result = array("response" => array("httpCode" => 200, "message" => trans('messages.Driver profile has been updated successfully')));
			} catch (JWTException $e) {
				$result = array("response" => array("httpCode" => 400, "message" => trans('messages.Kindly check the user credentials')));
			} catch (TokenExpiredException $e) {
				$result = array("response" => array("httpCode" => 400, "message" => trans('messages.Kindly check the user credentials')));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	public function pSendOtp(Request $data) {
		$rules = array(
			'language' => 'required',
			'phoneNumber' => 'required',
			'countryCode' => 'required',
		);
		$post_data = $data->all();
		if (isset($post_data['language'])) {
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
			$result = array("status" => 0, "Error" => trans('messages.Error List'), "message" => $errors);
		} else {
			$post_data = $data->all();
			$phone = $post_data['phoneNumber'];
			$countryCode = $post_data['countryCode'];
			$phoneNumber = $countryCode . '-' . $phone;
			$user_details = DB::table('drivers')
				->select('id')
				->where('mobile_number', '=', $phone)
				->first();
			$result = array("status" => 0, "message" => trans('messages.Mobile Number is not register '));
			if (count($user_details) > 0) {
				$users = Drivers::find($user_details->id);

				$string = str_random(8);
				$pass_string = md5($string);
				$app_config = getAppConfig();
				//print_r($user_details);exit;

				$number = str_replace('-', '', $users->mobile_number); //to remove the '-'

				$message = 'You have received your new password for ' . getAppConfig()->site_name . '. Your New Password is ' . $string . '.';
				$twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
				$twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
				$from_number = "+14783471785";
				$client = new Services_Twilio($twilo_sid, $twilio_token);
				//print_r ($client);exit;
				// Create an authenticated client for the Twilio API
				try {
					$m = $client->account->messages->sendMessage(
						$from_number, // the text will be sent from your Twilio number
						$number, // the phone number the text will be sent to
						$message // the body of the text message
					);

					$users->hash_password = $pass_string;
					$users->updated_date = date("Y-m-d H:i:s");
					$users->save();
					$token = JWTAuth::fromUser($users, array('exp' => 200000000000));
					$result = array("status" => 1, "message" => trans('messages.New Password has been sent to your register mobile number.'), "detail" => array("countryCode" => $post_data['countryCode'], "phoneNumber" => $post_data['phoneNumber'], "userPassword" => $string));
				} catch (Exception $e) {
					$result = array("status" => 0, "message" => $e->getMessage());
					return json_encode($result);
				} catch (\Services_Twilio_RestException $e) {
					$result = array("status" => 0, "message" => $e->getMessage());
					return json_encode($result);
				}
			}
		}
		return json_encode($result);

	}

	public function pforgotPassword(Request $data) {
		$rules = array(
			'language' => 'required',
			'phoneNumber' => 'required',
			'countryCode' => 'required',
		);
		$post_data = $data->all();
		if (isset($post_data['language'])) {
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
			$result = array("status" => 0, "Error" => trans('messages.Error List'), "message" => $errors);
		} else {
			$post_data = $data->all();
			$phone = $post_data['phoneNumber'];
			$countryCode = $post_data['countryCode'];
			$phoneNumber = $phone;
			$user_details = DB::table('drivers')
				->select('id')
				->where('mobile_number', '=', $phoneNumber)
				->first();
			//  print_r($user_details); exit;
			$result = array("status" => 2, "message" => trans('messages.Mobile Number is not register'));
			if (count($user_details) > 0) {
				$users = Drivers::find($user_details->id);
				$otp = rand(1000, 9999);
				$otp_unique = str_random(8);
				$pass_string = md5($otp_unique);
				$app_config = getAppConfig();
				$number = str_replace('-', '', $users->mobile_number); //to remove the '-'
				$message = 'You have received OTP password for ' . getAppConfig()->site_name . '. Your OTP code is ' . $otp . '. This code can be used only once and dont share this code with anyone.';
				$twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
				$twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
				$from_number = "+14783471785";
				$client = new Services_Twilio($twilo_sid, $twilio_token);
				//print_r ($client);exit;
				// Create an authenticated client for the Twilio API
				try {
					$m = $client->account->messages->sendMessage(
						$from_number, // the text will be sent from your Twilio number
						$number, // the phone number the text will be sent to
						$message // the body of the text message
					);

					$users->phone_otp = $otp;
					$users->otp_unique = $pass_string;
					$users->updated_date = date("Y-m-d H:i:s");
					$users->save();
					$token = JWTAuth::fromUser($users, array('exp' => 200000000000));
					$result = array("status" => 1, "otpUnique" => $pass_string, "userOtp" => $otp, "message" => trans('messages.New OTP has been sent to your register mobile number.'));
				} catch (Exception $e) {
					$result = array("status" => 0, "message" => $e->getMessage());
					return json_encode($result);
				} catch (\Services_Twilio_RestException $e) {
					$result = array("status" => 0, "message" => $e->getMessage());
					return json_encode($result);
				}
			}
		}
		return json_encode($result);

	}

	public function otp_process(Request $data) {
		$post_data = $data->all();

		$rules = array(
			'otpUnique' => 'required',

		);

		if (isset($post_data['language']) && $post_data['language'] == 2) {
			App::setLocale('ar');
		} else {
			App::setLocale('en');
		}

		//print_r($post_data); exit;
		$user_details = DB::table('drivers')
			->select('id')
		//->where('otp_unique', '=', $post_data['otpUnique'])
			->where('phone_otp', '=', $post_data['otp'])
			->first();
		//  print_r($user_details); exit;
		$result = array("status" => 0, "message" => trans('messages.Verification Failed kindly check your otp or otpUnique'));
		if (count($user_details) > 0) {

			$user_data = Drivers::find($user_details->id);
			$otp_unique = str_random(8);
			$pass_string = md5($otp_unique);
			$user_data->otp_unique = $pass_string;
			$user_data->is_verified = 1;
			$user_data->save();
			$token = JWTAuth::fromUser($user_data, array('exp' => 200000000000));

			$result = array("status" => 1, "userUnique" => $pass_string, "message" => trans('messages.OTP Verified Successfully,Please change password.'));

		}
		return json_encode($result);
	}

	public function update_new_password(Request $data) {

		$data_all = $data->all();
		// if ($data_all['language'] == 2) {
		// 	App::setLocale('ar');
		// } else {
		// 	App::setLocale('en');
		// }

		$rules = [
			'userUnique' => ['required'],
			'newPassword' => ['required'],
			'retypePassword' => ['required'],
		];
		$errors = $result = array();

		$validator = app('validator')->make($data->all(), $rules);

		if ($validator->fails()) {

			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("status" => 0, "httpCode" => 400, "message" => $errors, "Error" => trans('messages.Error List'));
		} else {

			$errors = '';
			$password = $data_all['newPassword'];
			if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*?[0-9])[a-zA-Z\d]{6,}$/', $password)) {
				$errors .= $validator->errors()->add('newPassword', 'The password Minimum 6 characters at least 1 Uppercase Alphabet, 1 Lowercase Alphabet and 1 Number.');
			}
			if ($errors != '') {
				$result = array("status" => 3, "httpCode" => 400, "message" => trans("messages.The password Minimum 6 characters at least 1 Uppercase Alphabet, 1 Lowercase Alphabet and 1 Number."));
			}

			$retypePassword = $data_all['retypePassword'];
			if ($retypePassword != $password) {

				$result = array("status" => 2, "httpCode" => 400, "message" => trans('messages.Password and retypePassword is not matched please check'));
			} else {

				//$check_auth = JWTAuth::toUser($data_all['token']);
				//Get new password details from posts

				$string = $data_all['newPassword'];
				$pass_string = md5($string);
				$session_userid = $data_all['userUnique'];
				$users = DB::table('drivers')
					->select('otp_unique', 'drivers.first_name', 'drivers.last_name', 'drivers.email')
					->where('drivers.otp_unique', $session_userid)

					->get();

				$res = DB::table('drivers')
					->where('otp_unique', '=', $session_userid)
					->update(['hash_password' => $pass_string]);
				//->save();

				if ($users['otp_unique'] = $rules['userUnique']) {

					$result = array("status" => 1, "httpCode" => 200, "message" => trans('messages.Your Password Changed Successfully ,Please login again.'));

				}
				// else {

				// 	$result = array("status" => 4, "httpCode" => 400, "message" => trans('messages.userUnique is not correct.'));
				// }
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	public function pDriverInfo(Request $data) {

		$post_data = $data->all();
		$rules = [
			'id' => ['required', 'integer'],
			//'token' => ['required'],
		];
		$error = $result = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			$errors = '';
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$error[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $error);
			$result = array("status" => 0, "httpCode" => 400, "Error" => trans("messages.Error List"), "message" => $errors);
		} else {
			try {
				//$check_auth = JWTAuth::toUser($post_data['token']);
				$driver_data = DB::table('drivers')
					->select('drivers.id', 'first_name as name', 'drivers.profile_image as imageUrl ', 'drivers.mobile_number as mobile', 'drivers.email')
					->where('drivers.id', $post_data['id'])
					->where('drivers.active_status', 1)
					->first();
				if (count($driver_data) > 0) {
					$driver_data->name = ($driver_data->name != '') ? $driver_data->name : '';
					//$driver_data->last_name = ($driver_data->last_name != '') ? $driver_data->last_name : '';
					$imageName = url('/assets/admin/base/images/default_avatar_male.jpg');
					if (file_exists(base_path() . '/public/assets/admin/base/images/drivers/' . $driver_data->imageUrl) && $driver_data->imageUrl != '') {
						$imageName = URL::to("/assets/admin/base/images/drivers/" . $driver_data->imageUrl . '?' . time());
					}
					$driver_data->imageUrl = $imageName;
					$result = array("status" => 1, "httpCode" => 200, "message" => trans("messages.Driver details"), 'data' => $driver_data);
				} else {
					$result = array("status" => 2, "httpCode" => 400, "message" => trans("messages.No driver found"));
				}
			} catch (JWTException $e) {
				$result = array("httpCode" => 400, "message" => trans("messages.Kindly check the user credentials"));
			} catch (TokenExpiredException $e) {
				$result = array("httpCode" => 400, "message" => trans("messages.Kindly check the user credentials"));
			}
		}
		return json_encode($result);exit;

	}

	public function pDriverLogout(Request $data) {
		$post_data = $data->all();
		// if ($post_data['language'] == 2) {
		// 	App::setLocale('ta');
		// } else {
		// 	App::setLocale('en');
		// }
		$rules = [
			'id' => ['required'],
			//'language' => ['required'],
		];
		$errors = $result = array();

		$validator = app('validator')->make($post_data, $rules);

		if ($validator->fails()) {
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("status" => 0, "httpCode" => 400, "message" => trans('messages.Error List'), "Error" => $errors);
		}

		// if ($post_data['driverId']!=== 0) {

		// 	echo "no";

		// }

		else {
			$user_det = Drivers::find($post_data['id']);
			$user_det['android_device_id'] = '';
			$user_det['android_device_token'] = '';
			$user_det['ios_device_id'] = '';
			$user_det['ios_device_token'] = '';
			$user_det->save();
			$result = array("status" => 1, "message" => trans('messages.Logged out successfully'));
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);

	}

	public function pUploadProfileImage(Request $data) {
		$data_all = $data->all();
		// if ($data_all['language'] == 2) {
		// 	App::setLocale('ar');
		// } else {
		// 	App::setLocale('en');
		// }
		$rules = [
			'image' => ['mimes:jpeg,jpg,png', 'max:5120'],
		];
		$errors = $result = array();
		$validator = app('validator')->make($data->all(), $rules);
		if ($validator->fails()) {
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "status" => false, "Message" => $errors, "Error" => trans("messages.Error List")));
		} else {
			//$check_auth = JWTAuth::toUser($data_all['token']);
			$id = $data_all['id'];
			// store datas in to database
			$users = Drivers::find($id);
			if (isset($data_all['image']) && $data_all['image'] != '') {
				$destinationPath = base_path() . '/public/assets/admin/base/images/admin/profile/'; // upload path
				$imageName = $users->id . '.' . $data_all['image']->getClientOriginalExtension();
				$data->file('image')->move($destinationPath, $imageName);
				$destinationPath1 = url('/assets/admin/base/images/admin/profile/' . $imageName);
				Image::make($destinationPath1)->fit(75, 75)->save(base_path() . '/public/assets/admin/base/images/admin/profile/thumb/' . $imageName);
				Image::make($destinationPath1)->fit(260, 170)->save(base_path() . '/public/assets/admin/base/images/admin/profile/' . $imageName);

				$users->profile_image = $imageName;
				$users->save();
			}
			$imageName = url('/assets/admin/base/images/default_avatar_male.jpg');
			if (file_exists(base_path() . '/public/assets/admin/base/images/admin/profile/' . $users->profile_image) && $users->profile_image != '') {
				$imageName = URL::to("assets/admin/base/images/admin/profile/" . $users->profile_image);
			}
			$result = array("data" => array("httpCode" => 200, "status" => 1, "Message" => trans("messages.Profile Image uploaded successfully"), "imagePath" => $imageName));
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	public function getcoreconfig(Request $data) {

		$data_all = $data->all();

		$rules = [
			'deviceId' => 'required',
		];
		$errors = $result = array();
		$validator = app('validator')->make($data->all(), $rules);
		if ($validator->fails()) {
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("response" => array("httpCode" => 400, "status" => false, "Message" => $errors, "Error" => trans("messages.Error List")));
		} else {

			$static = " {
    \"data\": {
    \"appName\": \"PSDN\",
    \"appLogo\": \"https://randomuser.me/api/portraits/men/82.jpg\",
    \"loginLogo\": \"https://randomuser.me/api/portraits/men/82.jpg\",
    \"countryCode\": \"+91\",
    \"aboutUs\": \"http://localhost/gov/AdminLTE-2.4.5/about_us.html\",
    \"terms\": \"http://localhost/gov/AdminLTE-2.4.5/terms_and_condition.html\",
     \"androidKey\": \"KSDGKSKDDSKGKCVVFD\",
        \"noImageUrl\": \"https://randomuser.me/api/portraits/men/82.jpg\",
        \"errorReportCase\": \"error_report\",
        \"socketUrl\": \"http://localhost/gov/AdminLTE-2.4.5/\"
},

 \"status\": 1,
    \"message\": \"success\"

}";

		}

		return $static;

	}

	public function driverOrderList(Request $data) {

		$post_data = $data->all();
		//$language_id = $post_data["language"];
		$rules = [
			'id' => ['required'],
			//'token' => ['required'],
		];
		$error = $result = array();
		$validator = app('validator')->make($post_data, $rules);
		if ($validator->fails()) {
			$errors = '';
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$error[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $error);
			$result = array("status" => 0, "httpCode" => 400, "Error" => trans("messages.Error List"), "message" => $errors);
		} else {

			$id = $rules;
			$driver_data = DB::table('orders')
				->join('driver_orders', 'orders.driver_ids', '=', 'driver_orders.driver_id')

				->where('driver_orders.driver_id', $post_data['id'])
				->select('orders.order_status', 'orders.id as orderId', 'orders.total_amount as totalAmount', 'orders.delivery_address as dropLocation', 'orders.created_date as orderTime', 'driver_orders.assigned_time')
				->distinct()
				->limit(10)
				->offset(0)
				->get($id);

			if ($driver_data) {
				$result = array("status" => 1, "httpCode" => 200, "message" => trans("messages.Driver order list"), 'data' => $driver_data);
			} else {
				$result = array("status" => 2, "httpCode" => 400, "message" => trans("messages.No driver found"));
			}

		}
		return json_encode($result);exit;
	}

	/* get driver location api 13/6/19*/

	public function getDriverCurrentLocation(Request $data) {
		$post_data = $data->all();

		$driver_data = DB::table('driver_track_location')
			->select('driver_track_location.driver_id', 'driver_track_location.latitude', 'driver_track_location.longitude')
			->where('driver_track_location.driver_id', $post_data['driver_id'])
			->first();
		if ($driver_data) {
			$result = array("status" => 1, "httpCode" => 200, "message" => trans("messages.Driver Details"), 'driver_details' => $driver_data);
		} else {
			$result = array("status" => 2, "httpCode" => 400, "message" => trans("messages.No details found"));
		}
		return $result;
	}
	/* get driver location api */

}
