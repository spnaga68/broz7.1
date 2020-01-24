<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use Closure;
use App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Text;
use Illuminate\Support\Facades\Input;
use App\Model\notification_triggers;
use Mail;
use PushNotification;
use App\Model\autoassign_order_logs;
use App\Model\order;
use App\Model\driver_settings;
use DateTime;
use App\Model\users;


class Cron extends Controller
{
    const DRIVER_ORDER_RESPONSE_TEMPLATE = 24;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /* To send push notification */
    public function send_notification()
    {
		
        $notification_list = DB::table('notification_triggers')
							->select('id', 'type', 'sender_id', 'sender_email', 'from_name', 'receivers', 'subject', 'content', 'additional_params', 'try_count')
							->where('try_count','<','1')
							->orderBy('id', 'asc')
							->get();

							//print_r($notification_list); exit;
        if(count($notification_list) > 0)
        {
            foreach($notification_list as $notify)
            {
                $id        = $notify->id;
                $type      = $notify->type;
                $content   = $notify->content;
                $from_name = $notify->from_name;
                $receivers = $notify->receivers;
                $subject   = $notify->subject;
                //~ echo $content;die;
                $try_count = $notify->try_count;
                $from      = $notify->sender_email;
                $additional_params = json_decode($notify->additional_params,true);
                if($type == 1) {

					try {
						$email = smtp($from, $from_name, $receivers, $subject, $content);
						if($email) {
							
							$notification = Notification_triggers::find($id);
							$notification->try_count  = 1;
							$notification->updated_at = date("Y-m-d H:i:s");
							$notification->save();
						} else {
							$notification = Notification_triggers::find($id);
							if($try_count == 5)
								$notification->try_count = 0;
							else
								$notification->try_count = $try_count + 1;
								$notification->updated_at = date("Y-m-d H:i:s");
								$notification->save();
						}
					}
					catch (\Exception $e) { //catch (Sly\NotificationPusher\Exception\PushException $e)
                            Log::Instance()->add(Log::ERROR, $e);
                        }
                }
                if($type == 2) {

					try {

						
						$new_content = strip_tags($content);
						$android[0]     = PushNotification::Device($receivers);

						//print_r($android); exit;
						$android_device = PushNotification::DeviceCollection($android);
						$message        = PushNotification::Message($new_content,$additional_params);
						$collection     = PushNotification::app('TijikAndroid')->to($android_device);
						$collection->adapter->setAdapterParameters(['sslverifypeer' => false]);
						$collection->send($message);
						foreach ($collection->pushManager as $push)
						{
							$response = $push->getAdapter()->getResponse()->getResponse();
							//print_r($response);//die;
							if(isset($response['success']) && $response['success'] == 1) {
								$notification = Notification_triggers::find($id);
								$notification->try_count  = 1;
								$notification->updated_at = date("Y-m-d H:i:s");
								$notification->save();
							}
						}
					}
					catch (\Exception $e) { //catch (Sly\NotificationPusher\Exception\PushException $e)
						echo "in"; exit;
                            Log::Instance()->add(Log::ERROR, $e);
                        }
                }
                if($type == 3) {
					
					$new_content = strip_tags($content);
                    $ios[0]         = PushNotification::Device($receivers);
                    $ios_device     = PushNotification::DeviceCollection($ios);
                    $message        = PushNotification::Message($new_content,$additional_params);
                    $ios_collection = PushNotification::app('AxsIOS')->to($ios_device)->send($message);
                    foreach ($ios_collection->pushManager as $push)
                    {
                        try {
                            $response = $push->getAdapter()->getResponse()->getCode();
                            //print_r($response);//die;
                            if($response == 0) {
                                $notification = Notification_triggers::find($id);
                                $notification->try_count  = 1;
                                $notification->updated_at = date("Y-m-d H:i:s");
                                $notification->save();
							}
                        } catch (\Exception $e) { //catch (Sly\NotificationPusher\Exception\PushException $e)
                            Log::Instance()->add(Log::ERROR, $e);
                        }
                    }
                }
            }
        }
    }

