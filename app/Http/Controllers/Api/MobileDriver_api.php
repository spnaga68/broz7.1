<?php
namespace App\Http\Controllers\Api;
use App;
use App\Http\Controllers\Controller;
use App\Model\drivers;
use App\Model\driver_orders;
use App\Model\driver_settings;
use App\Model\driver_track_location;
use App\Model\users;
use App\Model\vendors;
use DB;
use Dingo\Api\Http\Request;
DB::enableQueryLog();
use JWTAuth;
//use Services_Twilio;
use Twilio\Rest\Client;

use Session;
use Tymon\JWTAuth\Exceptions\JWTException;
use URL;
use App\Model\settings;
use App\Model\settings_infos;
use App\Model\driver_cores;
use Illuminate\Support\Facades\Validator;
use App\Model\order;
use App\Http\Controllers\Api\outlet;
use DateTime;
//use Intervention\Image\ImageManagerStatic as Image;
//use Image;


class MobileDriver_api extends Controller {
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
//Ram :

	public function driverLogin(Request $data) {

		$post_data = $data->all();

		if (isset($post_data['language']) && $post_data['language'] == 2) {
			App::setLocale('ar');
		} else {
			App::setLocale('en');
		}
		$rules = [
			'password' => 'required',
			//'login_type' => ['required'],
			'deviceType' => ['required'],
			'phone' => ['required'],
			// 'longitude' => ['required'],
			'deviceId' => ['required_if:deviceType,2,3'],
			'deviceToken' => ['required_if:deviceType,2,3'],
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
				->select('drivers.id', 'drivers.social_title', 'first_name', 'drivers.last_name', 'drivers.email', 'drivers.active_status', 'drivers.created_date', 'drivers.modified_date', 'drivers.is_verified', 'drivers.profile_image', 'drivers.driver_status', 'drivers.mobile_number', 'drivers.country_code', 'drivers.gender')
			//->where('drivers.email', $email)
				->where('drivers.mobile_number', $phone)
				->where('drivers.hash_password', $password)
				->where('drivers.active_status', 1)
				->first();

									//print_r($driver_data);exit();

			if (count($driver_data) > 0) {

				if ($driver_data->is_verified == 0) {
					$result = array("status" => 3, "httpCode" => 400, "message" => trans('messages.Please confirm you mail to activation.'));
				} else if ($driver_data->active_status == 0) {
					$result = array("status" => 4, "httpCode" => 400, "message" => trans('messages.Your registration has blocked pls contact Your Admin.'));
				} else {
					if ($post_data['deviceType'] == 2) {
						$res = DB::table('drivers')->where('id', $driver_data->id)->update(['android_device_token' => $post_data['deviceToken'], 'android_device_id' => $post_data['deviceId'], 'login_type' => $post_data['deviceType']]);
					}
					if ($post_data['deviceType'] == 3) {
						$res = DB::table('drivers')->where('id', $driver_data->id)->update(['ios_device_token' => $post_data['deviceToken'], 'ios_device_id' => $post_data['deviceId'], 'login_type' => $post_data['deviceType']]);
					}
					$driver_image = url('/assets/admin/base/images/default_avatar_male.jpg?' . time());
					if (file_exists(base_path() . '/public/assets/admin/base/images/drivers/' . $driver_data->profile_image) && $driver_data->profile_image != '') {
						$driver_image = URL::to("assets/admin/base/images/drivers/" . $driver_data->profile_image . '?' . time());
					}

					$android_device_id = isset($post_data['deviceId']) ? $post_data['deviceId'] : '';
					$android_device_token = isset($post_data['deviceToken']) ? $post_data['deviceToken'] : '';

					$ios_device_id = isset($post_data['deviceId']) ? $post_data['deviceId'] : '';
					$ios_device_token = isset($post_data['deviceToken']) ? $post_data['deviceToken'] : '';

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
						$d_t_location->login_type = $post_data['deviceType'];
						$d_t_location->created_date = date("Y-m-d H:i:s");
						if (isset($post_data['deviceType']) && !empty($post_data['deviceType'])) {
							if ($post_data['deviceType'] == 2) {
								$d_t_location->android_device_id = isset($post_data['deviceId']) ? $post_data['deviceId'] : '';
								$d_t_location->android_device_token = isset($post_data['deviceToken']) ? $post_data['deviceToken'] : '';
							}
							if ($post_data['deviceType'] == 3) {
								$d_t_location->ios_device_id = isset($post_data['deviceId']) ? $post_data['deviceId'] : '';
								$d_t_location->ios_device_token = isset($post_data['deviceToken']) ? $post_data['deviceToken'] : '';
							}
						}
						$d_t_location->save();
					}
					//$driver_data->id = base64_encode('id');
					$token = JWTAuth::fromUser($driver_data, array('exp' => 200000000000));


					$result = array("status" => 1, "message" => trans('messages.Successfully loggedIn'), "httpcode" => 200, "detail" => array("userName" => isset($driver_data->first_name) ? $driver_data->first_name : "", "imageUrl" => $driver_image, "userEmail" => $driver_data->email, "countryCode" => $driver_data->country_code, "phoneNum" => $driver_data->mobile_number, "id" => $driver_data->id, "token" => $token, "gender" => $gender, "last_name" => $last_name));

				}
			} else {
				$result = array("status" => 2, "httpCode" => 400, 'message' => trans('messages.Invalid phoneNumber or password'));
			}
		}
		return $result;
	}


	public function login(Request $data) { // for auto logout driver while another one using that number
		$post_data = $data->all();
		if (isset($post_data['language']) && $post_data['language'] == 2) {
			App::setLocale('ar');
		} else {
			App::setLocale('en');
		}
		$rules = [
			'password' => 'required',
			'deviceType' => ['required'],
			'phone' => ['required'],
			'deviceId' => ['required_if:deviceType,2,3'],
			'deviceToken' => ['required_if:deviceType,2,3'],
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
			//$check_auth = JWTAuth::toUser($post_data['token']);
			$phone = $post_data['phone'];
			$password = md5($post_data['password']);
			$driver_data = DB::table('drivers')
				->select('*')
				->where('drivers.mobile_number', $phone)
				->where('drivers.hash_password', $password)
				->where('drivers.active_status', 1)
				->first();
			if (count($driver_data) > 0) {
				$android_device_id = isset($post_data['deviceId']) ? $post_data['deviceId'] : '';
				$android_device_token = isset($post_data['deviceToken']) ? $post_data['deviceToken'] : '';

				$ios_device_id = isset($post_data['deviceId']) ? $post_data['deviceId'] : '';
				$ios_device_token = isset($post_data['deviceToken']) ? $post_data['deviceToken'] : '';

				if($post_data['deviceToken'] != $driver_data->android_device_token || $post_data['deviceType'] != $driver_data->ios_device_token) {
					$list =[31,32,19];
					$orders = DB::table('orders')
						->select('*')
						->where('orders.driver_ids', $driver_data->id)
						->whereIn('orders.order_status', $list)
						->count();
					if($orders == 0) {

						if ($driver_data->is_verified == 0) {
						$result = array("status" => 3, "message" => trans('messages.Please confirm you mail to activation.'));
						} else if ($driver_data->active_status == 0) {
							$result = array("status" => 4, "message" => trans('messages.Your registration has blocked pls contact Your Admin.'));
						} else {

							//$login_status_update = update_force_login($post_data, $driver_data->id);

							logout_push($driver_data);

							if ($post_data['deviceType'] == 2) {
								$res = DB::table('drivers')->where('id', $driver_data->id)->update(['android_device_token' => $post_data['deviceToken'], 'android_device_id' => $post_data['deviceId'], 'login_type' => $post_data['deviceType']]);
							}
							if ($post_data['deviceType'] == 3) {
								$res = DB::table('drivers')->where('id', $driver_data->id)->update(['ios_device_token' => $post_data['deviceToken'], 'ios_device_id' => $post_data['deviceId'], 'login_type' => $post_data['deviceType']]);
							}
							$driver_image = url('/assets/admin/base/images/default_avatar_male.jpg?' . time());
							if (file_exists(base_path() . '/public/assets/admin/base/images/drivers/' . $driver_data->profile_image) && $driver_data->profile_image != ''){
								$driver_image = URL::to("assets/admin/base/images/drivers/" . $driver_data->profile_image . '?' . time());
							}

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
								$d_t_location->login_type = $post_data['deviceType'];
								$d_t_location->created_date = date("Y-m-d H:i:s");
								if (isset($post_data['deviceType']) && !empty($post_data['deviceType'])) {
									if ($post_data['deviceType'] == 2) {
										$d_t_location->android_device_id = isset($post_data['deviceId']) ? $post_data['deviceId'] : '';
										$d_t_location->android_device_token = isset($post_data['deviceToken']) ? $post_data['deviceToken'] : '';
									}
									if ($post_data['deviceType'] == 3) {
										$d_t_location->ios_device_id = isset($post_data['deviceId']) ? $post_data['deviceId'] : '';
										$d_t_location->ios_device_token = isset($post_data['deviceToken']) ? $post_data['deviceToken'] : '';
									}
								}
								$d_t_location->save();
							}
							//$driver_data->id = base64_encode('id');
							$token = JWTAuth::fromUser($driver_data, array('exp' => 200000000000));
							$result = array("status" => 1, "message" => trans('messages.Successfully loggedIn'), "httpcode" => 200, "detail" => array("userName" => isset($driver_data->first_name) ? $driver_data->first_name : "", "imageUrl" => $driver_image, "userEmail" => $driver_data->email, "countryCode" => $driver_data->country_code, "phoneNum" => $driver_data->mobile_number, "id" => $driver_data->id, "token" => $token, "gender" => $driver_data->gender, "last_name" => $driver_data->last_name));
						}


					}else {
						$result = array("status" => 2, "httpCode" => 400, 'message' => trans('messages.driver is in trip'));
					}

				}else
				{

					if ($driver_data->is_verified == 0) {
						$result = array("status" => 3, "httpCode" => 400, "message" => trans('messages.Please confirm you mail to activation.'));
					} else if ($driver_data->active_status == 0) {
						$result = array("status" => 4, "httpCode" => 400, "message" => trans('messages.Your registration has blocked pls contact Your Admin.'));
					} else {
						if ($post_data['deviceType'] == 2) {
							$res = DB::table('drivers')->where('id', $driver_data->id)->update(['android_device_token' => $post_data['deviceToken'], 'android_device_id' => $post_data['deviceId'], 'login_type' => $post_data['deviceType']]);
						}
						if ($post_data['deviceType'] == 3) {
							$res = DB::table('drivers')->where('id', $driver_data->id)->update(['ios_device_token' => $post_data['deviceToken'], 'ios_device_id' => $post_data['deviceId'], 'login_type' => $post_data['deviceType']]);
						}
						$driver_image = url('/assets/admin/base/images/default_avatar_male.jpg?' . time());
						if (file_exists(base_path() . '/public/assets/admin/base/images/drivers/' . $driver_data->profile_image) && $driver_data->profile_image != '') {
							$driver_image = URL::to("assets/admin/base/images/drivers/" . $driver_data->profile_image . '?' . time());
						}

						

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
							$d_t_location->login_type = $post_data['deviceType'];
							$d_t_location->created_date = date("Y-m-d H:i:s");
							if (isset($post_data['deviceType']) && !empty($post_data['deviceType'])) {
								if ($post_data['deviceType'] == 2) {
									$d_t_location->android_device_id = isset($post_data['deviceId']) ? $post_data['deviceId'] : '';
									$d_t_location->android_device_token = isset($post_data['deviceToken']) ? $post_data['deviceToken'] : '';
								}
								if ($post_data['deviceType'] == 3) {
									$d_t_location->ios_device_id = isset($post_data['deviceId']) ? $post_data['deviceId'] : '';
									$d_t_location->ios_device_token = isset($post_data['deviceToken']) ? $post_data['deviceToken'] : '';
								}
							}
							$d_t_location->save();
						}
						//$driver_data->id = base64_encode('id');
						$token = JWTAuth::fromUser($driver_data, array('exp' => 200000000000));
						$result = array("status" => 1, "message" => trans('messages.Successfully loggedIn'), "httpcode" => 200, "detail" => array("userName" => isset($driver_data->first_name) ? $driver_data->first_name : "", "imageUrl" => $driver_image, "userEmail" => $driver_data->email, "countryCode" => $driver_data->country_code, "phoneNum" => $driver_data->mobile_number, "id" => $driver_data->id, "token" => $token, "gender" => $driver_data->gender, "last_name" => $driver_data->last_name));
					}
				}
			} else {
				$result = array("status" => 2, "httpCode" => 400, 'message' => trans('messages.Invalid phoneNumber or password'));
			}
		}
				return json_encode($result, JSON_UNESCAPED_UNICODE);

	}

	public function UpdateProfile(Request $data) {
		$post_data = $data->all();
		$rules = [
			'driverId' => ['required'],
			'firstName' => ['required', 'max:56'],
			'lastName' => ['required', 'max:56'],
			'token' => ['required'],
			'mobile' => ['required', 'max:50', 'regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/'],
			'gender' => ['required'],
			//'driverStatus' => ['required'],
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
			$result = array("status" => 2, "Error" => trans('messages.Error List'), "message" => $errors);
		} else {
			//$check_auth = JWTAuth::toUser($post_data['token']);
			try {
				$id = $post_data['driverId'];
				$drivers = Drivers::find($id);
				$drivers->first_name = $post_data['firstName'];
				$drivers->last_name = $post_data['lastName'];
				$drivers->mobile_number = $post_data['mobile'];
				$drivers->gender = $post_data['gender'];
				//$drivers->driver_status = $post_data['driverStatus'];
				$drivers->modified_date = date("Y-m-d H:i:s");
				$drivers->save();
				
				$driver_array= array();
				$drivers = Drivers::find($id);
 				$driver_image = url('/assets/admin/base/images/default_avatar_male.jpg?' . time());
				if (file_exists(base_path() . '/public/assets/admin/base/images/drivers/' . $drivers->profile_image) && $drivers->profile_image != '') {
					$driver_image = URL::to("assets/admin/base/images/drivers/" . $drivers->profile_image . '?' . time());
				}
   				$driver_array['userName']=$drivers->first_name;
				$driver_array['lname']=$drivers->last_name;
				$driver_array['email']=$drivers->email;
				$driver_array['mobile']=$drivers->mobile_number;
				$driver_array['id']=$drivers->id;
				$driver_array['image_url']=$driver_image;
				$driver_array['imageUrl']=$driver_image;
				//print_r($driver_array);exit;


				$result = array("status" => 1, "message" => trans('messages.Driver profile has been updated successfully'),'data'=>$driver_array);
			} catch (JWTException $e) {
				$result = array("status" => 2, "message" => trans('messages.Kindly check the user credentials'));
			} catch (TokenExpiredException $e) {
				$result = array("status" => 2, "message" => trans('messages.Kindly check the user credentials'));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}
	public function UploadProfileImage(Request $data) {
		$data_all = $data->all();
		// if ($data_all['language'] == 2) {
		// 	App::setLocale('ar');
		// } else {
		// 	App::setLocale('en');
		// }
		$rules = [
			//'image' => 'required|image|mimes:jpeg,jpg,png|max:5120',
			'image' => ['mimes:jpeg,jpg,png', 'max:5120'],
		];
		$errors = $result = array();
		$validator = app('validator')->make($data->all(), $rules);
		if ($validator->fails()) {
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result =  array( "status" => 0, "message" => $errors, "detail" => trans("messages.Error List"));
		} else {
			//$check_auth = JWTAuth::toUser($data_all['token']);
			$id = $data_all['id'];
			// store datas in to database
			$users = Drivers::find($id);
			if (isset($data_all['image']) && $data_all['image'] != '') {

				$destinationPath = base_path() . '/public/assets/admin/base/images/drivers/'; // upload path
				//$imageName = $users->id . '.' . $data_all['image']->getClientOriginalExtension();
        		$imageName = time().'.'.$data_all['image']->getClientOriginalExtension();
				//print_r($imageName);exit();
				$data->file('image')->move($destinationPath, $imageName);
				$destinationPath1 = url('/assets/admin/base/images/drivers/' . $imageName);
				//Image::make($destinationPath1)->fit(75, 75)->save(base_path() . '/public/assets/admin/base/images/admin/profile/thumb/' . $imageName);
				//Image::make($destinationPath1)->fit(260, 170)->save(base_path() . '/public/assets/admin/base/images/admin/profile/' . $imageName);
				//Image::make($destinationPath1)->save(base_path() . '/public/assets/admin/base/images/admin/profile/thumb/' . $imageName);
				//Image::make($destinationPath1)->save(base_path() . '/public/assets/admin/base/images/admin/profile/' . $imageName);
				
				$users->profile_image = $imageName;
				$users->save();
			}
			$imageName = url('/assets/admin/base/images/default_avatar_male.jpg');
			if (file_exists(base_path() . '/public/assets/admin/base/images/drivers/' . $users->profile_image) && $users->profile_image != '') {
				$imageName = URL::to("assets/admin/base/images/drivers/" . $users->profile_image);
			}
			$result = array("status" => 1, "message" => trans("messages.Profile Image uploaded successfully"), "imagePath" => $imageName);
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	public function SendOtp(Request $data) {
		$rules = array(
			'language' => 'required',
			'phoneNumber' => 'required',
			'countryCode' => 'required',
		);
		$post_data = $data->all();
		if (isset($post_data['language']) && $post_data['language'] == 2) {
            App::setLocale('ar');
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
			//$check_auth = JWTAuth::toUser($post_data['token']);
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
					/*$m = $client->account->messages->sendMessage(
						$from_number, // the text will be sent from your Twilio number
						$number, // the phone number the text will be sent to
						$message // the body of the text message
					);*/
                $m =  $client->messages->create($number, array('from' => $from_number, 'body' => $message));

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

	public function forgotPassword(Request $data) {
		$rules = array(
			'language' => 'required',
			'phoneNumber' => 'required',
			'countryCode' => 'required',
		);
		$post_data = $data->all();
		if (isset($post_data['language']) && $post_data['language'] == 2) {
            App::setLocale('ar');
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
			//$check_auth = JWTAuth::toUser($post_data['token']);
			$post_data = $data->all();
			$phone = $post_data['phoneNumber'];
			$countryCode = $post_data['countryCode'];
			$phoneNumber = $phone;
			$user_details = DB::table('drivers')
				->select('id')
				->where('mobile_number', '=', $phoneNumber)
				->first();
		 	//print_r($user_details); exit;
			$result = array("status" => 2, "message" => trans('messages.Mobile Number is not register'));
			if (count($user_details) > 0) {
				$users = Drivers::find($user_details->id);
				$otp = rand(1000, 9999);
				$otp_unique = str_random(8);
				$pass_string = md5($otp_unique);
				$app_config = getAppConfig();
				$number = str_replace('-', '', $users->mobile_number); //to remove the '-'
				$message = 'You have received OTP password for ' . getAppConfig()->site_name . '. Your OTP code is ' . $otp . '. This code can be used only once and dont share this code with anyone.';
				/*$twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
				$twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
				$from_number = "+14783471785";
				$client = new Services_Twilio($twilo_sid, $twilio_token);*/
				$twilo_sid = TWILIO_ACCOUNTSID;
                $twilio_token = TWILIO_AUTHTOKEN;
                $from_number = TWILIO_NUMBER;
                $client = new Client($twilo_sid, $twilio_token);
				//$number='8075802161';
				$number=$countryCode.$number;
			//	print_r($number);exit();
				//$number ='+918075802161';
				// Create an authenticated client for the Twilio API
				try {
					/*$m = $client->account->messages->sendMessage(
						$from_number, // the text will be sent from your Twilio number
						$number, // the phone number the text will be sent to
						$message // the body of the text message
					);*/
					//print_r($m);exit;
                $m =  $client->messages->create($number, array('from' => $from_number, 'body' => $message));

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
		//$check_auth = JWTAuth::toUser($post_data['token']);
		$user_details = DB::table('drivers')
			->select('id')
		//->where('otp_unique', '=', $post_data['otpUnique'])
			->where('phone_otp', '=', $post_data['otp'])
			->first();
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
			//$check_auth = JWTAuth::toUser($post_data['token']);
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

	public function DriverInfo(Request $data) {

		$post_data = $data->all();
		$rules = [
			'id' => ['required', 'integer'],
			//'driverId' => ['required', 'integer'],
			//'token' => ['required'],
		];
		//$error = $result = array();
		//print_r($post_data);exit;
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
				$post_data['driverId'] =$post_data['id'];
	

			try {
				//$check_auth = JWTAuth::toUser($post_data['token']);
				$driver_data = DB::table('drivers')
					->select('drivers.id', 'first_name as userName', 'drivers.profile_image as imageUrl ', 'drivers.mobile_number as mobile', 'drivers.email')
					->where('drivers.id', $post_data['driverId'])
					->where('drivers.active_status', 1)
					->first();
					//print_r(base_path() . '/public/assets/admin/base/images/drivers/');exit();
				if (count($driver_data) > 0) {
					//1573047460.jpg
					$driver_data->userName = ($driver_data->userName != '') ? $driver_data->userName : '';
					//$driver_data->last_name = ($driver_data->last_name != '') ? $driver_data->last_name : '';
					$imageName = url('/assets/admin/base/images/default_avatar_male.jpg');
					if (file_exists(base_path() . '/public/assets/admin/base/images/drivers/' . $driver_data->imageUrl) && $driver_data->imageUrl != '') {
						$imageName = URL::to("/assets/admin/base/images/drivers/" . $driver_data->imageUrl . '?' . time());
					}
					$driver_data->imageUrl = $imageName;

					 /**driver review average calculating and updated here **/ 
			        $reviews_average=DB::table('driver_reviews')
			                ->selectRaw('SUM(ratings) as total_rating,count(driver_reviews.driver_id) as tcount')
			                ->where("driver_reviews.driver_id","=",$post_data['driverId'])
			                ->get();
			       	//echo"<pre>";print_r($reviews_average);exit;
			        $average_rating=0;

			        if(count($reviews_average)){
			            $total_rating = $reviews_average[0]->total_rating;
			            if($total_rating)
			            {
				            $average_rating=$total_rating/$reviews_average[0]->tcount;
				            $average_rating    = round($average_rating);

			            }
			            
			            
			        }
					//print_r($average_rating);exit;
					$driver_data->driverRating = $average_rating;

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

	public function DriverLogout(Request $data) {
		$post_data = $data->all();
		// if ($post_data['language'] == 2) {
		// 	App::setLocale('ta');
		// } else {
		// 	App::setLocale('en');
		// }
		$rules = [
			//'driverId' => ['required'],
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
			//$check_auth = JWTAuth::toUser($post_data['token']);
			$post_data['driverId'] =$post_data['id'];
			$user_det = Drivers::find($post_data['driverId']);
			$user_det['android_device_id'] = '';
			$user_det['android_device_token'] = '';
			$user_det['ios_device_id'] = '';
			$user_det['ios_device_token'] = '';
			$user_det->save();
			$result = array("status" => 1, "message" => trans('messages.Logged out successfully'));
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
			$id=1;
			$driver_core = driver_cores::find($id);
			//print_r($driver_core);exit();
			$static = array();

			$deviceType =isset($data_all['deviceType'])?$data_all['deviceType']:'';
			if($deviceType == 1)
			{
				$static['data']['iosupdateType'] =  $driver_core['iosupdate_type'];
				$static['data']['iosupdateMessage'] =  $driver_core['iosupdate_message'];
				$static['data']['iosforceMessage'] =  $driver_core['iosupdate_message'];
				$static['data']['iosforceUpdateVersion'] =  $driver_core['iosforceupdate_version'];
				$static['data']['ioslatestVersion'] =  $driver_core['ioslatest_version'];
			}else
			{

				$static['data']['updateType'] =  $driver_core['update_type'];
				$static['data']['updateMessage'] =  $driver_core['update_message'];
				$static['data']['forceMessage'] =  $driver_core['update_message'];
				$static['data']['forceUpdateVersion'] =  $driver_core['forceupdate_version'];
				$static['data']['latestVersion'] =  $driver_core['latest_version'];

			}
			$static['data']['appName'] = $driver_core['app_name'];
			$static['data']['appLogo'] =  "https://randomuser.me/api/portraits/men/82.jpg";
			$static['data']['loginLogo'] =  "https://randomuser.me/api/portraits/men/82.jpg";
			$static['data']['countryCode'] =  $driver_core['country_code'];
			$static['data']['aboutUs'] =  BASE_URL.'/driverabout-us';
			$static['data']['terms'] =  BASE_URL.'/driverterms-condition';
			$static['data']['androidKey'] =  $driver_core['android_key'];
			$static['data']['noImageUrl'] =  $driver_core['no_imageurl'];
			$static['data']['errorReportCase'] =  $driver_core['error_reportcase'];
			$static['data']['socket_url'] =  $driver_core['socket_url'];
			$static['data']['contact_no'] =  "+91-8667730776";
			$static['data']['currentVersion'] =  "12";
			$static['data']['currencySymbol'] =  CURRENCYCODE;
			$static['status'] = 1;
			$static['message'] = "success"; 
			$result =json_encode($static);
			//print_r($static);exit;
			/*$static = " {
		    \"data\": {
		    \"appName\": \"PSDN\",
		    \"appLogo\": \"https://randomuser.me/api/portraits/men/82.jpg\",
		    \"loginLogo\": \"https://randomuser.me/api/portraits/men/82.jpg\",
		    \"countryCode\": \"+91\",
		    \"aboutUs\": \"http://localhost/gov/AdminLTE-2.4.5/about_us.html\",
		    \"terms\": \"http://localhost/gov/AdminLTE-2.4.5/terms_and_condition.html\",
		     \"androidKey\": \"KSDGKSKDDSKGKCVVFD\",
		      \"latestVersion\": 1,
		       \"forceUpdateVersion\": 0,
		        \"updateType\": 0,
		         \"updateMessage\": \"Kindly update the app\",
		        \"noImageUrl\": \"https://randomuser.me/api/portraits/men/82.jpg\",
		        \"errorReportCase\": \"error_report\",
		        \"socketUrl\": \"http://localhost/gov/AdminLTE-2.4.5/\"
			},

			 \"status\": 1,
			    \"message\": \"success\"

			}";
			print_r($static);exit;*/

		}

		return $result;

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
			//$check_auth = JWTAuth::toUser($post_data['token']);
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

	/* Changing the Order status */

	/*public function mchange_order_status(Request $data) {
		$post_data = $data->all();
		$rules = [
			'driverId' => ['required'],
			'orderId' => ['required'],
			//	'token' => ['required'],
			//	'language' => ['required'],
			'orderStatus' => ['required'],
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
			$result = array("status" => 2, "httpCode" => 400, "Error" => trans("messages.Error List"), "Message" => $errors);
		} else {
			try {

				$id = $post_data['driverId'];
				$order_id = $post_data['orderId'];

				if ($post_data['orderStatus'] == 2) {
					//Reject order  =>order_staus 18 =pending
					$reason = isset($post_data['reason']) ? $post_data['reason'] : '';
					$status_change = DB::update('update orders set orders_status = 18 ,reason = ' . $reason . ' where id = ' . $post_data['orderId'] . ', driver_ids = ' . $post_data['driverId'] . '');
				} else {
					//=>order_staus 12 =delivered
					//$status_change = DB::update('update orders set orders_status = 19 where id = ' . $post_data['orderId'] . ' AND driver_ids = ' . $post_data['driverId'] . '');

					// 19=> diapatched state
					$status_change = DB::update('update orders set order_status = 19 where id = ' . intval($post_data['orderId']) . '');

				}
				$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.Order Status updated successfully"));
			} catch (JWTException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
			} catch (TokenExpiredException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}*/
	/* Orders details */

	public function mdriver_order_info(Request $data) {
		$data_all = $data->all();
		$rules = [
			'driverId' => ['required'],
			'orderId' => ['required'],
			'lang' => ['required'],
			'token' => ['required'],
		];
		$errors = $result = array();

		$validator = app('validator')->make($data->all(), $rules);

		if ($validator->fails()) {
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("status" => 2, "httpCode" => 400, "Message" => $errors, "Error" => trans("messages.Error List"));
		} else {
			try {

				//$check_auth = JWTAuth::toUser($data_all['token']);
				$language_id = $data_all["lang"];
				$query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
				$query2 = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language_id . ' and orders.vendor_id = vendors_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';

				$order_list = DB::table('orders')
					->select('orders.id as orders_id', 'vendors.featured_image', 'users.image as user_image', 'users.first_name as user_first_name', 'users.last_name as user_last_name', 'users.mobile as user_mobile', 'drivers.first_name as driver_first_name', 'drivers.last_name as driver_last_name', 'orders.total_amount', 'orders.order_status', 'orders.modified_date', 'orders.created_date', 'orders.order_key_formated', 'outlet_infos.contact_address as outlet_address', 'user_address.address as user_address', 'user_address.latitude as user_latitude', 'user_address.longitude as user_longitude', 'outlets.latitude as outlet_latitude', 'outlets.longitude as outlet_longtitude', 'vendors_infos.vendor_name', 'outlet_infos.outlet_name', 'orders.digital_signature', 'orders.order_status', 'orders.assigned_time', 'orders.order_attachment', 'vendors.latitude', 'vendors.longitude', 'orders.customer_id')
					//->Join('orders_info', 'orders_info.order_id', '=', 'orders.id')
					->Join('vendors', 'vendors.id', '=', 'orders.vendor_id')
					->Join('vendors_infos', 'vendors_infos.id', '=', 'vendors.id')
					->Join('driver_orders', 'driver_orders.order_id', '=', 'orders.id')
					->Join('outlets', 'outlets.id', '=', 'orders.outlet_id')
					->join('outlet_infos', 'outlets.id', '=', 'outlet_infos.id')
					->Join('users', 'users.id', '=', 'orders.customer_id')
					->leftJoin('user_address', 'orders.delivery_address', '=', 'user_address.id')
					->Join('drivers', 'drivers.id', '=', 'driver_orders.driver_id')
					->where('drivers.id', '=', $data_all['driverId'])
					->where('orders.id', '=', $data_all['orderId'])
					->whereRaw($query1)
					->whereRaw($query2)
					->orderBy('orders.id', 'desc')
					->get();

					$id = 1;
					$settings = Settings::find($id);
					$orderinfo = DB::table('orders_info')
						->select('orders_info.id','orders_info.item_unit')
						->where('orders_info.order_id', '=', $data_all['orderId'])
						->count();
				//print_r($order_list);exit;
				$orders = array();
				if (count($order_list) > 0) {
					$o = 0;
					foreach ($order_list as $ord) {
						$orders['orderId'] = $ord->orders_id;
						$orders['totalamount'] = $ord->total_amount;
						//$orders[$o]['createdDate'] = date("D M j,g:i a", strtotime($ord->created_date));
						$orders['createdDate'] = strtotime($ord->created_date);
						$orders['assignedTime'] = strtotime($ord->assigned_time);
						$orders['pickupLocation'] = $ord->outlet_address;
						$orders['outletName'] = $ord->outlet_name;
						$orders['vendorName'] = $ord->vendor_name;
						$orders['dropLocation'] = ($ord->user_address != '') ? $ord->user_address : '';
						$orders['driverFirstName'] = ($ord->driver_first_name != '') ? $ord->driver_first_name : '';
						$orders['driverLastName'] = ($ord->driver_last_name != '') ? $ord->driver_last_name : '';
						$orders['userFirstName'] = ($ord->user_first_name != '') ? $ord->user_first_name : '';
						$orders['userLastName'] = ($ord->user_last_name != '') ? $ord->user_last_name : '';
						$orders['userMobile'] = ($ord->user_mobile != '') ? $ord->user_mobile : '';
						$orders['userLatitude'] = ($ord->user_latitude != '') ? $ord->user_latitude : '';
						$orders['userLongitude'] = ($ord->user_longitude != '') ? $ord->user_longitude : '';
						$orders['outletLatitude'] = ($ord->outlet_latitude != '') ? $ord->outlet_latitude : '';
						$orders['outletLongtitude'] = ($ord->outlet_longtitude != '') ? $ord->outlet_longtitude : '';

						/*	$orders[$o]['vendorLattitude'] = ($ord->latitude != '') ? $ord->latitude : '';
							$orders[$o]['vendorLongtitude'] = ($ord->longitude != '') ? $ord->longitude : '';*/
						$orders['orderStatus'] = ($ord->order_status != '') ? $ord->order_status : '';
						$orders['userId'] = ($ord->customer_id != '') ? $ord->customer_id : '';

						$user_image = $featured_image = URL::asset('assets/front/' . Session::get('general')->theme . '/images/no_image.png?' . time());
						if (file_exists(base_path() . '/public/assets/admin/base/images/users/' . $ord->user_image) && $ord->user_image != '') {
							$user_image = url('/assets/admin/base/images/users/' . $ord->user_image . '?' . time());
						}
						if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/list/' . $ord->featured_image) && $ord->featured_image != '') {
							$featured_image = url('/assets/admin/base/images/vendors/list/' . $ord->featured_image . '?' . time());
						}
						$orders['userImage'] = $user_image;
						$orders['featuredImage'] = $featured_image;
						$orders['adminPhone'] = $settings->telephone;
						$orders['itemCount'] = $orderinfo;
						//print_r($orders);exit;

					}
				}
				/*$order_status = DB::table('order_status')
					->select('*')
					->orderBy('order_status.id', 'desc')
					->get();*/
				$result =
				array("status" => 1, "httpCode" => 200, "message" => trans("messages.Orders list"), 'detail' => $orders);
			} catch (JWTException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials"));
			} catch (TokenExpiredException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials"));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	/* Orders list */
	public function mdriver_orders(Request $data) {
		$data_all = $data->all();
		$rules = [
			'driverId' => ['required'],
			'lang' => ['required'],
			'token' => ['required'],
			'limit' => ['required'],
			'start' => ['required'],
		];

		$errors = $result = array();

		$validator = app('validator')->make($data->all(), $rules);
		if ($validator->fails()) {
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("status" => 2, "httpCode" => 400, "Message" => $errors, "Error" => trans("messages.Error List"));
		} else {
			try {

				//$check_auth = JWTAuth::toUser($data_all['token']);
				$language_id = $data_all["lang"];
				$query1 = 'outlet_infos.language_id = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
				$order_list = DB::table('orders')
					->select('orders.id as orders_id',
						'vendors.featured_image',
						'users.image as user_image',
						'orders.total_amount',
						'orders.created_date',
						'orders.order_key_formated',
						'outlet_infos.contact_address as outlet_address',
						'user_address.address as user_address',
						'orders.order_status',
						'orders.customer_id',
						'orders.driver_ids as driver_ids',
						'orders.assigned_time as assigned_time',
						'drivers.id as driverId')
					->Join('vendors', 'vendors.id', '=', 'orders.vendor_id')
					->Join('outlets', 'outlets.id', '=', 'orders.outlet_id')
					->Join('users', 'users.id', '=', 'orders.customer_id')
					->join('outlet_infos', 'outlets.id', '=', 'outlet_infos.id')
					->leftJoin('user_address', 'orders.delivery_address', '=', 'user_address.id')
					->Join('driver_orders', 'driver_orders.order_id', '=', 'orders.id')
					->Join('drivers', 'drivers.id', '=', 'driver_orders.driver_id')
					//->Join('drivers', 'drivers.id', '=', 'orders.driver_ids')
					->distinct()

					->whereRaw($query1)
					->where('drivers.id', '=', (int) $data_all['driverId'])
					->orderBy('orders.id', 'desc')
					->limit($data_all['limit'])
					->offset($data_all['start'])
					->get();

					
				//print_r($order_list);exit;
				$orders = array();
				$average_rating=0;

				if (count($order_list) > 0) {
					 /**driver review average calculating and updated here **/ 
			        $reviews_average=DB::table('driver_reviews')
			                ->selectRaw('SUM(ratings) as total_rating,count(driver_reviews.driver_id) as tcount')
			                ->where("driver_reviews.driver_id","=", (int)$data_all['driverId'])
			                ->get();
			       	//echo"<pre>";print_r($reviews_average);exit;
			        $average_rating =0;
			        if(count($reviews_average)){
			            $total_rating = $reviews_average[0]->total_rating;
			             if($total_rating)
			            {
				            $average_rating=$total_rating/$reviews_average[0]->tcount;
				            $average_rating    = round($average_rating);
				        }
			            
			        }
					$o = 0;
					foreach ($order_list as $ord) {
						$orders[$o]['orderId'] = $ord->orders_id;
						$orders[$o]['totalamount'] = $ord->total_amount;
						//$orders[$o]['createddate'] = date("D M j,g:i a", strtotime($ord->created_date));
						$orders[$o]['createddate'] = strtotime($ord->created_date);
						$orders[$o]['assignedTime'] = strtotime($ord->assigned_time);
						$orders[$o]['pickupLocation'] = $ord->outlet_address;
						$orders[$o]['dropLocation'] = ($ord->user_address != '') ? $ord->user_address : '';
						$orders[$o]['orderStatus'] = $ord->order_status;
						$orders[$o]['userId'] = $ord->customer_id;
						$user_image = $featured_image = URL::asset('assets/front/' . Session::get('general')->theme . '/images/no_image.png?' . time());
						$user_image = $featured_image = URL::asset('assets/front/' . Session::get('general')->theme . '/images/no_image.png?' . time());
						if (file_exists(base_path() . '/public/assets/admin/base/images/users/' . $ord->user_image) && $ord->user_image != '') {
							$user_image = url('/assets/admin/base/images/users/' . $ord->user_image . '?' . time());
						}
						if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/list/' . $ord->featured_image) && $ord->featured_image != '') {
							$featured_image = url('/assets/admin/base/images/vendors/list/' . $ord->featured_image . '?' . time());
						}
						$orders[$o]['userImage'] = $user_image;
						$orders[$o]['featuredImage'] = $featured_image;
						$o++;
					}
				}
				$order_status = DB::table('order_status')
					->select('*')
					->orderBy('order_status.id', 'desc')
					->get();
				$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.Orders list"), 'detail' => $orders, 'driverRating' => $average_rating);
			} catch (JWTException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials"));
			} catch (TokenExpiredException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials"));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	/* To check the driver availability and update the driver location */
	public function mdriver_update_location(Request $data) {
		$data_all = $data->all();
		$rules = [
			'language' => ['required'],
			'driverId' => ['required'],
			'token' => ['required'],
			'deviceId' => ['required'],
			'deviceToken' => ['required'],
			'latitude' => ['required'],
			'longitude' => ['required'],
			'loginType' => ['required'],
		];
		$errors = $result = array();
		$validator = app('validator')->make($data_all, $rules);

		if ($validator->fails()) {
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("status" => 3, "httpCode" => 400, "Message" => $errors, "Error" => trans("messages.Error List"));
		} else {
			try {
				//$check_auth = JWTAuth::toUser($data_all['token']);
				$language_id = $data_all["language"];

				$check_device = check_driver_device($data_all['driverId'], $data_all['loginType'], $data_all['deviceToken']);
				if($check_device == 2)
				{
					$result = array("status" => 410, "message" => trans("messages.You have already logged in another device."));
					return json_encode($result, JSON_UNESCAPED_UNICODE);	
				}
				$drivers = Drivers::find($data_all["driverId"]);
				if (count($drivers) == 1) {
					if ($drivers->driver_status != 3) {

						$android_device_id = isset($data_all['deviceId']) ? $data_all['deviceId'] : '';
						$android_device_token = isset($data_all['deviceToken']) ? $data_all['deviceToken'] : '';

						$ios_device_id = isset($data_all['deviceId']) ? $data_all['deviceId'] : '';
						$ios_device_token = isset($data_all['deviceToken']) ? $data_all['deviceToken'] : '';
						$deviceType = isset($data_all['deviceType']) ? $data_all['deviceType'] : 2;

						/*TO UPDATE THE DEVICE TOKEN*/

						if ($deviceType == 2 && $drivers->android_device_token != $android_device_id) {
							$res = DB::table('drivers')->where('id', $drivers->id)->update(['android_device_token' => $android_device_token, 'android_device_id' => $android_device_id]);
						}
						if ($deviceType == 3 && $drivers->ios_device_token != $ios_device_token) {
							$res = DB::table('drivers')->where('id', $drivers->id)->update(['ios_device_token' => $ios_device_token, 'ios_device_id' => $ios_device_id]);
						}
						/*TO UPDATE THE DEVICE TOKEN*/

						$driver_settings = Driver_settings::find(1);
						$driver_track_location = DB::table('driver_track_location')
							->where('driver_id', $data_all["driverId"])
							->get();
						//location saving
						if (count($driver_track_location) > 0) {
							$d_t_location = DB::table('driver_track_location')
								->where('driver_id', $data_all["driverId"])
								->update(['created_date' => date("Y-m-d H:i:s"), 'latitude' => $data_all['latitude'], 'longitude' => $data_all['longitude'], 'android_device_id' => $android_device_id, 'android_device_token' => $android_device_token, 'ios_device_id' => $ios_device_id, 'ios_device_token' => $ios_device_token, 'login_type' => $data_all['loginType']]);

						} else {

							$d_t_location = new Driver_track_location;
							$d_t_location->driver_id = $data_all['driverId'];
							$d_t_location->today_date = date('Y-m-d');
							$d_t_location->latitude = $data_all['latitude'];
							$d_t_location->longitude = $data_all['longitude'];
							$d_t_location->login_type = $data_all['loginType'];
							$d_t_location->created_date = date("Y-m-d H:i:s");
							if (isset($data_all['loginType']) && !empty($data_all['loginType'])) {
								if ($data_all['loginType'] == 2) {
									$d_t_location->android_device_id = isset($data_all['deviceId']) ? $data_all['deviceId'] : '';
									$d_t_location->android_device_token = isset($data_all['deviceToken']) ? $data_all['deviceToken'] : '';
								}
								if ($data_all['loginType'] == 3) {
									$d_t_location->ios_device_id = isset($data_all['deviceId']) ? $data_all['deviceId'] : '';
									$d_t_location->ios_device_token = isset($data_all['deviceToken']) ? $data_all['deviceToken'] : '';
								}
							}
							$d_t_location->save();
						}
						//$drivers->driver_status =2;
						//print_r($drivers->driver_status);exit();
						if ($drivers->driver_status == 1 || $drivers->driver_status == 4) {
							// driver status 1 ->online 4-> order assigned by admin
							$language = getCurrentLang();

							$date1 = date("Y-m-d H:i:s");
							$time1 = strtotime($date1);
							$time = $time1 - (1 * 120);

							$currentTime2 = date("Y-m-d H:i:s", $time);

							$query1 = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language . ' and outlets.id = outlet_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
							$query2 = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = ' . $language . ' and orders.vendor_id = vendors_infos.id) > 0 THEN ' . $language . ' ELSE 1 END)';
							//$time_conditions = "orders.assigned_time > NOW() - INTERVAL '1 minutes' ";
							$orders = DB::table('orders')
								->select('orders.id as order_id',
									'orders.order_key_formated as order_key_formated',
									'orders.total_amount as total_amount',
									'orders.created_date as created_date',
									'orders.modified_date as modified_date',
									'orders.assigned_time as assigned_time',
									'orders.delivery_date as delivery_date',
									'orders.order_status as order_status',
									'orders.vendor_id as vendor_id',
									'users.first_name as first_name',
									'users.last_name as last_name',
									'users.name as user_name',
									'order_status.name as status_name',
									'order_status.color_code as color_code',
									'user_address.address as user_address',
									'outlet_infos.outlet_name as outlet_name',
									'vendors_infos.vendor_name as vendor_name',
									'vendors.contact_address as contact_address',
									'outlets.latitude as outlet_latitude',
									'outlets.longitude as outlet_longitude',
									'outlets.id as outlet_id'
								)
								->leftJoin('users', 'users.id', '=', 'orders.customer_id')
								->leftJoin('order_status', 'order_status.id', '=', 'orders.order_status')
								->leftJoin('user_address', 'orders.delivery_address', '=', 'user_address.id')
								->join('outlets', 'outlets.id', '=', 'orders.outlet_id')

								->join('outlet_infos', 'outlet_infos.id', '=', 'outlets.id')
								->join('vendors_infos', 'vendors_infos.id', '=', 'orders.vendor_id')
								->join('vendors', 'vendors.id', '=', 'orders.vendor_id')

								->where('orders.driver_ids', '=', (int) $data_all['driverId'])
								->where('orders.order_status', '=', 18)
								->where('orders.assigned_time', '>=', $currentTime2)
								->whereRaw($query1)
								->whereRaw($query2)
								->orderBy('orders.id', 'desc')
								->get();
							//print_r($orders);exit();
				 			
			            	$data = array();
							if (count($orders) > 0) {
								$datetime1 = new DateTime();
					            $datetime2 = new DateTime($orders[0]->assigned_time);
					            $diff = $datetime1->getTimestamp()-$datetime2->getTimestamp() ;
					           
					            //print_r($diff);exit;
								foreach ($orders as $or) {

									$driverinfo =driverorderInfo($or->order_id,$data_all['driverId']);
						            if(count($driverinfo))
						            {

										$orders = DB::table('orders')
											->select('driver_ids')
											->where('orders.id', $or->order_id)
											->first();

										$driver_ids = $orders->driver_ids;
										$vendor_address = isset($or->contact_address) ? $or->contact_address : '';
										$vendor_name = isset($or->vendor_name) ? $or->vendor_name : '';
										$vendor_log = url('/assets/admin/base/images/default_avatar_male.jpg?' . time());
										if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $or->vendor_id)) {
											$vendor_log = URL::to("assets/admin/base/images/vendors/logos/" . $or->vendor_id . '?' . time());
										}

										//$order_id = $orders->order_id;
										/*$new_orders = Order::find($data_all['order_id']);
											$new_orders->driver_ids = $driver_ids . $data_all['driver'] . ',';
											$assigned_time = strtotime("+ " . $driver_settings->order_accept_time . " minutes", strtotime(date('Y-m-d H:i:s')));
											$update_assign_time = date("Y-m-d H:i:s", $assigned_time);
											$new_orders->assigned_time = $update_assign_time;
											$new_orders->save();*/

										$order_title = '' . ucfirst($or->outlet_name) . ' , A new order delivery has been sent';
										$order_title1 = '' . ucfirst($or->outlet_name) . ' , تم ارسال طلب توصيل جديد';
										/*$order_logs = new Autoassign_order_logs;
											$order_logs->driver_id = $data_all['driverId'];
											$order_logs->order_id = $or->order_id;
											$order_logs->driver_response = 0;
											$order_logs->driver_token = $data_all['deviceToken'];
											$order_logs->order_delivery_status = 0;
											$order_logs->assign_date = date("Y-m-d H:i:s");
											$order_logs->created_date = date("Y-m-d H:i:s");
											$order_logs->order_subject = $order_title;
											// $order_logs->order_subject_arabic = $order_title1;
											$order_logs->order_message = $order_title;
											$order_logs->save();*/

										$affected = DB::update('update drivers set driver_status = 2 where id = ?', array($data_all['driverId']));
										$data = array
											(
											'orderId' => $or->order_id,
											'driver_id' => $driver_ids,
											'vendoraddress' => $vendor_address,
											'vendorName' => $vendor_name,
											'vendorLog' => $vendor_log,
											'message' => $order_title,

										);
										$result = array("status" => 2, "httpCode" => 200, "Message" => trans("messages.Driver location has been updated successfully"), "detail" => $data,"driverStatus"=>2);


									}else
									{
										$result = array("status" => 1, "message" => trans("messages.Driver location has been updated successfully"),"driverStatus"=>1);

									}
									//print_r($result);exit;
								}



								return json_encode($result, JSON_UNESCAPED_UNICODE);	
										
							}

				            

						}else if($drivers->driver_status == 2 )	{				

						
							$date1 = date("Y-m-d H:i:s");
							$time1 = strtotime($date1);
							$time = $time1 - (1 * 60);
							$currentTime2 = date("Y-m-d H:i:s", $time);
							$orders = DB::table('driver_order_info')
								->select('id','assigned_time','order_id','driver_id')
								->where('assigned_time', '<', $currentTime2)
								->where('driver_id', '=',$data_all['driverId'])
								->get();
							if(count($orders))
							{
								foreach ($orders as $key => $value) {
									$status = [1,18,36,34];
									$orders = DB::table('orders')
										->select('order_status')
										->where('id', $value->order_id)
										->whereIn('order_status', $status)
										->count();
									if($orders !=0){
										$affected=DB::table('drivers')
											->where('drivers.id','=',$value->driver_id)
											->update(['driver_status'=>1]);
						            	$affected=DB::table('orders')
											->where('orders.id','=',$value->order_id)
											->update(['driver_ids'=>NULL]);
									}
								}
								$result = array("status" => 1, "message" => trans("messages.Driver location has been updated successfully"),"driverStatus"=>1);
								return json_encode($result, JSON_UNESCAPED_UNICODE);
							}

						}

						if($data_all["orderId"] != 0 )
						{
							$orders_det = DB::table('orders')
								->select('order_status','assigned_time')
								->where('orders.id', $data_all["orderId"])
								->where('orders.driver_ids', $data_all["driverId"])
								->first();
					
				            if(count($orders_det)){
								if($orders_det->order_status == 11)
								{
									$result = array("status" => 4, "httpCode" => 400, "Message" => trans("messages.the current order is cancelled by admin"),"detail"=>array('orderId'=>$data_all["orderId"]));
										return json_encode($result, JSON_UNESCAPED_UNICODE);

								}/*else
								{

									if ($diff > 60) {
										$affected=DB::table('drivers')
											->where('drivers.id','=',$data_all["driverId"])
											->update(['driver_status'=>3]);
						            	$affected=DB::table('orders')
											->where('orders.id','=',$data_all["orderId"])
											->update(['driver_ids'=>NULL]);

						            	$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.Driver location has been updated successfully"));

										return json_encode($result, JSON_UNESCAPED_UNICODE);

					            	}
								}*/
							}
						}
						$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.Driver location has been updated successfully"),"driverStatus"=>1);
					} else {
						$result = array("status" => 3, "httpCode" => 400, "Message" => trans("messages.Driver is an offline"));
					}
				} else {
					$result = array("status" => 3, "httpCode" => 400, "Message" => trans("messages.Invalid driver credentials"));
				}
			} catch (JWTException $e) {
				$result = array("status" => 3, "httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials"));
			} catch (TokenExpiredException $e) {
				$result = array("status" => 3, "httpCode" => 400, "Message" => trans("messages.Kindly check the user credentials"));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	/* Changing the Order accept status */
	// public function morder_accept(Request $data) {
	// 	$post_data = $data->all();
	// 	//print_r($post_data);exit;
	// 	$rules = [
	// 		'driverId' => ['required'],
	// 		'orderId' => ['required'],
	// 	];
	// 	$errors = $result = array();
	// 	$validator = app('validator')->make($post_data, $rules);
	// 	if ($validator->fails()) {
	// 		$errors = '';
	// 		$j = 0;
	// 		foreach ($validator->errors()->messages() as $key => $value) {
	// 			$error[] = is_array($value) ? implode(',', $value) : $value;
	// 		}
	// 		$errors = implode(", \n ", $error);
	// 		$result = array("status" => 2,  "message" => trans("messages.Error List"), "detail" => $errors);
	// 	} else {
	// 		try {
	// 			//$check_auth = JWTAuth::toUser($post_data['token']);
	// 			$driverId = $post_data['driverId'];
	// 			$orderId = $post_data['orderId'];
	// 			$comment = isset($post_data['comment']) ? $post_data['comment'] : '';
	// 			$date = date("Y-m-d H:i:s");

	// 			//driver accept order state =>31
	// 			$status_change = DB::update('update orders set order_status = 31 ,driver_ids = ' . $post_data['driverId'] . ' where id = ' . $post_data['orderId'] . '');

	// 			$driverDetail=DB::table('drivers')
	// 							->select('drivers.first_name')
	// 							->where('drivers.id', '=', (int) $driverId)
	// 							->get();
	// 			$driver_name= isset($driverDetail[0]->first_name)?$driverDetail[0]->first_name:'';
	// 			$accept="Accepted by ".$driver_name;


	// 			$affected = DB::update('update orders_log set order_status=?, order_comments = ? where id = (select max(id) from orders_log where order_id = ' . $orderId . ')', array(31, $accept));

	// 						//print_r($affected);exit;


	// 			//driver status changed to busy
	// 			$driver_status_change = DB::update('update drivers set driver_status = 2 where id = ?', array((int) $driverId));

	// 			$affected = DB::update('update orders set request_vendor = 0 where id = ?', array($post_data['orderId'])); //discussed wth prasanth


	// 			//driver order details save
	// 			$driver_orders = new Driver_orders;
	// 			$driver_orders->order_id = $orderId;
	// 			$driver_orders->driver_id = $driverId;
	// 			$driver_orders->assigned_time = date("H:i:s");
	// 			$driver_orders->created_at = date("Y-m-d H:i:s");
	// 			$driver_orders->updated_at = date("Y-m-d H:i:s");
	// 			$driver_orders->save();

	// 			$notify =push_notification($orderId,31,0);
	// 			/*FCM push notification*/

	// 			$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.Order Status updated successfully"));
	// 		} catch (JWTException $e) {
	// 			$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
	// 		} catch (TokenExpiredException $e) {
	// 			$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
	// 		}
	// 	}
	// 	return json_encode($result, JSON_UNESCAPED_UNICODE);
	// }

	public function morder_accept(Request $data) {
		$post_data = $data->all();
		//print_r($post_data);exit;
		$rules = [
			'driverId' => ['required'],
			'orderId' => ['required'],
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
			$result = array("status" => 2,  "message" => trans("messages.Error List"), "detail" => $errors);
		} else {
			try {
				//$check_auth = JWTAuth::toUser($post_data['token']);
				$driverId = $post_data['driverId'];
				$orderId = $post_data['orderId'];
				$comment = isset($post_data['comment']) ? $post_data['comment'] : '';
				$date = date("Y-m-d H:i:s");

				$ordersTable=DB::table('orders')
							 ->select('orders.order_status')
							 ->where('orders.id',$orderId)
							 ->get();
				//print_r($ordersTable[0]->order_status);exit();
				if($ordersTable[0]->order_status !=11){
					//driver accept order state =>31
					$status_change = DB::update('update orders set order_status = 31 ,driver_ids = ' . $post_data['driverId'] . ' where id = ' . $post_data['orderId'] . '');

					$driverDetail=DB::table('drivers')
									->select('drivers.first_name')
									->where('drivers.id', '=', (int) $driverId)
									->get();
					$driver_name= isset($driverDetail[0]->first_name)?$driverDetail[0]->first_name:'';
					$accept="Accepted by ".$driver_name;


					$affected = DB::update('update orders_log set order_status=?, order_comments = ? where id = (select max(id) from orders_log where order_id = ' . $orderId . ')', array(31, $accept));

								//print_r($affected);exit;


					//driver status changed to busy
					$driver_status_change = DB::update('update drivers set driver_status = 2 where id = ?', array((int) $driverId));

					$affected = DB::update('update orders set request_vendor = 0 where id = ?', array($post_data['orderId'])); //discussed wth prasanth


					//driver order details save
					$driver_orders = new Driver_orders;
					$driver_orders->order_id = $orderId;
					$driver_orders->driver_id = $driverId;
					$driver_orders->assigned_time = date("H:i:s");
					$driver_orders->created_at = date("Y-m-d H:i:s");
					$driver_orders->updated_at = date("Y-m-d H:i:s");
					$driver_orders->save();

					$notify =push_notification($orderId,31,0);
					/*FCM push notification*/

					$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.Order Status updated successfully"));
				}else
				{
					$result = array("status" => 4,  "message" => trans("messages.This Order is already Cancelled by Admin"));
				}
			} catch (JWTException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
			} catch (TokenExpiredException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	/* Changing the Order reject status */
	public function morder_reject(Request $data) {
		$post_data = $data->all();
		$rules = [
			'driverId' => ['required'],
			'orderId' => ['required'],
			'reason' => ['required'],
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
			$result = array("status" => 2, "httpCode" => 400, "Error" => trans("messages.Error List"), "Message" => $errors);
		} else {
			try {
				//$check_auth = JWTAuth::toUser($post_data['token']);
				$driverId = $post_data['driverId'];
				$orderId = $post_data['orderId'];

				$order = DB::table('orders')
					->select('drivers.first_name', 'drivers.last_name', 'orders.order_key_formated', 'orders.vendor_id', 'orders.order_status')
					->Join('drivers', 'drivers.id', '=', 'orders.driver_ids')
					->where('orders.id', '=', (int) $orderId)
					->get();
				//	echo"<pre>";print_r($order[0]->order_status);EXIT;
				$list =[31,32,19,12];

				if (!in_array($order[0]->order_status, $list))
				{
					//Reject order  =>order_staus 18 =packed
					$status_change = DB::update('update orders set order_status = 18 ,driver_ids = NULL,assigned_time = NULL where id = ' . $orderId . '');

					//driver status changed to free
					$driver_status_change = DB::update('update drivers set driver_status = 1 where id = ?', array($driverId));

					/*order rejection log*/
					$values = array('order_id' => $orderId,
						'driver_id' => $driverId,
						'reason' => $post_data['reason'],
						'rejected_time' => date('Y-m-d H:i:s'),
					);

					DB::table('order_rejections')->insert($values);

					/*driver rejection mail for admin*/

					$subject = ' Order rejected by the driver  - [' . $order[0]->first_name . '-' . $order[0]->last_name . ']';
					$driver_order_assign_response = 'Order delivery has been declined';
					$template = DB::table('email_templates')
						->select('*')
						->where('template_id', '=', self::DRIVER_ORDER_RESPONSE_TEMPLATE)
						->get();

					if (count($template)) {

						$from = $template[0]->from_email;
						$from_name = $template[0]->from;

						if (!$template[0]->template_id) {
							$template = 'mail_template';
							$from = getAppConfigEmail()->contact_mail;
							$subject = getAppConfig()->site_name . 'Order rejected by the driver  - [' . $order[0]->first_name . '-' . $order[0]->last_name . ']';
							$from_name = "";
						}

						$admin = Users::find(1);
						$admin_mail = $admin->email;
						$vendors = Vendors::find($order[0]->vendor_id);
						$vendor_mail = $vendors->email;
						$content = array("order" => array('name' => $admin->name, 'order_key' => $order[0]->order_key_formated, 'status' => $driver_order_assign_response, "driver_name" => $order[0]->first_name));
						$mail = smtp($from, $from_name, $admin_mail, $subject, $content, $template);
						//print_r($mail);exit;
					}

					/*driver rejection mail for admin*/

					$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.Order Status updated successfully"));
				}else{
					$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.You can't reject this trip... "));
			    }
			} catch (JWTException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
			} catch (TokenExpiredException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	/* Changing the Order dispatched status */
	// public function morder_dispatched(Request $data) {
	// 	$post_data = $data->all();
	// 	$rules = [
	// 		'driverId' => ['required'],
	// 		'orderId' => ['required'],
	// 	];
	// 	$errors = $result = array();
	// 	$validator = app('validator')->make($post_data, $rules);
	// 	if ($validator->fails()) {
	// 		$errors = '';
	// 		$j = 0;
	// 		foreach ($validator->errors()->messages() as $key => $value) {
	// 			$error[] = is_array($value) ? implode(',', $value) : $value;
	// 		}
	// 		$errors = implode(", \n ", $error);
	// 		$result = array("status" => 2, "httpCode" => 400, "Error" => trans("messages.Error List"), "Message" => $errors);
	// 	} else {
	// 		try {
	// 			//$check_auth = JWTAuth::toUser($post_data['token']);
	// 			$id = $post_data['driverId'];
	// 			$order_id = $post_data['orderId'];
	// 			$date = date("Y-m-d H:i:s");
	// 			$comment = isset($post_data['comment']) ? $post_data['comment'] : '';

	// 			$status_change = DB::update('update orders set order_status = 19 where id = ' . $post_data['orderId'] . '');
	// 			//$affected = DB::update('update orders_log set order_status = ?,log_time = ? where id = ?', array(19, $date, $post_data['orderId']));

	// 			$affected = DB::update('update orders_log set order_status=?, order_comments = ?, log_time = ? where id = (select max(id) from orders_log where order_id = ' . $order_id . ')', array(19, $comment, $date));

	// 			$notify =push_notification($order_id,19,0);

	// 			$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.Order Status updated successfully"));
	// 		} catch (JWTException $e) {
	// 			$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
	// 		} catch (TokenExpiredException $e) {
	// 			$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
	// 		}
	// 	}
	// 	return json_encode($result, JSON_UNESCAPED_UNICODE);
	// }

	public function morder_dispatched(Request $data) {
		$post_data = $data->all();
		$rules = [
			'driverId' => ['required'],
			'orderId' => ['required'],
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
			$result = array("status" => 2, "httpCode" => 400, "Error" => trans("messages.Error List"), "Message" => $errors);
		} else {
			try {
				//$check_auth = JWTAuth::toUser($post_data['token']);
				$id = $post_data['driverId'];
				$order_id = $post_data['orderId'];
				$date = date("Y-m-d H:i:s");
				$ordersTable=DB::table('orders')
							 ->select('orders.order_status')
							 ->where('orders.id',$order_id)
							 ->get();
				//print_r($ordersTable[0]->order_status);exit();
				if($ordersTable[0]->order_status !=11){
					$comment = isset($post_data['comment']) ? $post_data['comment'] : '';

					$status_change = DB::update('update orders set order_status = 19 where id = ' . $post_data['orderId'] . '');
					//$affected = DB::update('update orders_log set order_status = ?,log_time = ? where id = ?', array(19, $date, $post_data['orderId']));

					$affected = DB::update('update orders_log set order_status=?, order_comments = ?, log_time = ? where id = (select max(id) from orders_log where order_id = ' . $order_id . ')', array(19, $comment, $date));

					$notify =push_notification($order_id,19,0);

					$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.Order Status updated successfully"));
				}else
				{
					$result = array("status" => 4,  "message" => trans("messages.This Order is already Cancelled by Admin"));
				}
			} catch (JWTException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
			} catch (TokenExpiredException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	/* Changing the Order dispatched status */
	// public function morder_arrived(Request $data) {
	// 	$post_data = $data->all();
	// 	$rules = [
	// 		'driverId' => ['required'],
	// 		'orderId' => ['required'],
	// 	];
	// 	$errors = $result = array();
	// 	$validator = app('validator')->make($post_data, $rules);
	// 	if ($validator->fails()) {
	// 		$errors = '';
	// 		$j = 0;
	// 		foreach ($validator->errors()->messages() as $key => $value) {
	// 			$error[] = is_array($value) ? implode(',', $value) : $value;
	// 		}
	// 		$errors = implode(", \n ", $error);
	// 		$result = array("status" => 2, "httpCode" => 400, "Error" => trans("messages.Error List"), "Message" => $errors);
	// 	} else {
	// 		try {
	// 			//$check_auth = JWTAuth::toUser($post_data['token']);
	// 			$id = $post_data['driverId'];
	// 			$order_id = $post_data['orderId'];
	// 			$date = date("Y-m-d H:i:s");

	// 			$status_change = DB::update('update orders set order_status = 32 where id = ' . $post_data['orderId'] . '');
	// 			//$affected = DB::update('update orders_log set order_status = ?,log_time = ? where id = ?', array(19, $date, $post_data['orderId']));
	// 			$affected = DB::update('update orders_log set order_status=?, log_time = ? where id = (select max(id) from orders_log where order_id = ' . $order_id . ')', array(32, $date));

	// 			$notify =push_notification($order_id,32,0);
	// 			$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.Order Status updated successfully"));
	// 		} catch (JWTException $e) {
	// 			$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
	// 		} catch (TokenExpiredException $e) {
	// 			$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
	// 		}
	// 	}
	// 	return json_encode($result, JSON_UNESCAPED_UNICODE);
	// }

	public function morder_arrived(Request $data) {
		$post_data = $data->all();
		$rules = [
			'driverId' => ['required'],
			'orderId' => ['required'],
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
			$result = array("status" => 2, "httpCode" => 400, "Error" => trans("messages.Error List"), "Message" => $errors);
		} else {
			try {
				//$check_auth = JWTAuth::toUser($post_data['token']);
				$id = $post_data['driverId'];
				$order_id = $post_data['orderId'];
				$date = date("Y-m-d H:i:s");
				$ordersTable=DB::table('orders')
							 ->select('orders.order_status')
							 ->where('orders.id',$order_id)
							 ->get();
				//print_r($ordersTable[0]->order_status);exit();
				if($ordersTable[0]->order_status !=11){
					$status_change = DB::update('update orders set order_status = 32 where id = ' . $post_data['orderId'] . '');
					//$affected = DB::update('update orders_log set order_status = ?,log_time = ? where id = ?', array(19, $date, $post_data['orderId']));
					$affected = DB::update('update orders_log set order_status=?, log_time = ? where id = (select max(id) from orders_log where order_id = ' . $order_id . ')', array(32, $date));

					$notify =push_notification($order_id,32,0);
					$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.Order Status updated successfully"));
				}else
				{
					$result = array("status" => 4,  "message" => trans("messages.This Order is already Cancelled by Admin"));
				}
			} catch (JWTException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
			} catch (TokenExpiredException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	/* Changing the Order dispatched status */
/*	public function morder_delivered_copy(Request $data) {
		$post_data = $data->all();
		$rules = [
			'driverId' => ['required'],
			'orderId' => ['required'],
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
			$result = array("status" => 2, "httpCode" => 400, "Error" => trans("messages.Error List"), "Message" => $errors);
		} else {
			try {
				//$check_auth = JWTAuth::toUser($post_data['token']);
				$driverId = $post_data['driverId'];
				$order_id = $post_data['orderId'];
				$date = date("Y-m-d H:i:s");
				//$delivery = commonDelivery($post_data);
				//exit("ccc");
				$comment = isset($post_data['comment']) ? $post_data['comment'] : '';

				$status_change = DB::update('update orders set order_status = 12 where id = ' . $order_id . '');

				//$affected = DB::update('update orders_log set order_status = ?,log_time = ? where id = ?', array(12, $date, $order_id));

				$affected = DB::update('update orders_log set order_status=?, order_comments = ?, log_time = ? where id = (select max(id) from orders_log where order_id = ' . $order_id . ')', array(12, $comment, $date));

				$affected = DB::update('update drivers set driver_status =1  where id = ?', array($driverId));

				//$affected = DB::update('update autoassign_order_logs set order_delivery_status = ? where driver_id = ? and order_id = ?', array(1, $driverId, $order_id));

				$notify = DB::table('orders')
					->select('orders.assigned_time', 'users.android_device_token', 'users.ios_device_token', 'users.login_type', 'drivers.first_name', 'vendors_infos.vendor_name', 'orders.total_amount','orders.customer_id','orders.order_key_formated','orders.vendor_id','orders.outlet_id', 'order_status.name as status_name','orders.salesperson_id')
					->leftJoin('users', 'users.id', '=', 'orders.customer_id')
					->leftJoin('order_status', 'orders.order_status', '=', 'order_status.id')

					->leftJoin('vendors_infos', 'vendors_infos.id', '=', 'orders.vendor_id')

					->leftJoin('drivers', 'drivers.id', '=', 'orders.driver_ids')
					->where('orders.id', '=', (int) $order_id)
					->get();


				$referral =getreferral();
				$customer_id = isset($notify[0]->customer_id)?$notify[0]->customer_id:0;

				$users_details=DB::table('customer_referral')
                    ->select('*')
                    ->where('customer_referral.customer_id',$customer_id)
                    ->where('customer_referral.referal_amount_used', '!=', '1')
                    ->get();
                  //  print_r($users_details);exit;

               	if($users_details && $users_details[0]->referal_amount_used !=1){

	                $count_order=DB::table('orders')
	                    ->select('*')
	                    ->where('orders.customer_id',$customer_id)
	                    ->count();
	                //print_r($count_order);exit;
	                $wallet_details=DB::table('users')
	                    ->select('wallet_amount')
	                    ->where('users.id',$users_details[0]->referred_by)
	                    ->get();
	                $wallet_amount = isset($wallet_details[0]->wallet_amount)?$wallet_details[0]->wallet_amount:0;
	                if($count_order == $referral[0]->order_to_complete)
	                {
	                	$wallet_amount = $wallet_amount + $referral[0]->referred_amount;
	                	//print_r($referral);exit; 
	                	$affected = DB::update('update users set wallet_amount =?  where id = ?', array($wallet_amount,$users_details[0]->referred_by));
						$affected = DB::update('update customer_referral set referal_amount_used =1  where id = ?', array($users_details[0]->id));

	                }

					
				}
				//print_r($notify);exit;

				if (count($notify) > 0 && $notify[0]->login_type != 1 ) {
					$notifys = $notify[0];
					$order_title = 'your order is delivered';

					if($notifys->login_type == 2)// android device
					{
						$token = $notifys->android_device_token;
					}else if($notifys->login_type == 3)
					{
						$token = $notifys->ios_device_token;
					}
					$token =isset($token)?$token:'';
					$data = array
						(
						'id' => $order_id,
						'driverId' => $driverId,
						'orderId' => $order_id,
						'orderStatus' => 12,
						'type' => 2,
						'title' => $order_title,
						'message' => $order_title,
						'totalamount' => isset($notifys->total_amount) ? $notifys->total_amount : 0,
						'vendorName' => isset($notifys->vendor_name) ? $notifys->vendor_name : '',
						'request_type' => 1,
						"order_assigned_time" => isset($notifys->assigned_time) ? $notifys->assigned_time : '',
						'notification_dialog' => "1",
					);

					$fields = array
						(
						'registration_ids' => array($token),
						//'data' => $data,
						'notification' => array('title' => $order_title, 'body' =>  $data ,'sound'=>'Default','image'=>'Notification Image')
					);

					/*	$headers = array
						(
						'Authorization: key=AAAAI_fAV4w:APA91bFSR1TLAn1Vh134nzXLznsUVYiGnR4KiUYdAa3u0OccC5S-DyDdQRdnR0XugSRArsJGXC8AHE342eNhBbnK8np10KuyuWwiJxtndV75O4DyT3QCGXKFu_fwUTNPdB51Cno6Rewc',
						'Content-Type: application/json',
					);/
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

				/*$order_list = DB::table('orders')
					->select('orders.id as orders_id', 'orders.customer_id', 'orders.vendor_id', 'orders.outlet_id', 'orders.order_key_formated', 'orders.order_status', 'order_status.name as status_name')
					->leftJoin('order_status', 'orders.order_status', '=', 'order_status.id')
					->where('orders.id', '=', $order_id)
					->orderBy('orders.id', 'desc')
					->first();/



				$notify =$notify[0];
				$users = Users::find($notify->customer_id);


				//$orders = Orders::find($order_id);

				$subject = 'Your Order with ' . getAppConfig()->site_name . ' [' . $notify->order_key_formated . '] has been successfully Delivered!';
				$values = array('order_id' => $order_id,
					'customer_id' => $notify->customer_id,
					'vendor_id' => $notify->vendor_id,
					'outlet_id' => $notify->outlet_id,
					'message' => $subject,
					'read_status' => 0,
					'created_date' => date('Y-m-d H:i:s'));
				DB::table('notifications')->insert($values);



				/*Ram(20-08-19):/


			 	DB::insert('insert into outlet_reviews(customer_id,order_id,vendor_id,outlet_id
						) values(?,?,?,?)', [$notify->customer_id, $order_id,$notify->vendor_id,$notify->outlet_id,]);


				DB::insert('insert into driver_reviews(customer_id,order_id,vendor_id,outlet_id,driver_id
						) values(?,?,?,?,?)', [$notify->customer_id, $order_id,$notify->vendor_id,$notify->outlet_id,$driverId]);
						

				DB::table('salesperson')
                            ->where('salesperson.id', $notify->salesperson_id)
                           
                            ->update(['status' =>'F']);

               


                DB::table('orders')
                            ->where('id', $order_id)
                           
                            ->update(['delivery_date' =>date("Y-m-d H:i:s")]);

				/*delivery mail for user/


				$to = $users->email;

				//print_r($to);exit();
				$template = DB::table('email_templates')->select('*')->where('template_id', '=', self::ORDER_STATUS_UPDATE_USER)->get();
				if (count($template)) {
					$from = $template[0]->from_email;
					$from_name = $template[0]->from;
					if (!$template[0]->template_id) {
						$template = 'mail_template';
						$from = getAppConfigEmail()->contact_mail;
					}
					$subject = 'Your Order with ' . getAppConfig()->site_name . ' [' . $notify->order_key_formated . '] has been successfully Delivered!';
					$orderId = encrypt($order_id);
					$reviwe_id = base64_encode('123abc');
					$orders_link = '<a href="' . URL::to("order-info/" . $orderId) . '" title="' . trans("messages.View") . '">' . trans("messages.View") . '</a>';
					$review_link = '<a href="' . URL::to("order-info/" . $orderId . '?r=' . $reviwe_id) . '" title="' . trans("messages.View") . '">' . trans("messages.View") . '</a>';
					$content = array('name' => "" . $users->name, 'order_key' => "" . $notify->order_key_formated, 'status_name' => "" . $notify->status_name, 'orders_link' => "" . $orders_link, "review_link" => $review_link);

					$attachment = "";
					$email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
				}

				/*delivery mail for user end*/

				/*delivery  confirmation mail for admin/

				$template = DB::table('email_templates')
					->select('*')
					->where('template_id', '=', self::DRIVER_ORDER__DELIVERED_RESPONSE_ADMIN_TEMPLATE)
					->get();
				if (count($template)) {
					$from = $template[0]->from_email;
					$from_name = $template[0]->from;
					//$subject = $template[0]->subject;
					$drivers = Drivers::find($driverId);
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
					$content = array('name' => "" . $admin->name, 'order_key' => "" . $notify->order_key_formated, 'status_name' => "" . $notify->status_name, 'driver_name' => "" . $drivers->first_name);
					$mail = smtp($from, $from_name, $admin_mail, $adminsubject, $content, $template);
				}
				/*delivery  confirmation mail for admin/

				$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.Order Status updated successfully"));
			} catch (JWTException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
			} catch (TokenExpiredException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}
*/

	public function morder_delivered(Request $data) {
		$post_data = $data->all();
		$rules = [
			'driverId' => ['required'],
			'orderId' => ['required'],
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
			$result = array("status" => 2, "message" => trans("messages.Error List"), "detail" => $errors);
		} else {
			try {
				//$check_auth = JWTAuth::toUser($post_data['token']);
				$driverId = $data['driverId'];
				$order_id = $data['orderId'];
				$date = date("Y-m-d H:i:s");
				$comment = isset($post_data['comment']) ? $post_data['comment'] : '';
				$delivery = commonDelivery($post_data); //common fun for delivery
				$notify = push_notification($order_id,12,0);//common fun for push
				$customer_id = isset($notify->customerId)?$notify->customerId:0;
				$users = Users::find($customer_id);
				/*delivery mail for user*/
				/*	$to = $users->email;
					$template = DB::table('email_templates')->select('*')->where('template_id', '=', self::ORDER_STATUS_UPDATE_USER)->get();
					if (count($template)) {
						$from = $template[0]->from_email;
						$from_name = $template[0]->from;
						if (!$template[0]->template_id) {
							$template = 'mail_template';
							$from = getAppConfigEmail()->contact_mail;
						}
						$subject = 'Your Order with ' . getAppConfig()->site_name . ' [' . $notify->order_key_formated . '] has been successfully Delivered!';
						$orderId = encrypt($order_id);
						$reviwe_id = base64_encode('123abc');
						$orders_link = '<a href="' . URL::to("order-info/" . $orderId) . '" title="' . trans("messages.View") . '">' . trans("messages.View") . '</a>';
						$review_link = '<a href="' . URL::to("order-info/" . $orderId . '?r=' . $reviwe_id) . '" title="' . trans("messages.View") . '">' . trans("messages.View") . '</a>';
						$content = array('name' => "" . $users->name, 'order_key' => "" . $notify->order_key_formated, 'status_name' => "" . $notify->status_name, 'orders_link' => "" . $orders_link, "review_link" => $review_link);

						$attachment = "";
						$email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
					}
*/
				/*delivery mail for user end*/

				/*delivery  confirmation mail for admin*/
/*
					$template = DB::table('email_templates')
						->select('*')
						->where('template_id', '=', self::DRIVER_ORDER__DELIVERED_RESPONSE_ADMIN_TEMPLATE)
						->get();
					if (count($template)) {
						$from = $template[0]->from_email;
						$from_name = $template[0]->from;
						$drivers = Drivers::find($driverId);
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
						$content = array('name' => "" . $admin->name, 'order_key' => "" . $notify->order_key_formated, 'status_name' => "" . $notify->status_name, 'driver_name' => "" . $drivers->first_name);
						$mail = smtp($from, $from_name, $admin_mail, $adminsubject, $content, $template);
					}*/

				/*delivery  confirmation mail for admin*/

				$result = array("status" => 1, "message" => trans("messages.Order Status updated successfully"));
			} catch (JWTException $e) {
				$result = array("status" => 2, "message" => trans("messages.Something went wrong"));
			} catch (TokenExpiredException $e) {
				$result = array("status" => 2, "message" => trans("messages.Something went wrong"));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	public function mdriver_order_detail(Request $data) {

		$data_all = $data->all();
		$rules = [
			'driverId' => ['required'],
			'orderId' => ['required'],
			'lang' => ['required'],
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

				//$check_auth = JWTAuth::toUser($data_all['token']);
				$language_id = $data_all["lang"];
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
					->where('drivers.id', '=', $data_all['driverId'])
					->where('orders.id', '=', $data_all['orderId'])
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
                          where ' . $query . ' AND ' . $query1 . ' AND o.id = ? ORDER BY oi.id', array($data_all['orderId']));
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
								->where('order_id', '=', $data_all['orderId'])
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

	public function mdriver_shift_status(Request $data) {
		$post_data = $data->all();
		$rules = [
			'driverId' => ['required'],
			'shiftStatus' => ['required'],
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
			$result = array("status" => 2, "httpCode" => 400, "Error" => trans("messages.Error List"), "Message" => $errors);
		} else {
			try {
				//$check_auth = JWTAuth::toUser($post_data['token']);
				$driverId = $post_data['driverId'];
				$shiftStatus = $post_data['shiftStatus'];
				$logintype = isset($post_data['loginType'])?$post_data['loginType']:'';
				$deviceToken =isset($post_data['deviceToken'])?$post_data['deviceToken']:'';

				$check_device = check_driver_device($driverId, $logintype, $deviceToken);
				if($check_device == 2)
				{
					$result = array("status" => 410, "message" => trans("messages.You have already logged in another device."));
					return json_encode($result, JSON_UNESCAPED_UNICODE);	
				}			

				$drivers = Drivers::find($driverId);
				$driver_status = isset($drivers->driver_status) ? $drivers->driver_status : 0;
				if ($driver_status != 2 && $driver_status != 0) {
					if ($driver_status == 4 && $shiftStatus == 1) {
						$date1 = date("Y-m-d H:i:s");
						$time1 = strtotime($date1);
						$time = $time1 - (1 * 120);
						$currentTime2 = date("Y-m-d H:i:s", $time);
						$orders = DB::table('orders')
							->select('orders.id as order_id',
								'orders.assigned_time as assigned_time',
								'orders.order_status as order_status'
							)
							->where('orders.driver_ids', '=', (int) $drivers->id)
							->where('orders.order_status', '=', 18)
							->where('orders.assigned_time', '>=', $currentTime2)
							->orderBy('orders.id', 'desc')
							->get();
						if (count($orders) == 0) {
							$status_change = DB::update('update drivers set driver_status =1  where id = ?', array($driverId));
							//$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.shift Status updated successfully"));
							$result = array("httpCode" => 200, "status" => 1, "Message" => trans("messages.shift Status updated successfully"), "shiftStatus" => $shiftStatus);

						} else {
							$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.wait for sometime ...may be you have a trip"), "shiftStatus" => $driver_status);
						}

					} else {

						$status_change = DB::update('update drivers set driver_status =' . $shiftStatus . '  where id = ?', array($driverId));
						//$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.shift Status updated successfully"));
						$result = array("httpCode" => 200, "status" => 1, "Message" => trans("messages.shift Status updated successfully"), "shiftStatus" => $shiftStatus);

					}

				} else {

					$driver_id = $drivers->id;
					$condition = 'orders.order_status in (31, 19,32)';
					$trip_count = DB::table('orders')
						->select('orders.id')
						->where('orders.driver_ids', '=', $driver_id)
						->whereRaw($condition)
						->count();
					if ($trip_count == 0) {
						$status_change = DB::update('update drivers set driver_status =' . $shiftStatus . '  where id = ?', array($driverId));
						//$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.shift Status updated successfully"));
						$result = array("httpCode" => 200, "status" => 1, "Message" => trans("messages.shift Status updated successfully"), "shiftStatus" => $shiftStatus);

					} else {
						$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.you are in trip"), "shiftStatus" => $driver_status);
					}
				}

			} catch (JWTException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
			} catch (TokenExpiredException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);

	}

	public function mdriver_shift_status_new(Request $data) {
		$post_data = $data->all();
		$rules = [
			'driverId' => ['required'],
			'shiftStatus' => ['required'],
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
			$result = array("status" => 2, "httpCode" => 400, "Error" => trans("messages.Error List"), "Message" => $errors);
		} else {
			try {
				//$check_auth = JWTAuth::toUser($post_data['token']);
				$driverId = $post_data['driverId'];
				$shiftStatus = $post_data['shiftStatus'];

				$drivers = Drivers::find($driverId);
				$driver_status = isset($drivers->driver_status) ? $drivers->driver_status : 0;
				if ($driver_status != 2 && $driver_status != 0) {
					if ($driver_status == 4 && $shiftStatus == 1) {
						$date1 = date("Y-m-d H:i:s");
						$time1 = strtotime($date1);
						$time = $time1 - (1 * 120);
						$currentTime2 = date("Y-m-d H:i:s", $time);
						$orders = DB::table('orders')
							->select('orders.id as order_id',
								'orders.assigned_time as assigned_time',
								'orders.order_status as order_status'
							)
							->where('orders.driver_ids', '=', (int) $drivers->id)
							->where('orders.order_status', '=', 18)
							->where('orders.assigned_time', '>=', $currentTime2)
							->orderBy('orders.id', 'desc')
							->get();
						if (count($orders) == 0) {
							$status_change = DB::update('update drivers set driver_status =1  where id = ?', array($driverId));
							$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.shift Status updated successfully"));
						} else {
							$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.wait for sometime ...may be you have a trip"));
						}

					} /*else if($driver_status == 4 && $shiftStatus == 3) {

						$status_change = DB::update('update drivers set driver_status = 3  where id = ?', array($driverId));
						$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.shift Status updated successfully"));

					}*/else {
						/*if($shiftStatus == 1) { //1 ->shift in ,2->shift out
									$driverstatus = 1; //driver online (free)
							}else {
								$driverstatus = 3; //driver offline
						*/
						$status_change = DB::update('update drivers set driver_status =' . $driverstatus . '  where id = ?', array($driverId));

						$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.shift Status updated successfully"));
					}

				} else {

					$driver_id = $drivers->id;
					$condition = 'orders.order_status in (31, 19,32)';
					$trip_count = DB::table('orders')
						->select('orders.id')
						->where('orders.driver_ids', '=', $driver_id)
						->whereRaw($condition)
						->count();
					if ($trip_count == 0) {
						$status_change = DB::update('update drivers set driver_status =' . $driverstatus . '  where id = ?', array($driverId));
						$result = array("status" => 1, "httpCode" => 200, "Message" => trans("messages.shift Status updated successfully"));
					} else {
						$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.you are in trip"));

					}

				}

			} catch (JWTException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
			} catch (TokenExpiredException $e) {
				$result = array("status" => 2, "httpCode" => 400, "Message" => trans("messages.Something went wrong"));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);

	}

	public function mdriver_status(Request $data) {
		$post_data = $data->all();
		$rules = [
			'driverId' => ['required'],
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
				$driverId = $post_data['driverId'];
				$drivers = Drivers::find($driverId);
				$shiftStatus = $drivers->driver_status; //1=>shiftin ,2=>busy,3=>offline
				$date1 = date("Y-m-d 00:00:00");

				$completed_trip_count = DB::table('driver_orders')
					->leftJoin('orders','orders.id','=','driver_orders.order_id')
					->select('driver_orders.order_id')
					->where('driver_orders.created_at', '>=', $date1)
					->where('driver_orders.driver_id', '=', $driverId)
					->where('orders.order_status', '=', 12)
					->orwhere('orders.order_status', '=', 33)
					->where('orders.driver_ids', '=', $driverId)

					->distinct()
					->count();
				//print_r($completed_trip_count);exit();

				$status = [31, 19];
				$ongoing_trip = DB::table('orders')
					->whereIn('order_status', $status)
					->where('orders.driver_ids', '=', $driverId)
					->get();
				$order_id = isset($ongoing_trip->id) ? $ongoing_trip->id : 0;
				$ongoing_trip_count = count($ongoing_trip);

				/*job done today count*/

				$jobdonetodayCount = DB::table('orders')
					->select('orders.id')
					->where('orders.assigned_time', '>=', $date1)
					->where('orders.driver_ids', '=', $driverId)
					//->where('orders.order_status', '=', 12)
					->count();
				/*job done today count*/
				//$shiftStatus = ($shiftStatus == 3 )?2:1 ;
				$data = array(
					'shiftStatus' => $shiftStatus,
					'completeCount' => $completed_trip_count,
					'ongoingCount' => $ongoing_trip_count,
					'jobdonetodayCount' => $jobdonetodayCount,
					'orderId' => $order_id,
				);
				$result = array("status" => 1, "httpCode" => 200, "detail" => $data, "message" => trans("messages.driver status details"));

			} catch (JWTException $e) {
				$result = array("status" => 2, "httpCode" => 400, "message" => trans("messages.Something went wrong"));
			} catch (TokenExpiredException $e) {
				$result = array("status" => 2, "httpCode" => 400, "message" => trans("messages.Something went wrong"));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);

	}

	public function mdriver_status_new(Request $data) {
		//print_r("expression");exit();
		$post_data = $data->all();
		$rules = [
			'driverId' => ['required'],
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
				$driverId = $post_data['driverId'];
				$drivers = Drivers::find($driverId);
				$shiftStatus = $drivers->driver_status; //1=>shiftin ,2=>busy,3=>offline
				$date1 = date("Y-m-d 00:00:00");
				$completed_trip_count = DB::table('orders')
					->select('orders.id')
					->where('orders.assigned_time', '>=', $date1)
					->where('orders.driver_ids', '=', $driverId)
					->where('orders.order_status', '=', 12)
					->count();

				$status = [31, 19,32];
				$ongoing_trip = DB::table('orders')
					->whereIn('order_status', $status)
					->where('orders.driver_ids', '=', $driverId)
					->get();
				$order_id = isset($ongoing_trip->id) ? $ongoing_trip->id : 0;
				$ongoing_trip_count = count($ongoing_trip);
				
				/*job done today count*/

				$jobdonetodayCount = DB::table('orders')
					->select('orders.id')
					->where('orders.assigned_time', '>=', $date1)
					->where('orders.driver_ids', '=', $driverId)
					//->where('orders.order_status', '=', 12)
					->count();
				/*job done today count*/
				//$shiftStatus = ($shiftStatus == 3 )?2:1 ;
				$data = array(
					'shiftStatus' => $shiftStatus,
					'completeCount' => $completed_trip_count,
					'ongoingCount' => $ongoing_trip_count,
					'jobdonetodayCount'=>$jobdonetodayCount,
					'orderId' => $order_id,
				);
				$result = array("status" => 1, "httpCode" => 200, "detail" => $data, "message" => trans("messages.driver status details"));

			} catch (JWTException $e) {
				$result = array("status" => 2, "httpCode" => 400, "message" => trans("messages.Something went wrong"));
			} catch (TokenExpiredException $e) {
				$result = array("status" => 2, "httpCode" => 400, "message" => trans("messages.Something went wrong"));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);

	}

	public function promocodesamp(Request $data) {

		/*$res = array(array("featureName"=>"irst highlight",
						"data"=>array(array("color"=>"red"))
					),array("featureName"=>"second highlight",
						"data"=>array(array("color"=>"red"))
					));*/
		$res = array(array("featureName"=>"Highlight",
						"data"=>array(
							array("Description"=>"Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum"),
							array("KeyFeatures"=>"Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum "))
					),array("featureName"=>"Info",
						"data"=>array(array("ManufacturerDetails"=>"Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum"),array("Seller"=>"Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum"))
					));

		$res = json_encode($res);
		//print_r($res);exit;

		//$id=array(47);
		$result = DB::table('products')
				//->whereIn('id', $id)
				->where('active_status',1)
				->update(['more_description' => $res]);

		print_r("success");exit;



		$post_data = array("deviceId" => "43108d8c5c5476c0",
			"deviceToken" => "cHlFbeJob4c:APA91bGGDYnUBpIWKyxqRUIb3tOqlXQs0VU7RySIYF8JxVyBoyORHT-YJUtWMYPL_TqMrL6aY6jha5MHD67V4bqSnk5f8rW6H6g4QSK491Jom4G_DAvityQzEXpSTkv312a7R4nQQHjy",
			"language" => 1,
			"outletId" => 118,
			"paymentGatewayId" => 24,

			"promoCode" => "",
			"userId" => "693",
			"vendorId" => 239,
		);
		$post_data['promoCode'] = isset($post_data['promoCode']) ? $post_data['promoCode'] : 'BR20F';
		$post_data['promoCode'] = 'BR20F';

		$current_date = strtotime(date('Y-m-d'));
		$coupon_details = DB::table('coupons')
			->select('coupons.id as coupon_id', 'coupon_type', 'offer_amount', 'offer_type', 'coupon_code', 'start_date', 'end_date')
			->leftJoin('coupon_outlet', 'coupon_outlet.coupon_id', '=', 'coupons.id')
			->where('coupon_code', '=', $post_data['promoCode'])
			->where('coupon_outlet.outlet_id', '=', $post_data['outletId'])
			->first();
		$coupon_details->start_date = "2019-06-04 12:28:00";
		$coupon_details->end_date = "2019-06-28 12:28:00";

		if (count($coupon_details) == 0) {
			$result = array("response" => array("httpCode" => 400, "Message" => trans("messages.This coupon is not applicable for the current store.")));
			return json_encode($result);
		} else if ((strtotime($coupon_details->start_date) <= $current_date) && (strtotime($coupon_details->end_date) >= $current_date)) {

			print_r("expression");exit;
			$coupon_user_limit_details = DB::table('user_cart_limit')
				->select('cus_order_count', 'user_limit', 'total_order_count', 'coupon_limit')
				->where('customer_id', '=', $post_data['userId'])
				->where('coupon_code', '=', $post_data['promoCode'])
				->first();
			print_r($coupon_user_limit_details);exit;

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
		}

		print_r("dcffffffd");exit;

	}
	public function puhscheck_fun(Request $data)
    {
    	//$token ="sdfdsfsdfsfsf";
    	//$check_auth = JWTAuth::toUser($token);
		$order_title = 'checking';
		$data = array
			(
			'id' => 1,
			'driverId' => 2,
			'type' => 2,
			'title' => $order_title,
			'message' => $order_title,
			'request_type' => 1,
			"order_assigned_time" => 'check',
			'notification_dialog' => "1",
		);
		$fields = array
			(
			'registration_ids' => array('ezTA9U0Gsbs:APA91bHTDi112KRhnketi7u9yFfQP4fq4kzG-j9I_Pt0iw3sttDJT3VMVpcaMwYF2b6XsQowC3my8gsA-b-Qx8liCxcIT08f96zjtuG2bULW1dXstgS3HIYpuMkoSV2ezCjeschQeVg1'),
			//'data' => $data,
			//'notification' => $data,
			'data' => array('title' => $order_title, 'body' =>  $data ,'sound'=>'Default','image'=>'Notification Image')

			//'notification' => array('title' => 'checking', 'body' =>  $data ,'sound'=>'Default','image'=>'Notification Image', 'data' =>$data ),

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
		print_r($result);exit;
		curl_close($ch);

		/*FCM push notification*/	

    }


    


	/*	public function sample_chk(Request $data)
				{

				$data = array(
				array (
				"vendors_id"=> 236,
				"vendor_name"=> "BROZMART",
				"first_name"=>  "Prasanth",
				"last_name"=>  "M",
				"featured_image"=>  "236.jpg",
				"logo_image"=>  "236.jpg",
				"vendors_delivery_time"=>  "10",
				"category_ids"=>  "508,524,535,545",
				"vendors_average_rating"=>  5,
				"featured_vendor"=>  1,
				"contact_address"=>  "Gandhipuram, New Siddhapudur, Coimbatore, Tamil Nadu",
				"vendor_description"=>  "Shop offering a wide variety of items..",
				"distance"=>  "5802.83027157896",
				"outlets_id"=>  13
				),
				array (

				"vendors_id"=> 236,
				"vendor_name"=> "BROZMART",
				"first_name"=>  "Prasanth",
				"last_name"=>  "M",
				"featured_image"=>  "236.jpg",
				"logo_image"=>  "236.jpg",
				"vendors_delivery_time"=>  "10",
				"category_ids"=>  "508,524,535,545",
				"vendors_average_rating"=>  5,
				"featured_vendor"=>  1,
				"contact_address"=>  "Gandhipuram, New Siddhapudur, Coimbatore, Tamil Nadu",
				"vendor_description"=>  "Shop offering a wide variety of items..",
				"distance"=>  "5802.83027157896",
				"outlets_id"=>  15
				),
				array (

				"vendors_id"=> 237,
				"vendor_name"=> "BROZMART",
				"first_name"=>  "Prasanth",
				"last_name"=>  "M",
				"featured_image"=>  "236.jpg",
				"logo_image"=>  "236.jpg",
				"vendors_delivery_time"=>  "10",
				"category_ids"=>  "508,524,535,545",
				"vendors_average_rating"=>  5,
				"featured_vendor"=>  1,
				"contact_address"=>  "Gandhipuram, New Siddhapudur, Coimbatore, Tamil Nadu",
				"vendor_description"=>  "Shop offering a wide variety of items..",
				"distance"=>  "5802.83027157896",
				"outlets_id"=>  155
				),
				array (

				"vendors_id"=> 237,
				"vendor_name"=> "BROZMART",
				"first_name"=>  "Prasanth",
				"last_name"=>  "M",
				"featured_image"=>  "236.jpg",
				"logo_image"=>  "236.jpg",
				"vendors_delivery_time"=>  "10",
				"category_ids"=>  "508,524,535,545",
				"vendors_average_rating"=>  5,
				"featured_vendor"=>  1,
				"contact_address"=>  "Gandhipuram, New Siddhapudur, Coimbatore, Tamil Nadu",
				"vendor_description"=>  "Shop offering a wide variety of items..",
				"distance"=>  "5802.83027157896",
				"outlets_id"=>  155675
				));

				$list=$stores=array();
				foreach ($data as $key => $value) {
				if (!in_array($value['vendors_id'], $list))
				{
				array_push($list,$value['vendors_id']);
				$stores[$key]['vendorId']= $value['vendors_id'];
				$stores[$key]['vendorName']= $value['vendor_name'];
				$stores[$key]['categoryIds']= $value['vendor_name'];
				$stores[$key]['address']= $value['vendor_name'];
				$stores[$key]['description']= $value['vendor_name'];
				$stores[$key]['featuredImage']= $value['vendor_name'];
				$stores[$key]['deliveryTime']= $value['vendor_name'];
				$stores[$key]['vendorsRating']= $value['vendor_name'];
				$stores[$key]['offer']= $value['vendor_name'];
				$stores[$key]['comboOffer']= $value['vendor_name'];
				$stores[$key]['logoImage']= $value['vendor_name'];
				$stores[$key]['nearestList'][$key]['outletId']= $value['outlets_id'];
				$stores[$key]['nearestList'][$key]['outletName']= $value['outlets_id'];
				$stores[$key]['nearestList'][$key]['address']= $value['outlets_id'];
				$stores[$key]['nearestList'][$key]['description']= $value['outlets_id'];
				$stores[$key]['nearestList'][$key]['deliveryTime']= $value['outlets_id'];
				$stores[$key]['nearestList'][$key]['vendorsRating']= $value['outlets_id'];
				}else
				{
				foreach ($stores as $key => $val) {
				$x ='';
				if ($val['vendorId'] === $value['vendors_id']) {
				$x =$key;
				}
				}
				$count =count($stores[$x]['nearestList']);
				$stores[$x]['nearestList'][$count]['outletId']= $value['outlets_id'];
				$stores[$x]['nearestList'][$count]['outletName']= $value['outlets_id'];
				$stores[$x]['nearestList'][$count]['address']= $value['outlets_id'];
				$stores[$x]['nearestList'][$count]['description']= $value['outlets_id'];
				$stores[$x]['nearestList'][$count]['deliveryTime']= $value['outlets_id'];
				$stores[$x]['nearestList'][$count]['vendorsRating']= $value['outlets_id'];
				}
				}//print_r($stores);exit;

				}
				 */



	//Ram : 20/09/19

	public function assignDriver(Request $data) {

		
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
			$result =  array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
		} else {
			
			$new_orders = Order::find($data_all['order_id']);
			//	print_r($new_orders);exit();
			/*$driver_orders = driverorderInfo($data_all['order_id'],$data_all['driver']);
			$assigned_time = isset($driver_orders->assigned_time)?$driver_orders->assigned_time:'';
		    $datetime1 = new DateTime();
            $datetime2 = new DateTime($assigned_time);
            $diff = $datetime1->getTimestamp()-$datetime2->getTimestamp() ;
			*/

			if($new_orders->driver_ids == '' /*|| $diff > 60*/){
			
				$driver_settings = Driver_settings::find(1);
				$orders = DB::table('orders')
					->select('driver_ids')
					->where('orders.id', $data_all['order_id'])
					->first();
				$driver_ids = $orders->driver_ids;
				$new_orders->driver_ids = /*$driver_ids . */$data_all['driver'];
				$assigned_time = strtotime("+ " . $driver_settings->order_accept_time . " minutes", strtotime(date('Y-m-d H:i:s')));
				$update_assign_time = date("Y-m-d H:i:s", $assigned_time);
				$new_orders->assigned_time = $update_assign_time;
				$new_orders->save();

				driver_assignlog($data_all['order_id'],$data_all['driver']); //drive rorder logs


				$order_title = 'order assigned to you';
				$driver_detail = Drivers::find($data_all['driver']);
								$driverDetail=DB::table('drivers')
								->select('drivers.first_name')
								->where('drivers.id', '=', (int) $data_all['driver'])
								->get();
				$driver_name= isset($driverDetail[0]->first_name)?$driverDetail[0]->first_name:'';
				$accept="Request to ".$driver_name;

				$affected=DB::table('orders_log')
							->where('orders_log.order_id','=',$data_all['order_id'])
							->where('orders_log.order_status','=',18)
							->update(['order_comments'=>$accept]);
				

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

					/*$headers = array
						(
						'Authorization: key=AAAAI_fAV4w:APA91bFSR1TLAn1Vh134nzXLznsUVYiGnR4KiUYdAa3u0OccC5S-DyDdQRdnR0XugSRArsJGXC8AHE342eNhBbnK8np10KuyuWwiJxtndV75O4DyT3QCGXKFu_fwUTNPdB51Cno6Rewc',
						'Content-Type: application/json',
					);*/

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

				// Session::flash('message', trans('messages.Driver assigned successfully'));
				$result = array("status" => 1, "message" => trans('messages.Driver assigned successfully'));
			}else
			{
				//print_r("expression");exit();
				// Session::flash('message-failure', trans('messages.Driver Already assigned'));
				$result = array("status"=>2, "message" => trans('messages.Driver Already assigned'));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}



	public function availableDrivers(Request $data){


		
        $post_data = $data->all();
		$vendor_id = $post_data["vendorId"];
		$available_driver =array();
		$outlet_latitude=$post_data["outletLatitude"];
		$outlet_longitude=$post_data["outletLongitude"];
	    deltedriverorderinfo();
		$availableDrivers=vendors_drivers_list($outlet_latitude, $outlet_longitude, $vendor_id);
		foreach ($availableDrivers as $key => $value) {
			if(count($value)){				
					$available_driver[] = $value;
				/*$driver_info= get_driverOrder($value->driver_id);
				if(!count($driver_info)) {
					$available_driver[] = $value;

				}*/
			}
		}
		$result = array("status" => 1, "message" => trans('messages.availableDriversList'),'detail'=>$available_driver);

				return json_encode($result, JSON_UNESCAPED_UNICODE);


	}
	/*	public function substitution(Request $data)
	{
		$data_all = $data->all();
		$validation = Validator::make($data_all, array(
			'orderId' => 'required',
			'productId' => 'required',
			'replacementId' => 'required',
			'quantity' => 'required',
			'discountPrice' => 'required',
			'itemOffer' => 'required',
		));

		if ($validation->fails()) {
			$errors = '';
			$j = 0;
			foreach ($validation->errors()->messages() as $key => $value) {
				$error[] = is_array($value) ? implode(',', str_replace(".", " ", $value)) : str_replace(".", " ", $value);
			}
			$errors = implode("<br>", $error);
			$result =  array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
		} else {
			$replace_product = DB::table('products')
					->select('*')
					->where('products.id', '=', $data_all['replacementId'])
					->get();
			$order_list = DB::table('orders')
					->select('orders.id as orders_id','orders.total_amount as total', 'products.weight' ,'orders.delivery_charge','orders.service_tax','orders.customer_id','products.product_url')
					->Join('orders_info', 'orders_info.order_id', '=', 'orders.id')			
					->Join('products', 'products.id', '=', 'orders_info.item_id')
					->where('orders_info.item_id', '=', $data_all['productId'])
					->where('orders.id', '=', $data_all['orderId'])
					->get();
			$datas=array();
			$datas['user_id'] =$order_list[0]->customer_id;
			$datas['order_id'] =$data_all['orderId'];
			$datas['language'] =1;
			$datas['product_id'] =$data_all['productId'];
			$sub_total = $tax = $delivery_cost = 0;

			$order_details = get_order($datas);
			$orderdetail =$order_details[0];
			$oldProdcutweight = isset($orderdetail->weight)?$orderdetail->weight:0;
			$oldProdcutcost = isset($orderdetail->item_cost)?$orderdetail->item_cost:0;
			$oldProdcutqnty = isset($orderdetail->item_unit)?$orderdetail->item_unit:0;
			$newProdcutprice = isset($replace_product[0]->original_price)?$replace_product[0]->original_price:0;

			$adjustment_qty  = isset($orderdetail->adjust_weight_qty)?$orderdetail->adjust_weight_qty:0;
			$itemprice = $oldProdcutcost/ $oldProdcutweight; 	
			$adj_price = $adjustment_qty *	$itemprice;
			$sub_total += ($oldProdcutqnty * $oldProdcutcost) + $adj_price;
			$total = isset($order_list[0]->total)?$order_list[0]->total:0;
			$amnt1 =$total - $sub_total;
			$amnt2 =$amnt1 + $newProdcutprice;
			$product_url = isset($order_list[0]->product_url)?$order_list[0]->product_url:'';
			$replce_id = DB::table('orders_info')->insertGetId(
				[
					'item_id' => $data_all['replacementId'], 
					'item_cost' => $data_all['discountPrice'],
					'item_unit' => $data_all['quantity'], 
					'item_offer' => $data_all['itemOffer'], 
					'order_id' => $data_all['orderId'], 
					'adjust_weight_qty' => 0 , 
					'additional_comments' => $product_url , 
					'replacement_product_id' =>0 
				]
			);
			$res = DB::table('orders_info')
				->where('order_id', '=', $data_all['orderId'])
				->where('item_id', '=', $data_all['productId'])
				->update(['replacement_product_id' => $replce_id]);

			/*substitution log/
			$values = array('order_id' => $data_all['orderId'],
					'product_id' => $data_all['productId'],
					'replaced_product_info_id' => $replce_id,
					'replaced_product_id' => $data_all['replacementId'],
					'created_date' => date('Y-m-d H:i:s'));
			DB::table('substitution_log')->insert($values);
			
			$res = DB::table('orders')->where('id', $data_all['orderId'])->update(['total_amount' => abs($amnt2)]);
			$res = DB::table('orders_log')->where('order_id', $data_all['orderId'])->update(['total_amount' => abs($amnt2)]);
			$result = array("status" => 1, "message" => trans('messages.product replaced'));
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);

	}*/

	public function substitution(Request $data)
	{
		$data_all = $data->all();
		$validation = Validator::make($data_all, array(
			'orderId' => 'required',
			'productId' => 'required',
			'replacementId' => 'required',
			'quantity' => 'required',
			'discountPrice' => 'required',
			'itemOffer' => 'required',
		));

		if ($validation->fails()) {
			$errors = '';
			$j = 0;
			foreach ($validation->errors()->messages() as $key => $value) {
				$error[] = is_array($value) ? implode(',', str_replace(".", " ", $value)) : str_replace(".", " ", $value);
			}
			$errors = implode("<br>", $error);
			$result =  array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
		} else {
			$replace_product = DB::table('admin_products')
					->select('*')
					->where('admin_products.id', '=', $data_all['replacementId'])
					->get();
					//print_r($replace_product);exit();
			$order_list = DB::table('orders')
					->select('orders.id as orders_id','orders.total_amount as total', 'admin_products.weight' ,'orders.delivery_charge','orders.service_tax','orders.customer_id','admin_products.product_url')
					->Join('orders_info', 'orders_info.order_id', '=', 'orders.id')			
					->Join('admin_products', 'admin_products.id', '=', 'orders_info.item_id')
					->where('orders_info.item_id', '=', $data_all['productId'])
					->where('orders.id', '=', $data_all['orderId'])
					->get();
			//print_r($order_list);exit();
			$datas=array();
			$datas['user_id'] =$order_list[0]->customer_id;
			$datas['order_id'] =$data_all['orderId'];
			$datas['language'] =1;
			$datas['product_id'] =$data_all['productId'];
			$sub_total = $tax = $delivery_cost = 0;

			$order_details = get_order($datas);
			$orderdetail =$order_details[0];

			$oldProdcutweight = isset($orderdetail->weight)?$orderdetail->weight:0;
			$oldProdcutcost = isset($orderdetail->item_cost)?$orderdetail->item_cost:0;
			$oldProdcutqnty = isset($orderdetail->item_unit)?$orderdetail->item_unit:0;
			$adjustment_qty  = isset($orderdetail->adjust_weight_qty)?$orderdetail->adjust_weight_qty:0;
			//$newProdcutprice = isset($replace_product[0]->discount_price)?$replace_product[0]->discount_price:0;
			$newProdcutprice = isset($data_all['discountPrice'])?$data_all['discountPrice']:0;

			$quantity = isset($data_all['quantity'])?$data_all['quantity']:0;
			$total = isset($order_list[0]->total)?$order_list[0]->total:0;

			$new_product_price = $newProdcutprice *$quantity; //new prodcit price

			$itemprice = $oldProdcutcost/ $oldProdcutweight;
			$adj_price = $adjustment_qty *	$itemprice;
			if($adjustment_qty !=0)	{
				$item_price	= $oldProdcutcost/$oldProdcutweight ;
				$sub_total += $adj_price;
			}else{
				$sub_total += ($oldProdcutqnty * $oldProdcutcost);
			}

			$amnt1 =$total - $sub_total;
			$amnt2 =$amnt1 + $new_product_price;	
				
			$product_url = isset($order_list[0]->product_url)?$order_list[0]->product_url:'';
			$replce_id = DB::table('orders_info')->insertGetId(
				[
					'item_id' => $data_all['replacementId'], 
					'item_cost' => $newProdcutprice,
					'item_unit' => $data_all['quantity'], 
					'item_offer' => $data_all['itemOffer'], 
					'order_id' => $data_all['orderId'], 
					'adjust_weight_qty' => 0 , 
					'additional_comments' => $product_url , 
					'replacement_product_id' =>0 
				]
			);
			$res = DB::table('orders_info')
				->where('order_id', '=', $data_all['orderId'])
				->where('item_id', '=', $data_all['productId'])
				->update(['replacement_product_id' => $replce_id]);

			/*substitution log*/
			$values = array('order_id' => $data_all['orderId'],
					'product_id' => $data_all['productId'],
					'replaced_product_info_id' => $replce_id,
					'replaced_product_id' => $data_all['replacementId'],
					'created_date' => date('Y-m-d H:i:s'));
			DB::table('substitution_log')->insert($values);
			
			$res = DB::table('orders')->where('id', $data_all['orderId'])->update(['total_amount' => abs($amnt2)]);
			$res = DB::table('orders_log')->where('order_id', $data_all['orderId'])->update(['total_amount' => abs($amnt2)]);
			$result = array("status" => 1, "message" => trans('messages.product_replaced'));
			
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);

	}

	public function adjustments(Request $data)
	{
		$data_all = $data->all();
		//print_r($data_all);exit;
		$validation = Validator::make($data_all, array(
			'orderId' => 'required',
			'productId' => 'required',
			'adjustment' => 'required',
			'quantity' => 'required',
			'discountPrice' => 'required',
			'itemOffer' => 'required',
			'weight' => 'required',
		));
		if ($validation->fails()) {
			$errors = '';
			$j = 0;
			foreach ($validation->errors()->messages() as $key => $value) {
				$error[] = is_array($value) ? implode(',', str_replace(".", " ", $value)) : str_replace(".", " ", $value);
			}
			$errors = implode("<br>", $error);
			$result =  array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
		} else {
			$order_list = DB::table('orders')
					->select('orders.id as orders_id','orders.total_amount as total', 'admin_products.weight' ,'orders.delivery_charge','orders.service_tax','orders.customer_id')
					->Join('orders_info', 'orders_info.order_id', '=', 'orders.id')			
					->Join('admin_products', 'admin_products.id', '=', 'orders_info.item_id')
					->where('orders_info.item_id', '=', $data_all['productId'])
					->where('orders.id', '=', $data_all['orderId'])
					->get();



			$total =isset($order_list[0]->total)?$order_list[0]->total:0;
			$delivery_charge =isset($order_list[0]->delivery_charge)?$order_list[0]->delivery_charge:0;
			$service_tax =isset($order_list[0]->service_tax)?$order_list[0]->service_tax:0;
			$ord_weight =isset($order_list[0]->weight)?$order_list[0]->weight:0;
			$adjustment =isset($data_all['adjustment'])?$data_all['adjustment']:0;
			$item_price =isset($data_all['discountPrice'])?$data_all['discountPrice']:0;
			$weight =isset($data_all['weight'])?$data_all['weight']:0;
			$res = DB::table('orders_info')
				->where('order_id', '=', $data_all['orderId'])
				->where('item_id', '=', $data_all['productId'])
				->update(['adjust_weight_qty' =>  $adjustment]);

			$datas['user_id'] =$order_list[0]->customer_id;
			$datas['order_id'] =$data_all['orderId'];
			$datas['language'] =1;
			$sub_total = $tax = $delivery_cost = 0;

			$order_details = get_order($datas);
			foreach ($order_details as $key => $items) {	
				if ($items->replacement_product_id == 0 || $items->replacement_product_id == null) {    
	
					$adjustment_qty  = isset($items->adjust_weight_qty)?$items->adjust_weight_qty:0;
					$coupon_amount  = isset($items->coupon_amount)?$items->coupon_amount:0;
					if($adjustment_qty){$flat=1;}else{$flat=0;};
					$itemprice = $items->item_cost/ $items->weight; 	
					$adj_price = $adjustment_qty *	$itemprice;
					if($flat == 1){
						$sub_total += $sub_total + $adj_price;
					}else{
						$sub_total += ($items->item_unit * $items->item_cost);
					}
					//print_r($sub_total);echo"<br>";


					$tax += $items->service_tax;
					$delivery_settings = $this->get_delivery_settings();
				}
			}	
			//print($coupon_amount);exit;
			$tax_amount = $sub_total * $tax / 100;
			$total = $sub_total + $service_tax + $coupon_amount;
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

			$res = DB::table('orders')
				->where('id', '=', $data_all['orderId'])
				->update(['replace' => 2 ,'total_amount'=>$total]);
			$res = DB::table('orders_log')
				->where('id', '=', $data_all['orderId'])
				->update(['total_amount'=>$total]);

			$result = array("status" => 1, "message" => trans('messages.adjustment_done'));

		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);


	}

	public function get_delivery_settings() {
		$delivery_settings = DB::table('delivery_settings')->first();
		return $delivery_settings;
	}

	public function referralDetails(Request $data)
	{	
		$data_all = $data->all();
		$currency =getCurrencyList();
		$currency_code = isset($currency[0]->currency_code)?$currency[0]->currency_code:CURRENCYCODE;
		//print_r($data_all);exit;
		$validation = Validator::make($data_all, array(
			'userId' => 'required',
			
		));
		if ($validation->fails()) {
			$errors = '';
			$j = 0;
			foreach ($validation->errors()->messages() as $key => $value) {
				$error[] = is_array($value) ? implode(',', str_replace(".", " ", $value)) : str_replace(".", " ", $value);
			}
			$errors = implode("<br>", $error);
			$result =  array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
		} else {
			$referral =getreferral();
			$users_details=DB::table('users')
	            ->select('id','referral_code','wallet_amount')
	            ->where('users.id',$data_all['userId'])
	            ->get();

	        $wallet_amount = isset($users_details[0]->wallet_amount)?$users_details[0]->wallet_amount:0;
	        $referral_code = isset($users_details[0]->referral_code)?$users_details[0]->referral_code:"";
	        $referred_amount = isset($referral[0]->referred_amount)?$referral[0]->referred_amount:"";
	        $message = "Refer your friends and we\'ll give you ".$referral[0]->referred_amount ." after completing your friend first purchase.And your friend will get  ".$referral[0]->referral_amount.".";
	        $referalShareTxt ="Signup with this code ".$referral_code." and  get " .$referred_amount." amount instantly" ;
	        $points = $wallet_amount * 100;
	        $walletPoints = $points ." (1 ".$currency_code." = 100)";
	        //$walletPoints = $points ." (1 AED = 100)";

	      	$detail = array("walletAmount" =>$wallet_amount , "referralCode" =>$referral_code ,"referalDesc"=> $message,"referalShareTxt"=> $referalShareTxt,"walletPoints"=> $walletPoints);

	        $result = array("status" => 1, "message" => trans('messages.Referral details'),"details"=>$detail);

		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);


	}

	public function pushchk(Request $data)
	{

		//print_r("expression");exit;
		$order_title = 'Order Is Assigned';
        $description = 'Order Is Assigned ';
        $orderStatus =34;
        $token = "fIWWtPa1CNY:APA91bGWyp0mb-TJPydg4cK8mpMRDH9byQGmKCeOkPYo9asEneHos6yVk0odjaeEjO2Npe9O6n96sFHiJ6rybI8TEqk6u7VewGonBXWV4xORBciWmdYIeaxqxRETFd2KIPTH7UEpffPA";
            $data = array
                (
                'status' => 1,
                'message' => $order_title,
                'detail' =>array(
                'description'=>$description,    
                'type' => 2,
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
            print_r($result);exit;
            curl_close($ch);	
	}



public function change_password(Request $data) {

		$data_all = $data->all();
	
		$rules = [
			'id' => ['required'],
			'newPassword' => ['required'],
			'oldPassword' => ['required'],
			'retypePassword' => ['required'],
		];
		$errors = $result = array();

		$validator = app('validator')->make($data->all(), $rules);

		if ($validator->fails()) {

			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("status" => 0,  "message" => $errors, "Error" => trans('messages.Error List'));
		} else {
			//$check_auth = JWTAuth::toUser($post_data['token']);
			$errors = '';
			$oldPassword = $data_all['oldPassword'];
			$password = $data_all['newPassword'];
			if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*?[0-9])[a-zA-Z\d]{6,}$/', $password)) {
				$errors .= $validator->errors()->add('newPassword', 'The password Minimum 6 characters at least 1 Uppercase Alphabet, 1 Lowercase Alphabet and 1 Number.');
			}
			if ($errors != '') {
				$result = array("status" => 3,  "message" => trans("messages.The password Minimum 6 characters at least 1 Uppercase Alphabet, 1 Lowercase Alphabet and 1 Number."));
			}

			$retypePassword = $data_all['retypePassword'];
			if ($retypePassword != $password) {

				$result = array("status" => 4, "message" => trans('messages.Password and retypePassword is not matched please check'));
			} else {

				$string = $data_all['newPassword'];
				$pass_string = md5($string);
				//$session_userid = $data_all['userUnique'];
				$users = DB::table('drivers')
					->select('id','otp_unique', 'drivers.first_name', 'drivers.last_name', 'drivers.email')
					->where('drivers.id', $data_all['id'])
					->get();
				//print_r($users);exit();
				$res = DB::table('drivers')
					->where('id', '=',$data_all['id'])
					->update(['hash_password' => $pass_string]);
				//->save();

					$result = array("status" => 1, "message" => trans('messages.Your Password Changed Successfully ,Please login again.'));

				
				/*else {

					$result = array("status" => 2, "message" => trans('messages.Driver Not Found.'));
				}*/
				
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

}
