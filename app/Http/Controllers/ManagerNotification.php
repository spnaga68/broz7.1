<?php
namespace App\Http\Controllers;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Support\Facades\Text;
use Illuminate\Support\Facades\Input;
use Yajra\Datatables\Datatables;
use DB;
use Session;
use Closure;
use Image;
use MetaTag;
use Mail;
use File;
use SEO;
use SEOMeta;
use OpenGraph;
use Twitter;
use App;
use URL;
use App\Model\notifications;


class ManagerNotification extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
		$this->site_name = isset(getAppConfig()->site_name)?ucfirst(getAppConfig()->site_name):'';
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		
        if (!Session::get('manager_vendor'))
        {
            return redirect()->guest('vendors/login');
        }
        else {
            return view('managers.notification.list');
        }
    }
    /**
     * Display a listing of the weight classes.
     *
     * @return Response
     */
    public function anyAjaxStoreManagerNotificationList()
    {
        $outlet_id    = Session::get('manager_outlet');
        $notification = DB::table('notifications')
                            ->select('notifications.id as notification_id','notifications.message','notifications.read_status','notifications.created_date','users.name as user_name')
                            ->join('users','users.id','=','notifications.customer_id')
                            ->orderBy('notifications.created_date', 'desc')
                            ->where('outlet_id','=',$outlet_id);
        return Datatables::of($notification)->addColumn('action', function ($notification) {
            if($notification->read_status==0):
                $data = '<div class="btn-group notify-'.$notification->notification_id.'"><a href="javascript:;" onclick="change_read_status('.$notification->notification_id.')" class="btn btn-xs btn-white" title="'.trans("messages.Change to option read").'"><i class="fa fa-eye"></i>&nbsp;'.trans("messages.Change Status to read").'</a>';
            elseif($notification->read_status==1):
                $data = '-';
            endif;
            return $data;
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
            $data = '<strong>'.$notification->user_name.' - '.$notification->message.'</strong>';
            return $data;
        })
        ->rawColumns(['message','action','read_status'])

        ->make(true);
    }
    public function notifications_read(Request $request)
    {
        if($request->ajax())
        {
            $vendor_id    = Session::get('manager_vendor');
			$outlet_id    = Session::get('manager_outlet');
            $c_id      = $request->input('cid');
            $res       = DB::table('notifications')->where('id', $c_id)->update(['read_status' => 1,'modified_date'=>date('Y-m-d H:i:s')]);
            $notifications = DB::table('notifications')
                                ->select('notifications.id','notifications.order_id','notifications.message','notifications.created_date','notifications.read_status','users.name','users.image')
                                ->leftJoin('users','users.id','=','notifications.customer_id')
                                ->where('read_status', 0)
                                ->where('vendor_id', $vendor_id)
                                ->orderBy('created_date', 'desc')->get();
            $count = count($notifications);
            $data  = ($res==true)?1:0;
            return response()->json([
                'data' => $data,'count' => $count,'vid'=>$vendor_id
            ]);
        }
    }
    
    public function notifications_reading(Request $request)
    {
		
        if($request->ajax())
        {
            $vendor_id    = Session::get('manager_vendor');
			$outlet_id    = Session::get('manager_outlet');
			
            $c_id      = $request->input('cid');
            $res       = DB::table('notifications')->where('id', $c_id)->update(['read_status' => 1,'modified_date'=>date('Y-m-d H:i:s')]);
            $notifications = DB::table('notifications')
                                ->select('notifications.id','notifications.order_id','notifications.message','notifications.created_date','notifications.read_status','users.name','users.image')
                                ->leftJoin('users','users.id','=','notifications.customer_id')
                                ->where('read_status', 0)
                                ->where('vendor_id', $vendor_id)
								->where('outlet_id', $outlet_id)
                                ->orderBy('created_date', 'desc')->get();
            $count = count($notifications);
            $data  = ($res==true)?1:0;
            return response()->json([
                'data' => $data,'count' => $count,'vid'=>$vendor_id
            ]);
        }
    }
}