    /**  Order assign automation process **/
    public function OrderAssignNotification()
    {
			/** Get order info **/
		    $language = getCurrentLang();
		    $condition ="orders.order_type!=0";
            $query  = '"payment_gateways_info"."language_id" = (case when (select count(payment_gateways_info.payment_id) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.$language.' and orders.payment_gateway_id = payment_gateways_info.payment_id) > 0 THEN '.$language.' ELSE 1 END)';
            $query1 = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = '.$language.' and orders.outlet_id = outlet_infos.id) > 0 THEN '.$language.' ELSE 1 END)';
            $query2 = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = '.$language.' and orders.vendor_id = vendors_infos.id) > 0 THEN '.$language.' ELSE 1 END)';
            $orders = DB::table('orders')
                        ->select('orders.id','orders.id as order_id','orders.total_amount','orders.created_date','orders.modified_date','orders.delivery_date','users.first_name','users.last_name','orders.order_key_formated','order_status.name as status_name','order_status.color_code as color_code','users.name as user_name','transaction.currency_code','payment_gateways_info.name as payment_type','outlet_infos.outlet_name','vendors_infos.vendor_name as vendor_name','orders.id','outlet_infos.contact_address','outlets.latitude as outlet_latitude','outlets.longitude as outlet_longitude','outlets.id as outlet_id','user_address.address as user_address') 
                        ->leftJoin('users','users.id','=','orders.customer_id')
                        ->leftJoin('user_address', 'orders.delivery_address', '=', 'user_address.id')
                        ->leftJoin('order_status','order_status.id','=','orders.order_status')
                        ->leftjoin('transaction','transaction.order_id','=','orders.id')
                        ->Join('payment_gateways_info','payment_gateways_info.payment_id','=','orders.payment_gateway_id')
                        ->Join('vendors_infos','vendors_infos.id','=','orders.vendor_id')
                        ->Join('outlet_infos','outlet_infos.id','=','orders.outlet_id')
                        ->Join('outlets','outlets.id','=','outlet_infos.id')
                        ->whereRaw($query)->whereRaw($query1)->whereRaw($query2)
                        ->whereRaw($condition)
                        ->where('orders.order_status','=',10)
                        ->orderBy('orders.id', 'desc')
                        //->limit(1)
                        ->get();


            /** Get order info end **/           
                        
			if(count($orders) > 0 ){
				foreach($orders as $okey => $ovalue){

					$drivers= array();
					if($ovalue->outlet_latitude!='' &&  $ovalue->outlet_longitude!=''){
					/** Get near by driver **/
						$drivers = DB::select("select DISTINCT ON (driver_track_location.driver_id) driver_id, drivers.first_name, drivers.last_name, drivers.android_device_token,earth_distance(ll_to_earth(".$ovalue->outlet_latitude.",".$ovalue->outlet_longitude."), ll_to_earth(driver_track_location.latitude, driver_track_location.longitude)) as distance from drivers left join driver_track_location on driver_track_location.driver_id = drivers.id where earth_box(ll_to_earth(".$ovalue->outlet_latitude.",".$ovalue->outlet_longitude."), 5000) @> ll_to_earth(driver_track_location.latitude, driver_track_location.longitude) and drivers.active_status=1 and drivers.is_verified=1 and drivers.driver_status = 1 order by driver_track_location.driver_id,distance asc");
					}
					/** Get near by driver end **/

					if(count($drivers) > 0 ) {
						
						foreach($drivers as $od =>$odvalue){

							$get_autoassign_order_logs = DB::table('autoassign_order_logs')
										->select('*')
										->where('autoassign_order_logs.driver_id',$odvalue->driver_id)
										->where('autoassign_order_logs.order_id',$ovalue->order_id)
										->where('autoassign_order_logs.order_delivery_status','=',0)
										->where('autoassign_order_logs.auto_order_rejected','=',0)
										->orderby('autoassign_order_logs.id','desc')->first();

										if(count($get_autoassign_order_logs) > 0) {
											
											$assign_date=$get_autoassign_order_logs->assign_date;
											$created_date=$get_autoassign_order_logs->created_date;
											$order_delivery_status=$get_autoassign_order_logs->order_delivery_status;
											$driver_response= $get_autoassign_order_logs->driver_response;
											$order_rejected = $get_autoassign_order_logs->auto_order_rejected;
											$orderassign_log_auto_id=$get_autoassign_order_logs->id;

											/** Driver Settings Configuration **/
												$driver_settings = Driver_settings::find(1);
											/** Driver Settings Configuration end **/
											
											if(count($driver_settings) > 0){
												if($driver_settings->order_accept_time!=''){

													if($driver_response==0){
														$oadate = DateTime::createFromFormat('Y-m-d H:i:s', $created_date);
														$oadate->modify('+'.$driver_settings->order_accept_time.' minutes');
														$increment_time = $oadate->format('Y-m-d H:i:s');
														if($increment_time <= date('Y-m-d H:i:s')){
															$order_logs_rejected = Autoassign_order_logs::find($orderassign_log_auto_id);
															$order_logs_rejected->auto_order_rejected      = 1;
															$order_logs_rejected->updated_date      = date("Y-m-d H:i:s");
															$order_logs_rejected->save();

															/** send admin notification to auto reject driver order **/
															$subject     = getAppConfig()->site_name.' Order assign auto rejected by the system  - ['.$odvalue->first_name.'-'.$odvalue->last_name.']';
															$template    = DB::table('email_templates')
															->select('*')
															->where('template_id','=',self::DRIVER_ORDER_RESPONSE_TEMPLATE)
															->get();
															if(count($template))
															{
																$from = $template[0]->from_email;
																$from_name=$template[0]->from;
																//$subject = $template[0]->subject;
																if(!$template[0]->template_id)
																{
																$template = 'mail_template';
																$from     = getAppConfigEmail()->contact_mail;
																$subject     = getAppConfig()->site_name.'Order assign auto rejected by the system  - ['.$drivers->first_name.'-'.$drivers->last_name.']';
																$from_name = "";
																}
																$driver_order_assign_response = "This order assign has been auto rejected by the system because driver didn't respond the order assignment";
																$users = Users::find(1);
																$admin_mail = $users->email;
																$content = array("order" => array('name' =>$users->name,'id'=>$ovalue->order_key_formated,'status'=>$driver_order_assign_response));
																$mail = smtp($from,$from_name,$admin_mail,$subject,$content,$template);
															}
															/** send admin notification to auto reject driver order end  **/
														}
													}
													
												}
												
											}

										}else {

									
											 $get_autoassign_order_logs1 = DB::table('autoassign_order_logs')
											->select('*')
											->where('autoassign_order_logs.driver_id',$odvalue->driver_id)
											->where('autoassign_order_logs.order_id',$ovalue->order_id)
											->where('autoassign_order_logs.order_delivery_status','=',0)
											->where('autoassign_order_logs.auto_order_rejected','=',1)
											->where('autoassign_order_logs.driver_response','=',0)
											->orderby('autoassign_order_logs.id','desc')->first();

											if(count($get_autoassign_order_logs1) > 0) {
												
												if(($get_autoassign_order_logs1->driver_id != $odvalue->driver_id) && ($get_autoassign_order_logs1->order_id!=$ovalue->order_id)){
													
														if($odvalue->android_device_token != '')
														{

															 $order_title = ''.ucfirst($ovalue->outlet_name).' - '.$ovalue->order_key_formated.' A new order has been assigned';
															 $order_logs = new Autoassign_order_logs;
															 $order_logs->driver_id    = $odvalue->driver_id;
															 $order_logs->order_id    = $ovalue->order_id;
															 $order_logs->driver_response    = 0;
															 $order_logs->driver_token    = $odvalue->android_device_token;
															 $order_logs->order_delivery_status    = 0;
															 $order_logs->assign_date    = date("Y-m-d H:i:s");             
															 $order_logs->created_date = date("Y-m-d H:i:s");
															 $order_logs->order_subject = $order_title;
															 $order_logs->order_message = $ovalue->user_address;
															 $order_logs->save();
															 
															//$outlet_details = Order::outlet_details_by_order($ovalue->order_id);
															$notification_message = PushNotification::Message($order_title,array(
															'badge' => 1,
															'sound' => 'example.aiff',
															'actionLocKey' => $order_title,
															//'launchImage' => base_path().'/assets/admin/base/images/offers/'.$offer_image,
															'id' => $ovalue->order_id,
															'type' => 2,
															//'title' => $order_title,
															'title' => $ovalue->user_address,
															'custom' => array('id' => $ovalue->order_id,'type' => 2,'title' => $order_title,'log_id' => $order_logs->id,'order_key_formated' => $ovalue->order_key_formated,'request_type'=>1)//If type 1 means offers and 2 means orders
															));

															$android_device_arr[0] = PushNotification::Device($odvalue->android_device_token);
															$android_devices = PushNotification::DeviceCollection($android_device_arr);
															$collection = PushNotification::app('TijikAndroid')->to($android_devices);
															//it was need to set 'sslverifypeer' parameter to false
															$collection->adapter->setAdapterParameters(['sslverifypeer' => false]);
															$collection->send($notification_message);
															// get response for each device push
															foreach ($collection->pushManager as $push)
															{
																$response = $push->getAdapter()->getResponse();
															}
														}
													
												}
											}else {


													$get_autoassign_order_logs12 = DB::table('autoassign_order_logs')
													->select('*')
													->where('autoassign_order_logs.order_id',$ovalue->order_id)
													->where('autoassign_order_logs.order_delivery_status','=',0)
													->where('autoassign_order_logs.auto_order_rejected','=',0)
													->where('autoassign_order_logs.driver_response','=',0)
													->orderby('autoassign_order_logs.id','desc')->first();

													if(count($get_autoassign_order_logs12)==0){

														if($odvalue->android_device_token != '')
														{

															 $order_title = ''.ucfirst($ovalue->outlet_name).' - '.$ovalue->order_key_formated.' A new order has been assigned';
															 $order_logs = new Autoassign_order_logs;
															 $order_logs->driver_id    = $odvalue->driver_id;
															 $order_logs->order_id    = $ovalue->order_id;
															 $order_logs->driver_response    = 0;
															 $order_logs->driver_token    = $odvalue->android_device_token;
															 $order_logs->order_delivery_status    = 0;
															 $order_logs->order_subject = $order_title;
															 $order_logs->order_message = $ovalue->user_address;
															 $order_logs->assign_date    = date("Y-m-d H:i:s");             
															 $order_logs->created_date = date("Y-m-d H:i:s");
															 $order_logs->save();
															 
															//$outlet_details = Order::outlet_details_by_order($ovalue->order_id);
															 $notification_message = PushNotification::Message($order_title,array(
															'badge' => 1,
															'sound' => 'example.aiff',
															'actionLocKey' => $order_title,
															//'launchImage' => base_path().'/assets/admin/base/images/offers/'.$offer_image,
															'id' => $ovalue->order_id,
															'type' => 2,
															'title' => $order_title,
															'custom' => array('id' => $ovalue->order_id,'type' => 2,'title' => $order_title,'log_id' => $order_logs->id,'order_key_formated' => $ovalue->order_key_formated,'request_type'=>1)//If type 1 means offers and 2 means orders
															));

															$android_device_arr[0] = PushNotification::Device($odvalue->android_device_token);
															$android_devices = PushNotification::DeviceCollection($android_device_arr);
															$collection = PushNotification::app('TijikAndroid')->to($android_devices);
															//it was need to set 'sslverifypeer' parameter to false
															$collection->adapter->setAdapterParameters(['sslverifypeer' => false]);
															$collection->send($notification_message);
															// get response for each device push
															foreach ($collection->pushManager as $push)
															{
																$response = $push->getAdapter()->getResponse();
															}
														}
													}

											}
										

									}

						}
					}
					
				}
				
			}        
	}
	/**  Order assign automation process end **/
    
}
