<?php
namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Model\notifications;
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
use Illuminate\Support\Facades\Text;
use Illuminate\Support\Facades\Input;
use Yajra\Datatables\Datatables;
use URL;
use App\Model\notification_triggers;
//use App\Notification;

class CommonNotification extends Controller
{

	const COMMON_MAIL_TEMPLATE = 8;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->site_name = isset(getAppConfig()->site_name)?ucfirst(getAppConfig()->site_name):'Gotboobs';
        $this->site_email = isset(getAppConfig()->email)?ucfirst(getAppConfig()->email):'info@axs.co.za';
		SEOMeta::setTitle('Welcome to '.$this->site_name.' - Gotboobs');
        SEOMeta::setDescription($this->site_name);
        SEOMeta::addKeyword($this->site_name);
        OpenGraph::setTitle($this->site_name);
        OpenGraph::setDescription($this->site_name);
        OpenGraph::setUrl($this->site_name);
        Twitter::setTitle($this->site_name);
        Twitter::setSite('@'.$this->site_name);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('admin/notifications')){
                return view('errors.404');
            }
            return view('admin.notification.send');
        }
    }
	
	public function email_notificaition()
	{ 
		/** if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('admin/email-notifications')){
                return view('errors.404');
            }
            return view('admin.notification.email');
        } */


        if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('admin/email-notifications')){
                return view('errors.404');
            }
            SEOMeta::setTitle('Notification - '.$this->site_name.'');
            SEOMeta::setDescription('Notification - '.$this->site_name.'');
            return view('admin.notification.email');
        }
		
	}
	
	public function push_notification_view()
	{
		if (Auth::guest())
        {
            return redirect()->guest('admin/login');
        }
        else {
            if(!hasTask('admin/push-notifications'))
			{
                return view('errors.404');
            }

            return view('admin.notification.push');
        }
		
	}



	public function send_email(Request $data)
    {
		$fields['users'] = Input::get('users');
		$fields['subject'] = Input::get('subject');
		$fields['message'] = Input::get('message');
		$rules = array(
			'users' => 'required',
			'subject' => 'required',
			'message' => 'required',
		);
        $validator = Validator::make($fields, $rules);
        // process the validation
        if ($validator->fails())
        { 
            return Redirect::back()->withErrors($validator)->withInput();
        }
        else {
            try{
				$users_list = $_POST['users'];
				$subject  = ucfirst($_POST['subject']);
                $message  = ucfirst($_POST['message']);
                $template = DB::table('email_templates')
                            ->select('from_email', 'from', 'subject', 'template_id','content')
                            ->where('template_id','=',self::COMMON_MAIL_TEMPLATE)
                            ->get();
                $from_email = $template[0]->from_email;
                $from_name  = $template[0]->from;

                
				if(count($users_list) > 0 )
				{
					foreach($users_list as $e)
					{
						
						$N_triggers = new Notification_triggers;
						$users = DB::table('users')->select('*')->where('email','=',$e)->get();
						if(count($users)){
							$N_triggers->receiver_id =$users[0]->id;
						}
						$N_triggers->sender_id    = Auth::id();
						$N_triggers->sender_email = $from_email;
						$N_triggers->from_name    = $from_name;
						$N_triggers->subject      = $subject;
						$get_name                 = explode('@',$e);
						$get_name                 = str_replace('_',' ',str_replace('.',' ',ucfirst($get_name[0])));
						$pcontent                  = array("name" => $get_name,"notification" => array('MAIL' => $message));
						$template                 = array_merge($template,_TemplateDefaultResponse());
						$content                  = _TemplateResponse($pcontent,$template);
						$N_triggers->content      = $content;
						$N_triggers->created_at   = date("Y-m-d H:i:s");
						if($e){ 
							
							$N_triggers->type         = 1;
							$N_triggers->receivers    = $e;
							$N_triggers->save();
						}

						//Notification::create(['user_id' => $users[0]->id, 'timeline_id' => $users[0]->timeline_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' '.$message]);

						 $notification_data = [
 'user_id'	=> $users[0]->id,
 'notified_by'	=> Auth::user()->id,
 'description'	=> $message,
 'type'	=> 'notification',
 'created_at'	=> date('Y-m-d H:i:s'),
 ];
//Create a record in user settings table.
$usernotify = DB::table('notifications')->insert($notification_data);
					}
				}
                Session::flash('message', trans('messages.The email notifications has been sent successfully'));
            } catch(Exception $e) {
                Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/email-notifications');
        }
    }
	
	
	/** public function push_notification(Request $data)
    {
		
        $fields['estate_id'] = Input::get('estate_id');
        $fields['entity_type']       = 4;
        $fields['subject']     = Input::get('subject');
        $fields['message']     = Input::get('message');
        $rules = array(
			'estate_id' => 'required',
            'entity_type'   => 'required',
            'subject' => 'required',
            'message' => 'required',
        );
        $validator = Validator::make($fields, $rules);
		$entity_type     = Input::get('entity_type');
        $estate_id     = Input::get('estate_id');
        // process the validation
        if ($validator->fails())
        { 
            return Redirect::back()->withErrors($validator)->withInput();
        }
        else {
            try{
				$users_list = getUserList1($entity_type,$estate_id);
				$subject  = trim($_POST['subject']);
                $content  = trim($_POST['message']);
				if(count($users_list) > 0 ) {
					foreach($users_list as $e) {
						if($e->android_device_token) {
							$N_triggers = new Notification_triggers;
							$N_triggers->receiver_id  = $e->id;
							$N_triggers->sender_id    = Auth::id();
							$N_triggers->subject      = $subject;
							$N_triggers->content      = strip_tags($content);
							$msg_params = array('badge' => 1, 'sound' => 'example.aiff', 'actionLocKey' => $subject, 'id' => $e->id, 'title' => $_POST['subject'], 'app_name' => 'axs', 'custom' => array( 'id' => $e->id, 'title' => $_POST['subject'] ));
							$additional_params = json_encode($msg_params);
							$N_triggers->additional_params = $additional_params;
							$N_triggers->created_at   = date("Y-m-d H:i:s");
							$N_triggers->type         = 2;
							$N_triggers->receivers    = $e->android_device_token;
							$N_triggers->save();
						}
						
						if($e->iphone_device_token) {
							$N_triggers2 = new Notification_triggers;
							$N_triggers2->receiver_id  = $e->id;
							$N_triggers2->sender_id    = Auth::id();
							$N_triggers2->subject      = $subject;
							$N_triggers2->content      = strip_tags($content);
							$msg_params2 = array('badge' => 1, 'sound' => 'example.aiff', 'actionLocKey' => $subject, 'id' => $e->id, 'title' => $_POST['subject'], 'app_name' => 'axs', 'custom' => array( 'id' => $e->id, 'title' => $_POST['subject'] ));
							$additional_params2 = json_encode($msg_params2);
							$N_triggers2->additional_params = $additional_params2;
							$N_triggers2->created_at   = date("Y-m-d H:i:s");
							$N_triggers2->type         = 3;
							$N_triggers2->receivers    = $e->iphone_device_token;
							$N_triggers2->save();
						}
					}
					Session::flash('message', trans('messages.The push notification has been sent successfully'));
				} else {
					Session::flash('message', trans('messages.No residents found'));
				}
            } catch(Exception $e) {
                Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/push-notifications');
        }
    }
    ***/
	
	
	
    public function push_notification(Request $data)
    {
        //$fields['entity_type'] = Input::get('entity_type');
		$fields['users'] = Input::get('users');
       // $entity_type     = Input::get('entity_type');
        $fields['subject']     = Input::get('subject');
        $fields['message']     = Input::get('message');
        $rules = array(
           // 'entity_type'   => 'required',
            'subject' => 'required',
            'message' => 'required',
            'users' => 'required',
        );
        $validator = Validator::make($fields, $rules);
        // process the validation
        if ($validator->fails())
        { 
            return Redirect::back()->withErrors($validator)->withInput();
        }
        else {
            try{
				
				$users_list = $_POST['users'];
				$subject  = ucfirst($_POST['subject']);
                $message  = ucfirst($_POST['message']);
                $template = DB::table('email_templates')
                            ->select('from_email', 'from', 'subject', 'template_id','content')
                            ->where('template_id','=',self::COMMON_MAIL_TEMPLATE)
                            ->get();
                $from_email = $template[0]->from_email;
                $from_name  = $template[0]->from;
				
					if(count($users_list) > 0 )
					{
						foreach($users_list as $e)
						{
							$users = DB::table('users')->select('*')->where('email','=',$e)->get();
							$N_triggers = new Notification_triggers;
							$N_triggers->receiver_id  = $users[0]->id;
							$N_triggers->sender_id    = Auth::id();
							$N_triggers->sender_email = $from_email;
							$N_triggers->from_name    = $from_name;
							$N_triggers->subject      = $subject;
							$get_name                 = explode('@',$e);
							$get_name                 = str_replace('_',' ',str_replace('.',' ',ucfirst($get_name[0])));
							$content                  = array("name" => $get_name,"notification" => array('MAIL' => $message));
							$template                 = array_merge($template,_TemplateDefaultResponse());
							$content                  = _TemplateResponse($content,$template);
							$N_triggers->content      = $message;
							$N_triggers->created_at   = date("Y-m-d H:i:s");
					       $msg_params = array('badge' => 1, 'sound' => 'example.aiff', 'actionLocKey' => $subject, 'id' => $users[0]->id, 'title' => $_POST['subject'], 'app_name' => 'Oddappz', 'custom' => array( 'id' => $users[0]->id, 'title' => $_POST['subject'] ));
					        $additional_params = json_encode($msg_params);
					         if($users[0]->email != ''){
						    $N_triggers->type         = 1;
						    $N_triggers->sender_id    = Auth::id();
							$N_triggers->sender_email = $from_email;
							$N_triggers->from_name    = $from_name;
							$N_triggers->subject      = $subject;
							$N_triggers->receivers    = $e;
							$N_triggers->created_at   = date("Y-m-d H:i:s");
							$N_triggers->save();
						}
							   if($users[0]->android_device_id != '' && $users[0]->android_device_token != ''){
							    $N_triggers = new Notification_triggers;
								$N_triggers->type         = 2;
								$N_triggers->sender_id    = Auth::id();
								$N_triggers->sender_email = $from_email;
							    $N_triggers->from_name    = $from_name;
							    $N_triggers->subject      = $subject;
							    $N_triggers->content      = $message;
								$N_triggers->receivers    = $users[0]->android_device_token;
							    $N_triggers->additional_params = $additional_params;
                                $N_triggers->created_at   = date("Y-m-d H:i:s");
								$N_triggers->save();
								
							}
							 if($users[0]->ios_device_id != '' && $users[0]->ios_device_token != ''){
								
								$N_triggers = new Notification_triggers;
								$N_triggers->type         = 3;
								$N_triggers->sender_id    = Auth::id();
								$N_triggers->sender_email = $from_email;
							    $N_triggers->from_name    = $from_name;
							    $N_triggers->subject      = $subject;
							    $N_triggers->content      = $message;
								$N_triggers->receivers    = $users[0]->ios_device_token;
							    $N_triggers->additional_params = $additional_params;
                                $N_triggers->created_at   = date("Y-m-d H:i:s");
								$N_triggers->save();
							}
						}

						/*foreach($users_list as $e)
						{
							
							$users = DB::table('users')->select('*')->where('email','=',$e)->get();
							$N_triggers = new Notification_triggers;
							$N_triggers->receiver_id = $users[0]->id;
							$N_triggers->sender_id    = Auth::id();
							$N_triggers->sender_email = $from_email;
							$N_triggers->from_name    = $from_name;
							$N_triggers->subject      = $subject;
							$get_name                 = explode('@',$e);
							$get_name                 = str_replace('_',' ',str_replace('.',' ',ucfirst($get_name[0])));
							$content                  = array("name" => $get_name,"notification" => array('MAIL' => $content));
							$template                 = array_merge($template,_TemplateDefaultResponse());
							$content                  = _TemplateResponse($content,$template);
							$N_triggers->content      = $message;
							$msg_params = array('badge' => 1, 'sound' => 'example.aiff', 'actionLocKey' => $subject, 'id' => $users[0]->id, 'title' => $_POST['subject'], 'app_name' => 'axs', 'custom' => array( 'id' => $users[0]->id, 'title' => $_POST['subject'] ));
							$additional_params = json_encode($msg_params);
							$N_triggers->additional_params = $additional_params;
							$N_triggers->created_at   = date("Y-m-d H:i:s");
							if($users[0]->ios_device_token){ 
							    $N_triggers = new Notification_triggers;
								$N_triggers->type         = 2;
								$N_triggers->sender_id    = Auth::id();
								$N_triggers->sender_email = $from_email;
							    $N_triggers->from_name    = $from_name;
							    $N_triggers->subject      = $subject;
							    $N_triggers->content      = $message;
								$N_triggers->receivers    = $e->android_device_token;
							    $N_triggers->additional_params = $additional_params;
                                $N_triggers->created_at   = date("Y-m-d H:i:s");
								$N_triggers->save();
								
							}
							if($users[0]->ios_device_token){ 
								
								$N_triggers = new Notification_triggers;
								$N_triggers->type         = 2;
								$N_triggers->sender_id    = Auth::id();
								$N_triggers->sender_email = $from_email;
							    $N_triggers->from_name    = $from_name;
							    $N_triggers->subject      = $subject;
							    $N_triggers->content      = $message;
								$N_triggers->receivers    = $e->ios_device_token;
							    $N_triggers->additional_params = $additional_params;
                                $N_triggers->created_at   = date("Y-m-d H:i:s");
								$N_triggers->save();
							}
						}

						/*foreach($users_list as $e)
						{
							$users = DB::table('users')->select('*')->where('email','=',$e)->get();
							$N_triggers = new Notification_triggers;
							$N_triggers->receiver_id  =  $users[0]->id;
							$N_triggers->sender_id    = Auth::id();
							$N_triggers->sender_email = $from_email;
							$N_triggers->from_name    = $from_name;
							$N_triggers->subject      = $subject;
							$get_name                 = explode('@',$e);
							$get_name                 = str_replace('_',' ',str_replace('.',' ',ucfirst($get_name[0])));
							$content                  = array("name" => $get_name,"notification" => array('MAIL' => $content));
							$template                 = array_merge($template,_TemplateDefaultResponse());
							$content                  = _TemplateResponse($content,$template);
							$N_triggers->content      = $content;
							$msg_params = array('badge' => 1, 'sound' => 'example.aiff', 'actionLocKey' => $subject, 'id' => $users[0]->id, 'title' => $_POST['subject'], 'app_name' => 'axs', 'custom' => array( 'id' => $users[0]->id, 'title' => $_POST['subject'] ));
							$additional_params = json_encode($msg_params);
							$N_triggers->additional_params = $additional_params;
							$N_triggers->created_at   = date("Y-m-d H:i:s");
							if($users[0]->ios_device_token){ 
								
								$N_triggers->type         = 3;
								$N_triggers->receivers    = $e->iphone_device_token;
								$N_triggers->save();
							}
						}*/
					}
					

                Session::flash('message', trans('messages.The Notification has been sent successfully.'));
            } catch(Exception $e) {
                Log::Instance()->add(Log::ERROR, $e);
            }
            return Redirect::to('admin/push-notifications');
        }
    }

    
    /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function anyAjaxNotificationList()
    {
        $notification = DB::table('notifications')
                            ->select('notifications.id as notification_id','notifications.message','notifications.read_status','notifications.created_date','users.name as user_name')
                            ->join('users','users.id','=','notifications.customer_id')
                            ->orderBy('notifications.created_date', 'desc');
        return Datatables::of($notification)->addColumn('action', function ($notification) {
            if(hasTask('admin/read_notifications')){
                if($notification->read_status==0):
                    $data = '<div class="btn-group notify-'.$notification->notification_id.'"><a href="javascript:;" onclick="change_read_status('.$notification->notification_id.')" class="btn btn-xs btn-white" title="'.trans("messages.Change to option read").'"><i class="fa fa-eye"></i>&nbsp;'.trans("messages.Change Status to read").'</a>';
                elseif($notification->read_status==1):
                    $data = '-';
                endif;
                return $data;
            }
        })
        ->addColumn('read_status', function ($notification) {
            if($notification->read_status==0):
                $data = '<span  class="label label-danger n_status_'.$notification->notification_id.'">'.trans("messages.Not Read").'</span>';
            elseif($notification->read_status==1):
                $data = '<span  class="label label-success">'.trans("messages.Read").'</span>';
            endif;
            return $data;
        })
        ->addColumn('message', function ($notification) {
            $data = '<strong>'.ucfirst($notification->user_name).' - '.$notification->message.'</strong>';
            return $data;
        })
        ->addColumn('created_at', function ($notification) {
			$data = '-';
			if(!empty($notification->created_at)){
				$data = date("d-m-Y h:i:s", strtotime($notification->created_at));
			}
			return $data;
		})
		->addColumn('updated_at', function ($notification) {
			$data = '-';
			if(!empty($notification->updated_at)){
				$data = date("d-m-Y h:i:s", strtotime($notification->updated_at));
			}
			return $data;
		})
        ->make(true);
    }
}
