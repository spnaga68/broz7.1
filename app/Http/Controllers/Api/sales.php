<?php

namespace App\Http\Controllers\Api;
use App;
use App\Http\Controllers\Controller;
use JWTAuth;
//use Services_Twilio;
use Twilio\Rest\Client;

use Session;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Input;
use Tymon\JWTAuth\Exceptions\JWTException;
use URL;
use DB;
use App\Model\salesperson;
use Illuminate\Support\Facades\Crypt;

use Illuminate\Support\Facades\Text;




use DateTime;
use App\Model\drivers;
use App\Model\driver_orders;
use App\Model\driver_settings;
use App\Model\driver_track_location;
use App\Model\users;
use App\Model\vendors;
DB::enableQueryLog();
use App\Model\settings;
use App\Model\settings_infos;
use App\Model\driver_cores;
use Illuminate\Support\Facades\Validator;
use App\Model\order;
use App\Http\Controllers\Api\outlet;



class sales extends Controller {


	public function salesPersonInfo(Request $data) {

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
			$result = array("status" => 0,  "message" => trans("messages.Error List"), "detail" => $errors);
		} else {	
	

			try {
				//$check_auth = JWTAuth::toUser($post_data['token']);
				$salesperson_data = DB::table('salesperson')
					->select('salesperson.id', 'name as userName', 'salesperson.profile_image as imageUrl ', 'salesperson.mobile_number as mobile', 'salesperson.email')
					->where('salesperson.id', $post_data['id'])
					->where('salesperson.active_status', 1)
					->first();
				if (count(array($salesperson_data)) > 0) {
					$salesperson_data->userName = ($salesperson_data->userName != '') ? $salesperson_data->userName : '';
					//$driver_data->last_name = ($driver_data->last_name != '') ? $driver_data->last_name : '';
					$imageName = url('/assets/admin/base/images/default_avatar_male.jpg');
					if (file_exists(base_path() . '/public/assets/admin/base/images/drivers/' . $salesperson_data->imageUrl) && $salesperson_data->imageUrl != '') {
						$imageName = URL::to("/assets/admin/base/images/drivers/" . $salesperson_data->imageUrl . '?' . time());
					}
					$salesperson_data->imageUrl = $imageName;

					 /**driver review average calculating and updated here **/ 
			        /*$reviews_average=DB::table('driver_reviews')
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
			            
			            
			        }*/
					//print_r($average_rating);exit;
					//$driver_data->driverRating = $average_rating;

					$result = array("status" => 1,  "message" => trans("messages.Sales Person details"), 'details' => $salesperson_data);
				} else {
					$result = array("status" => 2,  "message" => trans("messages.No driver found"));
				}
			} catch (JWTException $e) {
				$result = array("status" => 0, "message" => trans("messages.Kindly check the user credentials"));
			} catch (TokenExpiredException $e) {
				$result = array("status" => 0, "message" => trans("messages.Kindly check the user credentials"));
			}
		}
		return json_encode($result);exit;

	}

	public function login_salesPerson(Request $data) {


		$post_data = $data->all();
		$rules = [
			'phone' => ['required'],
			'password' => ['required'],
			'login_type' => ['required'],
			'language' => ['required'],
			'device_id' => ['required_unless:login_type,1,2,3'],
			'device_token' => ['required_unless:login_type,1,2,3'],
		];
		
		if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
		$errors = $result = array();
		
		$validator = app('validator')->make($post_data, $rules);
		$phone = !empty($post_data['phone']) ? $post_data['phone'] : '';
		$password = !empty($post_data['password']) ? $post_data['password'] : '';
		$validator->after(function ($validator) use ($post_data) {
			if (!empty($post_data['phone'])) {

				$user_data = DB::select('SELECT salesPerson.id, salesPerson.email, salesPerson.social_title, salesPerson.name, salesPerson.profile_image, salesPerson.active_status,salesPerson.status, salesPerson.is_verified, salesPerson.mobile_number FROM salesPerson where salesPerson.hash_password = ? AND salesPerson.mobile_number = ?  limit 1', array(md5($post_data['password']), $post_data['phone']));


				

				if (count($user_data) == 0) {
					$validator->errors()->add('phone', 'Invalid login credentials');
				} else {
					$user_data = $user_data[0];
					if ($user_data->is_verified == 0) {
						$validator->errors()->add('phone', 'Kinldy verify your mobile');
					}
				}
			}
		});
		if ($validator->fails()) {
			$user_id = $mobile = 0;
			$phone_verify = 1;
			$errors = array();
			if (!empty($phone)) {
				$user_data = DB::select('SELECT salesPerson.id, salesPerson.email, salesPerson.social_title, salesPerson.name, salesPerson.profile_image	, salesPerson.active_status,salesPerson.status, salesPerson.is_verified, salesPerson.mobile_number FROM salesPerson where salesPerson.hash_password = ? AND salesPerson.mobile_number = ?  limit 1', array(md5($post_data['password']), $post_data['phone']));
				
				$user_id = isset($user_data[0]->id) ? $user_data[0]->id : 0;
				$mobile = isset($user_data[0]->mobile) ? $user_data[0]->mobile : 0;
			}
			$j = 0;
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result =  array( "status" => 3, "message" => $errors, "phone_verify" => $phone_verify, "user_id" => $user_id, "mobile" => $mobile);


		} else {

			if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        	} else {
            App::setLocale('en');
        	}
			$post_data['phone'] = trim(strtolower($post_data['phone']));
			$user_data = DB::select('SELECT salesPerson.id, salesPerson.email, salesPerson.social_title, salesPerson.name,  salesPerson.profile_image	, salesPerson.active_status,salesPerson.status, salesPerson.is_verified, salesPerson.mobile_number FROM salesPerson where salesPerson.hash_password = ? AND salesPerson.mobile_number = ?  limit 1', array(md5($post_data['password']), $post_data['phone']));
				// print_r($user_data);exit();

			$user_data = $user_data[0];
			if (count($user_data) > 0) {
				if ($user_data->is_verified == 0) {
					$result =  array( "status" => 0, "message" => trans("messages.Please confirm you mail to activation."));
				} else if ($user_data->active_status == 0) {
					$result =  array( "status" => 0, "message" => trans("messages.Your registration has blocked pls contact Your Admin."));
				} else {
					// Check login type based on mobile api parameters
					if (isset($post_data['login_type']) && !empty($post_data['login_type'])) {

						//Update the device token & id for Android
						if ($post_data['login_type'] == 2) {

							$res = DB::table('salesperson')
								->where('id', $user_data->id)
								->update(['android_device_token' => $post_data['device_token'], 'android_device_id' => $post_data['device_id'], 'login_type' => $post_data['login_type']]);
						}


						//Update the device token & id for iOS
						if ($post_data['login_type'] == 3) {
							$res = DB::table('salesperson')
								->where('id', $user_data->id)
								->update(['ios_device_token' => $post_data['device_token'], 'ios_device_id' => $post_data['device_id'], 'login_type' => $post_data['login_type']]);
						}
					}
					$token = JWTAuth::fromUser($user_data, array('exp' => 200000000000));

					
					$result =  array( "status" => 1, "message" => trans("messages.User Logged-in Successfully"),"details" =>array("user_id" => $user_data->id, "token" => $token, "email" => $user_data->email,  "social_title" => !empty($user_data->social_title) ? $user_data->social_title : '', "name" => isset($user_data->name) ? $user_data->name : "",  "image" => isset($user_data->image) ? $user_data->image : "", "mobile" => isset($user_data->mobile_number) ? $user_data->mobile_number : "", "phone_verify" => isset($user_data->phone_verify) ? $user_data->phone_verify : "", "user_type" => isset($post_data['user_type']) ? (int) $post_data['user_type'] : "0","status"=>$user_data->status));
					
				}
			} else {
				$result =  array( "status" => 2, "message" => trans("messages.Your account is inactive mode. Kindly contact admin."));
			}
		}
		return $result;
	}



	public function salesResendOtp(Request $data) {

		$rules = array(
			'language' => 'required',
			'phoneNumber' => 'required',
			'countryCode' => 'required',
		);
		$post_data = $data->all();
		

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
			//$check_auth = JWTAuth::toUser($post_data['token']);
			$post_data = $data->all();
			$phone = $post_data['phoneNumber'];
			$countryCode = $post_data['countryCode'];
			$phoneNumber = $countryCode . $phone;
			//$phoneNumber =$phone;
			$user_details = DB::table('salesperson')
				->select('id')
				->where('mobile_number', '=', $phone)
				->first();

				//print_r ($user_details);exit;

			$result = array("status" => 0, "message" => trans('messages.Mobile Number is not register '));
			if (count($user_details) > 0) {
				$users =Salesperson::find($user_details->id);

				$otp = rand(1000, 9999);
				$app_config = getAppConfig();
				// $number = str_replace('-', '', $users->mobile_number); //to remove the '-'
				$number=$phoneNumber;

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
					/*$m = $client->account->messages->sendMessage(
						$from_number, // the text will be sent from your Twilio number
						$number, // the phone number the text will be sent to
						$message // the body of the text message
					);*/
					                $m =  $client->messages->create($number, array('from' => $from_number, 'body' => $message));


					$users->phone_otp = $otp;
					$users->modified_date = date("Y-m-d");
					$users->save();
					$token = JWTAuth::fromUser($users, array('exp' => 200000000000));
					$result = array("status" => 1, "message" => trans('messages.New otp has been sent to your register mobile number.'));
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



	public function salesForgotPassword(Request $data) {
		$rules = array(
			'language' => 'required',
			'phoneNumber' => 'required',
			'countryCode' => 'required',
		);
		$post_data = $data->all();
		

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
			$result = array("status" => 0, "Error" => trans('messages.Error List'), "message" => $errors);
		} else {
			//$check_auth = JWTAuth::toUser($post_data['token']);
			$post_data = $data->all();
			$phone = $post_data['phoneNumber'];
			$countryCode = $post_data['countryCode'];
			$phoneNumber = $countryCode.$phone;
			$user_details = DB::table('salesperson')
				->select('id')
				->where('mobile_number', '=', $phone)
				->first();
		 	//print_r($user_details); exit;
			$result = array("status" => 2, "message" => trans('messages.Mobile Number is not register'));
			if (count($user_details) > 0) {
				$users =Salesperson::find($user_details->id);
						//print_r($users);exit();

				$otp = rand(1000, 9999);
				$otp_unique = str_random(8);
				$pass_string = md5($otp_unique);
				$app_config = getAppConfig();
				// $number = str_replace('-', '', $users->mobile_number); //to remove the '-'
				$number = $phoneNumber;
				$message = 'You have received OTP password for ' . getAppConfig()->site_name . '. Your OTP code is ' . $otp . '. This code can be used only once and dont share this code with anyone.';
				/*$twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
				$twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
				$from_number = "+14783471785";

				$client = new Services_Twilio($twilo_sid, $twilio_token);*/
				$twilo_sid = TWILIO_ACCOUNTSID;
                $twilio_token = TWILIO_AUTHTOKEN;
                $from_number = TWILIO_NUMBER;
                $client = new Client($twilo_sid, $twilio_token);
			
				try {
					/*$m = $client->account->messages->sendMessage(
						$from_number, // the text will be sent from your Twilio number
						$number, // the phone number the text will be sent to
						$message // the body of the text message
					);*/
					                $m =  $client->messages->create($number, array('from' => $from_number, 'body' => $message));

					//print_r($m);exit;

					$users->phone_otp = $otp;
					$users->otp_unique = $pass_string;
					$users->updated_at = date("Y-m-d H:i:s");
					$users->save();
					$token = JWTAuth::fromUser($users, array('exp' => 200000000000));
					$result = array("status" => 1,/* "otpUnique" => $pass_string,*/ "userOtp" => $otp, "message" => trans('messages.New OTP has been sent to your register mobile number.'));
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

	public function salesVerifyOtp(Request $data) {

		$post_data = $data->all();

		$rules = array(
			'otp' => 'required',

		);

		if ($post_data['language'] == "ar" || $post_data['language'] == 2) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }

		//$check_auth = JWTAuth::toUser($post_data['token']);
		$user_details = DB::table('salesperson')
			->select('id')
			->where('mobile_number', '=', $post_data['phone'])
			->where('phone_otp', '=', $post_data['otp'])
			->first();
		$result = array("status" => 0, "message" => trans('messages.Verification Failed kindly check your otp '));
		if (count($user_details) > 0) {

			$user_data =Salesperson::find($user_details->id);
			
			$user_data->is_verified = 1;
			$user_data->save();
			$token = JWTAuth::fromUser($user_data, array('exp' => 200000000000));

			$result = array("status" => 1,  "message" => trans('messages.OTP Verified Successfully,Please login.'));

		}
		return json_encode($result);
	}
	

	public function salesPersonLogout(Request $data) {
		$post_data = $data->all();
		// if ($post_data['language'] == 2) {
		// 	App::setLocale('ta');
		// } else {
		// 	App::setLocale('en');
		// }
		$rules = [
			// 'outlet_key' => ['required'],
			// 'language' => ['required'],
		];
		$errors = $result = array();

		$validator = app('validator')->make($post_data, $rules);

		if ($validator->fails()) {
			foreach ($validator->errors()->messages() as $key => $value) {
				$errors[] = is_array($value) ? implode(',', $value) : $value;
			}
			$errors = implode(", \n ", $errors);
			$result = array("status" => 0,  "message" => trans('messages.Error List'), "detail" => $errors);
		}
					$table=DB::table('salesPerson')
						->select('id')
						->where('id',$post_data['id']);

		 if(count($table)>0){
			// //$check_auth = JWTAuth::toUser($post_data['token']);
			 //$post_data['outlet_key'] =$post_data['id'];
			//  $user_det = Outlets::find($post_data['outlet_key']);
			// $user_det['android_device_id'] = '';
			// $user_det['android_device_token'] = '';
			// $user_det['ios_device_id'] = '';
			// $user_det['ios_device_token'] = '';
			// //$user_det->save();

			$result = array("status" => 1, "message" => trans('messages.Logged out successfully'));
		}
		
		return json_encode($result, JSON_UNESCAPED_UNICODE);

	}


	public function salespersonAssigned(Request $data)
	{

		$rules = array(
			'language' => 'required',
			'salesPersonId' => 'required',
			'orderId' => 'required',
		);
		$post_data = $data->all();
		

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
			$result = array("status" => 0, "Error" => trans('messages.Error List'), "message" => $errors);
		} else {

			$salesPersonId=$post_data['salesPersonId'];
			$orderId=$post_data['orderId'];

			$notify = DB::table('orders')
            ->select('orders.assigned_time', 'salesperson.android_device_token', 'salesperson.ios_device_token', 'orders.salesperson_id','orders.order_key_formated','order_status.name as status_name','salesperson.name as salesPersonName')
            
            ->Join('order_status','order_status.id', '=', 'orders.order_status')
            ->Join('salesperson','salesperson.id', '=', 'orders.salesperson_id')
            ->where('orders.id', '=', (int) $orderId)
            ->where('orders.salesperson_id', '=', (int) $salesPersonId)
            ->get();
            //print_r($notify);exit();
        if (count($notify) > 0 && $notify[0]->login_type != 1 ) {
       			$notifys = $notify[0];

            if($order_status==1){
                $order_title = '' . 'Order Placed';
                $description = '' . 'Your placed order successfully';
                $orderStatus =1;
            }
            if($order_status==10){
                $order_title = '' . 'Processing Your Order';
                $description = '' .$notifys->salesPersonName.' is processing your order '.$notifys->order_key_formated;
                $orderStatus =10;
            }
            if($order_status==18){
                $order_title = '' . 'Order Packed';
                $description = '' .$notifys->salesPersonName.' has packed your order '.$notifys->order_key_formated;
                $orderStatus =18;
            }

            if($order_status==11){
                $order_title = '' . 'Order Cancelled';
                $description = '' . 'Your placed order is cancelled successfully';
                $orderStatus =11;
            }

            if($order_status==12){
                $referral =getreferral();
                $order_title = '' . 'Order Delivered';
                $description = '' .$notifys->driverName .' has delivered your order '.$notifys->order_key_formated;
                $orderStatus =12;
            }

            if($order_status==14){
                $order_title = '' . 'Order Shipped';
                $description = '' . 'your placed order is shipped successfully';
                $orderStatus =14;
            }

            if($order_status==19){
                $order_title = '' . 'Order Picked By Driver';
                $description = '' . $notifys->driverName .' has picked your order and on his way for delivery '.$notifys->order_key_formated;
                $orderStatus =19;
            }

            if($order_status==31){
                $order_title = '' . 'Order Accepted By Driver';
                $description = '' . $notifys->driverName .' Accepted your order and your order will deliver shortly';
                $orderStatus =31;
            }

            if($order_status==32){
                $order_title = '' . 'Driver Arrived';
                $description = '' . $notifys->driverName. ' has arrived at store to pick your order '.$notifys->order_key_formated;
                $orderStatus =32;
            }  

            if($order_status==34){
                $order_title = '' . 'Sales Person Assigned';
                $description = '' .$notifys->salesPersonName.' is Assigned for your order '.$notifys->order_key_formated;
                $orderStatus =34;
            }
        }

         
        	if($notifys->login_type == 2){
                $token = $notifys->android_device_token;
            }else if($notifys->login_type == 3)
            {
                $token = $notifys->ios_device_token;
            }
            $token =isset($token)?$token:'';

           ;
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

	}


	/*public function saleUpdateProfile(Request $data){
		print_r("coming");exit();
		$post_data = $data->all();
		$id = $post_data['id'];
		if ($post_data['language'] == 2) {
			App::setLocale('ar');
		} else {
			App::setLocale('en');
		}
		$rules = [
			'first_name' => ['required', 'max:56'],
			'email' => ['required', 'email', 'max:250', 'unique:users,email,' . $id . ',id'],
			'last_name' => ['required', 'max:56'],
			'phone' => ['required', 'max:50', 'regex:/^\+91\d{10}$/'],
			'gender' => ['required'],
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
			$result = array("response" => array("httpCode" => 400, "status" => 2, "Error" => trans("messages.Error List"), "message" => $errors));
		} else {
			$check_auth = JWTAuth::toUser($post_data['token']);

			
			$users = Salesperson::find($id)
			$users->first_name = $post_data['first_name'];
			$users->last_name = $post_data['last_name'];
			$users->email = $post_data['email'];
			$users->name = $post_data['first_name'] . " " . $post_data['last_name'];
			$users->mobile = $post_data['phone'];
			$users->gender = $post_data['gender'];
			$users->social_title = ($post_data['gender'] == 'F') ? "Ms." : "Mr.";
			
			$users->updated_date = date("Y-m-d H:i:s");
			$users->android_device_id = isset($post_data['android_device_id']) ? $post_data['android_device_id'] : '';
			$users->android_device_token = isset($post_data['android_device_token']) ? $post_data['android_device_token'] : '';
			$users->ios_device_id = isset($post_data['ios_device_id']) ? $post_data['ios_device_id'] : '';
			$users->ios_device_token = isset($post_data['ios_device_token']) ? $post_data['ios_device_token'] : '';
			$users->save();


			$list = array("userId" => $users->id, "email" => $users->email, "name" => $users->name, "socialTitle" => !empty($users->social_title) ? $users->social_title : '', "firstName" => isset($users->first_name) ? $users->first_name : "", "lastName" => isset($users->last_name) ? $users->last_name : "", "image" => isset($users->image) ? $users->image : "", "mobile" => isset($users->mobile) ? $users->mobile : "", "facebookId" => isset($users->facebook_id) ? $users->facebook_id : "", "phoneVerify" => isset($users->phone_verify) ? $user_data->phone_verify : "");

			$result = array("httpCode" => 200, "status" => 1, "message" => trans("messages.User information has been updated successfully"),"data"=>[$list] );

		

		}
		
		return json_encode($result, JSON_UNESCAPED_UNICODE);

	}*/
	public function salespersonOrders (Request $data){

		$post_data = $data->all();

		$rules = array(
			'salesPersonId' => 'required',
			'language' => 'required',

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
			$language_id = isset($post_data['language_id'])?$post_data['language_id']:1;
  			//$skipSize = $post_data['skipSize'];
           // $pageSize = $post_data['pageSize'];
	        $salesPersonOrders=DB::table('salesperson_orders')
	         	//->limit($pageSize)
              //  ->skip($skipSize)
	            ->select('salesperson_orders.salesperson_id','salesperson_orders.order_id','orders.order_status','salesperson_orders.assigned_time')

	            ->leftJoin('orders', 'orders.salesperson_id', '=', 'salesperson_orders.salesperson_id')

	           	->where('orders.order_status','=',34)

	            ->where('salesperson_orders.salesperson_id','=',$post_data['salesPersonId'])

	           // ->where('salesperson_orders.created_at','=',date("Y-m-d"))	            
	           	->where('salesperson_orders.salesmanPackStatus','=',1)
	           	->orderby('salesperson_orders.created_at', 'desc')
	            ->LIMIT(1)
	            ->get();
	       	//print_r($salesPersonOrders);exit;
	        if($salesPersonOrders){
		        $produceInfo =$aaaaa = array();
				foreach ($salesPersonOrders as $key => $value) {

					$details = orderdetails($value->order_id,'','',$language_id,'',0);
				    $salesPersonOrders[$key]->orderProductList = $details['produceInfo'];
				
			        $details['orderData']->assigned_time = isset($salesPersonOrders[0]->assigned_time)?$salesPersonOrders[0]->assigned_time:'';

			         $sub=DB::select("select SUM(item_unit * item_cost) as subTotal from orders_info where order_id = $value->order_id");

			         if (count($sub)>0) {
			            $subb=array();
			            foreach ($sub as $keyy => $valuu) {
			                $subb[$keyy]['subTotal'] = $valuu->subtotal;
			            }
			        }
					$details['orderData']->subTotal = (int)$valuu->subtotal ;
			        $salesPersonOrders[$key]->orderData = $details['orderData'];

				}
				$result = array("status" => 1, "message" => "order items","orderAvailable"=>1, "orderProductList" => $details['produceInfo'], "orderData" => $details['orderData']);
			}else{
	        		$result = array("status" => 2, "message" => "no items found","orderAvailable"=>0);
				}
			}
		return json_encode($result);
	}

	public function salespersonOrders_old(Request $data){
		//print_r("expression");exit();
		$post_data = $data->all();

		$rules = array(
			'salesPersonId' => 'required',
			'language' => 'required',

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
			$language_id = isset($post_data['language_id'])?$post_data['language_id']:1;
  			//$skipSize = $post_data['skipSize'];
           // $pageSize = $post_data['pageSize'];

	        $salesPersonOrders=DB::table('salesperson_orders')
	         	//->limit($pageSize)
              //  ->skip($skipSize)
	            ->select('salesperson_orders.salesperson_id','salesperson_orders.order_id','orders.order_status','salesperson_orders.assigned_time')

	            ->leftJoin('orders', 'orders.salesperson_id', '=', 'salesperson_orders.salesperson_id')

	           	->where('orders.order_status','=',34)

	            ->where('salesperson_orders.salesperson_id','=',$post_data['salesPersonId'])

	           // ->where('salesperson_orders.created_at','=',date("Y-m-d"))	            
	           	->where('salesperson_orders.salesmanPackStatus','=',1)
	           	->orderby('salesperson_orders.created_at', 'desc')
	            ->LIMIT(1)
	            ->get();
	       // print_r($salesPersonOrders);exit;
	        if($salesPersonOrders){
		        $produceInfo =$aaaaa = array();
				foreach ($salesPersonOrders as $key => $value) {
					$query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and p.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
			        $wquery = '"weight_classes_infos"."lang_id" = (case when (select count(weight_classes_infos.lang_id) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and weight_classes.id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
					$order_items = DB::select('SELECT p.product_image, pi.description,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.id as order_id,o.invoice_id,pi.product_name,pi.description,o.coupon_amount,weight_classes_infos.title,weight_classes_infos.unit as unit_code,o.order_key_formated,p.weight,oi.replacement_product_id,oi.id,oi.additional_comments,oi.adjust_weight_qty,oi.pack_status,p.adjust_weight,o.id
					        FROM orders o
					        LEFT JOIN orders_info oi ON oi.order_id = o.id
					        LEFT JOIN products p ON p.id = oi.item_id
					        LEFT JOIN products_infos pi ON pi.id = p.id
					        LEFT JOIN weight_classes ON weight_classes.id = p.weight_class_id
					        LEFT JOIN weight_classes_infos ON weight_classes_infos.id = weight_classes.id
				        	where ' . $query . ' AND ' . $wquery . ' AND o.id = ? ORDER BY oi.id', array($value->order_id));
						$k =0;
						$subtot =0;
						//print_r($order_items);exit();
					    foreach ($order_items as $ke => $data) {
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
				              
				                $order_info=DB::select("select SUM(item_unit) as item_unit from orders_info where order_id = $data->order_id and item_id=$data->product_id");

				                if (count($order_info)>0) {
				                    $orderInfoArray=array();

				                    foreach ($order_info as $keys => $values) {
				                        $orderInfoArray[$keys]['itemCount']= $values->item_unit;
				                    }
				                }
				  
				                //$produceInfo[$k]['orderUnit'] = $values->item_unit;
				                $produceInfo[$k]['orderUnit'] = $data->item_unit;


				                $sum= DB::select("select   (item_cost * item_unit) as total  from orders_info where order_id = $data->order_id and item_id=$data->product_id");

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
				         
				                $weight = isset($data->weight)?$data->weight:$data->weight;
				                $produceInfo[$k]['weight'] =$weight;
				                $adjust_weight_qty= isset($data->adjust_weight_qty)?$data->adjust_weight_qty:"";
				                $weight_last = isset($data->adjust_weight_qty)?$data->adjust_weight_qty:$data->weight;
				               /* if ($data->adjust_weight == 1) {
				                    $qntyweight = $weight * $values->item_unit ;
				                    $produceInfo[$k]['weight'] = $qntyweight;
				                    $weight_last = $qntyweight+$adjust_weight_qty;
				                } else {
				                    $weight_last =$weight_last *$values->item_unit;
				                }

				                $itemprice =  $data->item_cost / $data->weight;
				                $amount =$weight_last * $itemprice;
				               
				                $produceInfo[$k]['totalAmount'] = $amount;

				           
				                $produceInfo[$k]['adjustmentWeight'] = $adjust_weight_qty;
				               
				                $produceInfo[$k]['adjust'] =0 ;
				                if ($data->adjust_weight_qty !=0 || $data->adjust_weight_qty !=null) {
				                    $produceInfo[$k]['adjust'] = 1;
				                }*/
				                if ($data->adjust_weight == 1) {
				                    $qntyweight = $weight * $values->item_unit ;
				                    $weight_last = $adjust_weight_qty;
				                } else {
				                    $weight_last =$weight_last *$values->item_unit;
				                }
				                $itemprice =  $data->item_cost / $data->weight;
				                $amount =$weight_last * $itemprice;
				               
				                if($amount !=0){$amounts = $amount;}else{$amounts= $valu->total;}
				                $produceInfo[$k]['totalAmount'] = $amounts;
				                $produceInfo[$k]['adjustmentWeight'] = $adjust_weight_qty;
				                $produceInfo[$k]['adjust'] =0 ;
				                $produceInfo[$k]['netWeight'] =$data->weight * $data->item_unit ;

				                if ($data->adjust_weight_qty !=0 || $data->adjust_weight_qty !=null) {
				                    $produceInfo[$k]['adjust'] = 1;
				                }
				               // print_r($amounts);echo "<br>";

				                 $subtot += $amounts;

				            	$k++;
				            }
				        }
				       // exit();
				       // print_r($subtot);exit();
				    $salesPersonOrders[$key]->orderProductList = $produceInfo;
				   // print_r($produceInfo);exit();
	   				$query2 = 'pgi.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and pg.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
	        		$oquery = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
				    $delivery_details = DB::select('SELECT o.delivery_instructions,
				        ua.address as user_contact_address,
				        o.customer_id as user_id,
				        ua.latitude as user_latitude,
				        ua.longitude as user_longitude,
				        pg.id as payment_gateway_id,
				        pgi.name,
				        o.total_amount,
				        o.order_comments,
				        o.delivery_instructions,
				        o.salesperson_id,
				        sals.name as salespersonName,
				        o.delivery_charge,
				        o.service_tax,
				        dti.start_time,
				        end_time,
				        o.created_date,
				        o.delivery_date,
				        o.order_type,
				        out_infos.contact_address,out.latitude as outlet_latitude,out.longitude as outlet_longitude,o.coupon_amount, u.email,o.driver_ids,dr.ratings,tr.ratings as rating,u.name as customer_name,drivers.first_name as driver_name,vendors_infos.vendor_name, vendors.logo_image,vendors.contact_address,vendors.contact_email,o.created_date,o.order_status,order_status.name,payment_gateways_info.name as payment_gateway_name,o.outlet_id,o.vendor_id,o.order_key_formated,o.invoice_id
					    FROM orders o LEFT JOIN user_address ua ON ua.id = o.delivery_address
					     LEFT JOIN users u ON u.id = ua.user_id
					    left join driver_reviews dr on dr.customer_id = o.customer_id
					    left join drivers  on drivers.id = o.driver_ids
					    left join salesperson sals on sals.id = o.salesperson_id
					    left join outlet_reviews tr on tr.customer_id = o.customer_id
					    left join payment_gateways pg on pg.id = o.payment_gateway_id
					    left join payment_gateways_info pgi on pgi.payment_id = pg.id
					    left join delivery_time_slots dts on dts.id=o.delivery_slot
					    left join delivery_time_interval dti on dti.id = dts.time_interval_id
					    left join outlets out on out.id = o.outlet_id
					    left join vendors vendors on vendors.id = o.vendor_id
	        			left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
	        			left join payment_gateways payment_gateways on payment_gateways.id = o.payment_gateway_id
	        			left join payment_gateways_info payment_gateways_info on payment_gateways_info.payment_id = payment_gateways.id
	        		

	        			left join order_status order_status on order_status.id = o.order_status

					    left join outlet_infos out_infos on out_infos.id = out.id where
				        ' . $query2 . ' AND ' . $oquery . ' AND o.id = ?', array($value->order_id));

				   // print_r($delivery_details);exit();
				    foreach ($delivery_details as $k => $v) {
			            $logo_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
			            if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $v->logo_image) && $v->logo_image != '') {
			                $logo_image = url('/assets/admin/base/images/vendors/logos/' . $v->logo_image);
			            }
			            $delivery_details[$k]->logo_image = $logo_image;
			            $delivery_details[$k]->start_time = ($v->start_time != '') ? $v->start_time : '';
			            $delivery_details[$k]->end_time = ($v->end_time != '') ? $v->end_time : '';
			        }
				    $orderData = new \stdClass();
			        $orderData->orderId = $value->order_id;

			        $order_info=DB::select("select SUM(item_unit) as item_unit from orders_info where order_id = $value->order_id");

			        if (count($order_info)>0) {
			            $orderInfoArray=array();
			            foreach ($order_info as $keys => $vall) {
			                $orderInfoArray[$keys]['itemCount']= $vall->item_unit;
			            }
			        }
			        $orderData->orderQuantity = $vall->item_unit;
			        $orderData->orderComments = isset($delivery_details[0]->order_comments)?$delivery_details[0]->order_comments:"";
			        $orderData->salesFleetId = isset($delivery_details[0]->salesperson_id) ? $delivery_details[0]->salesperson_id:"" ;
			        $orderData->salesFleetName = isset($delivery_details[0]->salespersonname) ? $delivery_details[0]->salespersonname:"";
			        $orderData->outletName = isset($delivery_details[0]->vendor_name) ? $delivery_details[0]->vendor_name:"";
			        $orderData->vendorLogo = isset($delivery_details[0]->logo_image) ? $delivery_details[0]->logo_image:"";
			        $orderData->outletAddress =  isset($delivery_details[0]->contact_address) ? $delivery_details[0]->contact_address:"";
			        $orderData->contactEmail = isset($delivery_details[0]->contact_email) ? $delivery_details[0]->contact_email:"";
			        $orderData->createdDate =  isset($delivery_details[0]->created_date) ? $delivery_details[0]->created_date:"";
			        $orderData->orderStatus = isset($delivery_details[0]->order_status) ? $delivery_details[0]->order_status:"";
			        $orderData->name = isset($delivery_details[0]->name) ? $delivery_details[0]->name:"";
			        $orderData->paymentGatewayName = isset($delivery_details[0]->payment_gateway_name) ? $delivery_details[0]->payment_gateway_name:"";
			        $orderData->outletId = isset($delivery_details[0]->outlet_id) ? $delivery_details[0]->outlet_id:"";
			        $orderData->vendorId =isset($delivery_details[0]->vendor_id) ? $delivery_details[0]->vendor_id:"";

			        $orderData->orderKeyFormated = isset($delivery_details[0]->order_key_formated) ? $delivery_details[0]->order_key_formated:"";		
			        $orderData->invoiceId = isset($delivery_details[0]->invoice_id) ? $delivery_details[0]->invoice_id:"";
			        $orderData->startTime = isset($delivery_details[0]->start_time) ? $delivery_details[0]->start_time:"";
			        $orderData->endTime = isset($delivery_details[0]->end_time) ? $delivery_details[0]->end_time:"";
			        $orderData->deliveryAddress = isset($delivery_details[0]->user_contact_address)?$delivery_details[0]->user_contact_address:'';
			        $orderData->assigned_time = isset($salesPersonOrders[0]->assigned_time)?$salesPersonOrders[0]->assigned_time:'';

			        

			         $sub=DB::select("select SUM(item_unit * item_cost) as subTotal from orders_info where order_id = $value->order_id");

			         if (count($sub)>0) {
			            $subb=array();
			            foreach ($sub as $keyy => $valuu) {
			                $subb[$keyy]['subTotal'] = $valuu->subtotal;
			               //print_r($subb);exit();

			            }


			        }


					//$orderData->subTotal = (int)$valuu->subtotal ;
					$orderData->subTotal = (int)$subtot ;

			        $salesPersonOrders[$key]->orderData = $orderData;

				}

				// if(count($produceInfo)==0){
			
				// $result = array("status" => 1, "message" => "order items","orderAvailable"=>0, "orderProductList" => $produceInfo, "orderData" => $orderData); //, "mob_delivery_details" => $delivery
				// }else{

					$result = array("status" => 1, "message" => "order items","orderAvailable"=>1, "orderProductList" => $produceInfo, "orderData" => $orderData);
				//}
		}else{
        		$result = array("status" => 2, "message" => "no items found","orderAvailable"=>0);
			}
		}
		return json_encode($result);
	}


	public function salespersonOrders_copy (Request $data){

		$post_data = $data->all();

		$rules = array(
			'salesPersonId' => 'required',
			'language' => 'required',

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
			$language_id = isset($post_data['language_id'])?$post_data['language_id']:1;
  			/*$skipSize = $post_data['skipSize'];
            $pageSize = $post_data['pageSize'];*/
	        $salesPersonOrders=DB::table('salesperson_orders')
	         	/*->limit($pageSize)
                ->skip($skipSize)*/
	            ->select('salesperson_orders.salesperson_id','salesperson_orders.order_id','orders.order_status')
	            ->leftJoin('orders', 'orders.salesperson_id', '=', 'salesperson_orders.salesperson_id')
	            ->where('salesperson_orders.salesperson_id','=',$post_data['salesPersonId'])
	            ->where('orders.salesperson_id','=',$post_data['salesPersonId'])
	            ->where('orders.order_status','=',34)
	            ->where('salesperson_orders.salesmanPackStatus','=',1)
	            ->orderby('salesperson_orders.assigned_time', 'desc')
	            ->LIMIT(1)
	            ->get();
	        print_r($salesPersonOrders);exit;
	        if($salesPersonOrders){
		        $produceInfo =$aaaaa = array();
				foreach ($salesPersonOrders as $key => $value) {
					$query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = ' . $language_id . ' and p.id = products_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
			        $wquery = '"weight_classes_infos"."lang_id" = (case when (select count(weight_classes_infos.lang_id) as totalcount from weight_classes_infos where weight_classes_infos.lang_id = ' . $language_id . ' and weight_classes.id = weight_classes_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
					$order_items = DB::select('SELECT p.product_image, pi.description,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.id as order_id,o.invoice_id,pi.product_name,pi.description,o.coupon_amount,weight_classes_infos.title,weight_classes_infos.unit as unit_code,o.order_key_formated,p.weight,oi.replacement_product_id,oi.id,oi.additional_comments,oi.adjust_weight_qty,oi.pack_status,p.adjust_weight,o.id
					        FROM orders o
					        LEFT JOIN orders_info oi ON oi.order_id = o.id
					        LEFT JOIN products p ON p.id = oi.item_id
					        LEFT JOIN products_infos pi ON pi.id = p.id
					        LEFT JOIN weight_classes ON weight_classes.id = p.weight_class_id
					        LEFT JOIN weight_classes_infos ON weight_classes_infos.id = weight_classes.id
				        	where ' . $query . ' AND ' . $wquery . ' AND o.id = ? ORDER BY oi.id', array($value->order_id));
						$k =0;
					    foreach ($order_items as $ke => $data) {
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
				                // print_r($values->item_unit);exit();
				  
				                $produceInfo[$k]['orderUnit'] = $values->item_unit;


				                $sum= DB::select("select   (item_cost * item_unit) as total  from orders_info where order_id = $data->order_id and item_id=$data->product_id");

				                if (count($sum)>0) {
				                    $sumArray=array();

				                    foreach ($sum as $ke => $valu) {
				                        $sumArray[$ke]['total']= $valu->total;
				                    }
				                }
				                $produceInfo[$k]['totalAmount'] = $valu->total;
				                $produceInfo[$k]['invoiceId'] = $data->invoice_id;
				                $produceInfo[$k]['productName'] = $data->product_name;
				                $produceInfo[$k]['couponAmount'] = $data->coupon_amount;
				                $produceInfo[$k]['title'] = $data->title;
				                $produceInfo[$k]['unitCode'] = $data->unit_code;
				                $produceInfo[$k]['orderKeyFormated'] = $data->order_key_formated;
				                $produceInfo[$k]['weight'] = $data->weight;
				             	// $produceInfo[$k]['invoicePdf'] = $data->invoic_pdf;
				         
				                $weight = isset($data->weight)?$data->weight:$data->weight;
				                $produceInfo[$k]['weight'] =$weight;
				                $adjust_weight_qty= isset($data->adjust_weight_qty)?$data->adjust_weight_qty:"";
				                $weight_last = isset($data->adjust_weight_qty)?$data->adjust_weight_qty:$data->weight;
				                if ($data->adjust_weight == 1) {
				                    //  print_r($values->item_unit);exit;
				                    $qntyweight = $weight * $values->item_unit ;
				                    //print_r($adjust_weight_qty);exit;
				                    $produceInfo[$k]['weight'] = $qntyweight;
				                    $weight_last = $qntyweight+$adjust_weight_qty;
				                } else {
				                    $weight_last =$weight_last *$values->item_unit;
				                }

				                $itemprice =  $data->item_cost / $data->weight;
				                $amount =$weight_last * $itemprice;
				                // print_r($weight_last);echo"<br>";
				                //print_r($itemprice);exit;

				                $produceInfo[$k]['totalAmount'] = $amount;

				           
				                $produceInfo[$k]['adjustmentWeight'] = $adjust_weight_qty;
				                // print_r($weight);echo"<br>";print_r($tot);echo"<br>";print_r($amount);exit;
				                $produceInfo[$k]['adjust'] =0 ;
				                if ($data->adjust_weight_qty !=0 || $data->adjust_weight_qty !=null) {
				                    $produceInfo[$k]['adjust'] = 1;
				                }
				                $item_price =
				            	$k++;
				            }
				        }
				    $salesPersonOrders[$key]->orderProductList = $produceInfo;

	   				$query2 = 'pgi.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = ' . $language_id . ' and pg.id = payment_gateways_info.payment_id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
	        		$oquery = 'out_infos.language_id = (case when (select count(*) as totalcount from outlet_infos where outlet_infos.language_id = ' . $language_id . ' and out.id = outlet_infos.id) > 0 THEN ' . $language_id . ' ELSE 1 END)';
				    $delivery_details = DB::select('SELECT o.delivery_instructions,
				        ua.address as user_contact_address,
				        o.customer_id as user_id,
				        ua.latitude as user_latitude,
				        ua.longitude as user_longitude,
				        pg.id as payment_gateway_id,
				        pgi.name,
				        o.total_amount,
				        o.order_comments,
				        o.delivery_instructions,
				        o.salesperson_id,
				        sals.name as salespersonName,
				        o.delivery_charge,
				        o.service_tax,
				        dti.start_time,
				        end_time,
				        o.created_date,
				        o.delivery_date,
				        o.order_type,
				        out_infos.contact_address,out.latitude as outlet_latitude,out.longitude as outlet_longitude,o.coupon_amount, u.email,o.driver_ids,dr.ratings,tr.ratings as rating,u.name as customer_name,drivers.first_name as driver_name,vendors_infos.vendor_name, vendors.logo_image,vendors.contact_address,vendors.contact_email,o.created_date,o.order_status,order_status.name,payment_gateways_info.name as payment_gateway_name,o.outlet_id,o.vendor_id,o.order_key_formated,o.invoice_id
					    FROM orders o LEFT JOIN user_address ua ON ua.id = o.delivery_address
					     LEFT JOIN users u ON u.id = ua.user_id
					    left join driver_reviews dr on dr.customer_id = o.customer_id
					    left join drivers  on drivers.id = o.driver_ids
					    left join salesperson sals on sals.id = o.salesperson_id
					    left join outlet_reviews tr on tr.customer_id = o.customer_id
					    left join payment_gateways pg on pg.id = o.payment_gateway_id
					    left join payment_gateways_info pgi on pgi.payment_id = pg.id
					    left join delivery_time_slots dts on dts.id=o.delivery_slot
					    left join delivery_time_interval dti on dti.id = dts.time_interval_id
					    left join outlets out on out.id = o.outlet_id
					    left join vendors vendors on vendors.id = o.vendor_id
	        			left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
	        			left join payment_gateways payment_gateways on payment_gateways.id = o.payment_gateway_id
	        			left join payment_gateways_info payment_gateways_info on payment_gateways_info.payment_id = payment_gateways.id
	        		

	        			left join order_status order_status on order_status.id = o.order_status

					    left join outlet_infos out_infos on out_infos.id = out.id where
				        ' . $query2 . ' AND ' . $oquery . ' AND o.id = ?', array($value->order_id));
				    foreach ($delivery_details as $k => $v) {
			            $logo_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
			            if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $v->logo_image) && $v->logo_image != '') {
			                $logo_image = url('/assets/admin/base/images/vendors/logos/' . $v->logo_image);
			            }
			            $delivery_details[$k]->logo_image = $logo_image;
			            $delivery_details[$k]->start_time = ($v->start_time != '') ? $v->start_time : '';
			            $delivery_details[$k]->end_time = ($v->end_time != '') ? $v->end_time : '';
			        }
				    $orderData = new \stdClass();
			        $orderData->orderId = $value->order_id;

			        $order_info=DB::select("select SUM(item_unit) as item_unit from orders_info where order_id = $value->order_id");

			        if (count($order_info)>0) {
			            $orderInfoArray=array();
			            foreach ($order_info as $keys => $vall) {
			                $orderInfoArray[$keys]['itemCount']= $vall->item_unit;
			            }
			        }
			        $orderData->orderQuantity = $vall->item_unit;
			        $orderData->orderComments = isset($delivery_details[0]->order_comments)?$delivery_details[0]->order_comments:"";
			        $orderData->salesFleetId = isset($delivery_details[0]->salesperson_id) ? $delivery_details[0]->salesperson_id:"" ;
			        $orderData->salesFleetName = isset($delivery_details[0]->salespersonname) ? $delivery_details[0]->salespersonname:"";
			        $orderData->outletName = isset($delivery_details[0]->vendor_name) ? $delivery_details[0]->vendor_name:"";
			        $orderData->vendorLogo = isset($delivery_details[0]->logo_image) ? $delivery_details[0]->logo_image:"";
			        $orderData->outletAddress =  isset($delivery_details[0]->contact_address) ? $delivery_details[0]->contact_address:"";
			        $orderData->contactEmail = isset($delivery_details[0]->contact_email) ? $delivery_details[0]->contact_email:"";
			        $orderData->createdDate =  isset($delivery_details[0]->created_date) ? $delivery_details[0]->created_date:"";
			        $orderData->orderStatus = isset($delivery_details[0]->order_status) ? $delivery_details[0]->order_status:"";
			        $orderData->name = isset($delivery_details[0]->name) ? $delivery_details[0]->name:"";
			        $orderData->paymentGatewayName = isset($delivery_details[0]->payment_gateway_name) ? $delivery_details[0]->payment_gateway_name:"";
			        $orderData->outletId = isset($delivery_details[0]->outlet_id) ? $delivery_details[0]->outlet_id:"";
			        $orderData->vendorId =isset($delivery_details[0]->vendor_id) ? $delivery_details[0]->vendor_id:"";

			        $orderData->orderKeyFormated = isset($delivery_details[0]->order_key_formated) ? $delivery_details[0]->order_key_formated:"";		
			        $orderData->invoiceId = isset($delivery_details[0]->invoice_id) ? $delivery_details[0]->invoice_id:"";
			        $orderData->startTime = isset($delivery_details[0]->start_time) ? $delivery_details[0]->start_time:"";
			        $orderData->endTime = isset($delivery_details[0]->end_time) ? $delivery_details[0]->end_time:"";
			        $orderData->deliveryAddress = isset($delivery_details[0]->user_contact_address)?$delivery_details[0]->user_contact_address:'';

			        $salesPersonOrders[$key]->orderData = $orderData;

				}
				$result = array("status" => 1, "message" => "order items", "details" => $salesPersonOrders); //, "mob_delivery_details" => $delivery
			}else{
        		$result = array("status" => 2, "message" => "no items found");
			}
		}
		return json_encode($result);
	}



	public function assignDriver_copy(Request $data) {

		
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
			if($new_orders->driver_ids == '' || $new_orders->driver_ids !== '' && $new_orders->order_status == 18){
				$driver_settings = Driver_settings::find(1);

				$orders = DB::table('orders')
					->select('driver_ids')
					->where('orders.id', $data_all['order_id'])
					->first();
				//print_r($orders);exit();	
				$driver_ids = $orders->driver_ids;

				$new_orders->driver_ids = /*$driver_ids . */$data_all['driver'];
				$assigned_time = strtotime("+ " . $driver_settings->order_accept_time . " minutes", strtotime(date('Y-m-d H:i:s')));
				$update_assign_time = date("Y-m-d H:i:s", $assigned_time);
				$new_orders->assigned_time = $update_assign_time;
				$new_orders->save();

				$order_title = 'order assigned to you';
				$driver_detail = Drivers::find($data_all['driver']);
				

				//$affected = DB::update('update drivers set driver_status = 2 where id = ?', array($data_all['driverId']));
				

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
					curl_close($ch);
				}

				$datetime1 = new DateTime();
                $datetime2 = new DateTime($orders->assigned_time);
                $diff = $datetime1->getTimestamp()-$datetime2->getTimestamp() ;

                //print_r( $orders->assigned_time) ;exit;


                if($diff < 60){

                $result = array("status" => 1, "message" => trans('messages.Driver assigned successfully'));
                }

                /*else{

                $data[$key]['driverId']="";
                $data[$key]['driverName']="";

                }*/
				
			}else
			{
				
				$result = array("status"=>2, "message" => trans('messages.Driver Already assigned'));
			}
		}
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}



    public function salespersonStatusChange(Request $data)
    {

        $post_data = $data->all();
        $rules = array(
			'salespersonId' => 'required',
			'status' => 'required',

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
                    $status = $post_data['status'];
                    $salespersonId = $post_data['salespersonId'];

                    $statusChange = DB::table('salesperson')
                                 ->where('salesperson.id' , $salespersonId)
                                 ->update(['status'=> $status]);

                    if($statusChange)
                     {
                        $result = array("status" => 1,  "message" => trans("messages.Salesperson Status Changed Successfully"));
                     }else{

                        $result = array("status" => 2,  "message" => trans("messages.Salesperson Id is Wrong"));
                     }            

                }

              return json_encode($result, JSON_UNESCAPED_UNICODE);


    }

     public function salespersonStatus(Request $data)
    {
        //print_r("expression");exit();

        $post_data = $data->all();
        $rules = array(
            'salespersonId' => 'required',

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
                    $salespersonId = $post_data['salespersonId'];


                    $status= DB::table('salesperson')
                         ->select('id as salesPersonId','name as salesPersonName','status')
                         ->where('salesperson.id' , $salespersonId)
                         ->get();

                    if(count($status)>0)
                     {
                     	$statusArray=array();
                     	foreach ($status as $key => $value) {
                     		$statusArray['salesPersonId']=$value->salesPersonId;
                     		$statusArray['salesPersonName']=$value->salesPersonName;
                     		$statusArray['status']=$value->status;
                     	}

                        $result = array("status" => 1,  "message" => trans("messages.Salesperson Status Detail"),"detail"=>$statusArray);

                     }else{

                        $result = array("status" => 2,  "message" => trans("messages.Salesperson Id is Wrong"));
                     }            

                }

              return json_encode($result, JSON_UNESCAPED_UNICODE);


    }


}