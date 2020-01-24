<?php

namespace App\Http\Controllers\Api;

use App\Model\orders;
use App;
use App\Http\Controllers\Controller;
use DB;
use JWTAuth;
//use Services_Twilio;
use Twilio\Rest\Client;

use Session;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Input;
use URL;
use App\Model\outlets;
use App\Model\outlet_reviews;
use App\Model\stores;
use App\Model\users;
use App\Model\drivers;
use App\Model\payment_history;
use DateTime;
class outlet extends Controller
{
    const ORDER_STATUS_UPDATE_USER = 18;
    const ORDER_MAIL_TEMPLATE = 5;
    const DRIVER_ORDER__DELIVERED_RESPONSE_ADMIN_TEMPLATE = 27;
    const USER_WALLET_PAYMENT = 33;

    public function orders(Request $requestdata)
    {
        $post_Data=$requestdata->all();

        $reqdata="";

        $data = array();
        $result = array("response" => array("httpCode" => 400, "status" => false ,"data"=>$data));

     
        if (!empty($post_Data['outletID'])) {
            $reqdata=$post_Data['outletID'];
        } else {
            $result = array("response" => array("httpCode" => 400, "status" => false ,"message"=>"invalid"));
            return json_encode($result);
        }

       
        //$orders=DB::select('select * from orders where outlet_id = `$reqdata`');
        
        $orders=DB::table('orders')
        ->select('*')
        ->where('orders.outlet_id', "=", $reqdata)
        ->get();

        if (count($orders)) {
            $orderList=array();
            foreach ($orders as $key => $value) {
                $orderList[$key]['orderID']=$value->id;
                $orderList[$key]['userID']=$value->customer_id;
                $orderList[$key]['userName']="";
                $orderList[$key]['userPhone']="";
                $orderList[$key]['orderQuantity']="";
                $orderList[$key]['orderAmount']=$value->total_amount;
                $orderList[$key]['orderDate']=explode(" ", $value->created_date)[0];
                $orderList[$key]['orderTime']=explode(" ", $value->created_date)[1];
                $orderList[$key]['orderStatus']=$value->order_status;
                $orderList[$key]['salesFleetID']=$value->salesFleet_id;
                $orderList[$key]['salesFleetName']="";
                $orderList[$key]['driverFleetID']="";
                $orderList[$key]['driverFleetName']="";
                $orderList[$key]['driverFleetPhone']="";
                $orderList[$key]['orderType']="";


                
                $orderItem=DB::table('orders_info')
                ->select('*')
                ->where('orders_info.order_id', '=', $value->id)
                ->join('products', 'products.id', '=', 'orders_info.item_id')
                ->get();

                $Item=array();

                if (count($orderItem)) {
                    foreach ($orderItem as $index => $itemData) {
                        $Item[$index]['itemID']=$itemData->item_id;
                        $Item[$index]['itemName']=$itemData->product_url;
                        $Item[$index]['itemMeasurement']="";
                        $Item[$index]['itemQuantity']=$itemData->quantity;
                        $Item[$index]['itemPrice']=$itemData->discount_price;
                        $Item[$index]['itemTax']="";
                        $Item[$index]['itemWeight']=$itemData->weight;
                        $Item[$index]['itemTotalAmount']=$itemData->discount_price;
                    }
                }

                $orderList[$key]['orderItems']=$Item;
                $Item=null;
            }
            $result = array("response" => array("httpCode" => 200, "status" => true ,"data"=>$orderList));
        } else {
            $result = array("response" => array("httpCode" => 200, "status" => true ,"data"=>""));
        }
        
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function ordersDetails(Request $requestdata)
    {
        $post_Data=$requestdata->all();


        $data = array();
        $result = array("response" => array("httpCode" => 400, "status" => false ,"data"=>$data));


        $orderID="";
        $outletID="";
     
        if (!empty($post_Data['outletID'])
        && !empty($post_Data['orderID'])) {
            $orderID=$post_Data['orderID'];
            $outletID=$post_Data['outletID'];
        } else {
            $result = array("response" => array("httpCode" => 400, "status" => false ,"message"=>"invalid"));
            return json_encode($result);
        }

        $orderDetails=DB::table('orders')
        ->select('*')
        ->where('orders.outlet_id', "=", $outletID)
        ->where('orders.id', '=', $orderID)
        ->get();


        if (!empty($orderDetails) && count($orderDetails)) {
            $orderList=array();
            foreach ($orderDetails as $key => $value) {
                $orderList[$key]['orderID']=$value->id;
                $orderList[$key]['userID']=$value->customer_id;
                $orderList[$key]['userName']="";
                $orderList[$key]['userPhone']="";
                $orderList[$key]['orderQuantity']="";
                $orderList[$key]['orderAmount']=$value->total_amount;
                $orderList[$key]['orderDate']=explode(" ", $value->created_date)[0];
                $orderList[$key]['orderTime']=explode(" ", $value->created_date)[1];
                $orderList[$key]['orderStatus']=$value->order_status;
                $orderList[$key]['salesFleetID']=$value->salesFleet_id;
                $orderList[$key]['salesFleetName']="";
                $orderList[$key]['driverFleetID']="";
                $orderList[$key]['driverFleetName']="";
                $orderList[$key]['driverFleetPhone']="";
                $orderList[$key]['orderType']="";


                
                $orderItem=DB::table('orders_info')
                ->select('*')
                ->where('orders_info.order_id', '=', $value->id)
                ->join('products', 'products.id', '=', 'orders_info.item_id')
                ->get();

                $Item=array();

                if (count($orderItem)) {
                    foreach ($orderItem as $index => $itemData) {
                        $Item[$index]['itemID']=$itemData->item_id;
                        $Item[$index]['itemName']=$itemData->product_url;
                        $Item[$index]['itemMeasurement']="";
                        $Item[$index]['itemQuantity']=$itemData->quantity;
                        $Item[$index]['itemPrice']=$itemData->discount_price;
                        $Item[$index]['itemTax']="";
                        $Item[$index]['itemWeight']=$itemData->weight;
                        $Item[$index]['itemTotalAmount']=$itemData->discount_price;
                    }
                }

                $orderList[$key]['orderItems']=$Item;
                $Item=null;
            }
            $result = array("response" => array("httpCode" => 200, "status" => true ,"data"=>$orderList));
        } else {
            $result = array("response" => array("httpCode" => 200, "status" => true ,"data"=>""));
        }

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function assignSalesFleet(Request $requestData)
    {
        $postData=$requestData->all();

        $orderID="";
        $outletID="";
        $salesFleetID="";

        if (!empty($postData['orderID'])
        && !empty($postData['outletID'])
        && !empty($postData['salesFleetID'])) {
            $orderID=$postData['orderID'];
            $outletID=$postData['outletID'];
            $salesFleetID=$postData['salesFleetID'];
        } else {
            $result = array("response" => array("httpCode" => 400, "status" => false ,"message"=>"invalid"));
            return json_encode($result);
        }
        $orderDetails=DB::table('orders')
        ->select('*')
        ->where('orders.outlet_id', "=", $outletID)
        ->where('orders.id', '=', $orderID)
        ->update(
            ['salesFleet_id'=>$salesFleetID,
            'order_status'=>10]
        );

        $result = array("response" => array("httpCode" => 200, "status" => false ,"message"=>"Sale Fleet assigned"));

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }



    public function assignDriverFleet(Request $requestData)
    {
        $postData=$requestData->all();

        $orderID="";
        $outletID="";
        $driverFleetID="";

        if (!empty($postData['orderID'])
        && !empty($postData['outletID'])
        && !empty($postData['driverFleetID'])) {
            $orderID=$postData['orderID'];
            $outletID=$postData['outletID'];
            $driverFleetID=$postData['driverFleetID'];
        } else {
            $result = array("response" => array("httpCode" => 400, "status" => false ,"message"=>"invalid"));
            return json_encode($result);
        }
        $orderDetails=DB::table('orders')
        ->select('*')
        ->where('orders.outlet_id', "=", $outletID)
        ->where('orders.id', '=', $orderID)
        ->update(
            ['driver_ids'=>$driverFleetID]
        );

        $result = array("response" => array("httpCode" => 200, "status" => false ,"message"=>"Driver Fleet assigned"));

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function FunctionName(Type $var = null)
    {
        # code...
    }



    // outletsManager to login:
    public function outlet_login(Request $data)
    {
        $post_data = $data->all();
        $rules = [
            //'email' => ['required'],
            'password' => ['required'],
           // 'outlet_key' => ['required'],
            'language' => ['required'],
            'phone' => ['required'],
            'countryCode' => ['required'],
           // 'vendor_id' => ['required'],
            //'device_id' => ['required_unless:login_type,1,2,3'],
            //'device_token' => ['required_unless:login_type,1,2,3'],
        ];
        if (isset($post_data['language']) && $post_data['language'] == 2) {
            App::setLocale('ar');
        } else {
            App::setLocale('en');
        }
        $errors = $result = array();
        //$post_data['email'] = isset($post_data['email']) ? trim(strtolower($post_data['email'])) : '';
        //print_r($post_data);exit;
        $validator = app('validator')->make($post_data, $rules);
        //$outlet_key = !empty($post_data['outlet_key']) ? $post_data['outlet_key'] : '';
        $phone = !empty($post_data['phone']) ? $post_data['phone'] : '';
        $countryCode = !empty($post_data['countryCode']) ? $post_data['countryCode'] : '';
        $password = !empty($post_data['password']) ? $post_data['password'] : '';
        $validator->after(function ($validator) use ($post_data) {
            if (!empty($post_data['phone'])) {
                $user_data = DB::select('SELECT outlets.id,out_infos.outlet_name, vendors_infos.vendor_name,outlets.contact_email, outlets.latitude, outlets.longitude,out_infos.contact_address,  outlets.active_status,outlets.vendor_id, outlets.contact_phone ,outlets.is_verified,vend.logo_image  FROM outlets left join outlet_infos out_infos on out_infos.id = outlets.id left join vendors_infos vendors_infos on vendors_infos.id = outlets.vendor_id left join vendors vend on vend.id = outlets.vendor_id where outlets.contact_phone = ?  AND outlets.password = ? AND outlets.country_code = ?  limit 1', array($post_data['phone'],  md5($post_data['password']),$post_data['countryCode'],));

                //print_r($user_data);exit;


                if (count($user_data) == 0) {
                    $validator->errors()->add('phone', 'Invalid login credentials');
                } else {
                    $user_data = $user_data[0];
                    if ($user_data->is_verified == 0) {
                        $validator->errors()->add('phone', 'Kinldy contacts your manager to verify');
                    }
                }
            }
        });
        if ($validator->fails()) {
            $user_id = $mobile = 0;
            $phone_verify = 1;
            $errors = array();
            if (!empty($phone)) {
                $user_data = DB::select('SELECT outlets.id,out_infos.outlet_name, vendors_infos.vendor_name,outlets.contact_email, outlets.latitude, outlets.longitude, out_infos.contact_address,  outlets.active_status,outlets.vendor_id, outlets.contact_phone ,outlets.is_verified,vend.logo_image  FROM outlets left join outlet_infos out_infos on out_infos.id = outlets.id left join vendors_infos vendors_infos on vendors_infos.id = outlets.vendor_id left join vendors vend on vend.id = outlets.vendor_id where outlets.contact_phone = ?  AND outlets.password = ? AND outlets.country_code = ?  limit 1', array($post_data['phone'],  md5($post_data['password']),$post_data['countryCode'],));
                $phone_verify = isset($user_data[0]->phone_verify)?$user_data[0]->phone_verify:0;
                $user_id = isset($user_data[0]->id) ? $user_data[0]->id : 0;
                $mobile = isset($user_data[0]->contact_phone) ? $user_data[0]->contact_phone : 0;
            }
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result =  array( "status" => 5, "message" => $errors, "phone_verify" => $phone_verify, "user_id" => $user_id, "mobile" => $mobile);
        } else {
            if ($post_data['language'] == 2) {
                App::setLocale('en');
            } else {
                App::setLocale('en');
            }
            //$post_data['email'] = trim(strtolower($post_data['email']));
            $user_data = DB::select('SELECT outlets.id,out_infos.outlet_name, vendors_infos.vendor_name,outlets.contact_email, outlets.latitude, outlets.longitude,out_infos.contact_address,  outlets.active_status,outlets.vendor_id, outlets.contact_phone ,outlets.is_verified,vend.logo_image  FROM outlets left join outlet_infos out_infos on out_infos.id = outlets.id left join vendors_infos vendors_infos on vendors_infos.id = outlets.vendor_id left join vendors vend on vend.id = outlets.vendor_id where outlets.contact_phone = ?  AND outlets.password = ? AND outlets.country_code = ?  limit 1', array($post_data['phone'],  md5($post_data['password']),$post_data['countryCode'],));
            $user_data = $user_data[0];
            if (count($user_data) > 0) {
                if ($user_data->is_verified == 0) {
                    $result =  array( "status" => 4, "message" => trans("messages.Please confirm you mail to activation."));
                } elseif ($user_data->active_status == 0) {
                    $result =  array( "status" => 3, "message" => trans("messages.Your registration has blocked pls contact Your Admin."));
                } else {
                    // Check login type based on mobile api parameters
                    // if (isset($post_data['login_type']) && !empty($post_data['login_type'])) {
                    //  //Update the device token & id for Android
                    //  if ($post_data['login_type'] == 2) {
                    //      $res = DB::table('users')
                    //          ->where('id', $user_data->id)
                    //          ->update(['android_device_token' => $post_data['device_token'], 'android_device_id' => $post_data['device_id'], 'login_type' => $post_data['login_type'], 'user_type' => $post_data['user_type']]);
                    //  }
                    //  //Update the device token & id for iOS
                    //  if ($post_data['login_type'] == 3) {
                    //      $res = DB::table('users')
                    //          ->where('id', $user_data->id)
                    //          ->update(['ios_device_token' => $post_data['device_token'], 'ios_device_id' => $post_data['device_id'], 'login_type' => $post_data['login_type'], 'user_type' => $post_data['user_type']]);
                    //  }
                    // }
                    //$token = JWTAuth::fromUser($user_data, array('exp' => 200000000000));

                    $vendorLogo = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/vendors/logos/' . $user_data->logo_image) && $user_data->logo_image != '') {
                        $vendorLogo = url('/assets/admin/base/images/vendors/logos/' . $user_data->logo_image);
                    }

                    //print_r($vendorLogo);exit();


                    $result = array( "status" => 1, "message" => trans("messages. Logged-in Successfully"),"details"=>array("outletId" => $user_data->id,"outletName" => $user_data->outlet_name,"vendorId" => $user_data->vendor_id,"vendorName" => $user_data->vendor_name,"latitude" => $user_data->latitude,"longitude" => $user_data->longitude,"outletAddress" => $user_data->contact_address,"image" => $vendorLogo, "Email" => $user_data->contact_email,"Phone"=>$user_data->contact_phone,'inchargeName'=>"" ));
                }
            } else {
                $result =  array( "status" => 2, "message" => trans("messages.Your account is inactive mode. Kindly contact admin."));
            }
            return $result;
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    public function outletsManager_login(Request $data)
    {
        $post_data = $data->all();
        $rules = [
                'password' => ['required'],
                'language' => ['required'],
                'phone' => ['required'],
                'countryCode' => ['required'],
               
            ];
        if (isset($post_data['language']) && $post_data['language'] == 2) {
            App::setLocale('ar');
        } else {
            App::setLocale('en');
        }
        $errors = $result = array();
           
        $validator = app('validator')->make($post_data, $rules);
        $phone = !empty($post_data['phone']) ? $post_data['phone'] : '';
        $countryCode = !empty($post_data['countryCode']) ? $post_data['countryCode'] : '';
        $password = !empty($post_data['password']) ? $post_data['password'] : '';
        $validator->after(function ($validator) use ($post_data) {
            if (!empty($post_data['phone'])) {
                /*$user_data = DB::select('SELECT outlets.id,out_infos.outlet_name, vendors_infos.vendor_name,outlets.contact_email, outlets.latitude, outlets.longitude,out_infos.contact_address,  outlets.active_status,outlets.vendor_id, outlets.contact_phone ,outlets.is_verified,vend.logo_image  FROM outlets left join outlet_infos out_infos on out_infos.id = outlets.id left join vendors_infos vendors_infos on vendors_infos.id = outlets.vendor_id left join vendors vend on vend.id = outlets.vendor_id where outlets.contact_phone = ?  AND outlets.password = ? AND outlets.country_code = ?  limit 1', array($post_data['phone'],  md5($post_data['password']),$post_data['countryCode'],));
                */

                $user_data = DB::select('SELECT outlet_managers.id,outlet_managers.first_name,outlet_managers.email,outlet_infos.contact_address, outlet_managers.active_status,outlet_managers.vendor_id,outlet_managers.outlet_id,outlet_infos.outlet_name, vendors_infos.vendor_name, outlet_managers.mobile_number ,outlet_managers.is_verified,outlet_managers.profile_image , outlets.latitude, outlets.longitude FROM outlet_managers left join outlet_infos outlet_infos on outlet_infos.id = outlet_managers.outlet_id left join vendors_infos vendors_infos on vendors_infos.id = outlet_managers.vendor_id left join outlets outlets on outlets.id = outlet_managers.outlet_id where outlet_managers.mobile_number = ?  AND outlet_managers.hash_password = ? AND outlet_managers.country_code = ? limit 1', array($post_data['phone'],  md5($post_data['password']),$post_data['countryCode']));
                //print_r($user_data);exit;


                if (count($user_data) == 0) {
                    $validator->errors()->add('phone', 'Invalid login credentials');
                } else {
                    $user_data = $user_data[0];
                    if ($user_data->is_verified == 0) {
                        $validator->errors()->add('phone', 'Kinldy contacts your manager to verify');
                    }
                }
            }
        });
        if ($validator->fails()) {
            $user_id = $mobile = 0;
            // $phone_verify = 1;
            $errors = array();
            if (!empty($phone)) {
                $user_data = DB::select('SELECT outlet_managers.id,outlet_managers.first_name,outlet_managers.email,outlet_infos.contact_address, outlet_managers.active_status,outlet_managers.vendor_id,outlet_managers.outlet_id,outlet_infos.outlet_name, vendors_infos.vendor_name, outlet_managers.mobile_number ,outlet_managers.is_verified,outlet_managers.profile_image , outlets.latitude, outlets.longitude FROM outlet_managers left join outlet_infos outlet_infos on outlet_infos.id = outlet_managers.outlet_id left join vendors_infos vendors_infos on vendors_infos.id = outlet_managers.vendor_id left join outlets outlets on outlets.id = outlet_managers.outlet_id where outlet_managers.mobile_number = ?  AND outlet_managers.hash_password = ? AND outlet_managers.country_code = ? limit 1', array($post_data['phone'],  md5($post_data['password']),$post_data['countryCode']));
                //$phone_verify = isset($user_data[0]->phone_verify)?$user_data[0]->phone_verify:0;
                $user_id = isset($user_data[0]->id) ? $user_data[0]->id : 0;
                $mobile = isset($user_data[0]->mobile_number) ? $user_data[0]->mobile_number : 0;
            }
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $errors[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }
            $errors = implode(", \n ", $errors);
            $result =  array( "status" => 0, "message" => $errors );
        } else {
            if ($post_data['language'] == 2) {
                App::setLocale('en');
            } else {
                App::setLocale('en');
            }
            $user_data = DB::select('SELECT outlet_managers.id,outlet_managers.first_name,outlet_managers.email,outlet_infos.contact_address, outlet_managers.active_status,outlet_managers.vendor_id,outlet_managers.outlet_id,outlet_infos.outlet_name, vendors_infos.vendor_name, outlet_managers.mobile_number ,outlet_managers.is_verified,outlet_managers.profile_image , outlets.latitude, outlets.longitude FROM outlet_managers left join outlet_infos outlet_infos on outlet_infos.id = outlet_managers.outlet_id left join vendors_infos vendors_infos on vendors_infos.id = outlet_managers.vendor_id left join outlets outlets on outlets.id = outlet_managers.outlet_id where outlet_managers.mobile_number = ?  AND outlet_managers.hash_password = ? AND outlet_managers.country_code = ? limit 1', array($post_data['phone'],  md5($post_data['password']),$post_data['countryCode']));
            $user_data = $user_data[0];
            if (count($user_data) > 0) {
                if ($user_data->is_verified == 0) {
                    $result =  array( "status" => 4, "message" => trans("messages.Please confirm you mail to activation."));
                } elseif ($user_data->active_status == 0) {
                    $result =  array( "status" => 3, "message" => trans("messages.Your registration has blocked pls contact Your Admin."));
                } else {
                    // Check login type based on mobile api parameters
                    // if (isset($post_data['login_type']) && !empty($post_data['login_type'])) {
                    //  //Update the device token & id for Android
                    //  if ($post_data['login_type'] == 2) {
                    //      $res = DB::table('users')
                    //          ->where('id', $user_data->id)
                    //          ->update(['android_device_token' => $post_data['device_token'], 'android_device_id' => $post_data['device_id'], 'login_type' => $post_data['login_type'], 'user_type' => $post_data['user_type']]);
                    //  }
                    //  //Update the device token & id for iOS
                    //  if ($post_data['login_type'] == 3) {
                    //      $res = DB::table('users')
                    //          ->where('id', $user_data->id)
                    //          ->update(['ios_device_token' => $post_data['device_token'], 'ios_device_id' => $post_data['device_id'], 'login_type' => $post_data['login_type'], 'user_type' => $post_data['user_type']]);
                    //  }
                    // }
                    //$token = JWTAuth::fromUser($user_data, array('exp' => 200000000000));
                    $profileLogo = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/managers/' . $user_data->profile_image) && $user_data->profile_image != '') {
                        $profileLogo = url('/assets/admin/base/images/managers/' . $user_data->profile_image);
                    }



                    $result = array( "status" => 1, "message" => trans("messages. Logged-in Successfully"),"details"=>array("outletsManagerId" => $user_data->id,"outletId" => $user_data->outlet_id,"outletName" => $user_data->outlet_name,"vendorId" => $user_data->vendor_id,"vendorName" => $user_data->vendor_name,"outletLat" => $user_data->latitude,"outletLong" => $user_data->longitude,"outletAddress" => $user_data->contact_address,"image" => $profileLogo, "Email" => $user_data->email,"Phone"=>$user_data->mobile_number,'inchargeName'=>"" ));
                }
            } else {
                $result =  array( "status" => 2, "message" => trans("messages.Your account is inactive mode. Kindly contact admin."));
            }
            return $result;
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

   
    public function outforgotPassword(Request $data)
    {
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
            //$check_auth = JWTAuth::toUser($post_data['token']);
            $post_data = $data->all();
            $phone = $post_data['phoneNumber'];
            $countryCode = $post_data['countryCode'];
            $phoneNumber = $phone;
            $user_details = DB::table('outlets')
                ->select('id')
                ->where('contact_phone', '=', $phoneNumber)
                ->first();
            //print_r($user_details); exit;
            $result = array("status" => 2, "message" => trans('messages.Mobile Number is not register'));
            if (count($user_details) > 0) {
                $users = Outlets::find($user_details->id);
                $otp = rand(1000, 9999);
                //$otp_unique = str_random(8);
                //$pass_string = md5($otp_unique);
                $app_config = getAppConfig();
                $number = str_replace('-', '', $users->contact_phone); //to remove the '-'
                $message = 'You have received OTP password for ' . getAppConfig()->site_name . '. Your OTP code is ' . $otp . '. This code can be used only once and dont share this code with anyone.';
               /* $twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
                $twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
                $from_number = "+14783471785";
                //print_r($from_number);exit();

                $client = new Services_Twilio($twilo_sid, $twilio_token);*/
                $twilo_sid = TWILIO_ACCOUNTSID;
                $twilio_token = TWILIO_AUTHTOKEN;
                $from_number = TWILIO_NUMBER;
                $client = new Client($twilo_sid, $twilio_token);

                //$number='8075802161';
                $number=$countryCode.$number;
                //  print_r($number);exit();
                //$number ='+918075802161';
                // Create an authenticated client for the Twilio API
                try {
                    /*$m = $client->account->messages->sendMessage(
                        $from_number, // the text will be sent from your Twilio number
                        $number, // the phone number the text will be sent to
                        $message // the body of the text message
                    );*/
                                    $m =  $client->messages->create($number, array('from' => $from_number, 'body' => $message));

                    //print_r($m);exit;

                    $users->phone_otp = $otp;
                    //$users->otp_unique = $pass_string;
                    $users->modified_date = date("Y-m-d H:i:s");
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





    public function outletManagerLogout(Request $data)
    {
        $post_data = $data->all();
        if (isset($post_data['language']) && $post_data['language'] == 2) {
            App::setLocale('ar');
        } else {
            App::setLocale('en');
        }
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
            $result = array("status" => 0, "message" => trans('messages.Error List'), "detail" => $errors);
        }
        $outlet_managers=DB::table('outlet_managers')
                        ->select('id')
                        ->where('id', $post_data['outletManagerId']);

        if (count($outlet_managers)>0) {
            $result = array("status" => 1, "message" => trans('messages.Logged out successfully'));
        }
        
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }


    /*public function verify_outlet_key(Request $data) {
        $post_data = $data->all();
        $rules = [
             'outlet_key'    => ['required'],
            'language' => ['required'],

        ];
        if (isset($post_data['language'])) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $errors = $result = array();
        //print_r($post_data['outlet_key']);exit;
        $validator = app('validator')->make($post_data, $rules);

        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 0, "Error" => trans("messages.Error List"), "message" => $errors);
        } else {

            $user_data = DB::select('SELECT outlets.id, outlets.contact_email,  outlets.active_status, outlets.contact_phone ,outlets.is_verified FROM outlets where outlets.outlet_key = ?  limit 1', array($post_data['outlet_key']));

            if (count($user_data) > 0) {
                $user_data = $user_data[0];
                if ($user_data->is_verified == 0) {
                    $result = array("status" => 0, "message" => trans("messages.Please verify your outlet_key."));
                } else if ($user_data->active_status == 0) {
                    $result = array("status" => 0, "message" => trans("messages.Your registration has blocked pls contact Your Admin."));
                } else {

                    $result = array("status" => 1, "message" => trans("Please enter your Password"), "details" => array("outletId" => $user_data->id, "phoneNumber" => $user_data->contact_phone, "Email" =>$user_data->contact_email ));
                }
            } else {
                $result = array("status" => 0, "message" => trans("messages.outletKey seems to be incorrect."));
            }
        }
        return $result;
    }*/

    public function outVerifyPhone(Request $data)
    {
        $post_data = $data->all();
        $rules = [
             'phone'    => ['required'],
            'language' => ['required'],
        
        ];
        if (isset($post_data['language'])) {
            App::setLocale($post_data['language']);
        } else {
            App::setLocale('en');
        }
        $errors = $result = array();
        //print_r($post_data['outlet_key']);exit;
        $validator = app('validator')->make($post_data, $rules);
        
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? trans('messages.' . implode(',', $value)) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 0, "Error" => trans("messages.Error List"), "message" => $errors);
        } else {
            $user_data = DB::select('SELECT outlets.id, outlets.contact_email,  outlets.active_status, outlets.contact_phone ,outlets.is_verified FROM outlets where outlets.contact_phone = ?  limit 1', array($post_data['phone']));

            if (count($user_data) > 0) {
                $user_data = $user_data[0];
                if ($user_data->is_verified == 0) {
                    $result = array("status" => 0, "message" => trans("messages.Please verify your phoneNumber."));
                } elseif ($user_data->active_status == 0) {
                    $result = array("status" => 0, "message" => trans("messages.Your registration has blocked pls contact Your Admin."));
                } else {
                    $result = array("status" => 1, "message" => trans("Please enter your Password"), "details" => array("outletId" => $user_data->id, "phoneNumber" => $user_data->contact_phone, "Email" =>$user_data->contact_email ));
                }
            } else {
                $result = array("status" => 0, "message" => trans("messages.This phoneNumber is  not registered."));
            }
        }
        return $result;
    }

    public function outVerifyOtp(Request $data)
    {
        $post_data = $data->all();

        $rules = array(
            'otp' => 'required',

        );

        if (isset($post_data['language']) && $post_data['language'] == 2) {
            App::setLocale('ar');
        } else {
            App::setLocale('en');
        }

        //$check_auth = JWTAuth::toUser($post_data['token']);
        $user_details = DB::table('outlets')
            ->select('id')
            ->where('phone_otp', '=', $post_data['otp'])
            ->first();
        $result = array("status" => 0, "message" => trans('messages.Verification Failed kindly check your otp '));
        if (count($user_details) > 0) {
            $user_data =Outlets::find($user_details->id);
            //$otp_unique = str_random(8);
            //$pass_string = md5($otp_unique);
            //$user_data->otp_unique = $pass_string;
            $user_data->is_verified = 1;
            $user_data->save();
            $token = JWTAuth::fromUser($user_data, array('exp' => 200000000000));

            $result = array("status" => 1,  "message" => trans('messages.OTP Verified Successfully,Please login.'));
        }
        return json_encode($result);
    }


    public function outResendOtp(Request $data)
    {
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
            $result = array("status" => 0, "Error" => trans("messages.Error List"), "message" => $errors);
        } else {
            $post_data = $data->all();
            $phone = $post_data['phoneNumber'];
            $countryCode = $post_data['countryCode'];
            //$phoneNumber = $countryCode . '-' . $phone;
            $phoneNumber =$phone;
            $user_details = DB::table('outlets')
                ->select('id')
                ->where('contact_phone', '=', $phoneNumber)
                ->first();
            //  print_r($user_details); exit;
            $result = array("status" => 0, "message" => trans("messages.Mobile Number is not register. "));
            if (count($user_details) > 0) {
                $users = Outlets::find($user_details->id);
                $otp = rand(1000, 9999);
                $app_config = getAppConfig();
                $number = str_replace('-', '', $users->contact_phone); //to remove the '-'
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
                   /* $m = $client->account->messages->sendMessage(
                        $from_number, // the text will be sent from your Twilio number
                        $number, // the phone number the text will be sent to
                        $message // the body of the text message
                    );*/
                                    $m =  $client->messages->create($number, array('from' => $from_number, 'body' => $message));


                    $users->phone_otp = $otp;
                    $users->modified_date = date("Y-m-d H:i:s");
                    $users->save();
                    $token = JWTAuth::fromUser($users, array('exp' => 200000000000));
                    $result = array("status" => 1, "message" => trans("messages.New OTP has been sent to your register mobile number."), "details" => array("countryCode" => $post_data['countryCode'], "phoneNumber" => $post_data['phoneNumber'], "userOtp" => $otp));
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


    /*public function fleets(Request $data){


    }*/



    public function salesPersonInfo(Request $data)
    {
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
                $driver_data = DB::table('sales_person')
                    ->select('sales_person.id', 'first_name as userName', 'sales_person.profile_image as imageUrl ', 'sales_person.mobile_number as mobile', 'sales_person.email')
                    ->where('sales_person.id', $post_data['driverId'])
                    ->where('sales_person.active_status', 1)
                    ->first();
                if (count($driver_data) > 0) {
                    $driver_data->userName = ($driver_data->userName != '') ? $driver_data->userName : '';
                    //$driver_data->last_name = ($driver_data->last_name != '') ? $driver_data->last_name : '';
                    $imageName = url('/assets/admin/base/images/default_avatar_male.jpg');
                    if (file_exists(base_path() . '/public/assets/admin/base/images/drivers/' . $driver_data->imageUrl) && $driver_data->imageUrl != '') {
                        $imageName = URL::to("/assets/admin/base/images/drivers/" . $driver_data->imageUrl . '?' . time());
                    }
                    $driver_data->imageUrl = $imageName;

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

                    $result = array("status" => 1, "httpCode" => 200, "message" => trans("messages.Sales Person details"), 'details' => $driver_data);
                } else {
                    $result = array("status" => 2, "httpCode" => 400, "message" => trans("messages.No driver found"));
                }
            } catch (JWTException $e) {
                $result = array("httpCode" => 400, "message" => trans("messages.Kindly check the user credentials"));
            } catch (TokenExpiredException $e) {
                $result = array("httpCode" => 400, "message" => trans("messages.Kindly check the user credentials"));
            }
        }
        return json_encode($result);
        exit;
    }

    //Ram : 12/09/2019

    
    public function outletOrders(Request $data)
    {
        $rules = [
            'language' => ['required'],
            'outletId' => ['required'],
            'type' => ['required'],
            
        ];

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
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            $outletId = $post_data['outletId'];
            $type = $post_data['type'];
            $vendorId = isset($post_data['vendorId'])?$post_data['vendorId']:0;
            if ($type==1) {
                $raw  = 'orders.outlet_id = '.$outletId ;
                $raw .= 'and orders.order_status ='. 1;
                //$raw .=" and orders.created_date <'".date('Y-m-d H:i:s')."'";
                $raw .= 'and orders.salesperson_id ='. 0;
            }
            if ($type==2) {
                $raw  = 'orders.outlet_id = '.$outletId ;
                 $raw .= 'and orders.order_status !='. 33 ;
                 //$raw .= 'and orders.order_status !='. 34;

                // $raw .=" and orders.created_date <'".date('Y-m-d H:i:s')."'";
                //$raw .= 'and orders.salesperson_id !='. 0;
                $raw .= 'and orders.order_status !='. 17;

                $raw .= 'and orders.order_status !='. 1;
                $raw .= 'and orders.order_status !='. 19;
                $raw .= 'and orders.order_status !='. 12;
                $raw .= 'and orders.order_status !='. 11;
                $raw .= 'and orders.order_status !='. 14;
                $raw .= 'and orders.order_status !='. 31;
                $raw .= 'and orders.order_status !='. 32;
               // $raw .= 'and orders.order_status ='. 36;
                //$raw .= 'and orders.driver_ids IS NULL';
            }
            if ($type==3) {
                $raw  ='orders.outlet_id= '.$outletId ;
                $raw .= 'and orders.order_status !='. 1;
                $raw .= 'and orders.order_status !='. 33;
                $raw .= 'and orders.order_status !='. 11;
                $raw .= 'and orders.order_status !='. 14;
                $raw .= 'and orders.order_status !='. 10;
                // $raw .= 'and orders.order_status ='. 31;
                // $raw .= 'and orders.order_status ='. 32;
                $raw .= 'and orders.order_status !='. 34;
                $raw .= 'and orders.order_status !='. 18;
                // $raw .= 'and orders.order_status ='. 19;
                $raw .= 'and orders.driver_ids IS NOT NULL';

                //  $raw .= 'and orders.order_status !='. 32;
                //$raw .=" and orders.created_date <'".date('Y-m-d H:i:s')."'";
            }
            //echo"<pre>";  print_r($raw);exit;
            $outletOrders=DB::table('orders')
                            ->join('order_status', 'order_status.id', '=', 'orders.order_status')
                            ->join('users', 'users.id', '=', 'orders.customer_id')
                            ->leftjoin('drivers', 'drivers.id', '=', 'orders.driver_ids')
                            ->leftjoin('salesperson', 'salesperson.id', '=', 'orders.salesperson_id')
                                                      
                            ->select(
                                'orders.id as orderId',
                                'orders.customer_id as customerId',
                                'orders.outlet_id as outletId',
                                'orders.vendor_id as vendorId',
                                'orders.order_key as orderKey',
                                'orders.order_key_formated as orderKeyFormated',
                                'orders.order_comments as orderComments',
                                'orders.order_status as orderStatus',
                                'order_status.name as statusName',
                                'orders.created_date as createdDate',
                                'users.name as customerName',
                                'orders.total_amount as totalAmount',
                                'orders.driver_ids as driverId',
                                'drivers.first_name as driverName',
                                'orders.salesperson_id as salesFleetId',
                                'salesperson.name as salesFleetName',
                                'orders.assigned_time as assignedTime'
                            )
                            ->distinct()
                            ->whereRaw($raw)
                            ->orderBy('createdDate', 'desc')
                            ->get();

            //echo"<pre>"; print_r($outletOrders)  ;exit();
                          

            if ($vendorId != 0) {    
            $salesperson=DB::table('salesperson')
                            ->select('salesperson.id as salesDriverId', 'salesperson.name as salesDriverName')
  
                            ->where('salesperson.status', '=', 'F')
                            ->where('salesperson.vendor_driver', '=', $vendorId)
                            ->get();
            }else{
            $salesperson=DB::table('salesperson')
                            ->select('salesperson.id as salesDriverId', 'salesperson.name as salesDriverName')
  
                            ->where('salesperson.status', '=', 'F')
                            ->get();
            }
            //print_r($salesperson);exit();      
            $count = count($outletOrders);
            $data=array();
            foreach ($outletOrders as $key => $value) {
                $data[$key]['orderId']=$value->orderId;
                $data[$key]['customerId']=$value->customerId;
                $data[$key]['outletId']=$value->outletId;
                $data[$key]['vendorId']=$value->vendorId;
                $data[$key]['orderKey']=$value->orderKey;
                $data[$key]['orderKeyFormated']=$value->orderKeyFormated;
                $data[$key]['orderComments']=isset($value->orderComments) ? $value->orderComments:"" ;
                $data[$key]['orderStatus']=$value->orderStatus;
                $data[$key]['createdDate']=$value->createdDate;
                $data[$key]['customerName']=$value->customerName;
                $data[$key]['totalAmount']=$value->totalAmount;
                $data[$key]['salesFleetId']=isset($value->salesFleetId)?$value->salesFleetId:"";
                $salesFleetName =isset($value->salesFleetName)?$value->salesFleetName:"Incharge";
                $data[$key]['salesFleetName']=($value->orderStatus == 36  ? 'Incharge' : $salesFleetName);
                //$data[$key]['salesFleetName']=isset($value->salesFleetName)?$value->salesFleetName:"";
               
                $datetime1 = new DateTime();
                $datetime2 = new DateTime($value->assignedTime);
                $diff = $datetime1->getTimestamp()-$datetime2->getTimestamp() ;

                //print_r( $diff) ;exit;


                if($diff < 60){

                $data[$key]['driverId']=isset($value->driverId)?$value->driverId:"";
                $data[$key]['driverName']=isset($value->driverName)?$value->driverName:"";
                }else{

                $data[$key]['driverId']="";
                $data[$key]['driverName']="";

                }

                $item =DB::select("select sum(item_unit) as quantity  from orders_info where orders_info.order_id = $value->orderId ");

                if (count($item)>0) {
                    $array=array();
                    foreach ($item as $items => $values) {
                        $array[$items]['orderQuantity']= $values->quantity;
                    }
                }

                $data[$key]['orderQuantity']= $values->quantity;

            /*    $orderDetails=DB::table('orders_info')
                            ->join('products_infos', 'products_infos.id', '=', 'orders_info.item_id')
                            ->join('products', 'products.id', '=', 'orders_info.item_id')
                            ->select('item_id', 'products_infos.product_name as productName', 'orders_info.item_unit as orderQuantity', 'orders_info.item_cost as itemPrice', 'products.product_image as productImage')
  
                            ->where('orders_info.order_id', $value->orderId)
                            ->get();*/

                $orderDetails =DB::table('orders_info')
                                ->join('admin_products','admin_products.id','=','orders_info.item_id')
                                ->select('item_id', 'admin_products.product_name as productName', 'orders_info.item_unit as orderQuantity', 'orders_info.item_cost as itemPrice', 'admin_products.image as productImage')
                                ->where('orders_info.order_id', $value->orderId)
                                ->get();
                   // print_r($orderDetails);exit();

                $orderDetailsArray=array();

                if (count($orderDetails)>0) {
                    foreach ($orderDetails as $order => $val) {
                        $orderDetailsArray[$order]["itemid"]=$val->item_id;
                        $orderDetailsArray[$order]["itemName"]=$val->productName;
                        $orderDetailsArray[$order]["itemQuantity"]=$val->orderQuantity;
                        $orderDetailsArray[$order]["itemPrice"]=$val->itemPrice;

                        /*$productImage = URL::asset('assets/front/' . Session::get("general")->theme . '/images/store_detial.png');

                        if (file_exists(base_path() . '/public/assets/admin/base/images/products/list/' . $val->productImage) && $val->productImage != '') {
                            $productImage = url('/assets/admin/base/images/products/list/' . $val->productImage);
                        }
                        $orderDetailsArray[$order]['productImage'] = $productImage;*/

                        $no_image = URL::asset('assets/front/' . Session::get("general")->theme . '/images/no_image.png');
                        $path = url('/assets/admin/base/images/products/admin_products/');
                        $productImage=json_decode($val->productImage);
                      
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


                        
                        $orderDetailsArray[$order]['productImage'] = $image1;



                    }
                }

                $data[$key]['itemDetail']=$orderDetailsArray;
            }
            $datas=array();

            foreach ($salesperson as $key => $value) {
                $datas[$key]['id']=$value->salesDriverId;
                $datas[$key]['name']=$value->salesDriverName;
            }
            
            $result = array("status" => 1, "message" => trans("messages.outletOrdersList")  ,"count"=>$count,"detail"=>$data,"availableSalesPerson"=>$datas);
        }
        return json_encode($result);
    }




    public function testing()
    {
        echo $_REQUEST["name"];
        exit();
    }


   /* public function assignSalesPerson(Request $data)
    {
        $rules = [
            'language' => ['required'],
            'salesPersonId' => ['required'],
            'orderId' => ['required'],
            
        ];

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
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            $salesPersonId = $post_data['salesPersonId'];
            $orderId = $post_data['orderId'];
                            //print_r($orderId);exit();

              
            $updateStatus=DB::table('salesperson')
                                ->where('salesperson.id', $salesPersonId)
                                ->where('salesperson.status', '=', 'F')
                                ->update(['status' =>'A']);


              $notify = DB::table('salesperson')
                    ->select('salesperson.name as salesPersonName','salesperson.android_device_token as salesperson_android_token','salesperson.ios_device_token as salesperson_ios_token','salesperson.login_type')
                    // ->Join('users', 'users.id', '=', 'orders.customer_id')
                    // ->Join('vendors_infos', 'vendors_infos.id', '=', 'orders.vendor_id')
                    // ->Join('vendors', 'vendors.id', '=', 'orders.vendor_id')
                    // ->Join('outlets', 'outlets.id', '=', 'orders.outlet_id')
                    // ->Join('outlet_infos','outlet_infos.id', '=', 'orders.outlet_id')
                    // ->Join('order_status','order_status.id', '=', 'orders.order_status')
                   // ->Join('salesperson','salesperson.id', '=', 'orders.salesperson_id')
                    // ->where('orders.id', '=', (int) $orderId)
                      ->where('salesperson.id', $salesPersonId)

                    ->get();
                $notifys=$notify[0];
                    //print_r($notifys);exit();

                $order_title = '' . 'New Order ';
                $description = 'New Order ' ;
                $orderStatus =34;
            
                if($notifys->login_type == 2){
                    $token = $notifys->salesperson_android_token;
                }else if($notifys->login_type == 3)
                {
                    $token = $notifys->salesperson_ios_token;
                }
               // $token =isset($token)?$token:'';
               // $token="e_gwC-VGEe_gwC-VGE5A:APA91bHETHPofUbROzfQHPNGQ02NyvyA1JkHgu9muhMvBJWNmvSvnFzV5W5DyGueRbDTvkGJ2AFDKU4e2QadKKTKDjz_wQqoTnKAjYmhIXRsG_eeMiXlQk4hXiPd41-g3zjj2oX6r-Zb";
              //print_r($token);exit();

                $item =DB::select("select sum(item_unit) as quantity  from orders_info where orders_info.order_id = $orderId ");
                $total_amount    =DB::select("select total_amount  from orders where id = $orderId ");
                //print_r($total_amount);exit();
               // print_r($item);exit();

                if (count($item)>0) {
                    $array=array();
                    foreach ($item as $items => $values) {
                        $array[$items]['orderQuantity']= $values->quantity;
                    }
                }
                $totalamount = 0;
                if (count($total_amount)>0) {
                    $array=array();
                   $totalamount =isset($total_amount[0]->total_amount)?$total_amount[0]->total_amount:0;
                }


                //print_r($values->quantity);exit();
                $data = array
                    (
                    'status' => 1,
                    'message' => $order_title,
                    'detail' =>array(
                    'description'=>$description,    
                     'salesPersonName' => isset($notif->salesPersonName) ? $notif->salesPersonName : '',
                     'orderId' => $orderId,
                    // 'driverId' => isset($notif->driver_ids) ? $notif->driver_ids : '',
                     'orderStatus' => $orderStatus,
                     'type' => 2,
                     'title' => $order_title,
                     'itemQuantity' => $values->quantity,
                     'totalamount' => isset($totalamount) ? $totalamount : 0,
                    // 'vendorName' => isset($notif->vendor_name) ? $notif->vendor_name : '',
                    // 'vendorId' => isset($notif->vendorId) ? $notif->vendorId : '',
                    // 'outletId' => isset($notif->outletId) ? $notif->outletId : '',
                    // 'outlet_name' => isset($notif->outlet_name) ? $notif->outlet_name : '',
                     'request_type' => 1,
                    // "order_assigned_time" => isset($notif->assigned_time) ? $notif->assigned_time : '',
                     'notification_dialog' => "1",
                     'priority' => "",
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

  


            if ($updateStatus) {
                $insert = DB::table('salesperson_orders')
                                ->insert(['order_id' => $orderId,
                                            'salesperson_id' =>$salesPersonId,
                                            'updated_at' => date('Y-m-d H:i:s'),
                                            'created_at' => date('Y-m-d H:i:s'),
                                            'assigned_time' =>date('H:i:s')]);

                $insert = DB::table('orders')
                                ->where('id', $orderId)
                                ->update(['salesperson_id' => $salesPersonId ,'order_status' => 34]);



                 $updateStatus = DB::table('salesperson_orders')
                                
                                ->where('salesperson_orders.order_id', $orderId)
                                ->where('salesperson_orders.salesperson_id', $salesPersonId)
                                ->update(['salesmanPackStatus' => 1]);

               
                $affected = DB::update('update orders_log set order_status=?  where id = (select max(id) from orders_log where order_id = ' . $orderId . ')', array(34));

               $push =push_notification($orderId, 34,0);

               
                $result = array("status" => 1, "message" => trans("messages.SalesPerson assigned succesfully"));
            } else {
                $result = array("status" => 2, "message" => trans("messages.SalesPerson is busy please assign to the another SalesPerson."));
            }
        }

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    */
    /* public function assignSalesPerson(Request $data)
    {
        $rules = [
            'language' => ['required'],
            'salesPersonId' => ['required'],
            'orderId' => ['required'],
            
        ];

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
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            $salesPersonId = $post_data['salesPersonId'];
            $orderId = $post_data['orderId'];
                            //print_r($orderId);exit();

              
            $updateStatus=DB::table('salesperson')
                                ->where('salesperson.id', $salesPersonId)
                                ->where('salesperson.status', '=', 'F')
                                ->update(['status' =>'A']);


           $notify = DB::table('salesperson')
                ->select('salesperson.name as salesPersonName','salesperson.android_device_token as salesperson_android_token','salesperson.ios_device_token as salesperson_ios_token','salesperson.login_type')
                // ->Join('users', 'users.id', '=', 'orders.customer_id')
                // ->Join('vendors_infos', 'vendors_infos.id', '=', 'orders.vendor_id')
                // ->Join('vendors', 'vendors.id', '=', 'orders.vendor_id')
                // ->Join('outlets', 'outlets.id', '=', 'orders.outlet_id')
                // ->Join('outlet_infos','outlet_infos.id', '=', 'orders.outlet_id')
                // ->Join('order_status','order_status.id', '=', 'orders.order_status')
                // ->Join('salesperson','salesperson.id', '=', 'orders.salesperson_id')
                // ->where('orders.id', '=', (int) $orderId)
                ->where('salesperson.id', $salesPersonId)
                ->get();

            $notifys=$notify[0];
            $order_title = '' . 'New Order ';
            $description = 'New Order ' ;
            $orderStatus =34;
        
            if($notifys->login_type == 2){
                $token = $notifys->salesperson_android_token;
            }else if($notifys->login_type == 3)
            {
                $token = $notifys->salesperson_ios_token;
            }
            $token =isset($token)?$token:'';
            //$token="fory84RtWq0:APA91bGzI6ErepVnqL3jKKHzNZH_g9N-t46fhqDxANPtOU5STUirixG2MM4gB7FqEz2iDm0AlUKAcmMRDifu4mDMaw7otiMvIQRukc4xsCwqE47FeZFgcfm547t6UjOQGxu1l8tODRBy    ";

            $item =DB::select("select sum(item_unit) as quantity  from orders_info where orders_info.order_id = $orderId ");
            $detail    =DB::select("select total_amount,service_tax,coupon_amount,delivery_charge  from orders where id = $orderId ");
            $details=$detail[0];
            $sub_tot =$details->total_amount - $details->service_tax - $details->coupon_amount - $details->delivery_charge;
            if (count($item)>0) {
                $array=array();
                foreach ($item as $items => $values) {
                    $array[$items]['orderQuantity']= $values->quantity;
                }
            }
            $data = array
                (
                'status' => 1,
                'message' => $order_title,
                'detail' =>array(
                'description'=>$description,    
                    'salesPersonName' => isset($notif->salesPersonName) ? $notif->salesPersonName : '',
                    'orderId' => $orderId,
                    'orderStatus' => $orderStatus,
                    'type' => 2,
                    'title' => $order_title,
                    'itemQuantity' => $values->quantity,
                    'totalamount' => isset($sub_tot) ? $sub_tot : 0,
                    'request_type' => 1,
                    'notification_dialog' => "1",
                    'priority' => "",
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
            curl_close($ch);
            if ($updateStatus) {
                $insert = DB::table('salesperson_orders')
                                ->insert(['order_id' => $orderId,
                                            'salesperson_id' =>$salesPersonId,
                                            'updated_at' => date('Y-m-d H:i:s'),
                                            'created_at' => date('Y-m-d H:i:s'),
                                            'assigned_time' =>date('H:i:s')]);

                $insert = DB::table('orders')
                                ->where('id', $orderId)
                                ->update(['salesperson_id' => $salesPersonId ,'order_status' => 34]);



                 $updateStatus = DB::table('salesperson_orders')
                                
                                ->where('salesperson_orders.order_id', $orderId)
                                ->where('salesperson_orders.salesperson_id', $salesPersonId)
                                ->update(['salesmanPackStatus' => 1]);

               
                $affected = DB::update('update orders_log set order_status=?  where id = (select max(id) from orders_log where order_id = ' . $orderId . ')', array(34));

               $push =push_notification($orderId, 34,0);

               
                $result = array("status" => 1, "message" => trans("messages.SalesPerson assigned succesfully"));
            } else {
                $result = array("status" => 2, "message" => trans("messages.SalesPerson is busy please assign to the another SalesPerson."));
            }
        }

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }*/
     public function assignSalesPerson(Request $data)
    {
        $rules = [
            'language' => ['required'],
            'salesPersonId' => ['required_if:packingByAdmin,0'],
            'orderId' => ['required'],
            'packingByAdmin' => ['required'],
            
        ];

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
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {

            $packingByAdmin = $post_data['packingByAdmin'];
            $orderId = $post_data['orderId'];
            $salesPersonId = $post_data['salesPersonId'];
    
            if($packingByAdmin != 1)
            {
                                                //print_r($orderId);exit();

                  
                $updateStatus=DB::table('salesperson')
                                    ->where('salesperson.id', $salesPersonId)
                                    ->where('salesperson.status', '=', 'F')
                                    ->update(['status' =>'A']);


               $notify = DB::table('salesperson')
                    ->select('salesperson.name as salesPersonName','salesperson.android_device_token as salesperson_android_token','salesperson.ios_device_token as salesperson_ios_token','salesperson.login_type')
                    // ->Join('users', 'users.id', '=', 'orders.customer_id')
                    // ->Join('vendors_infos', 'vendors_infos.id', '=', 'orders.vendor_id')
                    // ->Join('vendors', 'vendors.id', '=', 'orders.vendor_id')
                    // ->Join('outlets', 'outlets.id', '=', 'orders.outlet_id')
                    // ->Join('outlet_infos','outlet_infos.id', '=', 'orders.outlet_id')
                    // ->Join('order_status','order_status.id', '=', 'orders.order_status')
                    // ->Join('salesperson','salesperson.id', '=', 'orders.salesperson_id')
                    // ->where('orders.id', '=', (int) $orderId)
                    ->where('salesperson.id', $salesPersonId)
                    ->get();

                $notifys=$notify[0];
                $order_title = '' . 'New Order ';
                $description = 'New Order ' ;
                $orderStatus =34;
            
                if($notifys->login_type == 2){
                    $token = $notifys->salesperson_android_token;
                }else if($notifys->login_type == 3)
                {
                    $token = $notifys->salesperson_ios_token;
                }
                $token =isset($token)?$token:'';
                //$token="fory84RtWq0:APA91bGzI6ErepVnqL3jKKHzNZH_g9N-t46fhqDxANPtOU5STUirixG2MM4gB7FqEz2iDm0AlUKAcmMRDifu4mDMaw7otiMvIQRukc4xsCwqE47FeZFgcfm547t6UjOQGxu1l8tODRBy    ";

                $item =DB::select("select sum(item_unit) as quantity  from orders_info where orders_info.order_id = $orderId ");
                $detail    =DB::select("select total_amount,service_tax,coupon_amount,delivery_charge  from orders where id = $orderId ");
                $details=$detail[0];
                $sub_tot =$details->total_amount - $details->service_tax - $details->coupon_amount - $details->delivery_charge;
                if (count($item)>0) {
                    $array=array();
                    foreach ($item as $items => $values) {
                        $array[$items]['orderQuantity']= $values->quantity;
                    }
                }
                $data = array
                    (
                    'status' => 1,
                    'message' => $order_title,
                    'detail' =>array(
                    'description'=>$description,    
                        'salesPersonName' => isset($notif->salesPersonName) ? $notif->salesPersonName : '',
                        'orderId' => $orderId,
                        'orderStatus' => $orderStatus,
                        'type' => 2,
                        'title' => $order_title,
                        'itemQuantity' => $values->quantity,
                        'totalamount' => isset($sub_tot) ? $sub_tot : 0,
                        'request_type' => 1,
                        'notification_dialog' => "1",
                        'priority' => "",
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
                curl_close($ch);
                if ($updateStatus) {
                    $insert = DB::table('salesperson_orders')
                                    ->insert(['order_id' => $orderId,
                                                'salesperson_id' =>$salesPersonId,
                                                'updated_at' => date('Y-m-d H:i:s'),
                                                'created_at' => date('Y-m-d H:i:s'),
                                                'assigned_time' =>date('H:i:s')]);

                    $insert = DB::table('orders')
                                    ->where('id', $orderId)
                                    ->update(['salesperson_id' => $salesPersonId ,'order_status' => 34]);



                     $updateStatus = DB::table('salesperson_orders')
                                    
                                    ->where('salesperson_orders.order_id', $orderId)
                                    ->where('salesperson_orders.salesperson_id', $salesPersonId)
                                    ->update(['salesmanPackStatus' => 1]);

                   
                    $affected = DB::update('update orders_log set order_status=?  where id = (select max(id) from orders_log where order_id = ' . $orderId . ')', array(34));

                   $push =push_notification($orderId, 34,0);

                   
                    $result = array("status" => 1, "message" => trans("messages.SalesPerson assigned succesfully"));
                } else {
                    $result = array("status" => 2, "message" => trans("messages.SalesPerson is busy please assign to the another SalesPerson."));
                }
            }else{
                
               $insert = DB::table('orders')
                        ->where('id', $orderId)
                        ->update(['salesperson_id' => 0 ,'order_status' => 36,'outlet_assign'=>1]);
                $affected = DB::update('update orders_log set order_status=?  where id = (select max(id) from orders_log where order_id = ' . $orderId . ')', array(36));
                $result = array("status" => 1, "message" => trans("messages.SalesPerson assigned succesfully"));

            }
        }

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }



    public function orderComplete(Request $data)
    {
        $rules = [
            'language' => ['required'],
            'salesPersonId' => ['required'],
            'orderId' => ['required'],
            
        ];

        $post_data = $data->all();

        $error = $result = array();
        $validator = app('validator')->make($post_data, $rules);
        if ($validator->fails()) {
            $errors = '';
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            $salesPersonId = $post_data['salesPersonId'];
            $orderId = $post_data['orderId'];


                
            //print_r($salesperson_orders);exit();

            $updateStatus=DB::table('salesperson')
                                ->where('salesperson.id', $salesPersonId)
                                ->where('salesperson.status', '=', 'A')
                                ->update(['status' =>'F']);





            $result = array("status" => 1, "message" => trans("This order  is completed"));
        }
        

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    public function get_order_detail($order_id)
    {
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

    public function updateOrderStatus(Request $data)
    {
        
        //need to add salesPersonId and outletId
        $post_data = $data->all();
        $rules = [
        //  //'userId' => ['required'],
        //  'orderId' => ['required'],
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
                $orderId = $post_data['order_id'];
                $comment = isset($post_data['comment']) ? $post_data['comment'] : '';
                $outlet_key =isset($post_data['sPin'])?$post_data['sPin']:'1223';
                $date = date("Y-m-d H:i:s");

                $order_status=$post_data['order_status_id'];

                /*$order_info=DB::table('orders_info')->select('id','pack_status')->where('orders_info.order_id', $orderId)->get();$packval = 0;
                foreach ($order_info as $key => $value) {$pack_status =isset($value->pack_status)?$value->pack_status:0;($pack_status != 1)?$packval=0:$packval=1;
                }*/
                $notify = DB::table('orders')
                    ->select('orders.assigned_time', 'users.android_device_token', 'users.ios_device_token', 'users.id as customerId ', 'users.login_type', 'users.first_name', 'vendors_infos.vendor_name', 'vendors.id as vendorId', 'orders.total_amount', 'outlets.id as outletId', 'outlet_infos.outlet_name', 'orders.driver_ids', 'orders.salesperson_id', 'orders.order_key_formated', 'order_status.name as status_name')
                    ->Join('users', 'users.id', '=', 'orders.customer_id')
                    ->Join('vendors_infos', 'vendors_infos.id', '=', 'orders.vendor_id')
                    ->Join('vendors', 'vendors.id', '=', 'orders.vendor_id')
                    ->Join('outlets', 'outlets.id', '=', 'orders.outlet_id')
                    ->Join('outlet_infos', 'outlet_infos.id', '=', 'orders.outlet_id')
                    ->Join('order_status', 'order_status.id', '=', 'orders.order_status')
                    ->where('orders.id', '=', (int) $orderId)
                    ->get();
                   //print_r($notify);exit();
                $notify =$notify[0];

                $customer_id = isset($notify->customerId)?$notify->customerId:0;
                $users = Users::find($customer_id);

                if ($post_data['order_status_id'] == 12) {
                    $data =array();
                    $driver_id=DB::table('orders')
                        ->select('driver_ids')
                        ->where('id', $orderId)
                        ->get();
                    $driverid =isset($driver_id[0]->driver_ids)?$driver_id[0]->driver_ids:0;
                    $data['driverId'] = $driverid;
                    $data['orderId'] =$orderId;
                    $delivery = commonDelivery($data); //common fun for delivery
                   
                /*delivery  confirmation mail for admin*/
                } elseif ($post_data['order_status_id'] == 1) {
                    $affected = DB::update('update orders set order_status = ?,order_comments = ? where id = ?', array($post_data['order_status_id'], $post_data['comment'], $post_data['order_id']));
                    $affected = DB::update('update orders_log set order_status=?, order_comments = ? where id = (select max(id) from orders_log where order_id = ' . $post_data['order_id'] . ')', array($post_data['order_status_id'], $post_data['comment']));
                    $affected = DB::update('update orders set request_vendor = 0 where id = ?', array($post_data['order_id']));

                    /*mail fun for user*/
                    $subject = 'Order Confirmation - Your Order with ' . getAppConfig()->site_name . ' [' . $notify->order_key_formated . '] has been successfully placed!';
                    $values = array('order_id' => $orderId,
                        'customer_id' => $notify->customerId,
                        'vendor_id' => $notify->vendorId,
                        'outlet_id' => $notify->outletId,
                        'message' => $subject,
                        'read_status' => 0,
                        'created_date' => date('Y-m-d H:i:s'));
                    DB::table('notifications')->insert($values);
                    
                    $to = $users->email;
                    $template = DB::table('email_templates')->select('*')->where('template_id', '=', self::ORDER_MAIL_TEMPLATE)->get();

                    if (count($template)) {
                        $from = $template[0]->from_email;
                        $from_name = $template[0]->from;
                        if (!$template[0]->template_id) {
                            $template = 'mail_template';
                            $from = getAppConfigEmail()->contact_mail;
                        }
                        $subject = 'Your Order with ' . getAppConfig()->site_name . ' [' . $notify->order_key_formated . '] has been successfully Placed!';
                        $orderId = encrypt($orderId);
                        $reviwe_id = base64_encode('123abc');
                        $orders_link = '<a href="' . URL::to("order-info/" . $orderId) . '" title="' . trans("messages.View") . '">' . trans("messages.View") . '</a>';
                        $review_link = '<a href="' . URL::to("order-info/" . $orderId . '?r=' . $reviwe_id) . '" title="' . trans("messages.View") . '">' . trans("messages.View") . '</a>';
                        $content = array('name' => "" . $users->name, 'order_key' => "" . $notify->order_key_formated, 'status_name' => "" . $notify->status_name, 'orders_link' => "" . $orders_link, "review_link" => $review_link);

                        $attachment = "";
                        $email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
                    }
                }
                elseif ($post_data['order_status_id'] == 33) {

                     $spin_count=DB::table('orders')
                        ->select('orders.id','outlet_managers.outlet_key')
                        ->leftJoin('outlet_managers', 'outlet_managers.outlet_id', '=', 'orders.outlet_id')
                         ->where('orders.id', $orderId)
                         ->where('outlet_managers.outlet_key', '=',$outlet_key)
                        ->count();
                      // print_r($spin_count);exit();
                        if($spin_count != 0)
                        {
                            $result = array("status" => 1, "message" => trans("messages.Order Status updated successfully"));

                            $affected = DB::update('update orders set order_status = ?,order_comments = ? where id = ?', array($post_data['order_status_id'], $post_data['comment'], $post_data['order_id']));
                            $affected = DB::update('update orders_log set order_status=?, order_comments = ? where id = (select max(id) from orders_log where order_id = ' . $post_data['order_id'] . ')', array($post_data['order_status_id'], $post_data['comment']));

                        }else{
                            $result = array("status" => 2, "message" => trans("messages.Invalid spin"));
                        }
                }else {
                    $affected = DB::update('update orders set order_status = ?,order_comments = ? where id = ?', array($post_data['order_status_id'], $post_data['comment'], $post_data['order_id']));
                    $affected = DB::update('update orders_log set order_status=?, order_comments = ? where id = (select max(id) from orders_log where order_id = ' . $post_data['order_id'] . ')', array($post_data['order_status_id'], $post_data['comment']));
                    $updateStatus=DB::table('orders_info')
                            ->where('orders_info.order_id', $post_data['order_id'])
                           // ->where('orders_info.item_id', $value['productId'])
                            ->update(['pack_status' => 1]);
                    //print_r("expression");exit;
                    $orders_info=DB::table('orders')
                        ->select('salesperson_id','outlet_assign')
                        ->where('id', $orderId)
                        ->get();
                    $outlet_assign=isset($orders_info[0]->outlet_assign)?$orders_info[0]->outlet_assign:0;
                    if($outlet_assign != 1)
                    {
                        $salesperson_id= isset($orders_info[0]->salesperson_id)?$orders_info[0]->salesperson_id:0;
                        
                        DB::table('salesperson')
                            ->where('salesperson.id', $salesperson_id)
                       
                            ->update(['status' =>'F']);

                        $updateStatus=DB::table('salesperson_orders')
                            ->where('salesperson_orders.order_id', $orderId)
                            ->update(['salesmanPackStatus' => 2]);
                        $notifyss = DB::table('salesperson')
                            ->select('salesperson.name as salesPersonName','salesperson.android_device_token as salesperson_android_token','salesperson.ios_device_token as salesperson_ios_token','salesperson.login_type')
                            ->where('salesperson.id', '=', (int)$salesperson_id)
                            ->get();  

                        $notif=$notifyss[0];
                        $order_title = '' . 'New Order';
                        $description =  'New Order ';

                        if($notif->login_type == 2){
                            $token =$notif->salesperson_android_token;
                        }
                        else if($notif->login_type == 3)
                        {
                            $token = $notif->salesperson_ios_token;
                        }
                        $token =isset($token)?$token:'';

                        //fswFUYS_TzI:APA91bHTAk83zJu1LbiPofoKY9wDxcg7GO8pPZ9ZMO-BfdIwn5pO2ZZ1jB6_jRRsvtQhW8nwoi_hF96hC2xCC-d5CWkMpSmjFJX1yY-16oZUyZccWBW-6n3qQOQZLS07m4ER31ZXLdCS
                        $data = array
                            (
                            'status' => 1,
                            'message' => $order_title,
                            'detail' =>array(
                            'description'=>$description,    
                    
                             'salesPersonName' => isset($notif->salesPersonName) ? $notif->salesPersonName : '',
                            // 'orderId' => $orderId,
                            // 'driverId' => isset($notif->driver_ids) ? $notif->driver_ids : '',
                            // 'orderStatus' => $orderStatus,
                            // 'type' => 2,
                             'title' => $order_title,
                            // 'totalamount' => isset($notif->total_amount) ? $notif->total_amount : 0,
                            // 'vendorName' => isset($notif->vendor_name) ? $notif->vendor_name : '',
                            // 'vendorId' => isset($notif->vendorId) ? $notif->vendorId : '',
                            // 'outletId' => isset($notif->outletId) ? $notif->outletId : '',
                            // 'outlet_name' => isset($notif->outlet_name) ? $notif->outlet_name : '',
                             'request_type' => 1,
                            // "order_assigned_time" => isset($notif->assigned_time) ? $notif->assigned_time : '',
                            // 'notification_dialog' => "1",
                        ));

                            //print_r(json_encode($data));exit();

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
                    $affected = DB::update('update orders set request_vendor = 0 where id = ?', array($post_data['order_id']));
                    if ($order_status != 33) {
                        $notify=push_notification($orderId, $order_status,0);
                    }
                    //$order_detail = $this->get_order_detail($post_data['order_id']);
                    $result = array("status" => 1, "message" => trans("messages.Order Status updated successfully"));

           
                }

                
            } catch (JWTException $e) {
                $result = array("status" => 2, "message" => trans("messages.Something went wrong"));
            } catch (TokenExpiredException $e) {
                $result = array("status" => 2, "message" => trans("messages.Something went wrong"));
            }


            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    /*public function orderItemDetails(Request $data)
    {
        $post_data = $data->all();
    
        App::setLocale('en');
 
        $language_id = 1;


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

        /*where ' . $query3 . ' AND ' . $query4 . ' AND o.id = ? AND o.customer_id= ? ORDER BY o.id ', array($post_data['orderId'], $post_data['userId']));/

  

        
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
        $qry = 'oi.replacement_product_id = 0 OR oi.replacement_product_id = null';
        $order_items = DB::select('SELECT p.product_image, pi.description,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.id as order_id,o.invoice_id,pi.product_name,pi.description,o.coupon_amount,weight_classes_infos.title,weight_classes_infos.unit as unit_code,o.order_key_formated,p.weight,oi.replacement_product_id,oi.id,oi.additional_comments,oi.adjust_weight_qty,oi.pack_status,p.adjust_weight
        FROM orders o
        LEFT JOIN orders_info oi ON oi.order_id = o.id
        LEFT JOIN products p ON p.id = oi.item_id
        LEFT JOIN products_infos pi ON pi.id = p.id
        LEFT JOIN weight_classes ON weight_classes.id = p.weight_class_id
        LEFT JOIN weight_classes_infos ON weight_classes_infos.id = weight_classes.id
        where ' . $query . ' AND ' . $wquery . ' AND o.id = ? ORDER BY oi.id', array($post_data['orderId']));
        // where ' . $query . ' AND ' . $wquery . ' AND ' . $qry . ' AND o.id = ? ORDER BY oi.id', array($post_data['orderId']));

       // echo"<pre>";print_r($order_items);exit;
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
            ->first();

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
        out_infos.contact_address,out.latitude as outlet_latitude,out.longitude as outlet_longitude,o.coupon_amount, u.email,o.driver_ids,dr.ratings,tr.ratings as rating,u.name as customer_name,drivers.first_name as driver_name
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
          left join outlet_infos out_infos on out_infos.id = out.id where
       ' . $query2 . ' AND ' . $oquery . ' AND o.id = ?', array($post_data['orderId']));
        // print_r($delivery_details);exit;
        // $delivery_details = DB::select('SELECT o.delivery_instructions as deliveryInstructions,ua.address ,pg.id as paymentGatewayId,pgi.name,o.total_amount as totalAmount,o.delivery_charge as deliverCharge,o.service_tax as serviceTax,dti.start_time ,end_time,o.created_date as createdDate,o.delivery_date as deliveryDate,o.order_type as orderType,out_infos.contact_address as contactAddress,o.coupon_amount as couponAmount, u.email FROM orders o LEFT JOIN user_address ua ON ua.id = o.delivery_address  LEFT JOIN users u ON u.id = ua.user_id  left join payment_gateways pg on pg.id = o.payment_gateway_id left join payment_gateways_info pgi on pgi.payment_id = pg.id left join delivery_time_slots dts on dts.id=o.delivery_slot left join delivery_time_interval dti on dti.id = dts.time_interval_id left join outlets out on out.id = o.outlet_id left join outlet_infos out_infos on out_infos.id = out.id where '.$query2.' AND '.$oquery.' AND o.id = ? AND o.customer_id= ?',array($post_data['orderId'],$post_data['userId']));
        // print_r($delivery_details);exit;
        foreach ($delivery_details as $k => $v) {
            // print_r($v);exit;
            $delivery_details[$k]->start_time = ($v->start_time != '') ? $v->start_time : '';
            $delivery_details[$k]->end_time = ($v->end_time != '') ? $v->end_time : '';
            $delivery_details[$k]->user_contact_address = ($v->user_contact_address != '') ? $v->user_contact_address : '';
            $delivery_details[$k]->contact_address = ($v->contact_address != '') ? $v->contact_address : '';
            $delivery_details[$k]->email = ($v->email != '') ? $v->email : '';
            $sub_total = ($v->total_amount) - ($v->delivery_charge + $v->service_tax) + ($v->coupon_amount);
            $delivery_details[$k]->sub_total = $sub_total;
            $tax_amount = $sub_total * $v->service_tax / 100;
            $delivery_details[$k]->tax_amount = $tax_amount;
            $delivery_details[$k]->userId = $v->user_id;
            $delivery_details[$k]->driverId =$v->driver_ids;
        }
       


        // $tracking_orders[0]['code']="1";
        // $tracking_orders[0]['name']="Initiated";

        // $tracking_orders[0]['code']="1";
        // $tracking_orders[0]['name']="Initiated";

        // $tracking_orders[0]['code']="1";
        // $tracking_orders[0]['name']="Initiated";

        // $tracking_orders[0]['code']="1";
        // $tracking_orders[0]['name']="Initiated";

        // $tracking_orders[0]['code']="1";
        // $tracking_orders[0]['name']="Initiated";



        $tracking_orders = array(1 => "Initiated", 10 => "Processed", 18 => "Packed", 19 => "Dispatched", 12 => "Delivered");


        $t =$y= 0;

        $last_state = $mob_last_state = "";


        $tracking_result = $mob_tracking_result = array();
        foreach ($tracking_orders as $key => $track) {
                      
        /*  $mob_tracking_result[$t]['text'] = $track;
            $mob_tracking_result[$t]['process'] = "0";
            $mob_tracking_result[$t]['order_comments'] = "";
            $mob_tracking_result[$t]['date'] = "";
            /
            $tracking_result[$key]['code'] = $key;
            $tracking_result[$key]['text'] = $track;
            $tracking_result[$key]['process'] = "0";
            $tracking_result[$key]['order_comments'] = "";
            $tracking_result[$key]['date'] = "";

            //print_r($tracking_result);echo"....";

            
            $check_status = DB::table('orders_log')
                ->select('order_id', 'log_time', 'order_comments')
                ->where('order_id', '=', $post_data['orderId'])
                ->where('order_status', '=', $key)
                ->first();

                 
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
          

                $y++;
            }
            $t++;
        }
        //exit;
        //prasanth edit
        $deliverynew = new \stdClass();
        $deliverynew->driverId = isset($delivery_details[0]->driverId) ? $delivery_details[0]->driverId:"" ;
        $deliverynew->deliveryInstructions = $delivery_details[0]->delivery_instructions;
        $deliverynew->customerName = $delivery_details[0]->customer_name;
        $deliverynew->driverName = isset($delivery_details[0]->driver_name) ? $delivery_details[0]->driver_name:"";
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
                // print_r($sum);exit;

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
                $produceInfo[$k]['invoicePdf'] = $data->invoic_pdf;
         
                $weight = isset($data->weight)?$data->weight:$data->weight;
                $produceInfo[$k]['weight'] =$weight;
                $adjust_weight_qty= isset($data->adjust_weight_qty)?$data->adjust_weight_qty:"";
                $weight_last = !empty($data->adjust_weight_qty)?$data->adjust_weight_qty:$data->weight;
                if ($data->adjust_weight == 1) {
                   /* $qntyweight = $weight * $values->item_unit ;
                    $produceInfo[$k]['weight'] = $qntyweight;
                    $weight_last = $qntyweight+$adjust_weight_qty;/

                    $qntyweight = $weight * $values->item_unit ;
                    $produceInfo[$k]['weight'] = $adjust_weight_qty;
                    $weight_last = $adjust_weight_qty;
                } else {
                    $weight_last =$weight_last *$values->item_unit;
                }
                  //  print_r($weight_last);exit;

                $itemprice =  $data->item_cost / $data->weight;                
                $amount =$weight_last * $itemprice;
              ///  print_r($amount);exit();
                if($amount !=0){$amounts = $amount;}else{$amounts= $valu->total;}

                $produceInfo[$k]['totalAmount'] = $amounts;

           
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
       //exit;

       // echo"<pre>";print_r($produceInfo);exit;

        $orderData = new \stdClass();
        $orderData->orderId = $vendor_info[0]->order_id;

        $order_info=DB::select("select SUM(item_unit) as item_unit from orders_info where order_id = $data->order_id");

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
     
        $result = array("response" => array("status" => 2, "message" => "no items found", "order_items" => array(), "deliveryDetails" => array(),   "lastState" => $last_state, "return_reasons" => $return_reasons,"reviews" => $reviews));
        if (count($order_items) > 0 && count($delivery_details) > 0 && count($vendor_info) > 0) {
            $result = array("status" => 1, "message" => "order items", "orderProductList" => $produceInfo, "deliveryDetails" => $deliverynew, "orderData" => $orderData, "mob_return_reasons" => $mob_return_reasons,"return_reasons" => $return_reasons, "lastState" => $last_state, "trackData" => $mob_tracking_result, "reviews" => $reviews, "order_id_encrypted" => encrypt($post_data['orderId'])); //, "mob_delivery_details" => $delivery
        }
        return json_encode($result);
    }
    */
    public function orderItemDetails_copy(Request $data)
    {
        $post_data = $data->all();
    
        App::setLocale('en');
 
        $language_id = 1;


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
        $qry = 'oi.replacement_product_id = 0 OR oi.replacement_product_id = null';
        $order_items = DB::select('SELECT p.product_image, pi.description,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.id as order_id,o.invoice_id,pi.product_name,pi.description,o.coupon_amount,weight_classes_infos.title,weight_classes_infos.unit as unit_code,o.order_key_formated,p.weight,oi.replacement_product_id,oi.id,oi.additional_comments,oi.adjust_weight_qty,oi.pack_status,p.adjust_weight
        FROM orders o
        LEFT JOIN orders_info oi ON oi.order_id = o.id
        LEFT JOIN products p ON p.id = oi.item_id
        LEFT JOIN products_infos pi ON pi.id = p.id
        LEFT JOIN weight_classes ON weight_classes.id = p.weight_class_id
        LEFT JOIN weight_classes_infos ON weight_classes_infos.id = weight_classes.id
        where ' . $query . ' AND ' . $wquery . ' AND o.id = ? ORDER BY oi.id', array($post_data['orderId']));
        // where ' . $query . ' AND ' . $wquery . ' AND ' . $qry . ' AND o.id = ? ORDER BY oi.id', array($post_data['orderId']));

       // echo"<pre>";print_r($order_items);exit;
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
            ->first();

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
        o.replace,
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
        out_infos.contact_address,out.latitude as outlet_latitude,out.longitude as outlet_longitude,o.coupon_amount, u.email,o.driver_ids,dr.ratings,tr.ratings as rating,u.name as customer_name,drivers.first_name as driver_name,o.used_wallet_amount
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
          left join outlet_infos out_infos on out_infos.id = out.id where
       ' . $query2 . ' AND ' . $oquery . ' AND o.id = ?', array($post_data['orderId']));
        //print_r($delivery_details);exit;
        // $delivery_details = DB::select('SELECT o.delivery_instructions as deliveryInstructions,ua.address ,pg.id as paymentGatewayId,pgi.name,o.total_amount as totalAmount,o.delivery_charge as deliverCharge,o.service_tax as serviceTax,dti.start_time ,end_time,o.created_date as createdDate,o.delivery_date as deliveryDate,o.order_type as orderType,out_infos.contact_address as contactAddress,o.coupon_amount as couponAmount, u.email FROM orders o LEFT JOIN user_address ua ON ua.id = o.delivery_address  LEFT JOIN users u ON u.id = ua.user_id  left join payment_gateways pg on pg.id = o.payment_gateway_id left join payment_gateways_info pgi on pgi.payment_id = pg.id left join delivery_time_slots dts on dts.id=o.delivery_slot left join delivery_time_interval dti on dti.id = dts.time_interval_id left join outlets out on out.id = o.outlet_id left join outlet_infos out_infos on out_infos.id = out.id where '.$query2.' AND '.$oquery.' AND o.id = ? AND o.customer_id= ?',array($post_data['orderId'],$post_data['userId']));
        // print_r($delivery_details);exit;
        foreach ($delivery_details as $k => $v) {
           // print_r($v);exit;
            $delivery_details[$k]->start_time = ($v->start_time != '') ? $v->start_time : '';
            $delivery_details[$k]->end_time = ($v->end_time != '') ? $v->end_time : '';
            $delivery_details[$k]->user_contact_address = ($v->user_contact_address != '') ? $v->user_contact_address : '';
            $delivery_details[$k]->contact_address = ($v->contact_address != '') ? $v->contact_address : '';
            $delivery_details[$k]->email = ($v->email != '') ? $v->email : '';
           // $sub_total = ($v->total_amount) - ($v->delivery_charge + $v->service_tax) - ($v->coupon_amount);
            $wallet_amt =isset($v->used_wallet_amount)?$v->used_wallet_amount:0;

            $sub_total = $v->total_amount - ($v->delivery_charge + $v->service_tax) + ($v->coupon_amount) +$wallet_amt;
           /* print_r($sub_total);exit();
            $sub_total = $sub_total + $wallet_amt;
            print_r($v->total_amount);echo"<pre>";print_r($v->delivery_charge);echo"<pre>";print_r($v->service_tax);echo"<pre>";print_r($v->coupon_amount);echo"<pre>";print_r($v->used_wallet_amount);exit();*/
            // print($v->total_amount);echo"<br>";print($v->delivery_charge);echo"<br>";print($v->service_tax);echo"<br>";print($sub_total);exit();
            // print_r($sub_total);exit();
            $delivery_details[$k]->sub_total = $sub_total;
            $tax_amount = $sub_total * $v->service_tax / 100;
            $delivery_details[$k]->tax_amount = $tax_amount;
            $delivery_details[$k]->userId = $v->user_id;
            $delivery_details[$k]->driverId =$v->driver_ids;
        }
       


        // $tracking_orders[0]['code']="1";
        // $tracking_orders[0]['name']="Initiated";

        // $tracking_orders[0]['code']="1";
        // $tracking_orders[0]['name']="Initiated";

        // $tracking_orders[0]['code']="1";
        // $tracking_orders[0]['name']="Initiated";

        // $tracking_orders[0]['code']="1";
        // $tracking_orders[0]['name']="Initiated";

        // $tracking_orders[0]['code']="1";
        // $tracking_orders[0]['name']="Initiated";



        $tracking_orders = array(1 => "Initiated", 10 => "Processed", 18 => "Packed", 19 => "Dispatched", 12 => "Delivered");


        $t =$y= 0;

        $last_state = $mob_last_state = "";


        $tracking_result = $mob_tracking_result = array();
        foreach ($tracking_orders as $key => $track) {
                      
        /*  $mob_tracking_result[$t]['text'] = $track;
            $mob_tracking_result[$t]['process'] = "0";
            $mob_tracking_result[$t]['order_comments'] = "";
            $mob_tracking_result[$t]['date'] = "";
            */
            $tracking_result[$key]['code'] = $key;
            $tracking_result[$key]['text'] = $track;
            $tracking_result[$key]['process'] = "0";
            $tracking_result[$key]['order_comments'] = "";
            $tracking_result[$key]['date'] = "";

            //print_r($tracking_result);echo"....";

            
            $check_status = DB::table('orders_log')
                ->select('order_id', 'log_time', 'order_comments')
                ->where('order_id', '=', $post_data['orderId'])
                ->where('order_status', '=', $key)
                ->first();

                 
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
          

                $y++;
            }
            $t++;
        }
        //exit;
        //prasanth edit
        $deliverynew = new \stdClass();
        $deliverynew->driverId = isset($delivery_details[0]->driverId) ? $delivery_details[0]->driverId:"" ;
        $deliverynew->deliveryInstructions = $delivery_details[0]->delivery_instructions;
        $deliverynew->customerName = $delivery_details[0]->customer_name;
        $deliverynew->driverName = isset($delivery_details[0]->driver_name) ? $delivery_details[0]->driver_name:"";
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
        $k = $subtot = 0;
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
                $subtot += $valu->total;

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
               // print_r($deliverynew->replace);echo "<br>";
                if($deliverynew->replace == 1)
                {
                  $amount =  $valu->total;
                }

                if($amount !=0){$amounts = $amount;}else{$amounts= $valu->total;}
                $produceInfo[$k]['totalAmount'] = $amounts;
                $produceInfo[$k]['adjustmentWeight'] = $adjust_weight_qty;
                $produceInfo[$k]['adjust'] =0 ;
                $produceInfo[$k]['netWeight'] =$data->weight * $data->item_unit ;
                if ($data->adjust_weight_qty !=0 || $data->adjust_weight_qty !=null) {
                    $produceInfo[$k]['adjust'] = 1;
                }
            $k++;
            }
        }
        //exit;

        //echo"<pre>";print_r($subtot);exit;

        $orderData = new \stdClass();
        $orderData->orderId = $vendor_info[0]->order_id;

        $order_info=DB::select("select SUM(item_unit) as item_unit from orders_info where order_id = $data->order_id");

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
     
        $result = array("response" => array("status" => 2, "message" => "no items found", "order_items" => array(), "deliveryDetails" => array(),   "lastState" => $last_state, "return_reasons" => $return_reasons,"reviews" => $reviews));
        if (count($order_items) > 0 && count($delivery_details) > 0 && count($vendor_info) > 0) {
            $result = array("status" => 1, "message" => "order items", "orderProductList" => $produceInfo, "deliveryDetails" => $deliverynew, "orderData" => $orderData, "mob_return_reasons" => $mob_return_reasons,"return_reasons" => $return_reasons, "lastState" => $last_state, "trackData" => $mob_tracking_result, "reviews" => $reviews, "order_id_encrypted" => encrypt($post_data['orderId'])); //, "mob_delivery_details" => $delivery
        }
        return json_encode($result);
    }

    public function orderItemDetails(Request $data)
    {
        $post_data = $data->all();
    
        App::setLocale('en');

        $rules = array(
            'orderId' => 'required',
            'outletId' => 'required',
            'vendorId' => 'required',

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
 
            $language_id = 1;
            $details = orderdetails($post_data['orderId'],$post_data['outletId'],$post_data['vendorId'],$language_id,'',0);
            $return_reasons = $this->return_reason($language_id);
            $mob_return_reasons = $this->mob_return_reason($language_id);
            $result = array("response" => array("status" => 2, "message" => "no items found", "order_items" => array(), "deliveryDetails" => array(),   "lastState" =>$details['last_state'], "return_reasons" => $return_reasons,"reviews" => $details['reviews']));
            if (count($details['produceInfo']) > 0 && count($details['deliverynew']) > 0) {
                $result = array("status" => 1, "message" => "order items", "orderProductList" => $details['produceInfo'], "deliveryDetails" => $details['deliverynew'], "orderData" => $details['orderData'], "mob_return_reasons" => $mob_return_reasons,"return_reasons" => $return_reasons, "lastState" => $details['last_state'], "trackData" => $details['mob_tracking_result'], "reviews" => $details['reviews'], "order_id_encrypted" => encrypt($post_data['orderId']));
            }
        }
        return json_encode($result);

    } 

    public function mob_return_reason($language)
    {
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
    
    public function return_reason($language)
    {
        $return_reasons = array();
        $result = array("response" => array("httpCode" => 400, "status" => false, "return_reasons" => $return_reasons));
        $query = '"return_reason"."lang_id" = (case when (select count(*) as totalcount from return_reason where return_reason.lang_id = ' . $language . ') > 0 THEN ' . $language . ' ELSE 1 END)';
        $return_reasons = DB::table('return_reason')
            ->whereRaw($query)
            ->orderBy('id', 'asc')
            ->get();
        $reasons_array = array();
        
    
        foreach ($return_reasons as $reasons) {
        }
    
        foreach ($return_reasons as $index => $value) {
            $reasons_array[$index]['id'] =$value->id;
            $reasons_array[$index]['name'] =$value->name;
        }

    
        return $reasons_array;
    }

    public function outletgetcoreconfig(Request $data)
    {
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
            //$driver_core = driver_cores::find($id);
            //print_r($driver_core);exit();
            $static = array();
            $static['data']['updateMessage'] =  "Kindly update the app";
            $static['data']['forceMessage'] =  "Kindly update the app";
            $static['data']['forceUpdateVersion'] =  1;
            $static['data']['latestVersion'] =  1;
            $static['data']['appName'] = "Outlet";
            $static['data']['appLogo'] =  "https://randomuser.me/api/portraits/men/82.jpg";
            $static['data']['loginLogo'] =  "https://randomuser.me/api/portraits/men/82.jpg";
            $static['data']['countryCode'] =  "+91";
           
            $static['data']['androidKey'] =  "KSDGKSKDDSKGKCVVFD";
            $static['data']['noImageUrl'] =  "https://randomuser.me/api/portraits/men/82.jpg";
            $static['data']['errorReportCase'] =  "error_report";
            $static['data']['socket_url'] =  "http://localhost/gov/AdminLTE-2.4.5/";
            $static['status'] = 1;
            $static['message'] = "success";
            $result =json_encode($static);
        }

        return $result;
    }

    //delivery common api:
    public function delivery_copy(Request $data)
    {
        $post_data = $data->all();
        $rules = [
        //  //'userId' => ['required'],
        //  'orderId' => ['required'],
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
            $result = array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            try {

       // print_r($post_data);exit();
                $driverId = $post_data['driverId'];
                $order_id = $post_data['orderId'];
                $date = date("Y-m-d H:i:s");
                $comment = isset($post_data['comment']) ? $post_data['comment'] : '';

                $status_change = DB::update('update orders set order_status = 12 where id = ' . $order_id . '');

                $affected = DB::update('update orders_log set order_status=?, order_comments = ?, log_time = ? where id = (select max(id) from orders_log where order_id = ' . $order_id . ')', array(12, $comment, $date));


                $affected = DB::update('update orders set order_status = ?,order_comments = ? where id = ?', array(12, $comment, $order_id));




                $affected = DB::update('update drivers set driver_status =1  where id = ?', array($driverId));

                $notify=$this->order_delivery($driverId, $order_id, $comment);


        
                $result = array("status" => 1, "message" => trans("messages.Order Status updated successfully"));
            } catch (JWTException $e) {
                $result = array("status" => 2,  "message" => trans("messages.Something went wrong"));
            } catch (TokenExpiredException $e) {
                $result = array("status" => 2,  "message" => trans("messages.Something went wrong"));
            }
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function order_delivery($driverId, $order_id, $comment)
    {
        // print_r("fhfgh");exit();
        $notify = DB::table('orders')
             ->select('orders.assigned_time', 'users.android_device_token', 'users.ios_device_token', 'users.login_type', 'drivers.first_name', 'vendors_infos.vendor_name', 'orders.total_amount', 'orders.customer_id', 'orders.order_key_formated', 'orders.vendor_id', 'orders.outlet_id', 'order_status.name as status_name', 'orders.salesperson_id', 'outlet_infos.outlet_name')
             ->leftJoin('users', 'users.id', '=', 'orders.customer_id')
             ->leftJoin('order_status', 'orders.order_status', '=', 'order_status.id')

             ->leftJoin('vendors_infos', 'vendors_infos.id', '=', 'orders.vendor_id')

             ->leftJoin('drivers', 'drivers.id', '=', 'orders.driver_ids')
             ->leftJoin('outlet_infos', 'outlet_infos.id', '=', 'orders.outlet_id')

             ->where('orders.id', '=', (int) $order_id)
             ->get();
        //print_r($notify);exit;


        $referral =getreferral();
        $customer_id = isset($notify[0]->customer_id)?$notify[0]->customer_id:0;
        $users_details=DB::table('customer_referral')
                       ->select('*')
                       ->where('customer_referral.customer_id', $customer_id)
                       ->where('customer_referral.referal_amount_used', '!=', '1')
                       ->get();
        // print_r($users_details);exit;
        if ($users_details && $users_details[0]->referal_amount_used !=1) {
            $count_order=DB::table('orders')
                ->select('*')
                ->where('orders.customer_id', $customer_id)
                ->count();
            //print_r($count_order);exit;
            $wallet_details=DB::table('users')
                ->select('wallet_amount')
                ->where('users.id', $users_details[0]->referred_by)
                ->get();
            $wallet_amount = isset($wallet_details[0]->wallet_amount)?$wallet_details[0]->wallet_amount:0;
            if ($count_order == $referral[0]->order_to_complete) {
                $wallet_amount = $wallet_amount + $referral[0]->referred_amount;
                //print_r($referral);exit;
                $affected = DB::update('update users set wallet_amount =?  where id = ?', array($wallet_amount,$users_details[0]->referred_by));
                $affected = DB::update('update customer_referral set referal_amount_used =1  where id = ?', array($users_details[0]->id));
            }
        }

        if (count($notify) > 0 && $notify[0]->login_type != 1) {
            $notifys = $notify[0];
            $order_title = 'your order is delivered';

            if ($notifys->login_type == 2) {// android device
                $token = $notifys->android_device_token;
            } elseif ($notifys->login_type == 3) {
                $token = $notifys->ios_device_token;
            }
            $token =isset($token)?$token:'';
            $data = array(
             'id' => $order_id,
             'driverId' => $driverId,
             'orderId' => $order_id,
             'orderStatus' => 12,
             'type' => 2,
             'title' => $order_title,
             'message' => $order_title,
             'totalamount' => isset($notifys->total_amount) ? $notifys->total_amount : 0,
             'vendorName' => isset($notifys->vendor_name) ? $notifys->vendor_name : '',
             'vendorId' => isset($notifys->vendorId) ? $notifys->vendorId : '',
                'outletId' => isset($notifys->outletId) ? $notifys->outletId : '',
                'outlet_name' => isset($notifys->outlet_name) ? $notifys->outlet_name : '',
             'request_type' => 1,
             "order_assigned_time" => isset($notifys->assigned_time) ? $notifys->assigned_time : '',
             'notification_dialog' => "1",
         );

            $fields = array(
             'registration_ids' => array($token),
             //'data' => $data,
             'notification' => array('title' => $order_title, 'body' =>  $data ,'sound'=>'Default','image'=>'Notification Image')
         );

            /*  $headers = array
                (
                'Authorization: key=AAAAI_fAV4w:APA91bFSR1TLAn1Vh134nzXLznsUVYiGnR4KiUYdAa3u0OccC5S-DyDdQRdnR0XugSRArsJGXC8AHE342eNhBbnK8np10KuyuWwiJxtndV75O4DyT3QCGXKFu_fwUTNPdB51Cno6Rewc',
                'Content-Type: application/json',
            );*/
            $headers = array(
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

        $notify =$notify[0];
        $users = Users::find($notify->customer_id);



        $subject = 'Your Order with ' . getAppConfig()->site_name . ' [' . $notify->order_key_formated . '] has been successfully Delivered!';
        $values = array('order_id' => $order_id,
             'customer_id' => $notify->customer_id,
             'vendor_id' => $notify->vendor_id,
             'outlet_id' => $notify->outlet_id,
             'message' => $subject,
             'read_status' => 0,
             'created_date' => date('Y-m-d H:i:s'));
        DB::table('notifications')->insert($values);



        /*Ram(20-08-19):*/

        $date = date("Y-m-d H:i:s");

        DB::insert('insert into outlet_reviews(customer_id,order_id,vendor_id,outlet_id,ratings,created_date
             ) values(?,?,?,?,?,?)', [$notify->customer_id, $order_id,$notify->vendor_id,$notify->outlet_id,"-2",$date]);


        DB::insert('insert into driver_reviews(customer_id,order_id,vendor_id,outlet_id,driver_id
             ) values(?,?,?,?,?)', [$notify->customer_id, $order_id,$notify->vendor_id,$notify->outlet_id,$driverId]);


        $salesperson_id=isset($notifys->salesperson_id)?$notifys->salesperson_id:0;

        if ($salesperson_id !=0) {
            $updateStatus=DB::table('salesperson')
                              ->where('salesperson.id', $salesperson_id)
                                       
                              ->update(['status' =>'F']);
        }


        /*delivery mail for user*/

        $to = $users->email;


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

        /*delivery  confirmation mail for admin*/

        $template = DB::table('email_templates')
                 ->select('*')
                 ->where('template_id', '=', self::DRIVER_ORDER__DELIVERED_RESPONSE_ADMIN_TEMPLATE)
                 ->get();

        // print_r($template);exit();

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
            
        /*delivery  confirmation mail for admin*/


        return 1;
    }
    /*public function push_notification($orderId,$order_status)
    {
       // print_r($order_status);exit;
        $notify = DB::table('orders')
                ->select('orders.assigned_time', 'users.android_device_token', 'users.ios_device_token','users.id as customerId ','users.login_type', 'users.first_name', 'vendors_infos.vendor_name','vendors.id as vendorId','orders.total_amount','outlets.id as outletId','outlet_infos.outlet_name','orders.driver_ids', 'orders.salesperson_id','orders.order_key_formated','order_status.name as status_name')
                ->Join('users', 'users.id', '=', 'orders.customer_id')
                ->Join('vendors_infos', 'vendors_infos.id', '=', 'orders.vendor_id')
                ->Join('vendors', 'vendors.id', '=', 'orders.vendor_id')
                ->Join('outlets', 'outlets.id', '=', 'orders.outlet_id')
                ->Join('outlet_infos','outlet_infos.id', '=', 'orders.outlet_id')
                ->Join('order_status','order_status.id', '=', 'orders.order_status')
                ->where('orders.id', '=', (int) $orderId)
                ->get();
        //print_r($notify);exit;

        if (count($notify) > 0 && $notify[0]->login_type != 1 ) {
            $notifys = $notify[0];


                if($order_status==1){
                    $order_title = '' . 'order placed';
                    $description = '' . 'your placed order successfully';
                    $orderStatus =1;
                }
                if($order_status==10){
                    $order_title = '' . 'processed';
                    $description = '' . 'your placed order is processed successfully';
                    $orderStatus =10;
                }
                if($order_status==18){
                    $order_title = '' . 'packed';
                    $description = '' . 'your placed order is packed successfully';
                    $orderStatus =18;
                }

                if($order_status==11){
                    $order_title = '' . 'cancelled';
                    $description = '' . 'your placed order is cancelled successfully';
                    $orderStatus =11;
                }

                if($order_status==12){


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
                    $reviews->order_id = $orderId;
                    $reviews->save();

                    $updateStatus=DB::table('salesperson')
                            ->where('salesperson.id', $notifys->salesperson_id)

                            ->update(['status' =>'F']);

                    $driverFree=DB::table('drivers')
                            ->where('id', $notifys->driver_ids)

                            ->update(['driver_status' =>1]);


                    $delivery_date=DB::table('orders')
                            ->where('id', $orderId)

                            ->update(['delivery_date' =>date("Y-m-d H:i:s")]);


                }

                if($order_status==14){
                    $order_title = '' . 'shipped';
                    $description = '' . 'your placed order is shipped successfully';
                    $orderStatus =14;
                }

                if($order_status==19){
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
                'data' => array('title' => $order_title, 'body' =>  $data ,'sound'=>'Default','image'=>'Notification Image')


            );
               // echo"<pre>";print_r($fields);exit;
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


            $notify =$notify[0];
            $users = Users::find($notify->customerId);







        if($order_status==1){

             $subject = 'Order Confirmation - Your Order with ' . getAppConfig()->site_name . ' [' . $notify->order_key_formated . '] has been successfully placed!';
             $values = array('order_id' => $orderId,
             'customer_id' => $notify->customerId,
             'vendor_id' => $notify->vendorId,
             'outlet_id' => $notify->outletId,
             'message' => $subject,
             'read_status' => 0,
             'created_date' => date('Y-m-d H:i:s'));
            DB::table('notifications')->insert($values);


            $to = $users->email;

            $template = DB::table('email_templates')->select('*')->where('template_id', '=', self::ORDER_MAIL_TEMPLATE)->get();

          // print_r($template);exit();
             if (count($template)) {
                 $from = $template[0]->from_email;

                 $from_name = $template[0]->from;
                 if (!$template[0]->template_id) {
                     $template = 'mail_template';
                     $from = getAppConfigEmail()->contact_mail;
                 }
                 $subject = 'Your Order with ' . getAppConfig()->site_name . ' [' . $notify->order_key_formated . '] has been successfully Placed!';
                 $orderId = encrypt($orderId);
                 $reviwe_id = base64_encode('123abc');
                 $orders_link = '<a href="' . URL::to("order-info/" . $orderId) . '" title="' . trans("messages.View") . '">' . trans("messages.View") . '</a>';
                 $review_link = '<a href="' . URL::to("order-info/" . $orderId . '?r=' . $reviwe_id) . '" title="' . trans("messages.View") . '">' . trans("messages.View") . '</a>';

                 $content = array('name' => "" . $users->name, 'order_key' => "" . $notify->order_key_formated, 'status_name' => "" . $notify->status_name, 'orders_link' => "" . $orders_link, "review_link" => $review_link);

                 $attachment = "";
                 $email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
             }
         }


        if($order_status==12){

             $subject = 'Your Order with ' . getAppConfig()->site_name . ' [' . $notify->order_key_formated . '] has been successfully Delivered!';
            $values = array('order_id' => $orderId,
             'customer_id' => $notify->customerId,
             'vendor_id' => $notify->vendorId,
             'outlet_id' => $notify->outletId,
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
                 $subject = 'Your Order with ' . getAppConfig()->site_name . ' [' . $notify->order_key_formated . '] has been successfully Delivered!';
                 $orderId = encrypt($orderId);
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

            // print_r($template);exit();

    if (count($template)) {
        $from = $template[0]->from_email;
        $from_name = $template[0]->from;
        //$subject = $template[0]->subject;
        $drivers = Drivers::find($notify->driver_ids);
        //print_r($drivers);exit();
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
         }
        return 1;

            /*FCM push notification/
    }*/
    public function getWeightclasses(Request $data)
    {
        $weightclasses=DB::table('weight_classes_infos')
            ->select('id', 'title', 'unit')
            ->get();
        $result = array("status" => 1,  "message" => trans("messages.success"),"details"=>$weightclasses);
        return json_encode($result, JSON_UNESCAPED_UNICODE);
        /*$weightclasses=DB::table('weight_classes')
            ->select('id')
           // ->select('id','weight_classes_infos.title','weight_classes_infos.unit')
            ->leftjoin('weight_classes_infos', 'weight_classes_infos.id', '=', 'weight_classes.id')
           // ->where('weight_classes.active_status = "1"')
            ->get();*/
    }

    public function orderPackestatus(Request $data)
    {
        $post_data = $data->all();
        //print_r($post_data);exit;
        $rules = [
         'orderId' => ['required'],
         'packdetails' => ['required'],
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
            $result = array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            try {
                foreach ($post_data['packdetails'] as $key => $value) {
                    $updateStatus=DB::table('orders_info')
                        ->where('orders_info.order_id', $post_data['orderId'])
                        ->where('orders_info.item_id', $value['productId'])
                        ->update(['pack_status' => $value['pack_status']]);

                   

                }
                $result = array("status" => 1, "message" => trans("messages.success"));
            } catch (JWTException $e) {
                $result = array("status" => 2,  "message" => trans("messages.Something went wrong"));
            } catch (TokenExpiredException $e) {
                $result = array("status" => 2,  "message" => trans("messages.Something went wrong"));
            }
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function outeletRevenue(Request $data)
    {
        $post_data = $data->all();
        $rules = [
         'outletId' => ['required'],
         'startdate' => ['required'],
         'enddate' => ['required'],
         'skipSize' => ['required'],
         'pageSize' => ['required'],
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
            $result = array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            try {
                $skipSize = $post_data['skipSize'];
                $pageSize = $post_data['pageSize'];
                $completedCount = $cancelCount = $cashInCount = $totalamount =0;

                $outletId = isset($post_data['outletId'])?$post_data['outletId']:0;
                $startdate = isset($post_data['startdate'])?$post_data['startdate']:"2019-10-09 00:00:00";
                $enddate = isset($post_data['enddate'])?$post_data['enddate']:"2019-10-09 00:00:00";

                 //print_r($startdate);echo"<br>";print_r($enddate);exit;
                $totalamount = DB::table('orders')
                    ->select('orders.total_amount', 'id', 'order_status')
                    ->where('orders.outlet_id', '=', $outletId)
                    //->whereBetween('orders.created_date', [$startdate, $enddate])
                    ->whereDate('orders.created_date', '>=' , $startdate)
                    ->whereDate('orders.created_date', '<=' , $enddate)
                    ->where('orders.order_status', '=', 12)
                   // ->get();
                    ->sum('orders.total_amount');

                $status = [12,33,11];
                $completed_trip = DB::table('orders')
                    ->select('orders.total_amount', 'orders.id', 'orders.order_status', 'transaction.payment_type','orders.created_date')
                    ->leftjoin('transaction', 'transaction.order_id', '=', 'orders.id')
                    ->where('orders.outlet_id', '=', $outletId)
                    ->whereDate('orders.created_date', '>=' , $startdate)
                    ->whereDate('orders.created_date', '<=' , $enddate)
                    ->whereIn('orders.order_status', $status)
                    ->limit($pageSize)
                    ->skip($skipSize)
                    ->orderBy('created_date', 'asc')
                    ->get();

                $data=array();

                foreach ($completed_trip as $key => $value) {
                    $data[$key]['total_amount']=$value->total_amount;
                    $data[$key]['id']=$value->id;
                    $data[$key]['order_status']=$value->order_status;
                    $data[$key]['payment_type']=$value->payment_type;
                    $data[$key]['created_date']=$value->created_date;
                }


                
               /* $rawCondition="";
                $rawCondition.="orders.created_date BETWEEN '".$startdate."' and '".$enddate."'";*/
                // if ($startdate == $enddate) {
                //     $rawCondition.="and orders.created_date =".$startdate."' and ";
                // } else {
                // }

                $details= DB::table('orders')
                    ->select('order_status', DB::raw('count(*) as count'))
                    ->where('orders.outlet_id', '=', $outletId)
                    //->whereRaw($rawCondition)
                    ->whereDate('orders.created_date', '>=' , $startdate)
                    ->whereDate('orders.created_date', '<=' , $enddate)
                    ->groupBy('order_status')
                    ->get();

                if (count($details)) {
                    foreach ($details as $key => $value) {
                        if ($value->order_status == 12) {
                            $completedCount = $value->count;
                        }
                        if ($value->order_status == 11) {
                            $cancelCount = $value->count;
                        }
                        if ($value->order_status == 33) {
                            $cashInCount = $value->count;
                        }
                    }
                }

                $detail['totalAmount'] = (double)$totalamount;
                $detail['completedCount'] = $completedCount;
                $detail['cancelCount'] = $cancelCount;
                $detail['cashInCount'] = $cashInCount;
                $detail['completedTriplist'] = $data;
               
                $result = array("status" => 1, "message" => trans("messages.success"),"detail"=>$detail);
            } catch (JWTException $e) {
                $result = array("status" => 2,  "message" => trans("messages.Something went wrong"));
            } catch (TokenExpiredException $e) {
                $result = array("status" => 2,  "message" => trans("messages.Something went wrong"));
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    public function outeletRevenue_copy(Request $data)
    {
        $post_data = $data->all();
        $rules = [
         'outletId' => ['required'],
         'startdate' => ['required'],
         'enddate' => ['required'],
         'skipSize' => ['required'],
         'pageSize' => ['required'],
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
            $result = array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            try {
                $skipSize = $post_data['skipSize'];
                $pageSize = $post_data['pageSize'];
                $completedCount = $cancelCount = $cashInCount = $totalamount =0;

                $outletId = isset($post_data['outletId'])?$post_data['outletId']:0;
                if($post_data['type'] == 1)
                {
                    $date         = date("Y-m-d");          
                    $startdate   = $date . ' 00:00:01';
                    $enddate     = $date . ' 23:59:59';
                }elseif($post_data['type'] == 2){
                    $start = date('Y-m-d',strtotime('this week'));
                    $end = date('Y-m-d',strtotime('this week +6 days'));
                    $startdate   = $start . ' 00:00:01';
                    $enddate     = $end . ' 23:59:59';
                }elseif($post_data['type'] == 3){
                   
                    $start = date('Y-m-d',strtotime('first day of this month'));
                    $end = date('Y-m-d',strtotime('last day of this month'));
                    $startdate   = $start . ' 00:00:01';
                    $enddate     = $end . ' 23:59:59';
                }else{
                    $startdate = isset($post_data['startdate'])?$post_data['startdate']:"2019-10-09 00:00:00";
                    $enddate = isset($post_data['enddate'])?$post_data['enddate']:"2019-10-09 00:00:00";
                }

               //print_r($startdate);echo "<br>"; print_r($enddate);exit();

                $totalamount = DB::table('orders')
                    ->select('orders.total_amount', 'id', 'order_status')
                    ->where('orders.outlet_id', '=', $outletId)
                    //->whereBetween('orders.created_date', [$startdate, $enddate])
                    ->whereDate('orders.created_date', '>=' , $startdate)
                    ->whereDate('orders.created_date', '<=' , $enddate)
                    ->where('orders.order_status', '=', 12)
                   // ->get();
                    ->sum('orders.total_amount');

                $status = [12,33,11];
                $completed_trip = DB::table('orders')
                    ->select('orders.total_amount', 'orders.id', 'orders.order_status', 'transaction.payment_type','orders.created_date')
                    ->leftjoin('transaction', 'transaction.order_id', '=', 'orders.id')
                    ->where('orders.outlet_id', '=', $outletId)
                    ->whereDate('orders.created_date', '>=' , $startdate)
                    ->whereDate('orders.created_date', '<=' , $enddate)
                    ->whereIn('orders.order_status', $status)
                    ->limit($pageSize)
                    ->skip($skipSize)
                    ->orderBy('created_date', 'asc')
                    ->get();

                    print_r($completed_trip);exit;

                $data=array();

                foreach ($completed_trip as $key => $value) {
                    $data[$key]['total_amount']=$value->total_amount;
                    $data[$key]['id']=$value->id;
                    $data[$key]['order_status']=$value->order_status;
                    $data[$key]['payment_type']=$value->payment_type;
                    $data[$key]['created_date']=$value->created_date;
                }


                
               /* $rawCondition="";
                $rawCondition.="orders.created_date BETWEEN '".$startdate."' and '".$enddate."'";*/
                // if ($startdate == $enddate) {
                //     $rawCondition.="and orders.created_date =".$startdate."' and ";
                // } else {
                // }

                $details= DB::table('orders')
                    ->select('order_status', DB::raw('count(*) as count'))
                    ->where('orders.outlet_id', '=', $outletId)
                    //->whereRaw($rawCondition)
                    ->whereDate('orders.created_date', '>=' , $startdate)
                    ->whereDate('orders.created_date', '<=' , $enddate)
                    ->groupBy('order_status')
                    ->get();

                if (count($details)) {
                    foreach ($details as $key => $value) {
                        if ($value->order_status == 12) {
                            $completedCount = $value->count;
                        }
                        if ($value->order_status == 11) {
                            $cancelCount = $value->count;
                        }
                        if ($value->order_status == 33) {
                            $cashInCount = $value->count;
                        }
                    }
                }

                $detail['totalAmount'] = (double)$totalamount;
                $detail['completedCount'] = $completedCount;
                $detail['cancelCount'] = $cancelCount;
                $detail['cashInCount'] = $cashInCount;
                $detail['completedTriplist'] = $data;
               
                $result = array("status" => 1, "message" => trans("messages.success"),"detail"=>$detail);
            } catch (JWTException $e) {
                $result = array("status" => 2,  "message" => trans("messages.Something went wrong"));
            } catch (TokenExpiredException $e) {
                $result = array("status" => 2,  "message" => trans("messages.Something went wrong"));
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function revenuExport(Request $data)
    {
        $post_data = $data->all();
        $rules = [
            'outletId' => ['required'],
            'startdate' => ['required'],
            'enddate' => ['required'],
            'totalAmount' => ['required'],
            'completedCount' => ['required'],
            'cancelCount' => ['required'],
           // 'email' => ['required'],
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
            $result = array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            try {
               // $currency =getCurrencyList();
                $currency_code = CURRENCYCODE;
                $details= DB::table('outlet_infos')
                    ->where('outlet_infos.id', '=', $post_data['outletId'])
                    ->select('outlet_infos.outlet_name', 'outlets.contact_email')
                    ->leftjoin('outlets', 'outlets.id', '=', 'outlet_infos.id')
                    ->get();
                //$to = "athira@mailinator.com";
                $email = isset($details[0]->contact_email)?$details[0]->contact_email:'';
                $to = $email;
                $template = DB::table('email_templates')->select('*')->where('template_id', '=', 30)->get();
                if (count($template)) {
                    $from = $template[0]->from_email;
                    $from_name = $template[0]->from;
                    $subject1 = $template[0]->subject;
                    if (!$template[0]->template_id) {
                        $template = 'mail_template';
                        $from = getAppConfigEmail()->contact_mail;
                    }
                    $subject = "BROZ";
                    $vendorname = $details[0]->outlet_name;
                    $totalAmount = isset($post_data['totalAmount'])?$post_data['totalAmount']:0;
                    $tripcount = isset($post_data['completedCount'])?$post_data['completedCount']:0;
                    $currencycode = $currency_code;
                    $startdate = $post_data['startdate'];
                    $enddate = $post_data['enddate'];

                    $content = array('subject' => "" . $subject1, 'vendorname' => "" . $vendorname, 'tripcount' => "" . $tripcount,'totalamount' => "" .$totalAmount , 'currencycode' => "" . $currencycode, "startdate" => $startdate, "enddate" => $enddate);
                    $attachment = "";
                    $email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
                }
                $result = array("status" => 1, "message" => trans("messages.success"));
            } catch (JWTException $e) {
                $result = array("status" => 2,  "message" => trans("messages.Something went wrong"));
            } catch (TokenExpiredException $e) {
                $result = array("status" => 2,  "message" => trans("messages.Something went wrong"));
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /*Cashier */
    public function getPaymentHistory(Request $data)
    {
        $post_data = $data->all();
        $rules = [
            'type' => ['required'],
            'mobile'    => ['required_unless:type,1'],
            'outlet_id'    => ['required'],

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
            $result = array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            try {
                $payment_history =array();
                if($post_data['type'] == 1)
                {
                    $payment_history_cashier = DB::table('payment_history')
                            ->join('outlets','payment_history.outlet_id','=','outlets.id')
                            ->join('outlet_infos','outlet_infos.id','=','outlets.id')
                            ->join('users','users.id','=','payment_history.customer_id')
                            ->select('payment_history.id','payment_history.customer_id','payment_history.created_date','payment_history.payment_type','payment_history.amount','payment_history.created_date','payment_history.outlet_id','outlets.latitude' , 'outlets.longitude','outlet_infos.contact_address','outlet_infos.outlet_name','users.name','payment_history.order_id')
                            ->where('payment_history.outlet_id',$post_data['outlet_id'])
                            ->where('payment_history.payment_type',3)
                            ->orderBy('payment_history.created_date', 'desc')

                            ->get();
                    foreach ($payment_history_cashier as $key => $value) {
                        $payment_history_cashier[$key]->currencyCode = 'AED';
                        $payment_history_cashier[$key]->type = "DE";

                        $logo_image = URL::asset('assets/admin/base/images/online-store.png');
                        if(!isset($value->outlet_name)){
                            if($value->payment_type == 1)
                            {
                                $logo_image = URL::asset('assets/admin/base/images/credit-card.png');
                                $payment_history_cashier[$key]->outlet_name = "Wallet add money sucess";
                                $payment_history_cashier[$key]->order_id = "";
                                $payment_history_cashier[$key]->type = "CR";


                            }else{
                                $logo_image = URL::asset('assets/admin/base/images/cashback.png');
                                $payment_history_cashier[$key]->outlet_name = "Wallet add money declain";
                                $payment_history_cashier[$key]->order_id = "";
                                $payment_history_cashier[$key]->type = "DE";


          
                            }
                            $payment_history_cashier[$key]->outlet_name = "Wallet";
                            $payment_history_cashier[$key]->latitude = "";
                            $payment_history_cashier[$key]->longitude = "";
                            $payment_history_cashier[$key]->contact_address = "";
                        } 
                        $payment_history_cashier[$key]->image = $logo_image;

                    }
      
                    $result = array("status" => 1,  "message" => trans("messages.Users Details"),"payment_history"=>$payment_history_cashier,"user_details"=>0);

                }else{
                    $user_details= DB::table('users')->select('*')->where('users.mobile', '=', $post_data['mobile'])->get();
                    if(count($user_details))
                    {
                        $customer_id = isset($user_details[0]->id)?$user_details[0]->id:0;
                        $payment_history_cashier = DB::table('payment_history')
                            ->join('outlets','payment_history.outlet_id','=','outlets.id')
                            ->join('outlet_infos','outlet_infos.id','=','outlets.id')
                            ->join('users','users.id','=','payment_history.customer_id')

                            ->select('payment_history.id','payment_history.payment_type','payment_history.amount','payment_history.order_id','payment_history.created_date','payment_history.outlet_id','outlets.latitude' , 'outlets.longitude','outlet_infos.contact_address','outlet_infos.outlet_name','payment_history.customer_id','payment_history.created_date','users.name')
                            ->where('payment_history.customer_id',$customer_id)
                            //->where('payment_history.outlet_id',$post_data['outlet_id'])

                            ->where('payment_history.payment_type',3)
                            ->orderBy('payment_history.created_date', 'desc')

                            ->get();
                        $payment_history_wallet = DB::table('payment_history')
                                ->join('users','users.id','=','payment_history.customer_id')

                                ->select('payment_history.id','payment_history.payment_type','payment_history.amount','payment_history.created_date','payment_history.outlet_id','payment_history.customer_id','payment_history.created_date','users.name')
                                ->where('payment_history.customer_id',$customer_id)
                               // ->where('payment_history.outlet_id',$post_data['outlet_id'])
                               ->where('payment_history.payment_type','!=',3)

                                ->orderBy('payment_history.created_date', 'desc')

                                ->get();
                            /*merge and sort the array based on created date*/
                            $payment_history=array_merge($payment_history_cashier,$payment_history_wallet);

                            $sort = array();
                            foreach($payment_history as $k=>$v) {
                                $sort['created_date'][$k] = $v->created_date;
                            }

                            if($payment_history){

                                array_multisort($sort['created_date'], SORT_DESC, $payment_history);
                            }
                             /*merge and sort the array based on created date*/
                        foreach ($payment_history as $key => $value) {
                            $payment_history[$key]->currencyCode = 'AED';
                            $payment_history[$key]->type = "DE";

                            $logo_image = URL::asset('assets/admin/base/images/online-store.png');
                            if(!isset($value->outlet_name)){
                                if($value->payment_type == 1)
                                {
                                    $logo_image = URL::asset('assets/admin/base/images/credit-card.png');
                                    $payment_history[$key]->outlet_name = "Wallet add money sucess";
                                    $payment_history[$key]->order_id = "";
                                    $payment_history[$key]->type = "CR";


                                }else{
                                    $logo_image = URL::asset('assets/admin/base/images/cashback.png');
                                    $payment_history[$key]->outlet_name = "Wallet add money declain";
                                    $payment_history[$key]->order_id = "";
                                    $payment_history[$key]->type = "DE";


              
                                }
                                $payment_history[$key]->outlet_name = "Wallet";
                                $payment_history[$key]->latitude = "";
                                $payment_history[$key]->longitude = "";
                                $payment_history[$key]->contact_address = "";
                            } 
                        $payment_history[$key]->image = $logo_image;

                    }
              
                        $result = array("status" => 1,  "message" => trans("messages.Users Details"),"payment_history"=>$payment_history);


                    }else{
                        $result = array("status" => 2,  "message" => trans("messages.Invalid Mobile number"));


                    }
                }

            }
            catch (JWTException $e) {
                $result = array("status" => 2,  "message" => trans("messages.Something went wrong"));
            } catch (TokenExpiredException $e) {
                $result = array("status" => 2,  "message" => trans("messages.Something went wrong"));
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);

    }

    public function searchUser(Request $data)
    {
        //print_r($data->all());exit();
        $post_data = $data->all();
        $rules = [
            //'type' => ['required'],
           //'mobile'    => ['required_unless:type,1'],
            'mobile'    => ['required'],
            'outlet_id'    => ['required'],

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
            $result = array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            try {


                $outlet_details= DB::table('outlets')
                    ->where('outlets.id', '=', $post_data['outlet_id'])
                    ->select('*')
                    ->get(); 
                $outlet_type = isset($outlet_details[0]->outlet_category)?$outlet_details[0]->outlet_category:0;
                //print_r($outlet_type);exit();
                $user_details= DB::table('users')->select('*')->where('users.mobile', '=', $post_data['mobile'])->get();
                if(count($user_details))
                {
                    $customer_id = isset($user_details[0]->id)?$user_details[0]->id:0;
                    $wallet = isset($user_details[0]->wallet_amount)?$user_details[0]->wallet_amount:0;
                   // print_r($wallet);exit();
                    $offer_wallet = isset($user_details[0]->offer_wallet)?$user_details[0]->offer_wallet:0;
                    $wallet_amount = $wallet + $offer_wallet ;

                    $wallet_amount = floatval($wallet_amount);
                    $wallet = floatval($wallet);
                    $offer_wallet = floatval($offer_wallet);
                    if($outlet_type ==1){      
                        $result = array("status" => 1,  "message" => trans("messages.success"),"note" => trans("messages.Offer wallet not applicable for grocery"),"wallet_amount"=>number_format($wallet, 2, '.', ''),"grocery_wallet"=>number_format($offer_wallet, 2, '.', ''),"customerDetail"=>$user_details[0],"actualWalletBalance"=>number_format($wallet, 2, '.', ''));
                    }else{
                        $result = array("status" => 1,  "message" => trans("messages.success"),"note" => trans("messages.Offer wallet applicable"),"wallet_amount"=>number_format($wallet, 2, '.', ''),"grocery_wallet"=>number_format($offer_wallet, 2, '.', ''),"customerDetail"=>$user_details[0],"actualWalletBalance"=>number_format($wallet_amount, 2, '.', ''));
                    }
                }else{
                    $result = array("status" => 2,  "message" => trans("messages.Invalid Mobile number"));


                }

            }
            catch (JWTException $e) {
                $result = array("status" => 2,  "message" => trans("messages.Something went wrong"));
            } catch (TokenExpiredException $e) {
                $result = array("status" => 2,  "message" => trans("messages.Something went wrong"));
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);

    }


    public function getWalletDebitOtP(Request $data)
    {
        $post_data = $data->all();
        $rules = [
            'customer_id' => ['required'],
            'outlet_id'    => ['required'],
            'amount'    => ['required'],
            'order_id'    => ['required'],
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
            $result = array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            $amount = $post_data['amount'];
            $outletId = $post_data['outlet_id'];
           // print_r($amount);exit();
            $user_details= DB::table('users')->select('*')->where('users.id', '=', $post_data['customer_id'])->get();
            $wallet = isset($user_details[0]->wallet_amount)?$user_details[0]->wallet_amount:0;
            $offer_wallet = isset($user_details[0]->offer_wallet)?$user_details[0]->offer_wallet:0;
            $wallet_amount = $wallet + $offer_wallet ;
            //Ram::
            $outlet_details= DB::table('outlets')
                        ->where('outlets.id', '=', $outletId)
                        ->select('*')
                        ->get(); 

            $type_key = isset($outlet_details[0]->outlet_category)?$outlet_details[0]->outlet_category:0; //1-grocery,2-laundry,3-barber
            //print_r($type_key);exit();

            if($type_key==1)
            {
                if($wallet < $amount)
                {
                    $result = array("status" => 2, "message" =>trans("messages.Do not have sufficient balance") );
                    return json_encode($result);
                }else{

                        /*pin generate and sms*/
                        $otp = rand(1000, 9999);
                        $res = DB::table('users')
                                 ->where('id', $user_details[0]->id)
                                ->update(['verfiy_pin' => $otp,'verify_pin_time'=>date("Y-m-d H:i:s")] );

                        $country_code    =isset($user_details[0]->country_code )?$user_details[0]->country_code:'+971';
                        //$number = "+918281715079";
                        $number = $country_code.$user_details[0]->mobile;
                        $message = 'You have received OTP password for ' . getAppConfig()->site_name . '. Your OTP code is ' . $otp . '. This code can be used only once and dont share this code with anyone.';
                        /*$twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
                        $twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
                        $from_number = "+14783471785";
                        $client = new Services_Twilio($twilo_sid, $twilio_token);*/
                        $twilo_sid = TWILIO_ACCOUNTSID;
                        $twilio_token = TWILIO_AUTHTOKEN;
                        $from_number = TWILIO_NUMBER;
                        $client = new Client($twilo_sid, $twilio_token);
                        // Create an authenticated client for the Twilio API
                        try {
                           /* $m = $client->account->messages->sendMessage(
                                $from_number, // the text will be sent from your Twilio number
                                $number, // the phone number the text will be sent to
                                $message // the body of the text message
                            );*/
                           $m =  $client->messages->create($number, array('from' => $from_number, 'body' => $message));

                           $result = array("status" => 1,  "message" => trans("messages.Otp sent"),"amount"=>$post_data['amount'],"order_id"=>$post_data['order_id'],"otp"=>$otp,"wallet_amount"=>$wallet_amount,"grocery_wallet"=>(int)$offer_wallet,);

                        } catch (Exception $e) {
                            $result = array("status" => 1, "message" => $e->getMessage(),"otp"=>$otp);
                            return json_encode($result);
                        } catch (\Services_Twilio_RestException $e) {
                            $result = array("status" => 1, "message" => $e->getMessage(),"otp"=>$otp);
                            return json_encode($result);
                        }
                        /*pin generate and sms*/
                    }
            }else 
            {
                if($wallet_amount < $amount)
                {
                    $result = array("status" => 2, "message" =>trans("messages.Do not have sufficient balance") );
                    return json_encode($result);
                }
                else{

                        /*pin generate and sms*/
                        $otp = rand(1000, 9999);
                        $res = DB::table('users')
                                 ->where('id', $user_details[0]->id)
                                ->update(['verfiy_pin' => $otp,'verify_pin_time'=>date("Y-m-d H:i:s")] );

                        $country_code    =isset($user_details[0]->country_code )?$user_details[0]->country_code:'+971';
                        //$number = "+918281715079";
                        $number = $country_code.$user_details[0]->mobile;
                        $message = 'You have received OTP password for ' . getAppConfig()->site_name . '. Your OTP code is ' . $otp . '. This code can be used only once and dont share this code with anyone.';
                       /* $twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
                        $twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
                        $from_number = "+14783471785";
                        $client = new Services_Twilio($twilo_sid, $twilio_token);*/
                        $twilo_sid = TWILIO_ACCOUNTSID;
                        $twilio_token = TWILIO_AUTHTOKEN;
                        $from_number = TWILIO_NUMBER;
                        $client = new Client($twilo_sid, $twilio_token);
                        // Create an authenticated client for the Twilio API
                        try {
                           /* $m = $client->account->messages->sendMessage(
                                $from_number, // the text will be sent from your Twilio number
                                $number, // the phone number the text will be sent to
                                $message // the body of the text message
                            );*/
                                            $m =  $client->messages->create($number, array('from' => $from_number, 'body' => $message));

                           $result = array("status" => 1,  "message" => trans("messages.Otp sent"),"amount"=>$post_data['amount'],"order_id"=>$post_data['order_id'],"otp"=>$otp,"wallet_amount"=>$wallet_amount,"grocery_wallet"=>(int)$offer_wallet,);

                        } catch (Exception $e) {
                            $result = array("status" => 1, "message" => $e->getMessage(),"otp"=>$otp);
                            return json_encode($result);
                        } catch (\Services_Twilio_RestException $e) {
                            $result = array("status" => 1, "message" => $e->getMessage(),"otp"=>$otp);
                            return json_encode($result);
                        }
                        /*pin generate and sms*/
                    }
            }

        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);


    }
    
    public function userWalletDebit(Request $data)
    {
        $post = $data->all();
        $rules = [
         'customer_id' => ['required'],
         'outlet_id' => ['required'],
        // 'outlet_manager_id' => ['required'],
         'verfiy_pin' => ['required'],
         'amount' => ['required'],
         'order_id' => ['required'],
         ];


        $errors = $result = array();
        $validator = app('validator')->make($post, $rules);
        if ($validator->fails()) {
            $errors = '';
            $j = 0;
            foreach ($validator->errors()->messages() as $key => $value) {
                $error[] = is_array($value) ? implode(',', $value) : $value;
            }
            $errors = implode(", \n ", $error);
            $result = array("status" => 0, "message" => trans("messages.Error List"), "detail" => $errors);
        } else {
            try {
                $date1 = date("Y-m-d H:i:s");
                $time1 = strtotime($date1);
                $time = $time1 - (1 * 120 *120 *60);
                $currentTime2 = date("Y-m-d H:i:s", $time);

                $user_details= DB::table('users')
                                ->select('*')
                                ->where('users.id', '=', $post['customer_id'])
                                ->where('users.verfiy_pin', '=', $post['verfiy_pin'])
                                ->where('users.verify_pin_time', '>=', $currentTime2)
                                ->get();

                if(count($user_details)) {


                    $outlet_details= DB::table('outlets')
                        ->where('outlets.id', '=', $post['outlet_id'])
                        ->select('*')
                        ->get(); 
                    $type_key = isset($outlet_details[0]->outlet_category)?$outlet_details[0]->outlet_category:0; //1-grocery,2-laundry,3-barber
                    $wallet_amount = isset($user_details[0]->wallet_amount)?$user_details[0]->wallet_amount:0;
                    $offer_wallet  = isset($user_details[0]->offer_wallet)?$user_details[0]->offer_wallet:0;
                    //print_r($type_key);exit();
                   // $type_key =2;
                    if($type_key != 1)
                    {   
                       
                        $tot_wallet = $wallet_amount+$offer_wallet;
                        if($tot_wallet>=$post['amount']) {
                           /* $wallet_amount =2000;
                            $offer_wallet =1500;
                            $post['amount'] =1000;*/
                            $offer_wallet = $offer_wallet- $post['amount']; 
                            if($offer_wallet <= 0) {
                                $wallet_amounts = $wallet_amount + $offer_wallet;
                                $offer_wallet =0;
                                $userDetails=DB::table('users')->where('users.id', '=', $post['customer_id'])->update(['offer_wallet'=>abs($offer_wallet),'wallet_amount'=>abs($wallet_amounts)]);
                            }else {                           
  
                                $wallet_amounts =$wallet_amount;
                                $userDetails=DB::table('users')->where('users.id', '=', $post['customer_id'])->update(['offer_wallet'=>abs($offer_wallet)]);
                            }
                            /**log**/
                            $terms      = new payment_history;
                            $terms->outlet_id =isset($post['outlet_id'])?$post['outlet_id']:0;
                            $terms->customer_id =isset($user_details[0]->id)?$user_details[0]->id:0;
                            $terms->amount =$post['amount'];
                            $terms->payment_type =3;//cashier payment
                            $terms->wallet_type =0;
                            $terms->order_id =isset($post['order_id'])?$post['order_id']:0;

                            $terms->created_at =date("Y-m-d H:i:s");
                            $terms->created_date =date("Y-m-d H:i:s");
                            $terms->updated_at =date("Y-m-d H:i:s");
                            $terms->save();
                            /**log**/
                            $wallet_amount = $wallet_amounts + $offer_wallet ;
                            $this->walletUserMail($user_details[0]->email,$post['amount'],$user_details[0]);
                            $amountDetails= DB::table('users')
                                ->select('users.wallet_amount')
                                ->where('users.id', '=', $post['customer_id'])
                                ->get();
                            //print_r($amountDetails);exit();
                            $actualWalletBalance= $amountDetails[0]->wallet_amount+$offer_wallet;
                            $updatePin= DB::table('users')
                                ->where('users.id', '=', $post['customer_id'])
                                ->update(['verfiy_pin'=> 0]);
                            $result = array("status" => 1,  "message" => trans("messages.Payment done successfully"),"wallet_amount"=>$amountDetails[0]->wallet_amount,"grocery_wallet"=>/*(int)$offer_wallet*/number_format($offer_wallet, 2, '.', ''),
                                "actualWalletBalance"=>number_format($actualWalletBalance, 2, '.', ''));

                        }
                        else{
                             $result = array("status" => 2,  "message" => trans("messages.dont have enough balance in customer wallet"));

                        }

                    }else{
                      /*  $wallet_amount =1200;
                        $offer_wallet =800;
                        $post['amount'] =1000;*/
                        $tot_wallet = $wallet_amount+$offer_wallet;
                        if($wallet_amount>=$post['amount']) {
                            $amount = $post['amount']- $wallet_amount;
                           // print_r($amount);exit();

                            $userDetails=DB::table('users')->where('users.id', '=', $post['customer_id'])->update(['wallet_amount'=>abs($amount)]);

                            /**log**/
                            $terms      = new payment_history;
                            $terms->outlet_id =isset($post['outlet_id'])?$post['outlet_id']:0;
                            $terms->customer_id =isset($user_details[0]->id)?$user_details[0]->id:0;
                            $terms->amount =$post['amount'];
                            $terms->payment_type =3;//cashier payment
                            $terms->wallet_type =0;
                            $terms->order_id =isset($post['order_id'])?$post['order_id']:0;
                            $terms->created_at =date("Y-m-d H:i:s");
                            $terms->created_date =date("Y-m-d H:i:s");
                            $terms->updated_at =date("Y-m-d H:i:s");
                            $terms->save();
                            /**log**/
                            $this->walletUserMail($user_details[0]->email,$post['amount'],$user_details[0]);

                            $updatePin= DB::table('users')
                                ->where('users.id', '=', $post['customer_id'])
                                ->update(['verfiy_pin'=> 0]);

                            $result = array("status" => 1,  "message" => trans("messages.Payment done successfully"),"wallet_amount"=>abs($amount),"grocery_wallet"=>/*abs($offer_wallet)*/number_format($offer_wallet, 2, '.', ''),
                                "actualWalletBalance"=>number_format(abs($amount), 2, '.', ''));
                        }else{
                            $result = array("status" => 2,  "message" => trans("messages.dont have enough balance in customer grocery wallet"));
                        }

                    }
                  
                }
                else{
                    $result = array("status" => 2,  "message" => trans("messages.Invalid Pin"));
                }


               
            }catch (JWTException $e) {
                $result = array("status" => 2,  "message" => trans("messages.Something went wrong"));
            } catch (TokenExpiredException $e) {
                $result = array("status" => 2,  "message" => trans("messages.Something went wrong"));
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);


    }
   public function walletUserMail($email,$amount,$user_details)
    {
        $template = DB::table('email_templates')->select('*')->where('template_id', '=', self::USER_WALLET_PAYMENT)->get();

       // $to = "athira123@mailinator.com";
        $to = $user_details->email;

        $template = DB::table('email_templates')->select('*')->where('template_id', '=', 33)->get();
        if (count($template)) {
            $from = $template[0]->from_email;
            $from_name = $template[0]->from;
            $subject1 = $template[0]->subject;
            if (!$template[0]->template_id) {
                $template = 'mail_template';
                $from = getAppConfigEmail()->contact_mail;
            }
            $subject = "BROZ";
            $name = $user_details->name;

            $currency_code = CURRENCYCODE;
            $amount =$amount;

            $content = array('subject' => "" . $subject1, 'name' => "" . $name, 'currency_code' => "" . $currency_code,'amount' => "" .$amount);
            $attachment = "";
            $email = smtp($from, $from_name, $to, $subject, $content, $template, $attachment);
        }
       

        $country_code    =isset($user_details->country_code )?$user_details->country_code:COUNTRYCODE;
        //$number = "+918281715079";
        $number = $country_code.$user_details->mobile;
        $message = 'A purchase of'.CURRENCYCODE .' '.$amount.'has been made using your Wallet'    ;
        /*$twilo_sid = "AC6d40eb10c6a8be92b2d097bc848fe7bc";
        $twilio_token = "a321f99bdd0f15c805e0c0c3387b5184";
        $from_number = "+14783471785";
        $client = new Services_Twilio($twilo_sid, $twilio_token);*/
        $twilo_sid = TWILIO_ACCOUNTSID;
                $twilio_token = TWILIO_AUTHTOKEN;
                $from_number = TWILIO_NUMBER;
                $client = new Client($twilo_sid, $twilio_token);
        // Create an authenticated client for the Twilio API
        try {
           /* $m = $client->account->messages->sendMessage(
                $from_number, // the text will be sent from your Twilio number
                $number, // the phone number the text will be sent to
                $message // the body of the text message
            );*/
            $m =  $client->messages->create($number, array('from' => $from_number, 'body' => $message));

        } catch (Exception $e) {
            $result = array("status" => -1, "message" => $e->getMessage());
            return json_encode($result);
        } catch (\Services_Twilio_RestException $e) {
            $result = array("status" => -1, "message" => $e->getMessage());
            return json_encode($result);
        }

     
    }

   

}
