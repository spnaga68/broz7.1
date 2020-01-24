<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Model\users;
use App\Model\order;
use App\Model\users_activity;
use App\Model\Users\groups;
use App\Model\Users\addresstype;
use App\Model\settings;
use Session;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Image;
use MetaTag;
use Mail;
use File;
use SEO;
use SEOMeta;
use OpenGraph;
use Twitter;
use App;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\Input;
use Yajra\Datatables\Datatables;
use URL;
use Illuminate\Support\Facades\Text;
use PushNotification;
use Hash;
use App\Model\driver_orders;
use App\Model\drivers;

use App\Model\autoassign_order_logs;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use App\Model\driver_settings;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;


class Orders extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    const ORDER_STATUS_UPDATE_USER = 18;
    const REFUND_APPROVE_EMAIL_TEMPLATE = 20;
    public function __construct()
    {
        $this->site_name = isset(getAppConfig()->site_name)?ucfirst(getAppConfig()->site_name):'';
        $this->middleware('auth');
        SEOMeta::setTitle($this->site_name);
        SEOMeta::setDescription($this->site_name);
        SEOMeta::addKeyword($this->site_name);
        OpenGraph::setTitle($this->site_name);
        OpenGraph::setDescription($this->site_name);
        OpenGraph::setUrl($this->site_name);
        Twitter::setTitle($this->site_name);
        Twitter::setSite('@'.$this->site_name);
        App::setLocale('en');
    }
    public function index()
    {  
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('admin/orders/index'))
            {
                return view('errors.404');
            }
            $condition ="orders.order_type!=0";
            if(Input::get('from') && Input::get('to'))
            {
                $from = date('Y-m-d H:i:s', strtotime(Input::get('from')));
                $to = date('Y-m-d H:i:s', strtotime(Input::get('to')));
                $condition .=" and orders.created_date BETWEEN '".$from."'::timestamp and '".$to."'::timestamp";
            }
            if(Input::get('from_amount') && Input::get('to_amount'))
            {
                $from_amount = preg_replace("/[^0-9,.]/", "", Input::get('from_amount'));
               $to_amount = preg_replace("/[^0-9,.]/", "", Input::get('to_amount'));
                $condition .=" and orders.total_amount BETWEEN '".$from_amount."' and '".$to_amount."'";
            }
            if(Input::get('order_status'))
            {
                $order_status = Input::get('order_status');
                $condition .=" and orders.order_status = ".$order_status."";
            }
            if(Input::get('payment_type'))
            {
                $payment_type = Input::get('payment_type');
                $condition .=" and orders.payment_gateway_id = ".$payment_type."";
            }
            if(Input::get('vendor'))
            {
                $vendor = Input::get('vendor');
                $condition .=" and orders.vendor_id = ".$vendor."";
            }
            if(Input::get('outlet'))
            {
                $outlet = Input::get('outlet');
                $condition .=" and orders.outlet_id = ".$outlet."";
            }
			$language = getCurrentLang();
            $query  = '"payment_gateways_info"."language_id" = (case when (select count(payment_gateways_info.payment_id) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.$language.' and orders.payment_gateway_id = payment_gateways_info.payment_id) > 0 THEN '.$language.' ELSE 1 END)';
            $query1 = '"outlet_infos"."language_id" = (case when (select count(outlet_infos.id) as totalcount from outlet_infos where outlet_infos.language_id = '.$language.' and orders.outlet_id = outlet_infos.id) > 0 THEN '.$language.' ELSE 1 END)';
            $query2 = '"vendors_infos"."lang_id" = (case when (select count(vendors_infos.id) as totalcount from vendors_infos where vendors_infos.lang_id = '.$language.' and orders.vendor_id = vendors_infos.id) > 0 THEN '.$language.' ELSE 1 END)';
            $orders = DB::table('orders')
                        ->select('orders.id','orders.total_amount','orders.created_date','orders.modified_date','orders.delivery_date','users.first_name','users.last_name','order_status.name as status_name','order_status.color_code as color_code','users.name as user_name','transaction.currency_code','payment_gateways_info.name as payment_type','outlet_infos.outlet_name','vendors_infos.vendor_name as vendor_name','orders.id','outlet_infos.contact_address','outlets.latitude as outlet_latitude','outlets.longitude as outlet_longitude','outlets.id as outlet_id','drivers.first_name as driver_name') 
                        ->leftJoin('users','users.id','=','orders.customer_id')
                        ->leftJoin('order_status','order_status.id','=','orders.order_status')
                        ->leftjoin('transaction','transaction.order_id','=','orders.id')
                        ->Join('payment_gateways_info','payment_gateways_info.payment_id','=','orders.payment_gateway_id')
                        ->Join('vendors_infos','vendors_infos.id','=','orders.vendor_id')
                        ->Join('outlet_infos','outlet_infos.id','=','orders.outlet_id')
                        ->Join('outlets','outlets.id','=','outlet_infos.id')
			->leftJoin('driver_orders','driver_orders.order_id','=','orders.id')
                        ->leftJoin('drivers','drivers.id','=','driver_orders.driver_id')
                        ->whereRaw($query)->whereRaw($query1)->whereRaw($query2)
                        ->whereRaw($condition)
                        ->orderBy('orders.id', 'desc')
                        ->paginate(10);
            $order_status = DB::table('order_status')->select('id','name')->orderBy('name', 'asc')->get();
            $query3       = '"payment_gateways_info"."language_id" = (case when (select count(payment_gateways_info.language_id) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.getCurrentLang().' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN '.getCurrentLang().' ELSE 1 END)';
            $payment_seetings = DB::table('payment_gateways')
                                    ->select('payment_gateways.id','payment_gateways_info.name')
                                    ->leftJoin('payment_gateways_info','payment_gateways_info.payment_id','=','payment_gateways.id')
                                    ->whereRaw($query3)
                                    ->orderBy('id', 'asc')
                                    ->get();

                                  //  print_r($orders);exit;
            if(Input::get('export'))
            {
                $out = '"Order Id","User Name","Vendor Name","Outlet Name","Payment Type","Status","Total Amount","Order Date"'."\r\n";
                foreach($orders as $d)
                {
                    $out .= $d->id.',"'.$d->user_name.'","'.$d->vendor_name.'","'.$d->outlet_name.'","'.$d->payment_type.'","'.$d->status_name.'","'.$d->total_amount.$d->currency_code.'","'.date("d F, Y", strtotime($d->created_date)).'"'."\r\n";
                }
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=Orders.csv');
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                echo "\xEF\xBB\xBF"; // UTF-8 BOM
                echo $out;
                exit;
            }
            return view('admin.orders.list')->with('orders', $orders)->with('order_status', $order_status)->with('payment_seetings', $payment_seetings);
        }
    }
    public function order_info($order_id)
    {
        if(!hasTask('admin/orders/info'))
        {
            return view('errors.404');
        }
        $language_id = getCurrentLang();
        $query3 = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = '.$language_id.' and vendors.id = vendors_infos.id) > 0 THEN '.$language_id.' ELSE 1 END)';
        $query4 = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.$language_id.' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN '.$language_id.' ELSE 1 END)';
        $vendor_info = DB::select('SELECT vendors_infos.vendor_name,vendors.email,vendors.logo_image,o.id as order_id,o.created_date,o.order_status,order_status.name as status_name,order_status.color_code as color_code,o.outlet_id,vendors.id as vendor_id,o.order_key_formated
        FROM orders o
        left join vendors vendors on vendors.id = o.vendor_id
        left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
        left join order_status order_status on order_status.id = o.order_status
        left join payment_gateways payment_gateways on payment_gateways.id = o.payment_gateway_id
        left join payment_gateways_info payment_gateways_info on payment_gateways_info.payment_id = payment_gateways.id
        where '.$query3.' AND '.$query4.' AND o.id = ? ORDER BY o.id',array($order_id));

        $query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = '.$language_id.' and p.id = products_infos.id) > 0 THEN '.$language_id.' ELSE 1 END)';
        $order_items = DB::select('SELECT p.product_image,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.invoice_id,pi.product_name,pi.description,o.coupon_amount
        FROM orders o
        LEFT JOIN orders_info oi ON oi.order_id = o.id
        LEFT JOIN products p ON p.id = oi.item_id
        LEFT JOIN products_infos pi ON pi.id = p.id
        where '.$query.' AND o.id = ? ORDER BY oi.id',array($order_id));

        $query1 = '"out_inf"."language_id" = (case when (select count(outlet_infos.language_id) as totalcount from outlet_infos where outlet_infos.language_id = '.getCurrentLang().' and o.outlet_id = outlet_infos.id) > 0 THEN '.getCurrentLang().' ELSE 1 END)';
        $query2 = 'pgi.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.$language_id.' and pg.id = payment_gateways_info.payment_id) > 0 THEN '.$language_id.' ELSE 1 END)';
        $delivery_details = DB::select('SELECT o.id as order_id,o.order_status as order_status, o.delivery_instructions,ua.address,pg.id as payment_gateway_id,pgi.name,o.total_amount,o.delivery_charge,o.service_tax,dti.start_time,end_time,o.created_date,o.delivery_date,o.order_type,out_inf.contact_address,o.coupon_amount,users.first_name,users.last_name,users.email,users.mobile,o.order_key_formated,out_inf.outlet_name,trans.currency_code,pgi.name as payment_gateway_name,users.name as user_name, o.invoice_id FROM orders o
                    left join users on o.customer_id = users.id
                    LEFT JOIN user_address ua ON ua.id = o.delivery_address
                    left join payment_gateways pg on pg.id = o.payment_gateway_id
                    left join payment_gateways_info pgi on pgi.payment_id = pg.id
                    left join delivery_time_slots dts on dts.id=o.delivery_slot
                    left join delivery_time_interval dti on dti.id = dts.time_interval_id
                    left join outlets out on out.id = o.outlet_id
                    left join outlet_infos out_inf on out.id = o.outlet_id
                    left join transaction trans on trans.order_id = o.id
                    where '.$query1.' AND '.$query2.' AND o.id = ?',array($order_id));
        
        $order_history = DB::select('SELECT ol.order_comments,ol.order_status,log_time,order_status.name as status_name,order_status.color_code as color_code
        FROM orders_log ol
        left join order_status order_status on order_status.id = ol.order_status
        where ol.order_id = ? ORDER BY ol.id',array($order_id));
        $order_history1 = DB::select('SELECT ol.id,ol.digital_signature,ol.order_attachment FROM orders_log ol where ol.order_id = ? and ol.order_status = 12',array($order_id));
        $order_status_list = DB::select('SELECT * FROM order_status WHERE id in(1,10,18,19,14,12) ORDER BY order_status.id');
        return view('admin.orders.view')->with('order_items', $order_items)->with('delivery_details', $delivery_details)->with('vendor_info', $vendor_info)->with('order_history', $order_history)->with('order_status_list', $order_status_list)->with('order_history1', $order_history1);
    }
    
    public function update_status(Request $data)
    {
        if(!hasTask('admin/orders/update-status'))
        {
            return view('errors.404');
        }
        $post_data = $data->all();
        $affected  = DB::update('update orders set order_status = ?,order_comments = ? where id = ?', array($post_data['order_status_id'],$post_data['comment'],$post_data['order_id']));
        $affected  = DB::update('update orders_log set order_status=?, order_comments = ? where id = (select max(id) from orders_log where order_id = '. $post_data['order_id'].')', array($post_data['order_status_id'],$post_data['comment']));
        $order_detail  = $this->get_order_detail($post_data['order_id']);
        $order_details = $order_detail["order_items"];
        $delivery_details = $order_detail["delivery_details"];
        $vendor_info = $order_detail["vendor_info"];
        if($post_data['order_status_id'] == 18)
        {

                $date1 = date("Y-m-d H:i:s");
                    $time1 = strtotime($date1);
                    $time = $time1 - (1 * 30);
                    $date = date("Y-m-d H:i:s", $time);

              $vendor_info = $order_detail["vendor_info"];


               $drivers_details = $vendor_info[0]->driver_ids;

              $driver_conditions = '';

              if($drivers_details !='')
              {

                $driver_conditions  = " and drivers.id NOT IN (".rtrim($drivers_details,",").") ";
              }    


            $drivers = DB::select("select driver_id,distance ,first_name,last_name,android_device_token from (select DISTINCT ON (driver_track_location.driver_id) driver_id, drivers.first_name, drivers.last_name, drivers.android_device_token,earth_distance(ll_to_earth(".$vendor_info[0]->outlet_latitude.",".$vendor_info[0]->outlet_longitude."), ll_to_earth(driver_track_location.latitude, driver_track_location.longitude)) as distance from drivers left join driver_track_location on driver_track_location.driver_id = drivers.id where earth_box(ll_to_earth(".$vendor_info[0]->outlet_latitude.",".$vendor_info[0]->outlet_longitude."), 30000)  @> ll_to_earth(driver_track_location.latitude, driver_track_location.longitude) and drivers.active_status=1 and drivers.android_device_token!='' and  drivers,id = 14 and drivers.is_verified=1 and drivers.driver_status = 1 ".$driver_conditions."  order by driver_track_location.driver_id,distance asc) as temp_table");

print_r( $drivers);exit;
         

            if(count($drivers) > 0 ) 
            {
                $assigned_drivers = 0;
                foreach($drivers as $od =>$odvalue)
                   {

                        if($assigned_drivers == 1)
                        {

                            break;
                        }    

                            $get_autoassign_order_logs12 = DB::table('autoassign_order_logs')
                            ->select('*')
                            ->where('autoassign_order_logs.order_id',$post_data['order_id'])
                            ->where('autoassign_order_logs.order_delivery_status','=',0)
                            ->where('autoassign_order_logs.auto_order_rejected','=',1)
                            ->where('autoassign_order_logs.driver_response','=',0)
                            ->orderby('autoassign_order_logs.id','desc')
                            ->first();
                          

                       $driver_settings = Driver_settings::find(1);
                        $created_date=date("Y-m-d H:i:s");


                        //print_r($get_autoassign_order_logs12);exit;

                        if(count($get_autoassign_order_logs12) >=0)
                        {
                        

                            if($odvalue->android_device_token != '')
                            {

                               // echo $odvalue->driver_id;exit;


                                 $orders =  DB::table('orders')
                                            ->select('driver_ids')
                                            ->where('orders.id',$post_data['order_id'])
                                            ->first(); 


                               $driver_ids =   $orders->driver_ids;  
                               $new_orders = Order::find($post_data['order_id']);
                               $new_orders->driver_ids = $driver_ids.$odvalue->driver_id.',';
                              
                              $assigned_time = strtotime("+ ".$driver_settings->order_accept_time." minutes", strtotime(date('Y-m-d H:i:s')));
                               $update_assign_time = date("Y-m-d H:i:s", $assigned_time);
                                $new_orders->assigned_time = $update_assign_time; 
                               $new_orders->save();


                                $assigned_drivers = 1;    
                                $order_title = ''.ucfirst($vendor_info[0]->vendor_name).' , A new order delivery has been sent';
                                $order_title1 = ''.ucfirst($vendor_info[0]->vendor_name).' , تم ارسال طلب توصيل جديد';
                                $order_logs = new Autoassign_order_logs;
                                $order_logs->driver_id    = $odvalue->driver_id;
                                $order_logs->order_id    = $post_data['order_id'];
                                $order_logs->driver_response    = 0;
                                $order_logs->driver_token    = $odvalue->android_device_token;
                                $order_logs->order_delivery_status    = 0;
                                $order_logs->order_subject = $order_title;
                                // $order_logs->order_subject_arabic = $order_title1;
                                $order_logs->order_message = $order_title;
                                $order_logs->assign_date    = date("Y-m-d H:i:s");             
                                $order_logs->created_date = date("Y-m-d H:i:s");
                               
                                $order_logs->save();

                                      

                               
                                $affected  = DB::update('update drivers set driver_status = 2 where id = ?', array($odvalue->driver_id));


                                $data = array
                                (
                                'id' => $post_data['order_id'],
                                'type' => 2,
                                'title' => $order_title,
                                'message' => $order_title,
                                'log_id' => $order_logs->id,
                                'order_key_formated' => $vendor_info[0]->order_key_formated,
                                'request_type'=>1,
                                "order_accept_time"=>$driver_settings->order_accept_time,
                                'notification_dialog' => "1"
                                );



                                $fields = array
                                (
                                'registration_ids'  => array($odvalue->android_device_token),
                                'data'          => $data
                                );

                                $headers = array
                                (
                                'Authorization: key='.env(FCM_SERVER_KEY),
                                'Content-Type: application/json'
                                );

                              //  print_r($fields);exit;

                                $ch = curl_init();
                                curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
                                curl_setopt( $ch,CURLOPT_POST, true );
                                curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
                                curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
                                curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
                                curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
                                $result = curl_exec($ch );
                                curl_close( $ch );
                            }
                        }
                   }
               }
        }
        echo("pushhhh1");
        if(isset($post_data['notify']) && $post_data['notify'] == 1)
        {
        	   echo("pushhhh2");
            
            $logo = url('/assets/front/'.Session::get("general")->theme.'/images/'.Session::get("general"
           echo("pushhhh");)->theme.'.png');
            if(file_exists(base_path().'/public/assets/admin/base/images/vendors/list/'.$vendor_info[0]->logo_image)) { 
                $vendor_image ='<img width="100px" height="100px" src="'.URL::to("assets/admin/base/images/vendors/list/".$vendor_info[0]->logo_image).'") >';
            }
            else
            {  
                $vendor_image ='<img width="100px" height="100px" src="'.URL::to("assets/front/base/images/blog_no_images.png").'") >';
            }
            $delivery_date = date("d F, l", strtotime($delivery_details[0]->delivery_date)); 
            $delivery_time = date('g:i a', strtotime($delivery_details[0]->start_time)).'-'.date('g:i a', strtotime($delivery_details[0]->end_time));
            $users = Users::find($delivery_details[0]->customer_id); 
          
            $to    = $users->email;
            $subject = 'Your Order with '.getAppConfig()->site_name.' ['.$vendor_info[0]->order_key_formated .'] has been successfully '.$vendor_info[0]->status_name.'!';
            $template=DB::table('email_templates')->select('*')->where('template_id','=',self::ORDER_STATUS_UPDATE_USER)->get();
            if(count($template))
            {
                $from = $template[0]->from_email;
                $from_name = $template[0]->from;
                if(!$template[0]->template_id)
                {
                    $template = 'mail_template';
                    $from = getAppConfigEmail()->contact_mail;
                }
                $orders_link ='<a href="'.URL::to("orders").'" title="'.trans("messages.View").'">'.trans("messages.View").'</a>';
                $content =array('name' =>"".$users->name,'order_key'=>"".$vendor_info[0]->order_key_formated,'status_name'=>"".$vendor_info[0]->status_name,'orders_link'=>"".$orders_link);
                $attachment = "";
                $email=smtp($from,$from_name,$to,$subject,$content,$template,$attachment);
            }
            $order_title = 'Your order '.$vendor_info[0]->order_key_formated.'  has been '.$vendor_info[0]->status_name;
            $notification_message = PushNotification::Message($order_title,array(
                'badge' => 1,
                'sound' => 'example.aiff',
                'actionLocKey' => $order_title,
                //'launchImage' => base_path().'/assets/admin/base/images/offers/'.$offer_image,
                'id' => $post_data['order_id'],
                'type' => 2,
                'title' => $order_title,
                'custom' => array('id' => $post_data['order_id'],'type' => 2,'title' => $order_title)//If type 1 means offers and 2 means orders
            ));
            if($users->android_device_token != '')
            {
            	//echo("came to android".$users->android_device_token);
                $android_device_arr[0] = PushNotification::Device($users->android_device_token);
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
            if($users->ios_device_token != '')
			{
				//echo("came to ios".$users->ios_device_token);
				$ios[0]         = PushNotification::Device($users->ios_device_token);
				$ios_device     = PushNotification::DeviceCollection($ios);
				//$message        = PushNotification::Message($order_title,$notification_message);
				$ios_collection = PushNotification::app('TijikIOS')->to($ios_device)->send($notification_message);
				foreach ($ios_collection->pushManager as $push)
				{
					try {
						$response = $push->getAdapter()->getResponse()->getCode();
						if($response == 0)
						{
							/*$notification = Notification_triggers::find($id);
							$notification->try_count  = 6;
							$notification->updated_at = date("Y-m-d H:i:s");
							$notification->save();*/
						}
						else {
							/*$notification = Notification_triggers::find($id);
							if($try_count == 5)
								$notification->try_count = 7;
							else
								$notification->try_count = $try_count + 1;
							$notification->updated_at = date("Y-m-d H:i:s");
							$notification->save();*/
						}
					}
					catch (Sly\NotificationPusher\Exception\PushException $e) {
						/*$notification = Notification_triggers::find($id);
						if($try_count == 5)
							$notification->try_count = 7;
						else
							$notification->try_count = $try_count + 1;
						$notification->updated_at = date("Y-m-d H:i:s");
						$notification->save();*/
					}
				}
			}	   
        }
        return 1;
    }
    
    public function load_history($order_id)
    {
        if(!hasTask('admin/orders/load_history'))
        {
            return view('errors.404');
        }
        $order_history = DB::select('SELECT ol.order_comments,ol.order_status,log_time,order_status.name as status_name,order_status.color_code as color_code
        FROM orders_log ol
        left join order_status order_status on order_status.id = ol.order_status
        where ol.order_id = ? ORDER BY ol.id',array($order_id));
        
        $data_tab = '<table class="table table-bordered"><thead><tr>
                    <td class="text-left">'.trans("messages.Date").'</td>
                    <td class="text-right">'.trans("messages.Comment").'</td>
                    <td class="text-right">'.trans("messages.Status").'</td>
                    </tr></thead><tbody>';
                            $subtotal = "";
                            foreach($order_history as $history) {
                    $data_tab .= '<tr><td class="text-left">'.date('M j Y g:i A', strtotime($history->log_time)).'</td>';
                    $data_tab .= '<td class="text-right">'.$history->order_comments.'</td>';
                    $data_tab .= '<td class="text-right">'.$history->status_name.'</td></tr>';
                            }
                    $data_tab .= '</tbody>';
                    $data_tab .= '</table>';
        echo $data_tab;exit;
    }
    
    
    public function get_order_detail($order_id)
    {
        $language_id = getCurrentLang();
        $query3 = '"vendors_infos"."lang_id" = (case when (select count(*) as totalcount from vendors_infos where vendors_infos.lang_id = '.$language_id.' and vendors.id = vendors_infos.id) > 0 THEN '.$language_id.' ELSE 1 END)';
        $query4 = '"payment_gateways_info"."language_id" = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.$language_id.' and payment_gateways.id = payment_gateways_info.payment_id) > 0 THEN '.$language_id.' ELSE 1 END)';
        $vendor_info = DB::select('SELECT vendors_infos.vendor_name,vendors.email, outlets.latitude as outlet_latitude,outlets.longitude as outlet_longitude,o.driver_ids,vendors.logo_image,o.id as order_id,o.created_date,o.order_status,order_status.name as status_name,order_status.color_code as color_code,payment_gateways_info.name as payment_gateway_name,o.outlet_id,vendors.id as vendor_id,o.order_key_formated
        FROM orders o
        left join vendors vendors on vendors.id = o.vendor_id
        left join outlets outlets on outlets.id = o.outlet_id
        left join vendors_infos vendors_infos on vendors_infos.id = vendors.id
        left join order_status order_status on order_status.id = o.order_status
        left join payment_gateways payment_gateways on payment_gateways.id = o.payment_gateway_id
        left join payment_gateways_info payment_gateways_info on payment_gateways_info.payment_id = payment_gateways.id
        where '.$query3.' AND '.$query4.' AND o.id = ? ORDER BY o.id',array($order_id));

        $query = 'pi.lang_id = (case when (select count(*) as totalcount from products_infos where products_infos.lang_id = '.$language_id.' and p.id = products_infos.id) > 0 THEN '.$language_id.' ELSE 1 END)';
        $order_items = DB::select('SELECT p.product_image,p.id AS product_id,oi.item_cost,oi.item_unit,oi.item_offer,o.total_amount,o.delivery_charge,o.service_tax,o.invoice_id,pi.product_name,pi.description,o.coupon_amount
        FROM orders o
        LEFT JOIN orders_info oi ON oi.order_id = o.id
        LEFT JOIN products p ON p.id = oi.item_id
        LEFT JOIN products_infos pi ON pi.id = p.id
        where '.$query.' AND o.id = ? ORDER BY oi.id',array($order_id));

        $query2 = 'pgi.language_id = (case when (select count(*) as totalcount from payment_gateways_info where payment_gateways_info.language_id = '.$language_id.' and pg.id = payment_gateways_info.payment_id) > 0 THEN '.$language_id.' ELSE 1 END)';
        $delivery_details = DB::select('SELECT o.delivery_instructions,ua.address,pg.id as payment_gateway_id,pgi.name,o.total_amount,o.delivery_charge,o.service_tax,dti.start_time,end_time,o.created_date,o.delivery_date,o.order_type,out_inf.contact_address,o.coupon_amount,o.customer_id FROM orders o
                    LEFT JOIN user_address ua ON ua.id = o.delivery_address
                    left join payment_gateways pg on pg.id = o.payment_gateway_id
                    left join payment_gateways_info pgi on pgi.payment_id = pg.id
                    left join delivery_time_slots dts on dts.id=o.delivery_slot
                    left join delivery_time_interval dti on dti.id = dts.time_interval_id
                    left join outlets out on out.id = o.outlet_id
                    left join outlet_infos out_inf on out.id = o.outlet_id
                    where '.$query2.' AND o.id = ?',array($order_id));
        if(count($order_items)>0 && count($delivery_details)>0 && count($vendor_info)>0)
        {
            $result = array("order_items"=>$order_items,"delivery_details"=>$delivery_details,"vendor_info"=>$vendor_info);
        }
        return $result;
    }
    /*
    * Vendor Fund Request List Amount
    */
    public function fund_requests_list()
    {
        if (Auth::guest()){
            return redirect()->guest('admin/login');
        } else {
            if(!hasTask('orders/fund_requests'))
            {
                return view('errors.404');
            }
            $condition ="1=1";
            if(Input::get('from') && Input::get('to'))
            {
                $from = date('Y-m-d H:i:s', strtotime(Input::get('from')));
                $to   = date('Y-m-d H:i:s', strtotime(Input::get('to')));
                $condition .=" and payment_request_vendors.created_date BETWEEN '".$from."'::timestamp and '".$to."'::timestamp";
            }
            $query = '"vendors_infos"."lang_id" = (case when (select count(id) as totalcount from vendors_infos where vendors_infos.lang_id = '.getCurrentLang().' and payment_request_vendors.vendor_id = vendors_infos.id) > 0 THEN '.getCurrentLang().' ELSE 1 END)';
            $list_data = DB::table('payment_request_vendors')
                            ->join('vendors_infos','vendors_infos.id','=','payment_request_vendors.vendor_id')
                            //->join('transaction','transaction.vendor_id','=','payment_request_vendors.vendor_id')
                            ->select('payment_request_vendors.id','payment_request_vendors.approve_status','payment_request_vendors.vendor_id','payment_request_vendors.created_date','payment_request_vendors.modified_date','payment_request_vendors.current_balance','payment_request_vendors.request_amount','payment_request_vendors.unique_id','vendors_infos.vendor_name')
                            ->whereRaw($query)
                            ->whereRaw($condition)
                            ->orderBy('payment_request_vendors.created_date', 'desc')
                            ->get();
                            //print_r($list_data);
            return view('admin.request_amount.list')->with('return_orders',$list_data);
        }
    }
    /*
    * Ajax request payment request for vendors
    */
    public function anyAjaxRequestPayments()
    {
        $currency_side   = getCurrencyPosition()->currency_side;
        $language = getCurrentLang();
        $currency_symbol = getCurrency($language); 
        $query = '"vendors_infos"."lang_id" = (case when (select count(id) as totalcount from vendors_infos where vendors_infos.lang_id = '.getCurrentLang().' and payment_request_vendors.vendor_id = vendors_infos.id) > 0 THEN '.getCurrentLang().' ELSE 1 END)';
        $list_data = DB::table('payment_request_vendors')
                ->join('vendors_infos','vendors_infos.id','=','payment_request_vendors.vendor_id')
                //->join('transaction','transaction.vendor_id','=','payment_request_vendors.vendor_id')
                ->select('payment_request_vendors.*','vendors_infos.vendor_name')
                ->whereRaw($query)
                ->orderBy('payment_request_vendors.created_date', 'desc');
        return Datatables::of($list_data)->addColumn('action', function ($list_data) {
            if(hasTask('orders/approve_fund_status'))
            {
                $html = '--';
                if($list_data->approve_status==0):
                    $html='<div class="request_amount_'.$list_data->id.'">
                        <select name="status" id="fund_status_'.$list_data->id.'" class="form-control" onchange="approve_fund_status('.$list_data->id.','.$list_data->vendor_id.')">
                            <option '.(($list_data->approve_status==0)?"selected='selected'":"").' value="0">'.trans("messages.Pending").'</option>
                            <option '.(($list_data->approve_status==1)?"selected='selected'":"").' value="1">'.trans("messages.Completed").'</option>
                            <option '.(($list_data->approve_status==2)?"selected='selected'":"").' value="2">'.trans("messages.Cancelled").'</option>
                        </select>
                    </div>';
                endif;
                return $html;
            }
        })
        ->addColumn('approve_status', function ($list_data) {
            if($list_data->approve_status==0):
                $data = '<span class="label label-warning" id="approve_status_'.$list_data->id.'">'.trans("messages.Pending").'</span>';
            elseif($list_data->approve_status==1):
                $data = '<span class="label label-success">'.trans("messages.Completed").'</span>';
            elseif($list_data->approve_status==2):
                $data = '<span class="label label-danger">'.trans("messages.Cancelled").'</span>';
            endif;
            return $data;
        })
        ->addColumn('modified_date', function ($list_data) {
            $data = '-';
            if(!empty($list_data->modified_date)):
                $data = trim($list_data->modified_date);
            endif;
            return $data;
        })
        ->addColumn('current_balance', function ($list_data) {
            if($currency_side == 1)
            {
                $data = $currency_symbol.$list_data->current_balance;
            }
            else {
                $data = $list_data->current_balance.$currency_symbol;
            }
            return $data;
        })
        ->addColumn('request_amount', function ($list_data) {
            if($currency_side == 1)
            {
                $data = $currency_symbol.$list_data->request_amount;
            }
            else {
                $data = $list_data->request_amount.$currency_symbol;
            }
            return $data;
        })
        ->make(true);
    }
    //Get city list for ajax request
    public function update_fund_request(Request $request)
    {
        if(!hasTask('orders/approve_fund_status'))
        {
            return view('errors.404');
        }
        if($request->ajax())
        {
            $_id    = $request->input('cid');
            $v_id   = $request->input('vid');
            $status = $request->input('status');
            $query  = '"vendors_infos"."lang_id" = (case when (select count(id) as totalcount from vendors_infos where vendors_infos.lang_id = '.getCurrentLang().' and vendors.id = vendors_infos.id) > 0 THEN '.getCurrentLang().' ELSE 1 END)';
            $funds  = DB::table('payment_request_vendors')
                        ->leftJoin('vendors','vendors.id','=','payment_request_vendors.vendor_id')
                        ->leftJoin("vendors_infos",function($join){
                            $join->on("vendors_infos.id","=","payment_request_vendors.vendor_id")
                                ->on("vendors_infos.id","=","vendors.id");
                        })
                        ->select('payment_request_vendors.*','vendors.email','vendors_infos.vendor_name')
                        ->whereRaw($query)
                        ->where('payment_request_vendors.id',$_id)
                        ->orderBy('payment_request_vendors.created_date', 'desc')->get();
            //Update return approve status with payment request vendors table
            $admin_id = Auth::id();
            $date     = date('Y-m-d H:i:s');
            if($status==1)
            { 
                $approve_status = 'Completed';
                $result = DB::update('update payment_request_vendors set approve_status = ?,modified_by = ?,modified_date = ? where id = ?', array($status,$admin_id,$date,$_id));
            }
            else if($status==2) {
                $approve_status = 'Cancelled';
                $result = DB::update('update payment_request_vendors set approve_status = ?,modified_by = ?,modified_date = ? where id = ?', array($status,$admin_id,$date,$_id));
                //Return back amount to vendor
                $result1 = DB::update('update vendors set current_balance = current_balance + ? where id = ?', array($funds[0]->request_amount,$v_id));
            }
            //Send mail to vendor regarding when admin changed approval status
            $template = DB::table('email_templates')
                            ->select('*')
                            ->where('template_id','=',self::REFUND_APPROVE_EMAIL_TEMPLATE)
                            ->get();
            if(count($template))
            {
                $from = $template[0]->from_email;
                $from_name = $template[0]->from;
                $subject = $template[0]->subject;
                if(!$template[0]->template_id)
                {
                    $template = 'mail_template';
                    $from = getAppConfigEmail()->contact_email;
                    $subject = getAppConfig()->site_name." Refund Request Information";
                    $from_name = "";
                }
                $cont_replace = "Following Fund Request ID: <b>".$funds[0]->unique_id."</b> Administrator was updated following status <b>". $approve_status ."</b>.";
                $cont_replace1 = "We wish you will get more customers and earnings and make benefit of everyone.";
                $content = array("name" => $funds[0]->vendor_name,"email"=>$funds[0]->email,"replacement"=>$cont_replace,"replacement1"=>$cont_replace1);
                $email = smtp($from,$from_name,$funds[0]->email,$subject,$content,$template);
            }
            $res = ($result)?1:0;
            return response()->json([
                'data' => $res
            ]);
        }
    }
    public function order_destory($id)
    {
        if(!hasTask('admin/orders/delete'))
        {
            return view('errors.404');
        }
        $order = Order::find($id);
        $order->delete();
        Session::flash('message', trans('messages.Order has been deleted successfully!'));
        return Redirect::to('admin/orders/index');
    }
	    public function driver_orders()
    {
        if(!hasTask('admin/orders/delete'))
        {
            return view('errors.404');
        }
        $order = Order::find($id);
        $order->delete();
        Session::flash('message', trans('messages.Order has been deleted successfully!'));
        return Redirect::to('admin/orders/index');
    }
    /* To assign driver for orders */
    public function assign_driver_orders(Request $data)
    { 
        
        if(!hasTask('admin/orders/index'))
        {
            return view('errors.404');
        }
        $data_all = $data->all();
        $validation = Validator::make($data_all, array(
            'order_id' => 'required',
            'driver'   => 'required',
        ));
        if ($validation->fails())
        {
            $errors = '';
            $j = 0;
            foreach( $validation->errors()->messages() as $key => $value) 
            {
                $error[] = is_array($value)?implode( ',',str_replace("."," ",$value) ):str_replace("."," ",$value);
            }
            $errors = implode( "<br>", $error );
            $result = array("response" => array("httpCode" => 400, "Error" => trans("messages.Error List"), "Message" => $errors));
        }
           else {
                    $driver_settings = Driver_settings::find(1);

                    $orders  = DB::table('orders')
                            ->select('driver_ids')
                            ->where('orders.id',$data_all['order_id'])
                            ->first();
                    $driver_ids =   $orders->driver_ids;  
                    $new_orders = Order::find($data_all['order_id']);
                    $new_orders->driver_ids = $driver_ids.$data_all['driver'].',';
                    $assigned_time = strtotime("+ ".$driver_settings->order_accept_time." minutes", strtotime(date('Y-m-d H:i:s')));
                    $update_assign_time = date("Y-m-d H:i:s", $assigned_time);
                    $new_orders->assigned_time = $update_assign_time;
                    $new_orders->save();


                    /* $driver_orders = new Driver_orders;
                    $driver_orders->order_id      = $data_all['order_id'];
                    $driver_orders->driver_id     = $data_all['driver'];
                    $driver_orders->assigned_time = date("H:i:s");
                    $driver_orders->created_at    = date("Y-m-d H:i:s");
                    $driver_orders->updated_at    = date("Y-m-d H:i:s");
                    $driver_orders->save();
                    $affected  = DB::update('update orders set order_status = 19 where id = ?', array($data_all['order_id']));
                    $affected  = DB::update('update orders_log set order_status=19 where id = (select max(id) from orders_log where order_id = '. $data_all['order_id'].')');
                    $affected  = DB::update("update orders_log set log_time = '".date("Y-m-d h:i:s a")."' where id = (select max(id) from orders_log where order_id = ". $data_all['order_id'].")");*/
                    //$outlet_details = Order::outlet_details_by_order($data_all['order_id']);
                    $order_title = 'order assigned to you';

                    $driver_detail = Drivers::find($data_all['driver']);
                    $order_logs = new Autoassign_order_logs;
                    $order_logs->driver_id    = $data_all['driver'];
                    $order_logs->order_id    = $data_all['order_id'];
                    $order_logs->driver_response    = 0;
                    $order_logs->driver_token    = $driver_detail->android_device_token;
                    $order_logs->order_delivery_status    = 0;
                    $order_logs->assign_date    = date("Y-m-d H:i:s");             
                    $order_logs->created_date = date("Y-m-d H:i:s");
                    $order_logs->order_subject = $order_title;
                    // $order_logs->order_subject_arabic = $order_title1;
                    $order_logs->order_message = $order_title;
                    $order_logs->save();

                    $affected  = DB::update('update drivers set driver_status = 2 where id = ?', array($data_all['driver']));

                    if($driver_detail->android_device_token != '')
                    {

                        $orders = Order::find($data_all['order_id']) ;
                      
                        $data = array
                        (
                        'id' => $data_all['order_id'],
                        'type' => 2,
                        'title' => $order_title,
                        'message'   => $order_title,
                        'log_id' => $order_logs->id,
                        'order_key_formated' => $orders->order_key_formated,
                        'request_type'=>2,
                        "order_accept_time"=>$driver_settings->order_accept_time,
                        'notification_dialog' => "1"
                        );

                        $fields = array
                        (
                        'registration_ids'  => array($driver_detail->android_device_token),
                        'data'          => $data
                        );

                        $headers = array
                        (
                        'Authorization: key=APA91bFSR1TLAn1Vh134nzXLznsUVYiGnR4KiUYdAa3u0OccC5S-DyDdQRdnR0XugSRArsJGXC8AHE342eNhBbnK8np10KuyuWwiJxtndV75O4DyT3QCGXKFu_fwUTNPdB51Cno6Rewc',
                        'Content-Type: application/json'
                        );

                        $ch = curl_init();
                        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
                        curl_setopt( $ch,CURLOPT_POST, true );
                        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
                        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
                        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
                        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
                        $result = curl_exec($ch );
                        curl_close( $ch );
                        //echo $result;


                    }


                      Session::flash('message', trans('messages.Driver assigned successfully'));
                       $result = array("response" => array("httpCode" => 200, "Message" => trans('messages.Driver assigned successfully')));
                }
        return json_encode($result,JSON_UNESCAPED_UNICODE);
    }
}
